<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payment Successful') }}
        </h2>
    </x-slot>

    <style>
        .success-container { max-width: 500px; margin: 0 auto; padding: 4rem 1rem; text-align: center; }
        .success-card { background: #fff; border: 1px solid #e5e7eb; padding: 4rem 2rem; box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.1); position: relative; }
        .success-icon { width: 5rem; height: 5rem; background: #fff; color: #10b981; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; border: 1px solid #10b981; }
        .success-title { font-size: 2rem; font-weight: 900; color: #111827; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem; }
        .success-text { color: #6b7280; font-size: 1rem; font-weight: 600; margin-bottom: 3rem; }
        .btn-home { display: flex; align-items: center; justify-content: center; gap: 0.1rem; background: #10b981; color: #000; padding: 1.25rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.15em; text-decoration: none; transition: all 0.2s; border: none; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.1); }
        .btn-home span { width: 180px; text-align: center; }
        .btn-home:hover { background: #059669; transform: translate(-2px, -2px); box-shadow: 4px 4px 0px 0px rgba(0,0,0,0.1); }
        .decorative-line { position: absolute; top: 0; left: 0; width: 100%; height: 8px; background: #10b981; }
    </style>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="success-container">
            <div class="success-card">
                <div class="decorative-line"></div>
                <div class="success-icon">
                    <svg style="width:3rem;height:3rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="success-title">{{ __('Payment Done') }}</h3>
                <p class="success-text">{{ __('The driver has successfully received the funds. Your balance has been updated.') }}</p>
                
                <a href="{{ route('wallet.index') }}" class="btn-home">
                    <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span>{{ __('Back to Wallet') }}</span>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
