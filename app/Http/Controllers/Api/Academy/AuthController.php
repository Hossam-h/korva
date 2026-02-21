<?php

namespace App\Http\Controllers\Api\Academy;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Academy\AcademyRegister;
use App\Http\Requests\Academy\CheckOtp;
use App\Http\Requests\Academy\OnBoardingRequest;
use App\Models\Academy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

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
                    ->where('contact_number', $request['contact_number'])
                    ->first();

                if (!$checkUserExists) {
                    $academy = Academy::create([
                        'contact_number' => $request['contact_number'],
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


        $user = auth('academy')->user() ?? null;

        if (isset($user)) {
            return $this->sendError(__('message.credential_invalid'), 401);
        }

        $user['token'] = isset($token) ? $token : null;

      

        return  $this->sendResponse($user, __('message.login success'));
    }


       public function getToken($data, $type = null)
    {
        if ($type == 'email') {
            $user = Auth::guard('academy')->getProvider()->retrieveByCredentials([
                'email' => $data['email'], 'user_type' => $data['user_type']
            ]);
        } else {
            $user = Auth::guard('academy')->getProvider()->retrieveByCredentials([
                'contact_number' => $data['contact_number'], 'user_type' => $data['user_type']
            ]);
        }

        if (!$user) {
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

        return $this->sendResponse($academy->fresh(), __('message.onboarding_success'));
    }

}
