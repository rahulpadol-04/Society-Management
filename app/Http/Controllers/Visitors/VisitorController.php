<?php

declare(strict_types=1);

namespace App\Http\Controllers\Visitors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Visitors\StoreVisitorPassRequest;
use App\Models\Flat;
use App\Models\VisitorLog;
use App\Models\VisitorPass;
use App\Services\Visitors\VisitorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitorController extends Controller
{
    public function __construct(protected VisitorService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', VisitorPass::class);

        $today = now()->startOfDay();

        $kpi = [
            'in_today'    => VisitorLog::whereDate('checked_in_at', today())->where('status', 'in')->count(),
            'checked_out' => VisitorLog::whereDate('checked_in_at', today())->where('status', 'out')->count(),
            'expected'    => VisitorPass::approved()
                ->whereDate('expected_at', today())
                ->count(),
            'pending'     => VisitorPass::where('status', 'pending')->count(),
        ];

        $logs = VisitorLog::with(['pass', 'flat', 'guardUser'])
            ->latest('checked_in_at')
            ->limit(500)
            ->get();

        $passes = VisitorPass::with(['host', 'flat'])
            ->latest()
            ->limit(500)
            ->get();

        return view('visitors.index', compact('kpi', 'logs', 'passes'));
    }

    public function create(): View
    {
        $this->authorize('create', VisitorPass::class);

        $flats = Flat::with('tower')->orderBy('number')->get();

        return view('visitors.create', compact('flats'));
    }

    public function store(StoreVisitorPassRequest $request): RedirectResponse
    {
        $pass = $this->service->createPass($request->validated());

        return redirect()->route('visitors.show', $pass)
            ->with('success', "Visitor pass {$pass->code} created successfully.");
    }

    public function show(VisitorPass $visitor): View
    {
        $this->authorize('view', $visitor);

        $visitor->load(['host', 'flat', 'approver', 'logs.guardUser', 'logs.flat']);

        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data='.urlencode($visitor->code);

        return view('visitors.show', [
            'pass'  => $visitor,
            'qrUrl' => $qrUrl,
        ]);
    }

    public function approve(VisitorPass $visitor): RedirectResponse
    {
        $this->authorize('approve', $visitor);

        $this->service->approve($visitor, auth()->id());

        return back()->with('success', 'Visitor pass approved.');
    }

    public function reject(Request $request, VisitorPass $visitor): RedirectResponse
    {
        $this->authorize('reject', $visitor);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $this->service->reject($visitor, $data['reason'] ?? null);

        return back()->with('success', 'Visitor pass rejected.');
    }
}
