<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[#0a0a0a]">
        <div class="w-full sm:max-w-md mt-6 px-8 py-10 bg-[#141414] border border-[#222] shadow-[0_10px_40px_rgba(0,0,0,0.5)] overflow-hidden sm:rounded-2xl flex flex-col items-center">
            
            <!-- Animated Icon -->
            <div class="mb-8 relative">
                <div class="absolute inset-0 bg-emerald-500/20 blur-3xl rounded-full"></div>
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/20 animate-pulse">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>

            <h2 class="text-3xl font-bold text-white mb-2 tracking-tight">Two-Step Verification</h2>
            <p class="text-gray-400 text-center mb-8 px-4 leading-relaxed">
                We've sent a 6-digit verification code to your device via push notification. Please enter it below to proceed.
            </p>

            @if(config('app.debug'))
            <div class="mb-6 px-4 py-2 bg-emerald-500/10 border border-emerald-500/20 rounded-lg">
                <p class="text-emerald-400 text-sm font-mono">
                    TEST CODE: <span class="font-bold text-lg tracking-widest">{{ $otpCode }}</span>
                </p>
            </div>
            @endif

            <form method="POST" action="{{ route('otp.verify.web') }}" class="w-full">
                @csrf
                <input type="hidden" name="action_type" value="{{ $action_type }}">
                <input type="hidden" name="intended_url" value="{{ $intended_url }}">
                <input type="hidden" name="action_data" value="{{ json_encode($action_data) }}">

                <div class="mb-8">
                    <label for="otp_code" class="sr-only">Verification Code</label>
                    <div class="flex justify-center gap-3" id="otp-input-container">
                        <input type="text" id="otp_code" name="otp_code" maxlength="6" 
                               class="w-full text-center text-4xl font-mono tracking-[1em] py-4 bg-[#1a1a1a] border-[#333] text-emerald-400 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all placeholder-gray-700"
                               placeholder="000000" autofocus required>
                    </div>
                    @error('otp_code')
                        <p class="mt-2 text-sm text-red-500 text-center">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-4">
                    <button type="submit" class="w-full py-4 bg-emerald-500 hover:bg-emerald-600 text-black font-bold rounded-xl shadow-lg shadow-emerald-500/20 transform transition-all active:scale-95 text-lg">
                        Verify & Complete Action
                    </button>
                    
                    <div class="flex items-center justify-between mt-4">
                        <button type="button" onclick="location.reload()" class="text-sm text-gray-400 hover:text-white transition-colors flex items-center gap-2">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Resend Code
                        </button>
                        
                        <a href="{{ url('/') }}" class="text-sm text-gray-500 hover:text-red-400 transition-colors uppercase font-bold tracking-wider">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>

            <div class="mt-12 flex gap-4 opacity-30 select-none">
                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
            </div>
        </div>
    </div>

    <style>
        /* Custom styles for the OTP page */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
        @keyframes subtle-pulse {
            0%, 100% { opacity: 0.1; }
            50% { opacity: 0.2; }
        }
    </style>
</x-guest-layout>
