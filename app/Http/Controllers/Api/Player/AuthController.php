<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Player\PlayerCheckOtpRequest;
use App\Http\Requests\Player\PlayerCompleteProfileRequest;
use App\Http\Requests\Player\PlayerLoginRequest;
use App\Http\Requests\Player\PlayerRegisterRequest;
use App\Http\Requests\Player\PlayerSetPasswordRequest;
use App\Mail\OtpMail;
use App\Models\Player;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    /**
     * Register a player — sends OTP via SMS/WhatsApp (phone) or email.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(PlayerRegisterRequest $request)
    {
        $contact = $request->contact;
        $isEmail = filter_var($contact, FILTER_VALIDATE_EMAIL);

        if ($isEmail) {
            // Generate random 4-digit OTP and store in cache
            $otp = (string) rand(1000, 9999);
            generateOtp($contact, $otp);

            // Send OTP via email
            try {
                Mail::to($contact)->send(new OtpMail($otp));
            } catch (\Exception $e) {
                \Log::error('Email OTP failed for: ' . $contact . ' - ' . $e->getMessage());
                return $this->sendError(__('message.otp_send_failed'), [], 422);
            }

            return $this->sendResponse([], __('message.otp_send_success'));
        } else {
            // Send OTP via SMS/WhatsApp using the send() helper
            $sent = send($contact);

            if ($sent) {
                return $this->sendResponse([], __('message.otp_send_success'));
            }

            return $this->sendError(__('message.otp_send_failed_check_your_number_and_country_code'), [], 422);
        }
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

        $key  = $contact . '_' . $request->otp;
        $data = Cache::get($key);

        // Check if OTP exists in global array or in cache with key
        if (in_array($request->otp, Cache::get('otps', [])) || $data) {
            DB::beginTransaction();

            if ($data) {
                // Check for existing player
                $player = Player::where($field, $contact)->first();

                if (! $player) {
                    $player = Player::create([
                        $field       => $contact,
                        'first_name' => '',
                        'last_name'  => '',
                        'type'       => 'player',
                    ]);
                }

                Cache::forget($key);

                $token = JWTAuth::fromUser($player);
                Auth::guard('player')->setUser($player);
            } else {
                return $this->sendError(__('message.otp_invalid'), [], 401);
            }

            DB::commit();
        } else {
            return $this->sendError(__('message.otp_invalid'), [], 401);
        }

        $user = auth('player')->user();

        if (! $user) {
            return $this->sendError(__('message.credential_invalid'), [], 401);
        }

        return $this->sendResponse([
            'access_token' => $token ?? null,
            'token_type'   => 'bearer',
            'expires_in'   => auth('player')->factory()->getTTL() * 60,
            'player'       => $user,
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
     * Complete player profile data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeProfile(PlayerCompleteProfileRequest $request)
    {
        $player = auth('player')->user();

        $player->update($request->validated());

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
}
