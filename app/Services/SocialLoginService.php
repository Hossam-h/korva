<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use UnexpectedValueException;

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
     * Verify an Apple identity token — the signed JWT the app gets from
     * ASAuthorizationAppleIDCredential.identityToken (iOS) or the Android/JS
     * equivalent. This is the token the mobile app must send, NOT Apple's
     * accessToken: the access token is an opaque string with no identity
     * claims (only usable against Apple's own token-revoke/refresh endpoint),
     * whereas the identity token is a self-contained JWT we can verify
     * locally against Apple's public keys — no callback to Apple needed
     * beyond fetching those keys, mirroring how verifyGoogle() works above.
     */
    protected function verifyApple(string $token): array
    {
        $keySet = Cache::remember('apple_jwks', now()->addHours(6), function () {
            return Http::timeout(5)->get('https://appleid.apple.com/auth/keys')->throw()->json();
        });

        $payload = JWT::decode($token, JWK::parseKeySet($keySet, 'RS256'));

        if ($payload->iss !== 'https://appleid.apple.com') {
            throw new UnexpectedValueException('Unexpected Apple identity token issuer: '.$payload->iss);
        }

        $allowedAudiences = array_filter(array_map('trim', explode(',', (string) config('services.apple.client_id'))));

        if ($allowedAudiences && ! in_array($payload->aud, $allowedAudiences, true)) {
            throw new UnexpectedValueException('Unexpected Apple identity token audience: '.$payload->aud);
        }

        return [
            'provider_id' => (string) $payload->sub,
            // Only present on the FIRST authorization; null on every login after.
            'email'       => $payload->email ?? null,
            // Apple never includes the name in the token at all (unlike Google).
            'name'        => null,
        ];
    }
}
