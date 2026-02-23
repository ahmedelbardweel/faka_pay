<x-app-layout>
    <style>
        .admin-container { background: #f9fafb; min-height: 100vh; padding: 2rem 1rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: #000; color: #fff; padding: 1.5rem; border: none; box-shadow: 4px 4px 0px 0px #10b981; }
        .stat-label { font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #9ca3af; margin-bottom: 0.5rem; }
        .stat-value { font-size: 1.875rem; font-weight: 900; color: #10b981; }
        
        .admin-card { background: #fff; border: 1px solid #e5e7eb; margin-bottom: 2rem; }
        .card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .card-title { font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.875rem; }
        
        table { width: 100%; border-collapse: collapse; }
        th { background: #f9fafb; padding: 0.75rem 1.5rem; text-align: left; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        td { padding: 1rem 1.5rem; font-size: 0.875rem; border-bottom: 1px solid #f3f4f6; }
        
        .badge { padding: 0.25rem 0.75rem; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        
        .btn-reset { color: #ef4444; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; background: none; border: none; cursor: pointer; }
        .btn-reset:hover { text-decoration: underline; }
    </style>

    <div class="admin-container">
        <div class="max-w-7xl mx-auto">
            
            <!-- Stats Section -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">System Liquidity</div>
                    <div class="stat-value">{{ number_format($stats['total_balance'], 2) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Active Users</div>
                    <div class="stat-value">{{ $stats['total_users'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Transactions</div>
                    <div class="stat-value">{{ $stats['total_transactions'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Pending Transfers</div>
                    <div class="stat-value">{{ $stats['pending_transfers'] }}</div>
                </div>
            </div>

            @if (session('status'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 font-bold uppercase text-xs">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- User Management -->
                <div class="lg:col-span-2">
                    <div class="admin-card">
                        <div class="card-header">
                            <h3 class="card-title">User Network</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table>
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Balance</th>
                                        <th>Device Lock</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <td>
                                                <div class="font-bold text-gray-900">{{ $user->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                            </td>
                                            <td class="font-mono font-bold text-emerald-600">
                                                {{ number_format($user->wallet->balance ?? 0, 2) }}
                                            </td>
                                            <td>
                                                @if ($user->device_token)
                                                    <span class="badge badge-red">Locked</span>
                                                @else
                                                    <span class="badge badge-green">Open</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($user->device_token)
                                                    <form method="POST" action="{{ route('admin.device.reset', $user->id) }}">
                                                        @csrf
                                                        <button type="submit" class="btn-reset">Reset Lock</button>
                                                    </form>
                                                @else
                                                    <span class="text-gray-300 text-xs font-bold uppercase">No Action</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions Feed -->
                <div class="lg:col-span-1">
                    <div class="admin-card">
                        <div class="card-header">
                            <h3 class="card-title">Live Feed</h3>
                        </div>
                        <div class="p-0">
                            @foreach ($recentTransactions as $tx)
                                <div class="p-4 border-bottom border-gray-100 last:border-0">
                                    <div class="flex justify-between items-start mb-1">
                                        <span class="text-[0.65rem] font-black uppercase tracking-tighter text-gray-400">{{ $tx->type }}</span>
                                        <span class="text-xs font-bold {{ $tx->amount > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                            {{ $tx->amount > 0 ? '+' : '' }}{{ number_format($tx->amount, 2) }}
                                        </span>
                                    </div>
                                    <div class="text-xs font-medium text-gray-800">{{ $tx->description }}</div>
                                    <div class="text-[10px] text-gray-400 mt-1">{{ $tx->created_at->diffForHumans() }}</div>
                                </div>
                            @endforeach
                            @if($recentTransactions->isEmpty())
                                <div class="p-8 text-center text-gray-400 text-xs font-bold uppercase">No Transactions Yet</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
