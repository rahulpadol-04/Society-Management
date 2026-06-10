<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreSocietyRequest;
use App\Http\Requests\Platform\UpdateSocietyRequest;
use App\Models\Society;
use App\Models\SubscriptionPlan;
use App\Services\Society\SocietyRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SocietyController extends Controller
{
    public function __construct(protected SocietyRegistrationService $registration) {}

    public function index(): View
    {
        abort_unless(request()->user()->can('societies.view'), 403);

        $societies = Society::withTrashed(false)
            ->with('plan')
            ->withCount('users')
            ->latest()
            ->get();

        $kpi = [
            'total'     => Society::count(),
            'active'    => Society::where('status', 'active')->count(),
            'trial'     => Society::where('subscription_status', 'trial')->count(),
            'suspended' => Society::where('status', 'suspended')->count(),
        ];

        return view('superadmin.societies.index', compact('societies', 'kpi'));
    }

    public function create(): View
    {
        $this->authorize('create', Society::class);

        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        return view('superadmin.societies.create', compact('plans'));
    }

    public function store(StoreSocietyRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $plan = $data['plan_id']
            ? SubscriptionPlan::find($data['plan_id'])
            : null;

        $society = $this->registration->register(
            societyData: [
                'name'          => $data['name'],
                'email'         => $data['email'],
                'phone'         => $data['phone'] ?? null,
                'address_line1' => $data['address_line1'] ?? null,
                'city'          => $data['city'] ?? null,
                'state'         => $data['state'] ?? null,
                'country'       => $data['country'] ?? 'India',
                'postal_code'   => $data['postal_code'] ?? null,
            ],
            adminData: [
                'name'     => $data['admin_name'],
                'email'    => $data['admin_email'],
                'password' => $data['admin_password'],
            ],
            plan: $plan,
        );

        return redirect()->route('societies.show', $society)
            ->with('success', "Society \"{$society->name}\" provisioned successfully.");
    }

    public function show(Society $society): View
    {
        $this->authorize('view', $society);

        $society->load(['plan', 'subscriptions.plan']);

        $sid = $society->id;

        $usage = [
            'residents' => DB::table('residents')->where('society_id', $sid)->count(),
            'flats'     => DB::table('flats')->where('society_id', $sid)->count(),
            'complaints'=> DB::table('complaints')->where('society_id', $sid)->count(),
            'users'     => DB::table('users')->where('society_id', $sid)->count(),
        ];

        $recentActivity = DB::table('users')
            ->where('society_id', $sid)
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'email', 'created_at']);

        return view('superadmin.societies.show', compact('society', 'usage', 'recentActivity'));
    }

    public function edit(Society $society): View
    {
        $this->authorize('update', $society);

        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        return view('superadmin.societies.edit', compact('society', 'plans'));
    }

    public function update(UpdateSocietyRequest $request, Society $society): RedirectResponse
    {
        $data = $request->validated();

        if (isset($data['plan_id'])) {
            $data['current_plan_id'] = $data['plan_id'];
            unset($data['plan_id']);
        }

        $society->update($data);

        return redirect()->route('societies.show', $society)
            ->with('success', 'Society updated.');
    }

    public function destroy(Society $society): RedirectResponse
    {
        $this->authorize('delete', $society);

        $society->delete();

        return redirect()->route('societies.index')
            ->with('success', "Society \"{$society->name}\" deleted.");
    }

    /** Toggle suspended / active status. */
    public function suspend(Request $request, Society $society): RedirectResponse
    {
        $this->authorize('suspend', $society);

        if ($society->status === 'suspended') {
            $society->update(['status' => 'active', 'subscription_status' => 'active']);
            $message = "Society \"{$society->name}\" reactivated.";
        } else {
            $society->update(['status' => 'suspended', 'subscription_status' => 'suspended']);
            $message = "Society \"{$society->name}\" suspended.";
        }

        return redirect()->back()->with('success', $message);
    }

    /** Begin impersonating a society — sets session key for IdentifyTenant. */
    public function impersonate(Society $society): RedirectResponse
    {
        $this->authorize('impersonate', $society);

        session(['impersonate_society_id' => $society->id]);

        return redirect()->route('dashboard')
            ->with('info', "Now impersonating \"{$society->name}\".");
    }

    /** Stop impersonating — clears session key. */
    public function stopImpersonating(): RedirectResponse
    {
        session()->forget('impersonate_society_id');

        return redirect()->route('dashboard')
            ->with('info', 'Impersonation ended.');
    }
}
