<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jarvis Clone - Multi-Location Dental Engine</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        
        <!-- SIDEBAR (Houses the exact 20 Jarvis Modules) -->
        <aside class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between hidden md:flex shrink-0">
            <div class="overflow-y-auto flex-1 chunk-scrollbar">
                <!-- App Brand Header -->
                <div class="h-16 flex items-center px-6 border-b border-slate-200 gap-2 sticky top-0 bg-white z-10">
                    <i data-lucide="bar-chart-big" class="text-blue-600 w-6 h-6"></i>
                    <span class="font-bold text-lg tracking-tight text-slate-900">Jarvis<span class="text-blue-600 font-medium">Analytics</span></span>
                </div>
                
                <!-- Complete Jarvis Menu Architecture -->
                <div class="p-3 space-y-4">
                    
                    <!-- CORE VIEW -->
                    <div>
                        <div class="px-3 text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">Core Engines</div>
                        <nav class="space-y-0.5">
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md bg-blue-50 text-blue-700 font-semibold text-sm">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
                            </a>
                            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="calendar" class="w-4 h-4"></i> Calendar
                            </a>
                            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="camera" class="w-4 h-4"></i> Snapshot
                            </a>
                            <a href="{{route('huddle.index')}}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="users-round" class="w-4 h-4"></i> Morning Huddle
                            </a>
                        </nav>
                    </div>

                    <!-- CLINICAL & PATIENT OPERATION -->
                    <div>
                        <div class="px-3 text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">Operations</div>
                        <nav class="space-y-0.5">
                            <a href="{{ route('operations.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="briefcase" class="w-4 h-4"></i> Operations
                            </a>
                            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="monitor" class="w-4 h-4"></i> Front Office
                            </a>
                            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Hygiene Recall
                            </a>
                            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="user-square" class="w-4 h-4"></i> Patient Portal
                            </a>
                        </nav>
                    </div>

                    <!-- FINANCIALS & RCM -->
                    <div>
                        <div class="px-3 text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">Financials & RCM</div>
                        <nav class="space-y-0.5">
                            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="dollar-sign" class="w-4 h-4"></i> Financials
                            </a>
                            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="file-check-2" class="w-4 h-4"></i> Deposit Slip
                            </a>
                            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="hourglass" class="w-4 h-4"></i> Aging
                            </a>
                            <a href="{{ route('eod.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="zap" class="w-4 h-4"></i> EOD Live
                            </a>
                            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="landmark" class="w-4 h-4"></i> RCM
                            </a>
                        </nav>
                    </div>

                    <!-- BUSINESS INTELLIGENCE -->
                    <div>
                        <div class="px-3 text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">Intelligence & Mining</div>
                        <nav class="space-y-0.5">
                            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="line-chart" class="w-4 h-4"></i> KPIs
                            </a>
                            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="pickaxe" class="w-4 h-4"></i> Tx Miner
                            </a>
                            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="trending-up" class="w-4 h-4"></i> Practice Potential
                            </a>
                            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="git-commit" class="w-4 h-4"></i> Waterfall
                            </a>
                            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="file-bar-chart-2" class="w-4 h-4"></i> Reports V2
                            </a>
                        </nav>
                    </div>

                    <!-- PORTALS & CONFIG -->
                    <div>
                        <div class="px-3 text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">Portals</div>
                        <nav class="space-y-0.5">
                            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="stethoscope" class="w-4 h-4"></i> Provider Portal
                            </a>
                            <a href="#" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm">
                                <i data-lucide="server-cog" class="w-4 h-4"></i> Provisioner
                            </a>
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