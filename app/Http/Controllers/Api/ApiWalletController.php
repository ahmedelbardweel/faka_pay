<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QrTransfer;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApiWalletController extends Controller
{
    /**
     * Get wallet balance and recent transactions.
     */
    public function getWalletData()
    {
        $user = Auth::user();
        $wallet = $user->wallet ?: $user->wallet()->create(['balance' => 0]);
        $transactions = $user->transactions()->latest()->take(20)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'iban' => $wallet->iban,
                'balance' => $wallet->balance,
                'transactions' => $transactions
            ]
        ]);
    }

    /**
     * Create a new QR transfer token.
     */
    public function createTransfer(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet || $wallet->balance < $request->amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance'
            ], 400);
        }

        $token = Str::random(32);
        
        $transfer = QrTransfer::create([
            'sender_id' => $user->id,
            'amount' => $request->amount,
            'remaining_amount' => $request->amount,
            'token' => $token,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'amount' => $request->amount,
                'status' => 'pending'
            ]
        ]);
    }

    /**
     * Process a scanned QR token (Online/Sync).
     */
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
            return response()->json(['success' => false, 'message' => 'QR code is empty or already used.'], 422);
        }

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
            return response()->json(['success' => false, 'message' => 'Amount is higher than QR balance! (Available: ' . $transfer->remaining_amount . ')'], 422);
        }

        $receiverWallet = $receiver->wallet ?: $receiver->wallet()->create(['balance' => 0]);

        if ($senderWallet->balance < $deductionAmount) {
            return response()->json(['success' => false, 'message' => 'Sender has insufficient wallet balance.']);
        }

        DB::transaction(function () use ($sender, $receiver, $senderWallet, $receiverWallet, $transfer, $deductionAmount) {
            // Deduct from sender
            $senderWallet->decrement('balance', $deductionAmount);
            Transaction::create([
                'user_id' => $sender->id,
                'amount' => -$deductionAmount,
                'type' => 'transfer_send',
                'description' => 'Sent to ' . $receiver->name . ' (API Partial)',
                'reference_id' => $receiver->id,
            ]);

            // Add to receiver
            $receiverWallet->increment('balance', $deductionAmount);
            Transaction::create([
                'user_id' => $receiver->id,
                'amount' => $deductionAmount,
                'type' => 'transfer_receive',
                'description' => 'Received from ' . $sender->name . ' (API Partial)',
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

        return response()->json([
            'success' => true, 
            'message' => 'Transfer processed successfully!',
            'data' => [
                'deducted' => $deductionAmount,
                'remaining' => $transfer->fresh()->remaining_amount,
                'status' => $transfer->fresh()->status,
                'new_balance' => $receiverWallet->fresh()->balance
            ]
        ]);
    }

    /**
     * Batch process multiple tokens (Offline Sync).
     */
    public function syncTransfers(Request $request)
    {
        $request->validate([
            'tokens' => 'required|array',
            // tokens can be string (token only) or array ['token' => '...', 'amount' => 5]
        ]);

        $receiver = Auth::user();
        $results = [];
        $tokens = $request->tokens;
        $processedInBatch = [];

        foreach ($tokens as $tokenData) {
            $token = is_array($tokenData) ? ($tokenData['token'] ?? null) : $tokenData;
            $requestAmount = is_array($tokenData) ? ($tokenData['amount'] ?? null) : null;

            if (!$token) continue;

            // Deduplicate tokens within the same request batch
            if (in_array($token, $processedInBatch)) {
                continue;
            }
            $processedInBatch[] = $token;

            // Lock the transfer record to prevent concurrent syncs from multiple requests/threads
            $transfer = QrTransfer::where('token', $token)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$transfer) {
                $results[] = [
                    'token' => $token,
                    'status' => 'failed',
                    'message' => 'Invalid or already used.'
                ];
                continue;
            }

            if ($transfer->sender_id === $receiver->id) {
                $results[] = [
                    'token' => $token,
                    'status' => 'failed',
                    'message' => 'Cannot scan own QR.'
                ];
                continue;
            }

            $deductionAmount = $requestAmount ?? $transfer->remaining_amount;

            if ($deductionAmount > $transfer->remaining_amount) {
                $results[] = [
                    'token' => $token,
                    'status' => 'failed',
                    'message' => 'Amount exceeds QR balance.'
                ];
                continue;
            }

            $sender = User::find($transfer->sender_id);
            $senderWallet = $sender->wallet;
            
            if (!$senderWallet || $senderWallet->balance < $deductionAmount) {
                $results[] = [
                    'token' => $token,
                    'status' => 'failed',
                    'message' => 'Insufficient sender balance.'
                ];
                continue;
            }

            $receiverWallet = $receiver->wallet ?: $receiver->wallet()->create(['balance' => 0]);

            try {
                DB::transaction(function () use ($sender, $receiver, $senderWallet, $receiverWallet, $transfer, $deductionAmount) {
                    $senderWallet->decrement('balance', $deductionAmount);
                    Transaction::create([
                        'user_id' => $sender->id,
                        'amount' => -$deductionAmount,
                        'type' => 'transfer_send',
                        'description' => 'Sent to ' . $receiver->name . ' (Batch Partial)',
                        'reference_id' => $receiver->id,
                    ]);

                    $receiverWallet->increment('balance', $deductionAmount);
                    Transaction::create([
                        'user_id' => $receiver->id,
                        'amount' => $deductionAmount,
                        'type' => 'transfer_receive',
                        'description' => 'Received from ' . $sender->name . ' (Batch Partial)',
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

                $results[] = [
                    'token' => $token,
                    'status' => 'success',
                    'message' => 'Transfer completed.'
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'token' => $token,
                    'status' => 'failed',
                    'message' => 'System error.'
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'results' => $results,
                'new_balance' => $receiver->wallet->fresh() ? $receiver->wallet->balance : 0
            ]
        ]);
    }

    /**
     * Top up user wallet (Testing only).
     */
    public function topUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        $wallet = $user->wallet ?: $user->wallet()->create(['balance' => 0]);
        
        $wallet->increment('balance', $request->amount);

        Transaction::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'type' => 'topup',
            'description' => 'Test Top-up via API',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Top-up successful',
            'data' => [
                'new_balance' => $wallet->fresh()->balance
            ]
        ]);
    }

    /**
     * Check status of a transfer.
     */
    public function checkStatus($token)
    {
        $transfer = QrTransfer::where('token', $token)->first();
        
        if (!$transfer) {
            return response()->json(['success' => false, 'message' => 'Transfer not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $transfer->status,
                'amount' => $transfer->amount,
                'remaining_amount' => $transfer->remaining_amount,
                'sender_name' => $transfer->sender->name
            ]
        ]);
    }
}
