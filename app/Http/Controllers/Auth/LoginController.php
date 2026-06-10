<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Auth\LoginActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(protected LoginActivity $activity) {}

    public function show(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $data = $request->validated();

        /** @var User|null $user */
        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            $this->activity->record($request, 'failed', $user, $data['email']);

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('auth.failed')]);
        }

        if ($user->status !== 'active') {
            $this->activity->record($request, 'locked', $user);

            return back()->withErrors(['email' => 'Your account is not active. Contact your administrator.']);
        }

        // Gate behind 2FA without authenticating the session yet.
        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put('login.2fa.id', $user->id);
            $request->session()->put('login.2fa.remember', (bool) ($data['remember'] ?? false));

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, (bool) ($data['remember'] ?? false));

        return $this->finalize($request, $user);
    }

    public function finalize(Request $request, User $user): RedirectResponse
    {
        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $this->activity->record($request, 'success', $user);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->activity->record($request, 'logout', $request->user());

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
