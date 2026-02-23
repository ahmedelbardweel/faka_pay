<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Scan Money QR Code') }}
        </h2>
    </x-slot>

    <style>
        :root { --card-padding: 2.5rem; }
        .wallet-container { max-width: 600px; margin: 0 auto; padding: 2rem 1rem; }
        .wallet-card { background: #fff; border-radius: 0; padding: var(--card-padding); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); border: 1px solid #e5e7eb; }
        @media (max-width: 640px) { 
            :root { --card-padding: 1.25rem; }
            .wallet-card { padding: 1.5rem 1rem; }
            .message-text { font-size: 1.125rem; }
            .back-btn span { width: auto !important; min-width: 140px; }
        }
        .scan-header { text-align: center; margin-bottom: 2rem; }
        .icon-box { width: 4rem; height: 4rem; background: #fff; border-radius: 0; border: 1px solid #059669; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #059669; }
        .card-title { font-size: 1.5rem; font-weight: 900; color: #111827; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .card-desc { color: #6b7280; font-size: 0.875rem; font-weight: 600; }
        
        .scanner-wrapper { position: relative; border-radius: 0; overflow: hidden; background: #111827; border: 1px solid #e5e7eb; min-height: 300px; }
        #reader { width: 100% !important; border: none !important; }
        #scanner-overlay { position: absolute; inset: 0; border: 40px solid rgba(0,0,0,0.3); border-radius: 0; pointer-events: none; z-index: 10; }
        .overlay-pulse { position: absolute; inset: 0; border: 3px solid rgba(52, 211, 153, 0.8); border-radius: 0; animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
        
        #result { text-align: center; padding: 2.5rem; border-radius: 0; border: 1px solid #e5e7eb; }
        .result-icon { width: 4rem; height: 4rem; border-radius: 0; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; border: 1px solid currentColor; }
        .message-text { font-size: 1.25rem; font-weight: 900; margin-bottom: 1.5rem; text-transform: uppercase; }
        .back-btn { display: inline-block; background: #111827; color: #000; padding: 1.25rem 2.5rem; border-radius: 0; font-weight: 800; text-decoration: none; transition: all 0.2s; border: none; cursor: pointer; text-transform: uppercase; }
        .back-btn:hover { background: #059669; transform: translate(-2px, -2px); box-shadow: 4px 4px 0px 0px rgba(0,0,0,0.1); }
        .status-footer { text-align: center; margin-top: 1.5rem; color: #111827; font-weight: 900; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; word-break: break-word; line-height: 1.5; }
        .manual-entry-divider { display: flex; align-items: center; margin: 2rem 0; color: #9ca3af; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; }
        .manual-entry-divider::before, .manual-entry-divider::after { content: ""; flex: 1; height: 1px; background: #e5e7eb; }
        .manual-entry-divider span { padding: 0 1rem; }
        
        .manual-input { width: 100%; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 0; font-family: monospace; font-size: 0.875rem; margin-bottom: 1rem; text-align: center; text-transform: uppercase; letter-spacing: 0.05em; }
        .manual-input:focus { outline: none; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }
        
        .loading-overlay { position: absolute; inset: 0; background: rgba(255,255,255,0.9); z-index: 50; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .spinner { width: 2.5rem; height: 2.5rem; border: 4px solid #f3f4f6; border-top-color: #10b981; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 1rem; }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        .hidden { display: none !important; }

        /* Offline Styles */
        .offline-mode .icon-box { border-color: #f59e0b; color: #f59e0b; }
        .offline-mode .card-title { color: #f59e0b; }
        .offline-mode .spinner { border-top-color: #f59e0b; }
        .offline-mode .back-btn { background: #f59e0b !important; }
        .offline-badge { display: none; background: #fef3c7; color: #92400e; padding: 0.5rem 1rem; font-weight: 800; font-size: 0.75rem; border: 1px solid #fcd34d; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 0.1em; }
        .offline-mode .offline-badge { display: block; }
    </style>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="wallet-container">
            <div class="wallet-card">
                <div class="scan-header">
                    <div class="icon-box">
                        <svg style="width:2rem;height:2rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
                    </div>
                    <div class="offline-badge">
                        <span class="flex items-center justify-center gap-2">
                            <svg style="width:1rem;height:1rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-3.536 5 5 0 015-5M8.464 8.464L3 3m5.464 5.464L7.05 7.05"></path></svg>
                            {{ __('Offline Mode - Capturing locally') }}
                        </span>
                    </div>
                    <h3 class="card-title">{{ __('Scan to Receive') }}</h3>
                    <p class="card-desc">{{ __('Use your camera or upload a QR image from gallery') }}</p>
                </div>

                <div id="scanner-container" class="relative">
                    <div id="loading-overlay" class="loading-overlay hidden">
                        <div class="spinner"></div>
                        <p class="card-desc">{{ __('Processing Image...') }}</p>
                    </div>

                    <div class="scanner-wrapper">
                        <div id="reader"></div>
                        <div id="scanner-overlay">
                            <div class="overlay-pulse"></div>
                            <!-- Visual Guide Corners -->
                            <div style="position:absolute;top:20px;left:20px;width:30px;height:30px;border-top:4px solid #fff;border-left:4px solid #fff;"></div>
                            <div style="position:absolute;top:20px;right:20px;width:30px;height:30px;border-top:4px solid #fff;border-right:4px solid #fff;"></div>
                            <div style="position:absolute;bottom:20px;left:20px;width:30px;height:30px;border-bottom:4px solid #fff;border-left:4px solid #fff;"></div>
                            <div style="position:absolute;bottom:20px;right:20px;width:30px;height:30px;border-bottom:4px solid #fff;border-right:4px solid #fff;"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex gap-4 w-full" id="upload-section">
                    <div class="flex-1">
                        <input type="file" id="qr-input-camera" accept="image/*" capture="environment" class="hidden">
                        <button onclick="document.getElementById('qr-input-camera').click()" class="wallet-btn" style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #111827; color: #fff; padding: 1.5rem 0.5rem; border: none;">
                            <svg style="width:2rem;height:2rem;margin-bottom:0.75rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
                            <span style="font-size: 0.875rem; font-weight: 800;">{{ __('Take Photo') }}</span>
                        </button>
                    </div>
                    <div class="flex-1">
                        <input type="file" id="qr-input-gallery" accept="image/*" class="hidden">
                        <button onclick="document.getElementById('qr-input-gallery').click()" class="wallet-btn" style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #fff; color: #111827; padding: 1.5rem 0.5rem; border: 1px solid #e5e7eb;">
                            <svg style="width:2rem;height:2rem;margin-bottom:0.75rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span style="font-size: 0.875rem; font-weight: 800;">{{ __('From Gallery') }}</span>
                        </button>
                    </div>
                </div>

                <div id="manual-section" class="mt-8 p-6 bg-emerald-50 border-y-2 border-emerald-100" style="margin-left: calc(-1 * var(--card-padding)); margin-right: calc(-1 * var(--card-padding)); padding-left: var(--card-padding); padding-right: var(--card-padding);">
                    <div class="manual-entry-divider" style="margin-top:0">
                        <span style="background:#ecfdf5">{{ __('Reliable Fallback') }}</span>
                    </div>
                    <p class="card-desc" style="font-size: 0.75rem; margin-bottom: 1rem; color: #059669; font-weight: 700;">
                        {{ __('If camera fails, enter the 32-character code shown on the other phone:') }}
                    </p>
                    <input type="text" id="manual-token" class="manual-input" style="border-color: #a7f3d0" placeholder="PASTE OR TYPE CODE HERE">
                    <button onclick="processToken(document.getElementById('manual-token').value)" class="back-btn" style="width: 100%; background: #10b981; color: #000; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2); display: flex; align-items: center; justify-content: center; gap: 0.1rem;">
                        <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span style="width: 220px; text-align: center;">{{ __('CONFIRM') }}</span>
                    </button>
                </div>
                
                <div id="result" class="hidden">
                    <div id="result-icon" class="result-icon"></div>
                    <p id="result-message" class="message-text"></p>
                    <a href="{{ route('wallet.index') }}" class="back-btn" style="width: 100%; background: #10b981; color: #000; display: flex; align-items: center; justify-content: center; gap: 0.1rem;">
                        <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        <span style="width: 220px; text-align: center;">{{ __('Back to Wallet') }}</span>
                    </a>
                </div>

                <div id="instruction-tips" class="mt-4 p-4 bg-emerald-50 border border-emerald-100">
                    <h4 style="font-size: 0.75rem; font-weight: 800; color: #065f46; text-transform: uppercase; margin-bottom: 0.5rem;">{{ __('How to scan successfully:') }}</h4>
                    <ul style="font-size: 0.7rem; color: #065f46; list-style: disc; padding-left: 1rem; line-height: 1.4;">
                        <li>{{ __('Hold the phone steady to avoid blur.') }}</li>
                        <li>{{ __('Ensure the QR code is centered and large in the photo.') }}</li>
                        <li>{{ __('Avoid glare or shadows on the other screen.') }}</li>
                    </ul>
                </div>

                <p id="status-text" class="status-footer mt-6">
                    {{ __('Ready to scan. Choose an option below.') }}
                </p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const html5QrCode = new Html5Qrcode("reader");
            const statusText = document.getElementById('status-text');
            const walletCard = document.querySelector('.wallet-card');
            const qrConfig = { fps: 15, qrbox: { width: 250, height: 250 } };

            function updateOnlineStatus() {
                if (navigator.onLine) {
                    walletCard.classList.remove('offline-mode');
                    statusText.innerText = "Back online. Syncing handled in background.";
                    statusText.style.color = "#10b981";
                    syncOfflineTransfers(); // Auto sync when back online
                } else {
                    walletCard.classList.add('offline-mode');
                    statusText.innerText = "No connection. QR will be saved offline and synced later.";
                    statusText.style.color = "#f59e0b";
                }
            }

            window.addEventListener('online', updateOnlineStatus);
            window.addEventListener('offline', updateOnlineStatus);
            updateOnlineStatus();
            syncOfflineTransfers(); // Initial check on load
            
            // Request Persistent Storage
            if (navigator.storage && navigator.storage.persist) {
                navigator.storage.persist().then(persistent => {
                    if (persistent) console.log("Storage will not be cleared except by explicit user action");
                    else console.log("Storage may be cleared under storage pressure");
                });
            }

            // Exit Warning for unsynced data
            window.onbeforeunload = function(e) {
                const queue = JSON.parse(localStorage.getItem('pending_transfers') || '[]');
                if (queue.length > 0) {
                    const message = "You have pending scans that haven't been synced yet! Closing this page may lose them.";
                    e.returnValue = message;
                    return message;
                }
            };

            function startScanner() {
                statusText.innerText = "Requesting camera access...";
                statusText.style.color = "#111827";
                
                Html5Qrcode.getCameras().then(devices => {
                    if (devices && devices.length) {
                        let cameraId = devices[0].id;
                        // Try to find rear camera
                        const rearCamera = devices.find(device => 
                            device.label.toLowerCase().includes('back') || 
                            device.label.toLowerCase().includes('rear')
                        );
                        if (rearCamera) cameraId = rearCamera.id;

                        html5QrCode.start(
                            cameraId, 
                            qrConfig,
                            onScanSuccess,
                            onScanFailure
                        ).catch(err => {
                            console.error("Scanner start error:", err);
                            statusText.innerText = "Error: " + err;
                            statusText.style.color = "#ef4444";
                        });
                    } else {
                        statusText.innerText = "No cameras found. Please ensure your device has a camera and it's not blocked.";
                        statusText.style.color = "#ef4444";
                    }
                }).catch(err => {
                    console.error("Camera detection error:", err);
                    statusText.innerText = "Camera Error: " + err + ". (Note: Camera requires HTTPS or localhost)";
                    statusText.style.color = "#ef4444";
                });
            }

            function onScanSuccess(decodedText) {
                // Only stop if actually scanning
                const stopPromise = html5QrCode.getState() === 2 // 2 is SCANNING
                    ? html5QrCode.stop() 
                    : Promise.resolve();

                stopPromise.then(() => {
                    statusText.innerText = "Verifying QR code...";
                    statusText.classList.remove('hidden');
                    
                    fetch("{{ route('wallet.process-transfer') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ token: decodedText })
                    })
                    .then(response => response.json())
                    .then(data => {
                        const resultDiv = document.getElementById('result');
                        const messageP = document.getElementById('result-message');
                        const resultIcon = document.getElementById('result-icon');
                        const scannerContainer = document.getElementById('scanner-container');
                        const uploadSection = document.getElementById('upload-section');

                        if (scannerContainer) scannerContainer.classList.add('hidden');
                        if (uploadSection) uploadSection.classList.add('hidden');
                        statusText.classList.add('hidden');
                        resultDiv.classList.remove('hidden');

                        if (data.success) {
                            resultDiv.style.borderColor = "#10b981";
                            resultIcon.style.color = "#10b981";
                            resultIcon.innerHTML = '<svg style="width:2rem;height:2rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>';
                            messageP.innerText = data.message;
                        } else {
                            resultDiv.style.borderColor = "#ef4444";
                            resultIcon.style.color = "#ef4444";
                            resultIcon.innerHTML = '<svg style="width:2rem;height:2rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>';
                            messageP.innerText = data.message;
                        }
                    })
                    .catch(err => {
                        statusText.innerText = "Network error: " + err;
                        statusText.style.color = "#ef4444";
                    });
                }).catch(err => {
                    console.error("Stop error:", err);
                });
            }

            function onScanFailure(error) {
                // Ignore frequent scan failures during search
            }

            // Start automatically
            if (window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                startScanner();
            } else {
                document.getElementById('scanner-container').classList.add('hidden');
                statusText.innerText = "Real-time camera is disabled due to insecure connection (HTTP). Please use 'Take Photo' or 'Gallery' below.";
                statusText.style.color = "#4b5563";
                statusText.style.background = "#fef3c7";
                statusText.style.padding = "1rem";
                statusText.style.border = "1px solid #fcd34d";
            }

            // Global process function to handle all entry types
            window.processToken = function(token) {
                if (!token || token.trim().length === 0) return;
                
                const trimmedToken = token.trim();

                // Check Offline state
                if (!navigator.onLine) {
                    saveOfflineToken(trimmedToken);
                    return;
                }

                document.getElementById('loading-overlay').classList.remove('hidden');
                statusText.innerText = "Verifying code...";
                statusText.style.color = "#111827";
                
                fetch("{{ route('wallet.process-transfer') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ token: token.trim() })
                })
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('result');
                    const messageP = document.getElementById('result-message');
                    const resultIcon = document.getElementById('result-icon');
                    const scannerContainer = document.getElementById('scanner-container');
                    const uploadSection = document.getElementById('upload-section');
                    const manualSection = document.getElementById('manual-section');
                    const statusP = document.getElementById('status-text');

                    if (scannerContainer) scannerContainer.classList.add('hidden');
                    if (uploadSection) uploadSection.classList.add('hidden');
                    if (manualSection) manualSection.classList.add('hidden');
                    document.getElementById('loading-overlay').classList.add('hidden');
                    statusP.classList.add('hidden');
                    resultDiv.classList.remove('hidden');

                    if (data.success) {
                        resultDiv.style.borderColor = "#10b981";
                        resultIcon.style.color = "#10b981";
                        resultIcon.innerHTML = '<svg style="width:2rem;height:2rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>';
                        messageP.innerText = data.message;
                    } else {
                        resultDiv.style.borderColor = "#ef4444";
                        resultIcon.style.color = "#ef4444";
                        resultIcon.innerHTML = '<svg style="width:2rem;height:2rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>';
                        messageP.innerText = data.message;
                    }
                })
                .catch(err => {
                    document.getElementById('loading-overlay').classList.add('hidden');
                    statusText.innerText = "Network error: " + err;
                    statusText.style.color = "#ef4444";
                });
            }

            // Common File Processing Logic
            function handleFileSelect(e) {
                if (e.target.files.length === 0) return;
                
                const imageFile = e.target.files[0];
                const loadingOverlay = document.getElementById('loading-overlay');
                loadingOverlay.classList.remove('hidden');
                
                statusText.innerText = "Scanning your photo. Please wait...";
                statusText.style.color = "#111827";
                statusText.style.background = "transparent";
                statusText.style.padding = "0";
                statusText.style.border = "none";
                
                // If camera is running, stop it first
                const stopPrev = html5QrCode.getState() === 2 ? html5QrCode.stop() : Promise.resolve();

                stopPrev.then(() => {
                    // We give a small timeout to let the UI update and the reader element to clear
                    setTimeout(() => {
                        html5QrCode.scanFile(imageFile, true)
                            .then(decodedText => {
                                if (navigator.vibrate) navigator.vibrate(200); // Feedback for mobile
                                processToken(decodedText);
                            })
                            .catch(err => {
                                console.error("File scan error:", err);
                                loadingOverlay.classList.add('hidden');
                                statusText.innerText = "COULD NOT READ QR. TIPS: 1. Get closer 2. Keep it steady 3. Avoid reflections. The QR must be clear and large.";
                                statusText.style.color = "#ef4444";
                                statusText.style.background = "#fee2e2";
                                statusText.style.padding = "1rem";
                                statusText.style.border = "1px solid #fecaca";
                                statusText.style.fontWeight = "800";
                                
                                if (window.location.protocol === 'https:' || window.location.hostname === 'localhost') {
                                    setTimeout(startScanner, 3000);
                                }
                            });
                    }, 500);
                }).catch(err => {
                    console.error("Error transitioning to file scan:", err);
                    loadingOverlay.classList.add('hidden');
                });
            }

            // Gallery and Camera intents
            document.getElementById('qr-input-gallery').addEventListener('change', handleFileSelect);
            document.getElementById('qr-input-camera').addEventListener('change', handleFileSelect);

            // Offline Storage Logic
            function saveOfflineToken(token) {
                let queue = JSON.parse(localStorage.getItem('pending_transfers') || '[]');
                if (!queue.includes(token)) {
                    queue.push(token);
                    localStorage.setItem('pending_transfers', JSON.stringify(queue));
                }

                showOfflineResult();
            }

            function showOfflineResult() {
                const resultDiv = document.getElementById('result');
                const messageP = document.getElementById('result-message');
                const resultIcon = document.getElementById('result-icon');
                const scannerContainer = document.getElementById('scanner-container');
                const uploadSection = document.getElementById('upload-section');
                const manualSection = document.getElementById('manual-section');
                const statusP = document.getElementById('status-text');

                if (scannerContainer) scannerContainer.classList.add('hidden');
                if (uploadSection) uploadSection.classList.add('hidden');
                if (manualSection) manualSection.classList.add('hidden');
                document.getElementById('loading-overlay').classList.add('hidden');
                
                statusP.classList.add('hidden');
                resultDiv.classList.remove('hidden');

                resultDiv.style.borderColor = "#f59e0b";
                resultIcon.style.color = "#f59e0b";
                resultIcon.innerHTML = '<svg style="width:2rem;height:2rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                messageP.innerHTML = 'CAPTURE SUCCESSFUL!<br><span style="font-size:0.875rem; color:#92400e">Saved offline. Money will transfer automatically when you connect to internet.</span>';
            }

            async function syncOfflineTransfers() {
                if (!navigator.onLine) return;
                const queue = JSON.parse(localStorage.getItem('pending_transfers') || '[]');
                if (queue.length === 0) return;

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
                        }
                    } catch (e) {
                        console.error("Sync failed for token:", token, e);
                    }
                }
                const remaining = queue.filter(t => !successfulSyncs.includes(t));
                localStorage.setItem('pending_transfers', JSON.stringify(remaining));
            }
        });
    </script>
    @endpush
</x-app-layout>
