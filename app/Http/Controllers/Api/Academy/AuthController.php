<?php

namespace App\Http\Controllers\Api\Academy;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Academy\AcademyRegister;
use App\Http\Requests\Academy\CheckOtp;
use App\Http\Requests\Academy\OnBoardingRequest;
use App\Models\Academy;
use App\Models\AcademyAttach;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    public function register(AcademyRegister $request)
    {
        $contactNumber = $request->contact_number;

        $send = send($contactNumber);

        if ($send) {
            $this->sendResponse([], __('message.otp_send_success'));
        } else {
            $this->sendError(__('message.otp_send_failed_check_your_number_and_country_code'), 422);
        }
    }

    public function checkOtp(CheckOtp $request)
    {
        $contactNumber = $request->contact_number;
        $key = $contactNumber.'_'.$request['otp'];
        $data = Cache::get($key);

        // Check if OTP exists in global array or in cache with key
        if (in_array($request['otp'], Cache::get('otps', [])) || $data) {
            DB::beginTransaction();

            if ($data) {
                // Check for existing user including soft-deleted ones
                $checkUserExists = Academy::withTrashed()
                    ->where('phone', $request['contact_number'])
                    ->first();

                if (! $checkUserExists) {
                    $academy = Academy::create([
                        'phone' => $request['contact_number'],
                    ]);
                }

                Cache::forget($key);
                $message = __('message.login success');

                $token = $this->getToken($request);
            } else {
                return $this->sendError(__('message.otp_invalid'), 401);
            }

            DB::commit();
        } else {
            return $this->sendError(__('message.otp_invalid'), 401);
        }

        $user = auth('academy')->user();

        if (! $user) {
            return $this->sendError(__('message.credential_invalid'), 401);
        }

        return $this->sendResponse([
            'token' => $token ?? null,
        ], __('message.login success'));
    }

    public function getToken($data, $type = null)
    {
        if ($type == 'email') {
            $user = Auth::guard('academy')->getProvider()->retrieveByCredentials([
                'email' => $data['email'],
            ]);
        } else {
            $user = Auth::guard('academy')->getProvider()->retrieveByCredentials([
                'phone' => $data['contact_number'],
            ]);
        }

        if (! $user) {
            return $this->sendError(__('message.credential_invalid'), 401);
        }

        $jwtToken = JWTAuth::fromUser($user);
        Auth::guard('academy')->setUser($user);

        return $jwtToken;
    }

    public function onBoarding(OnBoardingRequest $request)
    {
        $academy = auth('academy')->user();

        $academy->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'age_group' => $request->age_group,
            'country' => $request->country,
            'city' => $request->city,
            'address' => $request->address,
            'business_owner_email' => $request->business_owner_email,
            'business_owner_phone' => $request->business_owner_phone,
        ]);

        // Handle attachments
        if ($request->has('attachments')) {
            foreach ($request->attachments as $item) {
                $attach = AcademyAttach::create([
                    'academy_id' => $academy->id,
                    'attach_type' => $item['attach_type'],
                    'attach_path' => '', // will be updated by uploadFile()
                ]);

                $attach->uploadFile($item['attach_path'], 'attach_path', 'academy_attaches');
            }
        }

        return $this->sendResponse(
            $academy->fresh()->load('attaches'),
            __('message.onboarding_success')
        );
    }

    public function forgetPassword(ForgetPasswordRequest $request)
    {

        $academy = Academy::where('phone', $request->contact_number)->first();

        if (! $academy) {
            return $this->sendError(__('message.user_not_found'), 404);
        }

        $send = send($request->contact_number);

        if ($send) {
            $this->sendResponse([], __('message.otp_send_success'));
        } else {
            $this->sendError(__('message.otp_send_failed_check_your_number_and_country_code'), 422);
        }
    }

    public function checkForgetOtp(CheckOtpForgetRequest $request)
    {
        $contactNumber = $request->contact_number;
        $key = $contactNumber.'_'.$request['otp'];
        $data = Cache::get($key);

        // Check if OTP exists in global array or in cache with key
        if (in_array($request['otp'], Cache::get('otps', [])) || $data) {
            Cache::forget($key);
        } else {
            return $this->sendError(__('message.otp_invalid'), 401);
        }

        return $this->sendResponse([], __('message.otp_valid'));
    }

    public function resetPassword(ResetPasswordRequest $request)
    {

        $academy = Academy::where('contact_number', $request->contact_number)->first();

        if (! $academy) {
            return $this->sendError(__('message.user_not_found'), 404);
        }

        $academy->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->sendResponse($academy, __('message.password_reset_success'));
    }
}
