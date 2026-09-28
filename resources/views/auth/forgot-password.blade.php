<x-guest-layout>
    <x-slot:title>Forgot password</x-slot:title>

    <div class="text-center">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Forgot your password?</h1>
        <p class="mt-1 text-[13px] text-slate-500">Enter your email and we'll send you a link to choose a new one.</p>
    </div>

    <x-auth-session-status class="mt-5" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" class="app-input" required autofocus autocomplete="email"
                @if($errors->has('email')) aria-invalid="true" @endif>
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-[13px]" />
        </div>

        <button type="submit" class="app-btn mt-2">
            Email me a reset link
            <i data-lucide="send" class="w-4 h-4"></i>
        </button>
    </form>

    <p class="mt-6 text-center text-[13px] text-slate-500">
        <a href="{{ route('login') }}" class="font-semibold text-blue-700 hover:underline">Back to sign in</a>
    </p>
</x-guest-layout>
