<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Analytics\DashboardAnalytics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected DashboardAnalytics $analytics) {}

    public function index(Request $request): View
    {
        if ($request->user()->isSuperAdmin()) {
            return view('superadmin.dashboard', [
                'stats' => $this->analytics->platformStats(),
            ]);
        }

        return view('dashboard', [
            'stats' => $this->analytics->societyStats(),
        ]);
    }

    /** JSON feed for the Chart.js widgets (AJAX). */
    public function chart(Request $request, string $type): JsonResponse
    {
        return response()->json(
            $this->analytics->chart($type, $request->user())
        );
    }
}
