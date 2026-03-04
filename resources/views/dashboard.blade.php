<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 font-bold uppercase text-xs">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 font-bold uppercase text-xs">
                    {{ session('status') }}
                </div>
            @endif

            @if (auth()->user()->status !== 'approved')
                <div class="mb-6 p-6 bg-amber-50 border-2 border-amber-200 rounded-lg">
                    <h3 class="text-amber-800 font-black uppercase text-sm mb-2">Account Pending Verification</h3>
                    <p class="text-amber-700 text-xs">
                        Your account is currently being reviewed by our administration team. 
                        You will be able to access the wallet and perform operations once your KYC documents are verified.
                    </p>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></span>
                        <span class="text-[10px] font-bold text-amber-500 uppercase">Current Status: {{ auth()->user()->status }}</span>
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
