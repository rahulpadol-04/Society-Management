<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * TOTP-based two-factor authentication (Google Authenticator / Authy compatible)
 * built on pragmarx/google2fa. Secrets and recovery codes are stored encrypted
 * on the user model (see the User cast configuration).
 */
class TwoFactorService
{
    protected Google2FA $engine;

    public function __construct()
    {
        $this->engine = new Google2FA;
    }

    public function generateSecret(): string
    {
        return $this->engine->generateSecretKey();
    }

    public function verify(string $secret, string $code): bool
    {
        return $this->engine->verifyKey($secret, preg_replace('/\s+/', '', $code));
    }

    /** @return array<int,string> */
    public function generateRecoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))
            ->map(fn () => strtoupper(bin2hex(random_bytes(5))).'-'.strtoupper(bin2hex(random_bytes(5))))
            ->all();
    }

    public function qrCodeSvg(User $user, string $secret): string
    {
        $url = $this->engine->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return QrCode::format('svg')->size(220)->margin(1)->generate($url);
    }

    /** Verify a code or burn a matching recovery code. */
    public function challenge(User $user, string $code): bool
    {
        if ($user->two_factor_secret && $this->verify($user->two_factor_secret, $code)) {
            return true;
        }

        $codes = (array) $user->two_factor_recovery_codes;
        if (in_array($code, $codes, true)) {
            $user->forceFill([
                'two_factor_recovery_codes' => array_values(array_diff($codes, [$code])),
            ])->save();

            return true;
        }

        return false;
    }
}
