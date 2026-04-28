<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tumpat Solutions</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="Tumpat Solutions" class="h-16 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Tumpat Solutions</h1>
            <p class="text-gray-600 mt-2">Sign in to your account</p>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <!-- Phone Number -->
            <div class="mb-4">
                <label for="login" class="block text-gray-700 font-medium mb-2">Phone Number</label>
                <input 
                    type="tel" 
                    id="login" 
                    name="login" 
                    value="{{ old('login') }}"
                    required 
                    autofocus
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                    placeholder="0123456789"
                >
                @error('login')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                    placeholder="Enter your password"
                >
            </div>

            <!-- Remember Me -->
            <div class="flex items-center mb-6">
                <input 
                    type="checkbox" 
                    id="remember" 
                    name="remember"
                    class="w-4 h-4 text-orange-500 border-gray-300 rounded focus:ring-orange-500"
                >
                <label for="remember" class="ml-2 text-gray-700 text-sm">Remember me</label>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit"
                class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-200"
                style="background-color: #F27E26;"
                onmouseover="this.style.backgroundColor='#d66d1f'"
                onmouseout="this.style.backgroundColor='#F27E26'"
            >
                Sign In
            </button>
        </form>

        <!-- Footer -->
        <div class="mt-6 text-center text-gray-600 text-sm">
            <p>&copy; {{ date('Y') }} Tumpat Solutions. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
