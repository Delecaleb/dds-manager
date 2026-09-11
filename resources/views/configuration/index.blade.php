<x-app-layout>
    <style>
        .dds-toggle-track {
            width: 2.75rem;
            height: 1.375rem;
            background-color: #cbd5e1;
            border-radius: 9999px;
            position: relative;
            transition: background-color 0.2s ease-in-out;
            cursor: pointer;
        }
        .dds-toggle-knob {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 1.125rem;
            height: 1.125rem;
            background-color: #ffffff;
            border-radius: 9999px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.15);
            transition: transform 0.2s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .dds-toggle-check {
            width: 0.65rem;
            height: 0.65rem;
            color: #00bfa5;
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
        }
        .dds-toggle-input:checked + .dds-toggle-track {
            background-color: #00bfa5;
        }
        .dark .dds-toggle-track {
            background-color: #4b5563;
        }
        .dark .dds-toggle-input:checked + .dds-toggle-track {
            background-color: #00bfa5;
        }
        .dds-toggle-input:checked + .dds-toggle-track .dds-toggle-knob {
            transform: translateX(1.375rem);
        }
        .dds-toggle-input:checked + .dds-toggle-track .dds-toggle-check {
            opacity: 1;
        }
        .border-ja-green-200 {
            border-color: #00bfa5 !important;
        }
        .bg-ja-green-200 {
            background-color: #00bfa5 !important;
        }
        .text-ja-green-200 {
            color: #00bfa5 !important;
        }
        .hover\:bg-ja-green-200:hover {
            background-color: #00bfa5 !important;
        }
        .hover\:text-ja-green-200:hover {
            color: #00bfa5 !important;
        }
        .hover\:border-ja-green-200:hover {
            border-color: #00bfa5 !important;
        }
        .focus\:border-ja-green-200:focus {
            border-color: #00bfa5 !important;
        }
        .bg-switch {
            background-color: #cbd5e1;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        .bg-switch:checked {
            background-color: #00bfa5 !important;
            border-color: #00bfa5 !important;
            background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
            background-position: right 0.25rem center;
            background-repeat: no-repeat;
            background-size: 1rem 1rem;
        }
        .bg-light-green {
            background-color: #00bfa5 !important;
        }
        .dark .dark\:bg-dark-green {
            background-color: #00897b !important;
        }
        .enable-tag {
            font-size: 0.75rem;
            line-height: 1;
            padding: 0.5rem 0.75rem;
            border-radius: 0.125rem;
        }
        .multiselect {
            box-sizing: border-box;
            display: block;
            position: relative;
            min-height: 36px;
            text-align: left;
            color: #35495e;
        }
        .multiselect.w-48 {
            width: 12rem !important;
        }
        .multiselect.w-56 {
            width: 14rem !important;
        }
        .multiselect.w-40 {
            width: 10rem !important;
        }
        .multiselect.w-full {
            width: 100% !important;
        }
        .multiselect__select {
            position: absolute;
            width: 36px;
            height: 36px;
            right: 1px;
            top: 1px;
            padding: 4px 8px;
            text-align: center;
            transition: transform 0.2s ease;
            cursor: pointer;
        }
        .multiselect__select:before {
            position: relative;
            right: 0;
            top: 65%;
            color: #999;
            margin-top: 4px;
            border-style: solid;
            border-width: 5px 5px 0 5px;
            border-color: #999 transparent transparent transparent;
            content: "";
            display: inline-block;
        }
        .multiselect__tags {
            min-height: 36px;
            display: block;
            padding: 8px 36px 0 10px;
            border-radius: 2px;
            border: 1px solid #e2e8f0;
            background: #fff;
            font-size: 12px;
        }
        .multiselect__placeholder {
            color: #94a3b8;
            display: inline-block;
            margin-bottom: 6px;
            font-size: 12px;
        }
        .multiselect__single {
            position: relative;
            display: inline-block;
            min-height: 18px;
            line-height: 18px;
            border: none;
            background: transparent;
            padding: 0;
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 6px;
            vertical-align: top;
            font-size: 12px;
        }
        .multiselect__content-wrapper {
            position: absolute;
            background: #fff;
            width: 100%;
            max-height: 300px;
            overflow: auto;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-bottom-left-radius: 2px;
            border-bottom-right-radius: 2px;
            z-index: 50;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .multiselect__content-wrapper.hidden,
        .multiselect__content-wrapper[hidden],
        .hidden {
            display: none !important;
        }
        .multiselect__content {
            list-style: none;
            display: inline-block;
            padding: 0;
            margin: 0;
            min-width: 100%;
            vertical-align: top;
        }
        .multiselect__element {
            display: block;
        }
        .multiselect__option {
            display: block;
            padding: 8px 12px;
            min-height: 32px;
            line-height: 16px;
            text-decoration: none;
            text-transform: none;
            vertical-align: middle;
            position: relative;
            cursor: pointer;
            white-space: nowrap;
            font-size: 12px;
            color: #334155;
        }
        .multiselect__option:hover,
        .multiselect__option--highlight {
            background: #00bfa5 !important;
            outline: none;
            color: #fff !important;
        }
        .multiselect__option--selected {
            background: #f1f5f9;
            color: #00bfa5;
            font-weight: 700;
        }
        .dark .multiselect__tags {
            background: #1f2937;
            border-color: #374151;
            color: #fff;
        }
        .dark .multiselect__content-wrapper {
            background: #1f2937;
            border-color: #374151;
        }
        .dark .multiselect__option {
            color: #f3f4f6;
        }
        .dark .multiselect__option--selected {
            background: #374151;
            color: #00bfa5;
        }
    </style>

    <div class="bg-[#f8fafc] text-slate-800 font-sans antialiased min-h-full">
        <!-- Top Header & Tabs Bar -->
        <div class="bg-white border-b border-slate-200 sticky top-0 z-20 shadow-xs">
            <div class="px-8 pt-6 pb-0 max-w-[1600px] mx-auto">
                <div class="flex items-center justify-between pb-5">
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Configuration</h1>
                    
                    <button type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#001f3f] text-[#00bfa5] font-bold text-xs hover:bg-[#002e5c] transition-colors border border-emerald-500/20 shadow-sm cursor-pointer">
                        <i data-lucide="book-open" class="w-4 h-4 text-[#00bfa5]"></i>
                        <span>Quick Start Guide</span>
                    </button>
                </div>

                <!-- Tabs Navigation -->
                <nav id="config-tab-nav" class="-mb-px flex space-x-6 overflow-x-auto chunk-scrollbar pb-0" aria-label="Configuration Tabs">
                    @foreach($tabs as $slug => $label)
                        @php
                            $isActive = ($activeTab === $slug);
                            $defaultSub = match($slug) {
                                'kpis' => 'main/hygiene',
                                'code-mapping' => 'services',
                                'calendar' => 'daily-schedule',
                                'goals' => 'office',
                                'snapshot' => 'default',
                                'huddle' => 'yesterday',
                                'eod' => 'basics',
                                default => '',
                            };
                            $url = url('/configuration/' . $slug . ($defaultSub ? '/' . $defaultSub : ''));
                        @endphp
                        <a href="{{ $url }}"
                           data-tab="{{ $slug }}"
                           data-default-subtab="{{ $defaultSub }}"
                           class="config-tab-link whitespace-nowrap pb-3 border-b-2 text-xs font-semibold transition-all duration-150 flex items-center gap-1.5 {{ $isActive ? 'border-[#00bfa5] text-slate-900 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}">
                            <span>{{ $label }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>

        <!-- Main Content Area -->
        <main class="p-8 max-w-[1600px] mx-auto space-y-6">
            <!-- TAB: Basic -->
            <div id="tab-panel-basic" class="config-tab-panel {{ $activeTab === 'basic' ? '' : 'hidden' }} space-y-6">
                <!-- Info Alert Banner -->
                <div class="bg-[#dcf0fa] border border-[#b8e0f5] text-[#1b4d6b] rounded-md px-4 py-3 flex items-start gap-3 text-xs shadow-xs">
                    <div class="w-4 h-4 rounded-full border border-[#1b4d6b] flex items-center justify-center shrink-0 text-[10px] font-bold mt-0.5">
                        i
                    </div>
                    <div class="leading-relaxed">
                        <span class="font-bold">Info:</span> If you have other Jarvis browser windows open, please use your browser's refresh functionality to ensure settings load properly after making any changes in the Configuration module.
                    </div>
                </div>

                <div class="bg-white rounded-lg border border-slate-200/90 shadow-sm p-8 space-y-8">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Basic Settings</h2>
                    </div>

                    <!-- 1. Display Production Type -->
                    <div class="space-y-3">
                        <h3 class="text-sm font-bold text-slate-900">Display Production Type</h3>
                        <div class="border border-slate-200 rounded-lg overflow-hidden">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold">
                                        <th class="px-5 py-2.5 font-bold">Filter</th>
                                        <th class="px-5 py-2.5 font-bold text-right w-36">Enabled</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bg-[#f8fafc] border-b border-slate-100 hover:bg-slate-100/60 transition-colors">
                                        <td class="px-5 py-3 font-medium text-slate-800">Gross Production</td>
                                        <td class="px-5 py-3 text-right">
                                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                                <input type="checkbox" name="display_production[gross]" class="sr-only dds-toggle-input" checked>
                                                <div class="dds-toggle-track">
                                                    <div class="dds-toggle-knob">
                                                        <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr class="bg-white border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                        <td class="px-5 py-3 font-medium text-slate-800">Net Production</td>
                                        <td class="px-5 py-3 text-right">
                                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                                <input type="checkbox" name="display_production[net]" class="sr-only dds-toggle-input" checked>
                                                <div class="dds-toggle-track">
                                                    <div class="dds-toggle-knob">
                                                        <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr class="bg-[#f8fafc] hover:bg-slate-100/60 transition-colors">
                                        <td class="px-5 py-3 font-medium text-slate-800">Adjustment</td>
                                        <td class="px-5 py-3 text-right">
                                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                                <input type="checkbox" name="display_production[adjustment]" class="sr-only dds-toggle-input" checked>
                                                <div class="dds-toggle-track">
                                                    <div class="dds-toggle-knob">
                                                        <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </label>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 2. Dashboard Visits Display -->
                    <div class="space-y-3">
                        <h3 class="text-sm font-bold text-slate-900">Dashboard Visits Display</h3>
                        <div class="border border-slate-200 rounded-lg overflow-hidden">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold">
                                        <th class="px-5 py-2.5 font-bold">Filter</th>
                                        <th class="px-5 py-2.5 font-bold text-right w-36">Enabled</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bg-[#f8fafc] border-b border-slate-100 hover:bg-slate-100/60 transition-colors">
                                        <td class="px-5 py-3 font-medium text-slate-800">Total New Patient Tile</td>
                                        <td class="px-5 py-3 text-right">
                                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                                <input type="checkbox" name="dashboard_visits[new_patient_tile]" class="sr-only dds-toggle-input" checked>
                                                <div class="dds-toggle-track">
                                                    <div class="dds-toggle-knob">
                                                        <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr class="bg-white border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                        <td class="px-5 py-3 font-medium text-slate-800">New Patient Visits Figures Graph</td>
                                        <td class="px-5 py-3 text-right">
                                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                                <input type="checkbox" name="dashboard_visits[new_patient_graph]" class="sr-only dds-toggle-input" checked>
                                                <div class="dds-toggle-track">
                                                    <div class="dds-toggle-knob">
                                                        <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr class="bg-[#f8fafc] hover:bg-slate-100/60 transition-colors">
                                        <td class="px-5 py-3 font-medium text-slate-800">Patient Visits Figures Graph</td>
                                        <td class="px-5 py-3 text-right">
                                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                                <input type="checkbox" name="dashboard_visits[patient_visits_graph]" class="sr-only dds-toggle-input" checked>
                                                <div class="dds-toggle-track">
                                                    <div class="dds-toggle-knob">
                                                        <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </label>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 3. Front Office | Patient Portal Option -->
                    <div class="space-y-3">
                        <h3 class="text-sm font-bold text-slate-900">Front Office | Patient Portal Option</h3>
                        <div class="border border-slate-200 rounded-lg overflow-hidden">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold">
                                        <th class="px-5 py-2.5 font-bold">Filter</th>
                                        <th class="px-5 py-2.5 font-bold text-right w-36">Enabled</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bg-[#f8fafc] hover:bg-slate-100/60 transition-colors">
                                        <td class="px-5 py-3 font-medium text-slate-800">Inactive Patients</td>
                                        <td class="px-5 py-3 text-right">
                                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                                <input type="checkbox" name="front_office[inactive_patients]" class="sr-only dds-toggle-input" checked>
                                                <div class="dds-toggle-track">
                                                    <div class="dds-toggle-knob">
                                                        <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </label>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 4. Production-Type-Based Metrics (Net by default) -->
                    <div class="space-y-3">
                        <h3 class="text-sm font-bold text-slate-900">Production-Type-Based Metrics (Net by default)</h3>
                        <div class="border border-slate-200 rounded-lg overflow-hidden">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold">
                                        <th class="px-5 py-2.5 font-bold">Metric</th>
                                        <th class="px-5 py-2.5 font-bold text-center w-36">Net Production</th>
                                        <th class="px-5 py-2.5 font-bold text-center w-36">Gross Production</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bg-[#f8fafc] hover:bg-slate-100/60 transition-colors">
                                        <td class="px-5 py-3 font-medium text-slate-800">Collection Rate</td>
                                        <td class="px-5 py-3 text-center">
                                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                                <input type="radio" name="collection_rate_metric" value="net" class="sr-only dds-toggle-input" checked>
                                                <div class="dds-toggle-track">
                                                    <div class="dds-toggle-knob">
                                                        <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </label>
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                                <input type="radio" name="collection_rate_metric" value="gross" class="sr-only dds-toggle-input">
                                                <div class="dds-toggle-track">
                                                    <div class="dds-toggle-knob">
                                                        <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </label>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

            <!-- TAB: Email Settings -->
            <div id="tab-panel-email" class="config-tab-panel {{ $activeTab === 'email' ? '' : 'hidden' }} space-y-4">
                <!-- Info Alert 1: Timing / Latency -->
                <div class="bg-[#dcf0fa] border border-[#b8e0f5] text-[#1b4d6b] rounded-md px-4 py-3 flex items-start gap-3 text-xs shadow-xs">
                    <div class="w-4 h-4 rounded-full border border-[#1b4d6b] flex items-center justify-center shrink-0 text-[10px] font-bold mt-0.5">
                        i
                    </div>
                    <div class="leading-relaxed">
                        <span class="font-bold">Info:</span> Due to the latency of pulling data, emails may not be sent exactly on the selected sending time. We are ensuring that the data we are pulling is as real-time and current as possible. EOD and Morning Huddle will also be sent at the same time selected.
                    </div>
                </div>

                <!-- Info Alert 2: Cloud-based PMS -->
                <div class="bg-[#dcf0fa] border border-[#b8e0f5] text-[#1b4d6b] rounded-md px-4 py-3 flex items-start gap-3 text-xs shadow-xs">
                    <div class="w-4 h-4 rounded-full border border-[#1b4d6b] flex items-center justify-center shrink-0 text-[10px] font-bold mt-0.5">
                        i
                    </div>
                    <div class="leading-relaxed">
                        <span class="font-bold">Info:</span> Automated emails do not support Cloud-based PMS.
                    </div>
                </div>

                <!-- Actions / Filter Bar -->
                <div class="flex flex-wrap items-center justify-between gap-4 pt-1">
                    <!-- Search Locations -->
                    <div class="relative w-64">
                        <input type="text" id="emailLocationSearch" placeholder="Search Locations"
                            class="w-full bg-white border border-slate-300 rounded px-3 py-1.5 text-xs text-slate-800 placeholder:text-slate-400 pr-8 focus:outline-none focus:border-[#00bfa5] shadow-xs">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-slate-400">
                            <i data-lucide="search" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>

                    <!-- Respond to sender Email + Save/Clear -->
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-slate-900 whitespace-nowrap">Respond to sender Email:</span>
                        <input type="email" id="respondToSenderEmail" placeholder="email@mail.com" class="bg-white border border-slate-300 rounded px-3 py-1.5 text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-[#00bfa5] w-52 shadow-xs">
                        <button type="button" id="clearEmailBtn" class="border border-[#00bfa5] text-[#00bfa5] hover:bg-emerald-50 font-bold text-xs px-5 py-1.5 rounded transition-colors shadow-xs cursor-pointer">
                            Clear
                        </button>
                        <button type="button" id="saveEmailBtn" class="bg-[#00bfa5] text-white hover:bg-[#00a892] font-bold text-xs px-6 py-1.5 rounded transition-colors shadow-xs cursor-pointer">
                            Save
                        </button>
                    </div>
                </div>

                <!-- Email Settings Table -->
                <div class="border border-slate-200/90 rounded-lg overflow-hidden bg-white shadow-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs" id="emailSettingsTable">
                            <thead>
                                <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-700 font-bold">
                                    <th rowspan="2" class="w-12 px-4 py-3 text-center border-r border-slate-200/60">
                                        <input type="checkbox" id="email-select-all" class="rounded border-slate-300 text-[#00bfa5] focus:ring-[#00bfa5] w-4 h-4 cursor-pointer">
                                    </th>
                                    <th rowspan="2" class="w-16 px-4 py-3 font-bold border-r border-slate-200/60 text-slate-800">ID</th>
                                    <th rowspan="2" class="px-5 py-3 font-bold border-r border-slate-200/60 text-slate-800 min-w-[180px]">Location</th>
                                    <th colspan="2" class="px-5 py-2 text-center font-bold border-r border-b border-slate-200/60 text-slate-800 bg-slate-50/50">Recipients</th>
                                    <th colspan="2" class="px-5 py-2 text-center font-bold border-r border-b border-slate-200/60 text-slate-800 bg-slate-50/50">Automate</th>
                                    <th rowspan="2" class="px-5 py-3 text-center font-bold border-r border-slate-200/60 text-slate-800 min-w-[170px]">Email Sending Time (CST)</th>
                                    <th rowspan="2" class="w-20 px-4 py-3 text-center font-bold text-slate-800">Action</th>
                                </tr>
                                <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-700 font-bold">
                                    <th class="px-5 py-2.5 font-bold border-r border-slate-200/60 min-w-[220px]">Email To</th>
                                    <th class="px-5 py-2.5 font-bold border-r border-slate-200/60 min-w-[120px]">Email Cc</th>
                                    <th class="px-5 py-2.5 font-bold text-center border-r border-slate-200/60 w-24">EOD</th>
                                    <th class="px-5 py-2.5 font-bold text-center border-r border-slate-200/60 w-32">Morning Huddle</th>
                                </tr>
                            </thead>
                            <tbody id="emailSettingsTbody">
                                @foreach($emailSettings as $index => $row)
                                    @php
                                        $isEven = ($index % 2 === 1);
                                    @endphp
                                    <tr class="email-setting-row {{ $isEven ? 'bg-white' : 'bg-[#f8fafc]' }} border-b border-slate-100 hover:bg-slate-100/60 transition-colors" data-location="{{ strtolower($row['location']) }}" data-id="{{ $row['id'] }}">
                                        <td class="px-4 py-3 text-center border-r border-slate-100">
                                            <input type="checkbox" class="email-row-cb rounded border-slate-300 text-[#00bfa5] focus:ring-[#00bfa5] w-4 h-4 cursor-pointer">
                                        </td>
                                        <td class="px-4 py-3 font-medium text-slate-700 border-r border-slate-100">{{ $row['id'] }}</td>
                                        <td class="px-5 py-3 font-medium text-slate-800 border-r border-slate-100">{{ $row['location'] }}</td>
                                        <td class="px-5 py-3 text-slate-600 border-r border-slate-100 truncate max-w-[240px]">{{ $row['email_to'] }}</td>
                                        <td class="px-5 py-3 text-slate-600 border-r border-slate-100">{{ $row['email_cc'] }}</td>
                                        <td class="px-5 py-3 text-center border-r border-slate-100">
                                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                                <input type="checkbox" name="eod[{{ $row['id'] }}]" class="sr-only dds-toggle-input" {{ $row['eod'] ? 'checked' : '' }}>
                                                <div class="dds-toggle-track">
                                                    <div class="dds-toggle-knob">
                                                        <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </label>
                                        </td>
                                        <td class="px-5 py-3 text-center border-r border-slate-100">
                                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                                <input type="checkbox" name="morning_huddle[{{ $row['id'] }}]" class="sr-only dds-toggle-input" {{ $row['morning_huddle'] ? 'checked' : '' }}>
                                                <div class="dds-toggle-track">
                                                    <div class="dds-toggle-knob">
                                                        <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </label>
                                        </td>
                                        <td class="px-5 py-3 text-center font-medium text-slate-700 border-r border-slate-100">{{ $row['time'] }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <button type="button" class="text-[#00bfa5] hover:text-[#008f7c] font-black text-lg tracking-widest leading-none px-2 py-1 rounded hover:bg-emerald-50 transition-colors cursor-pointer" title="Options">
                                                •••
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB: Providers -->
            <div id="tab-panel-providers" class="config-tab-panel {{ $activeTab === 'providers' ? '' : 'hidden' }} space-y-6">
                <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6 space-y-6">
                    <!-- Top Bar: Title + Actions -->
                    <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                        <h3 class="text-2xl font-bold text-slate-900 leading-normal">Providers</h3>
                        <div class="flex items-center space-x-2">
                            <button type="button" id="clearProviderSettingsBtn" class="appearance-none inline-flex items-center py-2 px-3 justify-center font-bold text-xs h-9 rounded-sm border-2 bg-white border-[#00bfa5] text-[#00bfa5] hover:bg-[#00bfa5] hover:text-white transition-colors cursor-pointer">
                                Clear Settings
                            </button>
                            <button type="button" id="applyDefaultProviderSettingsBtn" class="appearance-none inline-flex items-center py-2 px-3 justify-center font-bold text-xs h-9 rounded-sm border-2 bg-[#00bfa5] border-[#00bfa5] text-white hover:bg-[#009b86] hover:border-[#009b86] transition-colors shadow-xs cursor-pointer">
                                Apply Default Settings
                            </button>
                            <button type="button" id="exportProviderCsvBtn" class="appearance-none inline-flex items-center py-2 px-3 justify-center font-bold text-xs h-9 rounded-sm border-2 bg-white border-[#00bfa5] text-[#00bfa5] hover:bg-[#00bfa5] hover:text-white transition-colors cursor-pointer">
                                <span>Export CSV</span>
                            </button>
                        </div>
                    </div>

                    <!-- Filters Grid -->
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-5">
                        <!-- 1. Location -->
                        <div>
                            <label class="block font-bold mb-1 text-slate-800 text-xs">Location</label>
                            <select id="provLocationFilter" class="w-full bg-white border border-slate-300 rounded-sm px-3 py-1.5 text-xs text-slate-700 font-medium focus:outline-none focus:border-[#00bfa5] h-9">
                                <option value="">Select a Location</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc }}" {{ $loc === '8 Mile' ? 'selected' : '' }}>{{ $loc }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 2. Provider Type or Line of Business -->
                        <div>
                            <label class="block font-bold mb-1 text-slate-800 text-xs">Provider Type or Line of Business</label>
                            <select id="provTypeFilter" class="w-full bg-white border border-slate-300 rounded-sm px-3 py-1.5 text-xs text-slate-700 font-medium focus:outline-none focus:border-[#00bfa5] h-9">
                                <option value="All" selected>All</option>
                                <option value="Set">Set</option>
                                <option value="Not Set">Not Set</option>
                                @foreach($specialties as $spec)
                                    <option value="{{ $spec }}">{{ $spec }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 3. Visibility -->
                        <div>
                            <label class="block font-bold mb-1 text-slate-800 text-xs">Visibility</label>
                            <select id="provVisibilityFilter" class="w-full bg-white border border-slate-300 rounded-sm px-3 py-1.5 text-xs text-slate-700 font-medium focus:outline-none focus:border-[#00bfa5] h-9">
                                <option value="All" selected>All</option>
                                <option value="Visible">Visible</option>
                                <option value="Hidden">Hidden</option>
                            </select>
                        </div>

                        <!-- 4. Has Production -->
                        <div>
                            <label class="block font-bold mb-1 text-slate-800 text-xs">Has Production</label>
                            <select id="provProductionFilter" class="w-full bg-white border border-slate-300 rounded-sm px-3 py-1.5 text-xs text-slate-700 font-medium focus:outline-none focus:border-[#00bfa5] h-9">
                                <option value="Last 6 Months">Last 6 Months</option>
                                <option value="Last 12 Months" selected>Last 12 Months</option>
                                <option value="Last 24 Months">Last 24 Months</option>
                                <option value="Any">Any</option>
                            </select>
                        </div>

                        <!-- 5. Search -->
                        <div>
                            <label class="block font-bold mb-1 text-slate-800 text-xs">Search</label>
                            <div class="relative">
                                <input type="search" id="provSearchInput" placeholder="Search"
                                    class="w-full bg-white border border-slate-300 rounded-sm px-3 py-1.5 text-xs text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-[#00bfa5] h-9 pr-8">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-slate-400">
                                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Providers Table -->
                    <div class="border border-slate-200 rounded-sm overflow-hidden bg-white shadow-xs">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs" id="providersTable">
                                <thead>
                                    <tr class="bg-slate-100 border-b border-slate-200 text-slate-700 font-semibold">
                                        <th class="px-3 py-4 text-center border-r border-slate-200/80 w-16">Visible</th>
                                        <th class="px-3 py-4 border-r border-slate-200/80 min-w-[180px]">Provider</th>
                                        <th class="px-3 py-4 border-r border-slate-200/80 min-w-[120px]">Location</th>
                                        <th class="px-3 py-4 text-right border-r border-slate-200/80 w-24">Provider ID</th>
                                        <th class="px-3 py-4 text-right border-r border-slate-200/80 w-28">Last Active</th>
                                        <th class="px-3 py-4 text-right border-r border-slate-200/80 w-36">Location Production</th>
                                        @foreach($specialties as $spec)
                                            <th class="px-2 py-4 text-center border-r border-slate-200/80 whitespace-nowrap min-w-[70px]">{{ $spec }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody id="providersTbody">
                                    @foreach($providers as $index => $row)
                                        @php
                                            $isEven = ($index % 2 === 1);
                                        @endphp
                                        <tr class="provider-row {{ $isEven ? 'bg-white' : 'bg-slate-50/60' }} border-b border-slate-100 hover:bg-slate-100/60 transition-colors"
                                            data-name="{{ strtolower($row['name']) }}"
                                            data-location="{{ strtolower($row['location']) }}"
                                            data-id="{{ $row['id'] }}"
                                            data-visible="{{ $row['visible'] ? 'visible' : 'hidden' }}">
                                            
                                            <!-- Visible switch -->
                                            <td class="px-3 py-2 text-center border-r border-slate-100 align-middle">
                                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                                    <input type="checkbox" name="prov_visible[{{ $row['id'] }}]" class="sr-only dds-toggle-input" {{ $row['visible'] ? 'checked' : '' }}>
                                                    <div class="dds-toggle-track">
                                                        <div class="dds-toggle-knob">
                                                            <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </label>
                                            </td>

                                            <!-- Provider Name -->
                                            <td class="px-3 py-2 font-medium text-slate-800 border-r border-slate-100 align-middle">{{ $row['name'] }}</td>

                                            <!-- Location -->
                                            <td class="px-3 py-2 text-slate-700 border-r border-slate-100 align-middle">{{ $row['location'] }}</td>

                                            <!-- Provider ID -->
                                            <td class="px-3 py-2 text-right font-medium text-slate-700 border-r border-slate-100 align-middle">{{ $row['id'] }}</td>

                                            <!-- Last Active -->
                                            <td class="px-3 py-2 text-right text-slate-700 border-r border-slate-100 align-middle">{{ $row['last_active'] }}</td>

                                            <!-- Location Production -->
                                            <td class="px-3 py-2 text-right font-semibold text-slate-800 border-r border-slate-100 align-middle">{{ $row['production'] }}</td>

                                            <!-- Specialty toggles -->
                                            @foreach($specialties as $spec)
                                                @php
                                                    $isChecked = ($row['specialty'] === $spec);
                                                @endphp
                                                <td class="px-2 py-2 text-center border-r border-slate-100 align-middle">
                                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                                        <input type="checkbox" name="prov_spec[{{ $row['id'] }}][{{ $spec }}]" class="sr-only dds-toggle-input" {{ $isChecked ? 'checked' : '' }}>
                                                        <div class="dds-toggle-track">
                                                            <div class="dds-toggle-knob">
                                                                <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Bar -->
                        <div class="flex flex-wrap items-center justify-between px-4 py-3 border-t border-slate-200 bg-slate-50/50 text-xs text-slate-600">
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center space-x-2">
                                    <span>Items per page</span>
                                    <select class="bg-white border border-slate-300 rounded px-2 py-1 text-xs focus:outline-none focus:border-[#00bfa5]">
                                        <option>10</option>
                                        <option>25</option>
                                        <option>50</option>
                                    </select>
                                </div>
                                <span>1-3 of 3 items</span>
                            </div>

                            <div class="flex items-center space-x-3">
                                <div class="flex items-center space-x-1.5">
                                    <select class="bg-white border border-slate-300 rounded px-2 py-1 text-xs focus:outline-none focus:border-[#00bfa5]">
                                        <option>1</option>
                                    </select>
                                    <span>of 1 pages</span>
                                </div>

                                <div class="flex items-center border border-slate-300 rounded overflow-hidden">
                                    <button type="button" disabled class="px-2.5 py-1 text-slate-300 bg-white border-r border-slate-300 cursor-not-allowed">
                                        <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button type="button" disabled class="px-2.5 py-1 text-slate-300 bg-white cursor-not-allowed">
                                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB: Goals -->
            <div id="tab-panel-goals" class="config-tab-panel {{ $activeTab === 'goals' ? '' : 'hidden' }} space-y-0">
                @php
                    $isGoalsOffice = in_array($activeSubtab, ['office', '']);
                    $isGoalsSpecialty = in_array($activeSubtab, ['specialties', 'specialty']);
                    $isGoalsProvider = in_array($activeSubtab, ['providers', 'provider']);
                @endphp
                <!-- Subtab navigation -->
                <ul role="tablist" class="flex flex-grow overflow-y-hidden mb-0 space-x-1" id="goals-subtab-nav">
                    <li role="presentation">
                        <a href="{{ url('/configuration/goals/office') }}"
                           data-goals-subtab="office"
                           role="tab"
                           aria-selected="{{ $isGoalsOffice ? 'true' : 'false' }}"
                           class="goals-subtab-btn text-xs py-2.5 px-6 rounded-t font-semibold transition-all cursor-pointer {{ $isGoalsOffice ? 'bg-white text-slate-900 border-t-2 border-[#00bfa5] shadow-xs font-bold' : 'bg-slate-200/70 text-slate-600 hover:bg-white hover:text-slate-800' }}">
                            Office
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="{{ url('/configuration/goals/specialties') }}"
                           data-goals-subtab="specialties"
                           role="tab"
                           aria-selected="{{ $isGoalsSpecialty ? 'true' : 'false' }}"
                           class="goals-subtab-btn text-xs py-2.5 px-6 rounded-t font-semibold transition-all cursor-pointer {{ $isGoalsSpecialty ? 'bg-white text-slate-900 border-t-2 border-[#00bfa5] shadow-xs font-bold' : 'bg-slate-200/70 text-slate-600 hover:bg-white hover:text-slate-800' }}">
                            Specialty
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="{{ url('/configuration/goals/providers') }}"
                           data-goals-subtab="providers"
                           role="tab"
                           aria-selected="{{ $isGoalsProvider ? 'true' : 'false' }}"
                           class="goals-subtab-btn text-xs py-2.5 px-6 rounded-t font-semibold transition-all cursor-pointer {{ $isGoalsProvider ? 'bg-white text-slate-900 border-t-2 border-[#00bfa5] shadow-xs font-bold' : 'bg-slate-200/70 text-slate-600 hover:bg-white hover:text-slate-800' }}">
                            Provider
                        </a>
                    </li>
                </ul>

                <div class="bg-white relative p-6 mb-10 shadow-sm rounded-b-lg border border-slate-200 space-y-6">
                    <!-- Goals Table (Office Subtab) -->
                    <div id="goals-subtab-panel-office" class="goals-subtab-panel {{ $isGoalsOffice ? '' : 'hidden' }} space-y-6">
                        <!-- Office Top Bar: Filters + Download/Upload Actions -->
                        <div class="flex flex-col mb-6 space-y-4 lg:flex-row lg:items-end lg:justify-between lg:space-y-0">
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-3 lg:w-3/5">
                                <!-- Location -->
                                <div>
                                    <label class="block font-bold mb-1 text-slate-800 text-xs">Location</label>
                                    <select id="goalLocationFilter" class="w-full bg-white border border-slate-300 rounded-sm px-3 py-1.5 text-xs text-slate-700 font-medium focus:outline-none focus:border-[#00bfa5] h-9">
                                        <option value="">All Locations</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc }}">{{ $loc }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Month -->
                                <div>
                                    <label class="block font-bold mb-1 text-slate-800 text-xs">Month</label>
                                    <select id="goalMonthFilter" class="w-full bg-white border border-slate-300 rounded-sm px-3 py-1.5 text-xs text-slate-700 font-medium focus:outline-none focus:border-[#00bfa5] h-9">
                                        @foreach($months as $m)
                                            <option value="{{ $m }}" {{ $m === 'September 2026' ? 'selected' : '' }}>{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Goal Type -->
                                <div>
                                    <label class="block font-bold mb-1 text-slate-800 text-xs">Goal Type</label>
                                    <select id="goalTypeFilter" class="w-full bg-white border border-slate-300 rounded-sm px-3 py-1.5 text-xs text-slate-700 font-medium focus:outline-none focus:border-[#00bfa5] h-9">
                                        <option value="monthly" selected>monthly</option>
                                        <option value="daily">daily</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Action buttons: Download CSV & Upload CSV -->
                            <div class="flex flex-col md:flex-row md:items-center space-y-2 md:space-y-0 md:space-x-2">
                                <button type="button" class="appearance-none inline-flex items-center justify-center py-2 px-6 font-bold text-xs h-9 rounded-sm border-2 bg-[#00bfa5] border-[#00bfa5] text-white hover:bg-[#009b86] hover:border-[#009b86] transition-colors shadow-xs cursor-pointer gap-1.5">
                                    <svg fill="currentColor" class="w-4 h-4" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M20.73 6.257l-6.3-6A.839.839 0 0013.8 0h-9C3.806 0 3 .768 3 1.715v20.57C3 23.234 3.806 24 4.8 24h14.4c.994 0 1.8-.767 1.8-1.714V6.857a.763.763 0 00-.27-.6zm-6.93-4.2l5.04 4.8H13.8v-4.8zm5.4 20.229H4.8V1.715H12v5.142c0 .947.806 1.715 1.8 1.715h5.4v13.714zM7.5 17.143h9v1.714h-9v-1.714zM7.5 12h9v1.714h-9V12z"/>
                                    </svg>
                                    <span>Download CSV</span>
                                </button>
                                <button type="button" class="appearance-none inline-flex items-center justify-center py-2 px-6 font-bold text-xs h-9 rounded-sm border-2 bg-[#00bfa5] border-[#00bfa5] text-white hover:bg-[#009b86] hover:border-[#009b86] transition-colors shadow-xs cursor-pointer gap-1.5">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4" viewBox="0 0 24 24">
                                        <path d="M1 14v9h22v-9M4 10l8-8 8 8M12 2v17"/>
                                    </svg>
                                    <span>Upload CSV</span>
                                </button>
                            </div>
                        </div>

                        <div class="border border-slate-200 rounded-sm overflow-hidden bg-white shadow-xs">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs" id="goalsTable">
                                    <thead>
                                        <tr class="bg-slate-100 border-b border-slate-200 text-slate-700 font-semibold">
                                            <th class="px-3 py-4 text-center border-r border-slate-200/80 w-12">
                                                <input type="checkbox" id="goal-select-all" class="rounded border-slate-300 text-[#00bfa5] focus:ring-[#00bfa5] w-4 h-4 cursor-pointer">
                                            </th>
                                            <th class="px-3 py-4 text-center border-r border-slate-200/80 w-16">ID</th>
                                            <th class="px-4 py-4 border-r border-slate-200/80 min-w-[180px]">Office Name</th>
                                            <th class="px-3 py-4 text-right border-r border-slate-200/80 min-w-[140px]">Gross Production</th>
                                            <th class="px-3 py-4 text-right border-r border-slate-200/80 min-w-[140px]">Net Production</th>
                                            <th class="px-3 py-4 text-right border-r border-slate-200/80 min-w-[140px]">Collection</th>
                                            <th class="px-3 py-4 text-right border-r border-slate-200/80 min-w-[120px]">Pts Visits</th>
                                            <th class="px-3 py-4 text-right border-r border-slate-200/80 min-w-[120px]">Npt Visits</th>
                                            <th class="px-3 py-4 text-right border-r border-slate-200/80 min-w-[120px]">Ini Bonding</th>
                                            <th class="px-3 py-4 text-right border-r border-slate-200/80 min-w-[120px]">Hyg Visits</th>
                                            <th class="px-3 py-4 text-center border-r border-slate-200/80 w-20">Saved</th>
                                        </tr>
                                    </thead>
                                    <tbody id="goalsTbody">
                                        @foreach($officeGoals as $index => $row)
                                            @php
                                                $isEven = ($index % 2 === 1);
                                            @endphp
                                            <tr class="goal-row {{ $isEven ? 'bg-white' : 'bg-slate-50/60' }} border-b border-slate-100 hover:bg-slate-100/60 transition-colors"
                                                data-office="{{ strtolower($row['office']) }}"
                                                data-id="{{ $row['id'] }}">
                                                
                                                <!-- Checkbox -->
                                                <td class="px-3 py-2 text-center border-r border-slate-100 align-middle">
                                                    <input type="checkbox" class="goal-row-cb rounded border-slate-300 text-[#00bfa5] focus:ring-[#00bfa5] w-4 h-4 cursor-pointer">
                                                </td>

                                                <!-- ID -->
                                                <td class="px-3 py-2 text-center font-medium text-slate-700 border-r border-slate-100 align-middle">{{ $row['id'] }}</td>

                                                <!-- Office Name -->
                                                <td class="px-4 py-2 font-medium text-slate-800 border-r border-slate-100 align-middle">{{ $row['office'] }}</td>

                                                <!-- Gross Production -->
                                                <td class="px-2 py-2 border-r border-slate-100 align-middle">
                                                    <input type="text" placeholder="$0" class="w-full bg-slate-50 border border-slate-200 rounded px-2.5 py-1 text-right text-xs font-medium text-slate-800 focus:outline-none focus:border-[#00bfa5] focus:bg-white transition-colors">
                                                </td>

                                                <!-- Net Production -->
                                                <td class="px-2 py-2 border-r border-slate-100 align-middle">
                                                    <input type="text" placeholder="$0" class="w-full bg-slate-50 border border-slate-200 rounded px-2.5 py-1 text-right text-xs font-medium text-slate-800 focus:outline-none focus:border-[#00bfa5] focus:bg-white transition-colors">
                                                </td>

                                                <!-- Collection -->
                                                <td class="px-2 py-2 border-r border-slate-100 align-middle">
                                                    <input type="text" placeholder="$0" class="w-full bg-slate-50 border border-slate-200 rounded px-2.5 py-1 text-right text-xs font-medium text-slate-800 focus:outline-none focus:border-[#00bfa5] focus:bg-white transition-colors">
                                                </td>

                                                <!-- Pts Visits -->
                                                <td class="px-2 py-2 border-r border-slate-100 align-middle">
                                                    <input type="text" placeholder="0" class="w-full bg-slate-50 border border-slate-200 rounded px-2.5 py-1 text-right text-xs font-medium text-slate-800 focus:outline-none focus:border-[#00bfa5] focus:bg-white transition-colors">
                                                </td>

                                                <!-- Npt Visits -->
                                                <td class="px-2 py-2 border-r border-slate-100 align-middle">
                                                    <input type="text" placeholder="0" class="w-full bg-slate-50 border border-slate-200 rounded px-2.5 py-1 text-right text-xs font-medium text-slate-800 focus:outline-none focus:border-[#00bfa5] focus:bg-white transition-colors">
                                                </td>

                                                <!-- Ini Bonding -->
                                                <td class="px-2 py-2 border-r border-slate-100 align-middle">
                                                    <input type="text" placeholder="0" class="w-full bg-slate-50 border border-slate-200 rounded px-2.5 py-1 text-right text-xs font-medium text-slate-800 focus:outline-none focus:border-[#00bfa5] focus:bg-white transition-colors">
                                                </td>

                                                <!-- Hyg Visits -->
                                                <td class="px-2 py-2 border-r border-slate-100 align-middle">
                                                    <input type="text" placeholder="0" class="w-full bg-slate-50 border border-slate-200 rounded px-2.5 py-1 text-right text-xs font-medium text-slate-800 focus:outline-none focus:border-[#00bfa5] focus:bg-white transition-colors">
                                                </td>

                                                <!-- Saved Icon -->
                                                <td class="px-3 py-2 text-center align-middle">
                                                    <div class="w-5 h-5 rounded-full bg-[#00bfa5] text-white flex items-center justify-center mx-auto shadow-xs">
                                                        <svg width="12" height="9" viewBox="0 0 18 13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M17 1L6 12L1 7"></path>
                                                        </svg>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Goals Table (Specialty Subtab) -->
                    <div id="goals-subtab-panel-specialty" class="goals-subtab-panel {{ $isGoalsSpecialty ? '' : 'hidden' }} space-y-6">
                        <!-- Specialty Top Bar: Filters + Download/Upload Actions -->
                        <div class="flex flex-col mb-6 space-y-4 lg:flex-row lg:items-end lg:justify-between lg:space-y-0">
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-3 lg:w-3/5">
                                <!-- Location -->
                                <div>
                                    <label class="block font-bold mb-1 text-slate-800 text-xs">Location</label>
                                    <select id="goalSpecLocationFilter" class="w-full bg-white border border-slate-300 rounded-sm px-3 py-1.5 text-xs text-slate-700 font-medium focus:outline-none focus:border-[#00bfa5] h-9">
                                        <option value="">All Locations</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc }}">{{ $loc }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Month -->
                                <div>
                                    <label class="block font-bold mb-1 text-slate-800 text-xs">Month</label>
                                    <select id="goalSpecMonthFilter" class="w-full bg-white border border-slate-300 rounded-sm px-3 py-1.5 text-xs text-slate-700 font-medium focus:outline-none focus:border-[#00bfa5] h-9">
                                        @foreach($months as $m)
                                            <option value="{{ $m }}" {{ $m === 'September 2026' ? 'selected' : '' }}>{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Goal Type -->
                                <div>
                                    <label class="block font-bold mb-1 text-slate-800 text-xs">Goal Type</label>
                                    <select id="goalSpecTypeFilter" class="w-full bg-white border border-slate-300 rounded-sm px-3 py-1.5 text-xs text-slate-700 font-medium focus:outline-none focus:border-[#00bfa5] h-9">
                                        <option value="monthly" selected>monthly</option>
                                        <option value="daily">daily</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Action buttons: Download CSV & Upload CSV -->
                            <div class="flex flex-col md:flex-row md:items-center space-y-2 md:space-y-0 md:space-x-2">
                                <button type="button" class="appearance-none inline-flex items-center justify-center py-2 px-6 font-bold text-xs h-9 rounded-sm border-2 bg-[#00bfa5] border-[#00bfa5] text-white hover:bg-[#009b86] hover:border-[#009b86] transition-colors shadow-xs cursor-pointer gap-1.5">
                                    <svg fill="currentColor" class="w-4 h-4" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M20.73 6.257l-6.3-6A.839.839 0 0013.8 0h-9C3.806 0 3 .768 3 1.715v20.57C3 23.234 3.806 24 4.8 24h14.4c.994 0 1.8-.767 1.8-1.714V6.857a.763.763 0 00-.27-.6zm-6.93-4.2l5.04 4.8H13.8v-4.8zm5.4 20.229H4.8V1.715H12v5.142c0 .947.806 1.715 1.8 1.715h5.4v13.714zM7.5 17.143h9v1.714h-9v-1.714zM7.5 12h9v1.714h-9V12z"/>
                                    </svg>
                                    <span>Download CSV</span>
                                </button>
                                <button type="button" class="appearance-none inline-flex items-center justify-center py-2 px-6 font-bold text-xs h-9 rounded-sm border-2 bg-[#00bfa5] border-[#00bfa5] text-white hover:bg-[#009b86] hover:border-[#009b86] transition-colors shadow-xs cursor-pointer gap-1.5">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4" viewBox="0 0 24 24">
                                        <path d="M1 14v9h22v-9M4 10l8-8 8 8M12 2v17"/>
                                    </svg>
                                    <span>Upload CSV</span>
                                </button>
                            </div>
                        </div>

                        <div class="border border-slate-200 rounded-sm overflow-hidden bg-white shadow-xs">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs" id="goalsSpecialtyTable">
                                    <thead>
                                        <tr class="bg-slate-100 border-b border-slate-200 text-slate-700 font-semibold">
                                            <th class="px-3 py-4 text-center border-r border-slate-200/80 w-12">
                                                <input type="checkbox" id="goal-spec-select-all" class="rounded border-slate-300 text-[#00bfa5] focus:ring-[#00bfa5] w-4 h-4 cursor-pointer">
                                            </th>
                                            <th class="px-3 py-4 text-center border-r border-slate-200/80 w-16">ID</th>
                                            <th class="px-4 py-4 border-r border-slate-200/80 min-w-[180px]">Office Name</th>
                                            @foreach($goalSpecialties as $spec)
                                                <th class="px-3 py-4 text-right border-r border-slate-200/80 min-w-[120px] whitespace-nowrap">{{ $spec }}</th>
                                            @endforeach
                                            <th class="px-3 py-4 text-center border-r border-slate-200/80 w-20">Saved</th>
                                        </tr>
                                    </thead>
                                    <tbody id="goalsSpecialtyTbody">
                                        @foreach($officeGoals as $index => $row)
                                            @php
                                                $isEven = ($index % 2 === 1);
                                            @endphp
                                            <tr class="goal-specialty-row {{ $isEven ? 'bg-white' : 'bg-slate-50/60' }} border-b border-slate-100 hover:bg-slate-100/60 transition-colors"
                                                data-office="{{ strtolower($row['office']) }}"
                                                data-id="{{ $row['id'] }}">
                                                
                                                <!-- Checkbox -->
                                                <td class="px-3 py-2 text-center border-r border-slate-100 align-middle">
                                                    <input type="checkbox" class="goal-spec-row-cb rounded border-slate-300 text-[#00bfa5] focus:ring-[#00bfa5] w-4 h-4 cursor-pointer">
                                                </td>

                                                <!-- ID -->
                                                <td class="px-3 py-2 text-center font-medium text-slate-700 border-r border-slate-100 align-middle">{{ $row['id'] }}</td>

                                                <!-- Office Name -->
                                                <td class="px-4 py-2 font-medium text-slate-800 border-r border-slate-100 align-middle">{{ $row['office'] }}</td>

                                                <!-- Specialty Input Fields (Doctor, Hygiene, Oral Surgery, Clear Aligners, Perio, Pedo, Endo, Ortho, Prostho) -->
                                                @foreach($goalSpecialties as $spec)
                                                    <td class="px-2 py-2 border-r border-slate-100 align-middle">
                                                        <input type="tel" placeholder="$0" class="v-money w-full bg-slate-50 border border-slate-200 rounded px-2.5 py-1 text-right text-xs font-medium text-slate-800 focus:outline-none focus:border-[#00bfa5] focus:bg-white transition-colors" align="right">
                                                    </td>
                                                @endforeach

                                                <!-- Saved Icon -->
                                                <td class="px-3 py-2 text-center align-middle">
                                                    <div class="w-5 h-5 rounded-full bg-[#00bfa5] text-white flex items-center justify-center mx-auto shadow-xs">
                                                        <svg width="12" height="9" viewBox="0 0 18 13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M17 1L6 12L1 7"></path>
                                                        </svg>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Goals Table (Provider Subtab) -->
                    <div id="goals-subtab-panel-provider" class="goals-subtab-panel {{ $isGoalsProvider ? '' : 'hidden' }} space-y-6">
                        <!-- Provider Top Bar: Filters + Actions -->
                        <div class="flex flex-col mb-6 space-y-3 lg:flex-row lg:items-end lg:justify-between lg:space-y-0">
                            <div class="grid grid-cols-1 gap-2 md:grid-cols-4 lg:w-3/5">
                                <!-- Location -->
                                <div>
                                    <label class="block font-bold mb-1 text-slate-800 text-xs">Location</label>
                                    <select id="goalProvLocationFilter" class="w-full bg-white border border-slate-300 rounded-sm px-3 py-1.5 text-xs text-slate-700 font-medium focus:outline-none focus:border-[#00bfa5] h-9">
                                        <option value="">All Locations</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc }}" {{ $loc === '8 Mile' ? 'selected' : '' }}>{{ $loc }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Provider Type -->
                                <div>
                                    <label class="block font-bold mb-1 text-slate-800 text-xs">Provider Type</label>
                                    <select id="goalProvTypeFilter" class="w-full bg-white border border-slate-300 rounded-sm px-3 py-1.5 text-xs text-slate-700 font-medium focus:outline-none focus:border-[#00bfa5] h-9">
                                        @foreach($providerTypes as $ptype)
                                            <option value="{{ $ptype }}" {{ $ptype === 'All Types' ? 'selected' : '' }}>{{ $ptype }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Goal Type -->
                                <div>
                                    <label class="block font-bold mb-1 text-slate-800 text-xs">Goal Type</label>
                                    <select id="goalProvGoalTypeFilter" class="w-full bg-white border border-slate-300 rounded-sm px-3 py-1.5 text-xs text-slate-700 font-medium focus:outline-none focus:border-[#00bfa5] h-9">
                                        <option value="All Types" selected>All Types</option>
                                        <option value="Monthly">Monthly</option>
                                        <option value="Daily">Daily</option>
                                    </select>
                                </div>

                                <!-- Month -->
                                <div>
                                    <label class="block font-bold mb-1 text-slate-800 text-xs">Month</label>
                                    <select id="goalProvMonthFilter" class="w-full bg-white border border-slate-300 rounded-sm px-3 py-1.5 text-xs text-slate-700 font-medium focus:outline-none focus:border-[#00bfa5] h-9">
                                        @foreach($months as $m)
                                            <option value="{{ $m }}" {{ $m === 'September 2026' ? 'selected' : '' }}>{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Action buttons: Add Goal, Download CSV & Upload CSV -->
                            <div class="flex flex-col md:flex-row md:items-center space-y-2 md:space-y-0 md:space-x-1.5">
                                <button type="button" class="appearance-none inline-flex items-center justify-center py-2 px-3 font-bold text-xs h-9 rounded-sm border-2 bg-[#00bfa5] border-[#00bfa5] text-white hover:bg-[#009b86] hover:border-[#009b86] transition-colors shadow-xs cursor-pointer gap-1">
                                    <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12 12" class="w-3.5 h-3.5 flex-shrink-0">
                                        <path d="M6 0v12M0 6h12" stroke="currentColor" stroke-width="2.5"></path>
                                    </svg>
                                    <span>Add Goal</span>
                                </button>
                                <button type="button" class="appearance-none inline-flex items-center justify-center py-2 px-5 font-bold text-xs h-9 rounded-sm border-2 bg-[#00bfa5] border-[#00bfa5] text-white hover:bg-[#009b86] hover:border-[#009b86] transition-colors shadow-xs cursor-pointer gap-1.5">
                                    <svg fill="currentColor" class="w-4 h-4" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M20.73 6.257l-6.3-6A.839.839 0 0013.8 0h-9C3.806 0 3 .768 3 1.715v20.57C3 23.234 3.806 24 4.8 24h14.4c.994 0 1.8-.767 1.8-1.714V6.857a.763.763 0 00-.27-.6zm-6.93-4.2l5.04 4.8H13.8v-4.8zm5.4 20.229H4.8V1.715H12v5.142c0 .947.806 1.715 1.8 1.715h5.4v13.714zM7.5 17.143h9v1.714h-9v-1.714zM7.5 12h9v1.714h-9V12z"/>
                                    </svg>
                                    <span>Download CSV</span>
                                </button>
                                <button type="button" class="appearance-none inline-flex items-center justify-center py-2 px-3 font-bold text-xs h-9 rounded-sm border-2 bg-[#00bfa5] border-[#00bfa5] text-white hover:bg-[#009b86] hover:border-[#009b86] transition-colors shadow-xs cursor-pointer gap-1.5">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4" viewBox="0 0 24 24">
                                        <path d="M1 14v9h22v-9M4 10l8-8 8 8M12 2v17"/>
                                    </svg>
                                    <span>Upload CSV</span>
                                </button>
                            </div>
                        </div>

                        <!-- Provider Goals Table -->
                        <div class="border border-slate-200 rounded-sm overflow-hidden bg-white shadow-xs">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs" id="goalsProviderTable">
                                    <thead>
                                        <tr class="bg-slate-100 border-b border-slate-200 text-slate-700 font-semibold">
                                            <th class="px-3 py-4 text-center border-r border-slate-200/80 w-12">
                                                <input type="checkbox" id="goal-prov-select-all" class="rounded border-slate-300 text-[#00bfa5] focus:ring-[#00bfa5] w-4 h-4 cursor-pointer">
                                            </th>
                                            <th class="px-3 py-4 text-center border-r border-slate-200/80 w-24">Office ID</th>
                                            <th class="px-4 py-4 border-r border-slate-200/80 min-w-[140px]">Office Name</th>
                                            <th class="px-3 py-4 text-left border-r border-slate-200/80 w-24">Provider ID</th>
                                            <th class="px-4 py-4 border-r border-slate-200/80 min-w-[180px]">Provider Name</th>
                                            <th class="px-3 py-4 border-r border-slate-200/80 w-28">Provider Type</th>
                                            <th class="px-3 py-4 border-r border-slate-200/80 w-24">Recurring</th>
                                            <th class="px-4 py-4 text-center border-r border-slate-200/80 min-w-[160px]">Production Goal</th>
                                            <th class="px-3 py-4 text-center w-16"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="goalsProviderTbody">
                                        @forelse($providerGoals as $index => $row)
                                            @php
                                                $isEven = ($index % 2 === 1);
                                            @endphp
                                            <tr class="goal-provider-row {{ $isEven ? 'bg-white' : 'bg-slate-50/60' }} border-b border-slate-100 hover:bg-slate-100/60 transition-colors"
                                                data-office="{{ strtolower($row['office_name']) }}"
                                                data-provider-type="{{ strtolower($row['provider_type']) }}"
                                                data-id="{{ $row['id'] }}">
                                                
                                                <!-- Checkbox -->
                                                <td class="px-3 py-2 text-center border-r border-slate-100 align-middle">
                                                    <input type="checkbox" class="goal-prov-row-cb rounded border-slate-300 text-[#00bfa5] focus:ring-[#00bfa5] w-4 h-4 cursor-pointer">
                                                </td>

                                                <!-- Office ID -->
                                                <td class="px-3 py-2 text-center font-medium text-slate-700 border-r border-slate-100 align-middle">
                                                    {{ $row['office_id'] }}
                                                </td>

                                                <!-- Office Name -->
                                                <td class="px-4 py-2 font-medium text-slate-800 border-r border-slate-100 align-middle">
                                                    {{ $row['office_name'] }}
                                                </td>

                                                <!-- Provider ID -->
                                                <td class="px-3 py-2 text-left font-medium text-slate-700 border-r border-slate-100 align-middle">
                                                    {{ $row['provider_id'] }}
                                                </td>

                                                <!-- Provider Name -->
                                                <td class="px-4 py-2 font-medium text-slate-800 border-r border-slate-100 align-middle min-w-[180px]">
                                                    {{ $row['provider_name'] }}
                                                </td>

                                                <!-- Provider Type -->
                                                <td class="px-3 py-2 text-slate-700 border-r border-slate-100 align-middle">
                                                    {{ $row['provider_type'] }}
                                                </td>

                                                <!-- Recurring -->
                                                <td class="px-3 py-2 text-slate-700 border-r border-slate-100 align-middle">
                                                    {{ $row['recurring'] ? 'Yes' : 'No' }}
                                                </td>

                                                <!-- Production Goal -->
                                                <td class="px-3 py-2 text-center border-r border-slate-100 align-middle min-w-[160px]">
                                                    <input type="tel" placeholder="$0" value="{{ $row['goal'] }}" class="v-money w-full bg-slate-50 border border-slate-200 rounded px-2.5 py-1 text-right text-xs font-medium text-slate-800 focus:outline-none focus:border-[#00bfa5] focus:bg-white transition-colors" align="right">
                                                </td>

                                                <!-- Action -->
                                                <td class="px-3 py-2 text-center align-middle">
                                                    <button type="button" class="text-[#00bfa5] hover:text-[#008f7c] font-black text-lg tracking-widest leading-none px-2 py-1 rounded hover:bg-emerald-50 transition-colors cursor-pointer" title="Options">
                                                        •••
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                                                    No provider goals found. Click "Add Goal" to set a new provider goal.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB: Reminders -->
            <div id="tab-panel-reminders" class="config-tab-panel {{ $activeTab === 'reminders' ? '' : 'hidden' }} space-y-6">
                <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6 mb-10">
                    <div class="reminder-table">
                        <div class="border border-slate-200 rounded-sm overflow-hidden bg-white shadow-xs">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs" id="remindersTable">
                                    <thead>
                                        <tr class="bg-slate-100 border-b border-slate-200 text-slate-700 font-semibold">
                                            <th class="px-3 py-4 text-center border-r border-slate-200/80 w-24">Enable</th>
                                            <th class="px-4 py-4 text-left border-r border-slate-200/80 min-w-[10rem]">Reminder Type</th>
                                            <th class="px-4 py-4 text-left border-r border-slate-200/80 min-w-[10rem]">Assign To</th>
                                            <th class="px-3 py-4 text-right border-r border-slate-200/80 w-24">Interval</th>
                                            <th class="px-3 py-4 text-center w-24">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="remindersTbody">
                                        @foreach($reminders as $index => $row)
                                            @php
                                                $isEven = ($index % 2 === 1);
                                            @endphp
                                            <tr class="reminder-table-row {{ $isEven ? 'bg-white' : 'bg-slate-50/60' }} border-b border-slate-100 hover:bg-slate-100/60 transition-colors"
                                                data-id="{{ $row['id'] }}">
                                                <!-- Enable Toggle -->
                                                <td class="px-3 py-2 text-center border-r border-slate-100 align-middle">
                                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                                        <input type="checkbox" name="reminder_enabled[{{ $row['id'] }}]" class="sr-only dds-toggle-input" {{ $row['enabled'] ? 'checked' : '' }}>
                                                        <div class="dds-toggle-track">
                                                            <div class="dds-toggle-knob">
                                                                <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </td>

                                                <!-- Reminder Type -->
                                                <td class="px-4 py-3 font-medium text-slate-800 border-r border-slate-100 align-middle">
                                                    {{ $row['type'] }}
                                                </td>

                                                <!-- Assign To -->
                                                <td class="px-4 py-3 text-slate-600 border-r border-slate-100 align-middle">
                                                    {{ $row['assign_to'] }}
                                                </td>

                                                <!-- Interval -->
                                                <td class="px-3 py-3 text-right font-medium text-slate-700 border-r border-slate-100 align-middle">
                                                    {{ $row['interval'] ?? '' }}
                                                </td>

                                                <!-- Action -->
                                                <td class="px-3 py-2 text-center align-middle relative">
                                                    <div class="relative inline-block text-left reminder-action-menu">
                                                        <button type="button" class="reminder-action-btn text-[#00bfa5] hover:text-[#008f7c] font-black text-lg tracking-widest leading-none px-2 py-1 rounded hover:bg-emerald-50 transition-colors cursor-pointer" title="Options">
                                                            •••
                                                        </button>
                                                        <div class="reminder-dropdown-menu hidden absolute right-0 z-20 mt-1 w-32 bg-white border border-slate-200 rounded shadow-lg py-1">
                                                            <a href="#" class="text-xs font-semibold flex items-center px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-[#00bfa5] transition-colors gap-2">
                                                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4 text-[#00bfa5] flex-shrink-0">
                                                                    <path d="M10.5 4H1v19h19v-9.5" stroke="currentColor" stroke-width="2"></path>
                                                                    <path d="M19 1.52l3.561 3.562-11.574 11.574-5.045 1.484 1.484-5.046L19 1.52zM15.977 4.543l3.562 3.562" stroke="currentColor" stroke-width="2" stroke-linejoin="round"></path>
                                                                </svg>
                                                                <span>Edit</span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB: Calendar -->
            <div id="tab-panel-calendar" class="config-tab-panel {{ $activeTab === 'calendar' ? '' : 'hidden' }} space-y-0" company-id="42" role="subscriber" subscription="enterprise">
                @php
                    $isCalDaily = ($activeSubtab === 'daily-schedule' || empty($activeSubtab));
                    $isCalHours = in_array($activeSubtab, ['worked-hours', 'work-hours']);
                    $isCalClosed = ($activeSubtab === 'closed-days');
                    $isCalDefaultClosed = ($activeSubtab === 'default-closed-days');
                @endphp
                <!-- Subtabs Navigation -->
                <ul role="tablist" class="flex flex-grow overflow-y-hidden mt-2 mb-0 border-b border-slate-200">
                    <li role="presentation">
                        <a href="{{ url('/configuration/calendar/daily-schedule') }}"
                           data-calendar-subtab="daily-schedule"
                           role="tab"
                           aria-selected="{{ $isCalDaily ? 'true' : 'false' }}"
                           class="calendar-subtab-btn text-xs py-3 px-4 rounded-t leading-snug font-semibold mr-1 transform transition-all duration-150 capitalize cursor-pointer {{ $isCalDaily ? 'bg-white text-slate-900 border-t-2 border-[#00bfa5] shadow-xs font-bold' : 'bg-slate-200/70 text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            Daily Schedule
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="{{ url('/configuration/calendar/worked-hours') }}"
                           data-calendar-subtab="worked-hours"
                           role="tab"
                           aria-selected="{{ $isCalHours ? 'true' : 'false' }}"
                           class="calendar-subtab-btn text-xs py-3 px-4 rounded-t leading-snug font-semibold mr-1 transform transition-all duration-150 capitalize cursor-pointer {{ $isCalHours ? 'bg-white text-slate-900 border-t-2 border-[#00bfa5] shadow-xs font-bold' : 'bg-slate-200/70 text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            Work Hours
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="{{ url('/configuration/calendar/closed-days') }}"
                           data-calendar-subtab="closed-days"
                           role="tab"
                           aria-selected="{{ $isCalClosed ? 'true' : 'false' }}"
                           class="calendar-subtab-btn text-xs py-3 px-4 rounded-t leading-snug font-semibold mr-1 transform transition-all duration-150 capitalize cursor-pointer {{ $isCalClosed ? 'bg-white text-slate-900 border-t-2 border-[#00bfa5] shadow-xs font-bold' : 'bg-slate-200/70 text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            Closed Days
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="{{ url('/configuration/calendar/default-closed-days') }}"
                           data-calendar-subtab="default-closed-days"
                           role="tab"
                           aria-selected="{{ $isCalDefaultClosed ? 'true' : 'false' }}"
                           class="calendar-subtab-btn text-xs py-3 px-4 rounded-t leading-snug font-semibold mr-1 transform transition-all duration-150 capitalize cursor-pointer {{ $isCalDefaultClosed ? 'bg-white text-slate-900 border-t-2 border-[#00bfa5] shadow-xs font-bold' : 'bg-slate-200/70 text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            Default Closed Days
                        </a>
                    </li>
                </ul>

                <!-- SUBTAB 1: Daily Schedule -->
                <div id="calendar-subtab-panel-daily-schedule" class="calendar-subtab-panel {{ $isCalDaily ? '' : 'hidden' }}">
                    <div class="bg-white relative p-6 mb-10 shadow-sm border border-t-0 border-slate-200 rounded-b-lg">
                        <!-- Top Filters Bar -->
                        <div class="flex items-center justify-between gap-4 mb-6">
                            <div class="flex items-center gap-3">
                                <!-- Month Picker -->
                                <div class="w-44">
                                    <select id="calendarMonthFilter" class="w-full text-xs font-semibold bg-white border border-slate-300 rounded px-3 py-2 text-slate-700 focus:outline-none focus:border-[#00bfa5] shadow-xs cursor-pointer">
                                        @foreach($months as $m)
                                            <option value="{{ $m }}" {{ $m === 'September 2026' ? 'selected' : '' }}>{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Location Picker -->
                                <div class="w-48">
                                    <select id="calendarLocationFilter" class="w-full text-xs font-semibold bg-white border border-slate-300 rounded px-3 py-2 text-slate-700 focus:outline-none focus:border-[#00bfa5] shadow-xs cursor-pointer">
                                        <option value="">All Locations</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc }}">{{ $loc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="calendar-daily-schedule-table">
                            <div class="overflow-x-auto relative border border-slate-200 rounded-sm">
                                <table class="w-full relative text-xs text-left border-collapse" id="calendarDailyScheduleTable">
                                    <thead role="rowgroup">
                                        <tr role="row" class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200">
                                            <th role="columnheader" class="px-3 py-4 text-center align-middle font-semibold text-xs border border-slate-200/80 w-12" rowspan="2">
                                                <label class="font-semibold items-center inline-flex text-xs cursor-pointer">
                                                    <input type="checkbox" id="calendar-select-all" class="rounded border-slate-300 text-[#00bfa5] focus:ring-0 w-4 h-4 cursor-pointer">
                                                </label>
                                            </th>
                                            <th role="columnheader" class="px-3 py-4 text-center align-middle font-semibold text-xs border border-slate-200/80 w-16" rowspan="2">
                                                ID
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold text-xs border border-slate-200/80 min-w-[10rem]" rowspan="2">
                                                Location
                                            </th>
                                            <th role="columnheader" class="px-3 py-3 align-middle font-semibold text-xs text-left border border-slate-200/80" colspan="3" style="min-width: 28rem;">
                                                Schedule
                                            </th>
                                            <th role="columnheader" class="px-3 py-4 text-center align-middle font-semibold text-xs border border-slate-200/80 w-28" rowspan="2">
                                                Make Default
                                            </th>
                                            <th role="columnheader" class="px-3 py-4 text-center align-middle font-semibold text-xs border border-slate-200/80 w-16" rowspan="2">
                                                Status
                                            </th>
                                        </tr>
                                        <tr role="row" class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200">
                                            <th role="columnheader" class="px-3 py-2.5 align-middle font-semibold text-xs text-left border border-slate-200/80 min-w-[8rem]">
                                                Day
                                            </th>
                                            <th role="columnheader" class="px-3 py-2.5 align-middle font-semibold text-xs text-left border border-slate-200/80 min-w-[10rem] w-48">
                                                1st Half
                                            </th>
                                            <th role="columnheader" class="px-3 py-2.5 align-middle font-semibold text-xs text-left border border-slate-200/80 min-w-[10rem] w-48">
                                                2nd Half
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody role="rowgroup" class="pb-3" id="calendarDailyScheduleTbody">
                                        @foreach($calendarLocations as $index => $row)
                                            @php
                                                $isEven = ($index % 2 === 1);
                                                $hasSchedule = !empty($row['schedule']);
                                            @endphp
                                            <tr role="row"
                                                class="calendar-schedule-row {{ $isEven ? 'bg-white' : 'bg-slate-50/60' }} border-b border-slate-100 hover:bg-slate-100/50 transition-colors"
                                                data-id="{{ $row['id'] }}"
                                                data-location="{{ strtolower($row['location']) }}">
                                                <!-- Checkbox -->
                                                <td role="cell" class="px-3 py-2 text-center align-middle border border-slate-100 align-top">
                                                    <label class="font-semibold items-center cursor-pointer inline-flex text-xs">
                                                        <input type="checkbox" class="calendar-row-cb rounded border-slate-300 text-[#00bfa5] focus:ring-0 w-4 h-4 cursor-pointer" value="{{ $row['id'] }}">
                                                    </label>
                                                </td>

                                                <!-- ID -->
                                                <td role="cell" class="px-3 py-2 text-center align-middle text-xs font-semibold text-slate-700 border border-slate-100 align-top">
                                                    {{ $row['id'] }}
                                                </td>

                                                <!-- Location -->
                                                <td role="cell" class="px-4 py-2 align-middle text-xs font-semibold text-slate-800 border border-slate-100 align-top">
                                                    <span>{{ $row['location'] }}</span>
                                                </td>

                                                @if($hasSchedule)
                                                    <!-- Day List -->
                                                    <td role="cell" class="px-3 py-2 align-middle text-xs font-medium text-slate-700 border border-slate-100">
                                                        @foreach($row['schedule'] as $sched)
                                                            <div class="py-2 font-semibold text-slate-800">{{ $sched['day'] }}</div>
                                                        @endforeach
                                                    </td>

                                                    <!-- 1st Half Time Pickers -->
                                                    <td role="cell" class="px-3 py-2 align-middle text-xs border border-slate-100">
                                                        @foreach($row['schedule'] as $sched)
                                                            <div class="py-1 flex items-center gap-1.5">
                                                                <input type="text"
                                                                       value="{{ $sched['first_half_start'] }}"
                                                                       class="w-20 px-1.5 py-1 text-xs border border-slate-200 rounded text-center bg-white font-medium text-slate-700 focus:outline-none focus:border-[#00bfa5]"
                                                                       placeholder="hh:mm A">
                                                                <span class="text-slate-400 font-bold px-0.5">-</span>
                                                                <input type="text"
                                                                       value="{{ $sched['first_half_end'] }}"
                                                                       class="w-20 px-1.5 py-1 text-xs border border-slate-200 rounded text-center bg-white font-medium text-slate-700 focus:outline-none focus:border-[#00bfa5]"
                                                                       placeholder="hh:mm A">
                                                            </div>
                                                        @endforeach
                                                    </td>

                                                    <!-- 2nd Half Time Pickers -->
                                                    <td role="cell" class="px-3 py-2 align-middle text-xs border border-slate-100">
                                                        @foreach($row['schedule'] as $sched)
                                                            <div class="py-1 flex items-center gap-1.5">
                                                                <input type="text"
                                                                       value="{{ $sched['second_half_start'] }}"
                                                                       class="w-20 px-1.5 py-1 text-xs border border-slate-200 rounded text-center bg-white font-medium text-slate-700 focus:outline-none focus:border-[#00bfa5]"
                                                                       placeholder="hh:mm A">
                                                                <span class="text-slate-400 font-bold px-0.5">-</span>
                                                                <input type="text"
                                                                       value="{{ $sched['second_half_end'] }}"
                                                                       class="w-20 px-1.5 py-1 text-xs border border-slate-200 rounded text-center bg-white font-medium text-slate-700 focus:outline-none focus:border-[#00bfa5]"
                                                                       placeholder="hh:mm A">
                                                            </div>
                                                        @endforeach
                                                    </td>
                                                @else
                                                    <!-- Empty Schedule Cells -->
                                                    <td role="cell" colspan="3" class="px-3 py-2 align-middle text-xs text-slate-400 border border-slate-100"></td>
                                                @endif

                                                <!-- Make Default Toggle -->
                                                <td role="cell" class="px-3 py-2 text-center align-middle border border-slate-100">
                                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                                        <input type="checkbox" name="calendar_default[{{ $row['id'] }}]" class="sr-only dds-toggle-input" {{ $row['make_default'] ? 'checked' : '' }}>
                                                        <div class="dds-toggle-track">
                                                            <div class="dds-toggle-knob">
                                                                <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </td>

                                                <!-- Status Badge -->
                                                <td role="cell" class="px-3 py-2 text-center align-middle border border-slate-100">
                                                    <div class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-[#00bfa5] text-white">
                                                        <svg width="12" height="10" viewBox="0 0 18 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="stroke-current">
                                                            <path d="M17 1L6 12L1 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr role="row" class="px-3 py-8">
                                            <td role="cell" class="px-3 py-4 text-xs border border-slate-100" colspan="8">
                                                &nbsp;
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUBTAB 2: Work Hours -->
                <div id="calendar-subtab-panel-worked-hours" class="calendar-subtab-panel {{ $isCalHours ? '' : 'hidden' }}">
                    <div class="bg-white relative p-6 mb-10 shadow-sm border border-t-0 border-slate-200 rounded-b-lg">
                        <!-- Top Filters and Actions Bar -->
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Month Picker -->
                                <div class="w-40">
                                    <select id="workHoursMonthFilter" class="w-full text-xs font-semibold bg-white border border-slate-300 rounded px-3 py-2 text-slate-700 focus:outline-none focus:border-[#00bfa5] shadow-xs cursor-pointer">
                                        @foreach($months as $m)
                                            <option value="{{ $m }}" {{ $m === 'September 2026' ? 'selected' : '' }}>{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Location Picker -->
                                <div class="w-56">
                                    <select id="workHoursLocationFilter" class="w-full text-xs font-semibold bg-white border border-slate-300 rounded px-3 py-2 text-slate-700 focus:outline-none focus:border-[#00bfa5] shadow-xs cursor-pointer">
                                        <option value="">All Locations</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc }}" {{ $loc === '8 Mile' ? 'selected' : '' }}>{{ $loc }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Range Picker -->
                                <div class="min-w-32">
                                    <select id="workHoursRangeFilter" class="w-full text-xs font-semibold bg-white border border-slate-300 rounded px-3 py-2 text-slate-700 focus:outline-none focus:border-[#00bfa5] shadow-xs cursor-pointer">
                                        <option value="Any">Any</option>
                                        <option value="Last 6 Months">Last 6 Months</option>
                                        <option value="Last 12 Months" selected>Last 12 Months</option>
                                        <option value="Last 24 Months">Last 24 Months</option>
                                    </select>
                                </div>

                                <!-- Search Providers -->
                                <div class="w-48 relative">
                                    <input type="search"
                                           id="workHoursSearchInput"
                                           placeholder="Search Providers"
                                           class="w-full text-xs font-medium bg-white border border-slate-300 rounded px-3 py-2 pl-8 text-slate-700 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] shadow-xs">
                                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none"></i>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2">
                                <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white hover:bg-[#00bfa5] hover:text-white font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                    <span>Export CSV</span>
                                </button>
                                <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-sm border-2 border-[#00bfa5] text-white bg-[#00bfa5] hover:bg-[#00a892] font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                    <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                    <span>Import CSV</span>
                                </button>
                            </div>
                        </div>

                        <!-- Work Hours Table -->
                        <div class="mt-2 overflow-x-auto relative border border-slate-200 rounded-sm shadow-xs" style="max-height: 550px;">
                            <table class="relative w-full text-xs text-left border-collapse" id="calendarWorkHoursTable">
                                <thead>
                                    <!-- Row 1: Day of Week -->
                                    <tr class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200 sticky top-0 z-20">
                                        <th class="py-3 px-3 border-r border-slate-200 bg-slate-100" style="width: 1px;"></th>
                                        <th class="py-3 px-3 border-r border-slate-200 bg-slate-100 min-w-44 sticky left-0 z-30"></th>
                                        <th class="py-3 px-3 border-r border-slate-200 bg-slate-100 min-w-32"></th>
                                        <th class="py-3 px-3 border-r border-slate-200 bg-slate-100" style="width: 1px;"></th>
                                        @foreach($workHoursDays as $day)
                                            <th class="py-2.5 px-1 text-center font-bold border-r border-slate-200 select-none {{ $day['is_weekend'] ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-800' }}" style="width: 2rem; min-width: 2rem;">
                                                {{ $day['name'] }}
                                            </th>
                                        @endforeach
                                    </tr>
                                    <!-- Row 2: Header Labels and Day Numbers -->
                                    <tr class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200 sticky top-9 z-20 shadow-xs">
                                        <th class="py-3 px-3 text-left font-semibold border-r border-slate-200 bg-slate-100 whitespace-nowrap" style="width: 1px;">
                                            Prov. ID
                                        </th>
                                        <th class="py-3 px-4 text-left font-semibold border-r border-slate-200 bg-slate-100 min-w-44 sticky left-0 z-30 whitespace-nowrap shadow-xs">
                                            Provider Name
                                        </th>
                                        <th class="py-3 px-4 text-center font-semibold border-r border-slate-200 bg-slate-100 min-w-32 whitespace-nowrap">
                                            Office Name
                                        </th>
                                        <th class="py-3 px-3 text-center font-semibold border-r border-slate-200 bg-slate-100 whitespace-nowrap w-20">
                                            Total Hrs.
                                        </th>
                                        @foreach($workHoursDays as $day)
                                            <th class="py-2.5 px-1 text-center font-bold border-r border-slate-200 select-none {{ $day['is_weekend'] ? 'bg-rose-50 text-rose-800' : 'bg-emerald-50 text-emerald-900' }}" style="width: 2rem; min-width: 2rem;">
                                                {{ $day['num'] }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($calendarWorkHoursProviders as $index => $p)
                                        @php
                                            $isEven = ($index % 2 === 1);
                                        @endphp
                                        <tr class="work-hours-row {{ $isEven ? 'bg-white' : 'bg-slate-50/70' }} border-b border-slate-100 hover:bg-slate-100/60 transition-colors"
                                            data-id="{{ $p['id'] }}"
                                            data-name="{{ strtolower($p['name']) }}"
                                            data-office="{{ strtolower($p['office']) }}">
                                            <!-- Prov. ID -->
                                            <td class="px-3 py-2 text-left font-semibold text-slate-700 border-r border-slate-100 whitespace-nowrap align-middle">
                                                {{ $p['id'] }}
                                            </td>

                                            <!-- Provider Name (Sticky Left) -->
                                            <td class="px-4 py-2 text-left font-semibold text-slate-800 border-r border-slate-100 whitespace-nowrap align-middle sticky left-0 z-10 {{ $isEven ? 'bg-white' : 'bg-[#f8fafc]' }} shadow-xs">
                                                {{ $p['name'] }}
                                            </td>

                                            <!-- Office Name -->
                                            <td class="px-4 py-2 text-center font-medium text-slate-700 border-r border-slate-100 whitespace-nowrap align-middle">
                                                {{ $p['office'] }}
                                            </td>

                                            <!-- Total Hrs. -->
                                            <td class="px-3 py-2 text-center font-bold text-slate-900 border-r border-slate-100 whitespace-nowrap align-middle provider-total-hours">
                                                {{ $p['total_hours'] }}
                                            </td>

                                            <!-- 30 Days Number Inputs -->
                                            @foreach($workHoursDays as $day)
                                                <td class="p-1 text-center border-r border-slate-100 align-middle">
                                                    <input type="number"
                                                           min="0"
                                                           max="24"
                                                           step="0.5"
                                                           name="worked_hrs[{{ $p['id'] }}][{{ $day['num'] }}]"
                                                           class="worked-hrs-input w-10 text-center text-xs py-1 px-0.5 border border-slate-200 rounded font-medium text-slate-700 focus:outline-none focus:border-[#00bfa5] bg-white"
                                                           placeholder="">
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- SUBTAB 3: Closed Days -->
                <div id="calendar-subtab-panel-closed-days" class="calendar-subtab-panel {{ $isCalClosed ? '' : 'hidden' }}">
                    <div class="bg-white relative p-6 mb-10 shadow-sm border border-t-0 border-slate-200 rounded-b-lg">
                        <!-- Top Filters and Info Bar -->
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Month Picker -->
                                <div class="w-40">
                                    <select id="closedDaysMonthFilter" class="w-full text-xs font-semibold bg-white border border-slate-300 rounded px-3 py-2 text-slate-700 focus:outline-none focus:border-[#00bfa5] shadow-xs cursor-pointer">
                                        @foreach($months as $m)
                                            <option value="{{ $m }}" {{ $m === 'September 2026' ? 'selected' : '' }}>{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Location Picker -->
                                <div class="w-48">
                                    <select id="closedDaysLocationFilter" class="w-full text-xs font-semibold bg-white border border-slate-300 rounded px-3 py-2 text-slate-700 focus:outline-none focus:border-[#00bfa5] shadow-xs cursor-pointer">
                                        <option value="">All Locations</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc }}" {{ $loc === '8 Mile' ? 'selected' : '' }}>{{ $loc }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Search Closed Day -->
                                <div class="w-48 relative">
                                    <input type="search"
                                           id="closedDaysSearchInput"
                                           placeholder="Search closed day"
                                           class="w-full text-xs font-medium bg-white border border-slate-300 rounded px-3 py-2 pl-8 text-slate-700 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] shadow-xs">
                                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none"></i>
                                </div>
                            </div>

                            <!-- Right: Smart Popover + Add Closed Day Button -->
                            <div class="flex items-center gap-2 ml-auto">
                                <!-- Smart Info Popover -->
                                <div class="relative group">
                                    <button type="button" class="p-1.5 rounded-full text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors cursor-pointer" title="Holiday Information">
                                        <svg height="16" width="16" fill="none" viewBox="0 0 15 15" class="inline-block text-slate-400 group-hover:text-slate-700">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M15 7.5a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0zm-14 0a6.5 6.5 0 1013 0 6.5 6.5 0 00-13 0z" fill="currentColor"></path>
                                            <path d="M8 12V7H7v5h1z" fill="currentColor"></path>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.5 5a.5.5 0 100-1 .5.5 0 000 1z" fill="currentColor" stroke="currentColor" stroke-width=".5"></path>
                                        </svg>
                                    </button>
                                    
                                    <div class="hidden group-hover:block absolute right-0 top-8 z-50 w-80 p-4 bg-slate-900 text-white text-xs rounded-lg shadow-xl border border-slate-800 pointer-events-none">
                                        <p class="whitespace-pre-wrap leading-relaxed text-slate-200">Jarvis will automatically consider the following Holidays as Closed in the system and will not count them as open when calculating Daily Goals.

There is no need to enter a Closed day for these holidays:
-New Year
-Memorial Day
-Independence Day
-Labor Day
-Thanksgiving
-Christmas Eve
-Christmas
-New Year's Eve</p>
                                    </div>
                                </div>

                                <!-- Add Closed Day Button -->
                                <button type="button"
                                        id="openAddClosedDayModalBtn"
                                        class="inline-flex items-center gap-1.5 px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-white bg-[#00bfa5] hover:bg-[#00a892] font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                    <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12 12" class="w-3.5 h-3.5 stroke-current">
                                        <path d="M6 0v12M0 6h12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                                    </svg>
                                    <span>Add Closed Day</span>
                                </button>
                            </div>
                        </div>

                        <!-- Closed Days Table -->
                        <div class="mt-4">
                            <div class="overflow-x-auto relative border border-slate-200 rounded-sm shadow-xs">
                                <table class="w-full relative text-xs text-left border-collapse" id="calendarClosedDaysTable">
                                    <thead role="rowgroup">
                                        <tr role="row" class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200">
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold text-xs border-r border-slate-200 cursor-pointer select-none hover:bg-slate-200/60 min-w-[12rem]">
                                                <div class="flex items-center justify-between">
                                                    <span>Name</span>
                                                    <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-400"></i>
                                                </div>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold text-xs border-r border-slate-200 min-w-[10rem]">
                                                <span>Office</span>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold text-xs border-r border-slate-200 min-w-[12rem]">
                                                <span>Description</span>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold text-xs border-r border-slate-200 cursor-pointer select-none hover:bg-slate-200/60 min-w-[10rem]">
                                                <div class="flex items-center justify-between">
                                                    <span>Date</span>
                                                    <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-400"></i>
                                                </div>
                                            </th>
                                            <th class="px-4 py-4 align-middle text-center w-20"></th>
                                        </tr>
                                    </thead>
                                    <tbody role="rowgroup" id="calendarClosedDaysTbody">
                                        @if(empty($closedDays))
                                            <tr id="emptyClosedDaysRow">
                                                <td colspan="5" class="py-12 text-center text-slate-400 text-xs font-medium">
                                                    <div class="flex flex-col items-center justify-center gap-2">
                                                        <i data-lucide="calendar-off" class="w-8 h-8 text-slate-300"></i>
                                                        <span>No closed days found for the selected month and location.</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @else
                                            @foreach($closedDays as $cd)
                                                <tr class="closed-day-row border-b border-slate-100 hover:bg-slate-50 transition-colors"
                                                    data-name="{{ strtolower($cd['name']) }}"
                                                    data-office="{{ strtolower($cd['office']) }}"
                                                    data-date="{{ $cd['date'] }}">
                                                    <td class="px-4 py-3 font-semibold text-slate-800 border-r border-slate-100">
                                                        {{ $cd['name'] }}
                                                    </td>
                                                    <td class="px-4 py-3 text-slate-700 border-r border-slate-100">
                                                        {{ $cd['office'] }}
                                                    </td>
                                                    <td class="px-4 py-3 text-slate-600 border-r border-slate-100">
                                                        {{ $cd['description'] }}
                                                    </td>
                                                    <td class="px-4 py-3 font-medium text-slate-700 border-r border-slate-100">
                                                        {{ $cd['date'] }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <button type="button" class="text-rose-500 hover:text-rose-700 text-xs font-semibold p-1 hover:bg-rose-50 rounded transition-colors delete-closed-day-btn" title="Delete">
                                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Closed Day Modal -->
                <div id="addClosedDayModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
                    <div class="bg-white rounded-lg border border-slate-200 shadow-xl max-w-md w-full p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 class="text-sm font-bold text-slate-900">Add Closed Day</h3>
                            <button type="button" id="closeAddClosedDayModalBtn" class="text-slate-400 hover:text-slate-600 p-1 rounded hover:bg-slate-100 cursor-pointer">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <form id="addClosedDayForm" class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Name *</label>
                                <input type="text" id="newClosedDayName" required placeholder="e.g. Staff Training Day" class="w-full text-xs px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#00bfa5]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Office *</label>
                                <select id="newClosedDayOffice" class="w-full text-xs px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#00bfa5] cursor-pointer">
                                    <option value="All Locations">All Locations</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc }}">{{ $loc }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Date *</label>
                                <input type="date" id="newClosedDayDate" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#00bfa5]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Description</label>
                                <textarea id="newClosedDayDesc" rows="2" placeholder="Optional description..." class="w-full text-xs px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#00bfa5]"></textarea>
                            </div>
                            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                                <button type="button" id="cancelAddClosedDayBtn" class="px-4 py-2 rounded text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">Cancel</button>
                                <button type="submit" class="px-5 py-2 rounded text-xs font-bold text-white bg-[#00bfa5] hover:bg-[#00a892] shadow-xs cursor-pointer">Add Closed Day</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- SUBTAB 4: Default Closed Days -->
                <div id="calendar-subtab-panel-default-closed-days" class="calendar-subtab-panel {{ $isCalDefaultClosed ? '' : 'hidden' }}">
                    <div class="bg-white relative p-6 mb-10 shadow-sm border border-t-0 border-slate-200 rounded-b-lg space-y-4">
                        <!-- Info / Notification Banner -->
                        <div id="defaultClosedDaysAlert" class="py-2.5 px-4 rounded-sm relative flex items-center bg-[#dcf0fa] border border-[#b8e0f5] text-[#1b4d6b] shadow-xs">
                            <div class="mr-3 shrink-0">
                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22 22" class="w-5 h-5 stroke-current">
                                    <path d="M11 1C5.477 1 1 5.477 1 11s4.477 10 10 10 10-4.477 10-10S16.523 1 11 1zM11 15v-4M11 7h-.01" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <div class="flex-1 flex items-center justify-between">
                                <div class="text-xs font-semibold">
                                    A default-closed day is open when there is a scheduled production.
                                </div>
                                <button type="button" id="dismissDefaultClosedAlertBtn" aria-label="Dismiss notification" class="opacity-50 hover:opacity-100 focus:outline-none p-1 rounded hover:bg-sky-200/50 cursor-pointer">
                                    <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="w-3.5 h-3.5 stroke-current">
                                        <path d="M3.131 2.929L16.87 16.667M3.131 16.667L16.87 2.929" stroke-width="2.5" stroke-linecap="round"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Top Filter Bar -->
                        <div class="flex flex-wrap justify-between items-center w-full">
                            <div class="relative w-48 block">
                                <input type="search"
                                       id="defaultClosedDaysSearchInput"
                                       placeholder="Search day"
                                       class="w-full text-xs font-medium bg-white border border-slate-300 rounded px-3 py-2 pl-8 text-slate-700 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] shadow-xs">
                                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="mt-4">
                            <div class="overflow-x-auto relative border border-slate-200 rounded-sm shadow-xs">
                                <table class="w-full relative text-xs text-left border-collapse" id="calendarDefaultClosedDaysTable">
                                    <thead role="rowgroup">
                                        <tr role="row" class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200">
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold text-xs border-r border-slate-200 cursor-pointer select-none hover:bg-slate-200/60 min-w-[12rem]">
                                                <div class="flex items-center justify-between">
                                                    <span>Name</span>
                                                    <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-400"></i>
                                                </div>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold text-xs border-r border-slate-200 cursor-pointer select-none hover:bg-slate-200/60 min-w-[8rem] max-w-[12rem]">
                                                <div class="flex items-center justify-between">
                                                    <span>Date</span>
                                                    <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-400"></i>
                                                </div>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold text-xs text-center w-32 min-w-[8rem]">
                                                <span>Default Closed</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody role="rowgroup" id="calendarDefaultClosedDaysTbody">
                                        @foreach($defaultClosedDays as $index => $row)
                                            @php
                                                $isEven = ($index % 2 === 1);
                                            @endphp
                                            <tr role="row"
                                                class="default-closed-day-row {{ $isEven ? 'bg-white' : 'bg-slate-50/70' }} border-b border-slate-100 hover:bg-slate-100/60 transition-colors"
                                                data-name="{{ strtolower($row['name']) }}"
                                                data-date="{{ strtolower($row['date']) }}">
                                                <td role="cell" class="px-4 py-3 font-semibold text-slate-800 border-r border-slate-100 align-middle">
                                                    {{ $row['name'] }}
                                                </td>
                                                <td role="cell" class="px-4 py-3 font-medium text-slate-700 border-r border-slate-100 align-middle">
                                                    {{ $row['date'] }}
                                                </td>
                                                <td role="cell" class="px-4 py-3 text-center align-middle">
                                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                                        <input type="checkbox" name="default_closed_day[{{ $row['id'] }}]" class="sr-only dds-toggle-input" {{ $row['default_closed'] ? 'checked' : '' }}>
                                                        <div class="dds-toggle-track">
                                                            <div class="dds-toggle-knob">
                                                                <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB: Code Mapping -->
            <div id="tab-panel-code-mapping" class="config-tab-panel {{ $activeTab === 'code-mapping' ? '' : 'hidden' }} space-y-0" company-id="42" role="subscriber" subscription="enterprise">
                @php
                    $isCmServices = ($activeSubtab === 'services' || empty($activeSubtab) || $activeSubtab === 'daily-schedule');
                    $isCmPayors = ($activeSubtab === 'payors');
                    $isCmReferrers = ($activeSubtab === 'referrers');
                    $isCmProviders = ($activeSubtab === 'providers');
                @endphp
                <!-- Subtabs Navigation -->
                <ul role="tablist" class="flex flex-grow overflow-y-hidden mt-2 mb-0 border-b border-slate-200">
                    <li role="presentation">
                        <a href="{{ url('/configuration/code-mapping/services') }}"
                           data-code-mapping-subtab="services"
                           role="tab"
                           aria-selected="{{ $isCmServices ? 'true' : 'false' }}"
                           class="code-mapping-subtab-btn text-xs py-3 px-4 rounded-t leading-snug font-semibold mr-1 transform transition-all duration-150 capitalize cursor-pointer {{ $isCmServices ? 'bg-white text-slate-900 border-t-2 border-[#00bfa5] shadow-xs font-bold' : 'bg-slate-200/70 text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            Services
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="{{ url('/configuration/code-mapping/payors') }}"
                           data-code-mapping-subtab="payors"
                           role="tab"
                           aria-selected="{{ $isCmPayors ? 'true' : 'false' }}"
                           class="code-mapping-subtab-btn text-xs py-3 px-4 rounded-t leading-snug font-semibold mr-1 transform transition-all duration-150 capitalize cursor-pointer {{ $isCmPayors ? 'bg-white text-slate-900 border-t-2 border-[#00bfa5] shadow-xs font-bold' : 'bg-slate-200/70 text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            Payors
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="{{ url('/configuration/code-mapping/referrers') }}"
                           data-code-mapping-subtab="referrers"
                           role="tab"
                           aria-selected="{{ $isCmReferrers ? 'true' : 'false' }}"
                           class="code-mapping-subtab-btn text-xs py-3 px-4 rounded-t leading-snug font-semibold mr-1 transform transition-all duration-150 capitalize cursor-pointer {{ $isCmReferrers ? 'bg-white text-slate-900 border-t-2 border-[#00bfa5] shadow-xs font-bold' : 'bg-slate-200/70 text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            Referrers
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="{{ url('/configuration/code-mapping/providers') }}"
                           data-code-mapping-subtab="providers"
                           role="tab"
                           aria-selected="{{ $isCmProviders ? 'true' : 'false' }}"
                           class="code-mapping-subtab-btn text-xs py-3 px-4 rounded-t leading-snug font-semibold mr-1 transform transition-all duration-150 capitalize cursor-pointer {{ $isCmProviders ? 'bg-white text-slate-900 border-t-2 border-[#00bfa5] shadow-xs font-bold' : 'bg-slate-200/70 text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            Providers
                        </a>
                    </li>
                </ul>

                <!-- SUBTAB 1: Services -->
                <div id="code-mapping-subtab-panel-services" class="code-mapping-subtab-panel {{ $isCmServices ? '' : 'hidden' }}">
                    <!-- List View -->
                    <div id="codeMappingServicesListView" class="{{ $activeAction === 'add' ? 'hidden' : '' }} bg-white relative p-6 mb-10 shadow-sm border border-t-0 border-slate-200 rounded-b-lg">
                        <!-- Top Filter & Actions Bar -->
                        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
                            <!-- Search -->
                            <div class="relative w-64 block">
                                <input type="search"
                                       id="codeMappingServicesSearchInput"
                                       placeholder="Search"
                                       class="w-full text-xs font-medium bg-white border border-slate-300 rounded px-3 py-2 pl-8 text-slate-700 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] shadow-xs">
                                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none"></i>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-3">
                                <a href="/configuration/code-mapping/services/import" class="inline-flex items-center gap-1.5 px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white hover:bg-[#00bfa5] hover:text-white font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                    <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                    <span>Upload CSV</span>
                                </a>
                                <a href="/configuration/code-mapping/services/add"
                                   id="openAddCodeMappingItemBtn"
                                   class="inline-flex items-center gap-1.5 px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-white bg-[#00bfa5] hover:bg-[#00a892] font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>Add Item</span>
                                </a>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="code-mapping-table">
                            <div class="overflow-x-auto relative border border-slate-200 rounded-sm shadow-xs">
                                <table class="w-full relative text-xs text-left border-collapse" id="codeMappingServicesTable">
                                    <thead role="rowgroup">
                                        <tr role="row" class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200">
                                            <th role="columnheader" class="px-3 py-4 text-center align-middle font-semibold text-xs border-r border-slate-200 w-12">
                                                <label class="font-semibold items-center opacity-50 inline-flex text-xs cursor-not-allowed">
                                                    <input type="checkbox" disabled class="rounded border-slate-300 text-[#00bfa5] w-4 h-4 cursor-not-allowed opacity-50">
                                                </label>
                                            </th>
                                            <th role="columnheader" class="px-3 py-4 text-center align-middle font-semibold text-xs border-r border-slate-200 w-16">
                                                ID
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold text-xs border-r border-slate-200 min-w-[8rem]">
                                                Base Code
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold text-xs border-r border-slate-200 min-w-[10rem]">
                                                Type
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold text-xs border-r border-slate-200 min-w-[12rem]">
                                                Service
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold text-xs border-r border-slate-200 min-w-[8rem]">
                                                Code
                                            </th>
                                            <th role="columnheader" class="px-3 py-4 text-center align-middle font-semibold text-xs w-20">
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody role="rowgroup" id="codeMappingServicesTbody">
                                        @if(empty($codeMappingServices))
                                            <tr id="emptyCodeMappingRow">
                                                <td colspan="7" class="py-6 text-center text-slate-500 text-xs font-medium bg-slate-50/50">
                                                    Click "Add Item" to add a service mapping.
                                                </td>
                                            </tr>
                                        @else
                                            @foreach($codeMappingServices as $item)
                                                <tr class="code-mapping-row border-b border-slate-100 hover:bg-slate-50 transition-colors"
                                                    data-base-code="{{ strtolower($item['base_code']) }}"
                                                    data-type="{{ strtolower($item['type']) }}"
                                                    data-service="{{ strtolower($item['service']) }}"
                                                    data-code="{{ strtolower($item['code']) }}">
                                                    <td class="px-3 py-2 text-center border-r border-slate-100 align-middle">
                                                        <input type="checkbox" class="rounded border-slate-300 text-[#00bfa5] w-4 h-4 cursor-pointer">
                                                    </td>
                                                    <td class="px-3 py-2 text-center font-semibold text-slate-700 border-r border-slate-100 align-middle">
                                                        {{ $item['id'] }}
                                                    </td>
                                                    <td class="px-4 py-3 font-semibold text-slate-800 border-r border-slate-100 align-middle">
                                                        {{ $item['base_code'] }}
                                                    </td>
                                                    <td class="px-4 py-3 text-slate-700 border-r border-slate-100 align-middle">
                                                        {{ $item['type'] }}
                                                    </td>
                                                    <td class="px-4 py-3 font-medium text-slate-800 border-r border-slate-100 align-middle">
                                                        {{ $item['service'] }}
                                                    </td>
                                                    <td class="px-4 py-3 text-slate-700 border-r border-slate-100 align-middle">
                                                        {{ $item['code'] }}
                                                    </td>
                                                    <td class="px-3 py-2 text-center align-middle">
                                                        <button type="button" class="text-slate-400 hover:text-[#00bfa5] p-1.5 rounded transition-colors edit-code-mapping-btn" title="Edit">
                                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                        </button>
                                                        <button type="button" class="text-rose-500 hover:text-rose-700 p-1.5 rounded transition-colors delete-code-mapping-btn" title="Delete">
                                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Dedicated Add Item View -->
                    <div id="codeMappingAddItemView" class="{{ $activeAction === 'add' ? '' : 'hidden' }} bg-white relative p-6 mb-10 shadow-sm border border-t-0 border-slate-200 rounded-b-lg" company-id="42" role="subscriber" subscription="enterprise">
                        <div class="flex mb-10 items-center justify-between">
                            <h3 class="mr-auto text-slate-900 font-bold text-2xl normal-case leading-normal flex items-center">
                                <a class="hover:text-[#00bfa5] cursor-pointer mr-3 text-slate-600 hover:text-slate-900 transition-colors code-mapping-back-link" href="/configuration/code-mapping/services">
                                    <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9 16" class="flex-shrink-0 align-middle inline-block w-4 h-4">
                                        <path d="M8 1L2 7.627l6 6.627" stroke="currentColor" stroke-width="2.5"></path>
                                    </svg>
                                </a> 
                                Add Item 
                            </h3>
                            <div class="flex items-center gap-3">
                                <a type="button" href="/configuration/code-mapping/services" class="appearance-none items-center py-2 px-8 justify-center select-none font-bold relative focus:outline-none ja-outline-button inline-flex rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white hover:bg-slate-50 text-xs h-9 cursor-pointer code-mapping-cancel-btn"> Cancel </a>
                                <button type="button" id="codeMappingSubmitAddBtn" disabled="disabled" class="appearance-none items-center py-2 px-8 justify-center select-none font-bold relative focus:outline-none opacity-50 pointer-events-none inline-flex rounded-sm border-2 bg-[#00bfa5] border-[#00bfa5] text-white hover:bg-[#00a892] text-xs h-9 cursor-pointer transition-all"> Add </button>
                            </div>
                        </div>

                        <!-- Form Fields -->
                        <div role="group" tabindex="-1" class="focus:outline-none mb-4 w-64 full-width-inputs">
                            <label class="font-bold mb-1 flex w-full text-xs text-slate-700"> Base Code </label>
                            <div class="relative w-full block inline-block">
                                <input type="text" id="addBaseCodeInput" placeholder="e.g. D0120" class="appearance-none rounded-sm p-2 border border-slate-300 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] text-slate-800 text-xs h-9 w-full">
                            </div>
                        </div>
                        <div role="group" tabindex="-1" class="focus:outline-none mb-4 w-64 full-width-inputs">
                            <label class="font-bold mb-1 flex w-full text-xs text-slate-700"> Type </label>
                            <div class="relative w-full inline-block">
                                <input type="text" id="addTypeInput" placeholder="e.g. Cleanings" class="appearance-none rounded-sm p-2 border border-slate-300 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] text-slate-800 text-xs h-9 w-full">
                            </div>
                        </div>
                        <div role="group" tabindex="-1" class="focus:outline-none mb-4 w-64 full-width-inputs">
                            <label class="font-bold mb-1 flex w-full text-xs text-slate-700"> Description </label>
                            <div class="relative w-full inline-block">
                                <input type="text" id="addDescriptionInput" placeholder="e.g. Routine cleaning and exam" class="appearance-none rounded-sm p-2 border border-slate-300 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] text-slate-800 text-xs h-9 w-full">
                            </div>
                        </div>

                        <!-- Arrow Divider -->
                        <div class="mt-8 mb-6 border-t border-slate-200 relative">
                            <div class="absolute -top-3 left-8 bg-white px-2 text-slate-400">
                                <svg class="w-5 h-5 text-[#00bfa5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                            </div>
                        </div>

                        <!-- Services Section -->
                        <h4 class="mb-4 text-slate-900 text-xl font-bold normal-case leading-normal"> Services </h4>
                        
                        <div class="flex flex-wrap items-center gap-3 mb-6">
                            <!-- Location Dropdown -->
                            <div role="group" tabindex="-1" class="focus:outline-none w-48">
                                <div class="relative w-full">
                                    <select id="addServiceLocationFilter" class="w-full text-xs bg-white border border-slate-300 rounded px-3 py-2 text-slate-700 focus:outline-none focus:border-[#00bfa5]">
                                        <option value="">All Locations</option>
                                        <option value="8 Mile" selected>8 Mile</option>
                                        <option value="ABKA Dental">ABKA Dental</option>
                                        <option value="Adrian">Adrian</option>
                                        <option value="Charlotte">Charlotte</option>
                                        <option value="Humble Memorial Dental">Humble Memorial Dental</option>
                                        <option value="Lansing">Lansing</option>
                                        <option value="Livernois">Livernois</option>
                                        <option value="Nassau Bay Dental">Nassau Bay Dental</option>
                                        <option value="Plymouth">Plymouth</option>
                                        <option value="Premier Image Dentistry">Premier Image Dentistry</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Search Input -->
                            <div role="group" tabindex="-1" class="focus:outline-none w-48">
                                <div class="relative w-full inline-block">
                                    <input type="search" id="addServiceSearchInput" autocomplete="off" placeholder="Search" class="appearance-none bg-white border border-slate-300 rounded-sm p-2 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] text-slate-800 text-xs h-9 w-full">
                                </div>
                            </div>
                        </div>

                        <!-- Services Selection Table -->
                        <div class="code-mapping-services-table">
                            <div class="overflow-x-auto relative border border-slate-200 rounded-sm shadow-xs">
                                <table class="w-full relative text-xs text-left border-collapse" id="codeMappingAvailableServicesTable">
                                    <thead role="rowgroup">
                                        <tr role="row" class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200">
                                            <th role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs border-r border-slate-200 w-12 text-center" style="min-width: 0.1%; width: 0.1%;">
                                                <span class="inline-block">
                                                    <label class="font-semibold items-center inline-flex text-xs cursor-pointer">
                                                        <input type="checkbox" id="addServiceSelectAllCb" class="rounded border-slate-300 text-[#00bfa5] w-4 h-4 cursor-pointer">
                                                    </label>
                                                </span>
                                            </th>
                                            <th role="columnheader" class="px-3 py-4 align-middle font-semibold cursor-pointer select-none text-xs border-r border-slate-200 w-16" style="min-width: 0.1%; width: 0.1%;">
                                                <span class="inline-block flex items-center justify-between"> ID </span>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold cursor-pointer select-none text-xs border-r border-slate-200 min-w-[10rem]">
                                                <span class="inline-block flex items-center justify-between"> Type </span>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold cursor-pointer select-none text-xs border-r border-slate-200 min-w-[16rem]">
                                                <span class="inline-block flex items-center justify-between"> Description </span>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold cursor-pointer select-none text-xs min-w-[8rem]">
                                                <span class="inline-block flex items-center justify-between"> ADA Code </span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody role="rowgroup" id="codeMappingAvailableServicesTbody">
                                        @foreach($codeMappingAvailableServices as $item)
                                            <tr role="row" class="add-service-row border-b border-slate-100 hover:bg-slate-50 transition-colors odd:bg-slate-50/40"
                                                data-id="{{ $item['id'] }}"
                                                data-type="{{ strtolower($item['type']) }}"
                                                data-description="{{ strtolower($item['description']) }}"
                                                data-ada-code="{{ strtolower($item['ada_code']) }}">
                                                <td role="cell" class="px-3 py-2 align-middle text-xs border-r border-slate-100 text-center">
                                                    <label class="font-semibold items-center inline-flex text-xs cursor-pointer">
                                                        <input type="checkbox" class="add-service-item-cb rounded border-slate-300 text-[#00bfa5] w-4 h-4 cursor-pointer"
                                                               data-id="{{ $item['id'] }}"
                                                               data-type="{{ $item['type'] }}"
                                                               data-description="{{ $item['description'] }}"
                                                               data-ada-code="{{ $item['ada_code'] }}">
                                                    </label>
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle text-xs font-semibold text-slate-700 border-r border-slate-100 add-service-cell-id">
                                                    {{ $item['id'] }}
                                                </td>
                                                <td role="cell" class="px-4 py-2 align-middle text-xs text-slate-700 border-r border-slate-100 add-service-cell-type">
                                                    {{ $item['type'] }}
                                                </td>
                                                <td role="cell" class="px-4 py-2 align-middle text-xs text-slate-600 border-r border-slate-100 add-service-cell-description">
                                                    {{ $item['description'] }}
                                                </td>
                                                <td role="cell" class="px-4 py-2 align-middle text-xs font-semibold text-slate-800 add-service-cell-ada-code">
                                                    {{ $item['ada_code'] }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination Controls -->
                            <div class="flex flex-wrap justify-between items-center text-xs mt-5 text-slate-600 gap-3">
                                <div class="flex items-center">
                                    <div tabindex="-1" class="flex items-center px-0">
                                        <label for="itemsPerPageSelect" class="hidden md:mr-2 md:inline-block font-medium">Items per page</label>
                                        <select id="itemsPerPageSelect" class="p-1.5 pr-6 rounded border border-slate-300 bg-white text-slate-700 focus:outline-none focus:border-[#00bfa5]">
                                            <option value="10" selected> 10 </option>
                                            <option value="20"> 20 </option>
                                            <option value="50"> 50 </option>
                                            <option value="100"> 100 </option>
                                        </select>
                                    </div>
                                    <div class="md:px-3 md:flex md:items-center font-medium">
                                        <span class="hidden md:inline md:mr-1">1-10</span> of 1060 items
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center px-2">
                                        <div class="mr-2">
                                            <select id="pageNumberSelect" class="p-1.5 pr-6 rounded border border-slate-300 bg-white text-slate-700 focus:outline-none focus:border-[#00bfa5]">
                                                <option value="1" selected> 1 </option>
                                                <option value="2"> 2 </option>
                                                <option value="3"> 3 </option>
                                                <option value="4"> 4 </option>
                                                <option value="5"> 5 </option>
                                            </select>
                                        </div> 
                                        of 106 <span class="ml-1 hidden md:inline">pages</span>
                                    </div>
                                    <div class="hidden md:flex md:items-center">
                                        <button disabled="disabled" type="button" class="py-1.5 px-3 rounded-l border border-slate-300 bg-white text-slate-400 opacity-50 cursor-not-allowed">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="flex-shrink-0 stroke-current"><path d="M15 3l-8 9 8 9" stroke-width="2.5"></path></svg>
                                        </button>
                                        <button type="button" class="py-1.5 px-3 rounded-r border-t border-b border-r border-slate-300 bg-white text-slate-600 hover:bg-slate-50 focus:outline-none cursor-pointer">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="flex-shrink-0 stroke-current"><path d="M9 21l8-9-8-9" stroke-width="2.5"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUBTAB 2: Payors -->
                <div id="code-mapping-subtab-panel-payors" class="code-mapping-subtab-panel {{ $isCmPayors ? '' : 'hidden' }}">
                    <!-- Payors List View -->
                    <div id="codeMappingPayorsListView" class="{{ $activeSubtab === 'payors' && $activeAction === 'add' ? 'hidden' : '' }} bg-white relative p-6 mb-10 shadow-sm border border-t-0 border-slate-200 rounded-b-lg">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                            <!-- Search -->
                            <div class="relative w-64 block">
                                <input type="search"
                                       id="codeMappingPayorsSearchInput"
                                       placeholder="Search"
                                       autocomplete="off"
                                       class="w-full text-xs font-medium bg-white border border-slate-300 rounded px-3 py-2 pl-8 text-slate-700 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] shadow-xs">
                                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none"></i>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-3">
                                <a type="button" href="/configuration/code-mapping/payors/import" class="inline-flex items-center gap-1.5 px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white hover:bg-[#00bfa5] hover:text-white font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                    <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                    <span>Upload CSV</span>
                                </a>
                                <a href="/configuration/code-mapping/payors/add" id="openAddPayorItemBtn" class="inline-flex items-center gap-1.5 px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-white bg-[#00bfa5] hover:bg-[#00a892] font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>Add Item</span>
                                </a>
                            </div>
                        </div>

                        <!-- Payors Table -->
                        <div class="code-mapping-table">
                            <div class="overflow-x-auto relative border border-slate-200 rounded-sm shadow-xs">
                                <table class="w-full relative text-xs text-left border-collapse" id="codeMappingPayorsTable">
                                    <thead role="rowgroup">
                                        <tr role="row" class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200">
                                            <th role="columnheader" class="px-3 py-4 text-center align-middle font-semibold text-xs border-r border-slate-200 w-12" style="min-width: 0.1%; width: 0.1%;">
                                                <span class="inline-block">
                                                    <label class="font-semibold items-center inline-flex text-xs cursor-pointer">
                                                        <input type="checkbox" id="payorSelectAllCb" class="rounded border-slate-300 text-[#00bfa5] w-4 h-4 cursor-pointer">
                                                    </label>
                                                </span>
                                            </th>
                                            <th role="columnheader" class="px-3 py-4 align-middle font-semibold cursor-pointer select-none text-xs border-r border-slate-200 w-16" style="min-width: 0.1%; width: 0.1%;">
                                                <span class="inline-block flex items-center justify-between"> ID </span>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold cursor-pointer select-none text-xs text-left border-r border-slate-200 min-w-[14rem]">
                                                <span class="inline-block flex items-center justify-between"> Payor Name </span>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold cursor-pointer select-none text-xs text-left border-r border-slate-200 min-w-[10rem]">
                                                <span class="inline-block flex items-center justify-between"> Uniform Name </span>
                                            </th>
                                            <th role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs text-center w-20">
                                                <span class="inline-block"> Action </span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody role="rowgroup" id="codeMappingPayorsTbody">
                                        @foreach($codeMappingPayors as $payor)
                                            <tr role="row" class="payor-row border-b border-slate-100 hover:bg-slate-50 transition-colors odd:bg-slate-50/40"
                                                data-id="{{ $payor['id'] }}"
                                                data-payor-name="{{ strtolower($payor['payor_name']) }}"
                                                data-uniform-name="{{ strtolower($payor['uniform_name']) }}">
                                                <td role="cell" class="px-3 py-2 align-middle text-xs border-r border-slate-100 text-center" style="min-width: 0.1%;">
                                                    <label class="font-semibold items-center inline-flex text-xs cursor-pointer">
                                                        <input type="checkbox" class="payor-row-cb rounded border-slate-300 text-[#00bfa5] w-4 h-4 cursor-pointer" value="{{ $payor['id'] }}">
                                                    </label>
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle text-xs font-semibold text-slate-700 border-r border-slate-100 payor-cell-id" style="min-width: 0.1%;">
                                                    {{ $payor['id'] }}
                                                </td>
                                                <td role="cell" class="px-4 py-2 align-middle text-xs font-medium text-slate-800 border-r border-slate-100 payor-cell-name" style="min-width: 10rem;">
                                                    {{ $payor['payor_name'] }}
                                                </td>
                                                <td role="cell" class="px-4 py-2 align-middle text-xs text-slate-700 border-r border-slate-100 payor-cell-uniform" style="min-width: 10rem;">
                                                    {{ $payor['uniform_name'] }}
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle text-xs text-center border-r border-slate-100" style="min-width: 0.1%;">
                                                    <div class="relative inline-block payor-action-container">
                                                        <button type="button" class="payor-action-btn text-[#00bfa5] hover:text-[#00a892] p-1 rounded focus:outline-none transition-colors cursor-pointer" title="Actions">
                                                            <i data-lucide="more-horizontal" class="w-5 h-5"></i>
                                                        </button>
                                                        <div class="payor-dropdown-menu hidden absolute right-0 mt-1 w-40 bg-white border border-slate-200 rounded shadow-lg z-30 py-1 text-left">
                                                            <button type="button" class="edit-payor-btn w-full text-xs font-semibold flex items-center px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-[#00bfa5] transition-colors cursor-pointer">
                                                                <i data-lucide="edit-3" class="w-4 h-4 mr-2.5 text-[#00bfa5]"></i>
                                                                <span>Edit Payor</span>
                                                            </button>
                                                            <button type="button" class="delete-payor-btn w-full text-xs font-semibold flex items-center px-4 py-2.5 text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                                                <i data-lucide="trash-2" class="w-4 h-4 mr-2.5 text-rose-500"></i>
                                                                <span>Delete Payor</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination Controls -->
                            <div class="flex flex-wrap justify-between items-center text-xs mt-5 text-slate-600 gap-3">
                                <div class="flex items-center md:items-stretch">
                                    <div tabindex="-1" class="flex items-center px-0">
                                        <label for="payorsItemsPerPage" class="hidden md:mr-2 md:inline-block font-medium">Items per page</label>
                                        <select id="payorsItemsPerPage" class="p-1.5 pr-6 rounded border border-slate-300 bg-white text-slate-700 focus:outline-none focus:border-[#00bfa5]">
                                            <option value="10" selected> 10 </option>
                                            <option value="20"> 20 </option>
                                            <option value="30"> 30 </option>
                                            <option value="40"> 40 </option>
                                            <option value="50"> 50 </option>
                                            <option value="60"> 60 </option>
                                        </select>
                                    </div>
                                    <div class="md:px-3 md:flex md:items-center font-medium">
                                        <span class="hidden md:inline md:mr-1">1-10</span> of 60 items
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center px-2">
                                        <div class="mr-2">
                                            <select id="payorsPageNumber" class="p-1.5 pr-6 rounded border border-slate-300 bg-white text-slate-700 focus:outline-none focus:border-[#00bfa5]">
                                                <option value="1" selected> 1 </option>
                                                <option value="2"> 2 </option>
                                                <option value="3"> 3 </option>
                                                <option value="4"> 4 </option>
                                                <option value="5"> 5 </option>
                                                <option value="6"> 6 </option>
                                            </select>
                                        </div> 
                                        of 6 <span class="ml-1 hidden md:inline">pages</span>
                                    </div>
                                    <div class="hidden md:flex md:items-center">
                                        <button disabled="disabled" type="button" class="py-1.5 px-3 rounded-l border border-slate-300 bg-white text-slate-400 opacity-50 cursor-not-allowed">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="flex-shrink-0 stroke-current"><path d="M15 3l-8 9 8 9" stroke-width="2.5"></path></svg>
                                        </button>
                                        <button type="button" class="py-1.5 px-3 rounded-r border-t border-b border-r border-slate-300 bg-white text-slate-600 hover:bg-slate-50 focus:outline-none cursor-pointer">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="flex-shrink-0 stroke-current"><path d="M9 21l8-9-8-9" stroke-width="2.5"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dedicated Add Payor Item View -->
                    <div id="codeMappingPayorAddItemView" class="{{ $activeSubtab === 'payors' && $activeAction === 'add' ? '' : 'hidden' }} bg-white relative p-6 mb-10 shadow-sm border border-t-0 border-slate-200 rounded-b-lg" company-id="42" role="subscriber" subscription="enterprise">
                        <div class="flex mb-10 items-center justify-between">
                            <h3 class="mr-auto text-slate-900 font-bold text-2xl normal-case leading-normal flex items-center">
                                <a class="hover:text-[#00bfa5] cursor-pointer mr-3 text-slate-600 hover:text-slate-900 transition-colors payor-back-link" href="/configuration/code-mapping/payors">
                                    <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9 16" class="flex-shrink-0 align-middle inline-block w-4 h-4">
                                        <path d="M8 1L2 7.627l6 6.627" stroke="currentColor" stroke-width="2.5"></path>
                                    </svg>
                                </a> 
                                Add Item 
                            </h3>
                            <div class="flex items-center gap-3">
                                <a type="button" href="/configuration/code-mapping/payors" class="appearance-none items-center py-2 px-8 justify-center select-none font-bold relative focus:outline-none ja-outline-button inline-flex rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white hover:bg-slate-50 text-xs h-9 cursor-pointer payor-cancel-btn"> Cancel </a>
                                <button type="button" id="codeMappingSubmitPayorAddBtn" disabled="disabled" class="appearance-none items-center py-2 px-8 justify-center select-none font-bold relative focus:outline-none opacity-50 pointer-events-none inline-flex rounded-sm border-2 bg-[#00bfa5] border-[#00bfa5] text-white hover:bg-[#00a892] text-xs h-9 cursor-pointer transition-all"> Add </button>
                            </div>
                        </div>

                        <!-- Datalist for Uniform Names -->
                        <datalist id="payorUniformNamesDatalist">
                            <option value="Delta"></option>
                            <option value="MetLife"></option>
                            <option value="Guardian"></option>
                            <option value="Cigna"></option>
                            <option value="Aetna"></option>
                            <option value="United Healthcare"></option>
                        </datalist>

                        <!-- Uniform Name Input -->
                        <div role="group" tabindex="-1" class="focus:outline-none w-64 full-width-inputs mb-6">
                            <label class="font-bold mb-1 flex w-full text-xs text-slate-700"> Uniform Name </label>
                            <div class="relative block w-full inline-block">
                                <input type="text" id="addPayorUniformNameInput" list="payorUniformNamesDatalist" placeholder="Delta" class="appearance-none rounded-sm p-2 border border-slate-300 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] text-slate-800 text-xs h-9 w-full">
                            </div>
                        </div>

                        <!-- Arrow Divider -->
                        <div class="mt-8 mb-6 border-t border-slate-200 relative">
                            <div class="absolute -top-3 left-8 bg-white px-2 text-slate-400">
                                <svg class="w-5 h-5 text-[#00bfa5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                            </div>
                        </div>

                        <!-- Payors Heading -->
                        <h4 class="mb-4 text-slate-900 text-xl font-bold normal-case leading-normal"> Payors </h4>

                        <!-- Filter & Action Row -->
                        <div class="flex flex-wrap items-center gap-3 mb-6">
                            <!-- Location Dropdown -->
                            <div role="group" tabindex="-1" class="focus:outline-none w-48">
                                <div class="relative w-full">
                                    <select id="addPayorLocationFilter" class="w-full text-xs bg-white border border-slate-300 rounded px-3 py-2 text-slate-700 focus:outline-none focus:border-[#00bfa5]">
                                        <option value="">All Locations</option>
                                        <option value="8 Mile" selected>8 Mile</option>
                                        <option value="ABKA Dental">ABKA Dental</option>
                                        <option value="Adrian">Adrian</option>
                                        <option value="Charlotte">Charlotte</option>
                                        <option value="Humble Memorial Dental">Humble Memorial Dental</option>
                                        <option value="Lansing">Lansing</option>
                                        <option value="Livernois">Livernois</option>
                                        <option value="Nassau Bay Dental">Nassau Bay Dental</option>
                                        <option value="Plymouth">Plymouth</option>
                                        <option value="Premier Image Dentistry">Premier Image Dentistry</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Search Input -->
                            <div role="group" tabindex="-1" class="focus:outline-none w-48">
                                <div class="relative w-full inline-block">
                                    <input type="search" id="addPayorSearchInput" autocomplete="off" placeholder="Search" class="appearance-none bg-white border border-slate-300 rounded-sm p-2 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] text-slate-800 text-xs h-9 w-full">
                                </div>
                            </div>

                            <!-- Export CSV Button -->
                            <div role="group" tabindex="-1" class="focus:outline-none ml-auto">
                                <button type="button" id="exportAvailablePayorsCsvBtn" class="border-2 appearance-none flex items-center justify-center select-none font-bold focus:outline-none rounded-sm py-2 px-6 bg-white border-[#00bfa5] text-[#00bfa5] hover:bg-[#00bfa5] hover:text-white text-xs h-9 cursor-pointer transition-colors shadow-xs">
                                    <span>Export CSV</span>
                                </button>
                            </div>
                        </div>

                        <!-- Available Payors Table -->
                        <div class="code-mapping-payors-table">
                            <div class="overflow-x-auto relative border border-slate-200 rounded-sm shadow-xs">
                                <table class="w-full relative text-xs text-left border-collapse" id="codeMappingAvailablePayorsTable">
                                    <thead role="rowgroup">
                                        <tr role="row" class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200">
                                            <th role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs border-r border-slate-200 w-12 text-center" style="min-width: 0.1%; width: 0.1%;">
                                                <span class="inline-block">
                                                    <label class="font-semibold items-center inline-flex text-xs cursor-pointer">
                                                        <input type="checkbox" id="addPayorSelectAllCb" class="rounded border-slate-300 text-[#00bfa5] w-4 h-4 cursor-pointer">
                                                    </label>
                                                </span>
                                            </th>
                                            <th role="columnheader" class="px-3 py-4 align-middle font-semibold cursor-pointer select-none text-xs border-r border-slate-200 w-16" style="min-width: 0.1%; width: 0.1%;">
                                                <span class="inline-block flex items-center justify-between"> ID </span>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold cursor-pointer select-none text-xs border-r border-slate-200 min-w-[14rem]">
                                                <span class="inline-block flex items-center justify-between"> Name </span>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold cursor-pointer select-none text-xs min-w-[10rem]">
                                                <span class="inline-block flex items-center justify-between"> Location </span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody role="rowgroup" id="codeMappingAvailablePayorsTbody">
                                        @foreach($codeMappingAvailablePayors as $item)
                                            <tr role="row" class="add-payor-row border-b border-slate-100 hover:bg-slate-50 transition-colors odd:bg-slate-50/40"
                                                data-id="{{ $item['id'] }}"
                                                data-name="{{ strtolower($item['name']) }}"
                                                data-location="{{ strtolower($item['location']) }}">
                                                <td role="cell" class="px-3 py-2 align-middle text-xs border-r border-slate-100 text-center" style="min-width: 0.1%;">
                                                    <label class="font-semibold items-center inline-flex text-xs cursor-pointer">
                                                        <input type="checkbox" class="add-payor-item-cb rounded border-slate-300 text-[#00bfa5] w-4 h-4 cursor-pointer"
                                                               data-id="{{ $item['id'] }}"
                                                               data-name="{{ $item['name'] }}"
                                                               data-location="{{ $item['location'] }}">
                                                    </label>
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle text-xs font-semibold text-slate-700 border-r border-slate-100 add-payor-cell-id" style="min-width: 0.1%;">
                                                    {{ $item['id'] }}
                                                </td>
                                                <td role="cell" class="px-4 py-2 align-middle text-xs font-medium text-slate-800 border-r border-slate-100 add-payor-cell-name" style="min-width: 10rem;">
                                                    {{ $item['name'] }}
                                                </td>
                                                <td role="cell" class="px-4 py-2 align-middle text-xs text-slate-700 add-payor-cell-location" style="min-width: 10rem;">
                                                    {{ $item['location'] }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination Controls -->
                            <div class="flex flex-wrap justify-between items-center text-xs mt-5 text-slate-600 gap-3">
                                <div class="flex items-center md:items-stretch">
                                    <div tabindex="-1" class="flex items-center px-0">
                                        <label for="addPayorsItemsPerPage" class="hidden md:mr-2 md:inline-block font-medium">Items per page</label>
                                        <select id="addPayorsItemsPerPage" class="p-1.5 pr-6 rounded border border-slate-300 bg-white text-slate-700 focus:outline-none focus:border-[#00bfa5]">
                                            @for($i = 10; $i <= 400; $i += 10)
                                                <option value="{{ $i }}" {{ $i === 10 ? 'selected' : '' }}> {{ $i }} </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="md:px-3 md:flex md:items-center font-medium">
                                        <span class="hidden md:inline md:mr-1">1-10</span> of 396 items
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center px-2">
                                        <div class="mr-2">
                                            <select id="addPayorsPageNumber" class="p-1.5 pr-6 rounded border border-slate-300 bg-white text-slate-700 focus:outline-none focus:border-[#00bfa5]">
                                                @for($p = 1; $p <= 40; $p++)
                                                    <option value="{{ $p }}" {{ $p === 1 ? 'selected' : '' }}> {{ $p }} </option>
                                                @endfor
                                            </select>
                                        </div> 
                                        of 40 <span class="ml-1 hidden md:inline">pages</span>
                                    </div>
                                    <div class="hidden md:flex md:items-center">
                                        <button disabled="disabled" type="button" class="py-1.5 px-3 rounded-l border border-slate-300 bg-white text-slate-400 opacity-50 cursor-not-allowed">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="flex-shrink-0 stroke-current"><path d="M15 3l-8 9 8 9" stroke-width="2.5"></path></svg>
                                        </button>
                                        <button type="button" class="py-1.5 px-3 rounded-r border-t border-b border-r border-slate-300 bg-white text-slate-600 hover:bg-slate-50 focus:outline-none cursor-pointer">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="flex-shrink-0 stroke-current"><path d="M9 21l8-9-8-9" stroke-width="2.5"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUBTAB 3: Referrers -->
                <div id="code-mapping-subtab-panel-referrers" class="code-mapping-subtab-panel {{ $isCmReferrers ? '' : 'hidden' }}">
                    <!-- List View -->
                    <div id="codeMappingReferrersListView" class="{{ $activeAction === 'add' ? 'hidden' : '' }} bg-white relative p-6 mb-10 shadow-sm border border-t-0 border-slate-200 rounded-b-lg">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                            <!-- Search -->
                            <div class="relative mr-auto inline-block w-64">
                                <input type="search"
                                       id="codeMappingReferrersSearchInput"
                                       placeholder="Search"
                                       autocomplete="off"
                                       class="w-full text-xs font-medium bg-white border border-slate-300 rounded px-3 py-2 pl-8 text-slate-700 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] shadow-xs">
                                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none"></i>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-3">
                                <a type="button" href="/configuration/code-mapping/referrers/import" class="inline-flex items-center gap-1.5 px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white hover:bg-[#00bfa5] hover:text-white font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                    <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                    <span>Upload CSV</span>
                                </a>
                                <button type="button" id="openAddReferrerItemBtn" class="inline-flex items-center gap-1.5 px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-white bg-[#00bfa5] hover:bg-[#00a892] font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>Add Item</span>
                                </button>
                            </div>
                        </div>

                        <!-- Referrers Table -->
                        <div class="code-mapping-table">
                            <div class="overflow-x-auto relative border border-slate-200 rounded-sm shadow-xs">
                                <table class="w-full relative text-xs text-left border-collapse" id="codeMappingReferrersTable">
                                    <thead role="rowgroup">
                                        <tr role="row" class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200">
                                            <th role="columnheader" class="px-3 py-4 text-center align-middle font-semibold text-xs border-r border-slate-200 w-12" style="min-width: 0.1%; width: 0.1%;">
                                                <span class="inline-block">
                                                    <label class="font-semibold items-center opacity-50 inline-flex text-xs {{ empty($codeMappingReferrers) ? 'cursor-not-allowed' : 'cursor-pointer' }}" id="referrerSelectAllLabel">
                                                        <input type="checkbox" id="referrerSelectAllCb" {{ empty($codeMappingReferrers) ? 'disabled="disabled"' : '' }} class="rounded border-slate-300 text-[#00bfa5] w-4 h-4 {{ empty($codeMappingReferrers) ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }}">
                                                    </label>
                                                </span>
                                            </th>
                                            <th role="columnheader" class="px-3 py-4 align-middle font-semibold cursor-pointer select-none text-xs border-r border-slate-200 w-16" style="min-width: 0.1%; width: 0.1%;">
                                                <span class="inline-block flex items-center justify-between"> ID </span>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold cursor-pointer select-none text-xs text-left border-r border-slate-200 min-w-[14rem]">
                                                <span class="inline-block flex items-center justify-between"> Referrer Name </span>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold cursor-pointer select-none text-xs text-left border-r border-slate-200 min-w-[10rem]">
                                                <span class="inline-block flex items-center justify-between"> Uniform Name </span>
                                            </th>
                                            <th role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs text-center w-20" style="min-width: 0.1%; width: 0.1%;">
                                                <span class="inline-block"> Action </span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody role="rowgroup" id="codeMappingReferrersTbody">
                                        @if(empty($codeMappingReferrers))
                                            <tr id="emptyReferrersRow" role="row" class="odd:bg-slate-50/40">
                                                <td role="cell" class="px-3 py-6 align-middle text-xs text-center text-slate-500 bg-slate-50/50" colspan="5" style="min-width: 10rem;">
                                                    Click "Add Item" to add a referrer mapping.
                                                </td>
                                            </tr>
                                        @else
                                            @foreach($codeMappingReferrers as $ref)
                                                <tr role="row" class="referrer-row border-b border-slate-100 hover:bg-slate-50 transition-colors odd:bg-slate-50/40"
                                                    data-id="{{ $ref['id'] }}"
                                                    data-referrer-name="{{ strtolower($ref['referrer_name']) }}"
                                                    data-uniform-name="{{ strtolower($ref['uniform_name']) }}">
                                                    <td role="cell" class="px-3 py-2 align-middle text-xs border-r border-slate-100 text-center" style="min-width: 0.1%;">
                                                        <label class="font-semibold items-center inline-flex text-xs cursor-pointer">
                                                            <input type="checkbox" class="referrer-row-cb rounded border-slate-300 text-[#00bfa5] w-4 h-4 cursor-pointer" value="{{ $ref['id'] }}">
                                                        </label>
                                                    </td>
                                                    <td role="cell" class="px-3 py-2 align-middle text-xs font-semibold text-slate-700 border-r border-slate-100 referrer-cell-id" style="min-width: 0.1%;">
                                                        {{ $ref['id'] }}
                                                    </td>
                                                    <td role="cell" class="px-4 py-2 align-middle text-xs font-medium text-slate-800 border-r border-slate-100 referrer-cell-name" style="min-width: 10rem;">
                                                        {{ $ref['referrer_name'] }}
                                                    </td>
                                                    <td role="cell" class="px-4 py-2 align-middle text-xs text-slate-700 border-r border-slate-100 referrer-cell-uniform" style="min-width: 10rem;">
                                                        {{ $ref['uniform_name'] }}
                                                    </td>
                                                    <td role="cell" class="px-3 py-2 align-middle text-xs text-center border-r border-slate-100" style="min-width: 0.1%;">
                                                        <div class="relative inline-block referrer-action-container">
                                                            <button type="button" class="referrer-action-btn text-[#00bfa5] hover:text-[#00a892] p-1 rounded focus:outline-none transition-colors cursor-pointer" title="Actions">
                                                                <i data-lucide="more-horizontal" class="w-5 h-5"></i>
                                                            </button>
                                                            <div class="referrer-dropdown-menu hidden absolute right-0 mt-1 w-40 bg-white border border-slate-200 rounded shadow-lg z-30 py-1 text-left">
                                                                <button type="button" class="edit-referrer-btn w-full text-xs font-semibold flex items-center px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-[#00bfa5] transition-colors cursor-pointer">
                                                                    <i data-lucide="edit-3" class="w-4 h-4 mr-2.5 text-[#00bfa5]"></i>
                                                                    <span>Edit Referrer</span>
                                                                </button>
                                                                <button type="button" class="delete-referrer-btn w-full text-xs font-semibold flex items-center px-4 py-2.5 text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                                                    <i data-lucide="trash-2" class="w-4 h-4 mr-2.5 text-rose-500"></i>
                                                                    <span>Delete Referrer</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Add Item View (Referrers) -->
                    <div id="codeMappingReferrerAddItemView" class="{{ $activeAction === 'add' ? '' : 'hidden' }} bg-white relative p-6 mb-10 shadow-sm border border-t-0 border-slate-200 rounded-b-lg" company-id="42" role="subscriber" subscription="enterprise">
                        <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100">
                            <h3 class="text-slate-900 text-2xl font-bold normal-case leading-normal flex items-center">
                                <a href="/configuration/code-mapping/referrers" class="referrer-back-link hover:text-[#00bfa5] mr-3 inline-flex items-center text-slate-600 transition-colors">
                                    <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9 16" class="w-5 h-5 flex-shrink-0 align-middle">
                                        <path d="M8 1L2 7.627l6 6.627" stroke="currentColor" stroke-width="2.5"></path>
                                    </svg>
                                </a>
                                <span>Add Item</span>
                            </h3>

                            <div class="flex items-center gap-3">
                                <a type="button" href="/configuration/code-mapping/referrers" class="referrer-cancel-btn inline-flex items-center justify-center px-8 py-2 rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white hover:bg-[#00bfa5] hover:text-white font-bold text-xs transition-colors shadow-xs cursor-pointer h-9">
                                    Cancel
                                </a>
                                <button type="button" id="codeMappingSubmitReferrerAddBtn" disabled="disabled" class="opacity-50 pointer-events-none inline-flex items-center justify-center px-8 py-2 rounded-sm border-2 border-[#00bfa5] text-white bg-[#00bfa5] hover:bg-[#00a892] font-bold text-xs transition-colors shadow-xs cursor-pointer h-9">
                                    Add
                                </button>
                            </div>
                        </div>

                        <!-- Uniform Name Input -->
                        <div role="group" tabindex="-1" class="focus:outline-none w-64 full-width-inputs mb-8">
                            <label class="font-bold mb-1 text-slate-900 flex w-full text-xs"> Uniform Name </label>
                            <div class="relative block w-full inline-block">
                                <input type="text"
                                       id="addReferrerUniformNameInput"
                                       list="referrerUniformNames"
                                       placeholder="e.g. Patient Referral"
                                       class="appearance-none rounded-sm p-2 border border-slate-300 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] w-full text-slate-800 text-xs h-9">
                            </div>
                        </div>

                        <!-- Downward Indicator Separator -->
                        <div class="mt-8 mb-6 relative pl-8">
                            <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-[#00bfa5]">
                                <svg class="w-5 h-5 text-[#00bfa5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                            </div>
                        </div>

                        <!-- Referrers Heading -->
                        <h4 class="mb-4 text-slate-900 text-xl font-bold normal-case leading-normal"> Referrers </h4>

                        <!-- Filter & Action Row -->
                        <div class="flex flex-wrap items-center gap-3 mb-6">
                            <!-- Location Dropdown -->
                            <div role="group" tabindex="-1" class="focus:outline-none w-48 mr-3">
                                <div class="relative w-full">
                                    <select id="addReferrerLocationFilter" class="w-full text-xs bg-white border border-slate-300 rounded px-3 py-2 text-slate-700 focus:outline-none focus:border-[#00bfa5]">
                                        <option value="">All Locations</option>
                                        <option value="8 Mile" selected>8 Mile</option>
                                        <option value="ABKA Dental">ABKA Dental</option>
                                        <option value="Adrian">Adrian</option>
                                        <option value="Charlotte">Charlotte</option>
                                        <option value="Humble Memorial Dental">Humble Memorial Dental</option>
                                        <option value="Lansing">Lansing</option>
                                        <option value="Livernois">Livernois</option>
                                        <option value="Nassau Bay Dental">Nassau Bay Dental</option>
                                        <option value="Plymouth">Plymouth</option>
                                        <option value="Premier Image Dentistry">Premier Image Dentistry</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Search Input -->
                            <div role="group" tabindex="-1" class="focus:outline-none w-48">
                                <div class="relative w-full inline-block">
                                    <input type="search" id="addReferrerSearchInput" autocomplete="off" placeholder="Search" class="appearance-none bg-white border border-slate-300 rounded-sm p-2 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] text-slate-800 text-xs h-9 w-full">
                                </div>
                            </div>
                        </div>

                        <!-- Available Referrers Table -->
                        <div class="code-mapping-referrers-table">
                            <div class="overflow-x-auto relative border border-slate-200 rounded-sm shadow-xs">
                                <table class="w-full relative text-xs text-left border-collapse" id="codeMappingAvailableReferrersTable">
                                    <thead role="rowgroup">
                                        <tr role="row" class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200">
                                            <th role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs border-r border-slate-200 w-12 text-center" style="min-width: 0.1%; width: 0.1%;">
                                                <span class="inline-block">
                                                    <label class="font-semibold items-center inline-flex text-xs cursor-pointer">
                                                        <input type="checkbox" id="addReferrerSelectAllCb" class="rounded border-slate-300 text-[#00bfa5] w-4 h-4 cursor-pointer">
                                                    </label>
                                                </span>
                                            </th>
                                            <th role="columnheader" class="px-3 py-4 align-middle font-semibold cursor-pointer select-none text-xs border-r border-slate-200 w-16" style="min-width: 0.1%; width: 0.1%;">
                                                <span class="inline-block flex items-center justify-between"> ID </span>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold cursor-pointer select-none text-xs border-r border-slate-200 min-w-[14rem]">
                                                <span class="inline-block flex items-center justify-between"> Name </span>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold cursor-pointer select-none text-xs min-w-[10rem]">
                                                <span class="inline-block flex items-center justify-between"> Location </span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody role="rowgroup" id="codeMappingAvailableReferrersTbody">
                                        @foreach($codeMappingAvailableReferrers as $item)
                                            <tr role="row" class="add-referrer-row border-b border-slate-100 hover:bg-slate-50 transition-colors odd:bg-slate-50/40"
                                                data-id="{{ $item['id'] }}"
                                                data-name="{{ strtolower($item['name']) }}"
                                                data-location="{{ strtolower($item['location']) }}">
                                                <td role="cell" class="px-3 py-2 align-middle text-xs border-r border-slate-100 text-center" style="min-width: 0.1%;">
                                                    <label class="font-semibold items-center inline-flex text-xs cursor-pointer">
                                                        <input type="checkbox" class="add-referrer-item-cb rounded border-slate-300 text-[#00bfa5] w-4 h-4 cursor-pointer"
                                                               data-id="{{ $item['id'] }}"
                                                               data-name="{{ $item['name'] }}"
                                                               data-location="{{ $item['location'] }}">
                                                    </label>
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle text-xs font-semibold text-slate-700 border-r border-slate-100 add-referrer-cell-id" style="min-width: 0.1%;">
                                                    {{ $item['id'] }}
                                                </td>
                                                <td role="cell" class="px-4 py-2 align-middle text-xs font-medium text-slate-800 border-r border-slate-100 add-referrer-cell-name" style="min-width: 10rem;">
                                                    {{ $item['name'] }}
                                                </td>
                                                <td role="cell" class="px-4 py-2 align-middle text-xs text-slate-700 add-referrer-cell-location" style="min-width: 10rem;">
                                                    {{ $item['location'] }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination Controls -->
                            <div class="flex flex-wrap justify-between items-center text-xs mt-5 text-slate-600 gap-3">
                                <div class="flex items-center md:items-stretch">
                                    <div tabindex="-1" class="flex items-center px-0">
                                        <label for="addReferrersItemsPerPage" class="hidden md:mr-2 md:inline-block font-medium">Items per page</label>
                                        <select id="addReferrersItemsPerPage" class="p-1.5 pr-6 rounded border border-slate-300 bg-white text-slate-700 focus:outline-none focus:border-[#00bfa5]">
                                            @for($i = 10; $i <= 180; $i += 10)
                                                <option value="{{ $i }}" {{ $i === 10 ? 'selected' : '' }}> {{ $i }} </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="md:px-3 md:flex md:items-center font-medium">
                                        <span class="hidden md:inline md:mr-1">1-10</span> of 175 items
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center px-2">
                                        <div class="mr-2">
                                            <select id="addReferrersPageNumber" class="p-1.5 pr-6 rounded border border-slate-300 bg-white text-slate-700 focus:outline-none focus:border-[#00bfa5]">
                                                @for($p = 1; $p <= 18; $p++)
                                                    <option value="{{ $p }}" {{ $p === 1 ? 'selected' : '' }}> {{ $p }} </option>
                                                @endfor
                                            </select>
                                        </div> 
                                        of 18 <span class="ml-1 hidden md:inline">pages</span>
                                    </div>
                                    <div class="hidden md:flex md:items-center">
                                        <button disabled="disabled" type="button" class="py-1.5 px-3 rounded-l border border-slate-300 bg-white text-slate-400 opacity-50 cursor-not-allowed">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="flex-shrink-0 stroke-current"><path d="M15 3l-8 9 8 9" stroke-width="2.5"></path></svg>
                                        </button>
                                        <button type="button" class="py-1.5 px-3 rounded-r border-t border-b border-r border-slate-300 bg-white text-slate-600 hover:bg-slate-50 focus:outline-none cursor-pointer">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="flex-shrink-0 stroke-current"><path d="M9 21l8-9-8-9" stroke-width="2.5"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUBTAB 4: Providers -->
                <div id="code-mapping-subtab-panel-providers" class="code-mapping-subtab-panel {{ $isCmProviders ? '' : 'hidden' }}">
                    <!-- Providers List View -->
                    <div id="codeMappingProvidersListView" class="{{ $activeAction === 'add' ? 'hidden' : '' }} bg-white relative p-6 mb-10 shadow-sm border border-t-0 border-slate-200 rounded-b-lg">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                            <!-- Search -->
                            <div class="relative mr-auto inline-block w-64">
                                <input type="search"
                                       id="codeMappingProvidersSearchInput"
                                       placeholder="Search"
                                       autocomplete="off"
                                       class="w-full text-xs font-medium bg-white border border-slate-300 rounded px-3 py-2 pl-8 text-slate-700 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] shadow-xs">
                                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none"></i>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-3">
                                <a type="button" href="/configuration/code-mapping/providers/import" class="inline-flex items-center gap-1.5 px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white hover:bg-[#00bfa5] hover:text-white font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                    <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                    <span>Upload CSV</span>
                                </a>
                                <button type="button" id="openAddProviderItemBtn" class="inline-flex items-center gap-1.5 px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-white bg-[#00bfa5] hover:bg-[#00a892] font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>Add Item</span>
                                </button>
                            </div>
                        </div>

                        <!-- Providers Table -->
                        <div class="code-mapping-table">
                            <div class="overflow-x-auto relative border border-slate-200 rounded-sm shadow-xs">
                                <table class="w-full relative text-xs text-left border-collapse" id="codeMappingProvidersTable">
                                    <thead role="rowgroup">
                                        <tr role="row" class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200">
                                            <th role="columnheader" class="px-3 py-4 text-center align-middle font-semibold text-xs border-r border-slate-200 w-12" style="min-width: 0.1%; width: 0.1%;">
                                                <span class="inline-block">
                                                    <label class="font-semibold items-center opacity-50 inline-flex text-xs {{ empty($codeMappingProviders) ? 'cursor-not-allowed' : 'cursor-pointer' }}" id="providerSelectAllLabel">
                                                        <input type="checkbox" id="providerSelectAllCb" {{ empty($codeMappingProviders) ? 'disabled="disabled"' : '' }} class="rounded border-slate-300 text-[#00bfa5] w-4 h-4 {{ empty($codeMappingProviders) ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }}">
                                                    </label>
                                                </span>
                                            </th>
                                            <th role="columnheader" class="px-3 py-4 align-middle font-semibold cursor-pointer select-none text-xs border-r border-slate-200 w-24" style="min-width: 0.1%; width: 0.1%;">
                                                <span class="inline-block flex items-center justify-between"> Provider ID </span>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold cursor-pointer select-none text-xs text-left border-r border-slate-200 min-w-[12rem]">
                                                <span class="inline-block flex items-center justify-between"> Provider Name </span>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold cursor-pointer select-none text-xs text-left border-r border-slate-200 min-w-[10rem]">
                                                <span class="inline-block flex items-center justify-between"> Location </span>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold cursor-pointer select-none text-xs text-left border-r border-slate-200 min-w-[10rem]">
                                                <span class="inline-block flex items-center justify-between"> Uniform Name </span>
                                            </th>
                                            <th role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs text-center w-20" style="min-width: 0.1%; width: 0.1%;">
                                                <span class="inline-block"> Action </span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody role="rowgroup" id="codeMappingProvidersTbody">
                                        @if(empty($codeMappingProviders))
                                            <tr id="emptyProvidersCodeMappingRow" role="row" class="odd:bg-slate-50/40">
                                                <td role="cell" class="px-3 py-6 align-middle text-xs text-center text-slate-500 bg-slate-50/50" colspan="6" style="min-width: 10rem;">
                                                    Click "Add Item" to add a provider mapping.
                                                </td>
                                            </tr>
                                        @else
                                            @foreach($codeMappingProviders as $prov)
                                                <tr role="row" class="provider-code-mapping-row border-b border-slate-100 hover:bg-slate-50 transition-colors odd:bg-slate-50/40"
                                                    data-id="{{ $prov['id'] }}"
                                                    data-provider-name="{{ strtolower($prov['provider_name']) }}"
                                                    data-location="{{ strtolower($prov['location']) }}"
                                                    data-uniform-name="{{ strtolower($prov['uniform_name']) }}">
                                                    <td role="cell" class="px-3 py-2 align-middle text-xs border-r border-slate-100 text-center" style="min-width: 0.1%;">
                                                        <label class="font-semibold items-center inline-flex text-xs cursor-pointer">
                                                            <input type="checkbox" class="provider-code-mapping-row-cb rounded border-slate-300 text-[#00bfa5] w-4 h-4 cursor-pointer" value="{{ $prov['id'] }}">
                                                        </label>
                                                    </td>
                                                    <td role="cell" class="px-3 py-2 align-middle text-xs font-semibold text-slate-700 border-r border-slate-100 provider-code-mapping-cell-id" style="min-width: 0.1%;">
                                                        {{ $prov['id'] }}
                                                    </td>
                                                    <td role="cell" class="px-4 py-2 align-middle text-xs font-medium text-slate-800 border-r border-slate-100 provider-code-mapping-cell-name" style="min-width: 12rem;">
                                                        {{ $prov['provider_name'] }}
                                                    </td>
                                                    <td role="cell" class="px-4 py-2 align-middle text-xs text-slate-700 border-r border-slate-100 provider-code-mapping-cell-location" style="min-width: 10rem;">
                                                        {{ $prov['location'] }}
                                                    </td>
                                                    <td role="cell" class="px-4 py-2 align-middle text-xs text-slate-700 border-r border-slate-100 provider-code-mapping-cell-uniform" style="min-width: 10rem;">
                                                        {{ $prov['uniform_name'] }}
                                                    </td>
                                                    <td role="cell" class="px-3 py-2 align-middle text-xs text-center border-r border-slate-100" style="min-width: 0.1%;">
                                                        <div class="relative inline-block provider-code-mapping-action-container">
                                                            <button type="button" class="provider-code-mapping-action-btn text-[#00bfa5] hover:text-[#00a892] p-1 rounded focus:outline-none transition-colors cursor-pointer" title="Actions">
                                                                <i data-lucide="more-horizontal" class="w-5 h-5"></i>
                                                            </button>
                                                            <div class="provider-code-mapping-dropdown-menu hidden absolute right-0 mt-1 w-40 bg-white border border-slate-200 rounded shadow-lg z-30 py-1 text-left">
                                                                <button type="button" class="edit-provider-code-mapping-btn w-full text-xs font-semibold flex items-center px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-[#00bfa5] transition-colors cursor-pointer">
                                                                    <i data-lucide="edit-3" class="w-4 h-4 mr-2.5 text-[#00bfa5]"></i>
                                                                    <span>Edit Provider</span>
                                                                </button>
                                                                <button type="button" class="delete-provider-code-mapping-btn w-full text-xs font-semibold flex items-center px-4 py-2.5 text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                                                    <i data-lucide="trash-2" class="w-4 h-4 mr-2.5 text-rose-500"></i>
                                                                    <span>Delete Provider</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Add Item View (Providers) -->
                    <div id="codeMappingProviderAddItemView" class="{{ $activeAction === 'add' ? '' : 'hidden' }} bg-white relative p-6 mb-10 shadow-sm border border-t-0 border-slate-200 rounded-b-lg" company-id="42" role="subscriber" subscription="enterprise">
                        <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100">
                            <h3 class="text-slate-900 text-2xl font-bold normal-case leading-normal flex items-center">
                                <a href="/configuration/code-mapping/providers" class="provider-back-link hover:text-[#00bfa5] mr-3 inline-flex items-center text-slate-600 transition-colors">
                                    <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9 16" class="w-5 h-5 flex-shrink-0 align-middle">
                                        <path d="M8 1L2 7.627l6 6.627" stroke="currentColor" stroke-width="2.5"></path>
                                    </svg>
                                </a>
                                <span>Add Item</span>
                            </h3>

                            <div class="flex items-center gap-3">
                                <a type="button" href="/configuration/code-mapping/providers" class="provider-cancel-btn inline-flex items-center justify-center px-8 py-2 rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white hover:bg-[#00bfa5] hover:text-white font-bold text-xs transition-colors shadow-xs cursor-pointer h-9">
                                    Cancel
                                </a>
                                <button type="button" id="codeMappingSubmitProviderAddBtn" disabled="disabled" class="opacity-50 pointer-events-none inline-flex items-center justify-center px-8 py-2 rounded-sm border-2 border-[#00bfa5] text-white bg-[#00bfa5] hover:bg-[#00a892] font-bold text-xs transition-colors shadow-xs cursor-pointer h-9">
                                    Add
                                </button>
                            </div>
                        </div>

                        <!-- Uniform Name Input -->
                        <datalist id="providerUniformNamesDatalist">
                            <option value="General Dentist"></option>
                            <option value="Hygienist"></option>
                            <option value="Orthodontist"></option>
                            <option value="Periodontist"></option>
                            <option value="Endodontist"></option>
                            <option value="Oral Surgeon"></option>
                            <option value="Pediatric Dentist"></option>
                        </datalist>
                        <div role="group" tabindex="-1" class="focus:outline-none w-64 full-width-inputs mb-8">
                            <label class="font-bold mb-1 text-slate-900 flex w-full text-xs"> Uniform Name </label>
                            <div class="relative block w-full inline-block">
                                <input type="text"
                                       id="addProviderUniformNameInput"
                                       list="providerUniformNamesDatalist"
                                       placeholder="e.g. General Dentist"
                                       class="appearance-none rounded-sm p-2 border border-slate-300 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] w-full text-slate-800 text-xs h-9">
                            </div>
                        </div>

                        <!-- Downward Indicator Separator -->
                        <div class="mt-8 mb-6 relative pl-8">
                            <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-[#00bfa5]">
                                <svg class="w-5 h-5 text-[#00bfa5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                            </div>
                        </div>

                        <!-- Providers Heading -->
                        <h4 class="mb-4 text-slate-900 text-xl font-bold normal-case leading-normal"> Providers </h4>

                        <!-- Filter & Action Row -->
                        <div class="grid gap-2 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 mb-6">
                            <!-- Location Dropdown -->
                            <div role="group" tabindex="-1" class="focus:outline-none">
                                <label class="font-bold mb-1 text-slate-900 flex w-full text-xs">Location</label>
                                <div class="relative w-full">
                                    <select id="addProviderLocationFilter" class="w-full text-xs bg-white border border-slate-300 rounded px-3 py-2 text-slate-700 focus:outline-none focus:border-[#00bfa5]">
                                        <option value="">All Locations</option>
                                        <option value="8 Mile">8 Mile</option>
                                        <option value="ABKA Dental">ABKA Dental</option>
                                        <option value="Adrian">Adrian</option>
                                        <option value="Charlotte">Charlotte</option>
                                        <option value="Humble Memorial Dental">Humble Memorial Dental</option>
                                        <option value="Lansing">Lansing</option>
                                        <option value="Livernois">Livernois</option>
                                        <option value="Nassau Bay Dental">Nassau Bay Dental</option>
                                        <option value="Plymouth">Plymouth</option>
                                        <option value="Premier Image Dentistry">Premier Image Dentistry</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Has Production Dropdown -->
                            <div role="group" tabindex="-1" class="focus:outline-none">
                                <label class="font-bold mb-1 text-slate-900 flex w-full text-xs">Has Production</label>
                                <div class="relative w-full">
                                    <select id="addProviderProductionFilter" class="w-full text-xs bg-white border border-slate-300 rounded px-3 py-2 text-slate-700 focus:outline-none focus:border-[#00bfa5]">
                                        <option value="Any">Any</option>
                                        <option value="Last 6 Months">Last 6 Months</option>
                                        <option value="Last 12 Months" selected>Last 12 Months</option>
                                        <option value="Last 24 Months">Last 24 Months</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Search Input -->
                            <div role="group" tabindex="-1" class="focus:outline-none">
                                <label class="font-bold mb-1 text-slate-900 flex w-full text-xs">Search</label>
                                <div class="relative w-full inline-block">
                                    <input type="search" id="addProviderSearchInput" autocomplete="off" placeholder="Search providers..." class="appearance-none bg-white border border-slate-300 rounded-sm p-2 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] text-slate-800 text-xs h-9 w-full">
                                </div>
                            </div>
                        </div>

                        <!-- Available Providers Table -->
                        <div class="code-mapping-providers-table">
                            <div class="overflow-x-auto relative border border-slate-200 rounded-sm shadow-xs">
                                <table class="w-full relative text-xs text-left border-collapse" id="codeMappingAvailableProvidersTable">
                                    <thead role="rowgroup">
                                        <tr role="row" class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200">
                                            <th role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs border-r border-slate-200 w-12 text-center" style="min-width: 0.1%; width: 0.1%;">
                                                <span class="inline-block">
                                                    <label class="font-semibold items-center inline-flex text-xs cursor-pointer">
                                                        <input type="checkbox" id="addProviderSelectAllCb" class="rounded border-slate-300 text-[#00bfa5] w-4 h-4 cursor-pointer">
                                                    </label>
                                                </span>
                                            </th>
                                            <th role="columnheader" class="px-3 py-4 align-middle font-semibold cursor-pointer select-none text-xs border-r border-slate-200 w-16" style="min-width: 0.1%; width: 0.1%;">
                                                <span class="inline-block flex items-center justify-between"> ID </span>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold cursor-pointer select-none text-xs border-r border-slate-200 min-w-[14rem]">
                                                <span class="inline-block flex items-center justify-between"> Name </span>
                                            </th>
                                            <th role="columnheader" class="px-4 py-4 align-middle font-semibold cursor-pointer select-none text-xs min-w-[10rem]">
                                                <span class="inline-block flex items-center justify-between"> Location </span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody role="rowgroup" id="codeMappingAvailableProvidersTbody">
                                        @foreach($codeMappingAvailableProviders as $item)
                                            <tr role="row" class="add-provider-row border-b border-slate-100 hover:bg-slate-50 transition-colors odd:bg-slate-50/40"
                                                data-id="{{ $item['id'] }}"
                                                data-name="{{ strtolower($item['name']) }}"
                                                data-location="{{ strtolower($item['location']) }}">
                                                <td role="cell" class="px-3 py-2 align-middle text-xs border-r border-slate-100 text-center" style="min-width: 0.1%;">
                                                    <label class="font-semibold items-center inline-flex text-xs cursor-pointer">
                                                        <input type="checkbox" class="add-provider-item-cb rounded border-slate-300 text-[#00bfa5] w-4 h-4 cursor-pointer"
                                                               data-id="{{ $item['id'] }}"
                                                               data-name="{{ $item['name'] }}"
                                                               data-location="{{ $item['location'] }}">
                                                    </label>
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle text-xs font-semibold text-slate-700 border-r border-slate-100 add-provider-cell-id" style="min-width: 0.1%;">
                                                    {{ $item['id'] }}
                                                </td>
                                                <td role="cell" class="px-4 py-2 align-middle text-xs font-medium text-slate-800 border-r border-slate-100 add-provider-cell-name" style="min-width: 10rem;">
                                                    {{ $item['name'] }}
                                                </td>
                                                <td role="cell" class="px-4 py-2 align-middle text-xs text-slate-700 add-provider-cell-location" style="min-width: 10rem;">
                                                    {{ $item['location'] }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination Controls -->
                            <div class="flex flex-wrap justify-between items-center text-xs mt-5 text-slate-600 gap-3">
                                <div class="flex items-center md:items-stretch">
                                    <div tabindex="-1" class="flex items-center px-0">
                                        <label for="addProvidersItemsPerPage" class="hidden md:mr-2 md:inline-block font-medium">Items per page</label>
                                        <select id="addProvidersItemsPerPage" class="p-1.5 pr-6 rounded border border-slate-300 bg-white text-slate-700 focus:outline-none focus:border-[#00bfa5]">
                                            @for($i = 10; $i <= 110; $i += 10)
                                                <option value="{{ $i }}" {{ $i === 10 ? 'selected' : '' }}> {{ $i }} </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="md:px-3 md:flex md:items-center font-medium">
                                        <span class="hidden md:inline md:mr-1">1-10</span> of 109 items
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center px-2">
                                        <div class="mr-2">
                                            <select id="addProvidersPageNumber" class="p-1.5 pr-6 rounded border border-slate-300 bg-white text-slate-700 focus:outline-none focus:border-[#00bfa5]">
                                                @for($p = 1; $p <= 11; $p++)
                                                    <option value="{{ $p }}" {{ $p === 1 ? 'selected' : '' }}> {{ $p }} </option>
                                                @endfor
                                            </select>
                                        </div> 
                                        of 11 <span class="ml-1 hidden md:inline">pages</span>
                                    </div>
                                    <div class="hidden md:flex md:items-center">
                                        <button disabled="disabled" type="button" class="py-1.5 px-3 rounded-l border border-slate-300 bg-white text-slate-400 opacity-50 cursor-not-allowed">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="flex-shrink-0 stroke-current"><path d="M15 3l-8 9 8 9" stroke-width="2.5"></path></svg>
                                        </button>
                                        <button type="button" class="py-1.5 px-3 rounded-r border-t border-b border-r border-slate-300 bg-white text-slate-600 hover:bg-slate-50 focus:outline-none cursor-pointer">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="flex-shrink-0 stroke-current"><path d="M9 21l8-9-8-9" stroke-width="2.5"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit / Add Code Mapping Item Modal -->
            <div id="codeMappingItemModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-lg border border-slate-200 shadow-xl max-w-md w-full p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 id="codeMappingModalTitle" class="text-xl font-bold text-slate-900">Edit Items</h2>
                        <button type="button" id="closeCodeMappingModalBtn" class="text-slate-400 hover:text-slate-600 p-1 rounded hover:bg-slate-100 cursor-pointer">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <form id="codeMappingItemForm" class="space-y-4 pt-1">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Base Code</label>
                            <input type="text" id="codeMappingBaseCode" required placeholder="e.g. D0120" class="w-full text-xs px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#00bfa5] bg-white text-slate-800">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Type</label>
                            <input type="text" id="codeMappingType" required placeholder="e.g. Preventive" class="w-full text-xs px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#00bfa5] bg-white text-slate-800">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Service</label>
                            <input type="text" id="codeMappingService" required placeholder="e.g. Periodic Oral Eval" class="w-full text-xs px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#00bfa5] bg-white text-slate-800">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Description</label>
                            <input type="text" id="codeMappingDesc" placeholder="e.g. Routine 6-month checkup" class="w-full text-xs px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#00bfa5] bg-white text-slate-800">
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" id="cancelCodeMappingModalBtn" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white hover:bg-slate-50 font-bold text-xs transition-colors cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" id="submitCodeMappingBtn" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-white bg-[#00bfa5] hover:bg-[#00a892] font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Set Uniform Name Modal -->
            <div id="setUniformNameModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-lg border border-slate-200 shadow-xl max-w-md w-full p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 class="text-xl font-bold text-slate-900">Set Uniform Name</h2>
                        <button type="button" id="closeSetUniformNameModalBtn" class="text-slate-400 hover:text-slate-600 p-1 rounded hover:bg-slate-100 cursor-pointer">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <form id="setUniformNameForm" class="space-y-4 pt-1">
                        <datalist id="uniformNames">
                            <option value="Delta"></option>
                            <option value="MetLife"></option>
                            <option value="Guardian"></option>
                            <option value="Cigna"></option>
                            <option value="Aetna"></option>
                            <option value="United Healthcare"></option>
                        </datalist>
                        <div role="group" tabindex="-1" class="focus:outline-none">
                            <label class="block text-xs font-bold text-slate-700 mb-1"> Uniform name: </label>
                            <div class="relative w-full block">
                                <input type="text" id="uniformNameInput" list="uniformNames" placeholder="e.g. Delta" class="appearance-none rounded-sm p-2 border border-slate-300 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] w-full text-slate-800 text-xs h-9">
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" id="cancelSetUniformNameBtn" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white hover:bg-slate-50 font-bold text-xs transition-colors cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-white bg-[#00bfa5] hover:bg-[#00a892] font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Set Referrer Uniform Name Modal -->
            <div id="setReferrerUniformNameModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-lg border border-slate-200 shadow-xl max-w-md w-full p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 class="text-xl font-bold text-slate-900">Set Uniform Name</h2>
                        <button type="button" id="closeSetReferrerUniformNameModalBtn" class="text-slate-400 hover:text-slate-600 p-1 rounded hover:bg-slate-100 cursor-pointer">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <form id="setReferrerUniformNameForm" class="space-y-4 pt-1">
                        <datalist id="referrerUniformNames">
                            <option value="Google Search"></option>
                            <option value="Patient Referral"></option>
                            <option value="Direct Mail"></option>
                            <option value="Social Media"></option>
                            <option value="Insurance Website"></option>
                            <option value="Doctor Referral"></option>
                        </datalist>
                        <div role="group" tabindex="-1" class="focus:outline-none">
                            <label class="block text-xs font-bold text-slate-700 mb-1"> Referrer name: </label>
                            <div class="relative w-full block mb-3">
                                <input type="text" id="referrerNameInput" placeholder="e.g. Google Ads Campaign" class="appearance-none rounded-sm p-2 border border-slate-300 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] w-full text-slate-800 text-xs h-9">
                            </div>
                            <label class="block text-xs font-bold text-slate-700 mb-1"> Uniform name: </label>
                            <div class="relative w-full block">
                                <input type="text" id="referrerUniformNameInput" list="referrerUniformNames" placeholder="e.g. Google Search" class="appearance-none rounded-sm p-2 border border-slate-300 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] w-full text-slate-800 text-xs h-9">
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" id="cancelSetReferrerUniformNameBtn" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white hover:bg-slate-50 font-bold text-xs transition-colors cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-white bg-[#00bfa5] hover:bg-[#00a892] font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Set Provider Code Mapping Uniform Name Modal -->
            <div id="setProviderCodeMappingUniformNameModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-lg border border-slate-200 shadow-xl max-w-md w-full p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 class="text-xl font-bold text-slate-900">Set Uniform Name</h2>
                        <button type="button" id="closeSetProviderCodeMappingModalBtn" class="text-slate-400 hover:text-slate-600 p-1 rounded hover:bg-slate-100 cursor-pointer">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <form id="setProviderCodeMappingUniformNameForm" class="space-y-4 pt-1">
                        <datalist id="providerCodeMappingUniformNames">
                            <option value="General Dentist"></option>
                            <option value="Hygienist"></option>
                            <option value="Orthodontist"></option>
                            <option value="Periodontist"></option>
                            <option value="Endodontist"></option>
                            <option value="Oral Surgeon"></option>
                            <option value="Pediatric Dentist"></option>
                        </datalist>
                        <div role="group" tabindex="-1" class="focus:outline-none">
                            <label class="block text-xs font-bold text-slate-700 mb-1"> Provider name: </label>
                            <div class="relative w-full block mb-3">
                                <input type="text" id="providerCodeMappingNameInput" placeholder="e.g. Dr. John Doe" class="appearance-none rounded-sm p-2 border border-slate-300 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] w-full text-slate-800 text-xs h-9">
                            </div>
                            <label class="block text-xs font-bold text-slate-700 mb-1"> Location: </label>
                            <div class="relative w-full block mb-3">
                                <select id="providerCodeMappingLocationInput" class="appearance-none rounded-sm p-2 border border-slate-300 focus:outline-none focus:border-[#00bfa5] w-full text-slate-800 text-xs h-9 bg-white">
                                    <option value="8 Mile">8 Mile</option>
                                    <option value="ABKA Dental">ABKA Dental</option>
                                    <option value="Adrian">Adrian</option>
                                    <option value="Charlotte">Charlotte</option>
                                    <option value="Humble Memorial Dental">Humble Memorial Dental</option>
                                    <option value="Lansing">Lansing</option>
                                    <option value="Livernois">Livernois</option>
                                    <option value="Nassau Bay Dental">Nassau Bay Dental</option>
                                    <option value="Plymouth">Plymouth</option>
                                    <option value="Premier Image Dentistry">Premier Image Dentistry</option>
                                </select>
                            </div>
                            <label class="block text-xs font-bold text-slate-700 mb-1"> Uniform name: </label>
                            <div class="relative w-full block">
                                <input type="text" id="providerCodeMappingUniformNameInput" list="providerCodeMappingUniformNames" placeholder="e.g. General Dentist" class="appearance-none rounded-sm p-2 border border-slate-300 placeholder-slate-400 focus:outline-none focus:border-[#00bfa5] w-full text-slate-800 text-xs h-9">
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" id="cancelSetProviderCodeMappingBtn" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white hover:bg-slate-50 font-bold text-xs transition-colors cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-white bg-[#00bfa5] hover:bg-[#00a892] font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB: KPIs -->
            <div id="tab-panel-kpis" class="config-tab-panel {{ $activeTab === 'kpis' ? '' : 'hidden' }} space-y-0" company-id="42" role="subscriber" subscription="enterprise">
                @php
                    $isKpiMain = in_array($activeSubtab, ['main', '']);
                    $isKpiProviders = ($activeSubtab === 'providers');
                    $isKpiSpecialty = ($activeSubtab === 'specialty');
                    $isKpiSpecialtyProviders = ($activeSubtab === 'specialty-providers');
                    $isKpiCustom = ($activeSubtab === 'custom');
                    $isKpiCustomCreate = ($isKpiCustom && $activeKpiCategory === 'create');

                    $mainCat = in_array($activeKpiCategory, ['hygiene', 'doctor', 'office']) ? $activeKpiCategory : 'hygiene';
                    $specialtyCat = in_array($activeKpiCategory, ['endo', 'perio', 'ortho', 'os', 'pedo']) ? $activeKpiCategory : 'endo';
                @endphp

                <!-- Subtab Navigation -->
                <ul role="tablist" class="flex flex-grow overflow-y-hidden mt-4" id="kpis-subtab-nav">
                    <li role="presentation">
                        <a href="{{ url('/configuration/kpis/main/' . $mainCat) }}"
                           data-kpi-subtab="main"
                           role="tab"
                           aria-selected="{{ $isKpiMain ? 'true' : 'false' }}"
                           class="kpi-subtab-btn text-xs py-3 rounded-t leading-snug block focus:outline-none font-semibold px-4 mr-1 transform transition-transform duration-150 hover:text-primary-300 capitalize hover:bg-white whitespace-no-wrap cursor-pointer {{ $isKpiMain ? 'translate-y-1 bg-white text-black dark:bg-gray-700 dark:text-white' : 'text-gray-400 bg-gray-100 dark:bg-gray-600' }}">
                            Main
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="{{ url('/configuration/kpis/providers/' . $mainCat) }}"
                           data-kpi-subtab="providers"
                           role="tab"
                           aria-selected="{{ $isKpiProviders ? 'true' : 'false' }}"
                           class="kpi-subtab-btn text-xs py-3 rounded-t leading-snug block focus:outline-none font-semibold px-4 mr-1 transform transition-transform duration-150 hover:text-primary-300 capitalize hover:bg-white whitespace-no-wrap cursor-pointer {{ $isKpiProviders ? 'translate-y-1 bg-white text-black dark:bg-gray-700 dark:text-white' : 'text-gray-400 bg-gray-100 dark:bg-gray-600' }}">
                            Providers
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="{{ url('/configuration/kpis/specialty/' . $specialtyCat) }}"
                           data-kpi-subtab="specialty"
                           role="tab"
                           aria-selected="{{ $isKpiSpecialty ? 'true' : 'false' }}"
                           class="kpi-subtab-btn text-xs py-3 rounded-t leading-snug block focus:outline-none font-semibold px-4 mr-1 transform transition-transform duration-150 hover:text-primary-300 capitalize hover:bg-white whitespace-no-wrap cursor-pointer {{ $isKpiSpecialty ? 'translate-y-1 bg-white text-black dark:bg-gray-700 dark:text-white' : 'text-gray-400 bg-gray-100 dark:bg-gray-600' }}">
                            Specialty
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="{{ url('/configuration/kpis/specialty-providers/' . $specialtyCat) }}"
                           data-kpi-subtab="specialty-providers"
                           role="tab"
                           aria-selected="{{ $isKpiSpecialtyProviders ? 'true' : 'false' }}"
                           class="kpi-subtab-btn text-xs py-3 rounded-t leading-snug block focus:outline-none font-semibold px-4 mr-1 transform transition-transform duration-150 hover:text-primary-300 capitalize hover:bg-white whitespace-no-wrap cursor-pointer {{ $isKpiSpecialtyProviders ? 'translate-y-1 bg-white text-black dark:bg-gray-700 dark:text-white' : 'text-gray-400 bg-gray-100 dark:bg-gray-600' }}">
                            Specialty Providers
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="{{ url('/configuration/kpis/custom/index') }}"
                           data-kpi-subtab="custom"
                           role="tab"
                           aria-selected="{{ $isKpiCustom ? 'true' : 'false' }}"
                           class="kpi-subtab-btn text-xs py-3 rounded-t leading-snug block focus:outline-none font-semibold px-4 mr-1 transform transition-transform duration-150 hover:text-primary-300 capitalize hover:bg-white whitespace-no-wrap cursor-pointer {{ $isKpiCustom ? 'translate-y-1 bg-white text-black dark:bg-gray-700 dark:text-white' : 'text-gray-400 bg-gray-100 dark:bg-gray-600' }}">
                            Custom KPIs
                        </a>
                    </li>
                </ul>

                <!-- KPI Panel Container -->
                <div id="kpisMainContainer" class="{{ $isKpiCustomCreate ? 'hidden' : '' }} bg-white relative p-6 dark:bg-gray-700 mb-10 shadow">
                    <!-- Standard Top Action / Filter Bar (Main, Providers, Specialty) -->
                    <div id="kpisStandardTopBar" class="{{ $isKpiCustom ? 'hidden' : 'flex' }} items-center flex-wrap gap-2 md:gap-0">
                        <!-- Location Multiselect -->
                        <div tabindex="-1" class="multiselect w-56 relative" id="kpisLocationMultiselect">
                            <button type="button" id="kpisLocationBtn" class="multiselect__tags w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-sm p-2 text-xs flex items-center justify-between text-left cursor-pointer focus:outline-none">
                                <span class="multiselect__single text-slate-800 dark:text-white font-medium" id="kpisLocationSelectedText">8 Mile</span>
                                <div class="multiselect__select text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </button>
                            <div tabindex="-1" id="kpisLocationDropdown" class="multiselect__content-wrapper absolute left-0 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg z-50 max-h-60 overflow-y-auto hidden">
                                <ul class="multiselect__content py-1 text-xs">
                                    @foreach($locations as $loc)
                                        <li class="multiselect__element">
                                            <button type="button" class="kpi-location-item w-full px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-white flex items-center justify-between {{ $loc === '8 Mile' ? 'bg-[#00bfa5]/10 font-bold text-[#00bfa5]' : '' }}" data-location="{{ $loc }}">
                                                <span>{{ $loc }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <!-- Category / Specialty Multiselect -->
                        <div tabindex="0" class="multiselect w-40 ml-1 relative" id="kpisCategoryMultiselect">
                            <button type="button" id="kpisCategoryBtn" class="multiselect__tags w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-sm p-2 text-xs flex items-center justify-between text-left cursor-pointer focus:outline-none">
                                <span class="multiselect__single text-slate-800 dark:text-white font-medium capitalize" id="kpisCategorySelectedText">
                                    @if($isKpiSpecialty || $isKpiSpecialtyProviders)
                                        {{ $kpiSpecialties[$specialtyCat] ?? 'Endo' }}
                                    @elseif($isKpiCustom)
                                        Custom
                                    @else
                                        {{ $kpiMainCategories[$mainCat] ?? 'Hygiene' }}
                                    @endif
                                </span>
                                <div class="multiselect__select text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </button>
                            <div tabindex="-1" id="kpisCategoryDropdown" class="multiselect__content-wrapper absolute left-0 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg z-50 max-h-60 overflow-y-auto hidden">
                                <ul class="multiselect__content py-1 text-xs" id="kpisCategoryOptionsList">
                                    <!-- Main / Provider items -->
                                    <li class="multiselect__element cat-opt-group cat-group-main {{ ($isKpiSpecialty || $isKpiSpecialtyProviders || $isKpiCustom) ? 'hidden' : '' }}">
                                        <button type="button" class="kpi-category-item w-full px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-white flex items-center justify-between {{ $mainCat === 'hygiene' ? 'font-bold text-[#00bfa5] bg-[#00bfa5]/10' : '' }}" data-category="hygiene">
                                            <span>Hygiene</span>
                                        </button>
                                    </li>
                                    <li class="multiselect__element cat-opt-group cat-group-main {{ ($isKpiSpecialty || $isKpiSpecialtyProviders || $isKpiCustom) ? 'hidden' : '' }}">
                                        <button type="button" class="kpi-category-item w-full px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-white flex items-center justify-between {{ $mainCat === 'doctor' ? 'font-bold text-[#00bfa5] bg-[#00bfa5]/10' : '' }}" data-category="doctor">
                                            <span>Doctor</span>
                                        </button>
                                    </li>
                                    <li class="multiselect__element cat-opt-group cat-group-main cat-opt-office {{ ($isKpiSpecialty || $isKpiSpecialtyProviders || $isKpiCustom || $isKpiProviders) ? 'hidden' : '' }}">
                                        <button type="button" class="kpi-category-item w-full px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-white flex items-center justify-between {{ $mainCat === 'office' ? 'font-bold text-[#00bfa5] bg-[#00bfa5]/10' : '' }}" data-category="office">
                                            <span>Office</span>
                                        </button>
                                    </li>
                                    <!-- Specialty items -->
                                    @foreach($kpiSpecialties as $specKey => $specLabel)
                                        <li class="multiselect__element cat-opt-group cat-group-specialty {{ ($isKpiSpecialty || $isKpiSpecialtyProviders) ? '' : 'hidden' }}">
                                            <button type="button" class="kpi-category-item w-full px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-white flex items-center justify-between {{ $specialtyCat === $specKey ? 'font-bold text-[#00bfa5] bg-[#00bfa5]/10' : '' }}" data-category="{{ $specKey }}">
                                                <span>{{ $specLabel }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <!-- Search KPIs Input -->
                        <div class="relative ml-1 block">
                            <input type="search" id="kpisSearchInput" autocomplete="off" placeholder="Search KPIs" class="appearance-none bg-white dark:bg-gray-800 dark:text-white border border-gray-700 dark:border-gray-800 rounded-sm p-2 placeholder-gray-400 dark:placeholder-white focus:outline-none focus:border-ja-green-200 dark:focus:border-ja-green-200 focus:bg-gray-000 bg-search dark:bg-search w-full text-xs h-9">
                        </div>

                        <!-- Provider / Specialty right spacer -->
                        <div class="flex items-center ml-auto {{ ($isKpiProviders || $isKpiSpecialtyProviders || $isKpiSpecialty) ? '' : 'hidden' }}" id="kpisProviderRightSpacer"></div>

                        <!-- Main actions container -->
                        <div class="flex items-center ml-auto flex-wrap gap-2 md:gap-0 {{ ($isKpiProviders || $isKpiSpecialtyProviders || $isKpiSpecialty || $isKpiCustom) ? 'hidden' : '' }}" id="kpisMainActions">
                            <!-- Group Averages toggle -->
                            <div class="mr-3 mt-2">
                                <label for="toggle-2" class="relative inline-flex items-center cursor-pointer select-none dark:text-white text-xs">
                                    <input type="checkbox" id="toggle-2" class="sr-only dds-toggle-input" checked>
                                    <div class="dds-toggle-track mr-2">
                                        <div class="dds-toggle-knob">
                                            <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </div>
                                    Group Averages
                                </label>
                            </div>

                            <!-- Export CSV -->
                            <button type="button" id="kpisExportCsvBtn" class="px-8 appearance-none flex items-center justify-center select-none font-bold focus:outline-none rounded-sm border-2 py-2 px-3 bg-white dark:bg-gray-800 border-ja-green-200 dark:text-white hover:text-white hover:bg-ja-green-200 dark:hover:bg-ja-green-200 text-xs h-9 cursor-pointer w-full transition-colors" style="width: unset;">
                                <span>Export CSV</span>
                            </button>

                            <!-- Import CSV -->
                            <div class="ml-1" id="kpisImportCsvWrapper">
                                <button type="button" id="kpisImportCsvBtn" class="appearance-none items-center py-2 px-8 justify-center select-none font-bold relative focus:outline-none ml-1 inline-flex rounded-sm border-2 bg-ja-green-200 border-ja-green-200 text-white dark:text-white hover:bg-white dark:hover:bg-gray-800 hover:text-ja-green-200 text-xs h-9 cursor-pointer transition-colors">
                                    Import CSV
                                </button>
                                <input type="file" id="kpisImportFileInput" accept=".csv" class="hidden">
                            </div>
                        </div>
                    </div>

                    <!-- Custom KPIs Top Action / Filter Bar -->
                    <div id="kpisCustomTopBar" class="{{ $isKpiCustom ? 'flex' : 'hidden' }} items-center">
                        <div class="md:flex items-center justify-between w-full">
                            <div class="md:flex items-center flex-wrap gap-1 md:gap-0">
                                <!-- 1. Line of Business Select -->
                                <div class="relative md:mr-1 mb-1 w-48" id="kpisCustomLobContainer">
                                    <select id="kpisCustomLobSelect" class="w-full text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-sm px-3 py-2 text-slate-700 dark:text-white focus:outline-none focus:border-[#00bfa5] h-9 cursor-pointer">
                                        <option value="">Line of Business</option>
                                        @foreach($customLinesOfBusiness as $lob)
                                            <option value="{{ $lob }}">{{ $lob }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 2. Transaction Type Select -->
                                <div class="relative md:mr-1 mb-1 w-48" id="kpisCustomTxTypeContainer">
                                    <select id="kpisCustomTxTypeSelect" class="w-full text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-sm px-3 py-2 text-slate-700 dark:text-white focus:outline-none focus:border-[#00bfa5] h-9 cursor-pointer">
                                        <option value="">Transaction Type</option>
                                        @foreach($customTransactionTypes as $txType)
                                            <option value="{{ $txType }}">{{ $txType }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 3. KPI Type Select -->
                                <div class="relative mb-1 w-48" id="kpisCustomKpiTypeContainer">
                                    <select id="kpisCustomKpiTypeSelect" class="w-full text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-sm px-3 py-2 text-slate-700 dark:text-white focus:outline-none focus:border-[#00bfa5] h-9 cursor-pointer">
                                        <option value="">KPI Type</option>
                                        @foreach($customKpiTypes as $kpiType)
                                            <option value="{{ $kpiType }}">{{ $kpiType }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <a href="/configuration/kpis/custom/create" id="openAddKpiBtn" class="appearance-none items-center py-2 px-8 justify-center select-none font-bold relative focus:outline-none md:ml-1 inline-flex rounded-sm border-2 bg-ja-green-200 border-ja-green-200 text-white dark:text-white hover:bg-white dark:hover:bg-gray-800 hover:text-ja-green-200 text-xs h-9 cursor-pointer"> Add KPI </a>
                        </div>
                    </div>

                    <!-- KPI Tables Container -->
                    <div class="mt-4">
                        <div class="">
                            <div class="overflow-x-auto relative">
                                <table class="w-full relative" id="kpisDataTable">
                                    <thead role="rowgroup" id="kpisStandardThead" class="{{ $isKpiCustom ? 'hidden' : '' }}">
                                        <tr role="row" class="">
                                            <th role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border text-center" style="min-width: 0.1%;">
                                                <span class="inline-block">Enabled</span>
                                            </th>
                                            <th role="columnheader" class="px-3 py-4 align-middle font-semibold w-3/12 text-xs bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border text-left" style="min-width: 0.1%;">
                                                <span class="inline-block">Kpi</span>
                                            </th>
                                            <th role="columnheader" class="px-3 py-4 align-middle font-semibold text-center text-xs bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border" style="min-width: 10rem; max-width: initial;">
                                                <span class="inline-block">Description</span>
                                            </th>
                                            <th role="columnheader" class="kpi-th-goal px-3 py-4 align-middle font-semibold w-2/12 text-center text-xs bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border {{ ($isKpiProviders || $isKpiSpecialtyProviders || $isKpiSpecialty) ? 'hidden' : '' }}" style="min-width: initial; max-width: initial;">
                                                <span class="inline-block">Goal</span>
                                            </th>
                                            <th role="columnheader" class="kpi-th-actions px-3 py-4 align-middle font-semibold text-xs bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border text-center {{ ($isKpiProviders || $isKpiSpecialtyProviders || $isKpiSpecialty) ? 'hidden' : '' }}" style="min-width: 1%;">
                                                <span class="inline-block">Actions</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <thead role="rowgroup" id="kpisCustomThead" class="{{ $isKpiCustom ? '' : 'hidden' }}">
                                        <tr role="row" class="">
                                            <th data-v-7c11c636="" role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border" style="min-width: 0.1%;"><span data-v-7c11c636="" class="inline-block">KPI Name</span></th>
                                            <th data-v-7c11c636="" role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border" style="min-width: 0.1%;"><span data-v-7c11c636="" class="inline-block">Transaction</span></th>
                                            <th data-v-7c11c636="" role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border" style="min-width: 0.1%;"><span data-v-7c11c636="" class="inline-block">Display</span></th>
                                            <th data-v-7c11c636="" role="columnheader" class="px-3 py-4 align-middle font-semibold text-center text-xs bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border" style="min-width: 10rem; max-width: initial;"><span data-v-7c11c636="" class="inline-block"> Description </span></th>
                                            <th data-v-7c11c636="" role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border" style="min-width: 1%;"><span data-v-7c11c636="" class="inline-block"> Actions </span></th>
                                        </tr>
                                    </thead>
                                    <tbody role="rowgroup" id="kpisTableTbody">
                                        <!-- Provider Hygiene KPIs -->
                                        @foreach($providerHygieneKpis as $kpi)
                                            <tr role="row" class="kpi-row cat-hygiene subtab-providers odd:bg-gray-000 dark:odd:bg-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-750 transition-colors {{ ($mainCat === 'hygiene' && $isKpiProviders) ? '' : 'hidden' }}"
                                                data-kpi-id="{{ $kpi['id'] }}"
                                                data-subtab-group="providers"
                                                data-category="hygiene"
                                                data-kpi-name="{{ strtolower($kpi['name']) }}"
                                                data-kpi-desc="{{ strtolower($kpi['description']) }}">
                                                <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border text-center" style="min-width: 0.1%;">
                                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                                        <input type="checkbox" class="sr-only dds-toggle-input kpi-enable-toggle kpi-provider-toggle" value="" {{ $kpi['enabled'] ? 'checked' : '' }}>
                                                        <div class="dds-toggle-track">
                                                            <div class="dds-toggle-knob">
                                                                <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle w-3/12 text-xs text-left dark:text-white border-white dark:border-gray-800 border" style="min-width: 0.1%;">
                                                    <div class="flex items-center justify-between">
                                                        <span>{{ $kpi['name'] }}</span>
                                                        <span class="rounded text-white relative inline-flex items-center leading-none font-semibold px-3 py-2 {{ $kpi['enabled'] ? 'bg-light-green enable-tag dark:bg-dark-green' : 'bg-gray-300 dark:bg-gray-900 dark:text-gray-1000' }} text-xs h-6 rounded-sm kpi-status-badge">{{ $kpi['enabled'] ? 'Enabled' : 'Disabled' }}</span>
                                                    </div>
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle overflow-ellipsis overflow-hidden text-xs dark:text-white border-white dark:border-gray-800 border kpi-cell-description" style="min-width: initial; max-width: initial;">
                                                    {{ $kpi['description'] }}
                                                </td>
                                            </tr>
                                        @endforeach

                                        <!-- Provider Doctor KPIs -->
                                        @foreach($providerDoctorKpis as $kpi)
                                            <tr role="row" class="kpi-row cat-doctor subtab-providers odd:bg-gray-000 dark:odd:bg-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-750 transition-colors {{ ($mainCat === 'doctor' && $isKpiProviders) ? '' : 'hidden' }}"
                                                data-kpi-id="{{ $kpi['id'] }}"
                                                data-subtab-group="providers"
                                                data-category="doctor"
                                                data-kpi-name="{{ strtolower($kpi['name']) }}"
                                                data-kpi-desc="{{ strtolower($kpi['description']) }}">
                                                <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border text-center" style="min-width: 0.1%;">
                                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                                        <input type="checkbox" class="sr-only dds-toggle-input kpi-enable-toggle kpi-provider-toggle" value="" {{ $kpi['enabled'] ? 'checked' : '' }}>
                                                        <div class="dds-toggle-track">
                                                            <div class="dds-toggle-knob">
                                                                <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle w-3/12 text-xs text-left dark:text-white border-white dark:border-gray-800 border" style="min-width: 0.1%;">
                                                    <div class="flex items-center justify-between">
                                                        <span>{{ $kpi['name'] }}</span>
                                                        <span class="rounded text-white relative inline-flex items-center leading-none font-semibold px-3 py-2 {{ $kpi['enabled'] ? 'bg-light-green enable-tag dark:bg-dark-green' : 'bg-gray-300 dark:bg-gray-900 dark:text-gray-1000' }} text-xs h-6 rounded-sm kpi-status-badge">{{ $kpi['enabled'] ? 'Enabled' : 'Disabled' }}</span>
                                                    </div>
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle overflow-ellipsis overflow-hidden text-xs dark:text-white border-white dark:border-gray-800 border kpi-cell-description" style="min-width: initial; max-width: initial;">
                                                    {{ $kpi['description'] }}
                                                </td>
                                            </tr>
                                        @endforeach

                                        <!-- Main Hygiene KPIs -->
                                        @foreach($hygieneKpis as $kpi)
                                            <tr role="row" class="kpi-row cat-hygiene subtab-main odd:bg-gray-000 dark:odd:bg-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-750 transition-colors {{ ($mainCat === 'hygiene' && $isKpiMain) ? '' : 'hidden' }}"
                                                data-kpi-id="{{ $kpi['id'] }}"
                                                data-subtab-group="main"
                                                data-category="hygiene"
                                                data-kpi-name="{{ strtolower($kpi['name']) }}"
                                                data-kpi-desc="{{ strtolower($kpi['description']) }}">
                                                <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border text-center" style="min-width: 0.1%;">
                                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                                        <input type="checkbox" class="sr-only dds-toggle-input kpi-enable-toggle" value="" {{ $kpi['enabled'] ? 'checked' : '' }}>
                                                        <div class="dds-toggle-track">
                                                            <div class="dds-toggle-knob">
                                                                <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle w-3/12 text-xs text-left dark:text-white border-white dark:border-gray-800 border" style="min-width: 0.1%;">
                                                    <div class="flex items-center justify-between font-semibold kpi-cell-name">
                                                        {{ $kpi['name'] }}
                                                    </div>
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle overflow-ellipsis overflow-hidden text-xs dark:text-white border-white dark:border-gray-800 border kpi-cell-description" style="min-width: initial; max-width: initial;">
                                                    {{ $kpi['description'] }}
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle text-center w-2/12 goal-cell-td goal-cell text-xs dark:text-white border-white dark:border-gray-800 border" style="min-width: initial; max-width: initial;">
                                                    <div class="flex justify-between space-x-2">
                                                        <input type="number" step="any" placeholder="0.0" value="{{ $kpi['goal'] }}" debounce-events="keyup" class="w-full text-right border-gray-400 dark:bg-gray-800 rounded-sm p-1 border text-xs dark:text-white focus:outline-none focus:border-ja-green-200 kpi-cell-goal">
                                                        <button type="button" class="kpi-reset-goal-btn focus:outline-none hover:opacity-80" title="Reset Goal">
                                                            <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle inline-block w-5 h-5 text-ja-green-200">
                                                                <path d="M15.363 4.773c2.318 1.267 3.909 3.734 4.392 5.701a8.5 8.5 0 11-16.653.708C3.317 9.815 4.044 8 5.272 6.5" stroke="currentColor" stroke-width="2"></path>
                                                                <path d="M14.59 9.74V4h5.74" stroke="currentColor" stroke-width="2"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border text-center" style="min-width: 1%;">
                                                    <div class="relative lels text-center kpi-action-container">
                                                        <span class="flex justify-center">
                                                            <button type="button" class="text-ja-green-200 focus:outline-none kpi-action-menu-btn" title="Actions">
                                                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle inline-block w-6 h-6">
                                                                    <path fill="currentColor" d="M2 10h4v4H2zM10 10h4v4h-4zM18 10h4v4h-4z"></path>
                                                                </svg>
                                                            </button>
                                                        </span>
                                                        <div class="bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-800 dark:text-white z-50 bg-pointer mt-3 hidden absolute right-0 kpi-dropdown-menu rounded-sm text-left">
                                                            <button type="button" class="kpi-edit-btn flex items-center px-4 py-3 text-xs font-semibold focus:outline-none focus:bg-gray-000 hover:bg-gray-100 dark:hover:bg-gray-700 dark:bg-gray-800 w-full" style="outline: none;">
                                                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle mr-1 inline-block w-5 h-5 text-ja-green-200">
                                                                    <path d="M10.5 4H1v19h19v-9.5" stroke="currentColor" stroke-width="2"></path>
                                                                    <path d="M19 1.52l3.561 3.562-11.574 11.574-5.045 1.484 1.484-5.046L19 1.52zM15.977 4.543l3.562 3.562" stroke="currentColor" stroke-width="2" stroke-linejoin="round"></path>
                                                                </svg>
                                                                Edit KPI
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach

                                        <!-- Main Doctor KPIs -->
                                        @foreach($doctorKpis as $kpi)
                                            <tr role="row" class="kpi-row cat-doctor subtab-main odd:bg-gray-000 dark:odd:bg-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-750 transition-colors {{ ($mainCat === 'doctor' && $isKpiMain) ? '' : 'hidden' }}"
                                                data-kpi-id="{{ $kpi['id'] }}"
                                                data-subtab-group="main"
                                                data-category="doctor"
                                                data-kpi-name="{{ strtolower($kpi['name']) }}"
                                                data-kpi-desc="{{ strtolower($kpi['description']) }}">
                                                <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border text-center" style="min-width: 0.1%;">
                                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                                        <input type="checkbox" class="sr-only dds-toggle-input kpi-enable-toggle" value="" {{ $kpi['enabled'] ? 'checked' : '' }}>
                                                        <div class="dds-toggle-track">
                                                            <div class="dds-toggle-knob">
                                                                <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle w-3/12 text-xs text-left dark:text-white border-white dark:border-gray-800 border" style="min-width: 0.1%;">
                                                    <div class="flex items-center justify-between font-semibold kpi-cell-name">
                                                        {{ $kpi['name'] }}
                                                    </div>
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle overflow-ellipsis overflow-hidden text-xs dark:text-white border-white dark:border-gray-800 border kpi-cell-description" style="min-width: initial; max-width: initial;">
                                                    {{ $kpi['description'] }}
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle text-center w-2/12 goal-cell-td goal-cell text-xs dark:text-white border-white dark:border-gray-800 border" style="min-width: initial; max-width: initial;">
                                                    <div class="flex justify-between space-x-2">
                                                        <input type="number" step="any" placeholder="0.0" value="{{ $kpi['goal'] }}" debounce-events="keyup" class="w-full text-right border-gray-400 dark:bg-gray-800 rounded-sm p-1 border text-xs dark:text-white focus:outline-none focus:border-ja-green-200 kpi-cell-goal">
                                                        <button type="button" class="kpi-reset-goal-btn focus:outline-none hover:opacity-80" title="Reset Goal">
                                                            <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle inline-block w-5 h-5 text-ja-green-200">
                                                                <path d="M15.363 4.773c2.318 1.267 3.909 3.734 4.392 5.701a8.5 8.5 0 11-16.653.708C3.317 9.815 4.044 8 5.272 6.5" stroke="currentColor" stroke-width="2"></path>
                                                                <path d="M14.59 9.74V4h5.74" stroke="currentColor" stroke-width="2"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border text-center" style="min-width: 1%;">
                                                    <div class="relative lels text-center kpi-action-container">
                                                        <span class="flex justify-center">
                                                            <button type="button" class="text-ja-green-200 focus:outline-none kpi-action-menu-btn" title="Actions">
                                                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle inline-block w-6 h-6">
                                                                    <path fill="currentColor" d="M2 10h4v4H2zM10 10h4v4h-4zM18 10h4v4h-4z"></path>
                                                                </svg>
                                                            </button>
                                                        </span>
                                                        <div class="bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-800 dark:text-white z-50 bg-pointer mt-3 hidden absolute right-0 kpi-dropdown-menu rounded-sm text-left">
                                                            <button type="button" class="kpi-edit-btn flex items-center px-4 py-3 text-xs font-semibold focus:outline-none focus:bg-gray-000 hover:bg-gray-100 dark:hover:bg-gray-700 dark:bg-gray-800 w-full" style="outline: none;">
                                                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle mr-1 inline-block w-5 h-5 text-ja-green-200">
                                                                    <path d="M10.5 4H1v19h19v-9.5" stroke="currentColor" stroke-width="2"></path>
                                                                    <path d="M19 1.52l3.561 3.562-11.574 11.574-5.045 1.484 1.484-5.046L19 1.52zM15.977 4.543l3.562 3.562" stroke="currentColor" stroke-width="2" stroke-linejoin="round"></path>
                                                                </svg>
                                                                Edit KPI
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach

                                        <!-- Main Office KPIs -->
                                        @foreach($officeKpis as $kpi)
                                            <tr role="row" class="kpi-row cat-office subtab-main odd:bg-gray-000 dark:odd:bg-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-750 transition-colors {{ ($mainCat === 'office' && $isKpiMain) ? '' : 'hidden' }}"
                                                data-kpi-id="{{ $kpi['id'] }}"
                                                data-subtab-group="main"
                                                data-category="office"
                                                data-kpi-name="{{ strtolower($kpi['name']) }}"
                                                data-kpi-desc="{{ strtolower($kpi['description']) }}">
                                                <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border text-center" style="min-width: 0.1%;">
                                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                                        <input type="checkbox" class="sr-only dds-toggle-input kpi-enable-toggle" value="" {{ $kpi['enabled'] ? 'checked' : '' }}>
                                                        <div class="dds-toggle-track">
                                                            <div class="dds-toggle-knob">
                                                                <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle w-3/12 text-xs text-left dark:text-white border-white dark:border-gray-800 border" style="min-width: 0.1%;">
                                                    <div class="flex items-center justify-between font-semibold kpi-cell-name">
                                                        {{ $kpi['name'] }}
                                                    </div>
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle overflow-ellipsis overflow-hidden text-xs dark:text-white border-white dark:border-gray-800 border kpi-cell-description" style="min-width: initial; max-width: initial;">
                                                    {{ $kpi['description'] }}
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle text-center w-2/12 goal-cell-td goal-cell text-xs dark:text-white border-white dark:border-gray-800 border" style="min-width: initial; max-width: initial;">
                                                    <div class="flex justify-between space-x-2">
                                                        <input type="number" step="any" placeholder="0.0" value="{{ $kpi['goal'] }}" debounce-events="keyup" class="w-full text-right border-gray-400 dark:bg-gray-800 rounded-sm p-1 border text-xs dark:text-white focus:outline-none focus:border-ja-green-200 kpi-cell-goal">
                                                        <button type="button" class="kpi-reset-goal-btn focus:outline-none hover:opacity-80" title="Reset Goal">
                                                            <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle inline-block w-5 h-5 text-ja-green-200">
                                                                <path d="M15.363 4.773c2.318 1.267 3.909 3.734 4.392 5.701a8.5 8.5 0 11-16.653.708C3.317 9.815 4.044 8 5.272 6.5" stroke="currentColor" stroke-width="2"></path>
                                                                <path d="M14.59 9.74V4h5.74" stroke="currentColor" stroke-width="2"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border text-center" style="min-width: 1%;">
                                                    <div class="relative lels text-center kpi-action-container">
                                                        <span class="flex justify-center">
                                                            <button type="button" class="text-ja-green-200 focus:outline-none kpi-action-menu-btn" title="Actions">
                                                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle inline-block w-6 h-6">
                                                                    <path fill="currentColor" d="M2 10h4v4H2zM10 10h4v4h-4zM18 10h4v4h-4z"></path>
                                                                </svg>
                                                            </button>
                                                        </span>
                                                        <div class="bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-800 dark:text-white z-50 bg-pointer mt-3 hidden absolute right-0 kpi-dropdown-menu rounded-sm text-left">
                                                            <button type="button" class="kpi-edit-btn flex items-center px-4 py-3 text-xs font-semibold focus:outline-none focus:bg-gray-000 hover:bg-gray-100 dark:hover:bg-gray-700 dark:bg-gray-800 w-full" style="outline: none;">
                                                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle mr-1 inline-block w-5 h-5 text-ja-green-200">
                                                                    <path d="M10.5 4H1v19h19v-9.5" stroke="currentColor" stroke-width="2"></path>
                                                                    <path d="M19 1.52l3.561 3.562-11.574 11.574-5.045 1.484 1.484-5.046L19 1.52zM15.977 4.543l3.562 3.562" stroke="currentColor" stroke-width="2" stroke-linejoin="round"></path>
                                                                </svg>
                                                                Edit KPI
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach

                                        <!-- Specialty KPIs (Endo, Perio, Ortho, OS, Pedo) -->
                                        @php
                                            $specialtiesList = [
                                                'endo' => $endoKpis,
                                                'perio' => $perioKpis,
                                                'ortho' => $orthoKpis,
                                                'os' => $osKpis,
                                                'pedo' => $pedoKpis,
                                            ];
                                        @endphp
                                        @foreach($specialtiesList as $specKey => $specItems)
                                            @foreach($specItems as $kpi)
                                                <!-- Specialty (3 columns with status badge) -->
                                                <tr role="row" class="kpi-row cat-{{ $specKey }} subtab-specialty odd:bg-gray-000 dark:odd:bg-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-750 transition-colors {{ ($specialtyCat === $specKey && $isKpiSpecialty) ? '' : 'hidden' }}"
                                                    data-kpi-id="{{ $kpi['id'] }}"
                                                    data-subtab-group="specialty"
                                                    data-category="{{ $specKey }}"
                                                    data-kpi-name="{{ strtolower($kpi['name']) }}"
                                                    data-kpi-desc="{{ strtolower($kpi['description']) }}">
                                                    <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border text-center" style="min-width: 0.1%;">
                                                        <label class="relative inline-flex items-center cursor-pointer select-none">
                                                            <input type="checkbox" class="sr-only dds-toggle-input kpi-enable-toggle kpi-provider-toggle" value="" {{ $kpi['enabled'] ? 'checked' : '' }}>
                                                            <div class="dds-toggle-track">
                                                                <div class="dds-toggle-knob">
                                                                    <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </td>
                                                    <td role="cell" class="px-3 py-2 align-middle w-3/12 text-xs text-left dark:text-white border-white dark:border-gray-800 border" style="min-width: 0.1%;">
                                                        <div class="flex items-center justify-between">
                                                            <span>{{ $kpi['name'] }}</span>
                                                            <span class="rounded text-white relative inline-flex items-center leading-none font-semibold px-3 py-2 {{ $kpi['enabled'] ? 'bg-light-green enable-tag dark:bg-dark-green' : 'bg-gray-300 dark:bg-gray-900 dark:text-gray-1000' }} text-xs h-6 rounded-sm kpi-status-badge">{{ $kpi['enabled'] ? 'Enabled' : 'Disabled' }}</span>
                                                        </div>
                                                    </td>
                                                    <td role="cell" class="px-3 py-2 align-middle overflow-ellipsis overflow-hidden text-xs dark:text-white border-white dark:border-gray-800 border kpi-cell-description" style="min-width: initial; max-width: initial;">
                                                        {{ $kpi['description'] }}
                                                    </td>
                                                </tr>

                                                <!-- Specialty Providers (3 columns with status badge) -->
                                                <tr role="row" class="kpi-row cat-{{ $specKey }} subtab-specialty-providers odd:bg-gray-000 dark:odd:bg-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-750 transition-colors {{ ($specialtyCat === $specKey && $isKpiSpecialtyProviders) ? '' : 'hidden' }}"
                                                    data-kpi-id="{{ $kpi['id'] }}"
                                                    data-subtab-group="specialty-providers"
                                                    data-category="{{ $specKey }}"
                                                    data-kpi-name="{{ strtolower($kpi['name']) }}"
                                                    data-kpi-desc="{{ strtolower($kpi['description']) }}">
                                                    <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border text-center" style="min-width: 0.1%;">
                                                        <label class="relative inline-flex items-center cursor-pointer select-none">
                                                            <input type="checkbox" class="sr-only dds-toggle-input kpi-enable-toggle kpi-provider-toggle" value="" {{ $kpi['enabled'] ? 'checked' : '' }}>
                                                            <div class="dds-toggle-track">
                                                                <div class="dds-toggle-knob">
                                                                    <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </td>
                                                    <td role="cell" class="px-3 py-2 align-middle w-3/12 text-xs text-left dark:text-white border-white dark:border-gray-800 border" style="min-width: 0.1%;">
                                                        <div class="flex items-center justify-between">
                                                            <span>{{ $kpi['name'] }}</span>
                                                            <span class="rounded text-white relative inline-flex items-center leading-none font-semibold px-3 py-2 {{ $kpi['enabled'] ? 'bg-light-green enable-tag dark:bg-dark-green' : 'bg-gray-300 dark:bg-gray-900 dark:text-gray-1000' }} text-xs h-6 rounded-sm kpi-status-badge">{{ $kpi['enabled'] ? 'Enabled' : 'Disabled' }}</span>
                                                        </div>
                                                    </td>
                                                    <td role="cell" class="px-3 py-2 align-middle overflow-ellipsis overflow-hidden text-xs dark:text-white border-white dark:border-gray-800 border kpi-cell-description" style="min-width: initial; max-width: initial;">
                                                        {{ $kpi['description'] }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach

                                        <!-- Custom KPIs -->
                                        @foreach($customKpis as $kpi)
                                            <tr role="row" class="kpi-row cat-custom subtab-custom odd:bg-gray-000 dark:odd:bg-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-750 transition-colors {{ $isKpiCustom ? '' : 'hidden' }}"
                                                data-kpi-id="{{ $kpi['id'] }}"
                                                data-subtab-group="custom"
                                                data-category="custom"
                                                data-line-of-business="{{ strtolower($kpi['line_of_business'] ?? '') }}"
                                                data-transaction="{{ strtolower($kpi['transaction'] ?? '') }}"
                                                data-display="{{ strtolower($kpi['display'] ?? '') }}"
                                                data-kpi-name="{{ strtolower($kpi['name']) }}"
                                                data-kpi-desc="{{ strtolower($kpi['description']) }}">
                                                <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border font-semibold kpi-cell-name">
                                                    {{ $kpi['name'] }}
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border kpi-cell-transaction">
                                                    {{ $kpi['transaction'] }}
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border kpi-cell-display">
                                                    {{ $kpi['display'] }}
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle overflow-ellipsis overflow-hidden text-xs dark:text-white border-white dark:border-gray-800 border kpi-cell-description" style="min-width: 10rem;">
                                                    {{ $kpi['description'] }}
                                                </td>
                                                <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border text-center" style="min-width: 1%;">
                                                    <div class="relative lels text-center kpi-action-container">
                                                        <span class="flex justify-center">
                                                            <button type="button" class="text-ja-green-200 focus:outline-none kpi-action-menu-btn" title="Actions">
                                                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle inline-block w-6 h-6">
                                                                    <path fill="currentColor" d="M2 10h4v4H2zM10 10h4v4h-4zM18 10h4v4h-4z"></path>
                                                                </svg>
                                                            </button>
                                                        </span>
                                                        <div class="bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-800 dark:text-white z-50 bg-pointer mt-3 hidden absolute right-0 kpi-dropdown-menu rounded-sm text-left">
                                                            <button type="button" class="kpi-edit-btn flex items-center px-4 py-3 text-xs font-semibold focus:outline-none focus:bg-gray-000 hover:bg-gray-100 dark:hover:bg-gray-700 dark:bg-gray-800 w-full" style="outline: none;">
                                                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle mr-1 inline-block w-5 h-5 text-ja-green-200">
                                                                    <path d="M10.5 4H1v19h19v-9.5" stroke="currentColor" stroke-width="2"></path>
                                                                    <path d="M19 1.52l3.561 3.562-11.574 11.574-5.045 1.484 1.484-5.046L19 1.52zM15.977 4.543l3.562 3.562" stroke="currentColor" stroke-width="2" stroke-linejoin="round"></path>
                                                                </svg>
                                                                Edit KPI
                                                            </button>
                                                            <button type="button" class="kpi-delete-btn flex items-center px-4 py-3 text-xs font-semibold text-rose-600 focus:outline-none hover:bg-rose-50 dark:hover:bg-rose-900/30 w-full">
                                                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle mr-1 inline-block w-5 h-5 text-rose-500">
                                                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                </svg>
                                                                Delete KPI
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Custom KPI View -->
                <div id="kpisAddCustomKpiView" class="{{ $isKpiCustomCreate ? '' : 'hidden' }} pb-10">
                    <div class="bg-white relative p-6 dark:bg-gray-700 p-5 lg:w-2/3 w-full shadow">
                        <h3 class="mb-4 flex items-center text-2xl font-bold normal-case leading-normal">
                            <a class="hover:text-ja-green-200" id="backToCustomKpisBtn" href="/configuration/kpis/custom/index">
                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9 16" class="flex-shrink-0 align-middle mr-3 inline-block w-5 h-5">
                                    <path d="M8 1L2 7.627l6 6.627" stroke="currentColor" stroke-width="2.5"></path>
                                </svg>
                            </a>
                            Add KPI
                        </h3>
                        <span>
                            <form id="addCustomKpiForm">
                                <div role="group" tabindex="-1" class="focus:outline-none mb-4">
                                    <label class="font-bold mb-1 dark:text-white mb-1 flex w-full text-xs" for="name"> KPI Name: </label>
                                    <div class="relative block">
                                        <input type="text" id="name" name="name" placeholder="# of Flouride per Day" class="appearance-none rounded-sm p-2 border placeholder-gray-400 dark:text-white dark:placeholder-white focus:outline-none focus:bg-gray-000 bg-white dark:bg-gray-800 border-gray-700 dark:border-gray-800 focus:border-ja-green-200 dark:focus:border-ja-green-200 w-full text-xs h-9" required>
                                    </div>
                                    <div class="text-xs mt-1 text-red-100" id="addKpiNameError"></div>
                                </div>
                                <div class="grid gap-x-4 mb-4 grid-cols-2">
                                    <div class="mr-2">
                                        <div role="group" tabindex="-1" class="focus:outline-none mb-0">
                                            <label class="font-bold mb-1 dark:text-white mb-1 flex w-full text-xs" for="transaction_type"> Transaction Type: </label>
                                            <div tabindex="-1" class="multiselect w-full" data-v-c1927b88="">
                                                <select id="transaction_type" name="transaction_type" class="w-full text-xs bg-white dark:bg-gray-800 border border-gray-700 dark:border-gray-800 rounded-sm px-3 py-2 text-slate-700 dark:text-white focus:outline-none focus:border-ja-green-200 h-9 cursor-pointer" required>
                                                    <option value="" disabled selected>Select item</option>
                                                    @foreach($customTransactionTypes as $txType)
                                                        <option value="{{ $txType }}">{{ $txType }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="text-xs mt-1 text-red-100"></div>
                                        </div>
                                    </div>
                                    <div class="ml-2">
                                        <div role="group" tabindex="-1" class="focus:outline-none mb-0">
                                            <label class="font-bold mb-1 dark:text-white mb-1 flex w-full text-xs" for="kpi_type"> KPI Type: </label>
                                            <div tabindex="-1" class="multiselect w-full" data-v-c1927b88="">
                                                <select id="kpi_type" name="kpi_type" class="w-full text-xs bg-white dark:bg-gray-800 border border-gray-700 dark:border-gray-800 rounded-sm px-3 py-2 text-slate-700 dark:text-white focus:outline-none focus:border-ja-green-200 h-9 cursor-pointer">
                                                    @foreach($customKpiTypes as $kpiType)
                                                        <option value="{{ $kpiType }}" {{ $kpiType === 'Hygiene' ? 'selected' : '' }}>{{ $kpiType }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="text-xs mt-1 text-red-100"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-x-4">
                                    <div class="mr-2">
                                        <div role="group" tabindex="-1" class="focus:outline-none mb-4">
                                            <label class="font-bold mb-1 dark:text-white mb-1 flex w-full text-xs" for="display"> Display As: </label>
                                            <div tabindex="-1" class="multiselect w-full" data-v-c1927b88="">
                                                <select id="display" name="display" class="w-full text-xs bg-white dark:bg-gray-800 border border-gray-700 dark:border-gray-800 rounded-sm px-3 py-2 text-slate-700 dark:text-white focus:outline-none focus:border-ja-green-200 h-9 cursor-pointer">
                                                    <option value="Count (#)" selected>Count (#)</option>
                                                    <option value="Dollar ($)">Dollar ($)</option>
                                                </select>
                                            </div>
                                            <div class="text-xs mt-1 text-red-100"></div>
                                        </div>
                                    </div>
                                    <div class="ml-2">
                                        <div role="group" tabindex="-1" class="focus:outline-none mb-4">
                                            <label class="font-bold mb-1 dark:text-white mb-1 flex w-full text-xs" for="lob"> Line of Business (optional): </label>
                                            <div tabindex="-1" class="multiselect multiselect--multiple multiselect--taggable w-full" data-v-c1927b88="">
                                                <select id="lob" name="line_of_business" class="w-full text-xs bg-white dark:bg-gray-800 border border-gray-700 dark:border-gray-800 rounded-sm px-3 py-2 text-slate-700 dark:text-white focus:outline-none focus:border-ja-green-200 h-9 cursor-pointer">
                                                    <option value="">Select item</option>
                                                    @foreach($customLinesOfBusiness as $lob)
                                                        <option value="{{ $lob }}">{{ $lob }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="text-xs mt-1 text-red-100"></div>
                                        </div>
                                    </div>
                                </div>
                                <div role="group" tabindex="-1" class="focus:outline-none mb-4">
                                    <label class="font-bold mb-1 dark:text-white mb-1 flex w-full text-xs" for="description"> Description: </label>
                                    <div class="relative w-full block">
                                        <textarea id="description" name="description" cols="50" rows="5" class="p-2 rounded-sm border placeholder-gray-400 dark:placeholder-white dark:text-white focus:outline-none focus:bg-gray-000 bg-white dark:bg-gray-800 border-gray-700 dark:border-gray-800 focus:border-ja-green-200 dark:focus:border-ja-green-200 w-full text-xs"></textarea>
                                    </div>
                                    <div class="text-xs mt-1 text-red-100"></div>
                                </div>
                                <div class="flex mt-2 items-center justify-start">
                                    <button type="submit" id="saveCustomKpiBtn" class="appearance-none items-center py-2 px-8 justify-center select-none font-bold relative focus:outline-none mr-2 inline-flex rounded-sm border-2 bg-ja-green-200 border-ja-green-200 text-white dark:text-white hover:bg-white dark:hover:bg-gray-800 hover:text-ja-green-200 text-xs h-9 cursor-pointer"> Save </button>
                                    <div id="customKpiSuccessMsg" class="text-xs mt-1 text-ja-green-200" style="display: none;">Custom KPI is successfully saved!</div>
                                </div>
                            </form>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Edit KPI Modal -->
            <div id="editKpiModal" tabindex="-1" role="dialog" class="fixed inset-0 overflow-auto z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
                <div role="document" class="mx-auto my-12 pointer-events-none max-w-lg w-full">
                    <div class="p-6 pointer-events-auto bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-slate-200 dark:border-gray-700 space-y-5">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-gray-700">
                            <h2 class="dark:text-white text-xl font-bold">Edit KPI</h2>
                            <button type="button" id="closeEditKpiModalBtn" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors cursor-pointer">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <form id="editKpiForm" class="space-y-4 text-xs">
                            <input type="hidden" id="editKpiIdInput">

                            <div>
                                <label class="block font-bold mb-1 text-slate-800 dark:text-white">KPI Name</label>
                                <input type="text" id="editKpiNameInput" required class="w-full bg-white dark:bg-gray-900 border border-slate-300 dark:border-gray-600 rounded px-3 py-2 text-slate-800 dark:text-white focus:outline-none focus:border-[#00bfa5]">
                            </div>

                            <div>
                                <label class="block font-bold mb-1 text-slate-800 dark:text-white">Description / Formula</label>
                                <textarea id="editKpiDescInput" rows="3" class="w-full bg-white dark:bg-gray-900 border border-slate-300 dark:border-gray-600 rounded px-3 py-2 text-slate-800 dark:text-white focus:outline-none focus:border-[#00bfa5]"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold mb-1 text-slate-800 dark:text-white">Target Goal</label>
                                    <input type="number" step="any" id="editKpiGoalInput" class="w-full bg-white dark:bg-gray-900 border border-slate-300 dark:border-gray-600 rounded px-3 py-2 text-slate-800 dark:text-white focus:outline-none focus:border-[#00bfa5]">
                                </div>

                                <div>
                                    <label class="block font-bold mb-1 text-slate-800 dark:text-white">Status</label>
                                    <div class="pt-2">
                                        <label class="relative inline-flex items-center cursor-pointer select-none font-semibold text-xs dark:text-white gap-2">
                                            <input type="checkbox" id="editKpiEnabledInput" class="sr-only dds-toggle-input">
                                            <div class="dds-toggle-track">
                                                <div class="dds-toggle-knob">
                                                    <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <span>Enabled</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-gray-700">
                                <button type="button" id="cancelEditKpiBtn" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-gray-700 font-bold text-xs transition-colors cursor-pointer">
                                    Cancel
                                </button>
                                <button type="submit" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-white bg-[#00bfa5] hover:bg-[#00a892] font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB: RCM User Mapping -->
            <div id="tab-panel-rcm" class="config-tab-panel {{ $activeTab === 'rcm' ? '' : 'hidden' }} space-y-0" company-id="42" role="subscriber" subscription="enterprise">
                <div class="bg-white relative p-6 dark:bg-gray-700 mb-10 shadow">
                    <div class="flex items-center justify-between flex-wrap gap-2 md:gap-0">
                        <!-- Location Multiselect -->
                        <div tabindex="-1" class="multiselect w-48 ml-1 relative" id="rcmLocationMultiselect" data-v-c1927b88="">
                            <button type="button" id="rcmLocationBtn" class="multiselect__tags w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-sm p-2 text-xs flex items-center justify-between text-left cursor-pointer focus:outline-none">
                                <span class="multiselect__single text-slate-800 dark:text-white font-medium" id="rcmLocationSelectedText">All Locations</span>
                                <div class="multiselect__select text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </button>
                            <div tabindex="-1" id="rcmLocationDropdown" class="multiselect__content-wrapper absolute left-0 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg z-50 max-h-60 overflow-y-auto hidden" style="max-height: 300px;">
                                <ul class="multiselect__content py-1 text-xs">
                                    <li class="multiselect__element">
                                        <button type="button" class="rcm-location-item w-full px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-white flex items-center justify-between bg-[#00bfa5]/10 font-bold text-[#00bfa5]" data-location="">
                                            <span>All Locations</span>
                                        </button>
                                    </li>
                                    @foreach($locations as $loc)
                                        <li class="multiselect__element">
                                            <button type="button" class="rcm-location-item w-full px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-white flex items-center justify-between" data-location="{{ $loc }}">
                                                <span>{{ $loc }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <!-- Search Locations Input -->
                        <div class="flex items-center">
                            <div class="relative w-48 block">
                                <input type="search" id="rcmSearchInput" autocomplete="off" placeholder="Search locations" class="appearance-none bg-white dark:bg-gray-800 dark:text-white border border-gray-700 dark:border-gray-800 rounded-sm p-2 placeholder-gray-400 dark:placeholder-white focus:outline-none focus:border-ja-green-200 dark:focus:border-ja-green-200 focus:bg-gray-000 bg-search dark:bg-search w-full text-xs h-9">
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="mt-6">
                        <div class="overflow-x-auto relative">
                            <table class="w-full relative" id="rcmUserMappingTable">
                                <thead role="rowgroup">
                                    <tr role="row" class="">
                                        <th data-v-7c11c636="" role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border text-center" style="min-width: 1rem; width: 60px;">
                                            <span data-v-7c11c636="" class="inline-block">ID</span>
                                        </th>
                                        <th data-v-7c11c636="" role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border text-left" style="min-width: 1rem;">
                                            <span data-v-7c11c636="" class="inline-block">Location</span>
                                        </th>
                                        <th data-v-7c11c636="" role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border text-center" style="min-width: 1rem; width: 6rem;">
                                            <span data-v-7c11c636="" class="inline-block">User Count</span>
                                        </th>
                                        <th data-v-7c11c636="" role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border text-left" style="min-width: 1rem;">
                                            <span data-v-7c11c636="" class="inline-block">Users</span>
                                        </th>
                                        <th data-v-7c11c636="" role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border text-center" style="min-width: 1rem; width: 6rem;">
                                            <span data-v-7c11c636="" class="inline-block">Action</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody role="rowgroup" id="rcmUserMappingTbody">
                                    @foreach($rcmUserMappings as $idx => $mapping)
                                        <tr role="row" class="rcm-row {{ $idx % 2 === 0 ? 'odd:bg-gray-000 dark:odd:bg-gray-800 bg-gray-000 dark:bg-gray-800' : 'bg-white dark:bg-gray-700' }} hover:bg-gray-100/60 dark:hover:bg-gray-750 transition-colors"
                                            data-id="{{ $mapping['id'] }}"
                                            data-location="{{ strtolower($mapping['location']) }}"
                                            data-users="{{ strtolower(implode(', ', $mapping['users'])) }}">
                                            <td role="cell" class="px-3 py-2 align-middle text-center text-xs dark:text-white border-white dark:border-gray-800 border rcm-cell-id" style="min-width: 0.1%; width: 20px;">
                                                {{ $mapping['id'] }}
                                            </td>
                                            <td role="cell" class="px-3 py-2 align-middle sticky left-0 border-b border-r-shadow-white border-white text-xs dark:text-white border-white dark:border-gray-800 border rcm-cell-location {{ $idx % 2 === 0 ? 'bg-gray-000 dark:bg-gray-800' : 'bg-white dark:bg-gray-700' }}" style="min-width: 10rem;">
                                                {{ $mapping['location'] }}
                                            </td>
                                            <td role="cell" class="px-3 py-2 align-middle text-center text-xs dark:text-white border-white dark:border-gray-800 border rcm-cell-count" style="min-width: 1rem; width: 5rem;">
                                                {{ $mapping['user_count'] }}
                                            </td>
                                            <td role="cell" class="px-3 py-2 align-middle overflow-hidden truncate text-xs dark:text-white border-white dark:border-gray-800 border rcm-cell-users" style="min-width: 1rem;">
                                                {{ !empty($mapping['users']) ? implode(', ', $mapping['users']) : '--' }}
                                            </td>
                                            <td role="cell" class="px-3 py-2 align-middle text-center text-xs dark:text-white border-white dark:border-gray-800 border" style="min-width: 5rem; width: 5rem;">
                                                <div data-v-0d6a3bfa="" class="relative lels text-center rcm-action-container">
                                                    <span data-v-0d6a3bfa="" class="flex justify-center">
                                                        <button data-v-0d6a3bfa="" type="button" class="text-ja-green-200 focus:outline-none rcm-action-menu-btn" title="Actions">
                                                            <svg data-v-0d6a3bfa="" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle inline-block w-6 h-6">
                                                                <path fill="currentColor" d="M2 10h4v4H2zM10 10h4v4h-4zM18 10h4v4h-4z"></path>
                                                            </svg>
                                                        </button>
                                                    </span>
                                                    <div data-v-0d6a3bfa="" class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-800 dark:text-white z-50 bg-pointer mt-3 hidden absolute right-0 rcm-dropdown-menu rounded-sm text-left">
                                                        <button type="button" class="rcm-edit-btn flex items-center px-4 py-3 text-xs font-semibold focus:outline-none focus:bg-gray-000 hover:bg-gray-000 dark:bg-gray-800 dark:hover:bg-gray-700 w-full" style="outline: none;">
                                                            <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle mr-1 inline-block w-5 h-5 text-ja-green-200">
                                                                <path d="M10.5 4H1v19h19v-9.5" stroke="currentColor" stroke-width="2"></path>
                                                                <path d="M19 1.52l3.561 3.562-11.574 11.574-5.045 1.484 1.484-5.046L19 1.52zM15.977 4.543l3.562 3.562" stroke="currentColor" stroke-width="2" stroke-linejoin="round"></path>
                                                            </svg>
                                                            <span>Edit Details</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit RCM User Mapping Modal -->
            <div id="editRcmModal" tabindex="-1" role="dialog" class="fixed inset-0 overflow-auto z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
                <div role="document" class="mx-auto my-12 pointer-events-none max-w-lg w-full">
                    <div class="p-6 pointer-events-auto bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-slate-200 dark:border-gray-700 space-y-5">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-gray-700">
                            <h2 class="dark:text-white text-xl font-bold flex items-center">
                                <span>Edit RCM User Mapping - </span>
                                <span id="editRcmLocationTitle" class="ml-1 text-[#00bfa5]">8 Mile</span>
                            </h2>
                            <button type="button" id="closeEditRcmModalBtn" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors cursor-pointer">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <form id="editRcmForm" class="space-y-4 text-xs">
                            <input type="hidden" id="editRcmLocationIdInput">

                            <div>
                                <label class="block font-bold mb-2 text-slate-800 dark:text-white">Assigned Users</label>
                                <div class="space-y-2 max-h-60 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded p-3 bg-gray-50 dark:bg-gray-900" id="rcmUserCheckboxesContainer">
                                    @foreach($allRcmUsers as $userItem)
                                        <label class="flex items-center gap-2 text-slate-700 dark:text-slate-200 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 p-1.5 rounded transition-colors">
                                            <input type="checkbox" value="{{ $userItem }}" class="rcm-user-cb rounded border-gray-300 text-[#00bfa5] focus:ring-[#00bfa5] w-4 h-4 cursor-pointer">
                                            <span class="text-xs">{{ $userItem }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-gray-700">
                                <button type="button" id="cancelEditRcmBtn" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-gray-700 font-bold text-xs transition-colors cursor-pointer">
                                    Cancel
                                </button>
                                <button type="submit" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-white bg-[#00bfa5] hover:bg-[#00a892] font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB: Snapshot Settings -->
            <div id="tab-panel-snapshot" class="config-tab-panel {{ $activeTab === 'snapshot' ? '' : 'hidden' }} space-y-0" company-id="42" role="subscriber" subscription="enterprise">
                <div class="flex items-end mt-4">
                    <ul role="tablist" class="flex flex-grow overflow-y-hidden" id="snapshotViewTabs">
                        @foreach($snapshotViews as $viewSlug => $viewLabel)
                            <li role="presentation">
                                <a href="/configuration/snapshot/{{ $viewSlug }}" role="tab" data-snapshot-view="{{ $viewSlug }}" class="snapshot-view-tab text-xs py-3 rounded-t leading-snug block focus:outline-none font-semibold px-4 mr-1 transform translate-y-1 transition-transform duration-150 hover:text-primary-300 capitalize hover:bg-white whitespace-no-wrap bg-white text-black dark:bg-gray-700 dark:text-white" aria-selected="{{ $activeSubtab === $viewSlug || ($activeSubtab === 'default' && $viewSlug === 'default') ? 'true' : 'false' }}">
                                    {{ $viewLabel }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="ml-6 mb-3">
                        <div class="flex flex-wrap items-center gap-3">
                            <button type="button" id="openAddSnapshotViewBtn" class="appearance-none items-center py-2 px-8 justify-center select-none font-bold relative focus:outline-none flex-shrink-0 w-32 inline-flex rounded-sm border-2 bg-ja-green-200 border-ja-green-200 text-white dark:text-white hover:bg-white dark:hover:bg-gray-800 hover:text-ja-green-200 text-xs h-9 cursor-pointer transition-colors">
                                Add View
                            </button>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="bg-white relative p-6 dark:bg-gray-700 mb-10 shadow">
                        <div class="notifications-section"></div>
                        <div class="flex items-center">
                            <!-- Category Multiselect -->
                            <div tabindex="0" class="multiselect w-40 relative" id="snapshotCategoryMultiselect" data-v-c1927b88="">
                                <button type="button" id="snapshotCategoryBtn" class="multiselect__tags w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-sm p-2 text-xs flex items-center justify-between text-left cursor-pointer focus:outline-none">
                                    <span class="multiselect__single text-slate-800 dark:text-white font-medium" id="snapshotCategorySelectedText">Basic</span>
                                    <div class="multiselect__select text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </button>
                                <div tabindex="-1" id="snapshotCategoryDropdown" class="multiselect__content-wrapper absolute left-0 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg z-50 max-h-60 overflow-y-auto hidden" style="max-height: 300px;">
                                    <ul class="multiselect__content py-1 text-xs" style="display: inline-block; width: 100%;">
                                        @foreach($snapshotCategories as $catItem)
                                            <li class="multiselect__element">
                                                <button type="button" class="snapshot-category-item w-full px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-white flex items-center justify-between {{ $catItem === 'Basic' ? 'bg-[#00bfa5]/10 font-bold text-[#00bfa5]' : '' }}" data-category="{{ $catItem }}">
                                                    <span>{{ $catItem }}</span>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <div class="flex items-center ml-auto gap-2"></div>
                        </div>

                        <div class="w-full mt-4">
                            <div class="main-table">
                                <div class="overflow-x-auto relative">
                                    <table class="w-full relative" id="snapshotMetricsTable">
                                        <thead role="rowgroup">
                                            <tr role="row" class="">
                                                <th data-v-7c11c636="" role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs text-left bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border" style="min-width: 10rem;">
                                                    <span data-v-7c11c636="" class="inline-block"> Title </span>
                                                </th>
                                                <th data-v-7c11c636="" role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs text-left bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border" style="min-width: 10rem;">
                                                    <span data-v-7c11c636="" class="inline-block">Definition</span>
                                                </th>
                                                <th data-v-7c11c636="" role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs text-center bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border" style="min-width: 10rem;">
                                                    <span data-v-7c11c636="" class="inline-block">Enabled</span>
                                                </th>
                                                <th data-v-7c11c636="" role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs text-center bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border" width="100" style="min-width: 10rem;">
                                                    <span data-v-7c11c636="" class="inline-block"> Order </span>
                                                </th>
                                                <th data-v-7c11c636="" role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs text-center bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border" width="100" style="min-width: 0.1%;">
                                                    <span data-v-7c11c636="" class="inline-block"> Actions</span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody role="rowgroup" id="snapshotMetricsTbody">
                                            @foreach($snapshotMetrics as $metric)
                                                <tr role="row" class="snapshot-row transition-colors duration-300 odd:bg-gray-000 dark:odd:bg-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-750"
                                                    data-metric-id="{{ $metric['id'] }}"
                                                    data-category="{{ $metric['category'] }}"
                                                    data-title="{{ strtolower($metric['title']) }}"
                                                    style="{{ $metric['category'] === 'Basic' ? '' : 'display: none;' }}">
                                                    <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border snapshot-cell-title" style="min-width: 10rem;">
                                                        {{ $metric['title'] }}
                                                    </td>
                                                    <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border snapshot-cell-definition" style="min-width: 10rem;">
                                                        {{ $metric['definition'] }}
                                                    </td>
                                                    <td role="cell" class="px-3 py-2 align-middle text-xs text-center dark:text-white border-white dark:border-gray-800 border" style="min-width: 0.1%;">
                                                        <label class="relative inline-flex items-center cursor-pointer select-none">
                                                            <input type="checkbox" class="sr-only dds-toggle-input snapshot-enable-toggle" {{ $metric['enabled'] ? 'checked' : '' }} value="{{ $metric['enabled'] ? 'true' : 'false' }}">
                                                            <div class="dds-toggle-track">
                                                                <div class="dds-toggle-knob">
                                                                    <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </td>
                                                    <td role="cell" class="px-3 py-2 align-middle flex justify-center text-xs text-center dark:text-white border-white dark:border-gray-800 border" style="min-width: 10rem;">
                                                        <button type="button" class="appearance-none items-center py-2 px-8 justify-center select-none font-bold relative focus:outline-none pl-4 pr-4 inline-flex rounded-sm border-2 bg-ja-green-200 border-ja-green-200 text-white dark:text-white hover:bg-white dark:hover:bg-gray-800 hover:text-ja-green-200 text-xs h-9 cursor-pointer transition-colors snapshot-order-up-btn" title="Move Up">↑</button>
                                                        <button type="button" class="appearance-none items-center py-2 px-8 justify-center select-none font-bold relative focus:outline-none ml-2 pl-4 pr-4 inline-flex rounded-sm border-2 bg-ja-green-200 border-ja-green-200 text-white dark:text-white hover:bg-white dark:hover:bg-gray-800 hover:text-ja-green-200 text-xs h-9 cursor-pointer transition-colors snapshot-order-down-btn" title="Move Down">↓</button>
                                                    </td>
                                                    <td role="cell" class="px-3 py-2 align-middle text-xs text-center dark:text-white border-white dark:border-gray-800 border" style="min-width: 0.1%;">
                                                        <div data-v-2e31da73="" class="relative inline-block snapshot-action-container" right="">
                                                            <span data-v-2e31da73="" class="block">
                                                                <button data-v-2e31da73="" type="button" class="text-ja-green-200 focus:outline-none snapshot-action-menu-btn" title="Actions">
                                                                    <svg data-v-2e31da73="" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle inline-block w-6 h-6">
                                                                        <path fill="currentColor" d="M2 10h4v4H2zM10 10h4v4h-4zM18 10h4v4h-4z"></path>
                                                                    </svg>
                                                                </button>
                                                            </span>
                                                            <div data-v-2e31da73="" class="z-10 mt-3 bg-white border border-gray-200 shadow bg-pointer dark:bg-gray-800 dark:border-gray-800 hidden absolute right-0 snapshot-dropdown-menu rounded-sm text-left">
                                                                <a role="menuitem" class="text-xs font-semibold flex items-center px-4 py-3 focus:outline-none focus:bg-gray-000 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 dark:focus:bg-gray-700 cursor-pointer snapshot-edit-btn">
                                                                    <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle mr-3 inline-block w-6 h-6 text-ja-green-200">
                                                                        <path d="M10.5 4H1v19h19v-9.5" stroke="currentColor" stroke-width="2"></path>
                                                                        <path d="M19 1.52l3.561 3.562-11.574 11.574-5.045 1.484 1.484-5.046L19 1.52zM15.977 4.543l3.562 3.562" stroke="currentColor" stroke-width="2" stroke-linejoin="round"></path>
                                                                    </svg>
                                                                    <span>Edit</span>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Snapshot Modal -->
            <div id="editSnapshotModal" tabindex="-1" role="dialog" class="fixed inset-0 overflow-auto z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
                <div role="document" class="mx-auto my-20 pointer-events-none max-w-lg w-full">
                    <div class="p-6 mx-6 pointer-events-auto bg-white dark:bg-gray-700 rounded-lg shadow-xl border border-gray-200 dark:border-gray-600 space-y-4">
                        <div class="relative pr-6 flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-600">
                            <h2 class="dark:text-white text-2xl font-bold normal-case leading-normal">Edit snapshot</h2>
                            <button type="button" id="closeEditSnapshotModalBtn" class="focus:outline-none hover:text-ja-green-200 dark:text-white cursor-pointer">
                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="flex-shrink-0 align-middle inline-block w-6 h-6">
                                    <path d="M3.131 2.929L16.87 16.667M3.131 16.667L16.87 2.929" stroke="currentColor" stroke-width="3"></path>
                                </svg>
                            </button>
                        </div>
                        <form id="editSnapshotForm" class="space-y-4 text-xs">
                            <input type="hidden" id="editSnapshotIdInput">
                            <div>
                                <label for="editSnapshotTitleInput" class="block font-bold mb-1 text-slate-800 dark:text-white">Title</label>
                                <input type="text" id="editSnapshotTitleInput" required class="appearance-none rounded-sm p-2 border border-gray-400 dark:border-gray-600 dark:bg-gray-800 text-xs dark:text-white focus:outline-none focus:border-ja-green-200 w-full">
                            </div>
                            <div>
                                <label for="editSnapshotDefinitionInput" class="block font-bold mb-1 text-slate-800 dark:text-white">Definition</label>
                                <textarea id="editSnapshotDefinitionInput" rows="3" class="appearance-none rounded-sm p-2 border border-gray-400 dark:border-gray-600 dark:bg-gray-800 text-xs dark:text-white focus:outline-none focus:border-ja-green-200 w-full"></textarea>
                            </div>
                            <div>
                                <label class="relative inline-flex items-center cursor-pointer select-none gap-3 font-bold text-slate-800 dark:text-white">
                                    <input type="checkbox" id="editSnapshotEnabledInput" class="sr-only dds-toggle-input">
                                    <div class="dds-toggle-track">
                                        <div class="dds-toggle-knob">
                                            <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </div>
                                    <span>Enabled</span>
                                </label>
                            </div>
                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-600">
                                <button type="button" id="cancelEditSnapshotBtn" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-gray-700 font-bold text-xs transition-colors cursor-pointer">
                                    Cancel
                                </button>
                                <button type="submit" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-white bg-[#00bfa5] hover:bg-[#00a892] font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Add Snapshot View Modal -->
            <div id="addSnapshotViewModal" tabindex="-1" role="dialog" class="fixed inset-0 overflow-auto z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
                <div role="document" class="mx-auto my-20 pointer-events-none max-w-lg w-full">
                    <div class="p-6 mx-6 pointer-events-auto bg-white dark:bg-gray-700 rounded-lg shadow-xl border border-gray-200 dark:border-gray-600 space-y-4">
                        <div class="relative pr-6 flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-600">
                            <h2 class="dark:text-white text-2xl font-bold normal-case leading-normal">Add Snapshot View</h2>
                            <button type="button" id="closeAddSnapshotViewModalBtn" class="focus:outline-none hover:text-ja-green-200 dark:text-white cursor-pointer">
                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="flex-shrink-0 align-middle inline-block w-6 h-6">
                                    <path d="M3.131 2.929L16.87 16.667M3.131 16.667L16.87 2.929" stroke="currentColor" stroke-width="3"></path>
                                </svg>
                            </button>
                        </div>
                        <form id="addSnapshotViewForm" class="space-y-4 text-xs">
                            <div>
                                <label for="newSnapshotViewNameInput" class="block font-bold mb-1 text-slate-800 dark:text-white">View Name</label>
                                <input type="text" id="newSnapshotViewNameInput" placeholder="e.g. Hygiene View" required class="appearance-none rounded-sm p-2 border border-gray-400 dark:border-gray-600 dark:bg-gray-800 text-xs dark:text-white focus:outline-none focus:border-ja-green-200 w-full">
                            </div>
                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-600">
                                <button type="button" id="cancelAddSnapshotViewBtn" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-gray-700 font-bold text-xs transition-colors cursor-pointer">
                                    Cancel
                                </button>
                                <button type="submit" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-white bg-[#00bfa5] hover:bg-[#00a892] font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                    Add View
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB: Huddle Settings -->
            <div id="tab-panel-huddle" class="config-tab-panel {{ $activeTab === 'huddle' ? '' : 'hidden' }} space-y-0" company-id="42" role="subscriber" subscription="enterprise">
                <ul role="tablist" class="flex flex-grow overflow-y-hidden mt-4" id="huddleSubtabsList">
                    @foreach($huddleSubtabs as $subSlug => $subLabel)
                        @php
                            $isHuddleSubActive = ($activeSubtab === $subSlug) || ($activeSubtab === '' && $subSlug === 'yesterday');
                        @endphp
                        <li role="presentation">
                            <a role="tab"
                               href="/configuration/huddle/{{ $subSlug }}"
                               data-huddle-subtab="{{ $subSlug }}"
                               aria-selected="{{ $isHuddleSubActive ? 'true' : 'false' }}"
                               class="huddle-subtab-link text-xs py-3 rounded-t leading-snug block focus:outline-none font-semibold px-4 mr-1 transform transition-transform duration-150 hover:text-primary-300 capitalize hover:bg-white whitespace-no-wrap {{ $isHuddleSubActive ? 'translate-y-1 bg-white text-black dark:bg-gray-700 dark:text-white' : 'bg-gray-000 text-gray-400 dark:bg-gray-600 hover:text-slate-800 dark:hover:text-white' }}">
                                {{ $subLabel }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="table-full-width-container relative bg-white dark:bg-gray-700 shadow flex flex-col justify-between overflow-x-auto min-h-20rem">
                    <table cellpadding="0" cellspacing="0" class="w-full text-left table-collapse" id="huddleMetricsTable">
                        <thead>
                            <tr>
                                <th class="text-xs font-semibold text-gray-400 dark:text-gray-200 uppercase bg-gray-000 dark:bg-gray-600 p-4 border-b border-gray-100 dark:border-gray-500 whitespace-no-wrap">
                                    Metric
                                </th>
                                <th class="text-xs font-semibold text-gray-400 dark:text-gray-200 uppercase bg-gray-000 dark:bg-gray-600 p-4 border-b border-gray-100 dark:border-gray-500 whitespace-no-wrap">
                                    Description
                                </th>
                                <th class="text-xs font-semibold text-gray-400 dark:text-gray-200 uppercase bg-gray-000 dark:bg-gray-600 p-4 border-b border-gray-100 dark:border-gray-500 whitespace-no-wrap w-24">
                                    Enabled
                                </th>
                                <th class="text-xs font-semibold text-gray-400 dark:text-gray-200 uppercase bg-gray-000 dark:bg-gray-600 p-4 border-b border-gray-100 dark:border-gray-500 whitespace-no-wrap w-24">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="align-baseline" id="huddleMetricsTbody">
                            @foreach($huddleMetrics as $metric)
                                @php
                                    $currentSub = $activeSubtab ?: 'yesterday';
                                    $isSubMatch = ($metric['subtab'] === $currentSub);
                                    $isHeader = !empty($metric['is_header']);
                                    $isBold = !empty($metric['bold']);
                                    $isIndent = !empty($metric['indent']);
                                    $hasExcludeCheckbox = !empty($metric['has_exclude_rejected_tx']);
                                    $isEnabled = $metric['enabled'] ?? false;
                                @endphp
                                <tr class="huddle-metric-row text-xs border-t border-gray-100 dark:border-gray-600 hover:bg-gray-000 dark:hover:bg-gray-600 {{ $isHeader ? 'bg-gray-50/50 dark:bg-gray-800/30 font-bold' : '' }} {{ $isSubMatch ? '' : 'hidden' }}"
                                    data-metric-id="{{ $metric['id'] }}"
                                    data-huddle-subtab="{{ $metric['subtab'] }}">
                                    <td class="p-4 border-gray-100 dark:border-gray-600 {{ $isIndent ? 'pl-8' : '' }}">
                                        <div class="h-full flex items-center flex-wrap gap-2">
                                            <span class="huddle-cell-title {{ $isBold ? 'font-bold' : '' }} text-black dark:text-white">{{ $metric['title'] }}</span>
                                            @if($hasExcludeCheckbox)
                                                <div class="ml-4 inline-flex items-center">
                                                    <input type="checkbox" id="exclude-rejected-tx-{{ $metric['id'] }}" name="exclude_rejected_tx_{{ $metric['id'] }}" class="mr-2 form-checkbox h-4 w-4 text-[#00bfa5] border-gray-300 rounded focus:ring-[#00bfa5] cursor-pointer">
                                                    <label for="exclude-rejected-tx-{{ $metric['id'] }}" class="text-gray-500 dark:text-gray-400 text-xs cursor-pointer select-none">Exclude Rejected TX plans</label>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-4 border-gray-100 dark:border-gray-600">
                                        <div class="h-full flex items-center">
                                            <span class="huddle-cell-desc text-black dark:text-white">{{ $metric['description'] }}</span>
                                        </div>
                                    </td>
                                    @if($isHeader)
                                        <td aria-hidden="true" class="p-4 border-gray-100 dark:border-gray-600"></td>
                                        <td aria-hidden="true" class="p-4 border-gray-100 dark:border-gray-600"></td>
                                    @else
                                        <td class="p-4 border-gray-100 dark:border-gray-600">
                                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                                <input type="checkbox" class="sr-only dds-toggle-input huddle-enable-toggle" {{ $isEnabled ? 'checked' : '' }} aria-checked="{{ $isEnabled ? '1' : '0' }}" value="{{ $isEnabled ? 'true' : 'false' }}">
                                                <div class="dds-toggle-track">
                                                    <div class="dds-toggle-knob">
                                                        <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </label>
                                        </td>
                                        <td class="p-4 border-gray-100 dark:border-gray-600">
                                            <div class="relative inline-block text-left">
                                                <button type="button" class="huddle-action-menu-btn text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 focus:outline-none p-1 rounded cursor-pointer" aria-label="Actions">
                                                    <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5">
                                                        <path d="M12 13a1 1 0 100-2 1 1 0 000 2zM19 13a1 1 0 100-2 1 1 0 000 2zM5 13a1 1 0 100-2 1 1 0 000 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    </svg>
                                                </button>
                                                <div class="huddle-dropdown-menu hidden absolute right-0 mt-1 w-32 bg-white dark:bg-gray-800 rounded shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-30">
                                                    <button type="button" class="huddle-edit-btn w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium cursor-pointer">
                                                        Edit Huddle
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Edit Huddle Modal -->
            <div id="editHuddleModal" tabindex="-1" role="dialog" class="fixed inset-0 overflow-auto z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
                <div role="document" class="mx-auto my-20 pointer-events-none max-w-lg w-full">
                    <div class="p-6 mx-6 pointer-events-auto bg-white dark:bg-gray-700 rounded-lg shadow-xl border border-gray-200 dark:border-gray-600 space-y-4">
                        <div class="relative pr-6 flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-600">
                            <h2 class="dark:text-white text-2xl font-bold normal-case leading-normal">Edit Huddle</h2>
                            <button type="button" id="closeEditHuddleModalBtn" class="focus:outline-none hover:text-ja-green-200 dark:text-white cursor-pointer" aria-label="Close modal">
                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="flex-shrink-0 align-middle inline-block w-6 h-6">
                                    <path d="M3.131 2.929L16.87 16.667M3.131 16.667L16.87 2.929" stroke="currentColor" stroke-width="3"></path>
                                </svg>
                            </button>
                        </div>
                        <form id="editHuddleForm" class="space-y-4 text-xs">
                            <input type="hidden" id="editHuddleMetricId" value="">
                            <div>
                                <label for="editHuddleTitleInput" class="block font-bold mb-1 text-slate-800 dark:text-white">Title</label>
                                <input type="text" id="editHuddleTitleInput" required class="appearance-none rounded-sm p-2 border border-gray-400 dark:border-gray-600 dark:bg-gray-800 text-xs dark:text-white focus:outline-none focus:border-ja-green-200 w-full">
                            </div>
                            <div>
                                <label for="editHuddleDescriptionInput" class="block font-bold mb-1 text-slate-800 dark:text-white">Description</label>
                                <textarea id="editHuddleDescriptionInput" rows="3" class="appearance-none rounded-sm p-2 border border-gray-400 dark:border-gray-600 dark:bg-gray-800 text-xs dark:text-white focus:outline-none focus:border-ja-green-200 w-full"></textarea>
                            </div>
                            <div class="flex items-center justify-between pt-2">
                                <span class="font-bold text-slate-800 dark:text-white">Enabled</span>
                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" id="editHuddleEnabledInput" class="sr-only dds-toggle-input">
                                    <div class="dds-toggle-track">
                                        <div class="dds-toggle-knob">
                                            <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-600">
                                <button type="button" id="cancelEditHuddleBtn" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-gray-700 font-bold text-xs transition-colors cursor-pointer">
                                    Cancel
                                </button>
                                <button type="submit" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-white bg-[#00bfa5] hover:bg-[#00a892] font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB: EOD Settings -->
            <div id="tab-panel-eod" class="config-tab-panel {{ $activeTab === 'eod' ? '' : 'hidden' }} space-y-0" company-id="42" role="subscriber" subscription="enterprise">
                <ul role="tablist" class="flex flex-grow overflow-y-hidden mt-4" id="eodSubtabsList">
                    @foreach($eodSubtabs as $subSlug => $subLabel)
                        @php
                            $isEodSubActive = ($activeSubtab === $subSlug) || ($activeSubtab === '' && $subSlug === 'basics');
                        @endphp
                        <li role="presentation">
                            <a role="tab"
                               href="/configuration/eod/{{ $subSlug }}"
                               data-eod-subtab="{{ $subSlug }}"
                               aria-selected="{{ $isEodSubActive ? 'true' : 'false' }}"
                               class="eod-subtab-link text-xs py-3 rounded-t leading-snug block focus:outline-none font-semibold px-4 mr-1 transform transition-transform duration-150 hover:text-primary-300 capitalize hover:bg-white whitespace-no-wrap {{ $isEodSubActive ? 'translate-y-1 bg-white text-black dark:bg-gray-700 dark:text-white' : 'bg-gray-000 text-gray-400 dark:bg-gray-600 hover:text-slate-800 dark:hover:text-white' }}">
                                {{ $subLabel }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="bg-white relative p-6 dark:bg-gray-700 mb-10 shadow">
                    <div class="main-table">
                        <div class="overflow-x-auto relative">
                            <table class="w-full relative" id="eodMetricsTable">
                                <thead role="rowgroup">
                                    <tr role="row">
                                        <th role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs text-left bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border" style="min-width: 10rem;">
                                            <span class="inline-block">Metric</span>
                                        </th>
                                        <th role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs text-center bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border" style="min-width: 0.1%;">
                                            <span class="inline-block">Description</span>
                                        </th>
                                        <th role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs text-center bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border" style="min-width: 0.1%;">
                                            <span class="inline-block">Enabled</span>
                                        </th>
                                        <th role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs text-center bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border" style="min-width: 0.1%;">
                                            <span class="inline-block">Locked</span>
                                        </th>
                                        <th role="columnheader" class="px-3 py-4 align-middle font-semibold text-xs text-center bg-gray-200 dark:bg-gray-700 dark:text-white border-white dark:border-gray-800 border" style="min-width: 0.1%;">
                                            <span class="inline-block">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody role="rowgroup" id="eodMetricsTbody">
                                    @foreach($eodMetrics as $metric)
                                        @php
                                            $currentSub = $activeSubtab ?: 'basics';
                                            $isSubMatch = ($metric['subtab'] === $currentSub);
                                            $isEnabled = $metric['enabled'] ?? false;
                                            $isLocked = $metric['locked'] ?? false;
                                        @endphp
                                        <tr role="row" class="eod-metric-row odd:bg-gray-000 dark:odd:bg-gray-800 hover:bg-slate-100/60 dark:hover:bg-gray-600 {{ $isSubMatch ? '' : 'hidden' }}"
                                            data-metric-id="{{ $metric['id'] }}"
                                            data-eod-subtab="{{ $metric['subtab'] }}">
                                            <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border font-medium" style="min-width: 10rem;">
                                                <span class="eod-cell-title">{{ $metric['title'] }}</span>
                                            </td>
                                            <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border" style="min-width: 10rem;">
                                                <span class="eod-cell-desc">{{ $metric['description'] }}</span>
                                            </td>
                                            <td role="cell" class="px-3 py-2 align-middle text-center text-xs dark:text-white border-white dark:border-gray-800 border" style="min-width: 10rem;">
                                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                                    <input type="checkbox" class="sr-only dds-toggle-input eod-enable-toggle" {{ $isEnabled ? 'checked' : '' }} aria-checked="{{ $isEnabled ? '1' : '0' }}" value="{{ $isEnabled ? 'true' : 'false' }}">
                                                    <div class="dds-toggle-track">
                                                        <div class="dds-toggle-knob">
                                                            <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </label>
                                            </td>
                                            <td role="cell" class="px-3 py-2 align-middle text-center text-xs dark:text-white border-white dark:border-gray-800 border" style="min-width: 10rem;">
                                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                                    <input type="checkbox" class="sr-only dds-toggle-input eod-locked-toggle" {{ $isLocked ? 'checked' : '' }} aria-checked="{{ $isLocked ? '1' : '0' }}" value="{{ $isLocked ? 'true' : 'false' }}">
                                                    <div class="dds-toggle-track">
                                                        <div class="dds-toggle-knob">
                                                            <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </label>
                                            </td>
                                            <td role="cell" class="px-3 py-2 align-middle text-center text-xs dark:text-white border-white dark:border-gray-800 border" style="min-width: 0.1%; width: 6rem;">
                                                <div class="relative inline-block text-center">
                                                    <button type="button" class="eod-action-menu-btn text-ja-green-200 focus:outline-none p-1 rounded cursor-pointer" aria-label="Actions">
                                                        <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle inline-block w-6 h-6">
                                                            <path fill="currentColor" d="M2 10h4v4H2zM10 10h4v4h-4zM18 10h4v4h-4z"></path>
                                                        </svg>
                                                    </button>
                                                    <div class="eod-dropdown-menu hidden absolute right-0 mt-2 w-32 bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-800 rounded z-50 py-1 text-left">
                                                        <button type="button" class="eod-edit-btn flex items-center px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 w-full cursor-pointer">
                                                            <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle mr-1.5 inline-block w-4 h-4">
                                                                <path d="M10.5 4H1v19h19v-9.5" stroke="currentColor" stroke-width="2"></path>
                                                                <path d="M19 1.52l3.561 3.562-11.574 11.574-5.045 1.484 1.484-5.046L19 1.52zM15.977 4.543l3.562 3.562" stroke="currentColor" stroke-width="2" stroke-linejoin="round"></path>
                                                            </svg>
                                                            <span>Edit EOD</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit EOD Modal -->
            <div id="editEodModal" tabindex="-1" role="dialog" class="fixed inset-0 overflow-auto z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
                <div role="document" class="mx-auto my-20 pointer-events-none max-w-lg w-full">
                    <div class="p-6 mx-6 pointer-events-auto bg-white dark:bg-gray-700 rounded-lg shadow-xl border border-gray-200 dark:border-gray-600 space-y-4">
                        <div class="relative pr-6 flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-600">
                            <h2 class="dark:text-white text-2xl font-bold normal-case leading-normal">Edit EOD</h2>
                            <button type="button" id="closeEditEodModalBtn" class="focus:outline-none hover:text-ja-green-200 dark:text-white cursor-pointer" aria-label="Close modal">
                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="flex-shrink-0 align-middle inline-block w-6 h-6">
                                    <path d="M3.131 2.929L16.87 16.667M3.131 16.667L16.87 2.929" stroke="currentColor" stroke-width="3"></path>
                                </svg>
                            </button>
                        </div>
                        <form id="editEodForm" class="space-y-4 text-xs">
                            <input type="hidden" id="editEodMetricId" value="">
                            <div>
                                <label for="editEodTitleInput" class="block font-bold mb-1 text-slate-800 dark:text-white">Title</label>
                                <input type="text" id="editEodTitleInput" required class="appearance-none rounded-sm p-2 border border-gray-400 dark:border-gray-600 dark:bg-gray-800 text-xs dark:text-white focus:outline-none focus:border-ja-green-200 w-full">
                            </div>
                            <div>
                                <label for="editEodDescriptionInput" class="block font-bold mb-1 text-slate-800 dark:text-white">Description</label>
                                <textarea id="editEodDescriptionInput" rows="3" class="appearance-none rounded-sm p-2 border border-gray-400 dark:border-gray-600 dark:bg-gray-800 text-xs dark:text-white focus:outline-none focus:border-ja-green-200 w-full"></textarea>
                            </div>
                            <div class="flex items-center justify-between pt-2">
                                <span class="font-bold text-slate-800 dark:text-white">Enabled</span>
                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" id="editEodEnabledInput" class="sr-only dds-toggle-input">
                                    <div class="dds-toggle-track">
                                        <div class="dds-toggle-knob">
                                            <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between pt-2">
                                <span class="font-bold text-slate-800 dark:text-white">Locked</span>
                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" id="editEodLockedInput" class="sr-only dds-toggle-input">
                                    <div class="dds-toggle-track">
                                        <div class="dds-toggle-knob">
                                            <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-600">
                                <button type="button" id="cancelEditEodBtn" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-[#00bfa5] bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-gray-700 font-bold text-xs transition-colors cursor-pointer">
                                    Cancel
                                </button>
                                <button type="submit" class="px-6 py-2 rounded-sm border-2 border-[#00bfa5] text-white bg-[#00bfa5] hover:bg-[#00a892] font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- OTHER TABS (Clean container ready for future information) -->
            @foreach($tabs as $slug => $label)
                @if($slug !== 'basic' && $slug !== 'email' && $slug !== 'providers' && $slug !== 'goals' && $slug !== 'reminders' && $slug !== 'calendar' && $slug !== 'code-mapping' && $slug !== 'kpis' && $slug !== 'rcm' && $slug !== 'snapshot' && $slug !== 'huddle' && $slug !== 'eod')
                    <div id="tab-panel-{{ $slug }}" class="config-tab-panel {{ $activeTab === $slug ? '' : 'hidden' }}">
                        <div class="bg-white rounded-lg border border-slate-200/90 shadow-sm p-8">
                            <div class="border-b border-slate-100 pb-4 mb-6">
                                <h2 class="text-xl font-bold text-slate-900 tracking-tight">{{ $label }}</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Manage and configure settings for {{ strtolower($label) }}</p>
                            </div>

                            <div class="p-12 text-center border border-dashed border-slate-200 rounded-lg bg-slate-50/40">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="settings-2" class="w-6 h-6"></i>
                                </div>
                                <h3 class="text-sm font-semibold text-slate-800">{{ $label }} Configuration</h3>
                                <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                                    This section is ready for {{ strtolower($label) }} configuration parameters and options.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabLinks = document.querySelectorAll('.config-tab-link');
            const tabPanels = document.querySelectorAll('.config-tab-panel');

            function switchGoalsSubtab(subtabSlug, pushState = true) {
                const normalized = (subtabSlug === 'specialties' || subtabSlug === 'specialty') ? 'specialties' : ((subtabSlug === 'providers' || subtabSlug === 'provider') ? 'providers' : 'office');
                const panelKey = (normalized === 'specialties') ? 'specialty' : (normalized === 'providers' ? 'provider' : 'office');

                const goalsBtns = document.querySelectorAll('.goals-subtab-btn');
                const goalsPanels = document.querySelectorAll('.goals-subtab-panel');

                goalsBtns.forEach(b => {
                    const btnSubtab = b.getAttribute('data-goals-subtab');
                    const isMatch = (btnSubtab === normalized || btnSubtab === panelKey);
                    if (isMatch) {
                        b.classList.remove('bg-slate-200/70', 'text-slate-600', 'font-semibold');
                        b.classList.add('bg-white', 'text-slate-900', 'border-t-2', 'border-[#00bfa5]', 'shadow-xs', 'font-bold');
                        b.setAttribute('aria-selected', 'true');
                    } else {
                        b.classList.remove('bg-white', 'text-slate-900', 'border-t-2', 'border-[#00bfa5]', 'shadow-xs', 'font-bold');
                        b.classList.add('bg-slate-200/70', 'text-slate-600', 'font-semibold');
                        b.setAttribute('aria-selected', 'false');
                    }
                });

                goalsPanels.forEach(panel => {
                    if (panel.id === 'goals-subtab-panel-' + panelKey || panel.id === 'goals-subtab-panel-' + normalized) {
                        panel.classList.remove('hidden');
                    } else {
                        panel.classList.add('hidden');
                    }
                });

                if (pushState && window.history.pushState) {
                    const newUrl = window.location.origin + '/configuration/goals/' + normalized;
                    if (window.location.pathname !== '/configuration/goals/' + normalized) {
                        window.history.pushState({ tab: 'goals', subtab: normalized }, '', newUrl);
                    }
                }
            }

            function switchCalendarSubtab(subtabSlug, pushState = true) {
                const normalized = (subtabSlug === 'work-hours' || subtabSlug === 'worked-hours') ? 'worked-hours' : (['closed-days', 'default-closed-days'].includes(subtabSlug) ? subtabSlug : 'daily-schedule');
                
                const calBtns = document.querySelectorAll('.calendar-subtab-btn');
                const calPanels = document.querySelectorAll('.calendar-subtab-panel');

                calBtns.forEach(b => {
                    const btnSubtab = b.getAttribute('data-calendar-subtab');
                    const isMatch = (btnSubtab === normalized);
                    if (isMatch) {
                        b.classList.remove('bg-slate-200/70', 'text-slate-600', 'font-semibold');
                        b.classList.add('bg-white', 'text-slate-900', 'border-t-2', 'border-[#00bfa5]', 'shadow-xs', 'font-bold');
                        b.setAttribute('aria-selected', 'true');
                    } else {
                        b.classList.remove('bg-white', 'text-slate-900', 'border-t-2', 'border-[#00bfa5]', 'shadow-xs', 'font-bold');
                        b.classList.add('bg-slate-200/70', 'text-slate-600', 'font-semibold');
                        b.setAttribute('aria-selected', 'false');
                    }
                });

                calPanels.forEach(panel => {
                    if (panel.id === 'calendar-subtab-panel-' + normalized) {
                        panel.classList.remove('hidden');
                    } else {
                        panel.classList.add('hidden');
                    }
                });

                if (pushState && window.history.pushState) {
                    const newUrl = window.location.origin + '/configuration/calendar/' + normalized;
                    if (window.location.pathname !== '/configuration/calendar/' + normalized) {
                        window.history.pushState({ tab: 'calendar', subtab: normalized }, '', newUrl);
                    }
                }
            }

            function switchCodeMappingSubtab(subtabSlug, pushState = true, action = '') {
                const normalized = ['payors', 'referrers', 'providers'].includes(subtabSlug) ? subtabSlug : 'services';
                
                const cmBtns = document.querySelectorAll('.code-mapping-subtab-btn');
                const cmPanels = document.querySelectorAll('.code-mapping-subtab-panel');

                cmBtns.forEach(b => {
                    const btnSubtab = b.getAttribute('data-code-mapping-subtab');
                    const isMatch = (btnSubtab === normalized);
                    if (isMatch) {
                        b.classList.remove('bg-slate-200/70', 'text-slate-600', 'font-semibold');
                        b.classList.add('bg-white', 'text-slate-900', 'border-t-2', 'border-[#00bfa5]', 'shadow-xs', 'font-bold');
                        b.setAttribute('aria-selected', 'true');
                    } else {
                        b.classList.remove('bg-white', 'text-slate-900', 'border-t-2', 'border-[#00bfa5]', 'shadow-xs', 'font-bold');
                        b.classList.add('bg-slate-200/70', 'text-slate-600', 'font-semibold');
                        b.setAttribute('aria-selected', 'false');
                    }
                });

                cmPanels.forEach(panel => {
                    if (panel.id === 'code-mapping-subtab-panel-' + normalized) {
                        panel.classList.remove('hidden');
                    } else {
                        panel.classList.add('hidden');
                    }
                });

                if (action === 'add') {
                    if (normalized === 'services') showCodeMappingAddView(false);
                    else if (normalized === 'payors') showPayorAddView(false);
                    else if (normalized === 'referrers') showReferrerAddView(false);
                    else if (normalized === 'providers') showProviderAddView(false);
                }

                if (pushState && window.history.pushState) {
                    const actionSuffix = action === 'add' ? '/add' : '';
                    const newUrl = window.location.origin + '/configuration/code-mapping/' + normalized + actionSuffix;
                    if (window.location.pathname !== '/configuration/code-mapping/' + normalized + actionSuffix) {
                        window.history.pushState({ tab: 'code-mapping', subtab: normalized, action: action }, '', newUrl);
                    }
                }
            }

            function switchKpisSubtab(subtabSlug, categorySlug = null, pushState = true) {
                const normalizedSubtab = ['providers', 'specialty', 'specialty-providers', 'custom'].includes(subtabSlug) ? subtabSlug : 'main';
                
                let normalizedCategory = categorySlug;
                if (!normalizedCategory) {
                    if (normalizedSubtab === 'specialty' || normalizedSubtab === 'specialty-providers') {
                        normalizedCategory = 'endo';
                    } else if (normalizedSubtab === 'custom') {
                        normalizedCategory = 'index';
                    } else if (normalizedSubtab === 'providers') {
                        normalizedCategory = 'hygiene';
                    } else {
                        normalizedCategory = 'hygiene';
                    }
                } else if (normalizedSubtab === 'providers' && normalizedCategory === 'office') {
                    normalizedCategory = 'hygiene';
                }

                // Update subtab buttons
                const kpiBtns = document.querySelectorAll('.kpi-subtab-btn');
                kpiBtns.forEach(b => {
                    const btnSubtab = b.getAttribute('data-kpi-subtab');
                    const isMatch = (btnSubtab === normalizedSubtab);
                    if (isMatch) {
                        b.classList.remove('text-gray-400', 'bg-gray-100', 'dark:bg-gray-600');
                        b.classList.add('translate-y-1', 'bg-white', 'text-black', 'dark:bg-gray-700', 'dark:text-white', 'font-semibold');
                        b.setAttribute('aria-selected', 'true');
                    } else {
                        b.classList.remove('translate-y-1', 'bg-white', 'text-black', 'dark:bg-gray-700', 'dark:text-white');
                        b.classList.add('text-gray-400', 'bg-gray-100', 'dark:bg-gray-600');
                        b.setAttribute('aria-selected', 'false');
                    }
                });

                // Toggle main list container vs Add Custom KPI view
                const kpisMainContainer = document.getElementById('kpisMainContainer');
                const kpisAddCustomKpiView = document.getElementById('kpisAddCustomKpiView');

                if (normalizedSubtab === 'custom' && normalizedCategory === 'create') {
                    if (kpisMainContainer) kpisMainContainer.classList.add('hidden');
                    if (kpisAddCustomKpiView) kpisAddCustomKpiView.classList.remove('hidden');
                } else {
                    if (kpisMainContainer) kpisMainContainer.classList.remove('hidden');
                    if (kpisAddCustomKpiView) kpisAddCustomKpiView.classList.add('hidden');
                }

                // Update Category Dropdown options visibility (Main vs Specialty vs Custom vs Providers)
                const catGroupMain = document.querySelectorAll('.cat-group-main');
                const catGroupSpecialty = document.querySelectorAll('.cat-group-specialty');
                const catOptOffice = document.querySelector('.cat-opt-office');
                const categoryMultiselect = document.getElementById('kpisCategoryMultiselect');
                const kpisCategorySelectedText = document.getElementById('kpisCategorySelectedText');
                const kpisStandardTopBar = document.getElementById('kpisStandardTopBar');
                const kpisCustomTopBar = document.getElementById('kpisCustomTopBar');
                const kpisStandardThead = document.getElementById('kpisStandardThead');
                const kpisCustomThead = document.getElementById('kpisCustomThead');
                const kpisMainActions = document.getElementById('kpisMainActions');
                const kpisProviderRightSpacer = document.getElementById('kpisProviderRightSpacer');
                const kpiThGoal = document.querySelectorAll('.kpi-th-goal');
                const kpiThActions = document.querySelectorAll('.kpi-th-actions');

                if (normalizedSubtab === 'specialty' || normalizedSubtab === 'specialty-providers') {
                    if (kpisStandardTopBar) { kpisStandardTopBar.classList.remove('hidden'); kpisStandardTopBar.classList.add('flex'); }
                    if (kpisCustomTopBar) { kpisCustomTopBar.classList.add('hidden'); kpisCustomTopBar.classList.remove('flex'); }
                    if (kpisStandardThead) kpisStandardThead.classList.remove('hidden');
                    if (kpisCustomThead) kpisCustomThead.classList.add('hidden');
                    catGroupMain.forEach(el => el.classList.add('hidden'));
                    catGroupSpecialty.forEach(el => el.classList.remove('hidden'));
                    if (catOptOffice) catOptOffice.classList.add('hidden');
                    if (categoryMultiselect) categoryMultiselect.classList.remove('hidden');
                    if (kpisCategorySelectedText) {
                        const specLabels = { 'endo': 'Endo', 'perio': 'Perio', 'ortho': 'Ortho', 'os': 'Os', 'pedo': 'Pedo' };
                        kpisCategorySelectedText.textContent = specLabels[normalizedCategory] || 'Endo';
                    }

                    if (kpisMainActions) kpisMainActions.classList.add('hidden');
                    if (kpisProviderRightSpacer) kpisProviderRightSpacer.classList.remove('hidden');
                    kpiThGoal.forEach(el => el.classList.add('hidden'));
                    kpiThActions.forEach(el => el.classList.add('hidden'));
                } else if (normalizedSubtab === 'custom') {
                    if (kpisStandardTopBar) { kpisStandardTopBar.classList.add('hidden'); kpisStandardTopBar.classList.remove('flex'); }
                    if (kpisCustomTopBar) { kpisCustomTopBar.classList.remove('hidden'); kpisCustomTopBar.classList.add('flex'); }
                    if (kpisStandardThead) kpisStandardThead.classList.add('hidden');
                    if (kpisCustomThead) kpisCustomThead.classList.remove('hidden');
                    catGroupMain.forEach(el => el.classList.add('hidden'));
                    catGroupSpecialty.forEach(el => el.classList.add('hidden'));
                    if (categoryMultiselect) categoryMultiselect.classList.add('hidden');
                    if (kpisMainActions) kpisMainActions.classList.add('hidden');
                    if (kpisProviderRightSpacer) kpisProviderRightSpacer.classList.add('hidden');
                    kpiThGoal.forEach(el => el.classList.remove('hidden'));
                    kpiThActions.forEach(el => el.classList.remove('hidden'));
                } else if (normalizedSubtab === 'providers') {
                    if (kpisStandardTopBar) { kpisStandardTopBar.classList.remove('hidden'); kpisStandardTopBar.classList.add('flex'); }
                    if (kpisCustomTopBar) { kpisCustomTopBar.classList.add('hidden'); kpisCustomTopBar.classList.remove('flex'); }
                    if (kpisStandardThead) kpisStandardThead.classList.remove('hidden');
                    if (kpisCustomThead) kpisCustomThead.classList.add('hidden');
                    catGroupMain.forEach(el => el.classList.remove('hidden'));
                    catGroupSpecialty.forEach(el => el.classList.add('hidden'));
                    if (catOptOffice) catOptOffice.classList.add('hidden');
                    if (categoryMultiselect) categoryMultiselect.classList.remove('hidden');
                    if (kpisMainActions) kpisMainActions.classList.add('hidden');
                    if (kpisProviderRightSpacer) kpisProviderRightSpacer.classList.remove('hidden');
                    kpiThGoal.forEach(el => el.classList.add('hidden'));
                    kpiThActions.forEach(el => el.classList.add('hidden'));
                    if (kpisCategorySelectedText) {
                        const provLabels = { 'hygiene': 'Hygiene', 'doctor': 'Doctor' };
                        kpisCategorySelectedText.textContent = provLabels[normalizedCategory] || 'Hygiene';
                    }
                } else {
                    // Main subtab
                    if (kpisStandardTopBar) { kpisStandardTopBar.classList.remove('hidden'); kpisStandardTopBar.classList.add('flex'); }
                    if (kpisCustomTopBar) { kpisCustomTopBar.classList.add('hidden'); kpisCustomTopBar.classList.remove('flex'); }
                    if (kpisStandardThead) kpisStandardThead.classList.remove('hidden');
                    if (kpisCustomThead) kpisCustomThead.classList.add('hidden');
                    catGroupMain.forEach(el => el.classList.remove('hidden'));
                    catGroupSpecialty.forEach(el => el.classList.add('hidden'));
                    if (catOptOffice) catOptOffice.classList.remove('hidden');
                    if (categoryMultiselect) categoryMultiselect.classList.remove('hidden');
                    if (kpisMainActions) kpisMainActions.classList.remove('hidden');
                    if (kpisProviderRightSpacer) kpisProviderRightSpacer.classList.add('hidden');
                    kpiThGoal.forEach(el => el.classList.remove('hidden'));
                    kpiThActions.forEach(el => el.classList.remove('hidden'));
                    if (kpisCategorySelectedText) {
                        const mainLabels = { 'hygiene': 'Hygiene', 'doctor': 'Doctor', 'office': 'Office' };
                        kpisCategorySelectedText.textContent = mainLabels[normalizedCategory] || 'Hygiene';
                    }
                }

                // Update active highlight in category dropdown
                document.querySelectorAll('.kpi-category-item').forEach(btn => {
                    const cat = btn.getAttribute('data-category');
                    if (cat === normalizedCategory) {
                        btn.classList.add('font-bold', 'text-[#00bfa5]', 'bg-[#00bfa5]/10');
                    } else {
                        btn.classList.remove('font-bold', 'text-[#00bfa5]', 'bg-[#00bfa5]/10');
                    }
                });

                // Filter table rows based on category and search
                filterKpisTable(normalizedSubtab, normalizedCategory);

                // Update URL history
                if (pushState && window.history.pushState) {
                    let path = '/configuration/kpis/' + normalizedSubtab;
                    if (normalizedCategory) {
                        path += '/' + normalizedCategory;
                    }
                    const newUrl = window.location.origin + path;
                    if (window.location.pathname !== path) {
                        window.history.pushState({ tab: 'kpis', subtab: normalizedSubtab, action: normalizedCategory }, '', newUrl);
                    }
                }

                if (window.lucide) window.lucide.createIcons();
            }

            function filterKpisTable(subtab = null, category = null) {
                if (!subtab) {
                    const activeSubtabBtn = document.querySelector('.kpi-subtab-btn[aria-selected="true"]');
                    subtab = activeSubtabBtn ? activeSubtabBtn.getAttribute('data-kpi-subtab') : 'main';
                }
                if (!category) {
                    const activeCategoryBtn = document.querySelector('.kpi-category-item.font-bold');
                    category = activeCategoryBtn ? activeCategoryBtn.getAttribute('data-category') : (subtab === 'custom' ? 'index' : (['specialty', 'specialty-providers'].includes(subtab) ? 'endo' : 'hygiene'));
                }

                const searchInput = document.getElementById('kpisSearchInput');
                const query = searchInput ? searchInput.value.trim().toLowerCase() : '';

                const lobSelect = document.getElementById('kpisCustomLobSelect');
                const selectedLob = lobSelect ? lobSelect.value.trim().toLowerCase() : '';

                const txSelect = document.getElementById('kpisCustomTxTypeSelect');
                const selectedTx = txSelect ? txSelect.value.trim().toLowerCase() : '';

                const kpiTypeSelect = document.getElementById('kpisCustomKpiTypeSelect');
                const selectedKpiType = kpiTypeSelect ? kpiTypeSelect.value.trim().toLowerCase() : '';

                const rows = document.querySelectorAll('.kpi-row');
                rows.forEach(row => {
                    const rowSubtab = row.getAttribute('data-subtab-group') || 'main';
                    const rowCategory = row.getAttribute('data-category') || '';
                    const name = row.getAttribute('data-kpi-name') || '';
                    const desc = row.getAttribute('data-kpi-desc') || '';

                    const subtabMatch = (rowSubtab === subtab);

                    if (subtab === 'custom') {
                        const rowLob = (row.getAttribute('data-line-of-business') || '').trim().toLowerCase();
                        const rowTx = (row.getAttribute('data-transaction') || '').trim().toLowerCase();
                        const rowDisplay = (row.getAttribute('data-display') || '').trim().toLowerCase();

                        const lobMatch = !selectedLob || rowLob === selectedLob;
                        const txMatch = !selectedTx || rowTx === selectedTx;
                        const kpiTypeMatch = !selectedKpiType || rowDisplay === selectedKpiType;
                        const searchMatch = !query || name.includes(query) || desc.includes(query);

                        if (subtabMatch && lobMatch && txMatch && kpiTypeMatch && searchMatch) {
                            row.classList.remove('hidden');
                        } else {
                            row.classList.add('hidden');
                        }
                    } else {
                        const categoryMatch = (rowCategory === category);
                        const searchMatch = !query || name.includes(query) || desc.includes(query);

                        if (subtabMatch && categoryMatch && searchMatch) {
                            row.classList.remove('hidden');
                        } else {
                            row.classList.add('hidden');
                        }
                    }
                });
            }

            function switchTab(tabSlug, pushState = true, subtabSlug = '', actionSlug = '') {
                // Update active tab link styling
                tabLinks.forEach(link => {
                    const isTarget = link.getAttribute('data-tab') === tabSlug;
                    if (isTarget) {
                        link.classList.remove('border-transparent', 'text-slate-500', 'hover:text-slate-800', 'hover:border-slate-300');
                        link.classList.add('border-[#00bfa5]', 'text-slate-900', 'font-bold');
                        link.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
                    } else {
                        link.classList.remove('border-[#00bfa5]', 'text-slate-900', 'font-bold');
                        link.classList.add('border-transparent', 'text-slate-500', 'hover:text-slate-800', 'hover:border-slate-300');
                    }
                });

                // Update tab panels
                tabPanels.forEach(panel => {
                    if (panel.id === 'tab-panel-' + tabSlug) {
                        panel.classList.remove('hidden');
                    } else {
                        panel.classList.add('hidden');
                    }
                });

                let targetSubtab = subtabSlug;
                if (tabSlug === 'code-mapping') {
                    targetSubtab = targetSubtab || 'services';
                    switchCodeMappingSubtab(targetSubtab, false, actionSlug);
                } else if (tabSlug === 'calendar') {
                    targetSubtab = targetSubtab || 'daily-schedule';
                    switchCalendarSubtab(targetSubtab, false);
                } else if (tabSlug === 'goals') {
                    targetSubtab = targetSubtab || 'office';
                    switchGoalsSubtab(targetSubtab, false);
                } else if (tabSlug === 'kpis') {
                    targetSubtab = targetSubtab || 'main';
                    const targetCategory = actionSlug || (['specialty', 'specialty-providers'].includes(targetSubtab) ? 'endo' : (targetSubtab === 'custom' ? 'index' : 'hygiene'));
                    switchKpisSubtab(targetSubtab, targetCategory, false);
                } else if (tabSlug === 'huddle') {
                    targetSubtab = targetSubtab || 'yesterday';
                    switchHuddleSubtab(targetSubtab, false);
                } else if (tabSlug === 'eod') {
                    targetSubtab = targetSubtab || 'basics';
                    switchEodSubtab(targetSubtab, false);
                }

                // Update browser URL without reloading
                if (pushState && window.history.pushState) {
                    let path = '/configuration/' + tabSlug;
                    if (targetSubtab) {
                        path += '/' + targetSubtab;
                        if (actionSlug) {
                            path += '/' + actionSlug;
                        }
                    }
                    const newUrl = window.location.origin + path;
                    if (window.location.pathname !== path) {
                        window.history.pushState({ tab: tabSlug, subtab: targetSubtab, action: actionSlug }, '', newUrl);
                    }
                }

                if (window.lucide) {
                    window.lucide.createIcons();
                }
            }

            // Click listener for tabs
            tabLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const tabSlug = this.getAttribute('data-tab');
                    const defaultSub = this.getAttribute('data-default-subtab') || '';
                    const defaultParts = defaultSub.split('/');
                    const subtab = defaultParts[0] || '';
                    const action = defaultParts[1] || '';
                    switchTab(tabSlug, true, subtab, action);
                });
            });

            // Subtab click listeners for KPIs
            document.querySelectorAll('.kpi-subtab-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const subtabSlug = this.getAttribute('data-kpi-subtab');
                    switchKpisSubtab(subtabSlug, null, true);
                });
            });

            // Handle browser back/forward history navigation
            window.addEventListener('popstate', function (e) {
                const pathParts = window.location.pathname.split('/').filter(Boolean);
                let currentTab = 'basic';
                let currentSubtab = '';
                let currentAction = '';
                if (pathParts.length >= 1 && pathParts[0] === 'configuration') {
                    currentTab = pathParts[1] || 'basic';
                    currentSubtab = pathParts[2] || '';
                    currentAction = pathParts[3] || '';
                }
                switchTab(currentTab, false, currentSubtab, currentAction);
            });

            // Location search filter on Email Settings tab
            const emailLocationSearch = document.getElementById('emailLocationSearch');
            if (emailLocationSearch) {
                emailLocationSearch.addEventListener('input', function () {
                    const query = this.value.trim().toLowerCase();
                    const rows = document.querySelectorAll('.email-setting-row');
                    rows.forEach(row => {
                        const loc = row.getAttribute('data-location') || '';
                        const id = row.getAttribute('data-id') || '';
                        if (!query || loc.includes(query) || id.includes(query)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Select all checkbox for email table
            const selectAllEmail = document.getElementById('email-select-all');
            if (selectAllEmail) {
                selectAllEmail.addEventListener('change', function () {
                    const cbs = document.querySelectorAll('.email-row-cb');
                    cbs.forEach(cb => cb.checked = selectAllEmail.checked);
                });
            }

            // Clear button for Email tab
            const clearEmailBtn = document.getElementById('clearEmailBtn');
            const respondToSenderEmail = document.getElementById('respondToSenderEmail');
            if (clearEmailBtn && respondToSenderEmail) {
                clearEmailBtn.addEventListener('click', function () {
                    respondToSenderEmail.value = '';
                });
            }

            // Provider Filters Live Handlers
            const provSearchInput = document.getElementById('provSearchInput');
            const provLocationFilter = document.getElementById('provLocationFilter');
            const provVisibilityFilter = document.getElementById('provVisibilityFilter');

            function filterProviders() {
                const query = provSearchInput ? provSearchInput.value.trim().toLowerCase() : '';
                const locVal = provLocationFilter ? provLocationFilter.value.trim().toLowerCase() : '';
                const visVal = provVisibilityFilter ? provVisibilityFilter.value.trim().toLowerCase() : 'all';

                const rows = document.querySelectorAll('.provider-row');
                rows.forEach(row => {
                    const name = row.getAttribute('data-name') || '';
                    const id = row.getAttribute('data-id') || '';
                    const loc = row.getAttribute('data-location') || '';
                    const vis = row.getAttribute('data-visible') || '';

                    const matchesQuery = !query || name.includes(query) || id.includes(query);
                    const matchesLoc = !locVal || loc === locVal;
                    const matchesVis = visVal === 'all' || vis === visVal;

                    if (matchesQuery && matchesLoc && matchesVis) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            if (provSearchInput) provSearchInput.addEventListener('input', filterProviders);
            if (provLocationFilter) provLocationFilter.addEventListener('change', filterProviders);
            if (provVisibilityFilter) provVisibilityFilter.addEventListener('change', filterProviders);

            // Provider Clear Settings Button
            const clearProviderSettingsBtn = document.getElementById('clearProviderSettingsBtn');
            if (clearProviderSettingsBtn) {
                clearProviderSettingsBtn.addEventListener('click', function () {
                    if (provSearchInput) provSearchInput.value = '';
                    if (provLocationFilter) provLocationFilter.value = '';
                    if (provVisibilityFilter) provVisibilityFilter.value = 'All';
                    filterProviders();
                });
            }

            // Goals Subtab Switching
            document.querySelectorAll('.goals-subtab-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const targetSubtab = this.getAttribute('data-goals-subtab');
                    switchGoalsSubtab(targetSubtab, true);
                });
            });

            // Goals Select All Checkbox (Office)
            const goalSelectAll = document.getElementById('goal-select-all');
            if (goalSelectAll) {
                goalSelectAll.addEventListener('change', function () {
                    const cbs = document.querySelectorAll('.goal-row-cb');
                    cbs.forEach(cb => cb.checked = goalSelectAll.checked);
                });
            }

            // Goals Select All Checkbox (Specialty)
            const goalSpecSelectAll = document.getElementById('goal-spec-select-all');
            if (goalSpecSelectAll) {
                goalSpecSelectAll.addEventListener('change', function () {
                    const cbs = document.querySelectorAll('.goal-spec-row-cb');
                    cbs.forEach(cb => cb.checked = goalSpecSelectAll.checked);
                });
            }

            // Goals Select All Checkbox (Provider)
            const goalProvSelectAll = document.getElementById('goal-prov-select-all');
            if (goalProvSelectAll) {
                goalProvSelectAll.addEventListener('change', function () {
                    const cbs = document.querySelectorAll('.goal-prov-row-cb');
                    cbs.forEach(cb => cb.checked = goalProvSelectAll.checked);
                });
            }

            // Goals Location Filter (Office Subtab)
            const goalLocationFilter = document.getElementById('goalLocationFilter');
            if (goalLocationFilter) {
                goalLocationFilter.addEventListener('change', function () {
                    const locVal = this.value.trim().toLowerCase();
                    const rows = document.querySelectorAll('.goal-row');
                    rows.forEach(row => {
                        const rowLoc = row.getAttribute('data-office') || '';
                        if (!locVal || rowLoc === locVal) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Goals Location Filter (Specialty Subtab)
            const goalSpecLocationFilter = document.getElementById('goalSpecLocationFilter');
            if (goalSpecLocationFilter) {
                goalSpecLocationFilter.addEventListener('change', function () {
                    const locVal = this.value.trim().toLowerCase();
                    const rows = document.querySelectorAll('.goal-specialty-row');
                    rows.forEach(row => {
                        const rowLoc = row.getAttribute('data-office') || '';
                        if (!locVal || rowLoc === locVal) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Goals Provider Subtab Filters
            const goalProvLocationFilter = document.getElementById('goalProvLocationFilter');
            const goalProvTypeFilter = document.getElementById('goalProvTypeFilter');

            function filterProviderGoals() {
                const locVal = goalProvLocationFilter ? goalProvLocationFilter.value.trim().toLowerCase() : '';
                const typeVal = goalProvTypeFilter ? goalProvTypeFilter.value.trim().toLowerCase() : 'all types';

                const rows = document.querySelectorAll('.goal-provider-row');
                rows.forEach(row => {
                    const rowLoc = (row.getAttribute('data-office') || '').toLowerCase();
                    const rowType = (row.getAttribute('data-provider-type') || '').toLowerCase();

                    const matchesLoc = !locVal || rowLoc === locVal;
                    const matchesType = typeVal === 'all types' || typeVal === 'all' || rowType === typeVal;

                    if (matchesLoc && matchesType) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            if (goalProvLocationFilter) goalProvLocationFilter.addEventListener('change', filterProviderGoals);
            if (goalProvTypeFilter) goalProvTypeFilter.addEventListener('change', filterProviderGoals);

            // Calendar Subtab Switching
            document.querySelectorAll('.calendar-subtab-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const targetSubtab = this.getAttribute('data-calendar-subtab');
                    switchCalendarSubtab(targetSubtab, true);
                });
            });

            // Calendar Select All Checkbox
            const calendarSelectAll = document.getElementById('calendar-select-all');
            if (calendarSelectAll) {
                calendarSelectAll.addEventListener('change', function () {
                    const cbs = document.querySelectorAll('.calendar-row-cb');
                    cbs.forEach(cb => cb.checked = calendarSelectAll.checked);
                });
            }

            // Calendar Location Filter
            const calendarLocationFilter = document.getElementById('calendarLocationFilter');
            if (calendarLocationFilter) {
                calendarLocationFilter.addEventListener('change', function () {
                    const locVal = this.value.trim().toLowerCase();
                    const rows = document.querySelectorAll('.calendar-schedule-row');
                    rows.forEach(row => {
                        const rowLoc = row.getAttribute('data-location') || '';
                        if (!locVal || rowLoc === locVal) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Work Hours Filtering
            const workHoursSearchInput = document.getElementById('workHoursSearchInput');
            const workHoursLocationFilter = document.getElementById('workHoursLocationFilter');

            function filterWorkHours() {
                const query = workHoursSearchInput ? workHoursSearchInput.value.trim().toLowerCase() : '';
                const locVal = workHoursLocationFilter ? workHoursLocationFilter.value.trim().toLowerCase() : '';

                const rows = document.querySelectorAll('.work-hours-row');
                rows.forEach(row => {
                    const name = row.getAttribute('data-name') || '';
                    const id = row.getAttribute('data-id') || '';
                    const office = row.getAttribute('data-office') || '';

                    const matchesQuery = !query || name.includes(query) || id.includes(query);
                    const matchesLoc = !locVal || office === locVal;

                    if (matchesQuery && matchesLoc) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            if (workHoursSearchInput) workHoursSearchInput.addEventListener('input', filterWorkHours);
            if (workHoursLocationFilter) workHoursLocationFilter.addEventListener('change', filterWorkHours);

            // Work Hours Calculation on Input
            document.querySelectorAll('.worked-hrs-input').forEach(input => {
                input.addEventListener('input', function () {
                    const row = this.closest('.work-hours-row');
                    if (!row) return;
                    let total = 0;
                    row.querySelectorAll('.worked-hrs-input').forEach(inp => {
                        const val = parseFloat(inp.value);
                        if (!isNaN(val)) total += val;
                    });
                    const totalCell = row.querySelector('.provider-total-hours');
                    if (totalCell) {
                        totalCell.textContent = total % 1 === 0 ? total : total.toFixed(1);
                    }
                });
            });

            // Closed Days Filtering
            const closedDaysSearchInput = document.getElementById('closedDaysSearchInput');
            const closedDaysLocationFilter = document.getElementById('closedDaysLocationFilter');

            function filterClosedDays() {
                const query = closedDaysSearchInput ? closedDaysSearchInput.value.trim().toLowerCase() : '';
                const locVal = closedDaysLocationFilter ? closedDaysLocationFilter.value.trim().toLowerCase() : '';

                const rows = document.querySelectorAll('.closed-day-row');
                let visibleCount = 0;
                rows.forEach(row => {
                    const name = row.getAttribute('data-name') || '';
                    const office = row.getAttribute('data-office') || '';
                    const date = row.getAttribute('data-date') || '';

                    const matchesQuery = !query || name.includes(query) || office.includes(query) || date.includes(query);
                    const matchesLoc = !locVal || locVal === 'all locations' || office.includes(locVal);

                    if (matchesQuery && matchesLoc) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                const emptyRow = document.getElementById('emptyClosedDaysRow');
                if (emptyRow) {
                    emptyRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : (rows.length === 0 ? '' : 'none');
                }
            }

            if (closedDaysSearchInput) closedDaysSearchInput.addEventListener('input', filterClosedDays);
            if (closedDaysLocationFilter) closedDaysLocationFilter.addEventListener('change', filterClosedDays);

            // Add Closed Day Modal Handlers
            const addClosedDayModal = document.getElementById('addClosedDayModal');
            const openAddClosedDayModalBtn = document.getElementById('openAddClosedDayModalBtn');
            const closeAddClosedDayModalBtn = document.getElementById('closeAddClosedDayModalBtn');
            const cancelAddClosedDayBtn = document.getElementById('cancelAddClosedDayBtn');
            const addClosedDayForm = document.getElementById('addClosedDayForm');

            function openClosedDayModal() {
                if (addClosedDayModal) addClosedDayModal.classList.remove('hidden');
            }

            function closeClosedDayModal() {
                if (addClosedDayModal) {
                    addClosedDayModal.classList.add('hidden');
                    if (addClosedDayForm) addClosedDayForm.reset();
                }
            }

            if (openAddClosedDayModalBtn) openAddClosedDayModalBtn.addEventListener('click', openClosedDayModal);
            if (closeAddClosedDayModalBtn) closeAddClosedDayModalBtn.addEventListener('click', closeClosedDayModal);
            if (cancelAddClosedDayBtn) cancelAddClosedDayBtn.addEventListener('click', closeClosedDayModal);

            // Add Closed Day Form Submission
            if (addClosedDayForm) {
                addClosedDayForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const name = document.getElementById('newClosedDayName').value.trim();
                    const office = document.getElementById('newClosedDayOffice').value;
                    const date = document.getElementById('newClosedDayDate').value;
                    const desc = document.getElementById('newClosedDayDesc').value.trim() || '--';

                    const tbody = document.getElementById('calendarClosedDaysTbody');
                    const emptyRow = document.getElementById('emptyClosedDaysRow');
                    if (emptyRow) emptyRow.style.display = 'none';

                    const tr = document.createElement('tr');
                    tr.className = 'closed-day-row border-b border-slate-100 hover:bg-slate-50 transition-colors';
                    tr.setAttribute('data-name', name.toLowerCase());
                    tr.setAttribute('data-office', office.toLowerCase());
                    tr.setAttribute('data-date', date);

                    tr.innerHTML = `
                        <td class="px-4 py-3 font-semibold text-slate-800 border-r border-slate-100">${name}</td>
                        <td class="px-4 py-3 text-slate-700 border-r border-slate-100">${office}</td>
                        <td class="px-4 py-3 text-slate-600 border-r border-slate-100">${desc}</td>
                        <td class="px-4 py-3 font-medium text-slate-700 border-r border-slate-100">${date}</td>
                        <td class="px-4 py-3 text-center">
                            <button type="button" class="text-rose-500 hover:text-rose-700 text-xs font-semibold p-1 hover:bg-rose-50 rounded transition-colors delete-closed-day-btn" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </td>
                    `;

                    tr.querySelector('.delete-closed-day-btn').addEventListener('click', function () {
                        tr.remove();
                    });

                    if (tbody) tbody.prepend(tr);
                    if (window.lucide) window.lucide.createIcons();
                    closeClosedDayModal();
                });
            }

            document.querySelectorAll('.delete-closed-day-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const row = this.closest('.closed-day-row');
                    if (row) row.remove();
                });
            });

            // Default Closed Days Filtering
            const defaultClosedDaysSearchInput = document.getElementById('defaultClosedDaysSearchInput');
            if (defaultClosedDaysSearchInput) {
                defaultClosedDaysSearchInput.addEventListener('input', function () {
                    const query = this.value.trim().toLowerCase();
                    const rows = document.querySelectorAll('.default-closed-day-row');
                    rows.forEach(row => {
                        const name = row.getAttribute('data-name') || '';
                        const date = row.getAttribute('data-date') || '';
                        if (!query || name.includes(query) || date.includes(query)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Dismiss Default Closed Alert Banner
            const dismissDefaultClosedAlertBtn = document.getElementById('dismissDefaultClosedAlertBtn');
            const defaultClosedDaysAlert = document.getElementById('defaultClosedDaysAlert');
            if (dismissDefaultClosedAlertBtn && defaultClosedDaysAlert) {
                dismissDefaultClosedAlertBtn.addEventListener('click', function () {
                    defaultClosedDaysAlert.remove();
                });
            }

            // Reminder Actions Dropdown Toggle
            document.querySelectorAll('.reminder-action-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const menu = this.closest('.reminder-action-menu').querySelector('.reminder-dropdown-menu');
                    document.querySelectorAll('.reminder-dropdown-menu').forEach(m => {
                        if (m !== menu) m.classList.add('hidden');
                    });
                    menu.classList.toggle('hidden');
                });
            });

            document.addEventListener('click', function () {
                document.querySelectorAll('.reminder-dropdown-menu').forEach(m => m.classList.add('hidden'));
            });

            // Code Mapping Subtab Switching
            document.querySelectorAll('.code-mapping-subtab-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const targetSubtab = this.getAttribute('data-code-mapping-subtab');
                    switchCodeMappingSubtab(targetSubtab, true);
                });
            });

            // Code Mapping Search Filtering
            const codeMappingServicesSearchInput = document.getElementById('codeMappingServicesSearchInput');
            if (codeMappingServicesSearchInput) {
                codeMappingServicesSearchInput.addEventListener('input', function () {
                    const query = this.value.trim().toLowerCase();
                    const rows = document.querySelectorAll('.code-mapping-row');
                    let visibleCount = 0;
                    rows.forEach(row => {
                        const baseCode = row.getAttribute('data-base-code') || '';
                        const type = row.getAttribute('data-type') || '';
                        const service = row.getAttribute('data-service') || '';
                        const code = row.getAttribute('data-code') || '';

                        if (!query || baseCode.includes(query) || type.includes(query) || service.includes(query) || code.includes(query)) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    const emptyRow = document.getElementById('emptyCodeMappingRow');
                    if (emptyRow) {
                        emptyRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : (rows.length === 0 ? '' : 'none');
                    }
                });
            }

            // Code Mapping View Switching (Services: List View vs Add Item View)
            const codeMappingServicesListView = document.getElementById('codeMappingServicesListView');
            const codeMappingAddItemView = document.getElementById('codeMappingAddItemView');
            const openAddCodeMappingItemBtn = document.getElementById('openAddCodeMappingItemBtn');
            const codeMappingSubmitAddBtn = document.getElementById('codeMappingSubmitAddBtn');
            const addBaseCodeInput = document.getElementById('addBaseCodeInput');
            const addTypeInput = document.getElementById('addTypeInput');
            const addDescriptionInput = document.getElementById('addDescriptionInput');
            const addServiceSelectAllCb = document.getElementById('addServiceSelectAllCb');
            const addServiceSearchInput = document.getElementById('addServiceSearchInput');
            const addServiceLocationFilter = document.getElementById('addServiceLocationFilter');

            function showCodeMappingAddView(pushState = true) {
                if (codeMappingServicesListView && codeMappingAddItemView) {
                    codeMappingServicesListView.classList.add('hidden');
                    codeMappingAddItemView.classList.remove('hidden');
                    if (pushState && window.history.pushState) {
                        window.history.pushState({ tab: 'code-mapping', subtab: 'services', action: 'add' }, '', '/configuration/code-mapping/services/add');
                    }
                    if (window.lucide) window.lucide.createIcons();
                }
            }

            function hideCodeMappingAddView(pushState = true) {
                if (codeMappingServicesListView && codeMappingAddItemView) {
                    codeMappingAddItemView.classList.add('hidden');
                    codeMappingServicesListView.classList.remove('hidden');
                    if (pushState && window.history.pushState) {
                        window.history.pushState({ tab: 'code-mapping', subtab: 'services' }, '', '/configuration/code-mapping/services');
                    }
                    if (addBaseCodeInput) addBaseCodeInput.value = '';
                    if (addTypeInput) addTypeInput.value = '';
                    if (addDescriptionInput) addDescriptionInput.value = '';
                    if (addServiceSelectAllCb) addServiceSelectAllCb.checked = false;
                    document.querySelectorAll('.add-service-item-cb').forEach(cb => cb.checked = false);
                    updateAddButtonState();
                    if (window.lucide) window.lucide.createIcons();
                }
            }

            if (openAddCodeMappingItemBtn) {
                openAddCodeMappingItemBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    showCodeMappingAddView();
                });
            }

            document.querySelectorAll('.code-mapping-back-link, .code-mapping-cancel-btn').forEach(el => {
                el.addEventListener('click', function (e) {
                    e.preventDefault();
                    hideCodeMappingAddView();
                });
            });

            function updateAddButtonState() {
                if (!codeMappingSubmitAddBtn) return;
                const baseVal = addBaseCodeInput ? addBaseCodeInput.value.trim() : '';
                const typeVal = addTypeInput ? addTypeInput.value.trim() : '';
                const checkedBoxes = document.querySelectorAll('.add-service-item-cb:checked');

                const isValid = baseVal.length > 0 || typeVal.length > 0 || checkedBoxes.length > 0;
                if (isValid) {
                    codeMappingSubmitAddBtn.removeAttribute('disabled');
                    codeMappingSubmitAddBtn.classList.remove('opacity-50', 'pointer-events-none');
                } else {
                    codeMappingSubmitAddBtn.setAttribute('disabled', 'disabled');
                    codeMappingSubmitAddBtn.classList.add('opacity-50', 'pointer-events-none');
                }
            }

            if (addBaseCodeInput) addBaseCodeInput.addEventListener('input', updateAddButtonState);
            if (addTypeInput) addTypeInput.addEventListener('input', updateAddButtonState);
            if (addDescriptionInput) addDescriptionInput.addEventListener('input', updateAddButtonState);

            // Select all services in Add View
            if (addServiceSelectAllCb) {
                addServiceSelectAllCb.addEventListener('change', function () {
                    const cbs = document.querySelectorAll('.add-service-item-cb');
                    cbs.forEach(cb => {
                        const row = cb.closest('.add-service-row');
                        if (!row || row.style.display !== 'none') {
                            cb.checked = addServiceSelectAllCb.checked;
                        }
                    });
                    updateAddButtonState();
                });
            }

            document.querySelectorAll('.add-service-item-cb').forEach(cb => {
                cb.addEventListener('change', updateAddButtonState);
            });

            // Filter services in Add View
            function filterAddServices() {
                const query = addServiceSearchInput ? addServiceSearchInput.value.trim().toLowerCase() : '';
                const rows = document.querySelectorAll('.add-service-row');
                rows.forEach(row => {
                    const id = row.getAttribute('data-id') || '';
                    const type = row.getAttribute('data-type') || '';
                    const desc = row.getAttribute('data-description') || '';
                    const ada = row.getAttribute('data-ada-code') || '';

                    if (!query || id.includes(query) || type.includes(query) || desc.includes(query) || ada.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            if (addServiceSearchInput) addServiceSearchInput.addEventListener('input', filterAddServices);
            if (addServiceLocationFilter) addServiceLocationFilter.addEventListener('change', filterAddServices);

            // Submit Add Item (Services)
            if (codeMappingSubmitAddBtn) {
                codeMappingSubmitAddBtn.addEventListener('click', function () {
                    const baseCode = addBaseCodeInput ? addBaseCodeInput.value.trim() : '';
                    const type = addTypeInput ? addTypeInput.value.trim() : '';
                    const desc = addDescriptionInput ? addDescriptionInput.value.trim() : '';
                    const checkedCbs = Array.from(document.querySelectorAll('.add-service-item-cb:checked'));

                    const tbody = document.getElementById('codeMappingServicesTbody');
                    const emptyRow = document.getElementById('emptyCodeMappingRow');
                    if (emptyRow) emptyRow.style.display = 'none';

                    if (checkedCbs.length === 0) {
                        // Create one row with provided form info
                        const newId = Math.floor(1000 + Math.random() * 9000);
                        const rowCode = baseCode || 'D' + Math.floor(1000 + Math.random() * 9000);
                        const rowType = type || 'General';
                        const rowService = desc || 'Service mapping';

                        createCodeMappingRow(newId, rowCode, rowType, rowService, rowCode, desc);
                    } else {
                        // Create a row for each checked service
                        checkedCbs.forEach(cb => {
                            const serviceId = cb.getAttribute('data-id');
                            const serviceType = type || cb.getAttribute('data-type');
                            const serviceDesc = cb.getAttribute('data-description');
                            const serviceAda = cb.getAttribute('data-ada-code');
                            const itemBaseCode = baseCode || serviceAda;

                            createCodeMappingRow(serviceId, itemBaseCode, serviceType, serviceDesc, serviceAda, desc);
                        });
                    }

                    hideCodeMappingAddView();
                });
            }

            function createCodeMappingRow(id, baseCode, type, service, code, desc = '') {
                const tbody = document.getElementById('codeMappingServicesTbody');
                if (!tbody) return;

                const tr = document.createElement('tr');
                tr.className = 'code-mapping-row border-b border-slate-100 hover:bg-slate-50 transition-colors';
                tr.setAttribute('data-base-code', baseCode.toLowerCase());
                tr.setAttribute('data-type', type.toLowerCase());
                tr.setAttribute('data-service', service.toLowerCase());
                tr.setAttribute('data-code', code.toLowerCase());
                tr.setAttribute('data-desc', desc);

                tr.innerHTML = `
                    <td class="px-3 py-2 text-center border-r border-slate-100 align-middle">
                        <input type="checkbox" class="rounded border-slate-300 text-[#00bfa5] w-4 h-4 cursor-pointer">
                    </td>
                    <td class="px-3 py-2 text-center font-semibold text-slate-700 border-r border-slate-100 align-middle code-mapping-cell-id">
                        ${id}
                    </td>
                    <td class="px-4 py-3 font-semibold text-slate-800 border-r border-slate-100 align-middle code-mapping-cell-base-code">
                        ${baseCode}
                    </td>
                    <td class="px-4 py-3 text-slate-700 border-r border-slate-100 align-middle code-mapping-cell-type">
                        ${type}
                    </td>
                    <td class="px-4 py-3 font-medium text-slate-800 border-r border-slate-100 align-middle code-mapping-cell-service">
                        ${service}
                    </td>
                    <td class="px-4 py-3 text-slate-700 border-r border-slate-100 align-middle code-mapping-cell-code">
                        ${code}
                    </td>
                    <td class="px-3 py-2 text-center align-middle">
                        <button type="button" class="text-slate-400 hover:text-[#00bfa5] p-1.5 rounded transition-colors edit-code-mapping-btn" title="Edit">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </button>
                        <button type="button" class="text-rose-500 hover:text-rose-700 p-1.5 rounded transition-colors delete-code-mapping-btn" title="Delete">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </td>
                `;

                tr.querySelector('.edit-code-mapping-btn').addEventListener('click', function () {
                    openCodeMappingModal(true, tr);
                });

                tr.querySelector('.delete-code-mapping-btn').addEventListener('click', function () {
                    tr.remove();
                    checkEmptyCodeMappingTable();
                });

                tbody.prepend(tr);
                if (window.lucide) window.lucide.createIcons();
            }

            // Code Mapping Modal & Item CRUD Handlers
            const codeMappingItemModal = document.getElementById('codeMappingItemModal');
            const closeCodeMappingModalBtn = document.getElementById('closeCodeMappingModalBtn');
            const cancelCodeMappingModalBtn = document.getElementById('cancelCodeMappingModalBtn');
            const codeMappingItemForm = document.getElementById('codeMappingItemForm');
            const codeMappingModalTitle = document.getElementById('codeMappingModalTitle');
            const submitCodeMappingBtn = document.getElementById('submitCodeMappingBtn');
            let editingCodeMappingRow = null;

            function openCodeMappingModal(isEdit = false, row = null) {
                if (!codeMappingItemModal) return;
                editingCodeMappingRow = isEdit ? row : null;

                if (isEdit && row) {
                    if (codeMappingModalTitle) codeMappingModalTitle.textContent = 'Edit Items';
                    if (submitCodeMappingBtn) submitCodeMappingBtn.textContent = 'Update';
                    
                    const baseCodeEl = row.querySelector('.code-mapping-cell-base-code');
                    const typeEl = row.querySelector('.code-mapping-cell-type');
                    const serviceEl = row.querySelector('.code-mapping-cell-service');
                    const desc = row.getAttribute('data-desc') || '';

                    document.getElementById('codeMappingBaseCode').value = baseCodeEl ? baseCodeEl.textContent.trim() : '';
                    document.getElementById('codeMappingType').value = typeEl ? typeEl.textContent.trim() : '';
                    document.getElementById('codeMappingService').value = serviceEl ? serviceEl.textContent.trim() : '';
                    document.getElementById('codeMappingDesc').value = desc;
                } else {
                    if (codeMappingModalTitle) codeMappingModalTitle.textContent = 'Edit Items';
                    if (submitCodeMappingBtn) submitCodeMappingBtn.textContent = 'Update';
                    if (codeMappingItemForm) codeMappingItemForm.reset();
                }

                codeMappingItemModal.classList.remove('hidden');
                if (window.lucide) window.lucide.createIcons();
            }

            function closeCodeMappingModal() {
                if (codeMappingItemModal) {
                    codeMappingItemModal.classList.add('hidden');
                    if (codeMappingItemForm) codeMappingItemForm.reset();
                    editingCodeMappingRow = null;
                }
            }

            if (closeCodeMappingModalBtn) closeCodeMappingModalBtn.addEventListener('click', closeCodeMappingModal);
            if (cancelCodeMappingModalBtn) cancelCodeMappingModalBtn.addEventListener('click', closeCodeMappingModal);

            if (codeMappingItemForm) {
                codeMappingItemForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const baseCode = document.getElementById('codeMappingBaseCode').value.trim();
                    const type = document.getElementById('codeMappingType').value.trim();
                    const service = document.getElementById('codeMappingService').value.trim();
                    const desc = document.getElementById('codeMappingDesc').value.trim();
                    const code = baseCode;

                    const tbody = document.getElementById('codeMappingServicesTbody');
                    const emptyRow = document.getElementById('emptyCodeMappingRow');

                    if (editingCodeMappingRow) {
                        editingCodeMappingRow.setAttribute('data-base-code', baseCode.toLowerCase());
                        editingCodeMappingRow.setAttribute('data-type', type.toLowerCase());
                        editingCodeMappingRow.setAttribute('data-service', service.toLowerCase());
                        editingCodeMappingRow.setAttribute('data-code', code.toLowerCase());
                        editingCodeMappingRow.setAttribute('data-desc', desc);

                        const baseCodeEl = editingCodeMappingRow.querySelector('.code-mapping-cell-base-code');
                        const typeEl = editingCodeMappingRow.querySelector('.code-mapping-cell-type');
                        const serviceEl = editingCodeMappingRow.querySelector('.code-mapping-cell-service');
                        const codeEl = editingCodeMappingRow.querySelector('.code-mapping-cell-code');

                        if (baseCodeEl) baseCodeEl.textContent = baseCode;
                        if (typeEl) typeEl.textContent = type;
                        if (serviceEl) serviceEl.textContent = service;
                        if (codeEl) codeEl.textContent = code;
                    } else {
                        if (emptyRow) emptyRow.style.display = 'none';
                        const newId = Math.floor(1000 + Math.random() * 9000);
                        createCodeMappingRow(newId, baseCode, type, service, code, desc);
                    }

                    if (window.lucide) window.lucide.createIcons();
                    closeCodeMappingModal();
                });
            }

            function checkEmptyCodeMappingTable() {
                const tbody = document.getElementById('codeMappingServicesTbody');
                const rows = tbody ? tbody.querySelectorAll('.code-mapping-row') : [];
                let emptyRow = document.getElementById('emptyCodeMappingRow');
                if (rows.length === 0) {
                    if (emptyRow) {
                        emptyRow.style.display = '';
                    } else if (tbody) {
                        const tr = document.createElement('tr');
                        tr.id = 'emptyCodeMappingRow';
                        tr.innerHTML = '<td colspan="7" class="py-6 text-center text-slate-500 text-xs font-medium bg-slate-50/50">Click "Add Item" to add a service mapping.</td>';
                        tbody.appendChild(tr);
                    }
                }
            }

            document.querySelectorAll('.edit-code-mapping-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const row = this.closest('.code-mapping-row');
                    if (row) openCodeMappingModal(true, row);
                });
            });

            document.querySelectorAll('.delete-code-mapping-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const row = this.closest('.code-mapping-row');
                    if (row) {
                        row.remove();
                        checkEmptyCodeMappingTable();
                    }
                });
            });

            // Payors Search Filtering
            const codeMappingPayorsSearchInput = document.getElementById('codeMappingPayorsSearchInput');
            if (codeMappingPayorsSearchInput) {
                codeMappingPayorsSearchInput.addEventListener('input', function () {
                    const query = this.value.trim().toLowerCase();
                    const rows = document.querySelectorAll('.payor-row');
                    rows.forEach(row => {
                        const id = row.getAttribute('data-id') || '';
                        const payorName = row.getAttribute('data-payor-name') || '';
                        const uniformName = row.getAttribute('data-uniform-name') || '';

                        if (!query || id.includes(query) || payorName.includes(query) || uniformName.includes(query)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Payors Select All Checkbox (List View)
            const payorSelectAllCb = document.getElementById('payorSelectAllCb');
            if (payorSelectAllCb) {
                payorSelectAllCb.addEventListener('change', function () {
                    const cbs = document.querySelectorAll('.payor-row-cb');
                    cbs.forEach(cb => {
                        const row = cb.closest('.payor-row');
                        if (!row || row.style.display !== 'none') {
                            cb.checked = payorSelectAllCb.checked;
                        }
                    });
                });
            }

            // Payor Action Dropdowns
            document.querySelectorAll('.payor-action-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const menu = this.closest('.payor-action-container').querySelector('.payor-dropdown-menu');
                    document.querySelectorAll('.payor-dropdown-menu').forEach(m => {
                        if (m !== menu) m.classList.add('hidden');
                    });
                    menu.classList.toggle('hidden');
                });
            });

            document.addEventListener('click', function () {
                document.querySelectorAll('.payor-dropdown-menu').forEach(m => m.classList.add('hidden'));
            });

            // Code Mapping Payors View Switching (List View vs Add Item View)
            const codeMappingPayorsListView = document.getElementById('codeMappingPayorsListView');
            const codeMappingPayorAddItemView = document.getElementById('codeMappingPayorAddItemView');
            const openAddPayorItemBtn = document.getElementById('openAddPayorItemBtn');
            const codeMappingSubmitPayorAddBtn = document.getElementById('codeMappingSubmitPayorAddBtn');
            const addPayorUniformNameInput = document.getElementById('addPayorUniformNameInput');
            const addPayorSelectAllCb = document.getElementById('addPayorSelectAllCb');
            const addPayorSearchInput = document.getElementById('addPayorSearchInput');
            const addPayorLocationFilter = document.getElementById('addPayorLocationFilter');

            function showPayorAddView(pushState = true) {
                if (codeMappingPayorsListView && codeMappingPayorAddItemView) {
                    codeMappingPayorsListView.classList.add('hidden');
                    codeMappingPayorAddItemView.classList.remove('hidden');
                    if (pushState && window.history.pushState) {
                        window.history.pushState({ tab: 'code-mapping', subtab: 'payors', action: 'add' }, '', '/configuration/code-mapping/payors/add');
                    }
                    if (window.lucide) window.lucide.createIcons();
                }
            }

            function hidePayorAddView(pushState = true) {
                if (codeMappingPayorsListView && codeMappingPayorAddItemView) {
                    codeMappingPayorAddItemView.classList.add('hidden');
                    codeMappingPayorsListView.classList.remove('hidden');
                    if (pushState && window.history.pushState) {
                        window.history.pushState({ tab: 'code-mapping', subtab: 'payors' }, '', '/configuration/code-mapping/payors');
                    }
                    if (addPayorUniformNameInput) addPayorUniformNameInput.value = '';
                    if (addPayorSelectAllCb) addPayorSelectAllCb.checked = false;
                    document.querySelectorAll('.add-payor-item-cb').forEach(cb => cb.checked = false);
                    updatePayorAddButtonState();
                    if (window.lucide) window.lucide.createIcons();
                }
            }

            if (openAddPayorItemBtn) {
                openAddPayorItemBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    showPayorAddView();
                });
            }

            document.querySelectorAll('.payor-back-link, .payor-cancel-btn').forEach(el => {
                el.addEventListener('click', function (e) {
                    e.preventDefault();
                    hidePayorAddView();
                });
            });

            function updatePayorAddButtonState() {
                if (!codeMappingSubmitPayorAddBtn) return;
                const uniformVal = addPayorUniformNameInput ? addPayorUniformNameInput.value.trim() : '';
                const checkedBoxes = document.querySelectorAll('.add-payor-item-cb:checked');

                const isValid = uniformVal.length > 0 || checkedBoxes.length > 0;
                if (isValid) {
                    codeMappingSubmitPayorAddBtn.removeAttribute('disabled');
                    codeMappingSubmitPayorAddBtn.classList.remove('opacity-50', 'pointer-events-none');
                } else {
                    codeMappingSubmitPayorAddBtn.setAttribute('disabled', 'disabled');
                    codeMappingSubmitPayorAddBtn.classList.add('opacity-50', 'pointer-events-none');
                }
            }

            if (addPayorUniformNameInput) addPayorUniformNameInput.addEventListener('input', updatePayorAddButtonState);

            // Select all in Available Payors Table
            if (addPayorSelectAllCb) {
                addPayorSelectAllCb.addEventListener('change', function () {
                    const cbs = document.querySelectorAll('.add-payor-item-cb');
                    cbs.forEach(cb => {
                        const row = cb.closest('.add-payor-row');
                        if (!row || row.style.display !== 'none') {
                            cb.checked = addPayorSelectAllCb.checked;
                        }
                    });
                    updatePayorAddButtonState();
                });
            }

            document.querySelectorAll('.add-payor-item-cb').forEach(cb => {
                cb.addEventListener('change', updatePayorAddButtonState);
            });

            // Filter available payors in Add Payor View
            function filterAddPayors() {
                const query = addPayorSearchInput ? addPayorSearchInput.value.trim().toLowerCase() : '';
                const locVal = addPayorLocationFilter ? addPayorLocationFilter.value.trim().toLowerCase() : '';
                const rows = document.querySelectorAll('.add-payor-row');
                rows.forEach(row => {
                    const id = row.getAttribute('data-id') || '';
                    const name = row.getAttribute('data-name') || '';
                    const loc = row.getAttribute('data-location') || '';

                    const matchesQuery = !query || id.includes(query) || name.includes(query);
                    const matchesLoc = !locVal || loc === locVal;

                    if (matchesQuery && matchesLoc) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            if (addPayorSearchInput) addPayorSearchInput.addEventListener('input', filterAddPayors);
            if (addPayorLocationFilter) addPayorLocationFilter.addEventListener('change', filterAddPayors);

            // Submit Add Payor Item
            if (codeMappingSubmitPayorAddBtn) {
                codeMappingSubmitPayorAddBtn.addEventListener('click', function () {
                    const uniformName = (addPayorUniformNameInput && addPayorUniformNameInput.value.trim()) ? addPayorUniformNameInput.value.trim() : 'Delta';
                    const checkedCbs = Array.from(document.querySelectorAll('.add-payor-item-cb:checked'));

                    const tbody = document.getElementById('codeMappingPayorsTbody');

                    if (checkedCbs.length === 0) {
                        // Create one row with default info
                        const newId = Math.floor(1000 + Math.random() * 9000);
                        const payorName = uniformName + ' Dental - ' + newId;
                        createPayorRow(newId, payorName, uniformName);
                    } else {
                        // Create a row for each checked payor
                        checkedCbs.forEach(cb => {
                            const payorId = cb.getAttribute('data-id');
                            const payorName = cb.getAttribute('data-name');
                            createPayorRow(payorId, payorName, uniformName);
                        });
                    }

                    hidePayorAddView();
                });
            }

            function createPayorRow(id, name, uniformName) {
                const tbody = document.getElementById('codeMappingPayorsTbody');
                if (!tbody) return;

                // Check if row with same ID already exists
                const existingRow = tbody.querySelector(`.payor-row[data-id="${id}"]`);
                if (existingRow) {
                    existingRow.setAttribute('data-uniform-name', uniformName.toLowerCase());
                    const uniformCell = existingRow.querySelector('.payor-cell-uniform');
                    if (uniformCell) uniformCell.textContent = uniformName;
                    return;
                }

                const tr = document.createElement('tr');
                tr.className = 'payor-row border-b border-slate-100 hover:bg-slate-50 transition-colors odd:bg-slate-50/40';
                tr.setAttribute('data-id', id);
                tr.setAttribute('data-payor-name', name.toLowerCase());
                tr.setAttribute('data-uniform-name', uniformName.toLowerCase());

                tr.innerHTML = `
                    <td role="cell" class="px-3 py-2 align-middle text-xs border-r border-slate-100 text-center" style="min-width: 0.1%;">
                        <label class="font-semibold items-center inline-flex text-xs cursor-pointer">
                            <input type="checkbox" class="payor-row-cb rounded border-slate-300 text-[#00bfa5] w-4 h-4 cursor-pointer" value="${id}">
                        </label>
                    </td>
                    <td role="cell" class="px-3 py-2 align-middle text-xs font-semibold text-slate-700 border-r border-slate-100 payor-cell-id" style="min-width: 0.1%;">
                        ${id}
                    </td>
                    <td role="cell" class="px-4 py-2 align-middle text-xs font-medium text-slate-800 border-r border-slate-100 payor-cell-name" style="min-width: 10rem;">
                        ${name}
                    </td>
                    <td role="cell" class="px-4 py-2 align-middle text-xs text-slate-700 border-r border-slate-100 payor-cell-uniform" style="min-width: 10rem;">
                        ${uniformName}
                    </td>
                    <td role="cell" class="px-3 py-2 align-middle text-xs text-center border-r border-slate-100" style="min-width: 0.1%;">
                        <div class="relative inline-block payor-action-container">
                            <button type="button" class="payor-action-btn text-[#00bfa5] hover:text-[#00a892] p-1 rounded focus:outline-none transition-colors cursor-pointer" title="Actions">
                                <i data-lucide="more-horizontal" class="w-5 h-5"></i>
                            </button>
                            <div class="payor-dropdown-menu hidden absolute right-0 mt-1 w-40 bg-white border border-slate-200 rounded shadow-lg z-30 py-1 text-left">
                                <button type="button" class="edit-payor-btn w-full text-xs font-semibold flex items-center px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-[#00bfa5] transition-colors cursor-pointer">
                                    <i data-lucide="edit-3" class="w-4 h-4 mr-2.5 text-[#00bfa5]"></i>
                                    <span>Edit Payor</span>
                                </button>
                                <button type="button" class="delete-payor-btn w-full text-xs font-semibold flex items-center px-4 py-2.5 text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                    <i data-lucide="trash-2" class="w-4 h-4 mr-2.5 text-rose-500"></i>
                                    <span>Delete Payor</span>
                                </button>
                            </div>
                        </div>
                    </td>
                `;

                tr.querySelector('.payor-action-btn').addEventListener('click', function (e) {
                    e.stopPropagation();
                    const menu = tr.querySelector('.payor-dropdown-menu');
                    document.querySelectorAll('.payor-dropdown-menu').forEach(m => {
                        if (m !== menu) m.classList.add('hidden');
                    });
                    menu.classList.toggle('hidden');
                });

                tr.querySelector('.edit-payor-btn').addEventListener('click', function (e) {
                    e.stopPropagation();
                    document.querySelectorAll('.payor-dropdown-menu').forEach(m => m.classList.add('hidden'));
                    openSetUniformNameModal(tr);
                });

                tr.querySelector('.delete-payor-btn').addEventListener('click', function (e) {
                    e.stopPropagation();
                    tr.remove();
                });

                tbody.prepend(tr);
                if (window.lucide) window.lucide.createIcons();
            }

            // Set Uniform Name Modal Handlers (for Edit Payor in list view)
            const setUniformNameModal = document.getElementById('setUniformNameModal');
            const closeSetUniformNameModalBtn = document.getElementById('closeSetUniformNameModalBtn');
            const cancelSetUniformNameBtn = document.getElementById('cancelSetUniformNameBtn');
            const setUniformNameForm = document.getElementById('setUniformNameForm');
            const uniformNameInput = document.getElementById('uniformNameInput');
            let editingPayorRow = null;

            function openSetUniformNameModal(row = null) {
                if (!setUniformNameModal) return;
                editingPayorRow = row;
                if (row) {
                    const currentUniform = row.querySelector('.payor-cell-uniform') ? row.querySelector('.payor-cell-uniform').textContent.trim() : '';
                    if (uniformNameInput) uniformNameInput.value = currentUniform;
                } else {
                    if (uniformNameInput) uniformNameInput.value = 'Delta';
                }
                setUniformNameModal.classList.remove('hidden');
                if (window.lucide) window.lucide.createIcons();
            }

            function closeSetUniformNameModal() {
                if (setUniformNameModal) {
                    setUniformNameModal.classList.add('hidden');
                    if (setUniformNameForm) setUniformNameForm.reset();
                    editingPayorRow = null;
                }
            }

            if (closeSetUniformNameModalBtn) closeSetUniformNameModalBtn.addEventListener('click', closeSetUniformNameModal);
            if (cancelSetUniformNameBtn) cancelSetUniformNameBtn.addEventListener('click', closeSetUniformNameModal);

            document.querySelectorAll('.edit-payor-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const row = this.closest('.payor-row');
                    document.querySelectorAll('.payor-dropdown-menu').forEach(m => m.classList.add('hidden'));
                    openSetUniformNameModal(row);
                });
            });

            document.querySelectorAll('.delete-payor-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const row = this.closest('.payor-row');
                    if (row) row.remove();
                });
            });

            if (setUniformNameForm) {
                setUniformNameForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const newUniform = uniformNameInput ? uniformNameInput.value.trim() : 'Delta';

                    if (editingPayorRow) {
                        editingPayorRow.setAttribute('data-uniform-name', newUniform.toLowerCase());
                        const uniformCell = editingPayorRow.querySelector('.payor-cell-uniform');
                        if (uniformCell) uniformCell.textContent = newUniform;
                    } else {
                        // Check if multiple payor checkboxes are selected
                        const checkedRows = Array.from(document.querySelectorAll('.payor-row-cb:checked')).map(cb => cb.closest('.payor-row')).filter(Boolean);
                        if (checkedRows.length > 0) {
                            checkedRows.forEach(row => {
                                row.setAttribute('data-uniform-name', newUniform.toLowerCase());
                                const uniformCell = row.querySelector('.payor-cell-uniform');
                                if (uniformCell) uniformCell.textContent = newUniform;
                            });
                        }
                    }

                    closeSetUniformNameModal();
                });
            }

            // Referrers Search Filtering
            const codeMappingReferrersSearchInput = document.getElementById('codeMappingReferrersSearchInput');
            if (codeMappingReferrersSearchInput) {
                codeMappingReferrersSearchInput.addEventListener('input', function () {
                    const query = this.value.trim().toLowerCase();
                    const rows = document.querySelectorAll('.referrer-row');
                    let visibleCount = 0;
                    rows.forEach(row => {
                        const id = row.getAttribute('data-id') || '';
                        const referrerName = row.getAttribute('data-referrer-name') || '';
                        const uniformName = row.getAttribute('data-uniform-name') || '';

                        if (!query || id.includes(query) || referrerName.includes(query) || uniformName.includes(query)) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    const emptyRow = document.getElementById('emptyReferrersRow');
                    if (emptyRow) {
                        if (rows.length === 0) {
                            emptyRow.style.display = '';
                        } else if (visibleCount === 0) {
                            emptyRow.style.display = '';
                        } else {
                            emptyRow.style.display = 'none';
                        }
                    }
                });
            }

            // Referrers Select All Checkbox
            const referrerSelectAllCb = document.getElementById('referrerSelectAllCb');
            const referrerSelectAllLabel = document.getElementById('referrerSelectAllLabel');
            if (referrerSelectAllCb) {
                referrerSelectAllCb.addEventListener('change', function () {
                    const cbs = document.querySelectorAll('.referrer-row-cb');
                    cbs.forEach(cb => {
                        const row = cb.closest('.referrer-row');
                        if (!row || row.style.display !== 'none') {
                            cb.checked = referrerSelectAllCb.checked;
                        }
                    });
                });
            }

            // Referrer Action Dropdowns
            document.querySelectorAll('.referrer-action-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const menu = this.closest('.referrer-action-container').querySelector('.referrer-dropdown-menu');
                    document.querySelectorAll('.referrer-dropdown-menu').forEach(m => {
                        if (m !== menu) m.classList.add('hidden');
                    });
                    menu.classList.toggle('hidden');
                });
            });

            document.addEventListener('click', function () {
                document.querySelectorAll('.referrer-dropdown-menu').forEach(m => m.classList.add('hidden'));
            });

            // Set Referrer Uniform Name Modal Handlers
            const setReferrerUniformNameModal = document.getElementById('setReferrerUniformNameModal');
            const closeSetReferrerUniformNameModalBtn = document.getElementById('closeSetReferrerUniformNameModalBtn');
            const cancelSetReferrerUniformNameBtn = document.getElementById('cancelSetReferrerUniformNameBtn');
            const setReferrerUniformNameForm = document.getElementById('setReferrerUniformNameForm');
            const referrerNameInput = document.getElementById('referrerNameInput');
            const referrerUniformNameInput = document.getElementById('referrerUniformNameInput');
            const openAddReferrerItemBtn = document.getElementById('openAddReferrerItemBtn');
            let editingReferrerRow = null;

            function openSetReferrerUniformNameModal(row = null) {
                if (!setReferrerUniformNameModal) return;
                editingReferrerRow = row;
                if (row) {
                    const currentName = row.querySelector('.referrer-cell-name') ? row.querySelector('.referrer-cell-name').textContent.trim() : '';
                    const currentUniform = row.querySelector('.referrer-cell-uniform') ? row.querySelector('.referrer-cell-uniform').textContent.trim() : '';
                    if (referrerNameInput) referrerNameInput.value = currentName;
                    if (referrerUniformNameInput) referrerUniformNameInput.value = currentUniform;
                } else {
                    if (referrerNameInput) referrerNameInput.value = '';
                    if (referrerUniformNameInput) referrerUniformNameInput.value = '';
                }
                setReferrerUniformNameModal.classList.remove('hidden');
                if (window.lucide) window.lucide.createIcons();
            }

            function closeSetReferrerUniformNameModal() {
                if (setReferrerUniformNameModal) {
                    setReferrerUniformNameModal.classList.add('hidden');
                    if (setReferrerUniformNameForm) setReferrerUniformNameForm.reset();
                    editingReferrerRow = null;
                }
            }

            if (closeSetReferrerUniformNameModalBtn) closeSetReferrerUniformNameModalBtn.addEventListener('click', closeSetReferrerUniformNameModal);
            if (cancelSetReferrerUniformNameBtn) cancelSetReferrerUniformNameBtn.addEventListener('click', closeSetReferrerUniformNameModal);

            // Code Mapping Referrers View Switching (List View vs Add Item View)
            const codeMappingReferrersListView = document.getElementById('codeMappingReferrersListView');
            const codeMappingReferrerAddItemView = document.getElementById('codeMappingReferrerAddItemView');
            const codeMappingSubmitReferrerAddBtn = document.getElementById('codeMappingSubmitReferrerAddBtn');
            const addReferrerUniformNameInput = document.getElementById('addReferrerUniformNameInput');
            const addReferrerSelectAllCb = document.getElementById('addReferrerSelectAllCb');
            const addReferrerSearchInput = document.getElementById('addReferrerSearchInput');
            const addReferrerLocationFilter = document.getElementById('addReferrerLocationFilter');

            function showReferrerAddView(pushState = true) {
                if (codeMappingReferrersListView && codeMappingReferrerAddItemView) {
                    codeMappingReferrersListView.classList.add('hidden');
                    codeMappingReferrerAddItemView.classList.remove('hidden');
                    if (pushState && window.history.pushState) {
                        window.history.pushState({ tab: 'code-mapping', subtab: 'referrers', action: 'add' }, '', '/configuration/code-mapping/referrers/add');
                    }
                    if (window.lucide) window.lucide.createIcons();
                }
            }

            function hideReferrerAddView(pushState = true) {
                if (codeMappingReferrersListView && codeMappingReferrerAddItemView) {
                    codeMappingReferrerAddItemView.classList.add('hidden');
                    codeMappingReferrersListView.classList.remove('hidden');
                    if (pushState && window.history.pushState) {
                        window.history.pushState({ tab: 'code-mapping', subtab: 'referrers' }, '', '/configuration/code-mapping/referrers');
                    }
                    if (addReferrerUniformNameInput) addReferrerUniformNameInput.value = '';
                    if (addReferrerSelectAllCb) addReferrerSelectAllCb.checked = false;
                    document.querySelectorAll('.add-referrer-item-cb').forEach(cb => cb.checked = false);
                    updateReferrerAddButtonState();
                    if (window.lucide) window.lucide.createIcons();
                }
            }

            if (openAddReferrerItemBtn) {
                openAddReferrerItemBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    showReferrerAddView();
                });
            }

            document.querySelectorAll('.referrer-back-link, .referrer-cancel-btn').forEach(el => {
                el.addEventListener('click', function (e) {
                    e.preventDefault();
                    hideReferrerAddView();
                });
            });

            function updateReferrerAddButtonState() {
                if (!codeMappingSubmitReferrerAddBtn) return;
                const uniformVal = addReferrerUniformNameInput ? addReferrerUniformNameInput.value.trim() : '';
                const checkedBoxes = document.querySelectorAll('.add-referrer-item-cb:checked');

                const isValid = uniformVal.length > 0 || checkedBoxes.length > 0;
                if (isValid) {
                    codeMappingSubmitReferrerAddBtn.removeAttribute('disabled');
                    codeMappingSubmitReferrerAddBtn.classList.remove('opacity-50', 'pointer-events-none');
                } else {
                    codeMappingSubmitReferrerAddBtn.setAttribute('disabled', 'disabled');
                    codeMappingSubmitReferrerAddBtn.classList.add('opacity-50', 'pointer-events-none');
                }
            }

            if (addReferrerUniformNameInput) addReferrerUniformNameInput.addEventListener('input', updateReferrerAddButtonState);

            // Select all in Available Referrers Table
            if (addReferrerSelectAllCb) {
                addReferrerSelectAllCb.addEventListener('change', function () {
                    const cbs = document.querySelectorAll('.add-referrer-item-cb');
                    cbs.forEach(cb => {
                        const row = cb.closest('.add-referrer-row');
                        if (!row || row.style.display !== 'none') {
                            cb.checked = addReferrerSelectAllCb.checked;
                        }
                    });
                    updateReferrerAddButtonState();
                });
            }

            document.querySelectorAll('.add-referrer-item-cb').forEach(cb => {
                cb.addEventListener('change', updateReferrerAddButtonState);
            });

            // Filter available referrers in Add Referrer View
            function filterAddReferrers() {
                const query = addReferrerSearchInput ? addReferrerSearchInput.value.trim().toLowerCase() : '';
                const locVal = addReferrerLocationFilter ? addReferrerLocationFilter.value.trim().toLowerCase() : '';
                const rows = document.querySelectorAll('.add-referrer-row');
                rows.forEach(row => {
                    const id = row.getAttribute('data-id') || '';
                    const name = row.getAttribute('data-name') || '';
                    const loc = row.getAttribute('data-location') || '';

                    const matchesQuery = !query || id.includes(query) || name.includes(query);
                    const matchesLoc = !locVal || loc === locVal;

                    if (matchesQuery && matchesLoc) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            if (addReferrerSearchInput) addReferrerSearchInput.addEventListener('input', filterAddReferrers);
            if (addReferrerLocationFilter) addReferrerLocationFilter.addEventListener('change', filterAddReferrers);

            // Submit Add Referrer Item
            if (codeMappingSubmitReferrerAddBtn) {
                codeMappingSubmitReferrerAddBtn.addEventListener('click', function () {
                    const uniformName = (addReferrerUniformNameInput && addReferrerUniformNameInput.value.trim()) ? addReferrerUniformNameInput.value.trim() : 'Patient Referral';
                    const checkedCbs = Array.from(document.querySelectorAll('.add-referrer-item-cb:checked'));

                    if (checkedCbs.length === 0) {
                        // Create one row with default info
                        const newId = Math.floor(100 + Math.random() * 900);
                        const refName = uniformName + ' - ' + newId;
                        createReferrerRow(newId, refName, uniformName);
                    } else {
                        // Create a row for each checked referrer
                        checkedCbs.forEach(cb => {
                            const refId = cb.getAttribute('data-id');
                            const refName = cb.getAttribute('data-name');
                            createReferrerRow(refId, refName, uniformName);
                        });
                    }

                    hideReferrerAddView();
                });
            }

            function checkEmptyReferrersTable() {
                const tbody = document.getElementById('codeMappingReferrersTbody');
                const rows = tbody ? tbody.querySelectorAll('.referrer-row') : [];
                let emptyRow = document.getElementById('emptyReferrersRow');
                const selectAll = document.getElementById('referrerSelectAllCb');
                const selectAllLabel = document.getElementById('referrerSelectAllLabel');

                if (rows.length === 0) {
                    if (emptyRow) {
                        emptyRow.style.display = '';
                    } else if (tbody) {
                        const tr = document.createElement('tr');
                        tr.id = 'emptyReferrersRow';
                        tr.className = 'odd:bg-slate-50/40';
                        tr.innerHTML = '<td role="cell" class="px-3 py-6 align-middle text-xs text-center text-slate-500 bg-slate-50/50" colspan="5" style="min-width: 10rem;">Click "Add Item" to add a referrer mapping.</td>';
                        tbody.appendChild(tr);
                    }
                    if (selectAll) {
                        selectAll.checked = false;
                        selectAll.setAttribute('disabled', 'disabled');
                        selectAll.classList.add('cursor-not-allowed', 'opacity-50');
                    }
                    if (selectAllLabel) {
                        selectAllLabel.classList.add('cursor-not-allowed');
                        selectAllLabel.classList.remove('cursor-pointer');
                    }
                } else {
                    if (emptyRow) emptyRow.style.display = 'none';
                    if (selectAll) {
                        selectAll.removeAttribute('disabled');
                        selectAll.classList.remove('cursor-not-allowed', 'opacity-50');
                    }
                    if (selectAllLabel) {
                        selectAllLabel.classList.remove('cursor-not-allowed');
                        selectAllLabel.classList.add('cursor-pointer');
                    }
                }
            }

            function createReferrerRow(id, referrerName, uniformName) {
                const tbody = document.getElementById('codeMappingReferrersTbody');
                if (!tbody) return;

                const emptyRow = document.getElementById('emptyReferrersRow');
                if (emptyRow) emptyRow.style.display = 'none';

                const tr = document.createElement('tr');
                tr.className = 'referrer-row border-b border-slate-100 hover:bg-slate-50 transition-colors odd:bg-slate-50/40';
                tr.setAttribute('data-id', id);
                tr.setAttribute('data-referrer-name', referrerName.toLowerCase());
                tr.setAttribute('data-uniform-name', uniformName.toLowerCase());

                tr.innerHTML = `
                    <td role="cell" class="px-3 py-2 align-middle text-xs border-r border-slate-100 text-center" style="min-width: 0.1%;">
                        <label class="font-semibold items-center inline-flex text-xs cursor-pointer">
                            <input type="checkbox" class="referrer-row-cb rounded border-slate-300 text-[#00bfa5] w-4 h-4 cursor-pointer" value="${id}">
                        </label>
                    </td>
                    <td role="cell" class="px-3 py-2 align-middle text-xs font-semibold text-slate-700 border-r border-slate-100 referrer-cell-id" style="min-width: 0.1%;">
                        ${id}
                    </td>
                    <td role="cell" class="px-4 py-2 align-middle text-xs font-medium text-slate-800 border-r border-slate-100 referrer-cell-name" style="min-width: 10rem;">
                        ${referrerName}
                    </td>
                    <td role="cell" class="px-4 py-2 align-middle text-xs text-slate-700 border-r border-slate-100 referrer-cell-uniform" style="min-width: 10rem;">
                        ${uniformName}
                    </td>
                    <td role="cell" class="px-3 py-2 align-middle text-xs text-center border-r border-slate-100" style="min-width: 0.1%;">
                        <div class="relative inline-block referrer-action-container">
                            <button type="button" class="referrer-action-btn text-[#00bfa5] hover:text-[#00a892] p-1 rounded focus:outline-none transition-colors cursor-pointer" title="Actions">
                                <i data-lucide="more-horizontal" class="w-5 h-5"></i>
                            </button>
                            <div class="referrer-dropdown-menu hidden absolute right-0 mt-1 w-40 bg-white border border-slate-200 rounded shadow-lg z-30 py-1 text-left">
                                <button type="button" class="edit-referrer-btn w-full text-xs font-semibold flex items-center px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-[#00bfa5] transition-colors cursor-pointer">
                                    <i data-lucide="edit-3" class="w-4 h-4 mr-2.5 text-[#00bfa5]"></i>
                                    <span>Edit Referrer</span>
                                </button>
                                <button type="button" class="delete-referrer-btn w-full text-xs font-semibold flex items-center px-4 py-2.5 text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                    <i data-lucide="trash-2" class="w-4 h-4 mr-2.5 text-rose-500"></i>
                                    <span>Delete Referrer</span>
                                </button>
                            </div>
                        </div>
                    </td>
                `;

                tr.querySelector('.referrer-action-btn').addEventListener('click', function (e) {
                    e.stopPropagation();
                    const menu = tr.querySelector('.referrer-dropdown-menu');
                    document.querySelectorAll('.referrer-dropdown-menu').forEach(m => {
                        if (m !== menu) m.classList.add('hidden');
                    });
                    menu.classList.toggle('hidden');
                });

                tr.querySelector('.edit-referrer-btn').addEventListener('click', function (e) {
                    e.stopPropagation();
                    document.querySelectorAll('.referrer-dropdown-menu').forEach(m => m.classList.add('hidden'));
                    openSetReferrerUniformNameModal(tr);
                });

                tr.querySelector('.delete-referrer-btn').addEventListener('click', function (e) {
                    e.stopPropagation();
                    tr.remove();
                    checkEmptyReferrersTable();
                });

                tbody.prepend(tr);
                checkEmptyReferrersTable();
                if (window.lucide) window.lucide.createIcons();
            }

            document.querySelectorAll('.edit-referrer-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const row = this.closest('.referrer-row');
                    document.querySelectorAll('.referrer-dropdown-menu').forEach(m => m.classList.add('hidden'));
                    openSetReferrerUniformNameModal(row);
                });
            });

            document.querySelectorAll('.delete-referrer-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const row = this.closest('.referrer-row');
                    if (row) {
                        row.remove();
                        checkEmptyReferrersTable();
                    }
                });
            });

            if (setReferrerUniformNameForm) {
                setReferrerUniformNameForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const refName = (referrerNameInput && referrerNameInput.value.trim()) ? referrerNameInput.value.trim() : 'Patient Referral';
                    const uniformVal = (referrerUniformNameInput && referrerUniformNameInput.value.trim()) ? referrerUniformNameInput.value.trim() : 'Patient Referral';

                    if (editingReferrerRow) {
                        editingReferrerRow.setAttribute('data-referrer-name', refName.toLowerCase());
                        editingReferrerRow.setAttribute('data-uniform-name', uniformVal.toLowerCase());
                        const nameCell = editingReferrerRow.querySelector('.referrer-cell-name');
                        const uniformCell = editingReferrerRow.querySelector('.referrer-cell-uniform');
                        if (nameCell) nameCell.textContent = refName;
                        if (uniformCell) uniformCell.textContent = uniformVal;
                    } else {
                        // Check if multiple checkboxes are selected
                        const checkedRows = Array.from(document.querySelectorAll('.referrer-row-cb:checked')).map(cb => cb.closest('.referrer-row')).filter(Boolean);
                        if (checkedRows.length > 0) {
                            checkedRows.forEach(row => {
                                row.setAttribute('data-uniform-name', uniformVal.toLowerCase());
                                const uniformCell = row.querySelector('.referrer-cell-uniform');
                                if (uniformCell) uniformCell.textContent = uniformVal;
                            });
                        } else {
                            const newId = Math.floor(100 + Math.random() * 900);
                            createReferrerRow(newId, refName, uniformVal);
                        }
                    }

                    closeSetReferrerUniformNameModal();
                });
            }

            // Providers Code Mapping Search Filtering
            const codeMappingProvidersSearchInput = document.getElementById('codeMappingProvidersSearchInput');
            if (codeMappingProvidersSearchInput) {
                codeMappingProvidersSearchInput.addEventListener('input', function () {
                    const query = this.value.trim().toLowerCase();
                    const rows = document.querySelectorAll('.provider-code-mapping-row');
                    let visibleCount = 0;
                    rows.forEach(row => {
                        const id = row.getAttribute('data-id') || '';
                        const providerName = row.getAttribute('data-provider-name') || '';
                        const location = row.getAttribute('data-location') || '';
                        const uniformName = row.getAttribute('data-uniform-name') || '';

                        if (!query || id.includes(query) || providerName.includes(query) || location.includes(query) || uniformName.includes(query)) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    const emptyRow = document.getElementById('emptyProvidersCodeMappingRow');
                    if (emptyRow) {
                        if (rows.length === 0) {
                            emptyRow.style.display = '';
                        } else if (visibleCount === 0) {
                            emptyRow.style.display = '';
                        } else {
                            emptyRow.style.display = 'none';
                        }
                    }
                });
            }

            // Providers Code Mapping Select All Checkbox
            const providerSelectAllCb = document.getElementById('providerSelectAllCb');
            const providerSelectAllLabel = document.getElementById('providerSelectAllLabel');
            if (providerSelectAllCb) {
                providerSelectAllCb.addEventListener('change', function () {
                    const cbs = document.querySelectorAll('.provider-code-mapping-row-cb');
                    cbs.forEach(cb => {
                        const row = cb.closest('.provider-code-mapping-row');
                        if (!row || row.style.display !== 'none') {
                            cb.checked = providerSelectAllCb.checked;
                        }
                    });
                });
            }

            // Provider Code Mapping Action Dropdowns
            document.querySelectorAll('.provider-code-mapping-action-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const menu = this.closest('.provider-code-mapping-action-container').querySelector('.provider-code-mapping-dropdown-menu');
                    document.querySelectorAll('.provider-code-mapping-dropdown-menu').forEach(m => {
                        if (m !== menu) m.classList.add('hidden');
                    });
                    menu.classList.toggle('hidden');
                });
            });

            document.addEventListener('click', function () {
                document.querySelectorAll('.provider-code-mapping-dropdown-menu').forEach(m => m.classList.add('hidden'));
            });

            // Set Provider Code Mapping Uniform Name Modal Handlers
            const setProviderCodeMappingUniformNameModal = document.getElementById('setProviderCodeMappingUniformNameModal');
            const closeSetProviderCodeMappingModalBtn = document.getElementById('closeSetProviderCodeMappingModalBtn');
            const cancelSetProviderCodeMappingBtn = document.getElementById('cancelSetProviderCodeMappingBtn');
            const setProviderCodeMappingUniformNameForm = document.getElementById('setProviderCodeMappingUniformNameForm');
            const providerCodeMappingNameInput = document.getElementById('providerCodeMappingNameInput');
            const providerCodeMappingLocationInput = document.getElementById('providerCodeMappingLocationInput');
            const providerCodeMappingUniformNameInput = document.getElementById('providerCodeMappingUniformNameInput');
            const openAddProviderItemBtn = document.getElementById('openAddProviderItemBtn');
            let editingProviderCodeMappingRow = null;

            function openSetProviderCodeMappingModal(row = null) {
                if (!setProviderCodeMappingUniformNameModal) return;
                editingProviderCodeMappingRow = row;
                if (row) {
                    const currentName = row.querySelector('.provider-code-mapping-cell-name') ? row.querySelector('.provider-code-mapping-cell-name').textContent.trim() : '';
                    const currentLocation = row.querySelector('.provider-code-mapping-cell-location') ? row.querySelector('.provider-code-mapping-cell-location').textContent.trim() : '8 Mile';
                    const currentUniform = row.querySelector('.provider-code-mapping-cell-uniform') ? row.querySelector('.provider-code-mapping-cell-uniform').textContent.trim() : '';
                    if (providerCodeMappingNameInput) providerCodeMappingNameInput.value = currentName;
                    if (providerCodeMappingLocationInput) providerCodeMappingLocationInput.value = currentLocation;
                    if (providerCodeMappingUniformNameInput) providerCodeMappingUniformNameInput.value = currentUniform;
                } else {
                    if (providerCodeMappingNameInput) providerCodeMappingNameInput.value = '';
                    if (providerCodeMappingLocationInput) providerCodeMappingLocationInput.value = '8 Mile';
                    if (providerCodeMappingUniformNameInput) providerCodeMappingUniformNameInput.value = '';
                }
                setProviderCodeMappingUniformNameModal.classList.remove('hidden');
                if (window.lucide) window.lucide.createIcons();
            }

            function closeSetProviderCodeMappingModal() {
                if (setProviderCodeMappingUniformNameModal) {
                    setProviderCodeMappingUniformNameModal.classList.add('hidden');
                    if (setProviderCodeMappingUniformNameForm) setProviderCodeMappingUniformNameForm.reset();
                    editingProviderCodeMappingRow = null;
                }
            }

            if (closeSetProviderCodeMappingModalBtn) closeSetProviderCodeMappingModalBtn.addEventListener('click', closeSetProviderCodeMappingModal);
            if (cancelSetProviderCodeMappingBtn) cancelSetProviderCodeMappingBtn.addEventListener('click', closeSetProviderCodeMappingModal);

            // Code Mapping Providers View Switching (List View vs Add Item View)
            const codeMappingProvidersListView = document.getElementById('codeMappingProvidersListView');
            const codeMappingProviderAddItemView = document.getElementById('codeMappingProviderAddItemView');
            const codeMappingSubmitProviderAddBtn = document.getElementById('codeMappingSubmitProviderAddBtn');
            const addProviderUniformNameInput = document.getElementById('addProviderUniformNameInput');
            const addProviderSelectAllCb = document.getElementById('addProviderSelectAllCb');
            const addProviderSearchInput = document.getElementById('addProviderSearchInput');
            const addProviderLocationFilter = document.getElementById('addProviderLocationFilter');
            const addProviderProductionFilter = document.getElementById('addProviderProductionFilter');

            function showProviderAddView(pushState = true) {
                if (codeMappingProvidersListView && codeMappingProviderAddItemView) {
                    codeMappingProvidersListView.classList.add('hidden');
                    codeMappingProviderAddItemView.classList.remove('hidden');
                    if (pushState && window.history.pushState) {
                        window.history.pushState({ tab: 'code-mapping', subtab: 'providers', action: 'add' }, '', '/configuration/code-mapping/providers/add');
                    }
                    if (window.lucide) window.lucide.createIcons();
                }
            }

            function hideProviderAddView(pushState = true) {
                if (codeMappingProvidersListView && codeMappingProviderAddItemView) {
                    codeMappingProviderAddItemView.classList.add('hidden');
                    codeMappingProvidersListView.classList.remove('hidden');
                    if (pushState && window.history.pushState) {
                        window.history.pushState({ tab: 'code-mapping', subtab: 'providers' }, '', '/configuration/code-mapping/providers');
                    }
                    if (addProviderUniformNameInput) addProviderUniformNameInput.value = '';
                    if (addProviderSelectAllCb) addProviderSelectAllCb.checked = false;
                    document.querySelectorAll('.add-provider-item-cb').forEach(cb => cb.checked = false);
                    updateProviderAddButtonState();
                    if (window.lucide) window.lucide.createIcons();
                }
            }

            if (openAddProviderItemBtn) {
                openAddProviderItemBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    showProviderAddView();
                });
            }

            document.querySelectorAll('.provider-back-link, .provider-cancel-btn').forEach(el => {
                el.addEventListener('click', function (e) {
                    e.preventDefault();
                    hideProviderAddView();
                });
            });

            function updateProviderAddButtonState() {
                if (!codeMappingSubmitProviderAddBtn) return;
                const uniformVal = addProviderUniformNameInput ? addProviderUniformNameInput.value.trim() : '';
                const checkedBoxes = document.querySelectorAll('.add-provider-item-cb:checked');

                const isValid = uniformVal.length > 0 || checkedBoxes.length > 0;
                if (isValid) {
                    codeMappingSubmitProviderAddBtn.removeAttribute('disabled');
                    codeMappingSubmitProviderAddBtn.classList.remove('opacity-50', 'pointer-events-none');
                } else {
                    codeMappingSubmitProviderAddBtn.setAttribute('disabled', 'disabled');
                    codeMappingSubmitProviderAddBtn.classList.add('opacity-50', 'pointer-events-none');
                }
            }

            if (addProviderUniformNameInput) addProviderUniformNameInput.addEventListener('input', updateProviderAddButtonState);

            // Select all in Available Providers Table
            if (addProviderSelectAllCb) {
                addProviderSelectAllCb.addEventListener('change', function () {
                    const cbs = document.querySelectorAll('.add-provider-item-cb');
                    cbs.forEach(cb => {
                        const row = cb.closest('.add-provider-row');
                        if (!row || row.style.display !== 'none') {
                            cb.checked = addProviderSelectAllCb.checked;
                        }
                    });
                    updateProviderAddButtonState();
                });
            }

            document.querySelectorAll('.add-provider-item-cb').forEach(cb => {
                cb.addEventListener('change', updateProviderAddButtonState);
            });

            // Filter available providers in Add Provider View
            function filterAddProviders() {
                const query = addProviderSearchInput ? addProviderSearchInput.value.trim().toLowerCase() : '';
                const locVal = addProviderLocationFilter ? addProviderLocationFilter.value.trim().toLowerCase() : '';
                const rows = document.querySelectorAll('.add-provider-row');
                rows.forEach(row => {
                    const id = row.getAttribute('data-id') || '';
                    const name = row.getAttribute('data-name') || '';
                    const loc = row.getAttribute('data-location') || '';

                    const matchesQuery = !query || id.includes(query) || name.includes(query);
                    const matchesLoc = !locVal || loc === locVal;

                    if (matchesQuery && matchesLoc) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            if (addProviderSearchInput) addProviderSearchInput.addEventListener('input', filterAddProviders);
            if (addProviderLocationFilter) addProviderLocationFilter.addEventListener('change', filterAddProviders);

            // Submit Add Provider Item
            if (codeMappingSubmitProviderAddBtn) {
                codeMappingSubmitProviderAddBtn.addEventListener('click', function () {
                    const uniformName = (addProviderUniformNameInput && addProviderUniformNameInput.value.trim()) ? addProviderUniformNameInput.value.trim() : 'General Dentist';
                    const checkedCbs = Array.from(document.querySelectorAll('.add-provider-item-cb:checked'));

                    if (checkedCbs.length === 0) {
                        const newId = Math.floor(100 + Math.random() * 900);
                        const provName = uniformName + ' - ' + newId;
                        const location = '8 Mile';
                        createProviderCodeMappingRow(newId, provName, location, uniformName);
                    } else {
                        checkedCbs.forEach(cb => {
                            const provId = cb.getAttribute('data-id');
                            const provName = cb.getAttribute('data-name');
                            const provLocation = cb.getAttribute('data-location') || '8 Mile';
                            createProviderCodeMappingRow(provId, provName, provLocation, uniformName);
                        });
                    }

                    hideProviderAddView();
                });
            }

            function checkEmptyProvidersCodeMappingTable() {
                const tbody = document.getElementById('codeMappingProvidersTbody');
                const rows = tbody ? tbody.querySelectorAll('.provider-code-mapping-row') : [];
                let emptyRow = document.getElementById('emptyProvidersCodeMappingRow');
                const selectAll = document.getElementById('providerSelectAllCb');
                const selectAllLabel = document.getElementById('providerSelectAllLabel');

                if (rows.length === 0) {
                    if (emptyRow) {
                        emptyRow.style.display = '';
                    } else if (tbody) {
                        const tr = document.createElement('tr');
                        tr.id = 'emptyProvidersCodeMappingRow';
                        tr.className = 'odd:bg-slate-50/40';
                        tr.innerHTML = '<td role="cell" class="px-3 py-6 align-middle text-xs text-center text-slate-500 bg-slate-50/50" colspan="6" style="min-width: 10rem;">Click "Add Item" to add a provider mapping.</td>';
                        tbody.appendChild(tr);
                    }
                    if (selectAll) {
                        selectAll.checked = false;
                        selectAll.setAttribute('disabled', 'disabled');
                        selectAll.classList.add('cursor-not-allowed', 'opacity-50');
                    }
                    if (selectAllLabel) {
                        selectAllLabel.classList.add('cursor-not-allowed');
                        selectAllLabel.classList.remove('cursor-pointer');
                    }
                } else {
                    if (emptyRow) emptyRow.style.display = 'none';
                    if (selectAll) {
                        selectAll.removeAttribute('disabled');
                        selectAll.classList.remove('cursor-not-allowed', 'opacity-50');
                    }
                    if (selectAllLabel) {
                        selectAllLabel.classList.remove('cursor-not-allowed');
                        selectAllLabel.classList.add('cursor-pointer');
                    }
                }
            }

            function createProviderCodeMappingRow(id, name, location, uniformName) {
                const tbody = document.getElementById('codeMappingProvidersTbody');
                if (!tbody) return;

                const emptyRow = document.getElementById('emptyProvidersCodeMappingRow');
                if (emptyRow) emptyRow.style.display = 'none';

                const tr = document.createElement('tr');
                tr.className = 'provider-code-mapping-row border-b border-slate-100 hover:bg-slate-50 transition-colors odd:bg-slate-50/40';
                tr.setAttribute('data-id', id);
                tr.setAttribute('data-provider-name', name.toLowerCase());
                tr.setAttribute('data-location', location.toLowerCase());
                tr.setAttribute('data-uniform-name', uniformName.toLowerCase());

                tr.innerHTML = `
                    <td role="cell" class="px-3 py-2 align-middle text-xs border-r border-slate-100 text-center" style="min-width: 0.1%;">
                        <label class="font-semibold items-center inline-flex text-xs cursor-pointer">
                            <input type="checkbox" class="provider-code-mapping-row-cb rounded border-slate-300 text-[#00bfa5] w-4 h-4 cursor-pointer" value="${id}">
                        </label>
                    </td>
                    <td role="cell" class="px-3 py-2 align-middle text-xs font-semibold text-slate-700 border-r border-slate-100 provider-code-mapping-cell-id" style="min-width: 0.1%;">
                        ${id}
                    </td>
                    <td role="cell" class="px-4 py-2 align-middle text-xs font-medium text-slate-800 border-r border-slate-100 provider-code-mapping-cell-name" style="min-width: 12rem;">
                        ${name}
                    </td>
                    <td role="cell" class="px-4 py-2 align-middle text-xs text-slate-700 border-r border-slate-100 provider-code-mapping-cell-location" style="min-width: 10rem;">
                        ${location}
                    </td>
                    <td role="cell" class="px-4 py-2 align-middle text-xs text-slate-700 border-r border-slate-100 provider-code-mapping-cell-uniform" style="min-width: 10rem;">
                        ${uniformName}
                    </td>
                    <td role="cell" class="px-3 py-2 align-middle text-xs text-center border-r border-slate-100" style="min-width: 0.1%;">
                        <div class="relative inline-block provider-code-mapping-action-container">
                            <button type="button" class="provider-code-mapping-action-btn text-[#00bfa5] hover:text-[#00a892] p-1 rounded focus:outline-none transition-colors cursor-pointer" title="Actions">
                                <i data-lucide="more-horizontal" class="w-5 h-5"></i>
                            </button>
                            <div class="provider-code-mapping-dropdown-menu hidden absolute right-0 mt-1 w-40 bg-white border border-slate-200 rounded shadow-lg z-30 py-1 text-left">
                                <button type="button" class="edit-provider-code-mapping-btn w-full text-xs font-semibold flex items-center px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-[#00bfa5] transition-colors cursor-pointer">
                                    <i data-lucide="edit-3" class="w-4 h-4 mr-2.5 text-[#00bfa5]"></i>
                                    <span>Edit Provider</span>
                                </button>
                                <button type="button" class="delete-provider-code-mapping-btn w-full text-xs font-semibold flex items-center px-4 py-2.5 text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                    <i data-lucide="trash-2" class="w-4 h-4 mr-2.5 text-rose-500"></i>
                                    <span>Delete Provider</span>
                                </button>
                            </div>
                        </div>
                    </td>
                `;

                tr.querySelector('.provider-code-mapping-action-btn').addEventListener('click', function (e) {
                    e.stopPropagation();
                    const menu = tr.querySelector('.provider-code-mapping-dropdown-menu');
                    document.querySelectorAll('.provider-code-mapping-dropdown-menu').forEach(m => {
                        if (m !== menu) m.classList.add('hidden');
                    });
                    menu.classList.toggle('hidden');
                });

                tr.querySelector('.edit-provider-code-mapping-btn').addEventListener('click', function (e) {
                    e.stopPropagation();
                    document.querySelectorAll('.provider-code-mapping-dropdown-menu').forEach(m => m.classList.add('hidden'));
                    openSetProviderCodeMappingModal(tr);
                });

                tr.querySelector('.delete-provider-code-mapping-btn').addEventListener('click', function (e) {
                    e.stopPropagation();
                    tr.remove();
                    checkEmptyProvidersCodeMappingTable();
                });

                tbody.prepend(tr);
                checkEmptyProvidersCodeMappingTable();
                if (window.lucide) window.lucide.createIcons();
            }

            document.querySelectorAll('.edit-provider-code-mapping-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const row = this.closest('.provider-code-mapping-row');
                    document.querySelectorAll('.provider-code-mapping-dropdown-menu').forEach(m => m.classList.add('hidden'));
                    openSetProviderCodeMappingModal(row);
                });
            });

            document.querySelectorAll('.delete-provider-code-mapping-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const row = this.closest('.provider-code-mapping-row');
                    if (row) {
                        row.remove();
                        checkEmptyProvidersCodeMappingTable();
                    }
                });
            });

            if (setProviderCodeMappingUniformNameForm) {
                setProviderCodeMappingUniformNameForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const provName = (providerCodeMappingNameInput && providerCodeMappingNameInput.value.trim()) ? providerCodeMappingNameInput.value.trim() : 'Dr. Jane Smith';
                    const location = (providerCodeMappingLocationInput && providerCodeMappingLocationInput.value) ? providerCodeMappingLocationInput.value : '8 Mile';
                    const uniformVal = (providerCodeMappingUniformNameInput && providerCodeMappingUniformNameInput.value.trim()) ? providerCodeMappingUniformNameInput.value.trim() : 'General Dentist';

                    if (editingProviderCodeMappingRow) {
                        editingProviderCodeMappingRow.setAttribute('data-provider-name', provName.toLowerCase());
                        editingProviderCodeMappingRow.setAttribute('data-location', location.toLowerCase());
                        editingProviderCodeMappingRow.setAttribute('data-uniform-name', uniformVal.toLowerCase());
                        const nameCell = editingProviderCodeMappingRow.querySelector('.provider-code-mapping-cell-name');
                        const locationCell = editingProviderCodeMappingRow.querySelector('.provider-code-mapping-cell-location');
                        const uniformCell = editingProviderCodeMappingRow.querySelector('.provider-code-mapping-cell-uniform');
                        if (nameCell) nameCell.textContent = provName;
                        if (locationCell) locationCell.textContent = location;
                        if (uniformCell) uniformCell.textContent = uniformVal;
                    } else {
                        // Check if multiple checkboxes are selected
                        const checkedRows = Array.from(document.querySelectorAll('.provider-code-mapping-row-cb:checked')).map(cb => cb.closest('.provider-code-mapping-row')).filter(Boolean);
                        if (checkedRows.length > 0) {
                            checkedRows.forEach(row => {
                                row.setAttribute('data-uniform-name', uniformVal.toLowerCase());
                                const uniformCell = row.querySelector('.provider-code-mapping-cell-uniform');
                                if (uniformCell) uniformCell.textContent = uniformVal;
                            });
                        } else {
                            const newId = Math.floor(100 + Math.random() * 900);
                            createProviderCodeMappingRow(newId, provName, location, uniformVal);
                        }
                    }

                    closeSetProviderCodeMappingModal();
                });
            }

            // KPI Location dropdown toggle & selection
            const kpisLocationBtn = document.getElementById('kpisLocationBtn');
            const kpisLocationDropdown = document.getElementById('kpisLocationDropdown');
            const kpisLocationSelectedText = document.getElementById('kpisLocationSelectedText');

            if (kpisLocationBtn && kpisLocationDropdown) {
                kpisLocationBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    kpisLocationDropdown.classList.toggle('hidden');
                    const catDropdown = document.getElementById('kpisCategoryDropdown');
                    if (catDropdown) catDropdown.classList.add('hidden');
                });

                document.querySelectorAll('.kpi-location-item').forEach(item => {
                    item.addEventListener('click', function (e) {
                        e.stopPropagation();
                        const locName = this.getAttribute('data-location') || '8 Mile';
                        if (kpisLocationSelectedText) kpisLocationSelectedText.textContent = locName;
                        document.querySelectorAll('.kpi-location-item').forEach(i => {
                            i.classList.remove('bg-[#00bfa5]/10', 'font-bold', 'text-[#00bfa5]');
                        });
                        this.classList.add('bg-[#00bfa5]/10', 'font-bold', 'text-[#00bfa5]');
                        kpisLocationDropdown.classList.add('hidden');
                    });
                });
            }

            // KPI Category dropdown toggle & selection
            const kpisCategoryBtn = document.getElementById('kpisCategoryBtn');
            const kpisCategoryDropdown = document.getElementById('kpisCategoryDropdown');

            if (kpisCategoryBtn && kpisCategoryDropdown) {
                kpisCategoryBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    kpisCategoryDropdown.classList.toggle('hidden');
                    if (kpisLocationDropdown) kpisLocationDropdown.classList.add('hidden');
                });

                document.querySelectorAll('.kpi-category-item').forEach(item => {
                    item.addEventListener('click', function (e) {
                        e.stopPropagation();
                        const cat = this.getAttribute('data-category');
                        const activeSubtabBtn = document.querySelector('.kpi-subtab-btn[aria-selected="true"]');
                        const currentSubtab = activeSubtabBtn ? activeSubtabBtn.getAttribute('data-kpi-subtab') : 'main';
                        switchKpisSubtab(currentSubtab, cat, true);
                        kpisCategoryDropdown.classList.add('hidden');
                    });
                });
            }

            // Custom KPI Select Filters (Line of Business, Transaction Type, KPI Type)
            const kpisCustomLobSelect = document.getElementById('kpisCustomLobSelect');
            const kpisCustomTxTypeSelect = document.getElementById('kpisCustomTxTypeSelect');
            const kpisCustomKpiTypeSelect = document.getElementById('kpisCustomKpiTypeSelect');

            if (kpisCustomLobSelect) {
                kpisCustomLobSelect.addEventListener('change', function () {
                    filterKpisTable('custom');
                });
            }
            if (kpisCustomTxTypeSelect) {
                kpisCustomTxTypeSelect.addEventListener('change', function () {
                    filterKpisTable('custom');
                });
            }
            if (kpisCustomKpiTypeSelect) {
                kpisCustomKpiTypeSelect.addEventListener('change', function () {
                    filterKpisTable('custom');
                });
            }

            // Helper to escape HTML characters
            function escapeHtml(str) {
                if (!str) return '';
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            // Add Custom KPI View Navigation & Form Submission
            const openAddKpiBtn = document.getElementById('openAddKpiBtn');
            if (openAddKpiBtn) {
                openAddKpiBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    switchKpisSubtab('custom', 'create', true);
                });
            }

            const backToCustomKpisBtn = document.getElementById('backToCustomKpisBtn');
            if (backToCustomKpisBtn) {
                backToCustomKpisBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    switchKpisSubtab('custom', 'index', true);
                });
            }

            const addCustomKpiForm = document.getElementById('addCustomKpiForm');
            const customKpiSuccessMsg = document.getElementById('customKpiSuccessMsg');
            if (addCustomKpiForm) {
                addCustomKpiForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const nameInput = document.getElementById('name');
                    const nameVal = nameInput ? nameInput.value.trim() : '';
                    const txInput = document.getElementById('transaction_type');
                    const txVal = txInput ? txInput.value : '';
                    const kpiTypeInput = document.getElementById('kpi_type');
                    const kpiTypeVal = kpiTypeInput ? kpiTypeInput.value : 'Hygiene';
                    const displayInput = document.getElementById('display');
                    const displayVal = displayInput ? displayInput.value : 'Count (#)';
                    const lobInput = document.getElementById('lob');
                    const lobVal = lobInput ? lobInput.value : '';
                    const descInput = document.getElementById('description');
                    const descVal = descInput ? descInput.value.trim() : '';

                    if (!nameVal) {
                        if (nameInput) nameInput.focus();
                        return;
                    }

                    // Create new row in tbody
                    const tbody = document.getElementById('kpisTableTbody');
                    if (tbody) {
                        const newTr = document.createElement('tr');
                        newTr.setAttribute('role', 'row');
                        newTr.className = 'kpi-row cat-custom subtab-custom odd:bg-gray-000 dark:odd:bg-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-750 transition-colors';
                        const newId = Date.now();
                        newTr.setAttribute('data-kpi-id', newId);
                        newTr.setAttribute('data-subtab-group', 'custom');
                        newTr.setAttribute('data-category', 'custom');
                        newTr.setAttribute('data-kpi-name', nameVal.toLowerCase());
                        newTr.setAttribute('data-transaction', txVal.toLowerCase());
                        newTr.setAttribute('data-display', kpiTypeVal.toLowerCase());
                        newTr.setAttribute('data-line-of-business', lobVal.toLowerCase());
                        newTr.setAttribute('data-kpi-desc', descVal.toLowerCase());

                        const safeName = escapeHtml(nameVal);
                        const safeTx = escapeHtml(txVal);
                        const safeDisplay = escapeHtml(displayVal);
                        const safeDesc = escapeHtml(descVal);

                        newTr.innerHTML = `
                            <td role="cell" class="px-3 py-2 align-middle text-xs font-semibold dark:text-white border-white dark:border-gray-800 border kpi-cell-name">
                                ${safeName}
                            </td>
                            <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border kpi-cell-transaction">
                                ${safeTx}
                            </td>
                            <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border kpi-cell-display">
                                ${safeDisplay}
                            </td>
                            <td role="cell" class="px-3 py-2 align-middle overflow-ellipsis overflow-hidden text-xs dark:text-white border-white dark:border-gray-800 border kpi-cell-description" style="min-width: 10rem;">
                                ${safeDesc}
                            </td>
                            <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border text-center" style="min-width: 1%;">
                                <div class="relative lels text-center kpi-action-container">
                                    <span class="flex justify-center">
                                        <button type="button" class="text-ja-green-200 focus:outline-none kpi-action-menu-btn" title="Actions">
                                            <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle inline-block w-6 h-6">
                                                <path fill="currentColor" d="M2 10h4v4H2zM10 10h4v4h-4zM18 10h4v4h-4z"></path>
                                            </svg>
                                        </button>
                                    </span>
                                    <div class="bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-800 dark:text-white z-50 bg-pointer mt-3 hidden absolute right-0 kpi-dropdown-menu rounded-sm text-left">
                                        <button type="button" class="kpi-edit-btn flex items-center px-4 py-3 text-xs font-semibold focus:outline-none focus:bg-gray-000 hover:bg-gray-100 dark:hover:bg-gray-700 dark:bg-gray-800 w-full" style="outline: none;">
                                            <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle mr-1 inline-block w-5 h-5 text-ja-green-200">
                                                <path d="M10.5 4H1v19h19v-9.5" stroke="currentColor" stroke-width="2"></path>
                                                <path d="M19 1.52l3.561 3.562-11.574 11.574-5.045 1.484 1.484-5.046L19 1.52zM15.977 4.543l3.562 3.562" stroke="currentColor" stroke-width="2" stroke-linejoin="round"></path>
                                            </svg>
                                            Edit KPI
                                        </button>
                                        <button type="button" class="kpi-delete-btn flex items-center px-4 py-3 text-xs font-semibold text-rose-600 focus:outline-none hover:bg-rose-50 dark:hover:bg-rose-900/30 w-full">
                                            <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle mr-1 inline-block w-5 h-5 text-rose-500">
                                                <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                            Delete KPI
                                        </button>
                                    </div>
                                </div>
                            </td>
                        `;
                        tbody.appendChild(newTr);

                        // Attach listeners to new elements
                        const menuBtn = newTr.querySelector('.kpi-action-menu-btn');
                        const menu = newTr.querySelector('.kpi-dropdown-menu');
                        if (menuBtn && menu) {
                            menuBtn.addEventListener('click', function (e) {
                                e.stopPropagation();
                                const isHidden = menu.classList.contains('hidden');
                                document.querySelectorAll('.kpi-dropdown-menu').forEach(m => m.classList.add('hidden'));
                                if (isHidden) menu.classList.remove('hidden');
                            });
                        }
                        const delBtn = newTr.querySelector('.kpi-delete-btn');
                        if (delBtn) {
                            delBtn.addEventListener('click', function (e) {
                                e.stopPropagation();
                                newTr.remove();
                            });
                        }
                        const editBtn = newTr.querySelector('.kpi-edit-btn');
                        if (editBtn) {
                            editBtn.addEventListener('click', function (e) {
                                e.stopPropagation();
                                document.querySelectorAll('.kpi-dropdown-menu').forEach(m => m.classList.add('hidden'));
                                openEditKpiModal(newTr);
                            });
                        }
                    }

                    if (customKpiSuccessMsg) {
                        customKpiSuccessMsg.style.display = 'block';
                    }

                    setTimeout(() => {
                        if (customKpiSuccessMsg) {
                            customKpiSuccessMsg.style.display = 'none';
                        }
                        addCustomKpiForm.reset();
                        switchKpisSubtab('custom', 'index', true);
                    }, 800);
                });
            }

            // Delete KPI button (e.g. for Custom KPIs)
            document.querySelectorAll('.kpi-delete-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const row = this.closest('.kpi-row');
                    if (row) {
                        row.remove();
                    }
                });
            });

            // Close KPI, RCM, and Snapshot dropdowns on document click
            document.addEventListener('click', function () {
                const kpisLocDropdown = document.getElementById('kpisLocationDropdown');
                if (kpisLocDropdown) kpisLocDropdown.classList.add('hidden');

                const kpisCatDropdown = document.getElementById('kpisCategoryDropdown');
                if (kpisCatDropdown) kpisCatDropdown.classList.add('hidden');

                const rcmLocDropdown = document.getElementById('rcmLocationDropdown');
                if (rcmLocDropdown) rcmLocDropdown.classList.add('hidden');

                const snapshotCatDropdown = document.getElementById('snapshotCategoryDropdown');
                if (snapshotCatDropdown) snapshotCatDropdown.classList.add('hidden');

                document.querySelectorAll('.kpi-dropdown-menu').forEach(m => m.classList.add('hidden'));
                document.querySelectorAll('.rcm-dropdown-menu').forEach(m => m.classList.add('hidden'));
                document.querySelectorAll('.snapshot-dropdown-menu').forEach(m => m.classList.add('hidden'));
                document.querySelectorAll('.huddle-dropdown-menu').forEach(m => m.classList.add('hidden'));
                document.querySelectorAll('.eod-dropdown-menu').forEach(m => m.classList.add('hidden'));
            });

            // Live search for KPIs
            const kpisSearchInput = document.getElementById('kpisSearchInput');
            if (kpisSearchInput) {
                kpisSearchInput.addEventListener('input', function () {
                    filterKpisTable();
                });
            }

            // Provider KPI Toggle badge update
            document.querySelectorAll('.kpi-provider-toggle').forEach(input => {
                input.addEventListener('change', function () {
                    const row = this.closest('tr');
                    if (!row) return;
                    const badge = row.querySelector('.kpi-status-badge');
                    if (badge) {
                        if (this.checked) {
                            this.setAttribute('aria-checked', 'true');
                            badge.textContent = 'Enabled';
                            badge.className = 'rounded text-white relative inline-flex items-center leading-none font-semibold px-3 py-2 bg-light-green enable-tag dark:bg-dark-green text-xs h-6 rounded-sm kpi-status-badge';
                        } else {
                            this.removeAttribute('aria-checked');
                            badge.textContent = 'Disabled';
                            badge.className = 'rounded text-white relative inline-flex items-center leading-none font-semibold px-3 py-2 bg-gray-300 text-white dark:bg-gray-900 dark:text-gray-1000 text-xs h-6 rounded-sm kpi-status-badge';
                        }
                    }
                });
            });

            // Goal Reset Buttons
            document.querySelectorAll('.kpi-reset-goal-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const row = this.closest('tr');
                    const goalInput = row ? row.querySelector('.kpi-cell-goal') : null;
                    if (goalInput) {
                        goalInput.value = '0.0';
                    }
                });
            });

            // Actions dropdown menu toggle
            document.querySelectorAll('.kpi-action-menu-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const menu = this.closest('.kpi-action-container').querySelector('.kpi-dropdown-menu');
                    document.querySelectorAll('.kpi-dropdown-menu').forEach(m => {
                        if (m !== menu) m.classList.add('hidden');
                    });
                    if (menu) menu.classList.toggle('hidden');
                });
            });

            // Edit KPI Modal handlers
            const editKpiModal = document.getElementById('editKpiModal');
            const closeEditKpiModalBtn = document.getElementById('closeEditKpiModalBtn');
            const cancelEditKpiBtn = document.getElementById('cancelEditKpiBtn');
            const editKpiForm = document.getElementById('editKpiForm');
            const editKpiIdInput = document.getElementById('editKpiIdInput');
            const editKpiNameInput = document.getElementById('editKpiNameInput');
            const editKpiDescInput = document.getElementById('editKpiDescInput');
            const editKpiGoalInput = document.getElementById('editKpiGoalInput');
            const editKpiEnabledInput = document.getElementById('editKpiEnabledInput');
            const openAddCustomKpiBtn = document.getElementById('openAddCustomKpiBtn');
            let editingKpiRow = null;

            function openEditKpiModal(row = null) {
                if (!editKpiModal) return;
                editingKpiRow = row;
                if (row) {
                    const id = row.getAttribute('data-kpi-id') || '';
                    const nameCell = row.querySelector('.kpi-cell-name');
                    const descCell = row.querySelector('.kpi-cell-description');
                    const goalInput = row.querySelector('.kpi-cell-goal');
                    const toggleInput = row.querySelector('.kpi-enable-toggle');

                    if (editKpiIdInput) editKpiIdInput.value = id;
                    if (editKpiNameInput) editKpiNameInput.value = nameCell ? nameCell.textContent.trim() : '';
                    if (editKpiDescInput) editKpiDescInput.value = descCell ? descCell.textContent.trim() : '';
                    if (editKpiGoalInput) editKpiGoalInput.value = goalInput ? goalInput.value.trim() : '0.0';
                    if (editKpiEnabledInput) editKpiEnabledInput.checked = toggleInput ? toggleInput.checked : true;
                } else {
                    if (editKpiIdInput) editKpiIdInput.value = '';
                    if (editKpiNameInput) editKpiNameInput.value = '';
                    if (editKpiDescInput) editKpiDescInput.value = '';
                    if (editKpiGoalInput) editKpiGoalInput.value = '0.0';
                    if (editKpiEnabledInput) editKpiEnabledInput.checked = true;
                }
                editKpiModal.classList.remove('hidden');
                if (window.lucide) window.lucide.createIcons();
            }

            function closeEditKpiModal() {
                if (editKpiModal) {
                    editKpiModal.classList.add('hidden');
                    if (editKpiForm) editKpiForm.reset();
                    editingKpiRow = null;
                }
            }

            if (closeEditKpiModalBtn) closeEditKpiModalBtn.addEventListener('click', closeEditKpiModal);
            if (cancelEditKpiBtn) cancelEditKpiBtn.addEventListener('click', closeEditKpiModal);

            document.querySelectorAll('.kpi-edit-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const row = this.closest('.kpi-row');
                    document.querySelectorAll('.kpi-dropdown-menu').forEach(m => m.classList.add('hidden'));
                    openEditKpiModal(row);
                });
            });

            if (openAddCustomKpiBtn) {
                openAddCustomKpiBtn.addEventListener('click', function () {
                    openEditKpiModal(null);
                });
            }

            if (editKpiForm) {
                editKpiForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const name = editKpiNameInput ? editKpiNameInput.value.trim() : '';
                    const desc = editKpiDescInput ? editKpiDescInput.value.trim() : '';
                    const goal = editKpiGoalInput ? editKpiGoalInput.value.trim() : '0.0';
                    const enabled = editKpiEnabledInput ? editKpiEnabledInput.checked : true;

                    if (editingKpiRow) {
                        editingKpiRow.setAttribute('data-kpi-name', name.toLowerCase());
                        editingKpiRow.setAttribute('data-kpi-desc', desc.toLowerCase());
                        const nameCell = editingKpiRow.querySelector('.kpi-cell-name');
                        const descCell = editingKpiRow.querySelector('.kpi-cell-description');
                        const goalInput = editingKpiRow.querySelector('.kpi-cell-goal');
                        const toggleInput = editingKpiRow.querySelector('.kpi-enable-toggle');

                        if (nameCell) nameCell.textContent = name;
                        if (descCell) descCell.textContent = desc;
                        if (goalInput) goalInput.value = goal;
                        if (toggleInput) toggleInput.checked = enabled;
                    } else {
                        // Create a new Custom KPI row
                        const tbody = document.getElementById('kpisTableTbody');
                        if (tbody) {
                            const newId = Math.floor(900 + Math.random() * 100);
                            const tr = document.createElement('tr');
                            tr.className = 'kpi-row cat-custom odd:bg-gray-000 dark:odd:bg-gray-800 hover:bg-gray-100/60 dark:hover:bg-gray-750 transition-colors';
                            tr.setAttribute('data-kpi-id', newId);
                            tr.setAttribute('data-subtab-group', 'custom');
                            tr.setAttribute('data-category', 'custom');
                            tr.setAttribute('data-kpi-name', name.toLowerCase());
                            tr.setAttribute('data-kpi-desc', desc.toLowerCase());

                            tr.innerHTML = `
                                <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border text-center" style="min-width: 0.1%;">
                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" class="sr-only dds-toggle-input kpi-enable-toggle" value="" ${enabled ? 'checked' : ''}>
                                        <div class="dds-toggle-track">
                                            <div class="dds-toggle-knob">
                                                <svg class="dds-toggle-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </label>
                                </td>
                                <td role="cell" class="px-3 py-2 align-middle w-3/12 text-xs text-left dark:text-white border-white dark:border-gray-800 border" style="min-width: 0.1%;">
                                    <div class="flex items-center justify-between font-semibold kpi-cell-name">
                                        ${name}
                                    </div>
                                </td>
                                <td role="cell" class="px-3 py-2 align-middle overflow-ellipsis overflow-hidden text-xs dark:text-white border-white dark:border-gray-800 border kpi-cell-description" style="min-width: initial; max-width: initial;">
                                    ${desc}
                                </td>
                                <td role="cell" class="px-3 py-2 align-middle text-center w-2/12 goal-cell-td goal-cell text-xs dark:text-white border-white dark:border-gray-800 border" style="min-width: initial; max-width: initial;">
                                    <div class="flex justify-between space-x-2">
                                        <input type="number" step="any" placeholder="0.0" value="${goal}" debounce-events="keyup" class="w-full text-right border-gray-400 dark:bg-gray-800 rounded-sm p-1 border text-xs dark:text-white focus:outline-none focus:border-ja-green-200 kpi-cell-goal">
                                        <button type="button" class="kpi-reset-goal-btn focus:outline-none hover:opacity-80" title="Reset Goal">
                                            <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle inline-block w-5 h-5 text-ja-green-200">
                                                <path d="M15.363 4.773c2.318 1.267 3.909 3.734 4.392 5.701a8.5 8.5 0 11-16.653.708C3.317 9.815 4.044 8 5.272 6.5" stroke="currentColor" stroke-width="2"></path>
                                                <path d="M14.59 9.74V4h5.74" stroke="currentColor" stroke-width="2"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td role="cell" class="px-3 py-2 align-middle text-xs dark:text-white border-white dark:border-gray-800 border text-center" style="min-width: 1%;">
                                    <div class="relative lels text-center kpi-action-container">
                                        <span class="flex justify-center">
                                            <button type="button" class="text-ja-green-200 focus:outline-none kpi-action-menu-btn" title="Actions">
                                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle inline-block w-6 h-6">
                                                    <path fill="currentColor" d="M2 10h4v4H2zM10 10h4v4h-4zM18 10h4v4h-4z"></path>
                                                </svg>
                                            </button>
                                        </span>
                                        <div class="bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-800 dark:text-white z-50 bg-pointer mt-3 hidden absolute right-0 kpi-dropdown-menu rounded-sm text-left">
                                            <button type="button" class="kpi-edit-btn flex items-center px-4 py-3 text-xs font-semibold focus:outline-none focus:bg-gray-000 hover:bg-gray-100 dark:hover:bg-gray-700 dark:bg-gray-800 w-full" style="outline: none;">
                                                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 align-middle mr-1 inline-block w-5 h-5 text-ja-green-200">
                                                    <path d="M10.5 4H1v19h19v-9.5" stroke="currentColor" stroke-width="2"></path>
                                                    <path d="M19 1.52l3.561 3.562-11.574 11.574-5.045 1.484 1.484-5.046L19 1.52zM15.977 4.543l3.562 3.562" stroke="currentColor" stroke-width="2" stroke-linejoin="round"></path>
                                                </svg>
                                                Edit KPI
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            `;

                            tr.querySelector('.kpi-reset-goal-btn').addEventListener('click', function () {
                                tr.querySelector('.kpi-cell-goal').value = '0.0';
                            });

                            tr.querySelector('.kpi-action-menu-btn').addEventListener('click', function (e) {
                                e.stopPropagation();
                                const m = tr.querySelector('.kpi-dropdown-menu');
                                document.querySelectorAll('.kpi-dropdown-menu').forEach(menu => { if (menu !== m) menu.classList.add('hidden'); });
                                if (m) m.classList.toggle('hidden');
                            });

                            tr.querySelector('.kpi-edit-btn').addEventListener('click', function (e) {
                                e.stopPropagation();
                                document.querySelectorAll('.kpi-dropdown-menu').forEach(m => m.classList.add('hidden'));
                                openEditKpiModal(tr);
                            });

                            tbody.prepend(tr);
                        }
                    }

                    closeEditKpiModal();
                });
            }

            // Export CSV for KPIs
            const kpisExportCsvBtn = document.getElementById('kpisExportCsvBtn');
            if (kpisExportCsvBtn) {
                kpisExportCsvBtn.addEventListener('click', function () {
                    const visibleRows = Array.from(document.querySelectorAll('.kpi-row:not(.hidden)'));
                    let csvContent = 'Enabled,KPI,Description,Goal\n';

                    visibleRows.forEach(row => {
                        const enabled = row.querySelector('.kpi-enable-toggle')?.checked ? 'Yes' : 'No';
                        const name = row.querySelector('.kpi-cell-name')?.textContent.trim().replace(/"/g, '""') || '';
                        const desc = row.querySelector('.kpi-cell-description')?.textContent.trim().replace(/"/g, '""') || '';
                        const goal = row.querySelector('.kpi-cell-goal')?.value.trim() || '0.0';

                        csvContent += `"${enabled}","${name}","${desc}","${goal}"\n`;
                    });

                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.setAttribute('href', url);
                    link.setAttribute('download', 'kpis_export.csv');
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                });
            }

            // Import CSV for KPIs
            const kpisImportCsvBtn = document.getElementById('kpisImportCsvBtn');
            const kpisImportFileInput = document.getElementById('kpisImportFileInput');

            if (kpisImportCsvBtn && kpisImportFileInput) {
                kpisImportCsvBtn.addEventListener('click', function () {
                    kpisImportFileInput.click();
                });

                kpisImportFileInput.addEventListener('change', function (e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = function (evt) {
                        const text = evt.target.result;
                        const lines = text.split('\n').filter(l => l.trim().length > 0);
                        if (lines.length > 1) {
                            alert(`Successfully processed ${lines.length - 1} KPI entries from CSV.`);
                        }
                    };
                    reader.readAsText(file);
                });
            }

            // ==========================================
            // RCM User Mapping Interactions
            // ==========================================
            let selectedRcmLocation = '';
            const rcmLocationBtn = document.getElementById('rcmLocationBtn');
            const rcmLocationDropdown = document.getElementById('rcmLocationDropdown');
            const rcmLocationSelectedText = document.getElementById('rcmLocationSelectedText');
            const rcmSearchInput = document.getElementById('rcmSearchInput');

            function filterRcmTable() {
                const searchVal = (rcmSearchInput ? rcmSearchInput.value : '').trim().toLowerCase();
                const rows = document.querySelectorAll('.rcm-row');

                rows.forEach(row => {
                    const loc = (row.getAttribute('data-location') || '').toLowerCase();
                    const id = (row.getAttribute('data-id') || '').toLowerCase();
                    const users = (row.getAttribute('data-users') || '').toLowerCase();

                    const matchesLocation = !selectedRcmLocation || loc === selectedRcmLocation.toLowerCase();
                    const matchesSearch = !searchVal || loc.includes(searchVal) || id.includes(searchVal) || users.includes(searchVal);

                    if (matchesLocation && matchesSearch) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            if (rcmLocationBtn && rcmLocationDropdown) {
                rcmLocationBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    rcmLocationDropdown.classList.toggle('hidden');
                });
            }

            document.querySelectorAll('.rcm-location-item').forEach(item => {
                item.addEventListener('click', function (e) {
                    e.stopPropagation();
                    selectedRcmLocation = this.getAttribute('data-location') || '';
                    if (rcmLocationSelectedText) {
                        rcmLocationSelectedText.textContent = selectedRcmLocation || 'All Locations';
                    }

                    document.querySelectorAll('.rcm-location-item').forEach(btn => {
                        btn.classList.remove('bg-[#00bfa5]/10', 'font-bold', 'text-[#00bfa5]');
                    });
                    this.classList.add('bg-[#00bfa5]/10', 'font-bold', 'text-[#00bfa5]');

                    if (rcmLocationDropdown) {
                        rcmLocationDropdown.classList.add('hidden');
                    }

                    filterRcmTable();
                });
            });

            if (rcmSearchInput) {
                rcmSearchInput.addEventListener('input', function () {
                    filterRcmTable();
                });
            }

            // Action menu toggles for RCM rows
            document.querySelectorAll('.rcm-action-menu-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const container = this.closest('.rcm-action-container');
                    const menu = container ? container.querySelector('.rcm-dropdown-menu') : null;
                    document.querySelectorAll('.rcm-dropdown-menu').forEach(m => {
                        if (m !== menu) m.classList.add('hidden');
                    });
                    if (menu) menu.classList.toggle('hidden');
                });
            });

            // Edit RCM Modal Handlers
            const editRcmModal = document.getElementById('editRcmModal');
            const closeEditRcmModalBtn = document.getElementById('closeEditRcmModalBtn');
            const cancelEditRcmBtn = document.getElementById('cancelEditRcmBtn');
            const editRcmForm = document.getElementById('editRcmForm');
            const editRcmLocationTitle = document.getElementById('editRcmLocationTitle');
            const editRcmLocationIdInput = document.getElementById('editRcmLocationIdInput');
            let editingRcmRow = null;

            function openEditRcmModal(row) {
                if (!editRcmModal || !row) return;
                editingRcmRow = row;

                const id = row.getAttribute('data-id') || '';
                const locCell = row.querySelector('.rcm-cell-location');
                const loc = locCell ? locCell.textContent.trim() : '';
                const usersStr = row.getAttribute('data-users') || '';
                const assignedUsers = usersStr.split(',').map(u => u.trim().toLowerCase()).filter(Boolean);

                if (editRcmLocationTitle) editRcmLocationTitle.textContent = loc;
                if (editRcmLocationIdInput) editRcmLocationIdInput.value = id;

                document.querySelectorAll('.rcm-user-cb').forEach(cb => {
                    cb.checked = assignedUsers.includes(cb.value.trim().toLowerCase());
                });

                editRcmModal.classList.remove('hidden');
                if (window.lucide) window.lucide.createIcons();
            }

            function closeEditRcmModal() {
                if (editRcmModal) {
                    editRcmModal.classList.add('hidden');
                    editingRcmRow = null;
                }
            }

            if (closeEditRcmModalBtn) closeEditRcmModalBtn.addEventListener('click', closeEditRcmModal);
            if (cancelEditRcmBtn) cancelEditRcmBtn.addEventListener('click', closeEditRcmModal);

            document.querySelectorAll('.rcm-edit-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const row = this.closest('.rcm-row');
                    document.querySelectorAll('.rcm-dropdown-menu').forEach(m => m.classList.add('hidden'));
                    openEditRcmModal(row);
                });
            });

            if (editRcmForm) {
                editRcmForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (!editingRcmRow) return;

                    const selectedUsers = Array.from(document.querySelectorAll('.rcm-user-cb:checked')).map(cb => cb.value.trim());
                    const countCell = editingRcmRow.querySelector('.rcm-cell-count');
                    const usersCell = editingRcmRow.querySelector('.rcm-cell-users');

                    if (countCell) countCell.textContent = selectedUsers.length;
                    if (usersCell) usersCell.textContent = selectedUsers.length > 0 ? selectedUsers.join(', ') : '--';

                    editingRcmRow.setAttribute('data-users', selectedUsers.join(', ').toLowerCase());

                    closeEditRcmModal();
                });
            }

            // ==========================================
            // Snapshot Settings Interactions
            // ==========================================
            let selectedSnapshotCategory = 'Basic';
            const snapshotCategoryBtn = document.getElementById('snapshotCategoryBtn');
            const snapshotCategoryDropdown = document.getElementById('snapshotCategoryDropdown');
            const snapshotCategorySelectedText = document.getElementById('snapshotCategorySelectedText');

            function filterSnapshotTable(category = null) {
                if (category) selectedSnapshotCategory = category;
                const rows = document.querySelectorAll('.snapshot-row');
                rows.forEach(row => {
                    const rowCat = row.getAttribute('data-category') || 'Basic';
                    if (rowCat === selectedSnapshotCategory) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            if (snapshotCategoryBtn && snapshotCategoryDropdown) {
                snapshotCategoryBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    snapshotCategoryDropdown.classList.toggle('hidden');
                });
            }

            document.querySelectorAll('.snapshot-category-item').forEach(item => {
                item.addEventListener('click', function (e) {
                    e.stopPropagation();
                    selectedSnapshotCategory = this.getAttribute('data-category') || 'Basic';
                    if (snapshotCategorySelectedText) {
                        snapshotCategorySelectedText.textContent = selectedSnapshotCategory;
                    }

                    document.querySelectorAll('.snapshot-category-item').forEach(btn => {
                        btn.classList.remove('bg-[#00bfa5]/10', 'font-bold', 'text-[#00bfa5]');
                    });
                    this.classList.add('bg-[#00bfa5]/10', 'font-bold', 'text-[#00bfa5]');

                    if (snapshotCategoryDropdown) {
                        snapshotCategoryDropdown.classList.add('hidden');
                    }

                    filterSnapshotTable(selectedSnapshotCategory);
                });
            });

            // Action menu toggles for Snapshot rows
            document.querySelectorAll('.snapshot-action-menu-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const container = this.closest('.snapshot-action-container');
                    const menu = container ? container.querySelector('.snapshot-dropdown-menu') : null;
                    document.querySelectorAll('.snapshot-dropdown-menu').forEach(m => {
                        if (m !== menu) m.classList.add('hidden');
                    });
                    if (menu) menu.classList.toggle('hidden');
                });
            });

            // Reordering rows (Up / Down)
            document.querySelectorAll('.snapshot-order-up-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const row = this.closest('.snapshot-row');
                    if (row) {
                        let prev = row.previousElementSibling;
                        while (prev && prev.style.display === 'none') {
                            prev = prev.previousElementSibling;
                        }
                        if (prev && prev.classList.contains('snapshot-row')) {
                            row.parentNode.insertBefore(row, prev);
                        }
                    }
                });
            });

            document.querySelectorAll('.snapshot-order-down-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const row = this.closest('.snapshot-row');
                    if (row) {
                        let next = row.nextElementSibling;
                        while (next && next.style.display === 'none') {
                            next = next.nextElementSibling;
                        }
                        if (next && next.classList.contains('snapshot-row')) {
                            row.parentNode.insertBefore(next, row);
                        }
                    }
                });
            });

            // Toggle checkbox change handler
            document.querySelectorAll('.snapshot-enable-toggle').forEach(toggle => {
                toggle.addEventListener('change', function () {
                    this.setAttribute('aria-checked', this.checked ? '1' : '0');
                    this.value = this.checked ? 'true' : 'false';
                });
            });

            // Edit Snapshot Modal Handlers
            const editSnapshotModal = document.getElementById('editSnapshotModal');
            const closeEditSnapshotModalBtn = document.getElementById('closeEditSnapshotModalBtn');
            const cancelEditSnapshotBtn = document.getElementById('cancelEditSnapshotBtn');
            const editSnapshotForm = document.getElementById('editSnapshotForm');
            const editSnapshotIdInput = document.getElementById('editSnapshotIdInput');
            const editSnapshotTitleInput = document.getElementById('editSnapshotTitleInput');
            const editSnapshotDefinitionInput = document.getElementById('editSnapshotDefinitionInput');
            const editSnapshotEnabledInput = document.getElementById('editSnapshotEnabledInput');
            let editingSnapshotRow = null;

            function openEditSnapshotModal(row) {
                if (!editSnapshotModal || !row) return;
                editingSnapshotRow = row;

                const id = row.getAttribute('data-metric-id') || '';
                const title = row.querySelector('.snapshot-cell-title') ? row.querySelector('.snapshot-cell-title').textContent.trim() : '';
                const definition = row.querySelector('.snapshot-cell-definition') ? row.querySelector('.snapshot-cell-definition').textContent.trim() : '';
                const toggle = row.querySelector('.snapshot-enable-toggle');
                const isEnabled = toggle ? toggle.checked : false;

                if (editSnapshotIdInput) editSnapshotIdInput.value = id;
                if (editSnapshotTitleInput) editSnapshotTitleInput.value = title;
                if (editSnapshotDefinitionInput) editSnapshotDefinitionInput.value = definition;
                if (editSnapshotEnabledInput) editSnapshotEnabledInput.checked = isEnabled;

                editSnapshotModal.classList.remove('hidden');
                if (window.lucide) window.lucide.createIcons();
            }

            function closeEditSnapshotModal() {
                if (editSnapshotModal) {
                    editSnapshotModal.classList.add('hidden');
                    editingSnapshotRow = null;
                }
            }

            if (closeEditSnapshotModalBtn) closeEditSnapshotModalBtn.addEventListener('click', closeEditSnapshotModal);
            if (cancelEditSnapshotBtn) cancelEditSnapshotBtn.addEventListener('click', closeEditSnapshotModal);

            document.querySelectorAll('.snapshot-edit-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const row = this.closest('.snapshot-row');
                    document.querySelectorAll('.snapshot-dropdown-menu').forEach(m => m.classList.add('hidden'));
                    openEditSnapshotModal(row);
                });
            });

            if (editSnapshotForm) {
                editSnapshotForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (!editingSnapshotRow) return;

                    const newTitle = editSnapshotTitleInput ? editSnapshotTitleInput.value.trim() : '';
                    const newDef = editSnapshotDefinitionInput ? editSnapshotDefinitionInput.value.trim() : '';
                    const newEnabled = editSnapshotEnabledInput ? editSnapshotEnabledInput.checked : false;

                    const titleCell = editingSnapshotRow.querySelector('.snapshot-cell-title');
                    const defCell = editingSnapshotRow.querySelector('.snapshot-cell-definition');
                    const toggle = editingSnapshotRow.querySelector('.snapshot-enable-toggle');

                    if (titleCell) titleCell.textContent = newTitle;
                    if (defCell) defCell.textContent = newDef;
                    if (toggle) {
                        toggle.checked = newEnabled;
                        toggle.setAttribute('aria-checked', newEnabled ? '1' : '0');
                        toggle.value = newEnabled ? 'true' : 'false';
                    }

                    editingSnapshotRow.setAttribute('data-title', newTitle.toLowerCase());

                    closeEditSnapshotModal();
                });
            }

            // Add View Modal Handlers
            const addSnapshotViewModal = document.getElementById('addSnapshotViewModal');
            const openAddSnapshotViewBtn = document.getElementById('openAddSnapshotViewBtn');
            const closeAddSnapshotViewModalBtn = document.getElementById('closeAddSnapshotViewModalBtn');
            const cancelAddSnapshotViewBtn = document.getElementById('cancelAddSnapshotViewBtn');
            const addSnapshotViewForm = document.getElementById('addSnapshotViewForm');
            const newSnapshotViewNameInput = document.getElementById('newSnapshotViewNameInput');

            function openAddSnapshotViewModal() {
                if (addSnapshotViewModal) {
                    addSnapshotViewModal.classList.remove('hidden');
                    if (newSnapshotViewNameInput) newSnapshotViewNameInput.value = '';
                }
            }

            function closeAddSnapshotViewModal() {
                if (addSnapshotViewModal) {
                    addSnapshotViewModal.classList.add('hidden');
                    if (addSnapshotViewForm) addSnapshotViewForm.reset();
                }
            }

            if (openAddSnapshotViewBtn) openAddSnapshotViewBtn.addEventListener('click', openAddSnapshotViewModal);
            if (closeAddSnapshotViewModalBtn) closeAddSnapshotViewModalBtn.addEventListener('click', closeAddSnapshotViewModal);
            if (cancelAddSnapshotViewBtn) cancelAddSnapshotViewBtn.addEventListener('click', closeAddSnapshotViewModal);

            if (addSnapshotViewForm) {
                addSnapshotViewForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const viewName = newSnapshotViewNameInput ? newSnapshotViewNameInput.value.trim() : '';
                    if (!viewName) return;

                    const tabsContainer = document.getElementById('snapshotViewTabs');
                    if (tabsContainer) {
                        const viewSlug = viewName.toLowerCase().replace(/[^a-z0-9]+/g, '-');
                        const li = document.createElement('li');
                        li.setAttribute('role', 'presentation');
                        li.innerHTML = `
                            <a href="/configuration/snapshot/${viewSlug}" role="tab" data-snapshot-view="${viewSlug}" class="snapshot-view-tab text-xs py-3 rounded-t leading-snug block focus:outline-none font-semibold px-4 mr-1 transform transition-transform duration-150 hover:text-primary-300 capitalize hover:bg-white whitespace-no-wrap text-slate-500 hover:text-slate-800 dark:text-gray-300" aria-selected="false">
                                ${viewName}
                            </a>
                        `;
                        tabsContainer.appendChild(li);

                        li.querySelector('.snapshot-view-tab').addEventListener('click', function (evt) {
                            evt.preventDefault();
                            document.querySelectorAll('.snapshot-view-tab').forEach(t => {
                                t.classList.remove('translate-y-1', 'bg-white', 'text-black', 'dark:bg-gray-700', 'dark:text-white');
                                t.classList.add('text-slate-500', 'hover:text-slate-800', 'dark:text-gray-300');
                                t.setAttribute('aria-selected', 'false');
                            });
                            this.classList.add('translate-y-1', 'bg-white', 'text-black', 'dark:bg-gray-700', 'dark:text-white');
                            this.classList.remove('text-slate-500', 'hover:text-slate-800', 'dark:text-gray-300');
                            this.setAttribute('aria-selected', 'true');

                            if (window.history.pushState) {
                                window.history.pushState({ tab: 'snapshot', subtab: viewSlug }, '', '/configuration/snapshot/' + viewSlug);
                            }
                        });
                    }

                    closeAddSnapshotViewModal();
                });
            }

            // View subtab click listeners
            document.querySelectorAll('.snapshot-view-tab').forEach(tab => {
                tab.addEventListener('click', function (e) {
                    e.preventDefault();
                    const viewSlug = this.getAttribute('data-snapshot-view') || 'default';
                    document.querySelectorAll('.snapshot-view-tab').forEach(t => {
                        t.classList.remove('translate-y-1', 'bg-white', 'text-black', 'dark:bg-gray-700', 'dark:text-white');
                        t.classList.add('text-slate-500', 'hover:text-slate-800', 'dark:text-gray-300');
                        t.setAttribute('aria-selected', 'false');
                    });
                    this.classList.add('translate-y-1', 'bg-white', 'text-black', 'dark:bg-gray-700', 'dark:text-white');
                    this.classList.remove('text-slate-500', 'hover:text-slate-800', 'dark:text-gray-300');
                    this.setAttribute('aria-selected', 'true');

                    if (window.history.pushState) {
                        window.history.pushState({ tab: 'snapshot', subtab: viewSlug }, '', '/configuration/snapshot/' + viewSlug);
                    }
                });
            });
            // ================= HUDDLE SETTINGS =================
            function switchHuddleSubtab(subtabSlug, pushState = true) {
                const normalized = (subtabSlug === 'today') ? 'today' : ((['tomorrow', 'next', 'next-working-day'].includes(subtabSlug)) ? 'tomorrow' : 'yesterday');

                // Update subtab pills
                document.querySelectorAll('.huddle-subtab-link').forEach(link => {
                    const btnSubtab = link.getAttribute('data-huddle-subtab');
                    const isMatch = (btnSubtab === normalized);
                    if (isMatch) {
                        link.classList.remove('bg-gray-000', 'text-gray-400', 'dark:bg-gray-600', 'hover:text-slate-800', 'dark:hover:text-white');
                        link.classList.add('translate-y-1', 'bg-white', 'text-black', 'dark:bg-gray-700', 'dark:text-white');
                        link.setAttribute('aria-selected', 'true');
                    } else {
                        link.classList.remove('translate-y-1', 'bg-white', 'text-black', 'dark:bg-gray-700', 'dark:text-white');
                        link.classList.add('bg-gray-000', 'text-gray-400', 'dark:bg-gray-600', 'hover:text-slate-800', 'dark:hover:text-white');
                        link.setAttribute('aria-selected', 'false');
                    }
                });

                // Filter rows
                document.querySelectorAll('.huddle-metric-row').forEach(row => {
                    const rowSubtab = row.getAttribute('data-huddle-subtab');
                    if (rowSubtab === normalized) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                });

                // Update URL history
                if (pushState && window.history.pushState) {
                    const newUrl = window.location.origin + '/configuration/huddle/' + normalized;
                    if (window.location.pathname !== '/configuration/huddle/' + normalized) {
                        window.history.pushState({ tab: 'huddle', subtab: normalized }, '', newUrl);
                    }
                }
            }

            // Huddle subtab click listeners
            document.querySelectorAll('.huddle-subtab-link').forEach(tab => {
                tab.addEventListener('click', function (e) {
                    e.preventDefault();
                    const subtabSlug = this.getAttribute('data-huddle-subtab') || 'yesterday';
                    switchHuddleSubtab(subtabSlug, true);
                });
            });

            // Action menu toggles for Huddle rows
            document.querySelectorAll('.huddle-action-menu-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const parent = this.closest('td');
                    const menu = parent ? parent.querySelector('.huddle-dropdown-menu') : null;
                    document.querySelectorAll('.huddle-dropdown-menu').forEach(m => {
                        if (m !== menu) m.classList.add('hidden');
                    });
                    if (menu) menu.classList.toggle('hidden');
                });
            });

            // Toggle checkbox change handler for Huddle
            document.querySelectorAll('.huddle-enable-toggle').forEach(toggle => {
                toggle.addEventListener('change', function () {
                    this.setAttribute('aria-checked', this.checked ? '1' : '0');
                    this.value = this.checked ? 'true' : 'false';
                });
            });

            // Edit Huddle Modal Handlers
            const editHuddleModal = document.getElementById('editHuddleModal');
            const closeEditHuddleModalBtn = document.getElementById('closeEditHuddleModalBtn');
            const cancelEditHuddleBtn = document.getElementById('cancelEditHuddleBtn');
            const editHuddleForm = document.getElementById('editHuddleForm');
            const editHuddleMetricId = document.getElementById('editHuddleMetricId');
            const editHuddleTitleInput = document.getElementById('editHuddleTitleInput');
            const editHuddleDescriptionInput = document.getElementById('editHuddleDescriptionInput');
            const editHuddleEnabledInput = document.getElementById('editHuddleEnabledInput');
            let editingHuddleRow = null;

            function openEditHuddleModal(row) {
                if (!editHuddleModal || !row) return;
                editingHuddleRow = row;

                const id = row.getAttribute('data-metric-id') || '';
                const title = row.querySelector('.huddle-cell-title') ? row.querySelector('.huddle-cell-title').textContent.trim() : '';
                const desc = row.querySelector('.huddle-cell-desc') ? row.querySelector('.huddle-cell-desc').textContent.trim() : '';
                const toggle = row.querySelector('.huddle-enable-toggle');
                const isEnabled = toggle ? toggle.checked : false;

                if (editHuddleMetricId) editHuddleMetricId.value = id;
                if (editHuddleTitleInput) editHuddleTitleInput.value = title;
                if (editHuddleDescriptionInput) editHuddleDescriptionInput.value = desc;
                if (editHuddleEnabledInput) editHuddleEnabledInput.checked = isEnabled;

                editHuddleModal.classList.remove('hidden');
            }

            function closeEditHuddleModal() {
                if (editHuddleModal) {
                    editHuddleModal.classList.add('hidden');
                    editingHuddleRow = null;
                }
            }

            if (closeEditHuddleModalBtn) closeEditHuddleModalBtn.addEventListener('click', closeEditHuddleModal);
            if (cancelEditHuddleBtn) cancelEditHuddleBtn.addEventListener('click', closeEditHuddleModal);

            document.querySelectorAll('.huddle-edit-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const row = this.closest('.huddle-metric-row');
                    document.querySelectorAll('.huddle-dropdown-menu').forEach(m => m.classList.add('hidden'));
                    openEditHuddleModal(row);
                });
            });

            if (editHuddleForm) {
                editHuddleForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (!editingHuddleRow) return;

                    const newTitle = editHuddleTitleInput ? editHuddleTitleInput.value.trim() : '';
                    const newDesc = editHuddleDescriptionInput ? editHuddleDescriptionInput.value.trim() : '';
                    const newEnabled = editHuddleEnabledInput ? editHuddleEnabledInput.checked : false;

                    const titleCell = editingHuddleRow.querySelector('.huddle-cell-title');
                    const descCell = editingHuddleRow.querySelector('.huddle-cell-desc');
                    const toggle = editingHuddleRow.querySelector('.huddle-enable-toggle');

                    if (titleCell) titleCell.textContent = newTitle;
                    if (descCell) descCell.textContent = newDesc;
                    if (toggle) {
                        toggle.checked = newEnabled;
                        toggle.setAttribute('aria-checked', newEnabled ? '1' : '0');
                        toggle.value = newEnabled ? 'true' : 'false';
                    }

                    closeEditHuddleModal();
                });
            }

            // ================= EOD SETTINGS TAB JS =================
            function switchEodSubtab(subtabSlug, pushState = true) {
                const normalized = subtabSlug || 'basics';

                // Update subtab links
                document.querySelectorAll('.eod-subtab-link').forEach(link => {
                    const isTarget = link.getAttribute('data-eod-subtab') === normalized;
                    if (isTarget) {
                        link.setAttribute('aria-selected', 'true');
                        link.classList.remove('bg-gray-000', 'text-gray-400', 'dark:bg-gray-600', 'hover:text-slate-800');
                        link.classList.add('translate-y-1', 'bg-white', 'text-black', 'dark:bg-gray-700', 'dark:text-white');
                    } else {
                        link.setAttribute('aria-selected', 'false');
                        link.classList.remove('translate-y-1', 'bg-white', 'text-black', 'dark:bg-gray-700', 'dark:text-white');
                        link.classList.add('bg-gray-000', 'text-gray-400', 'dark:bg-gray-600');
                    }
                });

                // Filter table rows
                document.querySelectorAll('.eod-metric-row').forEach(row => {
                    const rowSubtab = row.getAttribute('data-eod-subtab');
                    if (rowSubtab === normalized) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                });

                // Update URL history
                if (pushState && window.history.pushState) {
                    const newUrl = window.location.origin + '/configuration/eod/' + normalized;
                    if (window.location.pathname !== '/configuration/eod/' + normalized) {
                        window.history.pushState({ tab: 'eod', subtab: normalized }, '', newUrl);
                    }
                }
            }

            // EOD subtab click listeners
            document.querySelectorAll('.eod-subtab-link').forEach(tab => {
                tab.addEventListener('click', function (e) {
                    e.preventDefault();
                    const subtabSlug = this.getAttribute('data-eod-subtab') || 'basics';
                    switchEodSubtab(subtabSlug, true);
                });
            });

            // Action menu toggles for EOD rows
            document.querySelectorAll('.eod-action-menu-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const parent = this.closest('td');
                    const menu = parent ? parent.querySelector('.eod-dropdown-menu') : null;
                    document.querySelectorAll('.eod-dropdown-menu').forEach(m => {
                        if (m !== menu) m.classList.add('hidden');
                    });
                    if (menu) menu.classList.toggle('hidden');
                });
            });

            // Toggle checkbox change handler for EOD
            document.querySelectorAll('.eod-enable-toggle').forEach(toggle => {
                toggle.addEventListener('change', function () {
                    this.setAttribute('aria-checked', this.checked ? '1' : '0');
                    this.value = this.checked ? 'true' : 'false';
                });
            });

            document.querySelectorAll('.eod-locked-toggle').forEach(toggle => {
                toggle.addEventListener('change', function () {
                    this.setAttribute('aria-checked', this.checked ? '1' : '0');
                    this.value = this.checked ? 'true' : 'false';
                });
            });

            // Edit EOD Modal Handlers
            const editEodModal = document.getElementById('editEodModal');
            const closeEditEodModalBtn = document.getElementById('closeEditEodModalBtn');
            const cancelEditEodBtn = document.getElementById('cancelEditEodBtn');
            const editEodForm = document.getElementById('editEodForm');
            const editEodMetricId = document.getElementById('editEodMetricId');
            const editEodTitleInput = document.getElementById('editEodTitleInput');
            const editEodDescriptionInput = document.getElementById('editEodDescriptionInput');
            const editEodEnabledInput = document.getElementById('editEodEnabledInput');
            const editEodLockedInput = document.getElementById('editEodLockedInput');
            let editingEodRow = null;

            function openEditEodModal(row) {
                if (!editEodModal || !row) return;
                editingEodRow = row;

                const id = row.getAttribute('data-metric-id') || '';
                const title = row.querySelector('.eod-cell-title') ? row.querySelector('.eod-cell-title').textContent.trim() : '';
                const desc = row.querySelector('.eod-cell-desc') ? row.querySelector('.eod-cell-desc').textContent.trim() : '';
                const enableToggle = row.querySelector('.eod-enable-toggle');
                const lockedToggle = row.querySelector('.eod-locked-toggle');
                const isEnabled = enableToggle ? enableToggle.checked : false;
                const isLocked = lockedToggle ? lockedToggle.checked : false;

                if (editEodMetricId) editEodMetricId.value = id;
                if (editEodTitleInput) editEodTitleInput.value = title;
                if (editEodDescriptionInput) editEodDescriptionInput.value = desc;
                if (editEodEnabledInput) editEodEnabledInput.checked = isEnabled;
                if (editEodLockedInput) editEodLockedInput.checked = isLocked;

                editEodModal.classList.remove('hidden');
            }

            function closeEditEodModal() {
                if (editEodModal) {
                    editEodModal.classList.add('hidden');
                    editingEodRow = null;
                }
            }

            if (closeEditEodModalBtn) closeEditEodModalBtn.addEventListener('click', closeEditEodModal);
            if (cancelEditEodBtn) cancelEditEodBtn.addEventListener('click', closeEditEodModal);

            document.querySelectorAll('.eod-edit-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const row = this.closest('.eod-metric-row');
                    document.querySelectorAll('.eod-dropdown-menu').forEach(m => m.classList.add('hidden'));
                    openEditEodModal(row);
                });
            });

            if (editEodForm) {
                editEodForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (!editingEodRow) return;

                    const newTitle = editEodTitleInput ? editEodTitleInput.value.trim() : '';
                    const newDesc = editEodDescriptionInput ? editEodDescriptionInput.value.trim() : '';
                    const newEnabled = editEodEnabledInput ? editEodEnabledInput.checked : false;
                    const newLocked = editEodLockedInput ? editEodLockedInput.checked : false;

                    const titleCell = editingEodRow.querySelector('.eod-cell-title');
                    const descCell = editingEodRow.querySelector('.eod-cell-desc');
                    const enableToggle = editingEodRow.querySelector('.eod-enable-toggle');
                    const lockedToggle = editingEodRow.querySelector('.eod-locked-toggle');

                    if (titleCell) titleCell.textContent = newTitle;
                    if (descCell) descCell.textContent = newDesc;
                    if (enableToggle) {
                        enableToggle.checked = newEnabled;
                        enableToggle.setAttribute('aria-checked', newEnabled ? '1' : '0');
                        enableToggle.value = newEnabled ? 'true' : 'false';
                    }
                    if (lockedToggle) {
                        lockedToggle.checked = newLocked;
                        lockedToggle.setAttribute('aria-checked', newLocked ? '1' : '0');
                        lockedToggle.value = newLocked ? 'true' : 'false';
                    }

                    closeEditEodModal();
                });
            }
        });
    </script>
</x-app-layout>
