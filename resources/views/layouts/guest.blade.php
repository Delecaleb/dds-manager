<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Sign in' }} | Marcelo Analytics</title>
    <link rel="icon" type="image/png" href="{{ asset('public/images/logo-mark.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Form controls shared by every auth page (sign in, reset password, …). */
        .app-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            padding: 0.625rem 0.75rem;
            font-size: 0.875rem;
            color: #0f172a;
            background: #fff;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .app-input::placeholder { color: #94a3b8; }
        .app-input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .15);
        }
        .app-input[aria-invalid="true"] { border-color: #f43f5e; }
        .app-btn {
            width: 100%;
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            border-radius: 0.5rem;
            padding: 0.7rem 1rem;
            font-size: 0.875rem; font-weight: 600; color: #fff;
            background: linear-gradient(135deg, #0f2a5a 0%, #1d4ed8 100%);
            box-shadow: 0 6px 16px -6px rgba(29, 78, 216, .6);
            transition: filter .15s ease, transform .15s ease;
        }
        .app-btn:hover { filter: brightness(1.08); }
        .app-btn:active { transform: translateY(1px); }
        .app-btn:focus-visible { outline: 3px solid rgba(37, 99, 235, .35); outline-offset: 2px; }
    </style>
</head>

{{-- Deep-navy gradient in the logo's colors, a faint repeat of the tooth mark, and one white card. --}}
<body class="min-h-screen bg-[#08162e] font-sans antialiased text-slate-700">
    <div class="fixed inset-0 bg-gradient-to-br from-[#0a1d3f] via-[#0f2a5a] to-[#1e40af]" aria-hidden="true"></div>
    <div class="fixed inset-0 opacity-[0.05]" aria-hidden="true"
        style="background-image: url('{{ asset('public/images/logo-mark.png') }}'); background-size: 72px 72px; background-position: 18px 18px;"></div>
    <div class="fixed -top-32 -left-32 w-[480px] h-[480px] rounded-full bg-sky-400/20 blur-3xl" aria-hidden="true"></div>
    <div class="fixed -bottom-40 -right-24 w-[520px] h-[520px] rounded-full bg-blue-600/25 blur-3xl" aria-hidden="true"></div>

    <main class="relative min-h-screen flex flex-col items-center justify-center px-4 py-10 sm:px-6">
        <div class="w-full max-w-[440px]">
            <div class="bg-white rounded-2xl shadow-2xl shadow-black/40 ring-1 ring-white/10">
                <div class="flex justify-center px-8 pt-8">
                    <a href="{{ route('home') }}" aria-label="Marcelo Analytics">
                        <img src="{{ asset('public/images/logo.png') }}" alt="Marcelo Analytics" class="h-11 w-auto">
                    </a>
                </div>
                <div class="px-8 pb-8 pt-6">
                    {{ $slot }}
                </div>
            </div>
            <p class="mt-6 text-center text-[11px] text-white/60">
                Need help signing in? Email <a href="mailto:{{ config('legal.support_email') }}" class="text-white/80 hover:text-white underline underline-offset-2">{{ config('legal.support_email') }}</a>
            </p>
            <p class="mt-2 text-center text-[11px] text-white/50">
                &copy; {{ date('Y') }} Marcelo Analytics
                &middot; <a href="{{ route('privacy') }}" class="hover:text-white/80 underline underline-offset-2">Privacy Policy</a>
                &middot; <a href="{{ route('terms') }}" class="hover:text-white/80 underline underline-offset-2">Terms of Service</a>
            </p>
        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
