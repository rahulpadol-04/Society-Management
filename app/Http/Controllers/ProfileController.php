<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PasswordHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:120'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'designation' => ['nullable', 'string', 'max:120'],
            'avatar'      => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $request->user()->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user = $request->user();

        // Password policy: cannot reuse any of the last 5 passwords.
        $recent = PasswordHistory::where('user_id', $user->id)->latest()->take(5)->pluck('password');
        foreach ($recent as $hash) {
            if (Hash::check($data['password'], $hash)) {
                throw ValidationException::withMessages([
                    'password' => 'You cannot reuse one of your last 5 passwords.',
                ]);
            }
        }

        $user->update(['password' => $data['password'], 'password_changed_at' => now()]);
        PasswordHistory::create(['user_id' => $user->id, 'password' => $user->password]);

        return back()->with('success', 'Password changed successfully.');
    }
}
