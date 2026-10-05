<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Growth Engine | Marcelo Analytics</title>
    <link rel="icon" type="image/png" href="{{ asset('public/images/logo-mark.png') }}">
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

    <div class="flex h-screen overflow-hidden">

        {{-- Persistent module nav — the analytics shell uses an overlay menu; inside the
             Growth Engine the nav stays put, because the work here is multi-step.
             $umbrellas / $current come from App\View\Components\MarketingLayout. --}}
        <aside class="w-60 shrink-0 bg-white border-r border-slate-200 text-slate-600 flex flex-col">
            <a href="{{ route('marketing.index') }}" class="h-16 flex items-center gap-2.5 px-5 border-b border-slate-200 hover:bg-slate-50 transition-colors">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-4.5 h-4.5"></i>
                </div>
                <div class="leading-tight">
                    <div class="text-sm font-bold text-slate-900 tracking-tight">Growth Engine</div>
                    <div class="text-[10px] text-slate-400">{{ $current['label'] ?? 'Home' }}</div>
                </div>
            </a>

            @if($current)
                {{-- Umbrella switcher: jump between Marketing and AI Front Desk without the hub. --}}
                <div class="px-3 pt-3">
                    <div class="grid gap-1 p-1 rounded-lg bg-slate-100" style="grid-template-columns: repeat({{ count($umbrellas) }}, minmax(0, 1fr))">
                        @foreach($umbrellas as $key => $umbrella)
                            @php $on = $key === $umbrellaKey; @endphp
                            <a href="{{ route($umbrella['home']) }}"
                                title="{{ $umbrella['label'] }}" class="flex flex-col items-center justify-center gap-0.5 px-1 py-1.5 rounded-md text-[10px] font-semibold transition-colors {{ $on ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}"
                                @if($on) aria-current="true" @endif>
                                <i data-lucide="{{ $umbrella['icon'] }}" class="w-3.5 h-3.5 shrink-0"></i>
                                <span class="truncate max-w-full">{{ $umbrella['short'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <nav class="flex-1 overflow-y-auto p-3 space-y-5">
                @if($office)
                    {{-- Inside one office: the way back up to every office. --}}
                    <a href="{{ route('marketing.front-desk.index') }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i data-lucide="building-2" class="w-4 h-4"></i>
                        <span>Organization</span>
                    </a>
                @endif
                @if($current)
                    @foreach($current['nav'] as $group)
                        <div>
                            <div class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                {{ $group['label'] }}
                            </div>
                            <div class="space-y-0.5">
                                @foreach($group['items'] as $item)
                                    @php $active = request()->routeIs(...(array) ($item['active'] ?? $item['route'])); @endphp
                                    <a href="{{ route($item['route'], $item['params'] ?? []) }}"
                                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] transition-colors {{ $active ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                        @if($active) aria-current="page" @endif>
                                        <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4"></i>
                                        <span>{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div>
                        <div class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Workspaces</div>
                        <div class="space-y-0.5">
                            @foreach($umbrellas as $umbrella)
                                <a href="{{ route($umbrella['home']) }}"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                                    <i data-lucide="{{ $umbrella['icon'] }}" class="w-4 h-4"></i>
                                    <span>{{ $umbrella['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </nav>

            @if($office && count($offices) > 1)
                {{-- Office switcher: the same page, for another office. --}}
                <div class="px-3 pt-3 border-t border-slate-200">
                    <label for="fdOfficeSwitch" class="px-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Office</label>
                    @php $routeName = request()->route()->getName(); $routeParams = request()->route()->parameters(); @endphp
                    <select id="fdOfficeSwitch" onchange="window.location = this.value"
                        class="mt-1 w-full rounded-lg bg-white border border-slate-200 text-[12px] font-medium text-slate-700 px-2.5 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @foreach($offices as $key => $loc)
                            <option class="text-slate-900" value="{{ route($routeName, array_merge($routeParams, ['location' => $key])) }}" @selected($key === $office->key())>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="p-3 border-t border-slate-200">
                <a href="{{ route(auth()->user()->homeRoute()) }}"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                    <img src="{{ asset('public/images/logo-mark.png') }}" alt="" class="w-5 h-5">
                    <span>Back to Marcelo Analytics</span>
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

                    @if($frontDeskSource)
                        {{-- Where this page's call data comes from, and the sample-data preview toggle. --}}
                        @php $previewing = $frontDeskSource instanceof AppDomainFrontDeskSampleFrontDeskSource; @endphp
                        @unless($frontDeskSource->isLive())
                            <span class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[11px] font-semibold {{ $previewing ? 'border-violet-200 bg-violet-50 text-violet-800' : 'border-amber-200 bg-amber-50 text-amber-800' }}">
                                <i data-lucide="{{ $previewing ? 'eye' : 'flask-conical' }}" class="w-3.5 h-3.5"></i> {{ $frontDeskSource->label() }}
                            </span>
                            <a href="{{ request()->fullUrlWithQuery(['preview' => $previewing ? 0 : 1]) }}"
                                class="hidden md:inline text-[11px] font-bold text-slate-500 hover:text-slate-800">
                                {{ $previewing ? 'Exit preview' : 'Preview with sample data' }}
                            </a>
                        @endunless
                    @else
                        <span class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 text-[11px] font-semibold">
                            <i data-lucide="flask-conical" class="w-3.5 h-3.5"></i> Template — no live data yet
                        </span>
                    @endif

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
