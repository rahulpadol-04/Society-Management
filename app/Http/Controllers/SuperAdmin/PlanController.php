<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StorePlanRequest;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        abort_unless(request()->user()->can('plans.view'), 403);

        $plans = SubscriptionPlan::withTrashed(false)
            ->withCount('societies')
            ->orderBy('sort_order')
            ->get();

        return view('superadmin.plans.index', compact('plans'));
    }

    public function create(): View
    {
        $this->authorize('create', SubscriptionPlan::class);

        $features = config('communityos.features');

        return view('superadmin.plans.create', compact('features'));
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['features'] = $data['features'] ?? [];
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);

        $plan = SubscriptionPlan::create($data);

        return redirect()->route('plans.index')
            ->with('success', "Plan \"{$plan->name}\" created.");
    }

    public function show(SubscriptionPlan $plan): View
    {
        $this->authorize('view', $plan);

        $plan->loadCount('societies');

        return view('superadmin.plans.show', compact('plan'));
    }

    public function edit(SubscriptionPlan $plan): View
    {
        $this->authorize('update', $plan);

        $features = config('communityos.features');

        return view('superadmin.plans.edit', compact('plan', 'features'));
    }

    public function update(StorePlanRequest $request, SubscriptionPlan $plan): RedirectResponse
    {
        $data = $request->validated();
        $data['features'] = $data['features'] ?? [];
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);

        $plan->update($data);

        return redirect()->route('plans.index')
            ->with('success', "Plan \"{$plan->name}\" updated.");
    }

    public function destroy(SubscriptionPlan $plan): RedirectResponse
    {
        $this->authorize('delete', $plan);

        $plan->delete();

        return redirect()->route('plans.index')
            ->with('success', "Plan \"{$plan->name}\" deleted.");
    }
}
