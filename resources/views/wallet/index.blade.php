<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Wallet') }}
        </h2>
    </x-slot>

    <style>
        .wallet-container { max-width: 800px; margin: 0 auto; padding: 2rem 1rem; }
        .wallet-card { background: #fff; border-radius: 0; padding: 2.5rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); margin-bottom: 2rem; border: 1px solid #e5e7eb; }
        .balance-section { text-align: center; margin-bottom: 2.5rem; border-bottom: 2px solid #f3f4f6; padding-bottom: 2rem; }
        .balance-label { color: #9ca3af; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 0.5rem; display: block; }
        .balance-value { color: #10b981; font-size: 4rem; font-weight: 900; letter-spacing: -0.05em; margin: 0.5rem 0; line-height: 1; }
        .wallet-actions { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; }
        @media (max-width: 640px) { .wallet-actions { grid-template-columns: 1fr; } }
        .wallet-btn { display: flex; align-items: center; justify-content: center; gap: 0.1rem; padding: 1.25rem; border-radius: 0; font-weight: 800; text-transform: uppercase; font-size: 0.875rem; transition: all 0.2s; text-decoration: none; border: 1px solid #e5e7eb; cursor: pointer; }
        .wallet-btn span { width: 180px; text-align: center; }
        .btn-action { background: #10b981; color: #000; border: none; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.1); }
        .btn-action:hover { background: #059669; transform: translate(-2px, -2px); box-shadow: 4px 4px 0px 0px rgba(0,0,0,0.1); }
        
        .activity-header { margin-bottom: 1.5rem; border-bottom: 2px solid #e5e7eb; padding-bottom: 1rem; }
        .activity-title { font-size: 1.25rem; font-weight: 900; color: #111827; text-transform: uppercase; letter-spacing: 0.05em; }
        .transaction-item { display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 0; border-bottom: 1px solid #f3f4f6; }
        .transaction-meta { display: flex; align-items: center; gap: 1rem; }
        .icon-box { width: 3rem; height: 3rem; border-radius: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #e5e7eb; }
        .icon-income { background: #ecfdf5; color: #10b981; }
        .icon-expense { background: #fef2f2; color: #ef4444; }
        .transaction-info p { margin: 0; }
        .desc { font-weight: 800; color: #111827; }
        .time { font-size: 0.75rem; color: #9ca3af; margin-top: 0.25rem; font-weight: 600; }
        .amount { font-weight: 900; font-size: 1.125rem; }
        .amount-income { color: #10b981; }
        .amount-expense { color: #ef4444; }

        /* Sync Styles */
        .sync-panel { background: #fef2f2; border: 2px solid #ef4444; padding: 1.5rem; margin-bottom: 2rem; display: none; align-items: center; justify-content: space-between; border-radius: 0; box-shadow: 4px 4px 0px 0px rgba(239, 68, 68, 0.1); }
        .sync-info { display: flex; align-items: center; gap: 1rem; color: #991b1b; }
        .sync-count { background: #ef4444; color: #fff; padding: 0.25rem 0.75rem; font-weight: 900; font-size: 0.875rem; }
        .sync-actions { display: flex; gap: 0.5rem; }
        .btn-sync { background: #10b981; color: #000; border: none; padding: 0.75rem 1.5rem; font-weight: 800; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; cursor: pointer; transition: all 0.2s; }
        .btn-sync:hover { background: #059669; transform: scale(1.02); }
        .btn-backup { background: #fff; color: #111827; border: 1px solid #e5e7eb; padding: 0.75rem 1rem; font-weight: 800; text-transform: uppercase; font-size: 0.75rem; cursor: pointer; }
    </style>

    <div class="wallet-container">
        <!-- Sync Panel -->
        <div id="sync-panel" class="sync-panel">
            <div class="sync-info">
                <div class="sync-count" id="sync-count">0</div>
                <div>
                    <p style="font-weight: 900; margin: 0; text-transform: uppercase;">{{ __('Pending Offline Scans') }}</p>
                    <p style="font-size: 0.75rem; margin: 0; font-weight: 600;">{{ __('These transfers will be processed once synced.') }}</p>
                </div>
            </div>
            <div class="sync-actions">
                <button onclick="copyBackupCodes()" class="btn-backup" title="Copy as fallback">
                    <svg style="width:1rem;height:1rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                </button>
                <button onclick="syncOfflineTransfers()" id="sync-btn" class="btn-sync">{{ __('Sync Now') }}</button>
            </div>
        </div>

        <!-- Balance Card -->
        <div class="wallet-card">
            <div class="balance-section">
                <span class="balance-label">{{ __('Account Balance') }}</span>
                <h3 class="balance-value">${{ number_format($wallet->balance, 2) }}</h3>
            </div>

            <div class="wallet-actions">
                <a href="{{ route('wallet.deposit') }}" class="wallet-btn btn-action">
                    <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <span>{{ __('Add Funds') }}</span>
                </a>
                <a href="{{ route('wallet.send') }}" class="wallet-btn btn-action">
                    <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    <span>{{ __('Send Money') }}</span>
                </a>
                <a href="{{ route('wallet.scan') }}" class="wallet-btn btn-action">
                    <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
                    <span>{{ __('Scan & Receive') }}</span>
                </a>
            </div>
        </div>

        <!-- Activity Section -->
        <div class="wallet-card">
            <div class="activity-header">
                <h3 class="activity-title">{{ __('Recent Activity') }}</h3>
            </div>
            
            <div class="activity-list">
                @forelse($transactions as $transaction)
                    <div class="transaction-item">
                        <div class="transaction-meta">
                            <div class="icon-box {{ $transaction->amount > 0 ? 'icon-income' : 'icon-expense' }}">
                                @if($transaction->amount > 0)
                                    <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                                @else
                                    <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                                @endif
                            </div>
                            <div class="transaction-info">
                                <p class="desc">{{ $transaction->description }}</p>
                                <p class="time">{{ $transaction->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="amount {{ $transaction->amount > 0 ? 'amount-income' : 'amount-expense' }}">
                            {{ $transaction->amount > 0 ? '+' : '' }}${{ number_format(abs($transaction->amount), 2) }}
                        </div>
                    </div>
                @empty
                    <div style="padding: 3rem 0; text-align: center;">
                        <svg style="width:3rem;height:3rem;color:#e5e7eb;margin-bottom:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p style="color:#9ca3af;font-weight:600;">{{ __('No transactions yet.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            checkPendingSyncs();
            
            // Auto sync if online
            if (navigator.onLine) {
                syncOfflineTransfers(true); // silent sync
            }
        });

        function checkPendingSyncs() {
            const queue = JSON.parse(localStorage.getItem('pending_transfers') || '[]');
            const panel = document.getElementById('sync-panel');
            const countLabel = document.getElementById('sync-count');
            
            if (queue.length > 0) {
                panel.style.display = 'flex';
                countLabel.innerText = queue.length;
                
                // Persistence indicator
                if (navigator.storage && navigator.storage.persist) {
                    navigator.storage.persist();
                }
            } else {
                panel.style.display = 'none';
            }
        }

        function copyBackupCodes() {
            const queue = JSON.parse(localStorage.getItem('pending_transfers') || '[]');
            if (queue.length === 0) return;
            
            const text = "UNSYNCED WALLET TOKENS:\n" + queue.join("\n");
            navigator.clipboard.writeText(text).then(() => {
                alert("Backup copied to clipboard! Save these codes somewhere safe.");
            });
        }

        async function syncOfflineTransfers(silent = false) {
            const queue = JSON.parse(localStorage.getItem('pending_transfers') || '[]');
            if (queue.length === 0) return;

            const btn = document.getElementById('sync-btn');
            const originalText = btn.innerText;
            
            if (!silent) {
                btn.disabled = true;
                btn.innerText = 'SYNCING...';
            }

            let successfulSyncs = [];
            
            for (const token of queue) {
                try {
                    const response = await fetch("{{ route('wallet.process-transfer') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ token: token })
                    });
                    
                    const data = await response.json();
                    if (data.success || data.message.includes('already used')) {
                        successfulSyncs.push(token);
                    } else if (data.message && data.message.includes('insufficient')) {
                        alert("Sync failed for one transfer: Sender has insufficient balance.");
                        successfulSyncs.push(token); // Remove from queue so we don't spam failed syncs
                    }
                } catch (e) {
                    console.error("Sync failed for token:", token, e);
                }
            }

            // Update queue - remove successful syncs
            const remaining = queue.filter(t => !successfulSyncs.includes(t));
            localStorage.setItem('pending_transfers', JSON.stringify(remaining));

            if (successfulSyncs.length > 0) {
                if (!silent) {
                    window.location.reload(); // Refresh to show new balance/activity
                } else if (remaining.length === 0) {
                    document.getElementById('sync-panel').style.display = 'none';
                }
            }

            if (!silent) {
                btn.disabled = false;
                btn.innerText = originalText;
                checkPendingSyncs();
            }
        }
    </script>
    @endpush
</x-app-layout>
