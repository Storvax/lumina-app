<?php

namespace App\Http\Controllers;

use App\Models\SelfAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SelfAssessmentController extends Controller
{
    /** Histórico de auto-avaliações do utilizador */
    public function index()
    {
        $assessments = SelfAssessment::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        $phq9History = $assessments->where('type', 'phq9');
        $gad7History = $assessments->where('type', 'gad7');

        return view('assessment.index', compact('assessments', 'phq9History', 'gad7History'));
    }

    /** Formulário do questionário */
    public function create(string $type)
    {
        if (! in_array($type, ['phq9', 'gad7'])) {
            abort(404);
        }

        $questions = $type === 'phq9'
            ? SelfAssessment::phq9Questions()
            : SelfAssessment::gad7Questions();

        $options = SelfAssessment::answerOptions();

        return view('assessment.create', compact('type', 'questions', 'options'));
    }

    /** Guardar resultado e mostrar feedback */
    public function store(Request $request, string $type)
    {
        if (! in_array($type, ['phq9', 'gad7'])) {
            abort(404);
        }

        $questionCount = $type === 'phq9' ? 9 : 7;

        $validated = $request->validate([
            'answers'   => 'required|array|size:' . $questionCount,
            'answers.*' => 'required|integer|between:0,3',
        ]);

        $totalScore = array_sum($validated['answers']);
        $severity = SelfAssessment::calculateSeverity($type, $totalScore);

        $assessment = SelfAssessment::create([
            'user_id'     => Auth::id(),
            'type'        => $type,
            'answers'     => $validated['answers'],
            'total_score' => $totalScore,
            'severity'    => $severity,
        ]);

        return redirect()
            ->route('assessment.result', $assessment)
            ->with('just_completed', true);
    }

    /** Resultado de uma avaliação específica */
    public function show(SelfAssessment $assessment)
    {
        if ($assessment->user_id !== Auth::id()) {
            abort(403);
        }

        $questions = $assessment->type === 'phq9'
            ? SelfAssessment::phq9Questions()
            : SelfAssessment::gad7Questions();

        // Histórico anterior para comparação
        $previousAssessment = SelfAssessment::where('user_id', Auth::id())
            ->where('type', $assessment->type)
            ->where('id', '<', $assessment->id)
            ->orderByDesc('id')
            ->first();

        return view('assessment.show', compact('assessment', 'questions', 'previousAssessment'));
    }
}
