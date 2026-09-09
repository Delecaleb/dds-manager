<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DDS Manager Multi-Location Dental Engine</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://cdn.tailwindcss.com/3.4.17" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.tailwindcss.com/3.4.17">
    <script src="https://code.jquery.com/jquery-4.0.0.min.js"
        integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{-- Shared UI: single source of truth for component CSS (design tokens, sticky
         columns/header, heatmap, modals, tabs). Loaded after Tailwind so it can override. --}}
    <link rel="stylesheet" href="{{ asset('public/css/ui.css') }}">
    {{-- Shared UI behavior (window.DDS): formatters, stacking modals, URL-driven tabs,
         date-range helpers. Loaded in <head> after jQuery so DDS is available to every
         page script (incl. those that render at parse time). --}}
    <script src="{{ asset('public/js/ui.js') }}"></script>
</head>

<body class="bg-slate-50 text-slate-800 font-sans antialiased">

    <div id="overlay-menu" class="fixed inset-0 z-[200] hidden">
        <div id="menu-backdrop" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

        <aside
            class="absolute inset-y-0 left-0 w-64 bg-white shadow-2xl flex flex-col justify-between h-full transform transition-transform duration-300">
            <div class="overflow-y-auto flex-1 chunk-scrollbar">
                <div
                    class="h-16 flex items-center justify-between px-6 border-b border-slate-200 sticky top-0 bg-white z-10">
                    <div class="flex items-center gap-2">
                        <i data-lucide="bar-chart-big" class="text-blue-600 w-6 h-6"></i>
                        <span class="font-bold text-lg tracking-tight text-slate-900">DDS Manager</span>
                    </div>
                    <button id="close-menu-btn"
                        class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 focus:outline-none">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="p-3">
                    <nav class="space-y-0.5">
                        @if(auth()->user()->hasModuleAccess('aging'))
                            <a href="{{ route('aging.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('aging.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('aging.index')) aria-current="page" @endif>
                                <i data-lucide="hourglass" class="w-4 h-4"></i> Aging
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('calendar'))
                            <a href="{{ route('calendar.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('calendar.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('calendar.index')) aria-current="page" @endif>
                                <i data-lucide="calendar" class="w-4 h-4"></i> Calendar
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('dashboard'))
                            <a href="{{ route('dashboard') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('deposits'))
                            <a href="{{ route('deposits.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('deposits.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('deposits.index')) aria-current="page" @endif>
                                <i data-lucide="file-check-2" class="w-4 h-4"></i> Deposit Slip
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('eod'))
                            <a href="{{ route('eod.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('eod.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('eod.index')) aria-current="page" @endif>
                                <i data-lucide="zap" class="w-4 h-4"></i> EOD Live
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('financials'))
                            <a href="{{ route('financials.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('financials.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('financials.index')) aria-current="page" @endif>
                                <i data-lucide="dollar-sign" class="w-4 h-4"></i> Financials
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('front-office'))
                            <a href="{{ route('front-office.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('front-office.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('front-office.index')) aria-current="page" @endif>
                                <i data-lucide="monitor" class="w-4 h-4"></i> Front Office
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('hygiene-recall'))
                            <a href="{{ route('hygiene-recall.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('hygiene-recall.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('hygiene-recall.index')) aria-current="page" @endif>
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Hygiene Recall
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('kpis'))
                            <a href="{{ route('kpis.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('kpis.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('kpis.*')) aria-current="page" @endif>
                                <i data-lucide="bar-chart-2" class="w-4 h-4"></i> KPIs
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('huddle'))
                            <a href="{{ route('huddle.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('huddle.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('huddle.index')) aria-current="page" @endif>
                                <i data-lucide="users-round" class="w-4 h-4"></i> Morning Huddle
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('operations'))
                            <a href="{{ route('operations.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('operations.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('operations.index')) aria-current="page" @endif>
                                <i data-lucide="briefcase" class="w-4 h-4"></i> Operations
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('offices'))
                            <a href="{{ route('offices.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('offices.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('offices.*')) aria-current="page" @endif>
                                <i data-lucide="building-2" class="w-4 h-4 text-blue-600"></i> Offices / Locations
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('od-explorer'))
                            <a href="{{ route('od-explorer.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('od-explorer.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('od-explorer.*')) aria-current="page" @endif>
                                <i data-lucide="database" class="w-4 h-4 text-emerald-600"></i> OD Data Explorer
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('sync-manager'))
                            <a href="{{ route('sync-manager.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('sync-manager.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('sync-manager.*')) aria-current="page" @endif>
                                <i data-lucide="cloud-lightning" class="w-4 h-4 text-amber-500"></i> Data Sync Manager
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('patients'))
                            <a href="{{ route('patients.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('patients.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('patients.index')) aria-current="page" @endif>
                                <i data-lucide="user-square" class="w-4 h-4"></i> Patient Portal
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('provider-portal'))
                            <a href="{{ route('provider-portal.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('provider-portal.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('provider-portal.*')) aria-current="page" @endif>
                                <i data-lucide="stethoscope" class="w-4 h-4"></i> Provider Portal
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('provisioner'))
                            <a href="{{ route('provisioner.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('provisioner.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('provisioner.index')) aria-current="page" @endif>
                                <i data-lucide="server-cog" class="w-4 h-4"></i> Provisioner
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('rcm'))
                            <a href="{{ route('rcm.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('rcm.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('rcm.index')) aria-current="page" @endif>
                                <i data-lucide="landmark" class="w-4 h-4"></i> RCM
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('snapshot'))
                            <a href="{{ route('snapshot.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('snapshot.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('snapshot.index')) aria-current="page" @endif>
                                <i data-lucide="camera" class="w-4 h-4"></i> Snapshot
                            </a>
                        @endif

                        @if(auth()->user()->hasModuleAccess('tx-miner'))
                            <a href="{{ route('tx-miner.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('tx-miner.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('tx-miner.index')) aria-current="page" @endif>
                                <i data-lucide="search" class="w-4 h-4"></i> Tx Miner
                            </a>
                        @endif

                        {{-- Super Admin Administration Section --}}
                        @if(auth()->user()->isSuperAdmin())
                            <div class="pt-4 pb-1 px-3 border-t border-slate-100 mt-3">
                                <span class="text-[10px] font-bold text-purple-700 uppercase tracking-wider">Administration</span>
                            </div>
                            <a href="{{ route('admin.users.index') }}"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('admin.users.*') ? 'bg-purple-50 text-purple-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                                @if(request()->routeIs('admin.users.*')) aria-current="page" @endif>
                                <i data-lucide="shield-check" class="w-4 h-4 text-purple-600"></i> User & Access
                            </a>
                        @endif
                    </nav>
                </div>
            </div>

            <div class="p-3 border-t border-slate-200 bg-slate-50 sticky bottom-0 space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-7 h-7 rounded-full bg-[#001f3f] text-emerald-400 text-xs font-bold flex items-center justify-center shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                            <span class="inline-block text-[9px] font-semibold px-1.5 py-0.2 rounded border {{ auth()->user()->getRoleBadgeClass() }}">
                                {{ auth()->user()->getRoleName() }}
                            </span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Log Out">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
    </div>

    <div class="flex flex-col h-screen overflow-hidden">

        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0 z-30">
            <div class="flex items-center gap-4">
                <button id="menu-toggle-btn"
                    class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div class="flex items-center gap-2">
                    <i data-lucide="bar-chart-big" class="text-blue-600 w-5 h-5"></i>
                    <span class="font-bold text-md tracking-tight text-slate-900">DDS Manager</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @php
                    $allOffices = \App\Models\Office::where('is_active', true)->get();
                    $activeOfficeId = \App\Models\Office::getActiveOfficeId();
                    $currentOffice = $allOffices->firstWhere('id', $activeOfficeId) ?? $allOffices->first();
                @endphp
                @if($allOffices->count() > 0)
                    <form method="POST" action="{{ route('offices.switch') }}" class="flex items-center gap-2">
                        @csrf
                        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 rounded-lg text-xs font-semibold text-slate-700 border border-slate-200">
                            <i data-lucide="building" class="w-3.5 h-3.5 text-blue-600"></i>
                            <select name="office_id" onchange="this.form.submit()" class="bg-transparent font-medium text-slate-800 text-xs focus:outline-none cursor-pointer">
                                @foreach($allOffices as $off)
                                    <option value="{{ $off->id }}" {{ $off->id == $activeOfficeId ? 'selected' : '' }}>
                                        Location: {{ $off->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    <!-- Header Live Sync Report Button -->
                    <button onclick="window.openGlobalSyncReport({{ $activeOfficeId ?? ($allOffices->first()->id ?? 1) }}, '{{ addslashes($currentOffice->name ?? 'Office') }}')" type="button"
                        class="flex items-center gap-1.5 px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-lg border border-emerald-200 shadow-xs transition-colors cursor-pointer"
                        title="Live Sync Telemetry & Health Report">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="hidden md:inline">Sync Status</span>
                    </button>
                @endif

                <!-- User Profile Dropdown -->
                <div class="relative" id="user-dropdown-wrapper">
                    <button id="user-menu-btn" type="button"
                        class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors focus:outline-none cursor-pointer">
                        <div class="w-7 h-7 rounded-full bg-[#001f3f] text-emerald-400 text-xs font-bold flex items-center justify-center shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="hidden sm:flex flex-col text-left">
                            <span class="text-xs font-bold text-slate-900 leading-tight">{{ auth()->user()->name }}</span>
                            <span class="text-[10px] text-slate-500 leading-tight">{{ auth()->user()->getRoleName() }}</span>
                        </div>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
                    </button>

                    <!-- Dropdown Panel -->
                    <div id="user-dropdown-menu"
                        class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-50 animate-in fade-in duration-100">
                        <div class="px-4 py-2.5 border-b border-slate-100">
                            <p class="text-xs font-bold text-slate-900">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                            <span class="inline-block mt-1 text-[9px] font-semibold px-2 py-0.5 rounded-full border {{ auth()->user()->getRoleBadgeClass() }}">
                                {{ auth()->user()->getRoleName() }}
                            </span>
                        </div>

                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.users.index') }}"
                                class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 hover:text-purple-700 transition-colors">
                                <i data-lucide="shield-check" class="w-4 h-4 text-purple-600"></i>
                                <span>User & Access Management</span>
                            </a>
                        @endif

                        <a href="{{ route('profile.edit') }}"
                            class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 transition-colors">
                            <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                            <span>Profile Settings</span>
                        </a>

                        <div class="border-t border-slate-100 my-1"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-2 px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 transition-colors text-left cursor-pointer">
                                <i data-lucide="log-out" class="w-4 h-4 text-rose-500"></i>
                                <span>Sign Out</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto">
            {{$slot}}
        </main>
    </div>

    <!-- Reusable Global Sync Report Modal -->
    <x-office-sync-report-modal />

    <script>
        lucide.createIcons();

        // Overlay Navigation Controls
        const menuToggleBtn = document.getElementById('menu-toggle-btn');
        const closeMenuBtn = document.getElementById('close-menu-btn');
        const overlayMenu = document.getElementById('overlay-menu');
        const menuBackdrop = document.getElementById('menu-backdrop');

        function toggleMenu() {
            overlayMenu.classList.toggle('hidden');
        }

        if (menuToggleBtn) menuToggleBtn.addEventListener('click', toggleMenu);
        if (closeMenuBtn) closeMenuBtn.addEventListener('click', toggleMenu);
        if (menuBackdrop) menuBackdrop.addEventListener('click', toggleMenu);

        // Header User Menu Dropdown Controls
        const userMenuBtn = document.getElementById('user-menu-btn');
        const userDropdownMenu = document.getElementById('user-dropdown-menu');

        if (userMenuBtn && userDropdownMenu) {
            userMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', (e) => {
                if (!userDropdownMenu.contains(e.target) && !userMenuBtn.contains(e.target)) {
                    userDropdownMenu.classList.add('hidden');
                }
            });
        }
    </script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.tailwind.js"></script>
</body>

</html>