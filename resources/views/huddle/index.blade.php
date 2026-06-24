<x-app-layout>
    <div class="min-h-screen flex flex-col">

        <header class="bg-white border-b border-slate-200 px-6 py-4 flex flex-wrap items-center justify-between gap-4 shadow-sm">
            <div>
                <h1 class="text-xl font-semibold text-slate-800 tracking-tight">Unified Dental Morning Huddle</h1>
                <div class="flex items-center gap-1.5 text-slate-400 text-[11px] mt-0.5">
                    <i data-lucide="calendar-range" class="w-3.5 h-3.5"></i>
                    <span>Morning Huddle</span>
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2 text-slate-600 font-medium">
                    <div class="w-7 h-7 rounded-full bg-slate-200 flex items-center justify-center text-slate-600">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </div>
                    <span>Hello, Adeniyi</span>
                </div>
                <div class="text-[11px] font-mono border-l border-slate-200 pl-4 space-y-0.5">
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-400">LEDGER</span>
                        <span class="font-bold text-slate-700">100.00%</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-400">CALENDAR</span>
                        <span class="font-bold text-slate-700">100.00%</span>
                    </div>
                </div>
            </div>
        </header>

        <div class="bg-white border-b border-slate-200 px-6 py-3 flex flex-wrap items-center justify-between gap-4 shadow-sm">
            <div class="flex flex-wrap items-center gap-6">
                <div class="space-y-0.5">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Brand</label>
                    <div class="relative">
                        <select class="appearance-none bg-white border border-slate-300 rounded px-3 py-1 text-xs font-semibold text-slate-600 pr-8 focus:outline-none min-w-[140px]">
                            <option>ALL</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400">
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>
                </div>

                <div class="space-y-0.5">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Regional Manager</label>
                    <div class="relative">
                        <select class="appearance-none bg-white border border-slate-300 rounded px-3 py-1 text-xs font-semibold text-slate-600 pr-8 focus:outline-none min-w-[140px]">
                            <option>ALL</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400">
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>
                </div>

                <div class="space-y-0.5">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Location</label>
                    <div class="relative">
                        <select class="appearance-none bg-white border border-slate-300 rounded px-3 py-1 text-xs font-semibold text-slate-600 pr-8 focus:outline-none min-w-[140px]">
                            <option>8 Mile</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400">
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center border border-slate-300 rounded overflow-hidden bg-white shadow-sm mt-3 sm:mt-0">
                <div class="bg-slate-50 px-2.5 py-1.5 border-r border-slate-300 text-slate-400">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </div>
                <div class="px-4 py-1 text-xs font-semibold text-slate-700">
                    Jun 18 2026
                </div>
            </div>
        </div>

        <main class="flex-1 p-6 space-y-6 max-w-[1600px] mx-auto w-full">
            
            <div class="bg-slate-100/60 border border-slate-200/80 px-4 py-2 text-slate-600 font-bold tracking-wide rounded">
                MORNING HUDDLE 06/18/2026
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <div class="bg-white border border-slate-200 rounded p-4 shadow-sm space-y-3">
                    <div class="bg-slate-50 p-2 font-bold text-slate-700 border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        PREVIOUS WORKING DAY <span class="text-slate-400 font-normal">Tuesday 06/16/2026</span>
                    </div>
                    
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium flex items-center gap-1">Previous Working Day's Production (gross) <i data-lucide="info" class="w-3.5 h-3.5 text-sky-500 cursor-pointer"></i></span>
                            <input type="text" class="huddle-input" value="$9,840.00">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium">Goal</span>
                            <input type="text" class="huddle-input" value="$6,428.57">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium flex items-center gap-1">Previous Working Day's Production (net) <i data-lucide="info" class="w-3.5 h-3.5 text-sky-500 cursor-pointer"></i></span>
                            <input type="text" class="huddle-input" value="$9,851.16">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium flex items-center gap-1">Previous Working Day's Collection <i data-lucide="info" class="w-3.5 h-3.5 text-sky-500 cursor-pointer"></i></span>
                            <input type="text" class="huddle-input" value="$383.16">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium flex items-center gap-1">Patient Co-pay Collection (%) <i data-lucide="info" class="w-3.5 h-3.5 text-sky-500 cursor-pointer"></i></span>
                            <input type="text" class="huddle-input" value="0">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium flex items-center gap-1">Production Per Patient <i data-lucide="info" class="w-3.5 h-3.5 text-sky-500 cursor-pointer"></i></span>
                            <input type="text" class="huddle-input" value="$703.65">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium flex items-center gap-1">New Patients (Actual) <i data-lucide="info" class="w-3.5 h-3.5 text-sky-500 cursor-pointer"></i></span>
                            <input type="text" class="huddle-input" value="1">
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded p-4 shadow-sm space-y-3">
                    <div class="bg-slate-50 p-2 font-bold text-slate-700 border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        TODAY <span class="text-slate-400 font-normal">Thursday 06/18/2026</span>
                    </div>

                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium">Today's Scheduled Production</span>
                            <input type="text" class="huddle-input" value="$0">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium">Goal</span>
                            <input type="text" class="huddle-input" value="$6,428.57">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium flex items-center gap-1">New Patients (Scheduled) <i data-lucide="info" class="w-3.5 h-3.5 text-sky-500 cursor-pointer"></i></span>
                            <input type="text" class="huddle-input" value="0">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium flex items-center gap-1">New Patients (Actual) <i data-lucide="info" class="w-3.5 h-3.5 text-sky-500 cursor-pointer"></i></span>
                            <input type="text" class="huddle-input" value="0">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium flex items-center gap-1">Hygiene Production (Scheduled) <i data-lucide="info" class="w-3.5 h-3.5 text-sky-500 cursor-pointer"></i></span>
                            <input type="text" class="huddle-input" value="$0">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium flex items-center gap-1">Hygiene Production (Actual) <i data-lucide="info" class="w-3.5 h-3.5 text-sky-500 cursor-pointer"></i></span>
                            <input type="text" class="huddle-input" value="$0">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium flex items-center gap-1">Unscheduled Treatment <i data-lucide="info" class="w-3.5 h-3.5 text-sky-500 cursor-pointer"></i></span>
                            <input type="text" class="huddle-input" value="$0">
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded p-4 shadow-sm space-y-3">
                    <div class="bg-slate-50 p-2 font-bold text-slate-700 border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        NEXT WORKING DAY <span class="text-slate-400 font-normal">Friday 06/19/2026</span>
                    </div>

                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium">Next Working Day's Scheduled Production</span>
                            <input type="text" class="huddle-input" value="$840.00">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium">Goal</span>
                            <input type="text" class="huddle-input" value="$0">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium flex items-center gap-1">New Patients (Scheduled) <i data-lucide="info" class="w-3.5 h-3.5 text-sky-500 cursor-pointer"></i></span>
                            <input type="text" class="huddle-input" value="7">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium flex items-center gap-1">Hygiene Production (Scheduled) <i data-lucide="info" class="w-3.5 h-3.5 text-sky-500 cursor-pointer"></i></span>
                            <input type="text" class="huddle-input" value="$0">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium flex items-center gap-1">Unscheduled Treatment <i data-lucide="info" class="w-3.5 h-3.5 text-sky-500 cursor-pointer"></i></span>
                            <input type="text" class="huddle-input" value="$1,152.40">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium flex items-center gap-1">Unscheduled Family Members <i data-lucide="info" class="w-3.5 h-3.5 text-sky-500 cursor-pointer"></i></span>
                            <input type="text" class="huddle-input" value="7">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium flex items-center gap-1">Unscheduled Hygiene <i data-lucide="info" class="w-3.5 h-3.5 text-sky-500 cursor-pointer"></i></span>
                            <input type="text" class="huddle-input" value="41">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded p-4 flex items-center gap-6 shadow-sm">
                <span class="font-semibold text-slate-700">Do all Pts. have recall Rsvn.?:<span class="text-red-500 ml-0.5">*</span></span>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-slate-600">
                        <input type="radio" name="recall_rsvn_huddle" value="yes" checked class="w-4 h-4 text-sky-600 focus:ring-0">
                        <span>Yes</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-slate-600">
                        <input type="radio" name="recall_rsvn_huddle" value="no" class="w-4 h-4 text-sky-600 focus:ring-0">
                        <span>No</span>
                    </label>
                </div>
            </div>

            <div class="space-y-4">
                
                <div class="border border-slate-200 rounded bg-white shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between p-3 bg-slate-50 border-b border-slate-200">
                        <span class="font-bold text-slate-700 tracking-wide">General</span>
                        <button type="button" class="bg-sky-500 hover:bg-sky-600 text-white font-bold px-3 py-0.5 rounded transition-colors text-[11px]">add</button>
                    </div>
                    <div class="p-6 text-center text-slate-400 font-medium bg-slate-50/20">No Providers data available.</div>
                </div>

                <div class="border border-slate-200 rounded bg-white shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between p-3 bg-slate-50 border-b border-slate-200">
                        <span class="font-bold text-slate-700 tracking-wide">Providers (ORT)</span>
                        <button type="button" class="bg-sky-500 hover:bg-sky-600 text-white font-bold px-3 py-0.5 rounded transition-colors text-[11px]">add</button>
                    </div>
                    <div class="p-6 text-center text-slate-400 font-medium bg-slate-50/20">No ORT data available.</div>
                </div>

                <div class="border border-slate-200 rounded bg-white shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between p-3 bg-slate-50 border-b border-slate-200">
                        <span class="font-bold text-slate-700 tracking-wide">Hygiene</span>
                        <button type="button" class="bg-sky-500 hover:bg-sky-600 text-white font-bold px-3 py-0.5 rounded transition-colors text-[11px]">add</button>
                    </div>
                    <div class="p-6 text-center text-slate-400 font-medium bg-slate-50/20">No Hygiene data available.</div>
                </div>

                <div class="border border-slate-200 rounded bg-white shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between p-3 bg-slate-50 border-b border-slate-200">
                        <span class="font-bold text-slate-700 tracking-wide">Treatment Acceptance</span>
                        <button type="button" class="bg-sky-500 hover:bg-sky-600 text-white font-bold px-3 py-0.5 rounded transition-colors text-[11px]">add</button>
                    </div>
                    <div class="p-6 text-center text-slate-400 font-medium bg-slate-50/20">No Providers data available.</div>
                </div>

            </div>

            <div class="space-y-1.5">
                <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px]">Additional Comments:</label>
                <textarea rows="4" class="w-full border border-sky-600 p-3 rounded bg-white focus:outline-none focus:ring-1 focus:ring-sky-500 font-sans text-sm text-slate-700 resize-y"></textarea>
            </div>

            <div class="pt-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs px-8 py-2.5 rounded shadow-sm transition-colors tracking-wide uppercase">
                    Submit
                </button>
            </div>

        </main>
    </div>

</x-app-layout>