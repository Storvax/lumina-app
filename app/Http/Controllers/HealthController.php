<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\HealthMetric;
use App\Services\Analytics\HealthImportService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Gere a importação e visualização de métricas de saúde de wearables (CSV/JSON).
 */
class HealthController extends Controller
{
    public function __construct(private readonly HealthImportService $importer) {}

    /**
     * Página de importação com formulário de upload e instruções de formato.
     */
    public function import(): View
    {
        $user = Auth::user();

        $recent = HealthMetric::where('user_id', $user->id)
            ->orderByDesc('metric_date')
            ->limit(30)
            ->get()
            ->groupBy('metric_type');

        $lastImport = HealthMetric::where('user_id', $user->id)
            ->max('updated_at');

        return view('health.import', compact('recent', 'lastImport'));
    }

    /**
     * Processa o ficheiro enviado e importa as métricas.
     * O upsert no service garante idempotência — reimportar o mesmo ficheiro é seguro.
     */
    public function process(Request $request): RedirectResponse
    {
        $request->validate([
            'file'   => 'required|file|mimes:csv,txt,json|max:5120', // 5 MB
            'format' => 'required|in:csv,json',
        ], [
            'file.required' => 'Seleciona um ficheiro para importar.',
            'file.mimes'    => 'O ficheiro deve ser CSV ou JSON.',
            'file.max'      => 'O ficheiro não pode exceder 5 MB.',
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        $format  = $request->input('format');

        try {
            $rows = $format === 'json'
                ? $this->importer->parseJSON($content)
                : $this->importer->parseCSV($content);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        if ($rows->isEmpty()) {
            return back()->withErrors([
                'file' => 'Nenhuma métrica válida encontrada no ficheiro. Verifica o formato e os tipos de dados.',
            ]);
        }

        $count = $this->importer->store(Auth::user(), $rows, $format);

        return redirect()->route('health.import')
            ->with('success', "Importação concluída! {$count} métricas guardadas.");
    }

    /**
     * Devolve os dados de um tipo de métrica nos últimos 30 dias em JSON,
     * para alimentar o gráfico via fetch na página de importação.
     */
    public function chartData(Request $request): \Illuminate\Http\JsonResponse
    {
        $type = $request->input('type', 'heart_rate');

        if (! array_key_exists($type, HealthMetric::TYPES)) {
            return response()->json([]);
        }

        $data = HealthMetric::where('user_id', Auth::id())
            ->where('metric_type', $type)
            ->where('metric_date', '>=', Carbon::today()->subDays(29))
            ->orderBy('metric_date')
            ->get(['metric_date', 'value'])
            ->map(fn ($m) => [
                'date'  => $m->metric_date->format('d/m'),
                'value' => $m->value,
            ]);

        return response()->json($data);
    }
}
