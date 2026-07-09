@php
    $tab = 'marketing';
    $subtab = $activeSubtab ?? 'default';
@endphp

<div class="space-y-4">

    {{-- Subtab bar --}}
    <div class="bg-white border w-full border-slate-200 rounded shadow-sm">
        @if (!empty($subtabs))
            <ul class="flex border-b border-slate-200 px-4 pt-3 gap-1">
                @foreach ($subtabs as $slug => $label)
                    <a href="{{ route('operations.tab', $slug === 'default' ? [$tab] : [$tab, $slug]) }}"
                        data-ops-subtab="{{ $slug }}" class="text-xs font-semibold px-4 py-2 rounded-t cursor-pointer whitespace-nowrap
                                      {{ $slug === $subtab
                    ? 'bg-white text-black border border-b-0 border-slate-200 -mb-px hover:bg-slate-50'
                    : 'text-slate-400 hover:text-slate-600 bg-slate-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </ul>
        @endif

        {{-- Toolbar: zip filter --}}
        <div class="flex items-center justify-end p-3 bg-white">
            <div class="flex items-center space-x-2">
                <label for="marketing_zip" class="text-xs font-semibold text-slate-700">ZIP</label>
                <select id="marketing_zip" data-ops-filter="zip"
                    class="border border-slate-300 rounded px-2 py-1 text-xs bg-white text-slate-700 w-32 focus:outline-none focus:ring-1 focus:ring-[#00bfa5]">
                    <option value="ALL">ALL</option>
                    @if(isset($spec['available_zips']))
                        @foreach($spec['available_zips'] as $z)
                            <option value="{{ $z }}" {{ request('zip') == $z ? 'selected' : '' }}>{{ $z }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
    </div>

    @if ($subtab === 'default')
        {{-- Marketing default: Donut Charts for New Patients --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- Top 10 Referrals --}}
            <div class="bg-white border border-slate-200 rounded shadow-sm p-4 flex flex-col h-full">
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-gray-900 leading-tight">Top 10 Referrals <span
                            class="font-normal text-gray-400 ml-1">| New Patients</span></h3>
                </div>
                <!-- Relative container to give Chart.js an absolute bound -->
                <div class="relative w-full aspect-square flex-1 min-h-[300px]">
                    <canvas id="marketing_referrals_chart" class="absolute inset-0 w-full h-full"></canvas>
                </div>
            </div>

            {{-- Top 10 Payors --}}
            <div class="bg-white border border-slate-200 rounded shadow-sm p-4 flex flex-col h-full">
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-gray-900 leading-tight">Top 10 Payors <span
                            class="font-normal text-gray-400 ml-1">| New Patients</span></h3>
                </div>
                <div class="relative w-full aspect-square flex-1 min-h-[300px]">
                    <canvas id="marketing_payors_chart" class="absolute inset-0 w-full h-full"></canvas>
                </div>
            </div>

            {{-- Top 10 Employers --}}
            <div class="bg-white border border-slate-200 rounded shadow-sm p-4 flex flex-col h-full">
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-gray-900 leading-tight">Top 10 Employers <span
                            class="font-normal text-gray-400 ml-1">| New Patients</span></h3>
                </div>
                <div class="relative w-full aspect-square flex-1 min-h-[300px]">
                    <canvas id="marketing_employers_chart" class="absolute inset-0 w-full h-full"></canvas>
                </div>
            </div>

        </div>

    @else
        {{-- Patient Analysis --}}
        <div class="flex flex-col items-center justify-center p-12 bg-white border border-gray-200 rounded-lg shadow-sm">
            <p class="text-gray-500 font-medium">Patient Analysis View</p>
        </div>
    @endif

</div>

@if ($subtab === 'default')
    <script>
        (function () {
            // Generate randomized brand colors for donut slices.
            function generatePalette(count) {
                const colors = [
                    '#6ee7b7', '#34d399', '#10b981', '#059669', '#047857',
                    '#93c5fd', '#60a5fa', '#3b82f6', '#2563eb', '#1d4ed8'
                ];
                return colors.slice(0, count);
            }

            // Fallback waiting for Chart.js to load exactly like services tab
            function initializeMarketingCharts() {
                if (typeof Chart === 'undefined') {
                    setTimeout(initializeMarketingCharts, 100);
                    return;
                }

                // Configuration
                const chartOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: {
                            display: false // We can hide legend or show minimal
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return ` ${context.label}: ${context.raw} New Patients`;
                                }
                            }
                        }
                    }
                };

                // Data Payloads outputted natively by blade wrapper
                var referralsKeys = {!! json_encode(array_keys($spec['top_referrals'] ?? [])) !!};
                var referralsData = {!! json_encode(array_values($spec['top_referrals'] ?? [])) !!};

                var payorsKeys = {!! json_encode(array_keys($spec['top_payors'] ?? [])) !!};
                var payorsData = {!! json_encode(array_values($spec['top_payors'] ?? [])) !!};

                var employersKeys = {!! json_encode(array_keys($spec['top_employers'] ?? [])) !!};
                var employersData = {!! json_encode(array_values($spec['top_employers'] ?? [])) !!};

                // Ensure at least one placeholder exists to draw an empty circle like the mockup
                if (referralsKeys.length === 0) { referralsKeys = ['No Data']; referralsData = [1]; }
                if (payorsKeys.length === 0) { payorsKeys = ['No Data']; payorsData = [1]; }
                if (employersKeys.length === 0) { employersKeys = ['No Data']; employersData = [1]; }

                if (document.getElementById('marketing_referrals_chart')) {
                    new Chart(document.getElementById('marketing_referrals_chart').getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: referralsKeys,
                            datasets: [{
                                data: referralsData,
                                backgroundColor: ['#6ee7b7'], // Match the solid green donut from the mockup
                                borderWidth: 0
                            }]
                        },
                        options: chartOptions
                    });
                }

                if (document.getElementById('marketing_payors_chart')) {
                    new Chart(document.getElementById('marketing_payors_chart').getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: payorsKeys,
                            datasets: [{
                                data: payorsData,
                                backgroundColor: ['#6ee7b7'],
                                borderWidth: 0
                            }]
                        },
                        options: chartOptions
                    });
                }

                if (document.getElementById('marketing_employers_chart')) {
                    new Chart(document.getElementById('marketing_employers_chart').getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: employersKeys,
                            datasets: [{
                                data: employersData,
                                backgroundColor: ['#6ee7b7'],
                                borderWidth: 0
                            }]
                        },
                        options: chartOptions
                    });
                }
            }

            initializeMarketingCharts();
        })();
    </script>
@endif