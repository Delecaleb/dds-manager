<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DDS Manager Multi-Location Dental Engine</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        
        <!-- SIDEBAR (Houses the exact 20 DDS Manager Modules) -->
        <aside class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between hidden md:flex shrink-0">
            <div class="overflow-y-auto flex-1 chunk-scrollbar">
                <!-- App Brand Header -->
                <div class="h-16 flex items-center px-6 border-b border-slate-200 gap-2 sticky top-0 bg-white z-10">
                    <i data-lucide="bar-chart-big" class="text-blue-600 w-6 h-6"></i>
                    <span class="font-bold text-lg tracking-tight text-slate-900">DDS Manager<span class="text-blue-600 font-medium"></span></span>
                </div>
                
                <!-- Complete DDS Manager Menu Architecture -->
                <div class="p-3 space-y-4">
                    
                    <!-- CORE VIEW -->
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

                    <!-- CLINICAL & PATIENT OPERATION -->
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

                    <!-- FINANCIALS & RCM -->
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

                    <!-- PORTALS & CONFIG -->
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

            <!-- Open Dental Database Real-time Bridge status -->
            <div class="p-4 border-t border-slate-200 bg-slate-50 sticky bottom-0">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> OD LiveSync Active</span>
                    <i data-lucide="database" class="w-3.5 h-3.5 text-slate-400"></i>
                </div>
            </div>
        </aside>

        <!-- MAIN VIEWPORT -->
        <div class="flex-1 flex flex-col overflow-y-auto">
            {{$slot}}
        </div>
    </div>

    <!-- Lucide Script -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>