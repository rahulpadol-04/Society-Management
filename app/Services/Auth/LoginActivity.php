<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Records login / logout / failed attempts for the security audit trail
 * (Login History + Session Management features).
 */
class LoginActivity
{
    public function record(Request $request, string $status, ?User $user = null, ?string $email = null): LoginHistory
    {
        $agent = (string) $request->userAgent();

        return LoginHistory::create([
            'society_id'   => $user?->society_id,
            'user_id'      => $user?->id,
            'email'        => $email ?? $user?->email,
            'status'       => $status,
            'ip_address'   => $request->ip(),
            'user_agent'   => substr($agent, 0, 500),
            'platform'     => $this->detectPlatform($agent),
            'browser'      => $this->detectBrowser($agent),
            'logged_in_at' => now(),
        ]);
    }

    protected function detectPlatform(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Mac')     => 'macOS',
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'iPhone'), str_contains($agent, 'iPad') => 'iOS',
            str_contains($agent, 'Linux')   => 'Linux',
            default                         => 'Unknown',
        };
    }

    protected function detectBrowser(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'Edg')     => 'Edge',
            str_contains($agent, 'Chrome')  => 'Chrome',
            str_contains($agent, 'Firefox') => 'Firefox',
            str_contains($agent, 'Safari')  => 'Safari',
            default                         => 'Unknown',
        };
    }
}
