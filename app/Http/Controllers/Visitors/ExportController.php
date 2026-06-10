<?php

declare(strict_types=1);

namespace App\Http\Controllers\Visitors;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use App\Models\VisitorPass;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /** Export visitor logs as CSV. */
    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', VisitorPass::class);

        $logs = VisitorLog::with(['pass', 'flat', 'guardUser'])
            ->when($request->filled('from'), fn ($q) => $q->whereDate('checked_in_at', '>=', $request->from))
            ->when($request->filled('to'),   fn ($q) => $q->whereDate('checked_in_at', '<=', $request->to))
            ->orderBy('checked_in_at', 'desc')
            ->get();

        $filename = 'visitor-logs-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($logs): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                'ID', 'Pass Code', 'Name', 'Phone', 'Type', 'Purpose',
                'Vehicle', 'Gate', 'Flat', 'Guard', 'Checked In', 'Checked Out', 'Status',
            ]);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->pass?->code ?? '—',
                    $log->name,
                    $log->phone ?? '—',
                    $log->type,
                    $log->purpose ?? '—',
                    $log->vehicle_number ?? '—',
                    $log->gate ?? '—',
                    $log->flat?->number ?? '—',
                    $log->guardUser?->name ?? '—',
                    $log->checked_in_at?->format('Y-m-d H:i'),
                    $log->checked_out_at?->format('Y-m-d H:i') ?? '—',
                    $log->status,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
