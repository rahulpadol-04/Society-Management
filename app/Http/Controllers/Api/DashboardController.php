<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Analytics\DashboardAnalytics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Feeds the dashboard widgets and Chart.js graphs. The heavy lifting (and
 * per-role data shaping – a resident sees their flat, an admin the whole
 * society) lives in DashboardAnalytics; this controller is a thin pass-through.
 */
class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(protected DashboardAnalytics $analytics) {}

    public function chart(Request $request, string $type): JsonResponse
    {
        return $this->ok($this->analytics->chart($type, $request->user()));
    }
}
