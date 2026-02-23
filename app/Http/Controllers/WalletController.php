<?php

namespace App\Http\Controllers;

use App\Models\QrTransfer;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WalletController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $wallet = $user->wallet ?: $user->wallet()->create(['balance' => 0]);
        $transactions = $user->transactions()->latest()->take(10)->get();

        return view('wallet.index', compact('wallet', 'transactions'));
    }

    public function showDepositForm()
    {
        return view('wallet.deposit');
    }

    public function processDeposit(Request $request)
    {
        // If coming back from OTP, data is in session
        $data = $request->isMethod('get') && session()->has('otp_action_data') 
                ? session()->pull('otp_action_data') 
                : $request->all();

        // Manual validation if data is from session
        if ($request->isMethod('get')) {
            if (!isset($data['amount']) || $data['amount'] < 1) {
                return redirect()->route('wallet.deposit')->with('error', 'Invalid amount.');
            }
        } else {
            $request->validate([
                'amount' => 'required|numeric|min:1',
            ]);
        }

        $amount = $data['amount'];
        $user = Auth::user();
        $wallet = $user->wallet ?: $user->wallet()->create(['balance' => 0]);

        DB::transaction(function () use ($user, $wallet, $amount) {
            $wallet->increment('balance', $amount);

            Transaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => 'deposit',
                'description' => 'Wallet Top-up',
                'reference_id' => null,
            ]);
        });

        return redirect()->route('wallet.index')->with('success', 'Funds added successfully!');
    }

    public function createQr()
    {
        return view('wallet.send');
    }

    public function generateQr(Request $request)
    {
        // If coming back from OTP, data is in session
        $data = $request->isMethod('get') && session()->has('otp_action_data') 
                ? session()->pull('otp_action_data') 
                : $request->all();

        // Manual validation if data is from session
        if ($request->isMethod('get')) {
            if (!isset($data['amount']) || $data['amount'] < 1) {
                return redirect()->route('wallet.send')->with('error', 'Invalid amount.');
            }
        } else {
            $request->validate([
                'amount' => 'required|numeric|min:1',
            ]);
        }

        $amount = $data['amount'];
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet || $wallet->balance < $amount) {
            return back()->with('error', 'Insufficient balance');
        }

        $token = Str::random(32);
        
        $transfer = QrTransfer::create([
            'sender_id' => $user->id,
            'amount' => $request->amount,
            'remaining_amount' => $request->amount,
            'token' => $token,
            'status' => 'pending',
            'expires_at' => null, // Permanent QR
        ]);

        return view('wallet.qr', compact('transfer'));
    }

    public function scan()
    {
        return view('wallet.scan');
    }

    public function processTransfer(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'amount' => 'nullable|numeric|min:0.01',
        ]);

        $receiver = Auth::user();
        $transfer = QrTransfer::where('token', $request->token)
            ->where('status', 'pending')
            ->first();

        if (!$transfer) {
            return response()->json(['success' => false, 'message' => 'Invalid or already used QR code.']);
        }

        // Expiration check removed as per user request

        if ($transfer->sender_id === $receiver->id) {
            return response()->json(['success' => false, 'message' => 'You cannot scan your own QR code.']);
        }

        $sender = User::find($transfer->sender_id);
        $senderWallet = $sender->wallet;
        
        if (!$senderWallet) {
             return response()->json(['success' => false, 'message' => 'Sender wallet not found.']);
        }

        $deductionAmount = $request->amount ?? $transfer->remaining_amount;

        if ($deductionAmount > $transfer->remaining_amount) {
            return response()->json(['success' => false, 'message' => 'Requested amount exceeds QR balance.']);
        }

        $receiverWallet = $receiver->wallet ?: $receiver->wallet()->create(['balance' => 0]);

        if ($senderWallet->balance < $deductionAmount) {
            return response()->json(['success' => false, 'message' => 'Sender has insufficient balance.']);
        }

        DB::transaction(function () use ($sender, $receiver, $senderWallet, $receiverWallet, $transfer, $deductionAmount) {
            // Deduct from sender
            $senderWallet->decrement('balance', $deductionAmount);
            Transaction::create([
                'user_id' => $sender->id,
                'amount' => -$deductionAmount,
                'type' => 'transfer_send',
                'description' => 'Sent to ' . $receiver->name . ' (Partial)',
                'reference_id' => $receiver->id,
            ]);

            // Add to receiver
            $receiverWallet->increment('balance', $deductionAmount);
            Transaction::create([
                'user_id' => $receiver->id,
                'amount' => $deductionAmount,
                'type' => 'transfer_receive',
                'description' => 'Received from ' . $sender->name . ' (Partial)',
                'reference_id' => $sender->id,
            ]);

            $newRemaining = $transfer->remaining_amount - $deductionAmount;
            $updateData = [
                'remaining_amount' => $newRemaining,
            ];

            if ($newRemaining <= 0) {
                $updateData['status'] = 'completed';
                $updateData['receiver_id'] = $receiver->id;
            }

            $transfer->update($updateData);
        });

        return response()->json(['success' => true, 'message' => 'Transfer processed successfully!', 'remaining' => $transfer->fresh()->remaining_amount]);
    }

    public function checkTransferStatus($token)
    {
        $transfer = QrTransfer::where('token', $token)->first();
        
        if (!$transfer) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'status' => $transfer->status,
            'amount' => $transfer->amount,
            'remaining' => $transfer->remaining_amount,
            'message' => $transfer->status === 'completed' ? 'Success' : 'Pending'
        ]);
    }
}
