<x-guest-layout>
    <x-slot:title>Reset password</x-slot:title>

    <div class="text-center">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Choose a new password</h1>
        <p class="mt-1 text-[13px] text-slate-500">Use at least 8 characters.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" class="app-input" required autofocus autocomplete="username"
                @if($errors->has('email')) aria-invalid="true" @endif>
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-[13px]" />
        </div>

        <div>
            <label for="password" class="block text-[13px] font-semibold text-slate-700 mb-1.5">New password</label>
            <input id="password" name="password" type="password" class="app-input" required autocomplete="new-password"
                @if($errors->has('password')) aria-invalid="true" @endif>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-[13px]" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Confirm new password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="app-input" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-[13px]" />
        </div>

        <button type="submit" class="app-btn mt-2">
            Reset password
            <i data-lucide="check" class="w-4 h-4"></i>
        </button>
    </form>
</x-guest-layout>
