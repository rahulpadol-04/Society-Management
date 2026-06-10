<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function __construct(protected TwoFactorService $twoFactor, protected LoginController $login) {}

    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('login.2fa.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $userId = $request->session()->get('login.2fa.id');
        $user = User::find($userId);

        if (! $user || ! $this->twoFactor->challenge($user, $request->input('code'))) {
            return back()->withErrors(['code' => 'The provided two-factor code was invalid.']);
        }

        $remember = (bool) $request->session()->pull('login.2fa.remember', false);
        $request->session()->forget('login.2fa.id');

        Auth::login($user, $remember);

        return $this->login->finalize($request, $user);
    }
}
