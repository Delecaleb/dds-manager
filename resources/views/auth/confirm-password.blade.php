<x-guest-layout>
    <x-slot:title>Confirm password</x-slot:title>

    <div class="text-center">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Confirm your password</h1>
        <p class="mt-1 text-[13px] text-slate-500">This is a secure area. Please enter your password to continue.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="password" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Password</label>
            <input id="password" name="password" type="password" class="app-input" required autofocus autocomplete="current-password"
                @if($errors->has('password')) aria-invalid="true" @endif>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-[13px]" />
        </div>

        <button type="submit" class="app-btn mt-2">
            Confirm
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </button>
    </form>
</x-guest-layout>
