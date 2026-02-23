<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user) {
            $deviceToken = $request->cookie('device_lock');

            if ($user->device_token) {
                // Return generic error message to hide existence of lock logic to attackers if needed, 
                // but user requested "Device not authorized".
                if ($user->device_token !== $deviceToken) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'email' => __('هذا الجهاز غير مصرح به. يرجى الاتصال بالمسؤول.'),
                    ]);
                }
            }
        }

        $request->authenticate();

        $request->session()->regenerate();

        // If no token exists (First Login or Reset), generate and set it
        if ($user && is_null($user->device_token)) {
            $token = \Illuminate\Support\Str::uuid()->toString();
            $user->device_token = $token;
            $user->save();

            // Set cookie for 5 years (approx infinity for this use case)
            \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forever('device_lock', $token));
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
