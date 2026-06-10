<?php

declare(strict_types=1);

namespace App\Http\Controllers\Visitors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Visitors\CheckInRequest;
use App\Models\Flat;
use App\Models\VisitorLog;
use App\Models\VisitorPass;
use App\Services\Visitors\VisitorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GateController extends Controller
{
    public function __construct(protected VisitorService $service) {}

    /** Guard console: walk-in form + code lookup + currently-inside table. */
    public function gate(): View
    {
        $this->authorize('checkin', VisitorLog::class);

        $inside = VisitorLog::with(['pass', 'flat'])
            ->where('status', 'in')
            ->whereDate('checked_in_at', today())
            ->latest('checked_in_at')
            ->get();

        $flats = Flat::with('tower')->orderBy('number')->get();

        return view('visitors.gate', compact('inside', 'flats'));
    }

    /** Walk-in check-in (no pre-approved pass). */
    public function checkInWalkIn(CheckInRequest $request): RedirectResponse
    {
        $log = $this->service->checkIn($request->validated());

        return redirect()->route('visitors.gate')
            ->with('success', "{$log->name} checked in successfully.");
    }

    /** Check-in by scanning / entering a pass code. */
    public function checkInByCode(Request $request): RedirectResponse
    {
        $this->authorize('checkin', VisitorLog::class);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:30'],
            'gate' => ['nullable', 'string', 'max:50'],
        ]);

        $pass = $this->service->validateCode($data['code']);

        if (! $pass) {
            return back()->withErrors(['code' => 'Invalid or expired pass code.'])->withInput();
        }

        $log = $this->service->checkIn([
            'code' => $data['code'],
            'gate' => $data['gate'] ?? null,
        ]);

        return redirect()->route('visitors.gate')
            ->with('success', "{$log->name} checked in via pass {$pass->code}.");
    }

    /** Check out a visitor log entry. */
    public function checkOut(VisitorLog $log): RedirectResponse
    {
        $this->authorize('checkout', $log);

        $this->service->checkOut($log);

        return redirect()->route('visitors.gate')
            ->with('success', "{$log->name} checked out.");
    }
}
