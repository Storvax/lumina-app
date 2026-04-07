<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\HrWebhookConfiguration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HrIntegrationController extends Controller
{
    /**
     * Lista as integrações HR configuradas para a empresa.
     */
    public function index(): View
    {
        $company      = Auth::user()->company;
        $configs      = HrWebhookConfiguration::where('company_id', $company->id)
            ->withCount(['logs as total_events' => fn ($q) => $q])
            ->withCount(['logs as failed_events' => fn ($q) => $q->where('status', 'failed')])
            ->get();

        $recentLogs = \App\Models\HrWebhookLog::where('company_id', $company->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('corporate.hr-integrations', compact('company', 'configs', 'recentLogs'));
    }

    /**
     * Cria ou atualiza uma configuração de integração HR.
     * Gera automaticamente um secret_token HMAC seguro.
     */
    public function store(Request $request): RedirectResponse
    {
        $company = Auth::user()->company;

        $validated = $request->validate([
            'provider'    => ['required', 'in:sap,workday,generic'],
            'webhook_url' => ['nullable', 'url', 'max:500'],
            'event_types' => ['required', 'array', 'min:1'],
            'event_types.*' => ['in:employee.created,employee.terminated'],
        ]);

        HrWebhookConfiguration::updateOrCreate(
            ['company_id' => $company->id, 'provider' => $validated['provider']],
            [
                'webhook_url'  => $validated['webhook_url'],
                'secret_token' => Str::random(64),
                'is_active'    => true,
                'event_types'  => $validated['event_types'],
            ]
        );

        return back()->with('success', 'Integração configurada. Copia o endpoint e o token para o teu sistema HR.');
    }

    /**
     * Desativa e remove uma configuração de integração.
     */
    public function destroy(HrWebhookConfiguration $config): RedirectResponse
    {
        if ($config->company_id !== Auth::user()->company->id) {
            abort(403);
        }

        $config->delete();

        return back()->with('success', 'Integração removida.');
    }
}
