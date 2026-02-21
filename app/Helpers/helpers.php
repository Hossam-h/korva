<?php

use Illuminate\Support\Facades\Http;

if (!function_exists('send')) {
    /**
     * Send OTP via SMS first, fallback to WhatsApp if SMS fails
     * @param string $contactNumber Full phone number with country code
     * @return bool
     */
    function send($contactNumber)
    {
        // Try SMS first
        $smsResult = sendOtpViaChannel($contactNumber, 'sms');

        if ($smsResult) {
            return true;
        }

        // Fallback to WhatsApp if SMS fails
        \Log::info('SMS OTP failed, trying WhatsApp fallback for: ' . $contactNumber);
        $whatsappResult = sendOtpViaChannel($contactNumber, 'whatsapp');

        return $whatsappResult;
    }
}

if (!function_exists('sendOtpViaChannel')) {
    /**
     * Send OTP via specified channel (whatsapp or sms)
     * @param string $contactNumber Full phone number with country code
     * @param string $channel 'whatsapp' or 'sms'
     * @return bool
     */
    function sendOtpViaChannel($contactNumber, $channel = 'whatsapp')
    {
        try {
            $response = Http::withHeaders([
                'beon-token' => env('BEON_TOKEN'),
            ])->post(env('BEON_URL'), [
                'phoneNumber' => $contactNumber,
                'name'        => env('APP_NAME', 'Eyvar'),
                'type'        => $channel, // 'whatsapp' or 'sms'
                'otp_length'  => 4,
                'lang'        => 'en',
                'reference'   => rand(100, 999),
            ]);

            // Check response
            if ($response->successful()) {
                $responseData = $response->json();

                // Check if OTP exists in response
                if (isset($responseData['data']['otp'])) {
                    $otp = $responseData['data']['otp'];
                    generateOtp($contactNumber, $otp);
                    \Log::info("OTP sent successfully via {$channel} to: {$contactNumber} - OTP: {$otp}");
                    return true;
                } else {
                    \Log::error("{$channel} OTP Response missing OTP data: " . json_encode($responseData));
                    return false;
                }
            } else {
                \Log::error("{$channel} OTP API Error for {$contactNumber}: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            \Log::error("{$channel} OTP Exception for {$contactNumber}: " . $e->getMessage());
            return false;
        }
    }
}
