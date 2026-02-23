<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Scan this QR Code') }}
        </h2>
    </x-slot>

    <style>
        .wallet-container { max-width: 500px; margin: 0 auto; padding: 2rem 1rem; }
        .wallet-card { background: #fff; border-radius: 0; padding: 3rem 2rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); text-align: center; border: 1px solid #e5e7eb; }
        .amount-badge { margin-bottom: 2.5rem; border-bottom: 2px solid #f3f4f6; padding-bottom: 1.5rem; }
        .amount-label { color: #10b981; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 0.5rem; display: block; }
        .amount-value { font-size: 3.5rem; font-weight: 900; color: #10b981; letter-spacing: -0.05em; margin: 0; line-height: 1; }
        .qr-wrapper { position: relative; display: inline-block; margin-bottom: 2.5rem; padding: 1.5rem; background: #fff; border-radius: 0; box-shadow: 5px 5px 0px 0px rgba(0,0,0,0.05); border: 1px solid #000; }
        .qr-decoration { position: absolute; inset: -0.5rem; background: #ecfdf5; border-radius: 0; z-index: -1; border: 1px solid #d1fae5; }
        .cancel-btn { display: inline-flex; align-items: center; gap: 0.5rem; color: #111827; font-weight: 800; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; text-decoration: none; transition: all 0.2s; border: 1px solid #e5e7eb; padding: 0.75rem 1.5rem; border-radius: 0; }
        .cancel-btn:hover { background: #ef4444; color: #fff; border-color: #ef4444; }
        .back-btn { display: flex; align-items: center; justify-content: center; gap: 0.1rem; padding: 1.25rem; border-radius: 0; font-weight: 800; text-transform: uppercase; font-size: 0.875rem; transition: all 0.3s; text-decoration: none; border: none; cursor: pointer; }
        .back-btn span { width: 180px; text-align: center; }
        .back-btn:hover { transform: translate(-2px, -2px); box-shadow: 4px 4px 0px 0px rgba(0,0,0,0.1); }
    </style>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="wallet-container">
            <div class="wallet-card">
                <div class="amount-badge">
                    <span class="amount-label">{{ __('Payment Amount') }}</span>
                    <h3 class="amount-value">${{ number_format($transfer->amount, 2) }}</h3>
                </div>

                <div class="qr-wrapper" id="qr-code-container">
                    <div class="qr-decoration"></div>
                    <div id="qr-svg-wrapper">
                        {!! QrCode::size(240)->errorCorrection('H')->margin(1)->generate($transfer->token) !!}
                    </div>
                </div>

                <div style="margin-bottom: 2rem;">
                    <span class="amount-label" style="font-size: 0.65rem; margin-bottom: 0.25rem;">{{ __('Manual Entry Code') }}</span>
                    <div style="font-family: monospace; font-size: 1.15rem; font-weight: 900; background: #f9fafb; padding: 0.75rem; border: 1px dashed #ced4da; color: #111827; letter-spacing: 0.1em; word-break: break-all;">
                        {{ strtoupper($transfer->token) }}
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <button onclick="downloadQR()" class="back-btn" style="width: 100%; background: #10b981; color: #000; border: none; cursor: pointer;">
                        <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>{{ __('Download QR') }}</span>
                    </button>

                    <a href="{{ route('wallet.index') }}" class="back-btn" style="width: 100%; background: #fff; color: #111827; border: 1px solid #e5e7eb; text-decoration: none;">
                        <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        <span>{{ __('Back to Wallet') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function downloadQR() {
            const svg = document.querySelector('#qr-svg-wrapper svg');
            const svgData = new XMLSerializer().serializeToString(svg);
            const canvas = document.createElement("canvas");
            const ctx = canvas.getContext("2d");
            const img = new Image();
            
            img.onload = function() {
                canvas.width = img.width * 2;
                canvas.height = img.height * 2;
                ctx.fillStyle = "white";
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                
                const pngFile = canvas.toDataURL("image/png");
                const downloadLink = document.createElement("a");
                downloadLink.download = "Wallet-QR-${{ $transfer->amount }}.png";
                downloadLink.href = pngFile;
                downloadLink.click();
            };
            
            img.src = "data:image/svg+xml;base64," + btoa(svgData);
        }

        // Poll for transfer status every 3 seconds
        const pollStatus = setInterval(() => {
            fetch("{{ route('wallet.transfer-status', $transfer->token) }}")
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'completed') {
                        clearInterval(pollStatus);
                        window.location.href = "{{ route('wallet.success') }}";
                    }
                })
                .catch(error => console.error('Status check error:', error));
        }, 3000);
    </script>
</x-app-layout>
