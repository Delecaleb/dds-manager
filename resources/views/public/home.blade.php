@php
    $appName = config('app.name');
    $company = config('legal.company');
    $email = config('legal.contact_email');
    $moduleCount = collect($categories)->sum(fn ($c) => count($c['modules']));
    $menu = [
        ['#how-it-works', 'How it works'],
        ['#modules', 'Modules'],
        ['#integrations', 'Integrations'],
        ['#security', 'Security'],
        ['#faq', 'FAQ'],
    ];
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName }} | Practice analytics for {{ $company }}</title>
    <meta name="description" content="{{ $appName }} is the internal practice-analytics platform for {{ $company }}: production, collections, scheduling, treatment acceptance, recall and marketing return across every office, built on Open Dental data.">
    <link rel="icon" type="image/png" href="{{ asset('public/images/logo-mark.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    colors: { navy: { 900: '#08162e', 800: '#0a1d3f', 700: '#0f2a5a', 600: '#163a7a' } },
                },
            },
        };
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        html { scroll-padding-top: 5rem; }
        .nav-link { position: relative; }
        .nav-link::after {
            content: ''; position: absolute; left: 0; right: 0; bottom: -6px; height: 2px;
            background: #2563eb; transform: scaleX(0); transform-origin: left; transition: transform .2s ease;
        }
        .nav-link:hover::after, .nav-link.is-active::after { transform: scaleX(1); }
        .reveal { opacity: 0; transform: translateY(18px); transition: opacity .6s ease, transform .6s ease; }
        .reveal.is-visible { opacity: 1; transform: none; }
        .grid-bg {
            background-image: linear-gradient(rgba(255,255,255,.06) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,.06) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        .tab-btn[aria-selected="true"] { background: #0f2a5a; color: #fff; }
        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
        details[open] .faq-icon { transform: rotate(45deg); }
        .faq-icon { transition: transform .2s ease; }
        @media (prefers-reduced-motion: reduce) { .reveal { transition: none; opacity: 1; transform: none; } html { scroll-behavior: auto; } }
    </style>
</head>
<body class="bg-white text-slate-700 antialiased">

{{-- ========== Navigation ========== --}}
<header id="site-header" class="fixed inset-x-0 top-0 z-50 transition-colors duration-300 bg-navy-900/0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="h-16 flex items-center justify-between">
            <a href="#top" class="flex items-center gap-3" aria-label="{{ $appName }} home">
                <img src="{{ asset('public/images/logo-mark.png') }}" alt="" class="h-9 w-9">
                <span class="font-bold tracking-tight text-white">{{ $appName }}</span>
            </a>

            <nav class="hidden md:flex items-center gap-8" aria-label="Main">
                @foreach ($menu as [$href, $label])
                    <a href="{{ $href }}" class="nav-link text-sm font-medium text-white/80 hover:text-white">{{ $label }}</a>
                @endforeach
            </nav>

            <div class="hidden md:flex items-center gap-3">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-lg bg-white text-navy-700 text-sm font-semibold px-4 py-2 shadow-sm hover:bg-blue-50">
                    Sign in <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <button id="menu-toggle" type="button" class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg text-white hover:bg-white/10" aria-controls="mobile-menu" aria-expanded="false" aria-label="Open menu">
                <i data-lucide="menu" class="w-6 h-6" id="menu-icon-open"></i>
                <i data-lucide="x" class="w-6 h-6 hidden" id="menu-icon-close"></i>
            </button>
        </div>
    </div>

    <div id="mobile-menu" class="md:hidden hidden border-t border-white/10 bg-navy-900/95 backdrop-blur">
        <nav class="px-4 py-4 space-y-1" aria-label="Mobile">
            @foreach ($menu as [$href, $label])
                <a href="{{ $href }}" class="mobile-link block rounded-lg px-3 py-2.5 text-base font-medium text-white/85 hover:bg-white/10 hover:text-white">{{ $label }}</a>
            @endforeach
            <a href="{{ route('login') }}" class="mt-3 flex items-center justify-center gap-2 rounded-lg bg-white text-navy-700 font-semibold px-4 py-2.5">
                Sign in <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </nav>
    </div>
</header>

{{-- ========== Hero ========== --}}
<section id="top" class="relative overflow-hidden bg-navy-900 text-white">
    <div class="absolute inset-0 bg-gradient-to-br from-navy-800 via-navy-700 to-blue-800" aria-hidden="true"></div>
    <div class="absolute inset-0 grid-bg opacity-60" aria-hidden="true"></div>
    <div class="absolute -top-40 -left-40 w-[560px] h-[560px] rounded-full bg-sky-400/20 blur-3xl" aria-hidden="true"></div>
    <div class="absolute -bottom-48 -right-32 w-[620px] h-[620px] rounded-full bg-blue-500/25 blur-3xl" aria-hidden="true"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-24 lg:pt-40 lg:pb-32">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-6">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 ring-1 ring-white/20 text-xs font-semibold px-3 py-1">
                    <i data-lucide="building-2" class="w-3.5 h-3.5"></i> Internal platform of {{ $company }}
                </span>
                <h1 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.05]">
                    Every office.<br>Every provider.<br><span class="text-sky-300">One set of numbers.</span>
                </h1>
                <p class="mt-6 text-lg text-white/80 leading-relaxed max-w-xl">
                    {{ $appName }} turns raw Open Dental data into the metrics a dental group runs on:
                    production, collections, schedule capacity, treatment acceptance, hygiene recall and
                    marketing return, computed once and shown the same way everywhere.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-lg bg-white text-navy-700 font-semibold px-5 py-3 shadow-lg shadow-black/20 hover:bg-blue-50">
                        Sign in to your workspace <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="#how-it-works" class="inline-flex items-center gap-2 rounded-lg bg-white/10 ring-1 ring-white/25 text-white font-semibold px-5 py-3 hover:bg-white/15">
                        <i data-lucide="play-circle" class="w-4 h-4"></i> How it works
                    </a>
                </div>
                <dl class="mt-10 grid grid-cols-3 gap-6 max-w-md">
                    <div><dt class="text-2xl font-extrabold">{{ $moduleCount }}</dt><dd class="text-xs text-white/60 mt-1">Modules</dd></div>
                    <div><dt class="text-2xl font-extrabold">{{ count($categories) }}</dt><dd class="text-xs text-white/60 mt-1">Workgroups</dd></div>
                    <div><dt class="text-2xl font-extrabold">1</dt><dd class="text-xs text-white/60 mt-1">Definition per metric</dd></div>
                </dl>
            </div>

            {{-- Illustrative dashboard. Figures are sample values, not live data. --}}
            <div class="lg:col-span-6 reveal" aria-hidden="true">
                <div class="relative">
                    <div class="absolute -inset-3 rounded-3xl bg-gradient-to-br from-sky-400/30 to-blue-600/30 blur-2xl"></div>
                    <div class="relative rounded-2xl bg-white shadow-2xl shadow-black/40 ring-1 ring-white/20 overflow-hidden text-slate-800">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            </div>
                            <div class="text-[11px] font-medium text-slate-500">All locations · Month to date</div>
                            <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">Synced 2 min ago</span>
                        </div>
                        <div class="p-4 grid grid-cols-3 gap-3">
                            <div class="rounded-xl border border-slate-200 p-3">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Production</div>
                                <div class="mt-1 text-lg font-extrabold">$412,380</div>
                                <div class="text-[10px] text-emerald-600 font-semibold">+6.2% vs goal</div>
                            </div>
                            <div class="rounded-xl border border-slate-200 p-3">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Collections</div>
                                <div class="mt-1 text-lg font-extrabold">97.4%</div>
                                <div class="text-[10px] text-slate-500">net collection rate</div>
                            </div>
                            <div class="rounded-xl border border-slate-200 p-3">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tx acceptance</div>
                                <div class="mt-1 text-lg font-extrabold">41.8%</div>
                                <div class="text-[10px] text-blue-600 font-semibold">by dollars</div>
                            </div>
                        </div>
                        <div class="px-4 pb-4 grid grid-cols-5 gap-3">
                            <div class="col-span-3 rounded-xl border border-slate-200 p-3">
                                <div class="flex items-center justify-between">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Production by office</div>
                                    <i data-lucide="bar-chart-3" class="w-3.5 h-3.5 text-slate-400"></i>
                                </div>
                                <div class="mt-3 flex items-end gap-2 h-24">
                                    @foreach ([62, 84, 48, 92, 70, 56, 78] as $h)
                                        <div class="flex-1 rounded-t bg-gradient-to-t from-blue-700 to-sky-400" style="height: {{ $h }}%"></div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-span-2 rounded-xl border border-slate-200 p-3">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Recall due</div>
                                <ul class="mt-2 space-y-2">
                                    @foreach ([['Hygiene', 'w-4/5', 'bg-blue-600'], ['Perio', 'w-3/5', 'bg-sky-500'], ['Unscheduled Tx', 'w-2/5', 'bg-amber-500']] as [$l, $w, $c])
                                        <li>
                                            <div class="flex justify-between text-[10px] text-slate-600"><span>{{ $l }}</span></div>
                                            <div class="mt-1 h-1.5 rounded bg-slate-100"><div class="h-1.5 rounded {{ $c }} {{ $w }}"></div></div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ========== Value strip ========== --}}
<section class="border-b border-slate-200 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 grid grid-cols-2 md:grid-cols-4 gap-6 text-sm">
        @foreach ([
            ['database', 'Read-only sync', 'Open Dental is never modified'],
            ['check-circle-2', 'Consistent metrics', 'Same number on every screen'],
            ['users', 'Role-based access', 'Per-user, per-office permissions'],
            ['shield-check', 'Built for PHI', 'Minimum-necessary by design'],
        ] as [$icon, $t, $d])
            <div class="flex items-start gap-3">
                <span class="shrink-0 w-9 h-9 rounded-lg bg-white ring-1 ring-slate-200 text-blue-700 flex items-center justify-center"><i data-lucide="{{ $icon }}" class="w-4 h-4"></i></span>
                <div><div class="font-semibold text-slate-900">{{ $t }}</div><div class="text-slate-500 text-xs mt-0.5">{{ $d }}</div></div>
            </div>
        @endforeach
    </div>
</section>

{{-- ========== How it works ========== --}}
<section id="how-it-works" class="py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl reveal">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-700">How it works</span>
            <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">From practice management system to decisions, without touching the clinical database</h2>
            <p class="mt-4 text-lg text-slate-600">Open Dental stays the system of record. {{ $appName }} copies what it needs, computes each metric once, and serves it to the right people.</p>
        </div>

        <ol class="mt-14 relative grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="hidden md:block absolute top-6 left-[12.5%] right-[12.5%] h-px bg-gradient-to-r from-blue-200 via-blue-400 to-blue-200" aria-hidden="true"></div>
            @foreach ([
                ['refresh-cw', 'Incremental sync', 'Patients, appointments, procedures, treatment plans, claims, payments, adjustments, recalls and schedules are pulled into a local reporting database on a schedule, with checkpoints, retries and logging.'],
                ['calculator', 'One definition per metric', 'Production, collections, acceptance, recall and every other figure lives in a single domain service. A dashboard tile, a drill-down and an export all call the same code.'],
                ['layout-dashboard', 'Dashboards and drill-downs', 'Filter by date range, office and provider. Click any number to see the appointments, procedures or claims behind it.'],
                ['lock', 'Governed access', 'Users get a role and a set of offices. Modules, data and actions are limited to what that role is allowed to see.'],
            ] as [$icon, $t, $d])
                <li class="relative reveal">
                    <div class="relative z-10 w-12 h-12 rounded-xl bg-navy-700 text-white flex items-center justify-center shadow-lg shadow-blue-900/20">
                        <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
                    </div>
                    <div class="mt-5 text-xs font-bold text-slate-400">STEP {{ $loop->iteration }}</div>
                    <h3 class="mt-1 text-lg font-bold text-slate-900">{{ $t }}</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $d }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- ========== Modules (tabbed, from the module registry) ========== --}}
<section id="modules" class="py-20 lg:py-28 bg-slate-50 border-y border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl reveal">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-700">Modules</span>
            <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">{{ $moduleCount }} modules, organized by who uses them</h2>
            <p class="mt-4 text-lg text-slate-600">Executives, office managers, front desk, providers, billing and marketing each get the views they need. Access to each module is granted per user.</p>
        </div>

        <div class="mt-10 reveal">
            <div role="tablist" aria-label="Module groups" class="flex flex-wrap gap-2">
                @foreach ($categories as $key => $category)
                    <button type="button" role="tab" id="tab-{{ $key }}" aria-controls="panel-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                        class="tab-btn rounded-full px-4 py-2 text-sm font-semibold bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-100 transition-colors">
                        {{ $category['label'] }}
                    </button>
                @endforeach
            </div>

            @foreach ($categories as $key => $category)
                <div role="tabpanel" id="panel-{{ $key }}" aria-labelledby="tab-{{ $key }}" class="mt-8 {{ $loop->first ? '' : 'hidden' }}">
                    <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($category['modules'] as $module)
                            <li class="group bg-white rounded-2xl ring-1 ring-slate-200 p-5 hover:ring-blue-300 hover:shadow-lg hover:shadow-blue-900/5 transition">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center group-hover:bg-navy-700 group-hover:text-white transition-colors">
                                        <i data-lucide="{{ $module['icon'] }}" class="w-5 h-5"></i>
                                    </span>
                                    <h3 class="font-bold text-slate-900">{{ $module['name'] }}</h3>
                                </div>
                                <p class="mt-3 text-sm text-slate-600 leading-relaxed">{{ $module['description'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ========== Integrations ========== --}}
<section id="integrations" class="py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl reveal">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-700">Integrations</span>
            <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Three data sources. All of them read-only.</h2>
            <p class="mt-4 text-lg text-slate-600">{{ $appName }} reads from the systems the group already uses and never writes back to them.</p>
        </div>

        <div class="mt-12 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <article class="reveal rounded-2xl ring-1 ring-slate-200 p-7 bg-white">
                <span class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center"><i data-lucide="database" class="w-5 h-5"></i></span>
                <h3 class="mt-5 text-xl font-bold text-slate-900">Open Dental</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">The practice management system in every office. Tables are synchronized incrementally with per-table checkpoints so reporting never queries the live clinical database.</p>
                <ul class="mt-4 space-y-1.5 text-sm text-slate-600">
                    @foreach (['Patients, appointments and schedules', 'Procedures and treatment plans', 'Claims, payments and adjustments', 'Recalls and provider setup'] as $i)
                        <li class="flex gap-2"><i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>{{ $i }}</li>
                    @endforeach
                </ul>
            </article>

            <article class="reveal rounded-2xl ring-1 ring-slate-200 p-7 bg-white">
                <span class="w-11 h-11 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center"><i data-lucide="megaphone" class="w-5 h-5"></i></span>
                <h3 class="mt-5 text-xl font-bold text-slate-900">Google Ads</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">An administrator connects the group's own Google Ads manager account with Google sign-in. {{ $appName }} reads reporting data and places it next to new-patient and production figures to show return on ad spend per office.</p>
                <ul class="mt-4 space-y-1.5 text-sm text-slate-600">
                    @foreach (['Account names and IDs', 'Campaign names, status and channel', 'Daily cost, impressions, clicks, conversions'] as $i)
                        <li class="flex gap-2"><i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>{{ $i }}</li>
                    @endforeach
                    <li class="flex gap-2"><i data-lucide="x" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5"></i>Never creates or edits campaigns, ads, budgets or bids</li>
                    <li class="flex gap-2"><i data-lucide="x" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5"></i>Reads no other Google product</li>
                </ul>
            </article>

            <article class="reveal rounded-2xl ring-1 ring-slate-200 p-7 bg-white">
                <span class="w-11 h-11 rounded-xl bg-sky-50 text-sky-700 flex items-center justify-center"><i data-lucide="globe" class="w-5 h-5"></i></span>
                <h3 class="mt-5 text-xl font-bold text-slate-900">Website analytics</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">A small first-party snippet on the group's own websites records visits and form submissions so new leads can be attributed to the channel that produced them.</p>
                <ul class="mt-4 space-y-1.5 text-sm text-slate-600">
                    @foreach (['Page views, referrers and campaign parameters', 'Form submissions linked to the visit', 'Local storage instead of cookies', 'Nothing shared with third parties'] as $i)
                        <li class="flex gap-2"><i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>{{ $i }}</li>
                    @endforeach
                </ul>
            </article>
        </div>
    </div>
</section>

{{-- ========== Security ========== --}}
<section id="security" class="relative overflow-hidden bg-navy-900 text-white py-20 lg:py-28">
    <div class="absolute inset-0 bg-gradient-to-br from-navy-800 via-navy-700 to-blue-900" aria-hidden="true"></div>
    <div class="absolute inset-0 grid-bg opacity-40" aria-hidden="true"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <div class="reveal">
            <span class="text-xs font-bold uppercase tracking-wider text-sky-300">Security and privacy</span>
            <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight">Designed around protected health information</h2>
            <p class="mt-4 text-lg text-white/80 leading-relaxed">{{ $appName }} handles patient records, so it is built on the minimum-necessary principle: people see only the modules, offices and data their role requires.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('privacy') }}" class="inline-flex items-center gap-2 rounded-lg bg-white/10 ring-1 ring-white/25 px-4 py-2 text-sm font-semibold hover:bg-white/15"><i data-lucide="file-text" class="w-4 h-4"></i> Privacy Policy</a>
                <a href="{{ route('terms') }}" class="inline-flex items-center gap-2 rounded-lg bg-white/10 ring-1 ring-white/25 px-4 py-2 text-sm font-semibold hover:bg-white/15"><i data-lucide="scale" class="w-4 h-4"></i> Terms of Service</a>
            </div>
        </div>
        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach ([
                ['lock', 'HTTPS and sign-in only', 'Every page except this site and the legal pages requires authentication.'],
                ['users', 'No public sign-up', 'Accounts are created by an administrator for employees and contractors of ' . $company . '.'],
                ['key-round', 'Encrypted credentials', 'API tokens and OAuth refresh tokens are encrypted at rest and deleted when a connection is removed.'],
                ['eye-off', 'No data sales', 'Nothing is sold, rented or used to advertise to patients.'],
                ['clipboard-list', 'Audit trail', 'Sign-ins and sync activity are logged for security review.'],
                ['server', 'Isolated reporting database', 'Analytics run against a local copy, never the clinical system.'],
            ] as [$icon, $t, $d])
                <li class="reveal rounded-2xl bg-white/5 ring-1 ring-white/10 p-5">
                    <span class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center"><i data-lucide="{{ $icon }}" class="w-4 h-4"></i></span>
                    <h3 class="mt-3 font-semibold">{{ $t }}</h3>
                    <p class="mt-1 text-sm text-white/70 leading-relaxed">{{ $d }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- ========== FAQ ========== --}}
<section id="faq" class="py-20 lg:py-28">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center reveal">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-700">FAQ</span>
            <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Common questions</h2>
        </div>
        <div class="mt-10 divide-y divide-slate-200 border-y border-slate-200">
            @foreach ([
                ['Who can use ' . $appName . '?', 'Only employees, contractors and authorized agents of ' . $company . '. There is no public sign-up and the platform is not offered to other practices or vendors.'],
                ['Does it change anything in Open Dental?', 'No. The sync is read-only. The only writes to Open Dental come from features explicitly built to schedule or confirm appointments, and those are enabled per office by an administrator.'],
                ['Why does it ask for Google Ads access?', 'To read campaign reporting for the group\'s own ad accounts and compare advertising cost with the new patients and production those campaigns produce. The connection is read-only and can be revoked at any time from the integrations page or from your Google account permissions.'],
                ['How fresh are the numbers?', 'Open Dental tables sync on a schedule throughout the day, and each office shows when it last synced. Google Ads statistics are pulled daily, and the trailing window is re-pulled because Google restates conversions for several weeks.'],
                ['How do I get access or report a problem?', 'Ask your office manager or administrator to create or update your account. For anything else, email ' . $email . '.'],
            ] as [$q, $a])
                <details class="group py-5 reveal">
                    <summary class="flex items-center justify-between gap-4 cursor-pointer text-left">
                        <span class="font-semibold text-slate-900">{{ $q }}</span>
                        <span class="faq-icon shrink-0 w-7 h-7 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center"><i data-lucide="plus" class="w-4 h-4"></i></span>
                    </summary>
                    <p class="mt-3 text-sm text-slate-600 leading-relaxed pr-10">{{ $a }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- ========== CTA ========== --}}
<section class="pb-20 lg:pb-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal relative overflow-hidden rounded-3xl bg-gradient-to-br from-navy-700 to-blue-700 text-white px-8 py-12 lg:px-14 lg:py-16">
            <div class="absolute -top-24 -right-24 w-80 h-80 rounded-full bg-sky-400/30 blur-3xl" aria-hidden="true"></div>
            <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                <div class="max-w-xl">
                    <h2 class="text-3xl font-extrabold tracking-tight">Already on the team?</h2>
                    <p class="mt-3 text-white/80 text-lg">Sign in with your work account to open your workspace. Need access? Ask your office manager or administrator.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-lg bg-white text-navy-700 font-semibold px-6 py-3 shadow-lg shadow-black/20 hover:bg-blue-50">Sign in <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                    <a href="mailto:{{ $email }}" class="inline-flex items-center gap-2 rounded-lg bg-white/10 ring-1 ring-white/25 font-semibold px-6 py-3 hover:bg-white/15"><i data-lucide="mail" class="w-4 h-4"></i> Contact us</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ========== Footer ========== --}}
<footer id="contact" class="bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-1 md:grid-cols-12 gap-10">
        <div class="md:col-span-5">
            <a href="#top" class="flex items-center gap-3">
                <img src="{{ asset('public/images/logo.png') }}" alt="{{ $appName }}" class="h-10 w-auto">
            </a>
            <p class="mt-4 text-sm text-slate-600 leading-relaxed max-w-sm">The internal practice-analytics platform of {{ $company }}. Built on Open Dental data for a multi-location dental group.</p>
        </div>
        <div class="md:col-span-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Product</h3>
            <ul class="mt-4 space-y-2 text-sm">
                @foreach ($menu as [$href, $label])
                    <li><a href="{{ $href }}" class="text-slate-600 hover:text-slate-900">{{ $label }}</a></li>
                @endforeach
            </ul>
        </div>
        <div class="md:col-span-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Legal</h3>
            <ul class="mt-4 space-y-2 text-sm">
                <li><a href="{{ route('privacy') }}" class="text-slate-600 hover:text-slate-900">Privacy Policy</a></li>
                <li><a href="{{ route('terms') }}" class="text-slate-600 hover:text-slate-900">Terms of Service</a></li>
                <li><a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" rel="noopener" class="text-slate-600 hover:text-slate-900">Google API Services User Data Policy</a></li>
            </ul>
        </div>
        <div class="md:col-span-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Contact</h3>
            <ul class="mt-4 space-y-2 text-sm">
                <li class="flex items-center gap-2 text-slate-600"><i data-lucide="mail" class="w-4 h-4"></i><a href="mailto:{{ $email }}" class="hover:text-slate-900">{{ $email }}</a></li>
                <li class="flex items-center gap-2 text-slate-600"><i data-lucide="log-in" class="w-4 h-4"></i><a href="{{ route('login') }}" class="hover:text-slate-900">Sign in</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
            <span>&copy; {{ date('Y') }} {{ $company }}. All rights reserved.</span>
            <span>{{ $appName }} is for internal use only and is not available to the public.</span>
        </div>
    </div>
</footer>

<script>
    lucide.createIcons();

    (function () {
        // Header: transparent over the hero, solid once the page scrolls.
        var header = document.getElementById('site-header');
        function paintHeader() {
            var solid = window.scrollY > 24;
            header.classList.toggle('bg-navy-900/95', solid);
            header.classList.toggle('backdrop-blur', solid);
            header.classList.toggle('shadow-lg', solid);
            header.classList.toggle('shadow-black/20', solid);
            header.classList.toggle('bg-navy-900/0', !solid);
        }
        paintHeader();
        window.addEventListener('scroll', paintHeader, { passive: true });

        // Mobile menu.
        var toggle = document.getElementById('menu-toggle');
        var menu = document.getElementById('mobile-menu');
        var iconOpen = document.getElementById('menu-icon-open');
        var iconClose = document.getElementById('menu-icon-close');
        function setMenu(open) {
            menu.classList.toggle('hidden', !open);
            iconOpen.classList.toggle('hidden', open);
            iconClose.classList.toggle('hidden', !open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        }
        toggle.addEventListener('click', function () { setMenu(menu.classList.contains('hidden')); });
        menu.querySelectorAll('.mobile-link').forEach(function (a) { a.addEventListener('click', function () { setMenu(false); }); });

        // Active menu item follows the section in view.
        var links = Array.prototype.slice.call(document.querySelectorAll('.nav-link'));
        var sections = links.map(function (a) { return document.querySelector(a.getAttribute('href')); }).filter(Boolean);
        if ('IntersectionObserver' in window) {
            var spy = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) {
                    if (!e.isIntersecting) return;
                    links.forEach(function (a) { a.classList.toggle('is-active', a.getAttribute('href') === '#' + e.target.id); });
                });
            }, { rootMargin: '-40% 0px -55% 0px' });
            sections.forEach(function (s) { spy.observe(s); });

            // Reveal-on-scroll.
            var revealer = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) {
                    if (e.isIntersecting) { e.target.classList.add('is-visible'); revealer.unobserve(e.target); }
                });
            }, { threshold: 0.12 });
            document.querySelectorAll('.reveal').forEach(function (el) { revealer.observe(el); });
        } else {
            document.querySelectorAll('.reveal').forEach(function (el) { el.classList.add('is-visible'); });
        }

        // Module tabs.
        var tabs = document.querySelectorAll('[role="tab"]');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) {
                    var on = t === tab;
                    t.setAttribute('aria-selected', on ? 'true' : 'false');
                    document.getElementById(t.getAttribute('aria-controls')).classList.toggle('hidden', !on);
                });
            });
        });
    })();
</script>
</body>
</html>
