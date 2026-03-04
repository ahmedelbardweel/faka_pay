<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public Auth Routes
Route::post('/auth/register', [\App\Http\Controllers\Api\ApiAuthController::class, 'register']);
Route::post('/auth/login', [\App\Http\Controllers\Api\ApiAuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'account_status'])->group(function () {
    Route::post('/auth/logout', [\App\Http\Controllers\Api\ApiAuthController::class, 'logout']);
    
    Route::get('/user', function (Request $request) {
        return $request->user()->load('wallet');
    });

    // OTP & Notifications
    Route::post('/user/fcm-token', [\App\Http\Controllers\Api\OTPController::class, 'updateFcmToken']);
    Route::post('/otp/send', [\App\Http\Controllers\Api\OTPController::class, 'sendOtp']);
    Route::post('/otp/verify', [\App\Http\Controllers\Api\OTPController::class, 'verifyOtp']);

    // Wallet API
    Route::get('/wallet/data', [\App\Http\Controllers\Api\ApiWalletController::class, 'getWalletData']);
    Route::post('/wallet/topup', [\App\Http\Controllers\Api\ApiWalletController::class, 'topUp']);
    Route::post('/wallet/qr/create', [\App\Http\Controllers\Api\ApiWalletController::class, 'createTransfer']);
    Route::post('/wallet/qr/process', [\App\Http\Controllers\Api\ApiWalletController::class, 'processTransfer']);
    Route::post('/wallet/qr/sync', [\App\Http\Controllers\Api\ApiWalletController::class, 'syncTransfers']);
    Route::get('/wallet/qr/{token}/status', [\App\Http\Controllers\Api\ApiWalletController::class, 'checkStatus']);
});
