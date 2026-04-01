<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CorporateAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CorporateController extends Controller
{
    /**
     * Dashboard corporativo com métricas anónimas agregadas.
     * Toda a lógica de agregação delegada ao CorporateAnalyticsService.
     */
    public function dashboard(Request $request, CorporateAnalyticsService $analytics): View
    {
        $company = Auth::user()->company;

        // Período configurável por query string (7, 30 ou 90 dias).
        $days = in_array((int) $request->query('dias', 30), [7, 30, 90])
            ? (int) $request->query('dias', 30)
            : 30;

        $report = $analytics->generateReport($company->id, $days);

        return view('corporate.dashboard', array_merge($report, [
            'company'      => $company,
            'selected_days' => $days,
        ]));
    }
}
