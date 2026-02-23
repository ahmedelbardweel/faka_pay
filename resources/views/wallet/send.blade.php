<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Send Money') }}
        </h2>
    </x-slot>

    <style>
        .wallet-container { max-width: 500px; margin: 0 auto; padding: 2rem 1rem; }
        .wallet-card { background: #fff; border-radius: 0; padding: 2.5rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); margin-bottom: 2rem; border: 1px solid #e5e7eb; text-align: center; }
        .icon-box { width: 4rem; height: 4rem; background: #fff; border-radius: 0; border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #111827; }
        .card-title { font-size: 1.5rem; font-weight: 900; color: #111827; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .card-desc { color: #6b7280; font-size: 0.875rem; margin-bottom: 2rem; font-weight: 600; }
        .input-group { position: relative; margin-bottom: 2rem; }
        .currency-symbol { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #111827; font-size: 1.5rem; font-weight: 900; pointer-events: none; }
        .amount-input { width: 100%; padding: 1.25rem 1rem 1.25rem 3rem; font-size: 2rem; font-weight: 900; border: 1px solid #e5e7eb; border-radius: 0; background: #fff; transition: all 0.2s; color: #111827; box-sizing: border-box; }
        .amount-input:focus { outline: none; border-color: #4f46e5; box-shadow: 4px 4px 0px 0px rgba(79, 70, 229, 0.2); }
        .btn-submit { width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.1rem; background: #10b981; color: #000; padding: 1.25rem; border-radius: 0; font-weight: 800; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.1em; border: none; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.1); }
        .btn-submit span { width: 180px; text-align: center; }
        .btn-submit:hover { background: #059669; transform: translate(-2px, -2px); box-shadow: 4px 4px 0px 0px rgba(0,0,0,0.1); }
        .error-msg { color: #ef4444; font-size: 0.875rem; margin-top: 0.75rem; font-weight: 800; text-transform: uppercase; }
        .cancel-link { display: inline-block; margin-top: 1.5rem; color: #111827; font-weight: 800; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; text-decoration: none; transition: color 0.2s; border: 1px solid #e5e7eb; padding: 0.5rem 1rem; }
        .cancel-link:hover { background: #ef4444; color: #fff; border-color: #ef4444; }
    </style>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="wallet-container">
            <div class="wallet-card">
                <div class="icon-box">
                    <svg style="width:2rem;height:2rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </div>
                <h3 class="card-title">{{ __('Send Funds') }}</h3>
                <p class="card-desc">{{ __('Enter the amount you wish to transfer via QR code') }}</p>

                <form action="{{ route('wallet.generate-qr') }}" method="POST">
                    @csrf
                    
                    <div class="input-group">
                        <span class="currency-symbol">$</span>
                        <input type="number" name="amount" id="amount" step="0.01" min="1" 
                               class="amount-input" 
                               placeholder="0.00" required autofocus>
                        @error('amount')
                            <p class="error-msg">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn-submit">
                        <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        <span>{{ __('Create Payment QR') }}</span>
                    </button>
                    
                    <a href="{{ route('wallet.index') }}" class="cancel-link">
                        {{ __('Cancel') }}
                    </a>
                </form>

                @if(session('error'))
                    <div style="margin-top: 1.5rem; padding: 1rem; background: #fef2f2; border: 1px solid #fee2e2; border-radius: 1rem; color: #b91c1c; font-weight: 700; font-size: 0.875rem;">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
