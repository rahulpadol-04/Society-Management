<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterSocietyRequest;
use App\Models\SubscriptionPlan;
use App\Services\Society\SocietyRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Public self-service tenant signup: creates a Society, its admin user and a
 * trial subscription, then logs the new admin straight in.
 */
class SocietyRegistrationController extends Controller
{
    public function __construct(protected SocietyRegistrationService $registration) {}

    public function show(): View
    {
        $plans = SubscriptionPlan::active()->orderBy('sort_order')->get();

        return view('auth.register', compact('plans'));
    }

    public function store(RegisterSocietyRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $plan = isset($data['plan_id']) ? SubscriptionPlan::find($data['plan_id']) : null;

        $society = $this->registration->register(
            societyData: [
                'name'  => $data['society_name'],
                'city'  => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'],
            ],
            adminData: [
                'name'     => $data['admin_name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'] ?? null,
                'password' => $data['password'],
            ],
            plan: $plan,
        );

        Auth::login($society->users()->first());

        return redirect()->route('dashboard')
            ->with('success', "Welcome to CommunityOS! Your society \"{$society->name}\" is ready.");
    }
}
