<x-guest-layout>
    <x-slot:title>Sign in</x-slot:title>

    <div class="text-center">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Sign in</h1>
        <p class="mt-1 text-[13px] text-slate-500">Welcome back. Enter your details to continue.</p>
    </div>

    <x-auth-session-status class="mt-5" :status="session('status')" />

    @if ($errors->any())
        <div class="mt-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-[13px] text-rose-700 space-y-1" role="alert">
            @foreach ($errors->all() as $error)
                <div class="flex items-start gap-2">
                    <i data-lucide="circle-alert" class="w-4 h-4 shrink-0 text-rose-500 mt-px"></i>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Email or username</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i data-lucide="user" class="w-4 h-4"></i>
                </span>
                <input id="email" name="email" type="text" value="{{ old('email', old('username')) }}" placeholder="you@marcelo.local"
                    class="app-input pl-10" required autofocus autocomplete="username"
                    @if($errors->has('email')) aria-invalid="true" @endif>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-[13px] font-semibold text-slate-700">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-[12px] font-semibold text-blue-700 hover:underline">Forgot password?</a>
                @endif
            </div>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </span>
                <input id="password" name="password" type="password" placeholder="••••••••"
                    class="app-input pl-10 pr-11" required autocomplete="current-password">
                <button type="button" data-toggle-password="password" aria-label="Show password" aria-pressed="false"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-700">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <label class="flex items-center gap-2.5 text-[13px] text-slate-600 cursor-pointer select-none">
            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 accent-blue-700">
            Remember me on this device
        </label>

        <button type="submit" class="app-btn mt-2">
            Sign in
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </button>
    </form>

    <p class="mt-6 text-center text-[12px] text-slate-400">Trouble signing in? Contact your administrator.</p>

    <script>
        // Show / hide the password.
        document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.getAttribute('data-toggle-password'));
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                btn.innerHTML = '<i data-lucide="' + (show ? 'eye-off' : 'eye') + '" class="w-4 h-4"></i>';
                lucide.createIcons();
            });
        });
    </script>
</x-guest-layout>
