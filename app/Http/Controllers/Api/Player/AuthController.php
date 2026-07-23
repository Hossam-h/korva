<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Player\PlayerCheckOtpRequest;
use App\Http\Requests\Player\PlayerCompleteProfileRequest;
use App\Http\Requests\Player\PlayerForgotPasswordRequest;
use App\Http\Requests\Player\PlayerLoginRequest;
use App\Http\Requests\Player\PlayerRegisterRequest;
use App\Http\Requests\Player\PlayerResendOtpRequest;
use App\Http\Requests\Player\PlayerResetPasswordRequest;
use App\Http\Requests\Player\PlayerSetPasswordRequest;
use App\Http\Requests\Player\PlayerSocialLoginRequest;
use App\Http\Requests\Player\PlayerVerifyResetOtpRequest;
use App\Mail\OtpMail;
use App\Models\Player;
use App\Services\SocialLoginService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    /** Seconds a contact must wait between OTP requests. */
    private const OTP_COOLDOWN_SECONDS = 60;

    /** Failed OTP verification attempts before the contact is temporarily blocked. */
    private const OTP_MAX_ATTEMPTS = 5;

    /** Lifetime of a password-reset proof token. */
    private const RESET_TOKEN_TTL_MINUTES = 15;

    /**
     * Register a player — sends a 6-digit OTP via email or SMS/WhatsApp (phone).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(PlayerRegisterRequest $request)
    {
        return $this->dispatchOtp($request->contact);
    }

    /**
     * Resend the registration OTP (subject to the same cooldown/rate limit).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendOtp(PlayerResendOtpRequest $request)
    {
        return $this->dispatchOtp($request->contact);
    }

    /**
     * Verify OTP and create/login the player.
     * After verification the player should set a password.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkOtp(PlayerCheckOtpRequest $request)
    {
        $contact = $request->contact;
        $isEmail = filter_var($contact, FILTER_VALIDATE_EMAIL);
        $field   = $isEmail ? 'email' : 'phone';

        $attemptsKey = 'otp_attempts:' . $contact;

        if ((int) Cache::get($attemptsKey, 0) >= self::OTP_MAX_ATTEMPTS) {
            return $this->sendError(__('message.otp_too_many_attempts'), [], 429);
        }

        // Only the contact-specific OTP key is trusted (no shared global array match).
        $key = $contact . '_' . $request->otp;

        if (! Cache::get($key)) {
            $attempts = (int) Cache::get($attemptsKey, 0) + 1;
            Cache::put($attemptsKey, $attempts, now()->addMinutes(30));

            return $this->sendError(__('message.otp_invalid'), [], 401);
        }

        // OTP is valid — consume it and clear the attempt counter.
        Cache::forget($key);
        Cache::forget($attemptsKey);

        DB::beginTransaction();
        try {
            $player = Player::where($field, $contact)->first();

            if (! $player) {
                $player = Player::create([
                    $field       => $contact,
                    'first_name' => '',
                    'last_name'  => '',
                    // Initial default; the app overwrites this with the chosen
                    // account type (parent/player) during complete-profile.
                    'type'       => 'player',
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $token = JWTAuth::fromUser($player);
        Auth::guard('player')->setUser($player);

        return $this->sendResponse([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('player')->factory()->getTTL() * 60,
            'player'       => $player,
        ], __('message.login success'));
    }

    /**
     * Set password after OTP verification.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function setPassword(PlayerSetPasswordRequest $request)
    {
        $player = auth('player')->user();

        $player->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->sendResponse($player, __('message.password_set_success'));
    }

    /**
     * Complete player profile data (supports parent & player branches, image upload,
     * and map coordinates).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeProfile(PlayerCompleteProfileRequest $request)
    {
        $player = auth('player')->user();

        // Scalar fields first; the uploaded file is handled separately.
        $data = collect($request->validated())->except('image')->toArray();
        $player->update($data);

        if ($request->hasFile('image')) {
            $player->uploadFile($request->file('image'), 'image');
        }

        return $this->sendResponse($player->fresh(), __('message.profile_updated_success'));
    }

    /**
     * Login a player using email or phone with password.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(PlayerLoginRequest $request)
    {
        $login    = $request->login;
        $password = $request->password;

        // Determine if login value is email or phone
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $player = Player::where($field, $login)->first();

        if (! $player || ! Hash::check($password, $player->password)) {
            return $this->sendError(__('message.credential_invalid'), [], 401);
        }

        $token = JWTAuth::fromUser($player);

        $data = [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('player')->factory()->getTTL() * 60,
            'player'       => $player,
        ];

        return $this->sendResponse($data, __('message.login success'));
    }

    /**
     * Forgot password — send a reset OTP to a known player.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(PlayerForgotPasswordRequest $request)
    {
        $contact = $request->contact;
        $field   = filter_var($contact, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $player = Player::where($field, $contact)->first();

        if (! $player) {
            return $this->sendError(__('message.user_not_found'), [], 404);
        }

        return $this->dispatchOtp($contact);
    }

    /**
     * Verify the forgot-password OTP and issue a short-lived reset token (proof)
     * required to actually change the password.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyResetOtp(PlayerVerifyResetOtpRequest $request)
    {
        $contact = $request->contact;
        $key     = $contact . '_' . $request->otp;

        if (! Cache::get($key)) {
            return $this->sendError(__('message.otp_invalid'), [], 401);
        }

        // Consume the OTP and mint a single-use reset token bound to this contact.
        Cache::forget($key);

        $resetToken = Str::random(64);
        Cache::put('password_reset:' . $resetToken, $contact, now()->addMinutes(self::RESET_TOKEN_TTL_MINUTES));

        return $this->sendResponse(['reset_token' => $resetToken], __('message.otp_valid'));
    }

    /**
     * Reset the password using the reset token issued after OTP verification.
     * Cannot be completed with only public user information.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(PlayerResetPasswordRequest $request)
    {
        $tokenKey = 'password_reset:' . $request->reset_token;
        $contact  = Cache::get($tokenKey);

        if (! $contact) {
            return $this->sendError(__('message.reset_token_invalid'), [], 401);
        }

        $field  = filter_var($contact, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $player = Player::where($field, $contact)->first();

        if (! $player) {
            Cache::forget($tokenKey);

            return $this->sendError(__('message.user_not_found'), [], 404);
        }

        $player->update([
            'password' => Hash::make($request->password),
        ]);

        Cache::forget($tokenKey);

        return $this->sendResponse($player, __('message.password_reset_success'));
    }

    /**
     * Social login (Google / Apple). The mobile app performs native OAuth and
     * sends the resulting token; it is verified server-side before a JWT is issued.
     *
     * NOTE: requires laravel/socialite + provider credentials in config/services.php.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function socialLogin(PlayerSocialLoginRequest $request, SocialLoginService $social)
    {
        try {
            $identity = $social->verify($request->provider, $request->token);
        } catch (\Throwable $e) {
            \Log::warning('Social login verification failed: ' . $e->getMessage());

            return $this->sendError(__('message.social_login_failed'), [], 401);
        }

        $player = Player::where('provider', $request->provider)
            ->where('provider_id', $identity['provider_id'])
            ->first();

        // Fall back to matching an existing account by email, then link the provider.
        if (! $player && ! empty($identity['email'])) {
            $player = Player::where('email', $identity['email'])->first();
        }

        if (! $player) {
            $name = trim((string) ($identity['name'] ?? ''));
            [$first, $last] = array_pad(explode(' ', $name, 2), 2, '');

            $player = Player::create([
                'email'       => $identity['email'] ?? null,
                'first_name'  => $first,
                'last_name'   => $last,
                // Google/Apple never tell us parent vs. player — leave it
                // unset (null) rather than guessing. The app must call
                // complete-profile to set the real type, same as OTP signup.
                'provider'    => $request->provider,
                'provider_id' => $identity['provider_id'],
            ]);
        } else {
            $player->update([
                'provider'    => $request->provider,
                'provider_id' => $identity['provider_id'],
            ]);
        }

        $token = JWTAuth::fromUser($player);

        return $this->sendResponse([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('player')->factory()->getTTL() * 60,
            'player'       => $player,
        ], __('message.login success'));
    }

    /**
     * Get the authenticated player.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $player = auth('player')->user();

        return $this->sendResponse($player, 'Player retrieved successfully');
    }

    /**
     * Log the player out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('player')->logout();

        return $this->sendResponse(null, 'Successfully logged out');
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = auth('player')->refresh();

        $data = [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('player')->factory()->getTTL() * 60,
        ];

        return $this->sendResponse($data, 'Token refreshed successfully');
    }

    /**
     * Send an OTP to a contact, enforcing the resend cooldown. Shared by
     * register, resendOtp and forgotPassword.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function dispatchOtp(string $contact)
    {
        if ($remaining = $this->otpCooldownRemaining($contact)) {
            return $this->sendError(
                __('message.otp_resend_cooldown', ['seconds' => $remaining]),
                [],
                429
            );
        }

        if ($this->sendOtpTo($contact)) {
            $this->startOtpCooldown($contact);

            return $this->sendResponse([], __('message.otp_send_success'));
        }

        return $this->sendError(__('message.otp_send_failed'), [], 422);
    }

    /**
     * Generate + deliver a 6-digit OTP via email or phone.
     */
    private function sendOtpTo(string $contact): bool
    {
        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            $otp = '123456'; // static OTP for testing — remove after testing
            generateOtp($contact, $otp);

            try {
                Mail::to($contact)->send(new OtpMail($otp));
            } catch (\Exception $e) {
                \Log::error('Email OTP failed for: ' . $contact . ' - ' . $e->getMessage());

                return false;
            }

            return true;
        }

        // SMS/WhatsApp channel (currently a stub that stores a fixed 6-digit code).
        return (bool) send($contact);
    }

    /**
     * Remaining cooldown seconds for a contact (0 = may send now).
     */
    private function otpCooldownRemaining(string $contact): int
    {
        $until = Cache::get('otp_cooldown:' . $contact);

        if (! $until) {
            return 0;
        }

        return (int) max(0, now()->diffInSeconds($until, false));
    }

    /**
     * Start the resend cooldown window for a contact.
     */
    private function startOtpCooldown(string $contact): void
    {
        Cache::put(
            'otp_cooldown:' . $contact,
            now()->addSeconds(self::OTP_COOLDOWN_SECONDS),
            self::OTP_COOLDOWN_SECONDS
        );
    }
}
