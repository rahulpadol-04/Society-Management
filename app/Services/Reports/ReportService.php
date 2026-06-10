<?php

declare(strict_types=1);

namespace App\Services\Reports;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Aggregates cross-module data for the Reports feature module.
 *
 * Design rules (mirrors DashboardAnalytics):
 *  - Every query is wrapped in a Schema::hasTable / Schema::hasColumn guard so
 *    that the reports render correctly even if an optional module has not been
 *    enabled / migrated yet.
 *  - All queries are tenant-scoped via tenantQuery() which prepends a
 *    society_id WHERE clause when the table carries that column.
 *  - Return shape is ALWAYS: {title, columns[], rows[][], summary[], chart{}}.
 *    Callers can rely on the keys existing even when data is absent.
 */
class ReportService
{
    // -----------------------------------------------------------------------
    // Public report methods
    // -----------------------------------------------------------------------

    /**
     * Visitor report: daily totals + breakdown by visitor type.
     *
     * Source table: visitor_logs
     */
    public function visitorReport(Carbon $from, Carbon $to, ?int $sid = null): array
    {
        $empty = $this->emptyReport('Visitor Report');

        if (! Schema::hasTable('visitor_logs')) {
            return $empty;
        }

        $base = $this->tenantQuery('visitor_logs', $sid)
            ->whereBetween('checked_in_at', [$from->startOfDay(), $to->copy()->endOfDay()]);

        // Daily rows
        $daily = (clone $base)
            ->selectRaw('DATE(checked_in_at) as day, COUNT(*) as total')
            ->groupByRaw('DATE(checked_in_at)')
            ->orderByRaw('DATE(checked_in_at)')
            ->get();

        // By type
        $byType = (clone $base)
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();

        $rows = $daily->map(fn ($r) => [
            $r->day,
            (int) $r->total,
        ])->all();

        $summary = [
            [
                'label' => 'Total Visitors',
                'value' => $daily->sum('total'),
                'icon'  => 'bi-door-open',
                'color' => 'primary',
            ],
            [
                'label' => 'Days with Visitors',
                'value' => $daily->count(),
                'icon'  => 'bi-calendar-check',
                'color' => 'info',
            ],
            [
                'label' => 'Avg per Day',
                'value' => $daily->count() > 0
                    ? round($daily->sum('total') / $daily->count(), 1)
                    : 0,
                'icon'  => 'bi-bar-chart',
                'color' => 'success',
            ],
        ];

        $chart = [
            'labels'   => $daily->pluck('day')->all(),
            'datasets' => [
                [
                    'label' => 'Visitors',
                    'data'  => $daily->pluck('total')->all(),
                ],
            ],
        ];

        // Append by-type block to rows as a second section separator
        $typeRows = $byType->map(fn ($r) => [
            ucfirst((string) $r->type),
            (int) $r->total,
        ])->all();

        return [
            'title'   => 'Visitor Report',
            'columns' => ['Date', 'Total Visitors'],
            'rows'    => $rows,
            'summary' => $summary,
            'chart'   => $chart,
            'extras'  => [
                'by_type' => [
                    'title'   => 'Visitors by Type',
                    'columns' => ['Type', 'Count'],
                    'rows'    => $typeRows,
                ],
            ],
        ];
    }

    /**
     * Billing report: billed / paid / outstanding per billing period.
     *
     * Source table: maintenance_bills
     */
    public function billingReport(Carbon $from, Carbon $to, ?int $sid = null): array
    {
        $empty = $this->emptyReport('Billing Report');

        if (! Schema::hasTable('maintenance_bills')) {
            return $empty;
        }

        $rows = $this->tenantQuery('maintenance_bills', $sid)
            ->whereBetween('bill_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('period, SUM(total) as billed, SUM(paid_amount) as paid, SUM(total - paid_amount) as outstanding, COUNT(*) as count')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $totalBilled      = (float) $rows->sum('billed');
        $totalPaid        = (float) $rows->sum('paid');
        $totalOutstanding = (float) $rows->sum('outstanding');

        $summary = [
            ['label' => 'Total Billed',      'value' => money($totalBilled),      'icon' => 'bi-cash-stack',        'color' => 'primary'],
            ['label' => 'Collected',          'value' => money($totalPaid),        'icon' => 'bi-check-circle',      'color' => 'success'],
            ['label' => 'Outstanding',        'value' => money($totalOutstanding), 'icon' => 'bi-exclamation-circle','color' => 'warning'],
            ['label' => 'Collection Rate',    'value' => $totalBilled > 0
                ? round(($totalPaid / $totalBilled) * 100, 1).'%'
                : '—',
                'icon' => 'bi-percent', 'color' => 'info',
            ],
        ];

        $chart = [
            'labels'   => $rows->pluck('period')->all(),
            'datasets' => [
                ['label' => 'Billed',      'data' => $rows->pluck('billed')->map(fn ($v) => (float) $v)->all()],
                ['label' => 'Paid',        'data' => $rows->pluck('paid')->map(fn ($v) => (float) $v)->all()],
                ['label' => 'Outstanding', 'data' => $rows->pluck('outstanding')->map(fn ($v) => (float) $v)->all()],
            ],
        ];

        return [
            'title'   => 'Billing Report',
            'columns' => ['Period', 'Bills', 'Billed', 'Paid', 'Outstanding'],
            'rows'    => $rows->map(fn ($r) => [
                $r->period,
                (int) $r->count,
                money((float) $r->billed),
                money((float) $r->paid),
                money((float) $r->outstanding),
            ])->all(),
            'summary' => $summary,
            'chart'   => $chart,
            'extras'  => [],
        ];
    }

    /**
     * Collection report: payments by method + by month.
     *
     * Source table: maintenance_payments
     */
    public function collectionReport(Carbon $from, Carbon $to, ?int $sid = null): array
    {
        $empty = $this->emptyReport('Collection Report');

        if (! Schema::hasTable('maintenance_payments')) {
            return $empty;
        }

        $base = $this->tenantQuery('maintenance_payments', $sid)
            ->whereBetween('paid_at', [$from->startOfDay(), $to->copy()->endOfDay()]);

        // By method
        $byMethod = (clone $base)
            ->selectRaw('method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('method')
            ->orderByDesc('total')
            ->get();

        // By month
        $byMonth = (clone $base)
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(amount) as total, COUNT(*) as count")
            ->groupByRaw("DATE_FORMAT(paid_at, '%Y-%m')")
            ->orderBy('month')
            ->get();

        $grandTotal = (float) $byMethod->sum('total');
        $txnCount   = (int) $byMethod->sum('count');

        $summary = [
            ['label' => 'Total Collected', 'value' => money($grandTotal),  'icon' => 'bi-cash-coin',    'color' => 'success'],
            ['label' => 'Transactions',    'value' => $txnCount,           'icon' => 'bi-receipt',       'color' => 'info'],
            ['label' => 'Avg per Txn',     'value' => $txnCount > 0
                ? money($grandTotal / $txnCount)
                : '—',
                'icon' => 'bi-bar-chart', 'color' => 'primary',
            ],
        ];

        $chart = [
            'labels'   => $byMethod->pluck('method')->map(fn ($m) => ucfirst((string) $m))->all(),
            'datasets' => [
                ['label' => 'Amount Collected', 'data' => $byMethod->pluck('total')->map(fn ($v) => (float) $v)->all()],
            ],
        ];

        return [
            'title'   => 'Collection Report',
            'columns' => ['Month', 'Transactions', 'Amount'],
            'rows'    => $byMonth->map(fn ($r) => [
                $r->month,
                (int) $r->count,
                money((float) $r->total),
            ])->all(),
            'summary' => $summary,
            'chart'   => $chart,
            'extras'  => [
                'by_method' => [
                    'title'   => 'Collection by Payment Method',
                    'columns' => ['Method', 'Transactions', 'Amount'],
                    'rows'    => $byMethod->map(fn ($r) => [
                        ucfirst((string) $r->method),
                        (int) $r->count,
                        money((float) $r->total),
                    ])->all(),
                ],
            ],
        ];
    }

    /**
     * Complaint report: by status, by category, avg resolution hours.
     *
     * Source tables: complaints, complaint_categories
     */
    public function complaintReport(Carbon $from, Carbon $to, ?int $sid = null): array
    {
        $empty = $this->emptyReport('Complaint Report');

        if (! Schema::hasTable('complaints')) {
            return $empty;
        }

        $base = $this->tenantQuery('complaints', $sid)
            ->whereBetween('complaints.created_at', [$from->startOfDay(), $to->copy()->endOfDay()]);

        // By status
        $byStatus = (clone $base)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        // By category
        $hasCategories = Schema::hasTable('complaint_categories');

        $byCategory = $hasCategories
            ? (clone $base)
                ->leftJoin('complaint_categories', 'complaint_categories.id', '=', 'complaints.complaint_category_id')
                ->selectRaw('COALESCE(complaint_categories.name, "Uncategorised") as category, COUNT(*) as total')
                ->groupBy('category')
                ->orderByDesc('total')
                ->get()
            : collect();

        // Avg resolution (hours between created_at and resolved_at)
        $avgHours = null;
        if (Schema::hasColumn('complaints', 'resolved_at')) {
            $avgRaw = (clone $base)
                ->whereNotNull('resolved_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, complaints.created_at, resolved_at)) as avg_hours')
                ->value('avg_hours');
            $avgHours = $avgRaw !== null ? round((float) $avgRaw, 1) : null;
        }

        $total    = (int) $byStatus->sum('total');
        $resolved = (int) ($byStatus->firstWhere('status', 'resolved')?->total ?? 0)
            + (int) ($byStatus->firstWhere('status', 'closed')?->total ?? 0);

        $summary = [
            ['label' => 'Total Complaints',   'value' => $total,                              'icon' => 'bi-exclamation-octagon', 'color' => 'primary'],
            ['label' => 'Resolved / Closed',  'value' => $resolved,                           'icon' => 'bi-check2-circle',        'color' => 'success'],
            ['label' => 'Resolution Rate',    'value' => $total > 0 ? round(($resolved / $total) * 100, 1).'%' : '—', 'icon' => 'bi-percent', 'color' => 'info'],
            ['label' => 'Avg Resolution',     'value' => $avgHours !== null ? $avgHours.'h' : '—', 'icon' => 'bi-clock-history', 'color' => 'warning'],
        ];

        $chart = [
            'labels'   => $byStatus->pluck('status')->map(fn ($s) => ucfirst(str_replace('_', ' ', (string) $s)))->all(),
            'datasets' => [
                ['label' => 'Complaints', 'data' => $byStatus->pluck('total')->all()],
            ],
        ];

        return [
            'title'   => 'Complaint Report',
            'columns' => ['Status', 'Count'],
            'rows'    => $byStatus->map(fn ($r) => [
                ucfirst(str_replace('_', ' ', (string) $r->status)),
                (int) $r->total,
            ])->all(),
            'summary' => $summary,
            'chart'   => $chart,
            'extras'  => [
                'by_category' => [
                    'title'   => 'Complaints by Category',
                    'columns' => ['Category', 'Count'],
                    'rows'    => $byCategory->map(fn ($r) => [(string) $r->category, (int) $r->total])->all(),
                ],
            ],
        ];
    }

    /**
     * Facility report: bookings by facility and by status.
     *
     * Source tables: facility_bookings, facilities
     */
    public function facilityReport(Carbon $from, Carbon $to, ?int $sid = null): array
    {
        $empty = $this->emptyReport('Facility Report');

        if (! Schema::hasTable('facility_bookings') || ! Schema::hasTable('facilities')) {
            return $empty;
        }

        $rows = $this->tenantQuery('facility_bookings', $sid)
            ->join('facilities', 'facilities.id', '=', 'facility_bookings.facility_id')
            ->whereBetween('facility_bookings.created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('facilities.name as facility, facility_bookings.status, COUNT(*) as total, SUM(facility_bookings.amount) as revenue')
            ->groupBy('facilities.name', 'facility_bookings.status')
            ->orderBy('facilities.name')
            ->orderByDesc('total')
            ->get();

        $byFacility = $rows->groupBy('facility')->map(fn ($g) => [
            'bookings' => $g->sum('total'),
            'revenue'  => $g->sum('revenue'),
        ]);

        $summary = [
            ['label' => 'Total Bookings', 'value' => $rows->sum('total'),                       'icon' => 'bi-calendar-check', 'color' => 'primary'],
            ['label' => 'Revenue',        'value' => money((float) $rows->sum('revenue')),       'icon' => 'bi-cash-coin',      'color' => 'success'],
            ['label' => 'Facilities Used','value' => $byFacility->count(),                       'icon' => 'bi-building',       'color' => 'info'],
        ];

        $chart = [
            'labels'   => $byFacility->keys()->all(),
            'datasets' => [
                ['label' => 'Bookings', 'data' => $byFacility->pluck('bookings')->all()],
            ],
        ];

        return [
            'title'   => 'Facility Report',
            'columns' => ['Facility', 'Status', 'Bookings', 'Revenue'],
            'rows'    => $rows->map(fn ($r) => [
                (string) $r->facility,
                ucfirst((string) $r->status),
                (int) $r->total,
                money((float) $r->revenue),
            ])->all(),
            'summary' => $summary,
            'chart'   => $chart,
            'extras'  => [],
        ];
    }

    /**
     * Occupancy report: flats by status and by tower + occupancy %.
     *
     * Source tables: flats, towers
     */
    public function occupancyReport(?int $sid = null): array
    {
        $empty = $this->emptyReport('Occupancy Report');

        if (! Schema::hasTable('flats')) {
            return $empty;
        }

        // By status
        $byStatus = $this->tenantQuery('flats', $sid)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $totalFlats   = (int) $byStatus->sum('total');
        $occupiedFlats = (int) ($byStatus->firstWhere('status', 'occupied')?->total ?? 0);
        $vacantFlats   = (int) ($byStatus->firstWhere('status', 'vacant')?->total ?? 0);
        $occupancyPct  = $totalFlats > 0 ? round(($occupiedFlats / $totalFlats) * 100, 1) : 0.0;

        // By tower (if towers table exists)
        $byTower = collect();
        if (Schema::hasTable('towers')) {
            $byTower = $this->tenantQuery('flats', $sid)
                ->join('towers', 'towers.id', '=', 'flats.tower_id')
                ->selectRaw("towers.name as tower, COUNT(*) as total, SUM(CASE WHEN flats.status = 'occupied' THEN 1 ELSE 0 END) as occupied")
                ->groupBy('towers.name')
                ->orderBy('towers.name')
                ->get();
        }

        $summary = [
            ['label' => 'Total Flats',     'value' => $totalFlats,        'icon' => 'bi-house-door',  'color' => 'primary'],
            ['label' => 'Occupied',        'value' => $occupiedFlats,     'icon' => 'bi-house-check', 'color' => 'success'],
            ['label' => 'Vacant',          'value' => $vacantFlats,       'icon' => 'bi-house',        'color' => 'warning'],
            ['label' => 'Occupancy Rate',  'value' => $occupancyPct.'%',  'icon' => 'bi-percent',      'color' => 'info'],
        ];

        $chart = [
            'labels'   => ['Occupied', 'Vacant', 'Other'],
            'datasets' => [
                [
                    'label' => 'Units',
                    'data'  => [
                        $occupiedFlats,
                        $vacantFlats,
                        max(0, $totalFlats - $occupiedFlats - $vacantFlats),
                    ],
                ],
            ],
        ];

        $towerRows = $byTower->map(fn ($r) => [
            (string) $r->tower,
            (int) $r->total,
            (int) $r->occupied,
            max(0, (int) $r->total - (int) $r->occupied),
            $r->total > 0 ? round(((int) $r->occupied / (int) $r->total) * 100, 1).'%' : '—',
        ])->all();

        return [
            'title'   => 'Occupancy Report',
            'columns' => ['Status', 'Count'],
            'rows'    => $byStatus->map(fn ($r) => [
                ucfirst(str_replace('_', ' ', (string) $r->status)),
                (int) $r->total,
            ])->all(),
            'summary' => $summary,
            'chart'   => $chart,
            'extras'  => [
                'by_tower' => [
                    'title'   => 'Occupancy by Tower / Block',
                    'columns' => ['Tower', 'Total', 'Occupied', 'Vacant', 'Occupancy %'],
                    'rows'    => $towerRows,
                ],
            ],
        ];
    }

    /**
     * Financial report: income vs expense + surplus.
     *
     * Primary source: journal_lines + ledger_accounts (if accounting module active).
     * Fallback source: maintenance_payments (income) vs vendor_payments + asset costs (expense).
     */
    public function financialReport(Carbon $from, Carbon $to, ?int $sid = null): array
    {
        $empty = $this->emptyReport('Financial Report');

        if (Schema::hasTable('journal_lines') && Schema::hasTable('ledger_accounts')) {
            return $this->financialFromJournal($from, $to, $sid);
        }

        return $this->financialFallback($from, $to, $sid);
    }

    // -----------------------------------------------------------------------
    // Headline KPIs for the index page
    // -----------------------------------------------------------------------

    /**
     * Returns a small set of headline KPIs used on the reports hub page.
     * All values are Schema-guarded.
     */
    public function headlineKpis(?int $sid = null): array
    {
        return [
            'total_bills'       => $this->safeCount('maintenance_bills', $sid),
            'total_complaints'  => $this->safeCount('complaints', $sid),
            'total_visitors'    => $this->safeCount('visitor_logs', $sid),
            'total_flats'       => $this->safeCount('flats', $sid),
            'occupied_flats'    => $this->safeCount('flats', $sid, ['status' => 'occupied']),
            'collections_mtd'   => $this->safeSum('maintenance_payments', 'amount', $sid, fn ($q) => $q->where('paid_at', '>=', now()->startOfMonth())),
        ];
    }

    // -----------------------------------------------------------------------
    // Financial sub-methods
    // -----------------------------------------------------------------------

    private function financialFromJournal(Carbon $from, Carbon $to, ?int $sid): array
    {
        // Income accounts: credit side minus debit side
        $incomeRows = $this->tenantQuery('journal_lines', $sid)
            ->join('ledger_accounts', 'ledger_accounts.id', '=', 'journal_lines.ledger_account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('ledger_accounts.type', 'income')
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.entry_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('ledger_accounts.name as account, SUM(journal_lines.credit) as credit, SUM(journal_lines.debit) as debit')
            ->groupBy('ledger_accounts.name')
            ->orderByDesc('credit')
            ->get();

        // Expense accounts: debit side minus credit side
        $expenseRows = $this->tenantQuery('journal_lines', $sid)
            ->join('ledger_accounts', 'ledger_accounts.id', '=', 'journal_lines.ledger_account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('ledger_accounts.type', 'expense')
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.entry_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('ledger_accounts.name as account, SUM(journal_lines.debit) as debit, SUM(journal_lines.credit) as credit')
            ->groupBy('ledger_accounts.name')
            ->orderByDesc('debit')
            ->get();

        $totalIncome  = (float) $incomeRows->sum(fn ($r) => (float) $r->credit - (float) $r->debit);
        $totalExpense = (float) $expenseRows->sum(fn ($r) => (float) $r->debit - (float) $r->credit);
        $surplus      = $totalIncome - $totalExpense;

        $summary = $this->financialSummary($totalIncome, $totalExpense, $surplus);

        $chart = [
            'labels'   => ['Income', 'Expense', 'Surplus'],
            'datasets' => [
                ['label' => 'Amount (₹)', 'data' => [$totalIncome, $totalExpense, max(0, $surplus)]],
            ],
        ];

        $rows = [];

        foreach ($incomeRows as $r) {
            $rows[] = ['Income', (string) $r->account, money((float) $r->credit - (float) $r->debit), ''];
        }

        foreach ($expenseRows as $r) {
            $rows[] = ['Expense', (string) $r->account, '', money((float) $r->debit - (float) $r->credit)];
        }

        return [
            'title'   => 'Financial Report',
            'columns' => ['Type', 'Account', 'Income', 'Expense'],
            'rows'    => $rows,
            'summary' => $summary,
            'chart'   => $chart,
            'extras'  => [],
        ];
    }

    private function financialFallback(Carbon $from, Carbon $to, ?int $sid): array
    {
        $income  = 0.0;
        $expense = 0.0;

        // Income: maintenance payments
        if (Schema::hasTable('maintenance_payments')) {
            $income += (float) $this->tenantQuery('maintenance_payments', $sid)
                ->whereBetween('paid_at', [$from->startOfDay(), $to->copy()->endOfDay()])
                ->sum('amount');
        }

        // Expense: vendor payments
        if (Schema::hasTable('vendor_payments')) {
            $expense += (float) $this->tenantQuery('vendor_payments', $sid)
                ->whereBetween('paid_at', [$from->startOfDay(), $to->copy()->endOfDay()])
                ->sum('amount');
        }

        // Expense: asset purchase costs (pro-rated: assets purchased in range)
        if (Schema::hasTable('assets') && Schema::hasColumn('assets', 'purchase_cost')) {
            $expense += (float) $this->tenantQuery('assets', $sid)
                ->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])
                ->sum('purchase_cost');
        }

        $surplus = $income - $expense;
        $summary = $this->financialSummary($income, $expense, $surplus);

        $chart = [
            'labels'   => ['Income', 'Expense', 'Surplus'],
            'datasets' => [
                ['label' => 'Amount (₹)', 'data' => [$income, $expense, max(0.0, $surplus)]],
            ],
        ];

        return [
            'title'   => 'Financial Report (Summary)',
            'columns' => ['Category', 'Amount'],
            'rows'    => [
                ['Maintenance Collections', money($income)],
                ['Vendor Payments',         money($expense)],
                ['Net Surplus',             money($surplus)],
            ],
            'summary' => $summary,
            'chart'   => $chart,
            'extras'  => [],
        ];
    }

    private function financialSummary(float $income, float $expense, float $surplus): array
    {
        return [
            ['label' => 'Total Income',  'value' => money($income),  'icon' => 'bi-arrow-up-circle',   'color' => 'success'],
            ['label' => 'Total Expense', 'value' => money($expense), 'icon' => 'bi-arrow-down-circle', 'color' => 'danger'],
            ['label' => 'Net Surplus',   'value' => money($surplus), 'icon' => 'bi-bank',               'color' => $surplus >= 0 ? 'info' : 'warning'],
        ];
    }

    // -----------------------------------------------------------------------
    // Private helpers (copied from DashboardAnalytics pattern)
    // -----------------------------------------------------------------------

    private function emptyReport(string $title): array
    {
        return [
            'title'   => $title,
            'columns' => [],
            'rows'    => [],
            'summary' => [],
            'chart'   => ['labels' => [], 'datasets' => []],
            'extras'  => [],
        ];
    }

    private function tenantQuery(string $table, ?int $sid): \Illuminate\Database\Query\Builder
    {
        $q = DB::table($table);

        if ($sid && Schema::hasColumn($table, 'society_id')) {
            $q->where($table.'.society_id', $sid);
        }

        return $q;
    }

    private function safeCount(string $table, ?int $sid, array $where = []): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return (int) $this->tenantQuery($table, $sid)->where($where)->count();
    }

    private function safeSum(string $table, string $column, ?int $sid, callable $filter): float
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return 0.0;
        }

        $q = $this->tenantQuery($table, $sid);
        $filter($q);

        return (float) $q->sum($column);
    }

    /** @return array<int, Carbon> */
    private function lastMonths(int $count): array
    {
        return collect(range($count - 1, 0))
            ->map(fn (int $i) => now()->subMonths($i)->startOfMonth())
            ->all();
    }
}
