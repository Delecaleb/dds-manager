<x-guest-layout>
    <x-slot:title>Set your password</x-slot:title>

    @if (! $valid)
        <div class="text-center">
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">This invitation link no longer works</h1>
            <p class="mt-2 text-[13px] text-slate-500">
                It may have expired, been replaced by a newer invitation, or already been used.
                Ask your administrator to send a new one. If you already set your password, sign in instead.
            </p>
        </div>

        <a href="{{ route('login') }}" class="app-btn mt-6">
            Go to sign in
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    @else
        <div class="text-center">
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Welcome — set your password</h1>
            <p class="mt-1 text-[13px] text-slate-500">You'll sign in with <span class="font-semibold text-slate-700">{{ $email }}</span>.</p>
        </div>

        <form method="POST" action="{{ route('invitation.store') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <x-input-error :messages="$errors->get('email')" class="text-[13px]" />

            <div>
                <label for="name" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Your name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $name) }}" class="app-input" required autofocus autocomplete="name"
                    @if($errors->has('name')) aria-invalid="true" @endif>
                <x-input-error :messages="$errors->get('name')" class="mt-2 text-[13px]" />
            </div>

            <div>
                <label for="password" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Password</label>
                <input id="password" name="password" type="password" class="app-input" required autocomplete="new-password"
                    @if($errors->has('password')) aria-invalid="true" @endif>
                <p class="mt-1 text-[12px] text-slate-500">At least 8 characters.</p>
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-[13px]" />
            </div>

            <div>
                <label for="password_confirmation" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="app-input" required autocomplete="new-password">
            </div>

            <button type="submit" class="app-btn mt-2">
                Set password and sign in
                <i data-lucide="check" class="w-4 h-4"></i>
            </button>
        </form>
    @endif
</x-guest-layout>
