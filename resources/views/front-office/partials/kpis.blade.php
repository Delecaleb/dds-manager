<main class="max-w-[1600px] mx-auto bg-gray-50 min-h-screen pt-4 pb-12">

    <!-- Sections Container -->
    <div class="px-6 space-y-12">

        <!-- Office KPIs -->
        <section>
            <h2 class="text-sm font-bold text-gray-900 border-b-[3px] border-blue-600 pb-2 mb-4">Office KPIs</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="kpi-group-office">
                <!-- Javascript will inject skeletal cards here -->
            </div>
        </section>

        <!-- Doctor KPIs -->
        <section>
            <h2 class="text-sm font-bold text-gray-900 border-b-[3px] border-purple-600 pb-2 mb-4">Doctor KPIs</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="kpi-group-doctor">
                <!-- Javascript will inject skeletal cards here -->
            </div>
        </section>

        <!-- Hygiene KPIs -->
        <section>
            <h2 class="text-sm font-bold text-gray-900 border-b-[3px] border-emerald-500 pb-2 mb-4">Hygiene KPIs</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="kpi-group-hygiene">
                <!-- Javascript will inject skeletal cards here -->
            </div>
        </section>

    </div>
</main>

<!-- Blueprint Template for a Card -->
<template id="kpi-card-template">
    <div
        class="bg-white rounded-md border border-gray-200 p-4 shadow-sm flex flex-col gap-3 min-h-[140px] kpi-card-container">
        <div class="flex justify-between items-start">
            <h3 class="text-[13px] font-bold text-gray-800 kpi-title">Title Here</h3>
            <i class="fa-regular fa-angle-right text-gray-400 cursor-pointer hover:text-gray-600"></i>
        </div>

        <div class="flex items-center w-full gap-4 flex-1">
            <!-- Left Details -->
            <div class="flex flex-col flex-shrink-0 min-w-[35%] border-r border-gray-100 pr-4">
                <div class="text-2xl font-black text-gray-900 leading-none mb-3 kpi-value kpi-skeleton">
                    <div class="h-7 w-20 bg-gray-200 rounded animate-pulse"></div>
                </div>
                <div class="flex gap-6 mt-auto">
                    <div class="flex flex-col">
                        <span class="text-[9px] text-gray-400 font-medium uppercase mb-0.5">Last</span>
                        <span class="text-xs font-bold text-gray-800 kpi-last kpi-skeleton">
                            <div class="h-3 w-8 bg-gray-200 rounded animate-pulse mt-0.5"></div>
                        </span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[9px] text-gray-400 font-medium uppercase mb-0.5">Target</span>
                        <span class="text-xs font-bold text-gray-800 kpi-tgt kpi-skeleton">
                            <div class="h-3 w-8 bg-gray-200 rounded animate-pulse mt-0.5"></div>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right Progress -->
            <div class="flex-1 flex flex-col justify-center h-full pl-2">
                <div class="flex justify-between text-[11px] font-bold text-gray-500 mb-2 kpi-prog-lbl-wrapper hidden">
                    <span>Target</span>
                    <span class="kpi-target-label text-blue-600">0%</span>
                </div>
                <div class="w-full bg-gray-100 h-1.5 rounded-full relative overflow-hidden mb-2">
                    <div
                        class="absolute left-0 top-0 h-full bg-gray-200 rounded-full w-full animate-pulse kpi-pb-skeleton">
                    </div>
                    <div class="h-full rounded-full transition-all duration-1000 kpi-progress-bar w-0"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    $(document).ready(function () {
        // Configuration for the KPIs
        const kpiConfig = {
            'office': [
                { id: 'pat_retention', title: 'Patient Retention', type: 'percent', color: 'blue' },
                { id: 'tot_tx_plans', title: 'Total Treatment Plans in Day', type: 'number', color: 'blue' },
                { id: 'copay_col', title: 'Co-Pay Collection', type: 'percent', color: 'blue' },
                { id: 'resched', title: 'Re-scheduled', type: 'percent', color: 'blue' },
                { id: 'new_pat_a', title: 'New Patients A', type: 'percent', color: 'blue' },
                { id: 'no_show', title: 'No-Show Rate', type: 'percent', color: 'blue' },
                { id: 'pat_react', title: 'Patient Re-activation', type: 'percent', color: 'blue' },
                { id: 'pat_added', title: 'Patients Added', type: 'number', color: 'blue' },
                { id: 'pat_viewed', title: 'Patient Viewed', type: 'number', color: 'blue' },
                { id: 'new_pat_rev', title: 'New Patient Revenue', type: 'currency', color: 'blue' },
                { id: 'unsch_hyg_ret', title: 'Unscheduled hygiene return', type: 'percent', color: 'blue' }
            ],
            'doctor': [
                { id: 'doc_prod_same_day', title: 'Production per - Same Day', type: 'percent', color: 'purple' },
                { id: 'doc_case_acc', title: 'Case Acceptance Rate', type: 'percent', color: 'purple' },
                { id: 'doc_gross_prod', title: 'Gross Production (excluding Ortho/Perio)', type: 'currency', color: 'purple' },
                { id: 'doc_net_prod', title: 'Net Production (excluding Ortho/Perio)', type: 'currency', color: 'purple' },
                { id: 'doc_avg_op', title: 'Avg Per Operatory/Doctor (cumulative)', type: 'number', color: 'purple' },
                { id: 'doc_avg_prod_hr', title: 'Average Doctor Production per hour', type: 'currency', color: 'purple' },
                { id: 'doc_avg_tx_appt', title: 'Average Treatment per Doctor Appointment', type: 'currency', color: 'purple' },
                { id: 'doc_same_day_np', title: 'Same Day Treatment per New Patient', type: 'currency', color: 'purple' },
                { id: 'doc_avg_prod_np', title: 'Avg Production per New Patient', type: 'currency', color: 'purple' },
                { id: 'doc_avg_tx_visit', title: 'Avg treatment per visit (excluding x-ray)', type: 'currency', color: 'purple' },
                { id: 'doc_avg_tx_pat', title: 'Avg Treatment provided per Patient', type: 'currency', color: 'purple' },
                { id: 'doc_pat_cxl_rate', title: 'Patient appointment cancellation rate', type: 'percent', color: 'purple' },
                { id: 'doc_unsch_tx', title: 'Unscheduled treatment plans', type: 'currency', color: 'purple' },
                { id: 'doc_supplies', title: 'Doctor supplies', type: 'percent', color: 'purple' },
                { id: 'doc_med_supplies', title: 'Doctor Medical supplies', type: 'percent', color: 'purple' },
                { id: 'doc_tot_prod', title: 'Total Doctor Production', type: 'currency', color: 'purple' }
            ],
            'hygiene': [
                { id: 'hyg_pre_apt', title: 'Pre-apt', type: 'percent', color: 'emerald' },
                { id: 'hyg_unfilled', title: 'Unfilled hygiene apts.', type: 'number', color: 'emerald' },
                { id: 'hyg_avg_prod_hr', title: 'Avg. Prod. per hr', type: 'currency', color: 'emerald' },
                { id: 'hyg_avg_med_hr', title: 'Avg. Medical per Medical Per hr', type: 'currency', color: 'emerald' },
                { id: 'hyg_prebook_pt', title: 'Pre-booking per patient', type: 'percent', color: 'emerald' },
                { id: 'hyg_avg_time_appt', title: 'Avg. Time per appt', type: 'number', color: 'emerald' },
                { id: 'hyg_avg_xray_appt', title: 'Avg. X-ray per appt', type: 'number', color: 'emerald' },
                { id: 'hyg_pts_per_day', title: 'Number of Patients per Day', type: 'number', color: 'emerald' },
                { id: 'hyg_pts_per_hr', title: 'Avg pts per hour', type: 'currency', color: 'emerald' },
                { id: 'hyg_fluoride_pt', title: 'Fluoride/pt', type: 'number', color: 'emerald' },
                { id: 'hyg_adult_prophy', title: 'Adult Prophy and Perio Maint % of Patients', type: 'percent', color: 'emerald' },
                { id: 'hyg_adult_tx', title: 'Adult Hygiene Treatment % of Patients', type: 'percent', color: 'emerald' },
                { id: 'hyg_last_2', title: 'Last 2 Hygiene visits % of Patients', type: 'percent', color: 'emerald' },
                { id: 'hyg_last_2_months', title: 'Last 2 Hygiene Visits Last X months', type: 'percent', color: 'emerald' },
                { id: 'hyg_sealants', title: 'Sealants', type: 'number', color: 'emerald' },
                { id: 'hyg_srp', title: 'SRP completed', type: 'number', color: 'emerald' },
                { id: 'hyg_perio_med', title: 'Perio/Medical Treatments', type: 'number', color: 'emerald' },
                { id: 'hyg_prod_tx', title: 'Hygiene Production per Treatment', type: 'currency', color: 'emerald' },
                { id: 'hyg_tot_visits', title: 'Total Hygiene/Medical visits', type: 'number', color: 'emerald' },
                { id: 'hyg_unfilled_dt', title: 'Unfilled downtime', type: 'percent', color: 'emerald' },
                { id: 'hyg_avg_prod_visit', title: 'Average Hygiene Production per Visit', type: 'currency', color: 'emerald' },
                { id: 'hyg_case_acc', title: 'Case Acceptance Rate', type: 'percent', color: 'emerald' }
            ]
        };

        const template = document.getElementById('kpi-card-template').content;

        // Flatten into single queue
        let fetchQueue = [];

        // Render Skeletal UI initially
        ['office', 'doctor', 'hygiene'].forEach(group => {
            const container = $('#kpi-group-' + group);
            kpiConfig[group].forEach(kpi => {
                let clone = $(template.cloneNode(true));
                clone.find('.kpi-title').text(kpi.title);
                clone.find('.kpi-card-container').attr('id', 'card-' + kpi.id);
                // store metadata for formatting later
                clone.find('.kpi-card-container').data('type', kpi.type).data('color', kpi.color).data('kpi', kpi.id);
                container.append(clone);

                fetchQueue.push(kpi);
            });
        });

        const formatValue = (val, type) => {
            if (type === 'percent') return (parseFloat(val) || 0).toFixed(1) + '%';
            if (type === 'currency') return '$' + (parseFloat(val) || 0).toLocaleString('en-US');
            return (parseFloat(val) || 0).toLocaleString('en-US');
        };

        const getColorClasses = (colorName) => {
            const map = {
                'blue': { bg: 'bg-blue-500', text: 'text-blue-600' },
                'purple': { bg: 'bg-purple-500', text: 'text-purple-600' },
                'emerald': { bg: 'bg-emerald-500', text: 'text-emerald-600' }
            };
            return map[colorName] || map['blue'];
        };

        const executeQueue = async () => {
            // First render skeletons before loading to provide visual feedback if it was triggered by a filter change
            $('.kpi-value').text('').addClass('kpi-skeleton').append('<div class="h-7 w-20 bg-gray-200 rounded animate-pulse"></div>');
            $('.kpi-last, .kpi-tgt').text('').addClass('kpi-skeleton').append('<div class="h-3 w-8 bg-gray-200 rounded animate-pulse mt-0.5"></div>');
            $('.kpi-pb-skeleton').show();
            $('.kpi-prog-lbl-wrapper').addClass('hidden');
            $('.kpi-progress-bar').css('width', '0%').attr('class', 'h-full rounded-full transition-all duration-1000 kpi-progress-bar w-0');

            const sections = ['office', 'doctor', 'hygiene'];
            const dateParams = window.getFoDateParams ? window.getFoDateParams() : { month: $('#frontOfficeMonth').val() || '' };

            for (let section of sections) {
                try {
                    // Request entire section data at once
                    const params = $.extend({ section: section }, dateParams);
                    const response = await $.get(`{{ route('front-office.kpi-data') }}`, params);
                    const items = response.data || [];

                    items.forEach(data => {
                        const kpiMeta = kpiConfig[section].find(k => k.id === data.id);
                        if (!kpiMeta) return;

                        const card = $('#card-' + data.id);
                        const cTheme = getColorClasses(kpiMeta.color);

                        card.find('.kpi-skeleton').removeClass('kpi-skeleton').html('');

                        card.find('.kpi-value').text(formatValue(data.current, kpiMeta.type));
                        card.find('.kpi-last').text(formatValue(data.last, kpiMeta.type));
                        card.find('.kpi-tgt').text(formatValue(data.target, kpiMeta.type));

                        card.find('.kpi-pb-skeleton').hide();
                        card.find('.kpi-prog-lbl-wrapper').removeClass('hidden');
                        card.find('.kpi-target-label').text(formatValue(data.target, kpiMeta.type)).addClass(cTheme.text);

                        let pct = 0;
                        let targetNum = parseFloat(data.target) || 0;
                        let currentNum = parseFloat(data.current) || 0;
                        if (targetNum > 0 && typeof data.current !== 'string') {
                            pct = Math.min(100, Math.round((currentNum / targetNum) * 100));
                        }
                        card.find('.kpi-progress-bar').addClass(cTheme.bg).css('width', pct + '%');
                    });
                } catch (e) {
                    console.warn('Failed to load kpi section:', section, e);
                }
            }
        };

        // Start fetching sequentially on initial load
        executeQueue();

        window.reloadFoTables = function () {
            executeQueue();
        };
    });
</script>