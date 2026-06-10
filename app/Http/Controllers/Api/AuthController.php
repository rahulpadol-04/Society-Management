<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\LoginActivity;
use App\Services\Auth\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Token-based authentication for mobile apps and third-party API consumers via
 * Laravel Sanctum personal access tokens.
 */
class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected TwoFactorService $twoFactor,
        protected LoginActivity $activity,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
            'code'        => ['nullable', 'string'],   // 2FA code when required
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            $this->activity->record($request, 'failed', $user, $data['email']);
            throw ValidationException::withMessages(['email' => [__('auth.failed')]]);
        }

        if ($user->status !== 'active') {
            return $this->fail('Your account is not active.', 403);
        }

        if ($user->hasTwoFactorEnabled()) {
            if (empty($data['code'])) {
                return $this->ok(['requires_two_factor' => true], 'Two-factor code required.');
            }
            if (! $this->twoFactor->challenge($user, $data['code'])) {
                return $this->fail('Invalid two-factor code.', 422);
            }
        }

        $token = $user->createToken($data['device_name'] ?? 'api')->plainTextToken;

        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->save();
        $this->activity->record($request, 'success', $user);

        return $this->ok([
            'token' => $token,
            'user'  => $this->userPayload($user),
        ], 'Logged in successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->ok($this->userPayload($request->user()->load('roles')));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        $this->activity->record($request, 'logout', $request->user());

        return $this->ok(null, 'Logged out.');
    }

    protected function userPayload(User $user): array
    {
        return [
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'phone'       => $user->phone,
            'avatar_url'  => $user->avatar_url,
            'society_id'  => $user->society_id,
            'roles'       => $user->roles->pluck('slug'),
            'permissions' => $user->permissionSlugs(),
        ];
    }
}
