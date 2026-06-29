<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DDS Manager Multi-Location Dental Engine</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.tailwind.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.tailwindcss.css">
    <script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">

    <div id="overlay-menu" class="fixed inset-0 z-50 hidden">
        <div id="menu-backdrop" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
        
        <aside class="absolute inset-y-0 left-0 w-64 bg-white shadow-2xl flex flex-col justify-between h-full transform transition-transform duration-300">
            <div class="overflow-y-auto flex-1 chunk-scrollbar">
                <div class="h-16 flex items-center justify-between px-6 border-b border-slate-200 sticky top-0 bg-white z-10">
                    <div class="flex items-center gap-2">
                        <i data-lucide="bar-chart-big" class="text-blue-600 w-6 h-6"></i>
                        <span class="font-bold text-lg tracking-tight text-slate-900">DDS Manager</span>
                    </div>
                    <button id="close-menu-btn" class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 focus:outline-none">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                
                <div class="p-3 space-y-4">
                    <div>
                        <nav class="space-y-0.5">
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
                            </a>
                            <a href="{{ route('calendar.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('calendar.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}" @if(request()->routeIs('calendar.index')) aria-current="page" @endif>
                                <i data-lucide="calendar" class="w-4 h-4"></i> Calendar
                            </a>
                            <a href="{{ route('huddle.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('huddle.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}" @if(request()->routeIs('huddle.index')) aria-current="page" @endif>
                                <i data-lucide="users-round" class="w-4 h-4"></i> Morning Huddle
                            </a>
                            <a href="{{ route('eod.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('eod.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}" @if(request()->routeIs('eod.index')) aria-current="page" @endif>
                                <i data-lucide="zap" class="w-4 h-4"></i> EOD Live
                            </a>
                            <a href="{{ route('operations.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('operations.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}" @if(request()->routeIs('operations.index')) aria-current="page" @endif>
                                <i data-lucide="briefcase" class="w-4 h-4"></i> Operations
                            </a>
                            <a href="{{ route('snapshot.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('snapshot.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}" @if(request()->routeIs('snapshot.index')) aria-current="page" @endif>
                                <i data-lucide="camera" class="w-4 h-4"></i> Snapshot
                            </a>
                        </nav>
                    </div>

                    <div>
                        <nav class="space-y-0.5">
                            <a href="{{ route('front-office.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('front-office.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}" @if(request()->routeIs('front-office.index')) aria-current="page" @endif>
                                <i data-lucide="monitor" class="w-4 h-4"></i> Front Office
                            </a>
                            <a href="{{ route('patients.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('patients.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}" @if(request()->routeIs('patients.index')) aria-current="page" @endif>
                                <i data-lucide="user-square" class="w-4 h-4"></i> Patient Portal
                            </a>
                            <span class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm text-slate-400 cursor-not-allowed opacity-50">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Hygiene Recall
                            </span>
                        </nav>
                    </div>

                    <div>
                        <nav class="space-y-0.5">
                            <span class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm text-slate-400 cursor-not-allowed opacity-50">
                                <i data-lucide="dollar-sign" class="w-4 h-4"></i> Financials
                            </span>
                            <span class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm text-slate-400 cursor-not-allowed opacity-50">
                                <i data-lucide="file-check-2" class="w-4 h-4"></i> Deposit Slip
                            </span>
                            <span class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm text-slate-400 cursor-not-allowed opacity-50">
                                <i data-lucide="hourglass" class="w-4 h-4"></i> Aging
                            </span>
                            <span class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm text-slate-400 cursor-not-allowed opacity-50">
                                <i data-lucide="landmark" class="w-4 h-4"></i> RCM
                            </span>
                        </nav>
                    </div>

                    <div>
                        <nav class="space-y-0.5">
                            <span class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm text-slate-400 cursor-not-allowed opacity-50">
                                <i data-lucide="stethoscope" class="w-4 h-4"></i> Provider Portal
                            </span>
                            <span class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-sm text-slate-400 cursor-not-allowed opacity-50">
                                <i data-lucide="server-cog" class="w-4 h-4"></i> Provisioner
                            </span>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-slate-200 bg-slate-50 sticky bottom-0">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> OD LiveSync Active</span>
                    <i data-lucide="database" class="w-3.5 h-3.5 text-slate-400"></i>
                </div>
            </div>
        </aside>
    </div>

    <div class="flex flex-col h-screen overflow-hidden">
        
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0 z-30">
            <div class="flex items-center gap-4">
                <button id="menu-toggle-btn" class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div class="flex items-center gap-2">
                    <i data-lucide="bar-chart-big" class="text-blue-600 w-5 h-5"></i>
                    <span class="font-bold text-md tracking-tight text-slate-900">DDS Manager Dashboard</span>
                </div>
            </div>
            <div class="flex items-center gap-3"></div>
        </header>

        <main class="flex-1 overflow-y-auto">
            {{$slot}}
        </main>
    </div>

    <script>
        lucide.createIcons();

        // JavaScript Interaction Logic for Overlay
        const menuToggleBtn = document.getElementById('menu-toggle-btn');
        const closeMenuBtn = document.getElementById('close-menu-btn');
        const overlayMenu = document.getElementById('overlay-menu');
        const menuBackdrop = document.getElementById('menu-backdrop');

        function toggleMenu() {
            overlayMenu.classList.toggle('hidden');
        }

        menuToggleBtn.addEventListener('click', toggleMenu);
        closeMenuBtn.addEventListener('click', toggleMenu);
        menuBackdrop.addEventListener('click', toggleMenu);
    </script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.tailwind.js"></script>
</body>
</html>