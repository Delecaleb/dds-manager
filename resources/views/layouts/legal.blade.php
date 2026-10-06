{{--
    Shell for the public pages (/home, /privacy, /terms). Standalone on purpose: these pages
    are reached from the Google OAuth consent screen and the sign-in page, so they must not
    depend on the authenticated app layout.
--}}
@php
    $appName = config('app.name');
    $company = config('legal.company');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | {{ $appName }}</title>
    <meta name="description" content="@yield('description')">
    <link rel="icon" type="image/png" href="{{ asset('public/images/logo-mark.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .legal h2 { font-size: 1.125rem; font-weight: 700; color: #0f172a; margin-top: 2.25rem; margin-bottom: .75rem; }
        .legal h3 { font-size: 1rem; font-weight: 600; color: #0f172a; margin-top: 1.5rem; margin-bottom: .5rem; }
        .legal p, .legal li { font-size: .9375rem; line-height: 1.7; color: #334155; }
        .legal p + p { margin-top: .75rem; }
        .legal ul { list-style: disc; padding-left: 1.5rem; margin: .75rem 0; }
        .legal li + li { margin-top: .375rem; }
        .legal a { color: #1d4ed8; text-decoration: underline; text-underline-offset: 2px; }
        .legal strong { color: #0f172a; }
        .legal code { font-size: 13px; background: #f1f5f9; padding: 0 .25rem; border-radius: .25rem; word-break: break-all; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 font-sans antialiased text-slate-700">
    <header class="bg-[#0f2a5a] text-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-5 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="{{ $appName }}">
                <img src="{{ asset('public/images/logo-mark.png') }}" alt="" class="h-9 w-9">
                <span class="font-semibold tracking-tight">{{ $appName }}</span>
            </a>
            <a href="{{ route('login') }}" class="text-sm text-white/80 hover:text-white">Sign in</a>
        </div>
    </header>

    <main class="@yield('width', 'max-w-3xl') mx-auto px-4 sm:px-6 py-10">
        @yield('content')

        <nav class="mt-6 flex justify-center gap-4 text-xs text-slate-500">
            <a href="{{ route('home') }}" class="hover:text-slate-800 underline underline-offset-2">Home</a>
            <a href="{{ route('privacy') }}" class="hover:text-slate-800 underline underline-offset-2">Privacy Policy</a>
            <a href="{{ route('terms') }}" class="hover:text-slate-800 underline underline-offset-2">Terms of Service</a>
        </nav>

        <p class="mt-6 text-center text-xs text-slate-400">&copy; {{ date('Y') }} {{ $company }} &middot; {{ $appName }}</p>
    </main>
    <script>lucide.createIcons();</script>
</body>
</html>
