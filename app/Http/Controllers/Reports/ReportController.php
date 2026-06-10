<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /** Allowed report type slugs — validated on every request. */
    private const TYPES = [
        'visitor',
        'billing',
        'collection',
        'complaint',
        'facility',
        'occupancy',
        'financial',
    ];

    public function __construct(protected ReportService $service) {}

    // -----------------------------------------------------------------------
    // Reports hub
    // -----------------------------------------------------------------------

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $sid  = current_society_id();
        $kpis = $this->service->headlineKpis($sid);

        return view('reports.index', [
            'kpis' => $kpis,
        ]);
    }

    // -----------------------------------------------------------------------
    // Individual report view
    // -----------------------------------------------------------------------

    public function show(Request $request, string $type): View
    {
        abort_unless($request->user()->can('reports.view'), 403);
        abort_unless(in_array($type, self::TYPES, true), 404);

        [$from, $to] = $this->parseDateRange($request);

        $sid    = current_society_id();
        $report = $this->buildReport($type, $from, $to, $sid);

        return view('reports.show', [
            'type'   => $type,
            'from'   => $from->toDateString(),
            'to'     => $to->toDateString(),
            'report' => $report,
        ]);
    }

    // -----------------------------------------------------------------------
    // CSV export
    // -----------------------------------------------------------------------

    public function export(Request $request, string $type): StreamedResponse
    {
        abort_unless($request->user()->can('reports.export'), 403);
        abort_unless(in_array($type, self::TYPES, true), 404);

        [$from, $to] = $this->parseDateRange($request);

        $sid    = current_society_id();
        $report = $this->buildReport($type, $from, $to, $sid);

        $filename = $type.'-report-'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($report): void {
            $fh = fopen('php://output', 'w');

            // Main table
            fputcsv($fh, $report['columns']);

            foreach ($report['rows'] as $row) {
                fputcsv($fh, $row);
            }

            // Extra sections (by_type, by_method, by_tower, etc.)
            foreach ($report['extras'] as $extra) {
                fputcsv($fh, []);
                fputcsv($fh, [$extra['title']]);
                fputcsv($fh, $extra['columns']);

                foreach ($extra['rows'] as $row) {
                    fputcsv($fh, $row);
                }
            }

            fclose($fh);
        };

        return response()->stream($callback, 200, $headers);
    }

    // -----------------------------------------------------------------------
    // Print / PDF view
    // -----------------------------------------------------------------------

    public function print(Request $request, string $type): View
    {
        abort_unless($request->user()->can('reports.view'), 403);
        abort_unless(in_array($type, self::TYPES, true), 404);

        [$from, $to] = $this->parseDateRange($request);

        $sid    = current_society_id();
        $report = $this->buildReport($type, $from, $to, $sid);

        return view('reports.print', [
            'type'    => $type,
            'from'    => $from->toDateString(),
            'to'      => $to->toDateString(),
            'report'  => $report,
            'society' => current_society(),
        ]);
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    /** @return array{\Illuminate\Support\Carbon,\Illuminate\Support\Carbon} */
    private function parseDateRange(Request $request): array
    {
        $from = $request->filled('from')
            ? \Illuminate\Support\Carbon::parse($request->input('from'))->startOfDay()
            : now()->subDays(90)->startOfDay();

        $to = $request->filled('to')
            ? \Illuminate\Support\Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        return [$from, $to];
    }

    private function buildReport(string $type, \Illuminate\Support\Carbon $from, \Illuminate\Support\Carbon $to, ?int $sid): array
    {
        return match ($type) {
            'visitor'    => $this->service->visitorReport($from, $to, $sid),
            'billing'    => $this->service->billingReport($from, $to, $sid),
            'collection' => $this->service->collectionReport($from, $to, $sid),
            'complaint'  => $this->service->complaintReport($from, $to, $sid),
            'facility'   => $this->service->facilityReport($from, $to, $sid),
            'occupancy'  => $this->service->occupancyReport($sid),
            'financial'  => $this->service->financialReport($from, $to, $sid),
            default      => ['title' => '', 'columns' => [], 'rows' => [], 'summary' => [], 'chart' => ['labels' => [], 'datasets' => []], 'extras' => []],
        };
    }
}
