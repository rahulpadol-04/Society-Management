<?php

declare(strict_types=1);

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Models\Broadcast;
use App\Services\Communication\CommunicationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunicationController extends Controller
{
    public function __construct(protected CommunicationService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Broadcast::class);

        $kpi = $this->service->kpi();

        $recentBroadcasts = $this->service->repository()->query()
            ->with(['creator'])
            ->latest()
            ->limit(20)
            ->get();

        return view('communication.index', [
            'kpi'              => $kpi,
            'recentBroadcasts' => $recentBroadcasts,
        ]);
    }
}
