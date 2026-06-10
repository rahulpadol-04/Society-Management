<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Lets a logged-in user enable / confirm / disable TOTP two-factor auth from
 * their profile. The secret is generated, shown as a QR code, then confirmed
 * with a valid code before being activated.
 */
class TwoFactorSettingController extends Controller
{
    public function __construct(protected TwoFactorService $twoFactor) {}

    public function show(Request $request): View
    {
        $user = $request->user();
        $secret = $user->hasTwoFactorEnabled() ? null : $request->session()->get('2fa.secret');
        $qr = $secret ? $this->twoFactor->qrCodeSvg($user, $secret) : null;

        return view('auth.two-factor-settings', compact('user', 'secret', 'qr'));
    }

    public function enable(Request $request): RedirectResponse
    {
        $secret = $this->twoFactor->generateSecret();
        $request->session()->put('2fa.secret', $secret);

        return redirect()->route('two-factor.settings')
            ->with('info', 'Scan the QR code with your authenticator app, then confirm with a code.');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $secret = $request->session()->get('2fa.secret');

        if (! $secret || ! $this->twoFactor->verify($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        $request->user()->forceFill([
            'two_factor_secret'         => $secret,
            'two_factor_recovery_codes' => $this->twoFactor->generateRecoveryCodes(),
            'two_factor_confirmed_at'   => now(),
        ])->save();

        $request->session()->forget('2fa.secret');

        return redirect()->route('two-factor.settings')->with('success', 'Two-factor authentication enabled.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->user()->forceFill([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ])->save();

        return redirect()->route('two-factor.settings')->with('success', 'Two-factor authentication disabled.');
    }
}
