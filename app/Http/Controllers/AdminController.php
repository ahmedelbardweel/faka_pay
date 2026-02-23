<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        // Only allow admins
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized');
        }

        $stats = [
            'total_balance' => \App\Models\Wallet::sum('balance'),
            'total_users' => \App\Models\User::count(),
            'total_transactions' => \App\Models\Transaction::count(),
            'pending_transfers' => \App\Models\QrTransfer::where('status', 'pending')->count(),
        ];

        $users = \App\Models\User::with('wallet')->get();
        $recentTransactions = \App\Models\Transaction::with('user')->latest()->take(10)->get();

        return view('admin.dashboard', compact('users', 'stats', 'recentTransactions'));
    }

    public function resetDevice(\App\Models\User $user)
    {
        // Only allow admins
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized');
        }

        $user->device_token = null;
        $user->save();

        return redirect()->back()->with('status', 'Device reset successfully for ' . $user->name);
    }
}
