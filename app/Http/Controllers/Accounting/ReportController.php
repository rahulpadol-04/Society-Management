<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\LedgerAccount;
use App\Services\Accounting\AccountingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(protected AccountingService $service) {}

    public function trialBalance(Request $request): View|StreamedResponse
    {
        $this->authorize('accounting.reports');

        $asOf = $request->filled('as_of') ? Carbon::parse($request->input('as_of')) : now();
        $rows = $this->service->trialBalance($asOf);

        if ($request->boolean('csv')) {
            return $this->streamCsv('trial-balance', ['Account', 'Type', 'Code', 'Total Debit', 'Total Credit', 'Net'], $rows, function ($row) {
                return [$row['account_name'], $row['account_type'], $row['account_code'], $row['total_debit'], $row['total_credit'], $row['net']];
            });
        }

        return view('accounting.reports.trial-balance', compact('rows', 'asOf'));
    }

    public function profitLoss(Request $request): View|StreamedResponse
    {
        $this->authorize('accounting.reports');

        $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()));
        $to   = Carbon::parse($request->input('to', now()->endOfMonth()->toDateString()));

        $report = $this->service->profitAndLoss($from, $to);

        if ($request->boolean('csv')) {
            $rows = array_merge(
                array_map(fn ($r) => [$r['name'], 'Income', $r['amount']], $report['income']),
                array_map(fn ($r) => [$r['name'], 'Expense', $r['amount']], $report['expenses']),
            );

            return $this->streamCsv('profit-loss', ['Account', 'Category', 'Amount'], $rows, fn ($r) => $r);
        }

        return view('accounting.reports.profit-loss', compact('report', 'from', 'to'));
    }

    public function balanceSheet(Request $request): View|StreamedResponse
    {
        $this->authorize('accounting.reports');

        $asOf   = $request->filled('as_of') ? Carbon::parse($request->input('as_of')) : now();
        $report = $this->service->balanceSheet($asOf);

        if ($request->boolean('csv')) {
            $rows = array_merge(
                array_map(fn ($r) => [$r['name'], 'Asset', $r['amount']], $report['assets']),
                array_map(fn ($r) => [$r['name'], 'Liability', $r['amount']], $report['liabilities']),
                array_map(fn ($r) => [$r['name'], 'Equity', $r['amount']], $report['equity']),
            );

            return $this->streamCsv('balance-sheet', ['Account', 'Category', 'Amount'], $rows, fn ($r) => $r);
        }

        return view('accounting.reports.balance-sheet', compact('report', 'asOf'));
    }

    /**
     * @param  callable(array): array  $mapper
     */
    protected function streamCsv(string $filename, array $headers, array $rows, callable $mapper): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($headers, $rows, $mapper): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $mapper($row));
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename={$filename}-".now()->format('Y-m-d').'.csv');

        return $response;
    }
}
