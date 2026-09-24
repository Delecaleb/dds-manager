<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Growth Engine | DDS Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://code.jquery.com/jquery-4.0.0.min.js"
        integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>
    {{-- Same shared UI as the analytics app: one set of design tokens, tables, modals,
         tabs and formatters across both shells (public/css/ui.css + window.DDS). --}}
    <link rel="stylesheet" href="{{ asset('public/css/ui.css') }}">
    <script src="{{ asset('public/js/ui.js') }}"></script>
</head>

<body class="bg-slate-50 text-slate-800 font-sans antialiased">

    @php
        /* Growth Engine navigation. Each entry is a route name under the marketing. prefix,
           so adding a page is one line here plus its route. */
        $marketingNav = [
            [
                'label' => 'Measure',
                'items' => [
                    ['route' => 'marketing.index', 'icon' => 'gauge', 'label' => 'Overview'],
                    ['route' => 'marketing.funnel', 'icon' => 'filter', 'label' => 'Attribution Funnel'],
                    ['route' => 'marketing.channels', 'icon' => 'share-2', 'label' => 'Channels'],
                    ['route' => 'marketing.campaigns', 'icon' => 'megaphone', 'label' => 'Campaigns'],
                ],
            ],
            [
                'label' => 'Act',
                'items' => [
                    ['route' => 'marketing.leads', 'icon' => 'users', 'label' => 'Leads'],
                    ['route' => 'marketing.automations', 'icon' => 'workflow', 'label' => 'Automations'],
                    ['route' => 'marketing.alerts', 'icon' => 'bell-ring', 'label' => 'Alerts'],
                ],
            ],
            [
                'label' => 'Configure',
                'items' => [
                    ['route' => 'marketing.integrations', 'icon' => 'plug', 'label' => 'Integrations'],
                    ['route' => 'marketing.settings', 'icon' => 'sliders', 'label' => 'Settings'],
                ],
            ],
        ];
    @endphp

    <div class="flex h-screen overflow-hidden">

        {{-- Persistent module nav — the analytics shell uses an overlay menu; inside the
             Growth Engine the nav stays put, because the work here is multi-step. --}}
        <aside class="w-60 shrink-0 bg-[#0b1220] text-slate-300 flex flex-col">
            <div class="h-16 flex items-center gap-2.5 px-5 border-b border-white/10">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-4.5 h-4.5"></i>
                </div>
                <div class="leading-tight">
                    <div class="text-sm font-bold text-white tracking-tight">Growth Engine</div>
                    <div class="text-[10px] text-slate-400">Marketing</div>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto p-3 space-y-5">
                @foreach($marketingNav as $group)
                    <div>
                        <div class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                            {{ $group['label'] }}
                        </div>
                        <div class="space-y-0.5">
                            @foreach($group['items'] as $item)
                                @php $active = request()->routeIs($item['route']); @endphp
                                <a href="{{ route($item['route']) }}"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] transition-colors {{ $active ? 'bg-emerald-500/15 text-emerald-300 font-semibold' : 'text-slate-300 hover:bg-white/5 hover:text-white font-medium' }}"
                                    @if($active) aria-current="page" @endif>
                                    <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4"></i>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>

            <div class="p-3 border-t border-white/10">
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] font-medium text-slate-300 hover:bg-white/5 hover:text-white transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Back to DDS Manager</span>
                </a>
            </div>
        </aside>

        <div class="flex flex-col flex-1 overflow-hidden">

            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0 z-30">
                <div class="min-w-0">
                    <h1 class="text-sm font-bold text-slate-900 truncate">{{ $title ?? 'Overview' }}</h1>
                    @isset($subtitle)
                        <p class="text-[11px] text-slate-500 truncate">{{ $subtitle }}</p>
                    @endisset
                </div>

                <div class="flex items-center gap-3">
                    {{ $toolbar ?? '' }}

                    <span class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 text-[11px] font-semibold">
                        <i data-lucide="flask-conical" class="w-3.5 h-3.5"></i> Template — no live data yet
                    </span>

                    <div class="flex items-center gap-2 pl-3 border-l border-slate-200">
                        <div class="w-7 h-7 rounded-full bg-[#0b1220] text-emerald-400 text-xs font-bold flex items-center justify-center shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="hidden sm:flex flex-col text-left leading-tight">
                            <span class="text-xs font-bold text-slate-900">{{ auth()->user()->name }}</span>
                            <span class="text-[10px] text-slate-500">{{ auth()->user()->getRoleName() }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.tailwind.js"></script>
</body>

</html>
