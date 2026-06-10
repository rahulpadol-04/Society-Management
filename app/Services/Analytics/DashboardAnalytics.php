<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Aggregates dashboard metrics and Chart.js data feeds. Every query is guarded
 * with Schema checks so the dashboards render correctly even while feature
 * module tables are still being provisioned, and so optional modules never
 * break the home screen. Society dashboards are filtered by the current tenant.
 */
class DashboardAnalytics
{
    public function platformStats(): array
    {
        return [
            'total_societies'      => $this->count('societies'),
            'active_societies'     => $this->count('societies', ['status' => 'active']),
            'trial_societies'      => $this->count('societies', ['subscription_status' => 'trial']),
            'total_plans'          => $this->count('subscription_plans'),
            'mrr'                  => $this->monthlyRecurringRevenue(),
            'revenue_this_month'   => $this->revenueBetween(now()->startOfMonth(), now()->endOfMonth()),
            'open_support_tickets' => Schema::hasTable('support_tickets')
                ? DB::table('support_tickets')->whereIn('status', ['open', 'in_progress'])->count() : 0,
            'new_inquiries'        => $this->count('contact_inquiries', ['status' => 'new']),
        ];
    }

    public function societyStats(): array
    {
        $sid = current_society_id();

        return [
            'residents'        => $this->scoped('residents', $sid),
            'total_units'      => $this->scoped('flats', $sid),
            'occupied_units'   => $this->scoped('flats', $sid, ['status' => 'occupied']),
            'open_complaints'  => $this->scoped('complaints', $sid, fn ($q) => $q->whereNotIn('status', ['resolved', 'closed'])),
            'visitors_today'   => $this->scopedToday('visitor_logs', $sid),
            'pending_dues'     => $this->sum('maintenance_bills', 'total', $sid, fn ($q) => $q->whereIn('status', ['unpaid', 'partial', 'overdue'])),
            'bookings_pending' => $this->scoped('facility_bookings', $sid, ['status' => 'pending']),
            'notices'          => $this->scoped('notices', $sid),
        ];
    }

    public function chart(string $type, User $user): array
    {
        $sid = $user->isSuperAdmin() ? null : $user->society_id;

        return match ($type) {
            'revenue'                => $this->revenueChart(),
            'visitor-trends'         => $this->dailyTrend('visitor_logs', $sid, 'Visitors'),
            'complaint-trends'       => $this->dailyTrend('complaints', $sid, 'Complaints'),
            'maintenance-collection' => $this->collectionChart($sid),
            'occupancy'              => $this->occupancyChart($sid),
            'facility-usage'         => $this->facilityUsageChart($sid),
            default                  => ['labels' => [], 'datasets' => []],
        };
    }

    // ---- chart builders -------------------------------------------------

    protected function revenueChart(): array
    {
        $labels = $values = [];

        foreach ($this->lastMonths(12) as $month) {
            $labels[] = $month->format('M Y');
            $values[] = $this->revenueBetween($month->copy()->startOfMonth(), $month->copy()->endOfMonth());
        }

        return $this->dataset($labels, 'Revenue', $values);
    }

    protected function collectionChart(?int $sid): array
    {
        $labels = $billed = $collected = [];

        foreach ($this->lastMonths(6) as $month) {
            $labels[] = $month->format('M Y');
            $billed[] = Schema::hasTable('maintenance_bills')
                ? (float) $this->tenantQuery('maintenance_bills', $sid)
                    ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])->sum('total')
                : 0;
            $collected[] = Schema::hasTable('maintenance_payments')
                ? (float) $this->tenantQuery('maintenance_payments', $sid)
                    ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])->sum('amount')
                : 0;
        }

        return [
            'labels'   => $labels,
            'datasets' => [
                ['label' => 'Billed', 'data' => $billed],
                ['label' => 'Collected', 'data' => $collected],
            ],
        ];
    }

    protected function occupancyChart(?int $sid): array
    {
        if (! Schema::hasTable('flats')) {
            return $this->dataset(['Occupied', 'Vacant'], 'Units', [0, 0]);
        }

        $occupied = $this->tenantQuery('flats', $sid)->where('status', 'occupied')->count();
        $total = $this->tenantQuery('flats', $sid)->count();

        return $this->dataset(['Occupied', 'Vacant'], 'Units', [$occupied, max(0, $total - $occupied)]);
    }

    protected function facilityUsageChart(?int $sid): array
    {
        if (! Schema::hasTable('facility_bookings') || ! Schema::hasTable('facilities')) {
            return ['labels' => [], 'datasets' => []];
        }

        $rows = $this->tenantQuery('facility_bookings', $sid)
            ->join('facilities', 'facilities.id', '=', 'facility_bookings.facility_id')
            ->where('facility_bookings.created_at', '>=', now()->subDays(30))
            ->groupBy('facilities.name')
            ->selectRaw('facilities.name as name, COUNT(*) as total')
            ->pluck('total', 'name');

        return $this->dataset($rows->keys()->all(), 'Bookings (30d)', $rows->values()->all());
    }

    protected function dailyTrend(string $table, ?int $sid, string $label): array
    {
        $labels = $values = [];

        if (! Schema::hasTable($table)) {
            return $this->dataset([], $label, []);
        }

        for ($i = 13; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $labels[] = $day->format('d M');
            $values[] = $this->tenantQuery($table, $sid)->whereDate('created_at', $day->toDateString())->count();
        }

        return $this->dataset($labels, $label, $values);
    }

    // ---- low-level helpers ---------------------------------------------

    protected function monthlyRecurringRevenue(): float
    {
        if (! Schema::hasTable('subscriptions')) {
            return 0;
        }

        return (float) DB::table('subscriptions')->whereIn('status', ['active', 'trial'])->sum('amount');
    }

    protected function revenueBetween(Carbon $from, Carbon $to): float
    {
        if (! Schema::hasTable('subscription_invoices')) {
            return 0;
        }

        return (float) DB::table('subscription_invoices')
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$from, $to])
            ->sum('total');
    }

    protected function count(string $table, array $where = []): int
    {
        return Schema::hasTable($table) ? DB::table($table)->where($where)->count() : 0;
    }

    protected function scoped(string $table, ?int $sid, array|callable $where = []): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        $q = $this->tenantQuery($table, $sid);
        is_callable($where) ? $where($q) : $q->where($where);

        return $q->count();
    }

    protected function scopedToday(string $table, ?int $sid): int
    {
        return Schema::hasTable($table)
            ? $this->tenantQuery($table, $sid)->whereDate('created_at', today())->count()
            : 0;
    }

    protected function sum(string $table, string $column, ?int $sid, callable $filter): float
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return 0;
        }

        $q = $this->tenantQuery($table, $sid);
        $filter($q);

        return (float) $q->sum($column);
    }

    protected function tenantQuery(string $table, ?int $sid)
    {
        $q = DB::table($table);

        if ($sid && Schema::hasColumn($table, 'society_id')) {
            $q->where($table.'.society_id', $sid);
        }

        return $q;
    }

    /** @return array<int,Carbon> */
    protected function lastMonths(int $count): array
    {
        return collect(range($count - 1, 0))
            ->map(fn ($i) => now()->subMonths($i)->startOfMonth())
            ->all();
    }

    protected function dataset(array $labels, string $label, array $data): array
    {
        return ['labels' => $labels, 'datasets' => [['label' => $label, 'data' => $data]]];
    }
}
