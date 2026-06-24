<x-app-layout>
    <header class="bg-white border-b border-slate-200 px-6 py-3 flex flex-wrap items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-2 text-slate-500 font-medium">
            <i data-lucide="link-2" class="w-4 h-4"></i>
            <span>EOD Live</span>
        </div>
    </header>

    <div class="bg-white border-b border-slate-200 px-6 py-4 flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-6">
            <div class="space-y-1">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Brand</label>
                <div class="relative">
                    <select class="appearance-none bg-white border border-slate-300 rounded px-3 py-1 text-xs font-medium text-slate-700 pr-8 focus:outline-none min-w-[180px]">
                        <option>Detroit Dental Specialist</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400">
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Regional Manager</label>
                <div class="relative">
                    <select class="appearance-none bg-white border border-slate-300 rounded px-3 py-1 text-xs font-medium text-slate-700 pr-8 focus:outline-none min-w-[120px]">
                        <option>ALL</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400">
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Location</label>
                <div class="relative">
                    <select class="appearance-none bg-white border border-slate-300 rounded px-3 py-1 text-xs font-medium text-slate-700 pr-8 focus:outline-none min-w-[120px]">
                        <option>8 Mile</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400">
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button class="bg-[#22c55e] hover:bg-emerald-600 text-white p-1.5 rounded transition-colors shadow-sm">
                <i data-lucide="download" class="w-4 h-4"></i>
            </button>
            <div class="flex items-center border border-slate-300 rounded overflow-hidden bg-white">
                <div class="bg-slate-100 px-2.5 py-1.5 border-r border-slate-300 text-slate-500">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </div>
                <div class="px-4 py-1 text-xs font-medium text-slate-700">
                    Jun 18 2026
                </div>
            </div>
        </div>
    </div>

    <main class="p-6 space-y-4 max-w-[1400px] mx-auto">
        
        <div class="bg-sky-50 border border-sky-100 rounded text-slate-700 p-3 font-medium text-xs">
            Before generating and sending a report, please check sync percentages first to ensure figures are correct.
        </div>

        <div class="bg-white border border-slate-200 rounded shadow-sm">
            
            <div class="bg-white border-b border-slate-100 px-4 py-2.5 font-bold text-slate-700 text-sm">
                EOD
            </div>

            <div class="p-5 grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-2 space-y-4">
                    
                    <div class="bg-slate-50 border border-slate-200 p-3 rounded text-slate-600">
                        This data is generated straight from our DB records.
                    </div>
                    <div class="bg-slate-50 border border-slate-200 p-3 rounded text-slate-600">
                        Fields marked with an (<span class="text-red-500">*</span>) are mandatory fields that can be manually entered but are required fields.
                    </div>

                    <form class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 pt-4">
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Was the office open today?:<span class="text-red-500">*</span></span>
                                <div class="flex items-center gap-4 w-[140px]">
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" name="office_open" value="yes" checked class="accent-[#001f3f] w-4 h-4"> Yes
                                    </label>
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" name="office_open" value="no" class="accent-[#001f3f] w-4 h-4"> No
                                    </label>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Date:<span class="text-red-500">*</span></span>
                                <input type="text" class="app-input app-input-left" value="18-Jun-2026" readonly>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Office:<span class="text-red-500">*</span></span>
                                <input type="text" class="app-input app-input-left" value="8 Mile" readonly>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Monthly Booked Production:</span>
                                <input type="text" class="app-input" value="$30,569.15">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Daily Net Production:<span class="text-red-500">*</span></span>
                                <input type="text" class="app-input font-bold" value="$0">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Daily Gross Production:<span class="text-red-500">*</span></span>
                                <input type="text" class="app-input font-bold" value="$0">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Office Adjusted Daily Production (ADP):</span>
                                <input type="text" class="app-input" value="$0">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Office Scheduled VS Goal (monthly):</span>
                                <input type="text" class="app-input" value="22.64%">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Oral Surgery:</span>
                                <input type="text" class="app-input" value="$0">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Total Adjustment:<span class="text-red-500">*</span></span>
                                <input type="text" class="app-input" value="$0">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Daily Collections:</span>
                                <input type="text" class="app-input" value="$0">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Number of Providers:<span class="text-red-500">*</span></span>
                                <input type="text" class="app-input" value="0">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Case Acceptance:</span>
                                <input type="text" class="app-input" value="0.00%">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">New Patients:<span class="text-red-500">*</span></span>
                                <input type="text" class="app-input" value="0">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Total Patients Seen:<span class="text-red-500">*</span></span>
                                <input type="text" class="app-input" value="0">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Patients w/ Missing Referral:</span>
                                <input type="text" class="app-input" value="0">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium"># Of unsched treatment calls made today:<span class="text-red-500">*</span></span>
                                <input type="text" class="app-input" value="0">
                            </div>

                            <div class="flex items-center justify-between gap-4 pt-1">
                                <span class="font-medium">Do all Pts. have recall Rsvn.?:<span class="text-red-500">*</span></span>
                                <div class="flex items-center gap-4 w-[140px]">
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" name="recall_rsvn" value="yes" checked class="accent-[#001f3f] w-4 h-4"> Yes
                                    </label>
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" name="recall_rsvn" value="no" class="accent-[#001f3f] w-4 h-4"> No
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="bg-slate-50 p-1.5 font-bold text-slate-700 border-b border-slate-200">
                                Month-to-Date
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Office MTD Adjust Production VS Goal:</span>
                                <input type="text" class="app-input" value="21.99%">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Office MTD Collections VS Goal:</span>
                                <input type="text" class="app-input" value="33.46%">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Net Production:<span class="text-red-500">*</span></span>
                                <input type="text" class="app-input" value="$29,679.15">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Gross Production:<span class="text-red-500">*</span></span>
                                <input type="text" class="app-input" value="$32,565.00">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Adjustment:<span class="text-red-500">*</span></span>
                                <input type="text" class="app-input text-red-600" value="-$2,885.85">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Collection:<span class="text-red-500">*</span></span>
                                <input type="text" class="app-input" value="$45,165.78">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">New Patients:<span class="text-red-500">*</span></span>
                                <input type="text" class="app-input" value="8">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Total Patients:<span class="text-red-500">*</span></span>
                                <input type="text" class="app-input" value="88">
                            </div>

                            <div class="bg-slate-50 p-1.5 font-bold text-slate-700 border-b border-slate-200 mt-2">
                                Office Goals
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Monthly Production Goal:<span class="text-red-500">*</span></span>
                                <input type="text" class="app-input" value="$135,000.00">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Scheduled Production:</span>
                                <input type="text" class="app-input" value="$890.00">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Production Needed to Achieve goal:</span>
                                <input type="text" class="app-input" value="$105,320.85">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Remaining days to EOM:<span class="text-red-500">*</span></span>
                                <input type="text" class="app-input" value="12">
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">Project daily production needed to achieve goal:</span>
                                <input type="text" class="app-input" value="$8,776.74">
                            </div>
                        </div>

                        <div class="md:col-span-2 pt-6 space-y-4">
                            
                            <div class="border border-slate-100 rounded bg-slate-50/50">
                                <div class="flex items-center justify-between p-2 bg-slate-50 border-b border-slate-100">
                                    <span class="font-bold text-slate-700">General</span>
                                    <button type="button" class="bg-[#0ea5e9] text-white text-[11px] font-bold px-2.5 py-0.5 rounded">add</button>
                                </div>
                                <div class="p-4 text-center text-slate-400 font-medium">No Providers data available.</div>
                            </div>

                            <div class="border border-slate-100 rounded bg-slate-50/50">
                                <div class="flex items-center justify-between p-2 bg-slate-50 border-b border-slate-100">
                                    <span class="font-bold text-slate-700">Providers (ORT)</span>
                                    <button type="button" class="bg-[#0ea5e9] text-white text-[11px] font-bold px-2.5 py-0.5 rounded">add</button>
                                </div>
                                <div class="p-4 text-center text-slate-400 font-medium">No ORT data available.</div>
                            </div>

                            <div class="border border-slate-100 rounded bg-slate-50/50">
                                <div class="flex items-center justify-between p-2 bg-slate-50 border-b border-slate-100">
                                    <span class="font-bold text-slate-700">Hygiene</span>
                                    <button type="button" class="bg-[#0ea5e9] text-white text-[11px] font-bold px-2.5 py-0.5 rounded">add</button>
                                </div>
                                <div class="p-4 text-center text-slate-400 font-medium">No Hygiene data available.</div>
                            </div>

                            <div class="border border-slate-100 rounded bg-slate-50/50">
                                <div class="flex items-center justify-between p-2 bg-slate-50 border-b border-slate-100">
                                    <span class="font-bold text-slate-700">Treatment Acceptance</span>
                                    <button type="button" class="bg-[#0ea5e9] text-white text-[11px] font-bold px-2.5 py-0.5 rounded">add</button>
                                </div>
                                <div class="p-4 text-center text-slate-400 font-medium">No Providers data available.</div>
                            </div>

                            <div class="space-y-1 pt-2">
                                <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px]">Additional Comments:</label>
                                <textarea rows="3" class="w-full border border-[#0ea5e9] p-3 rounded focus:outline-none focus:box-shadow resize-y"></textarea>
                            </div>

                            <div class="pt-2">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-6 py-2 rounded shadow-sm transition-colors">
                                    Submit
                                </button>
                            </div>
                        </div>

                    </form>
                </div>

                <div class="space-y-4">
                    <div class="border border-slate-200 rounded overflow-hidden">
                        <div class="bg-slate-50 px-4 py-2 border-b border-slate-200 font-bold text-slate-700">
                            File History
                        </div>
                        <div class="p-4 bg-sky-50/30 text-slate-500 border border-dashed border-slate-200 m-3 rounded text-center">
                            No history saved yet.
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </main>
</x-app-layout>