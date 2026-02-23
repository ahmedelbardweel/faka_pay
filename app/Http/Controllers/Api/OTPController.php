<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OTPService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OTPController extends Controller
{
    protected $otpService;
    protected $notificationService;

    public function __construct(OTPService $otpService, NotificationService $notificationService)
    {
        $this->otpService = $otpService;
        $this->notificationService = $notificationService;
    }

    /**
     * Update User's FCM Token.
     */
    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = Auth::user();
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'FCM token updated successfully',
        ]);
    }

    /**
     * Send OTP to the authenticated user.
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action_type' => 'required|string|in:login,transfer,registration',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $otpCode = $this->otpService->generate($user, $request->action_type);

        // Trigger actual Push Notification via FCM
        $this->notificationService->sendPush(
            $user->fcm_token,
            "Verification Code",
            "Your 6-digit verification code for {$request->action_type} is: {$otpCode}",
            ['otp_code' => $otpCode, 'action_type' => $request->action_type]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent successfully to your device',
        ]);
    }

    /**
     * Verify OTP.
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp_code' => 'required|string|size:6',
            'action_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $isValid = $this->otpService->verify($user, $request->otp_code, $request->action_type);

        if ($isValid) {
            return response()->json([
                'status' => 'success',
                'message' => 'OTP verified successfully',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid or expired OTP code',
        ], 400);
    }
}
