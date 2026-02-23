<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // OTP Routes
    Route::get('/otp/verify', [\App\Http\Controllers\OtpWebController::class, 'showVerifyForm'])->name('otp.verify.web');
    Route::post('/otp/verify', [\App\Http\Controllers\OtpWebController::class, 'verify'])->name('otp.verify.web.process');

    // Wallet Routes
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/deposit', [WalletController::class, 'showDepositForm'])->name('wallet.deposit');
    Route::match(['get', 'post'], '/wallet/deposit/process', [WalletController::class, 'processDeposit'])->name('wallet.deposit.process')->middleware('otp.require');
    Route::get('/wallet/success', function() { return view('wallet.success'); })->name('wallet.success');
    Route::get('/wallet/send', [WalletController::class, 'createQr'])->name('wallet.send');
    Route::match(['get', 'post'], '/wallet/generate-qr', [WalletController::class, 'generateQr'])->name('wallet.generate-qr')->middleware('otp.require');
    Route::get('/wallet/qr/{token}/status', [WalletController::class, 'checkTransferStatus'])->name('wallet.transfer-status');
    Route::get('/wallet/scan', [WalletController::class, 'scan'])->name('wallet.scan');
    Route::post('/wallet/process-transfer', [WalletController::class, 'processTransfer'])->name('wallet.process-transfer');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Routes
    Route::get('/admin/dashboard', [\App\Http\Controllers\AdminController::class, 'index'])->name('admin.dashboard');
    Route::post('/admin/users/{user}/reset', [\App\Http\Controllers\AdminController::class, 'resetDevice'])->name('admin.device.reset');
});

require __DIR__.'/auth.php';
