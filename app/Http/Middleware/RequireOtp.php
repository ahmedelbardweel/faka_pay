<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireOtp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user has verified OTP in the last 15 minutes
        $lastVerified = session('otp_verified_at');
        
        if (!$lastVerified || now()->diffInMinutes($lastVerified) > 15) {
            
            // If it's a POST request (sensitive action), save the data to session
            if ($request->isMethod('post')) {
                session(['otp_action_data' => $request->all()]);
            }

            return redirect()->route('otp.verify.web', [
                'action_type' => 'transfer', // default or dynamic
                'intended_url' => $request->fullUrl(),
            ]);
        }

        return $next($request);
    }
}
