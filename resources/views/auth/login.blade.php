<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff & Admin Portal Sign In - Tumpat Solutions</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800|space-grotesk:500,600,700" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-body flex flex-col justify-between min-h-screen">
    <!-- Top Nav Back Link -->
    <div class="p-6">
        <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-semibold text-slate-300 hover:text-white transition-colors duration-200">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Return to Public Website
        </a>
    </div>

    <!-- Centered Card -->
    <div class="w-full max-w-md mx-auto px-4 py-8">
        <div class="login-card">
            <!-- Brand Header -->
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-block">
                    <img src="{{ asset('images/logo.png') }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='block'" alt="Tumpat Solutions" class="h-14 mx-auto mb-3">
                    <h2 class="text-2xl font-extrabold tracking-tight" style="display: none;">
                        <span class="text-emerald-600">TUMPAT</span> <span class="text-orange-600">SOLUTIONS</span>
                    </h2>
                </a>
                <h1 class="text-xl font-bold text-slate-900 mt-2">Operations & Staff Portal</h1>
                <p class="text-xs text-slate-500 mt-1">Authorized personnel and field engineers only</p>
            </div>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mb-5 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                    <div class="flex gap-2">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                <!-- Phone Number -->
                <div>
                    <label for="login" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Registered Mobile Number
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </span>
                        <input 
                            type="tel" 
                            id="login" 
                            name="login" 
                            value="{{ old('login') }}" 
                            required 
                            autofocus
                            placeholder="01XXXXXXXX"
                            class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors"
                        >
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">Malaysian mobile format (e.g. 0123456789)</p>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </span>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            placeholder="••••••••••••"
                            class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors"
                        >
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center text-xs text-slate-600 cursor-pointer">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            name="remember"
                            class="rounded border-slate-300 text-orange-600 focus:ring-orange-500 h-4 w-4"
                        >
                        <span class="ml-2">Remember credentials on this device</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit"
                    class="w-full py-3 px-4 rounded-lg text-white text-sm font-semibold tracking-wide transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2"
                    style="background: linear-gradient(135deg, #f7a04c, #f27e26);"
                >
                    Sign In to Portal
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </form>

            <!-- Security Badge -->
            <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-center gap-2 text-[11px] text-slate-400">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <span>Encrypted Session &bull; Geofence Verification Active</span>
            </div>
        </div>
    </div>

    <!-- Bottom Copyright -->
    <div class="p-6 text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} Tumpat Solutions Sdn Bhd. All rights reserved.
    </div>
</body>
</html>
