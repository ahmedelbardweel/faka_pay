<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'id_number' => ['required', 'string', 'unique:'.User::class],
            'id_photo' => ['required', 'image', 'max:5120'],
            'personal_photo' => ['required', 'image', 'max:5120'],
        ]);

        // Handle KYC File Uploads
        $idPhotoPath = $request->file('id_photo')->store('kyc/ids', 'public');
        $personalPhotoPath = $request->file('personal_photo')->store('kyc/personal', 'public');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'id_number' => $request->id_number,
            'id_photo' => $idPhotoPath,
            'personal_photo' => $personalPhotoPath,
            'status' => 'pending',
        ]);

        event(new Registered($user));

        // Device Lock: Set initial token
        $token = \Illuminate\Support\Str::uuid()->toString();
        $user->device_token = $token;
        $user->save();
        
        // Initialize Wallet
        $user->wallet()->create(['balance' => 0]);

        \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forever('device_lock', $token));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
