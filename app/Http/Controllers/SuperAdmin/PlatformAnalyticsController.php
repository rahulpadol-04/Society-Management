<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Society;
use App\Models\SubscriptionPlan;
use App\Services\Analytics\DashboardAnalytics;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlatformAnalyticsController extends Controller
{
    public function __construct(protected DashboardAnalytics $analytics) {}

    public function index(): View
    {
        abort_unless(request()->user()->can('platform-analytics.view'), 403);

        $stats = $this->analytics->platformStats();

        // Societies growth: count by month (last 12 months)
        $growth = DB::table('societies')
            ->selectRaw('DATE_FORMAT(created_at, "%b %Y") as month, COUNT(*) as total')
            ->whereNull('deleted_at')
            ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->groupByRaw('DATE_FORMAT(created_at, "%b %Y"), DATE_FORMAT(created_at, "%Y%m")')
            ->orderByRaw('DATE_FORMAT(created_at, "%Y%m")')
            ->pluck('total', 'month');

        // Plan distribution
        $planDistribution = Society::selectRaw('subscription_plans.name as plan_name, COUNT(societies.id) as total')
            ->leftJoin('subscription_plans', 'subscription_plans.id', '=', 'societies.current_plan_id')
            ->whereNull('societies.deleted_at')
            ->groupBy('subscription_plans.name')
            ->pluck('total', 'plan_name');

        // Top societies by user count
        $topSocieties = Society::withCount('users')
            ->whereNull('deleted_at')
            ->orderByDesc('users_count')
            ->limit(10)
            ->get(['id', 'name', 'slug', 'city', 'subscription_status']);

        return view('superadmin.platform-analytics.index', compact(
            'stats',
            'growth',
            'planDistribution',
            'topSocieties',
        ));
    }
}
