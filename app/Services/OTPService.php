<?php

namespace App\Services;

use App\Models\OtpVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OTPService
{
    /**
     * Generate a 6-digit OTP and save it to the database.
     *
     * @param User $user
     * @param string $actionType
     * @return string
     */
    public function generate(User $user, string $actionType): string
    {
        // Cancel any previous unused OTPs for this action
        OtpVerification::where('user_id', $user->id)
            ->where('action_type', $actionType)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        $otpCode = (string) rand(100000, 999999);
        
        OtpVerification::create([
            'user_id' => $user->id,
            'otp_code' => $otpCode,
            'action_type' => $actionType,
            'expires_at' => Carbon::now()->addMinutes(10),
            'is_used' => false,
        ]);

        return $otpCode;
    }

    /**
     * Verify the provided OTP code.
     *
     * @param User $user
     * @param string $otpCode
     * @param string $actionType
     * @return bool
     */
    public function verify(User $user, string $otpCode, string $actionType): bool
    {
        $verification = OtpVerification::where('user_id', $user->id)
            ->where('otp_code', $otpCode)
            ->where('action_type', $actionType)
            ->where('is_used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($verification) {
            $verification->update(['is_used' => true]);
            return true;
        }

        return false;
    }
}
