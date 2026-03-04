<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class ApiAuthController extends Controller
{
    /**
     * Handle API Registration.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'device_id' => ['nullable', 'string'],
            'id_number' => ['required', 'string', 'unique:'.User::class],
            'id_photo' => ['required', 'image', 'max:5120'], // 5MB limit
            'personal_photo' => ['required', 'image', 'max:5120'],
        ]);

        // Prevent multiple accounts per device
        if ($request->device_id && User::where('device_token', $request->device_id)->exists()) {
             return response()->json([
                'success' => false,
                'message' => 'This device is already associated with another account.'
            ], 403);
        }

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

        // Initialize Wallet
        $user->wallet()->create(['balance' => 0]);

        // Device Lock Logic
        $deviceToken = $request->device_id ?: \Illuminate\Support\Str::uuid()->toString();
        $user->device_token = $deviceToken;
        $user->save();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'token' => $token,
                'user' => $user->load('wallet'),
                'device_token' => $deviceToken
            ]
        ], 201);
    }

    /**
     * Handle API Login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'device_id' => ['nullable', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Check for Admin Approval
        if ($user->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is pending admin approval. Status: ' . $user->status
            ], 403);
        }

        // Handle Device Locking
        if ($user->device_token && $request->device_id && $user->device_token !== $request->device_id) {
             return response()->json([
                'success' => false,
                'message' => 'This account is locked to another device.'
            ], 403);
        }

        // If no device token, lock to this one
        if (!$user->device_token && $request->device_id) {
            $user->device_token = $request->device_id;
            $user->save();
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => $user->load('wallet')
            ]
        ]);
    }

    /**
     * Handle API Logout.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
