<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\StaffMember;
use App\Services\Staff\StaffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function __construct(protected StaffService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('payroll', StaffMember::class);

        $period   = $request->input('period', now()->format('Y-m'));

        $payrolls = Payroll::with('staffMember')
            ->where('period', $period)
            ->latest()
            ->get();

        return view('staff.payroll.index', compact('payrolls', 'period'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $this->authorize('payroll', StaffMember::class);

        $data = $request->validate([
            'period' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ], [
            'period.regex' => 'Period must be in YYYY-MM format.',
        ]);

        $result = $this->service->generatePayroll($data['period']);

        return redirect()->route('staff.payroll.index', ['period' => $data['period']])
            ->with('success', "Payroll generated for {$result['count']} staff member(s).");
    }

    public function markPaid(Request $request, Payroll $payroll): RedirectResponse
    {
        $this->authorize('payroll', StaffMember::class);

        $payroll->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Payroll marked as paid.');
    }
}
