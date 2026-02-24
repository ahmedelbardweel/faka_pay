<?php

namespace App\Http\Controllers;

use App\Services\OTPService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class OtpWebController extends Controller
{
    protected $otpService;
    protected $notificationService;

    public function __construct(OTPService $otpService, NotificationService $notificationService)
    {
        $this->otpService = $otpService;
        $this->notificationService = $notificationService;
    }

    /**
     * Show the OTP verification form.
     */
    public function showVerifyForm(Request $request)
    {
        $action_type = $request->query('action_type', 'transfer');
        $intended_url = $request->query('intended_url', URL::previous());
        $action_data = $request->session()->get('otp_action_data', []);

        // Optional: Regenerate/Resend OTP if requested
        $user = Auth::user();
        $otpCode = $this->otpService->generate($user, $action_type);
        
        // Send Push Notification (if available)
        $this->notificationService->sendPush(
            $user->fcm_token,
            "Verification Code",
            "Your 6-digit verification code is: {$otpCode}",
            ['otp_code' => $otpCode, 'action_type' => $action_type]
        );

        // Send Email
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\OtpVerificationMail($otpCode, $user->name));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send OTP email: ' . $e->getMessage());
        }

        return view('auth.otp-verify', compact('action_type', 'intended_url', 'action_data', 'otpCode'));
    }

    /**
     * Verify the OTP and proceed with the action.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
            'action_type' => 'required|string',
            'intended_url' => 'required|string',
        ]);

        $user = Auth::user();
        $isValid = $this->otpService->verify($user, $request->otp_code, $request->action_type);

        if ($isValid) {
            // Mark as verified in session
            session(['otp_verified_at' => now()]);
            
            // Re-submit the action or redirect back with data
            $actionData = json_decode($request->action_data, true);
            
            if ($request->action_type === 'transfer') {
                return redirect($request->intended_url)->with([
                    'otp_verified' => true,
                    'action_data' => $actionData,
                ]);
            }

            return redirect($request->intended_url);
        }

        return back()->withErrors(['otp_code' => 'The provided verification code is incorrect or expired.']);
    }
}
