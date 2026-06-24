<x-app-layout>
    <!-- HEADER -->
            <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between shrink-0 sticky top-0 z-10">
                <div class="flex items-center gap-4">
                    <!-- Global Multi-Location Context Switcher -->
                    <div class="relative">
                        <select class="appearance-none bg-slate-50 border border-slate-200 rounded-lg pl-3 pr-10 py-1.5 text-sm font-semibold text-slate-900 focus:outline-none focus:border-blue-500 focus:bg-white cursor-pointer transition-colors">
                            <option value="all">All Locations (Consolidated)</option>
                            <option value="downtown" selected>Downtown Dental Care</option>
                            <option value="northside">Northside Endodontics</option>
                            <option value="south">Southwest Ortho Clinic</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Profile & Data State Indicators -->
                <div class="flex items-center gap-4">
                    <div class="text-xs bg-emerald-50 text-emerald-700 font-semibold px-2.5 py-1 rounded border border-emerald-200 flex items-center gap-1.5">
                        <i data-lucide="check-circle" class="w-3 h-3"></i> Open Dental Inbound Match 100%
                    </div>
                    <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs">JA</div>
                        <span class="text-sm font-medium text-slate-700 hidden md:inline">Admin Controller</span>
                    </div>
                </div>
            </header>

            <!-- WORKSPACE INTERFACE CONTAINER -->
            <main class="p-6 space-y-6 max-w-7xl w-full mx-auto">
                
                <!-- JARVIS SPECIFIC KPI HEADER ROW -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Gross Production (MTD)</div>
                        <div class="text-3xl font-extrabold text-slate-900">$142,380</div>
                        <div class="text-xs text-emerald-600 mt-2 font-medium flex items-center gap-1">
                            <i data-lucide="trending-up" class="w-3 h-3"></i> +4.2% vs target huddle model
                        </div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Net Collection Rate</div>
                        <div class="text-3xl font-extrabold text-slate-900">97.4%</div>
                        <div class="text-xs text-slate-500 mt-2">Adjusted inside RCM system</div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tx Acceptance Ratio</div>
                        <div class="text-3xl font-extrabold text-slate-900">41.8%</div>
                        <div class="text-xs text-blue-600 mt-2 font-medium">Pulled via Tx Miner engine</div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Overdue Aging (>90)</div>
                        <div class="text-3xl font-extrabold text-red-600">$18,450</div>
                        <div class="text-xs text-red-500 mt-2 font-medium">Requires front office batch</div>
                    </div>
                </div>

                <!-- MAIN INTERACTIVE LAYOUT STRIPS -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    
                    <!-- LIVE EOD MONITOR (Cloned tracking concept) -->
                    <div class="bg-white border border-slate-200 rounded-xl xl:col-span-2 shadow-sm flex flex-col">
                        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                            <h2 class="font-bold text-slate-900 flex items-center gap-2">
                                <i data-lucide="activity" class="w-4 h-4 text-blue-600"></i>
                                EOD Live Tracking & Ledger Alignment
                            </h2>
                            <span class="text-xs font-medium text-blue-600 hover:underline cursor-pointer">Open Full Details</span>
                        </div>
                        <div class="p-5">
                            <div class="border border-slate-200 rounded-lg overflow-hidden">
                                <div class="grid grid-cols-3 bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 p-3">
                                    <div>Metric Pillar</div>
                                    <div>Open Dental Ledger Value</div>
                                    <div class="text-right">Jarvis Target Allocation</div>
                                </div>
                                <div class="divide-y divide-slate-100 text-sm text-slate-700">
                                    <div class="grid grid-cols-3 p-3">
                                        <div class="font-medium text-slate-900">Patient Collections</div>
                                        <div class="font-mono">$2,450.00</div>
                                        <div class="font-mono text-right text-emerald-600">$2,450.00 (Balanced)</div>
                                    </div>
                                    <div class="grid grid-cols-3 p-3">
                                        <div class="font-medium text-slate-900">Insurance EFT</div>
                                        <div class="font-mono">$4,100.00</div>
                                        <div class="font-mono text-right text-emerald-600">$4,100.00 (Balanced)</div>
                                    </div>
                                    <div class="grid grid-cols-3 p-3">
                                        <div class="font-medium text-slate-900">Unsubmitted Writeoffs</div>
                                        <div class="font-mono">$890.00</div>
                                        <div class="font-mono text-right text-amber-600">$0.00 (Pending Review)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MULTI-LOCATION DRILL DOWN -->
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm flex flex-col">
                        <div class="p-5 border-b border-slate-200">
                            <h2 class="font-bold text-slate-900 flex items-center gap-2">
                                <i data-lucide="network" class="w-4 h-4 text-blue-600"></i>
                                Cross-Location Potential
                            </h2>
                        </div>
                        <div class="p-5 space-y-3">
                            <div class="p-3 border border-slate-200 rounded-lg flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-bold text-slate-900">Downtown Dental Care</div>
                                    <div class="text-xs text-slate-400">Database Instance #01</div>
                                </div>
                                <span class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded font-bold">98% KPI Metric</span>
                            </div>
                            <div class="p-3 border border-slate-200 rounded-lg flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-bold text-slate-900">Northside Endodontics</div>
                                    <div class="text-xs text-slate-400">Database Instance #02</div>
                                </div>
                                <span class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded font-bold">84% KPI Metric</span>
                            </div>
                        </div>
                    </div>

                </div>

            </main>
</x-app-layout>