<?php

declare(strict_types=1);

namespace App\Http\Controllers\Residents;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

/**
 * Read-only resident directory accessible to residents and tenants.
 * Exposes only active residents' name, flat, and phone number.
 */
class DirectoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', \App\Models\Resident::class);

        // The directory uses its own permission check via the gate.
        if (! auth()->user()->can('directory.view')) {
            abort(403);
        }

        $residents = Resident::active()
            ->with(['flat'])
            ->whereIn('type', ['owner', 'tenant'])
            ->orderBy('name')
            ->get();

        return view('directory.index', ['residents' => $residents]);
    }

    public function download(): StreamedResponse
    {
        if (! auth()->user()->can('directory.export')) {
            abort(403);
        }

        $residents = Resident::active()
            ->with(['flat'])
            ->whereIn('type', ['owner', 'tenant'])
            ->orderBy('name')
            ->get();

        $filename = 'directory_'.now()->format('Ymd').'.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($residents): void {
            $fh = fopen('php://output', 'w');
            fputcsv($fh, ['Name', 'Type', 'Flat', 'Phone']);

            foreach ($residents as $r) {
                fputcsv($fh, [
                    $r->name,
                    ucfirst($r->type),
                    $r->flat?->number ?? '',
                    $r->phone ?? '',
                ]);
            }

            fclose($fh);
        };

        return response()->stream($callback, 200, $headers);
    }
}
