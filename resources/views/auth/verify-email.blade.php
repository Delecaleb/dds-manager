<x-guest-layout>
    <x-slot:title>Verify email</x-slot:title>

    <div class="text-center">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Check your email</h1>
        <p class="mt-1 text-[13px] text-slate-500">We sent you a verification link. Click it to finish setting up your account.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] text-emerald-800" role="status">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
        @csrf
        <button type="submit" class="app-btn">
            Resend verification email
            <i data-lucide="send" class="w-4 h-4"></i>
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-[13px] font-semibold text-slate-500 hover:text-slate-800 hover:underline">Sign out</button>
    </form>
</x-guest-layout>
