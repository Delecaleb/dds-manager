@php
    $metricDescriptions = [
        'BYO Production' => 'The total production presented by office (BYO) based on a given date range.',
        'BYO Active Pts' => 'The number of patients with completed procedures in the last 24 months, based on a given date range.',
        'BYO Active Pts Count' => 'The number of patients with completed procedures in the last 24 months, based on a given date range.',
        'BYO Avg # of Tx Plans Presented' => 'Total number of tx plans presented divided by the working day amount.',
        'BYO Close Percent' => 'The number of treatment plans accepted divided by the number of treatment plans presented (must be greater than $10).',
        'BYO Collection' => 'The total collection presented by office (BYO).',
        'BYO Co Pay Coll' => 'The total amount of money expected to be collected as a co-pay vs. what was actually collected.',
        'BYO Doc Collection' => 'Collection of all Doctor Providers.',
        'BYO Doc Production' => 'Production of all Doctor Providers.',
        'BYO $ in Pen. Tx' => 'The total expected production of treatment plans not yet completed.',
        'BYO Hyg Collection' => 'Collection of all Hygiene Providers.',
        'BYO Hyg Production' => 'Production of all Hygiene Providers.',
        'BYO Npts Visits' => 'The total number of new patients presented by office (BYO).',
        'BYO No Show Rate' => 'The count of all deleted/cancelled appointments divided by the count of all appointments.',
        'BYO Number of Treatment Plans Presented' => 'Total number of tx plans divided by total number of working days per provider.',
        'BYO Pts Appointment' => 'The total number of appointments in a given date range.',
        'BYO Patient Retention' => 'The patient retention rate based on a given date range.',
        'BYO Pts Visits' => 'The total number of patient visits presented by office (BYO).',
        'Cancellation Rate' => 'The rate of cancelled and broken appointments compared to total scheduled appointments.',
        'Coll per Doc' => 'Total collection amount per doctor provider.',
        'Coll per Hyg' => 'Total collection amount per hygiene provider.',
        'Collection VS production $' => 'Comparison of total collections versus total gross production in dollars.',
        'Collection VS production %' => 'Percentage of gross production collected over the selected period.',
        'DOC Avg Treatment Plan Existing' => 'Average treatment plan presentation value for existing patients.',
        'DOC Avg Treatment Plan New Patients' => 'Average treatment plan presentation value for new patients.',
        'DOC Avg. Treatment plan ($) per Existing Pts' => 'Average dollar value of treatment plans presented per existing patient.',
        'DOC Avg. Treatment plan ($) per New Pts' => 'Average dollar value of treatment plans presented per new patient.',
        'DOC Comprehensive Exam' => 'Count of completed comprehensive exams by doctor providers.',
        'DOC Limited Exam' => 'Count of completed limited / emergency exams by doctor providers.',
        'DOC Pts Visits' => 'Total patient visits completed by doctor providers.',
        'DOC Periodic Exam' => 'Count of completed periodic exams by doctor providers.',
        'DOC Production Per Exam' => 'Average doctor production generated per completed exam.',
        'HYG Adjunctive Aid' => 'Count and production of hygiene adjunctive procedures.',
        'HYG Avg FMX' => 'Average full mouth series (FMX) X-rays completed per hygiene provider.',
        'HYG Avg Production Per Day' => 'Average daily gross production generated per hygiene provider.',
        'HYG Avg Production Per Patient' => 'Average gross production generated per patient seen by hygiene.',
        'HYG Avg Production Per Procedure' => 'Average revenue per completed hygiene procedure.',
        'HYG Avg Production Per Provider' => 'Average total production generated per individual hygiene provider.',
        'HYG Avg SRP Per Day' => 'Average scaling and root planing (SRP) procedures completed per day.',
        'HYG Pts Visits' => 'Total patient visits completed by hygiene providers.',
        'HYG Perio Appointments' => 'Total periodontal maintenance and therapy appointments.',
        'HYG % Perio to Prophy' => 'Percentage of periodontal procedures compared to standard adult prophylaxes.',
        'HYG Periochip Placements' => 'Count of Periochip / antimicrobial placements completed.',
        'HYG Production Per Exam' => 'Average hygiene production generated per hygiene exam.',
        'HYG Reappointment Rate' => 'Percentage of hygiene patients who scheduled their next recall visit.',
        'HYG Ret. (Adult - past 12 months)' => 'Hygiene retention rate for adult patients over the past 12 months.',
        'HYG Ret. (Adult - past 6 months)' => 'Hygiene retention rate for adult patients over the past 6 months.',
        'HYG Ret. (Child - past 12 months)' => 'Hygiene retention rate for child patients over the past 12 months.',
        'HYG Ret. (Child - past 6 months)' => 'Hygiene retention rate for child patients over the past 6 months.',
        'HYG Sealants' => 'Total dental sealants completed by hygiene providers.',
        'HYG Varnish Applications Per Day' => 'Average fluoride varnish applications completed per day.',
        'HYG Whitening Procedure' => 'Count of completed whitening and bleaching procedures.',
        'Medicaid Percentage' => 'Percentage of patient volume and production attributed to Medicaid plans.',
        'OS Collection' => 'Total collections for Oral Surgery procedures.',
        'ORT Active Pts Count' => 'Total active patient count undergoing orthodontic treatment.',
        'ORT Active Pts %' => 'Percentage of total practice patients undergoing orthodontic treatment.',
        'ORT Avg. # of Tx Plans Presented' => 'Average number of orthodontic treatment plans presented.',
        'ORT Collection' => 'Total collections generated by orthodontic procedures.',
        'ORT Npts Visits' => 'Count of new patient visits for orthodontic consultations.',
        'ORT No Show Rate' => 'Orthodontic appointment no-show and cancellation percentage.',
        'ORT Pts Appts' => 'Total scheduled orthodontic appointments.',
        'ORT Patient Retention' => 'Patient retention percentage for ongoing orthodontic cases.',
        'ORT Pts Visits' => 'Total completed orthodontic patient visits.',
        'ORT Production' => 'Total gross production generated by orthodontic services.',
        'ORT U Pts Visits/day' => 'Unique orthodontic patient visits per working day.',
        'ORT U Pts Visits/mo' => 'Unique orthodontic patient visits per month.',
        'PPV Collection' => 'Collections generated from Pay Per Visit procedures.',
        'PPV Procedures' => 'Count of completed Pay Per Visit procedures.',
        'PPV Production' => 'Total production generated from Pay Per Visit procedures.',
        'PP Collection' => 'Total collections for Private Pay / Cash patients.',
        'PP Production' => 'Total production generated from Private Pay / Cash patients.',
        'PWD Collection' => 'Total collections for Patients with Disabilities / special care.',
        'PWD Npts Visits' => 'New patient visits for Patients with Disabilities / special care.',
        'PWD Pts Visits' => 'Total patient visits for Patients with Disabilities / special care.',
        'PWD Production' => 'Total production for Patients with Disabilities / special care.',
        'Perio Collection' => 'Total collections generated from Periodontic procedures.',
        'Prod per Doc' => 'Average production generated per doctor provider.',
        'Prod per Hyg' => 'Average production generated per hygiene provider.',
        'Prov Prod Exam Codes' => 'Provider production breakdown on clinical examination codes.',
    ];

    $selectedMetric = $metric ?? 'BYO Production';
    if ($selectedMetric === 'production') $selectedMetric = 'BYO Production';
    if ($selectedMetric === 'collection') $selectedMetric = 'BYO Collection';
    if ($selectedMetric === 'visits') $selectedMetric = 'BYO Pts Visits';

    $metricTitle = $selectedMetric;
    $metricDesc = $metricDescriptions[$selectedMetric] ?? 'Performance metric trends based on a given date range.';
@endphp

<div class="bg-transparent space-y-5">

    {{-- Subtabs Wrapper --}}
    @if (!empty($subtabs))
        <ul class="flex border-b border-slate-200 mt-4 gap-1">
            @foreach ($subtabs as $s => $label)
                <a href="#" data-ops-subtab="{{ $s }}" class="text-xs font-bold px-5 py-2.5 rounded-t tracking-wide whitespace-nowrap border break-words transition-colors
                                          {{ $s === ($activeSubtab ?? 'default')
                    ? 'bg-white text-slate-800 border-x-slate-200 border-t-slate-200 border-b-white -mb-px relative z-10'
                    : 'bg-slate-50 text-slate-400 border-transparent hover:text-slate-600' }}">
                    {{ $label }}
                </a>
            @endforeach
        </ul>
    @endif

    <div class="bg-white border rounded shadow-sm {{ empty($subtabs) ? '' : 'rounded-tl-none -mt-px relative z-0' }}">

        {{-- Info Box --}}
        <div id="opsTrendsInfoBox" class="p-4 bg-[#e0f0fa] m-4 rounded hidden sm:flex items-center gap-3 border border-[#bae0f5]">
            <div class="text-[#3b82f6]">
                <i data-lucide="info" class="w-5 h-5 opacity-90"></i>
            </div>
            <div class="text-[13.5px] text-[#0c6b9e] leading-snug">
                <span class="font-bold">{{ $metricTitle }}:</span> {{ $metricDesc }}<br>
                <span class="text-[#5ba1c9]">Please note, the provider-specific metrics ignore the Line of Business
                    filter.</span>
            </div>
            <button type="button" onclick="document.getElementById('opsTrendsInfoBox')?.remove()" class="ml-auto text-[#85bfe0] hover:text-[#0c6b9e]">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        {{-- Legend --}}
        <div class="flex items-center px-6 py-2 gap-4 text-xs font-bold text-slate-800">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-[#69e4bf] rounded-sm"></div>
                {{ count($clinics ?? []) === 1 ? 'Office ' . current($clinics) : 'Selected Offices' }}
            </div>
            @if(($activeSubtab ?? 'default') === 'compare')
                <div class="flex items-center gap-2 ml-4">
                    <div class="w-3 h-3 bg-[#cbd5e1] rounded-sm"></div>
                    Previous Year
                </div>
            @endif
        </div>

        {{-- Chart container --}}
        <div class="p-6 pt-2 pb-10 w-full relative" style="height: 480px;">
            <canvas id="opsTrendsChart"></canvas>
        </div>

    </div>

    {{-- Datatable wrapper --}}
    <div class="mt-8">
        @include('operations.tabs.table', ['spec' => $spec, 'subtabs' => [], 'tab' => $tab, 'activeSubtab' => $activeSubtab ?? 'default'])
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function () {
        // Slight delay ensures the custom script block injection evaluates and the canvas is fully mounted
        setTimeout(() => {
            const ctx = document.getElementById('opsTrendsChart');
            if (!ctx) return;

            const labels = @json($spec['labels'] ?? []);
            const dataCurrent = @json($spec['current'] ?? []);
            const dataLast = @json($spec['last'] ?? []);
            const isCompare = "{{ $activeSubtab ?? 'default' }}" === 'compare';

            const datasets = [
                {
                    label: '{{ $metricTitle }} - Selected Range',
                    data: dataCurrent,
                    borderColor: '#69e4bf',
                    backgroundColor: 'rgba(105, 228, 191, 0.45)', // Fill overlay identical to user's screen
                    borderWidth: 2,
                    pointBackgroundColor: '#69e4bf',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0
                }
            ];

            if (isCompare) {
                datasets.push({
                    label: 'Previous Year',
                    data: dataLast,
                    borderColor: '#cbd5e1',
                    backgroundColor: 'rgba(203, 213, 225, 0.1)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointBackgroundColor: '#cbd5e1',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: false,
                    tension: 0
                });
            }

            // Wipe old chart safely if it persists in active memory over hot-swaps
            if (window._opsTrendsChartInstance) {
                window._opsTrendsChartInstance.destroy();
            }

            window._opsTrendsChartInstance = new Chart(ctx, {
                type: 'line',
                data: { labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#fff',
                            titleColor: '#334155',
                            bodyColor: '#334155',
                            borderColor: '#e2e8f0',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: true,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9', borderDash: [3, 3], drawBorder: false },
                            ticks: {
                                callback: function (v) { return v >= 1000 ? (v / 1000) + 'k' : v; },
                                font: { size: 11, family: 'Inter, sans-serif' },
                                color: '#64748b',
                                padding: 10
                            }
                        },
                        x: {
                            offset: true,
                            grid: { display: false },
                            ticks: {
                                font: { size: 10, family: 'Inter, sans-serif' },
                                color: '#64748b',
                                padding: 10
                            }
                        }
                    }
                }
            });
        }, 100);
    })();
</script>