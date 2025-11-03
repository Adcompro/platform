<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-envelope mr-2 text-gray-400"></i>Email Address
            </label>
            <div class="relative">
                <div class="input-icon">
                    <i class="fas fa-user"></i>
                </div>
                <input id="email"
                       type="email"
                       name="email"
                       value="{{ old('email') }}"
                       required
                       autofocus
                       autocomplete="username"
                       class="input-with-icon login-input block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 transition duration-200"
                       style="border-color: #d1d5db; focus:border-color: var(--theme-primary);"
                       placeholder="Enter your email">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-lock mr-2 text-gray-400"></i>Password
            </label>
            <div class="relative">
                <div class="input-icon">
                    <i class="fas fa-key"></i>
                </div>
                <input id="password"
                       type="password"
                       name="password"
                       required
                       autocomplete="current-password"
                       class="input-with-icon login-input block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 transition duration-200"
                       style="border-color: #d1d5db; focus:border-color: var(--theme-primary);"
                       placeholder="Enter your password">
                <button type="button"
                        onclick="togglePassword()"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-eye" id="toggleIcon"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <input id="remember_me"
                       type="checkbox"
                       class="rounded border-gray-300 shadow-sm transition cursor-pointer"
                       style="color: var(--theme-primary);"
                       name="remember">
                <span class="ml-2 text-sm text-gray-600 group-hover:text-gray-800 transition">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm forgot-link font-medium transition"
                   href="{{ route('password.request') }}">
                    Forgot password?
                </a>
            @endif
        </div>

        <!-- Login Button -->
        <div>
            <button type="submit"
                    class="w-full btn-gradient text-white font-semibold py-3 px-4 rounded-lg shadow-lg flex items-center justify-center space-x-2">
                <span>Sign In</span>
                <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </form>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</x-guest-layout>
