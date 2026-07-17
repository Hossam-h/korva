<?php

namespace App\Services;

use RuntimeException;

/**
 * Verifies mobile social-login tokens and returns a normalised identity.
 *
 * The mobile app performs the native OAuth handshake (Google / Apple) and sends
 * the resulting token to the API; this service verifies it server-side.
 *
 * REQUIRES:
 *  - composer require laravel/socialite
 *  - config/services.php `google` / `apple` blocks + matching .env credentials
 *
 * Until those prerequisites are in place the verify() call throws, so the
 * endpoint fails cleanly instead of trusting an unverified token.
 */
class SocialLoginService
{
    /**
     * @return array{provider_id: string, email: ?string, name: ?string}
     */
    public function verify(string $provider, string $token): array
    {
        return match ($provider) {
            'google' => $this->verifyGoogle($token),
            'apple'  => $this->verifyApple($token),
            default  => throw new RuntimeException("Unsupported social provider: {$provider}"),
        };
    }

    /**
     * Verify a Google access/id token via Socialite's stateless driver.
     */
    protected function verifyGoogle(string $token): array
    {
        if (! class_exists(\Laravel\Socialite\Facades\Socialite::class)) {
            throw new RuntimeException('laravel/socialite is not installed. Run: composer require laravel/socialite');
        }

        /** @var \Laravel\Socialite\Two\User $user */
        $user = \Laravel\Socialite\Facades\Socialite::driver('google')
            ->stateless()
            ->userFromToken($token);

        return [
            'provider_id' => (string) $user->getId(),
            'email'       => $user->getEmail(),
            'name'        => $user->getName(),
        ];
    }

    /**
     * Verify an Apple identity token.
     *
     * TODO: validate the JWT signature against Apple's public keys
     * (https://appleid.apple.com/auth/keys) and the `aud`/`iss`/`exp` claims.
     * Requires APPLE_CLIENT_ID in config/services.php. Left as an explicit
     * failure until credentials + key verification are wired.
     */
    protected function verifyApple(string $token): array
    {
        throw new RuntimeException('Apple social login is not configured yet. Provide APPLE_CLIENT_ID and implement identity-token verification.');
    }
}
