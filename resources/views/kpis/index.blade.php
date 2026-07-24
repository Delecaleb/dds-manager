<x-app-layout>

  <style>
    @keyframes skel-pulse {

      0%,
      100% {
        opacity: 1
      }

      50% {
        opacity: .4
      }
    }

    .skel {
      display: inline-block;
      background: #e5e7eb;
      border-radius: .375rem;
      animation: skel-pulse 1.5s ease-in-out infinite
    }

    /* KPI card */
    .kpi-card {
      padding: 14px 16px 12px;
      border-bottom: 1px solid #f1f5f9
    }

    .kpi-card:nth-child(5n) {
      border-right: none
    }

    .kpi-section {
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      overflow: hidden;
      margin-bottom: 24px
    }

    .kpi-section-hdr {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 14px 18px 12px;
      border-bottom: 3px solid currentColor;
      background: #fff
    }

    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      background: #fff
    }

    .kpi-card {
      padding: 14px 16px 12px;
      border-right: 1px solid #f1f5f9;
      border-bottom: 1px solid #f1f5f9;
      position: relative
    }

    .kpi-card:nth-child(5n) {
      border-right: none
    }

    /* Tooltip */
    .kpi-tip-wrap {
      position: relative;
      display: inline-flex
    }

    .kpi-tip-wrap .tip-box {
      display: none;
      position: absolute;
      top: calc(100% + 6px);
      left: 50%;
      transform: translateX(-50%);
      background: #1e293b;
      color: #f1f5f9;
      font-size: 11px;
      line-height: 1.4;
      padding: 7px 10px;
      border-radius: 7px;
      width: 190px;
      white-space: normal;
      z-index: 50;
      pointer-events: none;
      box-shadow: 0 4px 16px rgba(0, 0, 0, .18)
    }

    .kpi-tip-wrap:hover .tip-box {
      display: block
    }
  </style>

  <!-- ── Header ─────────────────────────────────────────────────────────────── -->
  <header class="bg-white border-b border-gray-100 px-8 py-4 flex justify-between items-center">
    <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">KPIs</h1>
  </header>

  <!-- ── Filter bar ─────────────────────────────────────────────────────────── -->
  <section class="bg-white border-b border-gray-200 px-8 py-3 flex flex-wrap items-center gap-3">
    <x-daterange-picker id="kpiDateRange" />

    <select id="kpiLocation"
      class="border border-gray-300 rounded px-4 py-1.5 text-sm bg-white focus:outline-none focus:border-emerald-500 shadow-sm font-medium text-gray-700">
      <option value="all">All Locations</option>
      <option value="0" selected>8 Mile</option>
    </select>

    <button id="kpiUpdateBtn"
      class="bg-white border border-emerald-500 text-emerald-600 px-5 py-1.5 rounded text-sm font-semibold hover:bg-emerald-50 transition shadow-sm">
      Update
    </button>

    <span id="kpiLoading" class="hidden text-xs text-slate-400 ml-2 flex items-center gap-1.5">
      <svg class="animate-spin w-3.5 h-3.5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none"
        viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
      </svg>
      Loading…
    </span>
  </section>

  <!-- ── Tabs ───────────────────────────────────────────────────────────────── -->
  <section class="px-8 bg-white border-b border-gray-200 flex gap-6 text-sm font-medium text-gray-500">
    <button class="kpi-tab-btn border-b-2 border-emerald-500 text-emerald-600 font-bold pb-3 pt-4"
      data-tab="main">Main</button>
    <button class="kpi-tab-btn border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4"
      data-tab="specialty">Specialty</button>
    <button class="kpi-tab-btn border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4"
      data-tab="providers">Providers</button>
    <button class="kpi-tab-btn border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4"
      data-tab="specialty-providers">Specialty Providers</button>
  </section>

  <!-- ── Tab: Main ─────────────────────────────────────────────────────────── -->
  <main id="tab-main" class="p-6 space-y-6 kpi-tab-content">

    <!-- Hygiene Section -->
    <div class="kpi-section">
      <div class="kpi-section-hdr text-emerald-700" style="border-color:#10b981">
        <span class="text-base font-extrabold tracking-tight">Hygiene</span>
      </div>
      <div class="kpi-grid" id="hygiene-grid">
        <!-- rendered by JS -->
      </div>
    </div>

    <!-- Doctor Section -->
    <div class="kpi-section">
      <div class="kpi-section-hdr text-indigo-700" style="border-color: rgb(107, 83, 215)">
        <span class="text-base font-extrabold tracking-tight">Doctor</span>
      </div>
      <div class="kpi-grid" id="doctor-grid">
        <!-- rendered by JS -->
      </div>
    </div>

    <!-- Office Section -->
    <div class="kpi-section">
      <div class="kpi-section-hdr text-teal-700" style="border-color: rgb(0, 194, 255)">
        <span class="text-base font-extrabold tracking-tight">Office</span>
      </div>
      <div class="kpi-grid" id="office-grid">
        <!-- rendered by JS -->
      </div>
    </div>

  </main>

  <!-- ── Other tabs (stubs) ─────────────────────────────────────────────────── -->
  <!-- ── Tab: Specialty ─────────────────────────────────────────────────────── -->
  <main id="tab-specialty" class="hidden p-6 space-y-6 kpi-tab-content">
    <!-- Endo Section -->
    <div class="kpi-section">
      <div class="kpi-section-hdr text-emerald-700" style="border-color:#10b981">
        <span class="text-base font-extrabold tracking-tight">Endo</span>
      </div>
      <div class="kpi-grid" id="endo-grid">
        <!-- rendered by JS -->
      </div>
    </div>

    <!-- Perio Section -->
    <div class="kpi-section">
      <div class="kpi-section-hdr text-indigo-700" style="border-color:#6366f1">
        <span class="text-base font-extrabold tracking-tight">Perio</span>
      </div>
      <div class="kpi-grid" id="perio-grid">
        <!-- rendered by JS -->
      </div>
    </div>
    <!-- Ortho Section -->
    <div class="kpi-section">
      <div class="kpi-section-hdr text-sky-700" style="border-color:#0ea5e9">
        <span class="text-base font-extrabold tracking-tight">Ortho</span>
      </div>
      <div class="kpi-grid" id="ortho-grid">
        <!-- rendered by JS -->
      </div>
    </div>

    <!-- OS Section -->
    <div class="kpi-section">
      <div class="kpi-section-hdr text-rose-700" style="border-color:#e11d48">
        <span class="text-base font-extrabold tracking-tight">OS (Oral Surgery)</span>
      </div>
      <div class="kpi-grid" id="os-grid">
        <!-- rendered by JS -->
      </div>
    </div>

    <!-- Pedo Section -->
    <div class="kpi-section">
      <div class="kpi-section-hdr text-amber-700" style="border-color:#d97706">
        <span class="text-base font-extrabold tracking-tight">Pedo</span>
      </div>
      <div class="kpi-grid" id="pedo-grid">
        <!-- rendered by JS -->
      </div>
    </div>
  </main>
  <!-- ── Tab: Providers ─────────────────────────────────────────────────────── -->
  <main id="tab-providers" class="hidden p-6 space-y-6 kpi-tab-content">

    <!-- Sub-tabs for Providers -->
    <div class="flex gap-6 border-b border-gray-200">
      <button class="kpi-subtab-btn pb-2 pt-2 border-b-2 font-bold text-gray-900 border-gray-900"
        data-subtab="doctor">Doctor</button>
      <button
        class="kpi-subtab-btn pb-2 pt-2 border-b-2 font-medium text-gray-500 border-transparent hover:text-gray-700"
        data-subtab="hygiene">Hygiene</button>
    </div>

    <!-- Controls -->
    <div class="flex justify-between items-center mt-4">
      <div class="flex">
        <button class="px-4 py-1.5 text-sm font-medium bg-green-200 text-green-900">Top 20%</button>
        <button class="px-4 py-1.5 text-sm font-medium bg-yellow-100 text-yellow-800">Mid Tier</button>
        <button class="px-4 py-1.5 text-sm font-medium bg-red-200 text-red-900">Bottom 20%</button>
      </div>
      <div class="flex gap-2">
        <div class="relative">
          <input type="text" placeholder="Search"
            class="pl-3 pr-8 py-1.5 border border-gray-300 rounded text-sm focus:outline-none">
          <i class="fa fa-search absolute right-3 top-1.5 text-gray-400" style="margin-top:2px;"></i>
        </div>
        <button
          class="border border-emerald-500 text-emerald-800 font-bold px-3 py-1.5 rounded text-sm hover:bg-emerald-50">Export
          CSV</button>
      </div>
    </div>

    <!-- Table Container -->
    <div class="overflow-x-auto ring-1 ring-gray-200 shadow rounded-sm mt-4">
      <table class="min-w-full divide-y divide-gray-200 text-sm" id="providers-table">
        <thead class="bg-white">
          <tr id="providers-thead-tr">
            <!-- Headers injected via JS -->
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200" id="providers-tbody">
          <!-- Rows will be injected here -->
        </tbody>
        <tfoot class="bg-gray-100 font-bold" id="providers-tfoot">
          <tr>
            <td colspan="2" class="p-4 text-right">Avg:</td>
            <!-- Avg injected via JS -->
          </tr>
          <tr>
            <td colspan="2" class="p-4 text-right text-gray-600">Total:</td>
            <!-- Total injected via JS -->
          </tr>
        </tfoot>
      </table>
    </div>

  </main>
  <div id="tab-specialty-providers" class="kpi-tab-content hidden">
    <div class="border-b border-gray-200 px-8 bg-gray-50 flex items-center justify-between">
      <ul class="flex space-x-8 mt-4" id="specialty-providers-tabs" aria-label="Tabs">
        <li>
          <button onclick="switchSpecialtyProviderTab('endo')" id="tab-btn-endo"
            class="specialty-tab-btn whitespace-nowrap py-3 px-1 border-b-2 border-indigo-500 font-medium text-sm text-indigo-600 focus:outline-none">Endo</button>
        </li>
        <li>
          <button onclick="switchSpecialtyProviderTab('perio')" id="tab-btn-perio"
            class="specialty-tab-btn whitespace-nowrap py-3 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">Perio</button>
        </li>
        <li>
          <button onclick="switchSpecialtyProviderTab('ortho')" id="tab-btn-ortho"
            class="specialty-tab-btn whitespace-nowrap py-3 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">Ortho</button>
        </li>
        <li>
          <button onclick="switchSpecialtyProviderTab('os')" id="tab-btn-os"
            class="specialty-tab-btn whitespace-nowrap py-3 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">Os</button>
        </li>
        <li>
          <button onclick="switchSpecialtyProviderTab('pedo')" id="tab-btn-pedo"
            class="specialty-tab-btn whitespace-nowrap py-3 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">Pedo</button>
        </li>
      </ul>
      <button onclick="exportSpecialtyProvidersCSV()"
        class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-sm transition-colors duration-150">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
          <polyline points="7 10 12 15 17 10"></polyline>
          <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
        Export CSV
      </button>
    </div>

    <div class="w-full overflow-hidden bg-white mt-0.5">
      <div class="overflow-x-auto overflow-y-auto" style="max-height: calc(100vh - 250px);">
        <table class="w-full text-sm text-left border-collapse" style="min-width: max-content;">
          <thead>
            <tr id="specialty-providers-thead-tr" class="bg-white"></tr>
          </thead>
          <tbody id="specialty-providers-tbody" class="divide-y divide-gray-100 break-words whitespace-normal"></tbody>
          <tfoot id="specialty-providers-tfoot" class="bg-gray-50 divide-y divide-gray-200"></tfoot>
        </table>
      </div>
    </div>
  </div>

  <!-- FontAwesome (free CDN) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    crossorigin="anonymous">

  <script>
    var _kpiBase = "{{ url('') }}";
    var _kpiStart, _kpiEnd;

    /* ── Card definitions ──────────────────────────────────────────────────── */
    var HYGIENE_CARDS = [
      { k: 'perio_pct', label: '1. Perio %', fmt: 'pct', tip: 'The number of perio appointments (D4341, D4342, D4910, D4346, D4355) divided by the sum of all hygiene appointments (D1110, D1120, D4341, D4342, D4910, D4346, D4355) multiplied by 100.' },
      { k: 'fluoride_per_day', label: '2. # of Fluoride app. per day', fmt: 'dec1', tip: 'The sum of unique patients that received a Fluoride application (1206, 1208) divided by the number of Hygiene Working Days.' },
      { k: 'avg_prod_per_day', label: '3. Avg. Prod. per Day', fmt: 'money', tip: 'The total production filtered by Hygienist and divided by the number of working days (any day with production within the date range.)' },
      { k: 'avg_prod_per_prov_day', label: '4. Avg Production per Provider Per Day', fmt: 'money', tip: 'The total production filtered by Hygiene and divided by the number of working days by provider (days with > $0 in Gross Production are considered working days, excludes adjustments and deleted entries).' },
      { k: 'prod_per_visit', label: '5. Production per patient visit', fmt: 'money', tip: 'The total production by a Hygiene Provider divided by the total number of Hygiene Patient Visits.' },
      { k: 'fmx_per_day', label: '6. Avg. Fmx per day', fmt: 'dec1', tip: 'Sum of unique patients that received an FMX (code 0210) Divided by the number of Hygiene Working Days.' },
      { k: 'srp_per_day', label: '7. Avg. SRP per day', fmt: 'dec2', tip: 'The total of unique patient visits that received an SRP (codes 4341, 4342) Divided by the Total number of Hygiene Working Days.' },
      { k: 'visits_per_day', label: '8. Number of visits per day', fmt: 'dec1', tip: 'Number of Visits Per Day' },
      { k: 'reappt', label: '9. Hygiene Reappointment', fmt: 'pct', tip: 'Patients seen by within the date range for a hygiene visit (D1110, D1120, D4341, D4342, D4910, D4346, D4355) that also have a future hygiene appointment scheduled.' },
      { k: 'perio_reappt', label: '10. Perio Reappointment', fmt: 'pct', tip: 'Patients seen by within the date range for a Perio visit (codes 4341, 4342, 4910) that also have a future Perio appointment scheduled.' },
      { k: 'adult_retention_12m', label: '11. Adult Hygiene Retention (12 months)', fmt: 'pct', tip: 'Patients seen within the date range that were also seen for a previous appointment within the last 12months. Includes procedure codes D1110 & D4910.' },
      { k: 'adult_retention_6m', label: '12. Adult Hygiene Retention (6 months)', fmt: 'pct', tip: 'Patients seen within the date range that were also seen for a previous appointment within the last 6 months. Includes procedure codes D1110 & D4910.' },
      { k: 'child_retention_12m', label: '13. Child Hygiene Retention (12 months)', fmt: 'pct', tip: 'Patients under the age of 18 who were seen within the date range that were also seen for a previous appointment within the last 12 months. Filtered by service codes: D1120.' },
      { k: 'child_retention_6m', label: '14. Child Hygiene Retention (6 months)', fmt: 'pct', tip: 'Patients under the age of 18 who were seen within the date range that were also seen for a previous appointment within the last 6 months. Filtered by service codes: D1120.' },
      { k: 'sealants', label: '15. Sealants', fmt: 'int', tip: 'The sum of all sealant procedures within the date range. (D1351)' },
      { k: 'whitening', label: '16. Whitening Procedures', fmt: 'int', tip: 'The sum of whitening procedures within the date range. (D9972, D9973, D9974, D9975)' },
      { k: 'antimicrobial', label: '17. Antimicrobial Placement', fmt: 'int', tip: 'The sum of all PerioChip or Arestin procedures within the date range. (D4381)' },
      { k: 'prod_per_proc', label: '18. Hygiene Production per Procedure', fmt: 'money', tip: 'Total Production of Hygienists in a date range divided by Number of Hygiene codes. \'D1110\', \'D1120\', \'D4341\', \'D4342\', \'D4910\', \'D4346\', \'D4355\'.' },
      { k: 'visits_with_tx_pct', label: '19. % of Hygiene Visits with TX Plan', fmt: 'pct', tip: 'The total count of all hygiene visits within the date range divided by the number of hygiene visits that received a new or refreshed Tx plan, multiplied by 100.' },
      { k: 'tx_plans_per_day', label: '20. # of Tx plan per Day', fmt: 'dec1', tip: 'The total count of Tx plans presented within the date range divided by the total number of Hygiene Working Days.' },
      { k: 'avg_prod_per_hour', label: '21. Average Hygiene Production per Hour', fmt: 'money', tip: 'Displays the average production per hour for hygiene providers' },
      { k: 'case_acceptance', label: '22. Case Acceptance Rate', fmt: 'pct', tip: 'Displays the average production per hour for hygiene providers based on the office daily schedule setup.' },
    ];

    var DOCTOR_CARDS = [
      { k: 'case_acceptance_same_day', label: '1. Case Acceptance – Same Day', fmt: 'pct', tip: 'Displays the percentage of treatment plans that were completed same day. Filtered by General providers' },
      { k: 'case_acceptance_rate', label: '2. Case Acceptance Rate', fmt: 'pct', tip: 'Displays the percentage of treatment that is accepted for the selected date range. Accepted = Completed or Scheduled.' },
      { k: 'new_pt_tx_dollars', label: '3. $ New Patients Receiving Treatment Plans', fmt: 'money', tip: 'Displays the average $ amount of treatment plans presented to New Patients.' },
      { k: 'existing_pt_tx_dollars', label: '4. $ Existing Patients Receiving Treatment Plans', fmt: 'money', tip: 'Displays the total $ amount of treatment plans created for existing patients within the selected date range.' },
      { k: 'avg_apt_time_mins', label: '5. Avg Time per Doctor Appointment (minutes)', fmt: 'dec2', tip: 'Displays the average appointment time (in minutes) for all patients seen by a Doctor within the date range selected.' },
      { k: 'avg_prod_per_hour', label: '6. Average Doctor Production per Hour', fmt: 'money', tip: 'Displays the average production per hour for doctor providers' },
      { k: 'avg_prod_per_apt', label: '7. Average Production per Doctor Appointment', fmt: 'money', tip: 'Displays the average scheduled production per doctor appointment.' },
      { k: 'same_day_tx_per_new_pt', label: '8. Same Day Treatment per New Patient', fmt: 'money', tip: 'Displays the average $ amount of New Patient treatment plans that were completed same day.' },
      { k: 'avg_prod_per_prov_day', label: '9. Avg Production per Provider Per Day', fmt: 'money', tip: 'Displays the average production $ per Provider per Day.' },
      { k: 'avg_tx_per_existing_pt', label: '10. Avg. Treatment plan ($) per Existing Pts.', fmt: 'money', tip: 'Displays the average treatment plan $ per existing patient.' },
      { k: 'avg_tx_per_new_pt', label: '11. Avg. Treatment plan ($) per New Pts.', fmt: 'money', tip: 'Displays the average treatment plan $ per new patient.' },
      { k: 'pct_new_pt_with_tx', label: '12. % of new patients w/ treatment plans', fmt: 'pct', tip: 'Displays the percentage of New Patients that received a treatment plan within the date range.' },
      { k: 'pct_existing_pt_with_tx', label: '13. % of existing patients w/ treatment plans', fmt: 'pct', tip: 'Displays the percentage of existing patient that received a new or refreshed treatment plan.' },
      { k: 'reappt', label: '14. Doctor Reappoint', fmt: 'pct', tip: 'Displays the percentage of patients that were seen by a General Doctor and also have a future appointment.' },
      { k: 'prod_per_exam', label: '15. Doctor Production per Exam', fmt: 'money', tip: 'Displays the average production $ per exam for General Doctors.' },
      { k: 'total_production', label: '16. Total Doctor Production', fmt: 'money', tip: 'Total production of doctor providers.' },
    ];

    var OFFICE_CARDS = [
      { k: 'patient_retention', label: '1. Patient Retention', fmt: 'pct', tip: 'Displays the percentage of patients who were seen for an exam within the last 18 months compared to the active patient base. Active Patient = completed procedure code within last 36 months.' },
      { k: 'tx_plans_per_day', label: '2. # of Treatment Plans per Day', fmt: 'dec1', tip: 'The number of treatment plans presented (must be greater than $10) divided by the number of working days.' },
      { k: 'co_pay_collection', label: '3. Co-Pay Collection', fmt: 'pct', tip: 'The Total amount collected from a patient divided by what was expected to be collected from a patient (based on the insurance estimate), multiplied by 100.' },
      { k: 'unscheduled_tx', label: '4. Unscheduled Tx $', fmt: 'money', tip: 'Displays the total $ amount of unscheduled treatment during the selected date range' },
      { k: 'new_pt_fmx_pct', label: '5. New Patients Fmx %', fmt: 'pct', tip: 'Displays the percentage of new patients receiving a full mouth series x ray' },
      { k: 'no_show_rate', label: '6. No Show Rate', fmt: 'pct', tip: 'Displays the percentage of patients that missed their appointment or cancelled their appointment within 24 hours.' },
      { k: 'reactivation_list', label: '7. Patient Reactivation List', fmt: 'comma', tip: 'Displays the number of patients that have not been seen 12 months prior to the date range selected' },
      { k: 'patient_attrition', label: '8. Patient Attrition', fmt: 'comma', tip: 'The # of patients that became inactive during the selected timeframe.' },
      { k: 'patient_growth', label: '9. Patient Growth', fmt: 'signed', tip: 'The number of new patient visits minus the number of patients deactivated.' },
      { k: 'active_patients', label: '10. # of Active Patients', fmt: 'comma', tip: 'Displays the # of any patient that had a completed procedure within the last 18 months and has an active patient status. Excludes broken appointment codes (D9986 & D9987).' },
      { k: 'active_in_recare_pct', label: '11. % of Active Patients in Hygiene Recare', fmt: 'pct', tip: 'Displays the percentage of active patients that are in hygiene recare. Any patient seen within the last 12 months prior to the selected date range and had one of the following procedure codes completed: D4910, D1110, D1120.' },
    ];

    var ENDO_CARDS = [
      { k: 'total_production', label: '1. Total Production', fmt: 'money', tip: 'The total production filtered by Endo.' },
      { k: 'production_per_day', label: '2. Production per Day', fmt: 'money', tip: 'The total production filtered by Endo and divided by the number of working days by Endo providers.' },
      { k: 'total_consults', label: '3. Total Consults', fmt: 'int', tip: 'The total number of Consults (D9310) per patient in the given date range. Filtered by Endo providers.' },
      { k: 'consults_per_day', label: '4. Consults per day', fmt: 'dec2', tip: 'The total number of Consults (D9310) per patient in the given date range divided by the number of working days or days with production. Filtered by Endo providers.' },
      { k: 'retreats_count', label: '5. Re-treats Count', fmt: 'int', tip: 'Total Re-treats count. Filtered by service codes: D3346, D3347, D3348 and Endo providers.' },
      { k: 'rct_count', label: '6. RCT Count', fmt: 'int', tip: 'Total RCT count. Filtered by service codes: D3310, D3320, D3330 and Endo providers.' },
      { k: 'obstruction_count', label: '7. Obstruction Count', fmt: 'int', tip: 'Total Obstruction count. Filtered by service code: D3331 and Endo providers.' },
      { k: 'biopure_count', label: '8. Biopure Count', fmt: 'int', tip: 'Total count of patients that had Biopure code D3000 or D3999. Filtered by Endo providers.' },
      { k: 'patient_visits', label: '9. Patients Visits', fmt: 'int', tip: 'The number of patients with an associated procedure. Filtered by Endo providers.' },
    ];

    var PERIO_CARDS = [
      { k: 'total_production', label: '1. Total Production', fmt: 'money', tip: 'The total production filtered by Perio.' },
      { k: 'production_per_day', label: '2. Production per Day', fmt: 'money', tip: 'The total production filtered by Perio and divided by the number of working days by Perio providers.' },
      { k: 'total_consults', label: '3. Total Consults', fmt: 'int', tip: 'The total number of Consults (D9310) per patient in the given date range. Filtered by Perio providers.' },
      { k: 'consults_per_day', label: '4. Consults per day', fmt: 'dec2', tip: 'The total number of Consults (D9310) per patient in the given date range divided by the number of working days or days with production. Filtered by Perio providers.' },
      { k: 'treatment_plan_per_exam', label: '5. Treatment plan per Exam', fmt: 'money', tip: 'Total dollar amount on treatment plan divided by total exams(D0120, D0140, D0145, D0150, D0160, D0170, D0180) that day by Perio providers.' },
      { k: 'implant_placement_count', label: '6. Implant Placement Count', fmt: 'int', tip: 'The total implant placement (D6010) count. Filtered by Perio providers.' },
      { k: 'implant_placement_dollars', label: '7. Implant Placement $', fmt: 'money', tip: 'The total dollar amount of implant placement with service code D6010. Filtered by Perio providers.' },
      { k: 'sedations_dollars', label: '8. Sedations $', fmt: 'money', tip: 'The total dollar amount of sedation in the given date range. Filtered by sedation codes: D9222, D9223, D9239, D9243, D9248. Filtered by Perio providers.' },
      { k: 'patient_visits', label: '9. Patients Visits', fmt: 'int', tip: 'The number of patients with an associated procedure. Filtered by Perio providers.' },
      { k: 'perio_codes_dollars', label: '10. Perio Codes $', fmt: 'money', tip: 'The total dollar amount of Perio codes in the given date range. Filtered by perio codes: D4000 - D4999. Filtered by Perio providers.' },
    ];

    var ORTHO_CARDS = [
      { k: 'total_production', label: '1. Total Production', fmt: 'money', tip: 'The total production filtered by Ortho.' },
      { k: 'production_per_day', label: '2. Production per Day', fmt: 'money', tip: 'The total production filtered by Ortho and divided by the number of working days by Ortho providers.' },
      { k: 'total_consults', label: '3. Total Consults', fmt: 'int', tip: 'The total number of Consults (D9310) per patient in the given date range. Filtered by Ortho providers.' },
      { k: 'consults_per_day', label: '4. Consults per day', fmt: 'dec2', tip: 'The total number of Consults (D9310) per patient in the given date range divided by the number of working days or days with production. Filtered by Ortho providers.' },
      { k: 'total_active_patients_seen', label: '5. Total Active Patients Seen', fmt: 'int', tip: 'Total active patients seen with service codes D8670 and D8670A. Filtered by Ortho Provider.' },
      { k: 'active_patients_seen_per_day', label: '6. Active Patients seen per day', fmt: 'dec2', tip: 'Total active patients seen with service codes D8670 and D8670A divided by the total number of working days. Filtered by Ortho providers.' },
      { k: 'appliances_count', label: '7. Appliances Count', fmt: 'int', tip: 'The total count of Appliances codes D8220 and D8210. Filtered by Ortho providers.' },
      { k: 'phase_1_count', label: '8. Phase 1 Count', fmt: 'int', tip: 'The total count of Phase 1. Filtered by Ortho providers.' },
      { k: 'comprehensive_starts_count', label: '9. Comprehensive Starts Count', fmt: 'int', tip: 'Total Comprehensive starts count. Filtered by service codes: D8070, D8080 and D8090 and Ortho providers.' },
      { k: 'debonds_count', label: '10. Debonds Count', fmt: 'int', tip: 'Total Debond count. Filtered by service codes: D8999C and Ortho providers.' },
      { k: 'conversion', label: '11. Conversion (Starts/Consults)', fmt: 'pct', tip: 'Total count of Starts (D8010, D8020, D8030, D8040, D8050, D8060, D8070, D8080, D8090) divided by total number of Consult(D9310). Filtered by Ortho providers.' },
      { k: 'invisalign_starts_count', label: '12. Invisalign Starts Count', fmt: 'int', tip: 'The total count of Invisalign codes D8090 and D8080. Filtered by Ortho providers.' },
    ];

    var OS_CARDS = [
      { k: 'total_production', label: '1. Total Production', fmt: 'money', tip: 'The total production filtered by OS.' },
      { k: 'production_per_day', label: '2. Production per Day', fmt: 'money', tip: 'The total production filtered by OS and divided by the number of working days by OS providers.' },
      { k: 'total_consults', label: '3. Total Consults', fmt: 'int', tip: 'The total number of Consults (D9310) per patient in the given date range. Filtered by OS providers.' },
      { k: 'consults_per_day', label: '4. Consults per day', fmt: 'dec2', tip: 'The total number of Consults (D9310) per patient in the given date range divided by the number of working days or days with production. Filtered by OS providers.' },
      { k: 'treatment_plan_per_exam', label: '5. Treatment Plan Per Exam', fmt: 'money', tip: 'Total dollar amount on treatment plan divided by total exams (D0120, D0140, D0145, D0150, D0160, D0170, D0180) that day by OS providers.' },
      { k: 'implant_placement_count', label: '6. Implant placement Count', fmt: 'int', tip: 'The total implant placement (D6010) count. Filtered by OS providers.' },
      { k: 'implant_placement_dollars', label: '7. Implant Placement $', fmt: 'money', tip: 'The total dollar amount of implant placement with service code D6010. Filtered by OS providers.' },
      { k: 'sedations_dollars', label: '8. Sedations $', fmt: 'money', tip: 'The total dollar amount of sedation in the given date range. Filtered by sedation codes: D9222, D9223, D9239, D9243, D9248. Filtered by OS providers.' },
      { k: 'extractions_dollars', label: '9. Extractions $', fmt: 'money', tip: 'The total dollar amount of extraction in the given date range. Filtered by extraction codes: D7140, D7210, D7220, D7230, D7240, D7241, D7250. Filtered by OS providers.' },
    ];

    var PEDO_CARDS = [
      { k: 'stainless_steel_crowns', label: '1. Stainless Steel Crowns', fmt: 'int', tip: 'Total SSC count. Filtered by service codes D2929, D2930, D2931, D2932, D2933, D2934 and Pedo providers.' },
      { k: 'pulpotomies', label: '2. Pulpotomies', fmt: 'int', tip: 'Total Pulpotomies count. Filtered by service code D3220 and Pedo providers.' },
      { k: 'fillings', label: '3. Fillings', fmt: 'int', tip: 'Total Fillings count. Filtered by service code: D3230, D3240, D2330, D2331, D2332, D2335, D2391, D2392, D2393, D2394. Filtered by Pedo providers.' },
      { k: 'space_maintainer', label: '4. Space Maintainer', fmt: 'int', tip: 'Total Space Maintainer count. Filtered by service code: D1510, D1515, D1516, D1517, D1520, D1525. Filtered by Pedo providers.' },
      { k: 'total_extractions', label: '5. Total Extractions', fmt: 'int', tip: 'Total Ext count. Filtered by service code: D7110, D7111, D7140. Filtered by Pedo providers.' },
      { k: 'sealants', label: '6. Sealants', fmt: 'int', tip: 'Total Sealants count. Filtered by service code: D1351, 01351. Filtered by Pedo providers.' },
      { k: 'sedations', label: '7. Sedations', fmt: 'money', tip: 'The total dollar amount of sedation in the given date range. Filtered by extraction codes: D9220, D9221, D9230, D9612, D9243, D9239. Filtered by Pedo providers.' },
      { k: 'nitrous_sedation', label: '8. Nitrous Sedation', fmt: 'int', tip: 'Total Nitrous Sed count. Filtered by service code D9230 and Pedo providers.' },
      { k: 'total_crowns', label: '9. Total Crowns', fmt: 'int', tip: 'Total Crowns completed by Pedo toggled Providers.' },
      { k: 'prophylaxis', label: '10. Prophylaxis', fmt: 'int', tip: 'Total Cleanings (D1110/D1120/D4910). Filtered by Pedo providers.' },
      { k: 'fluoride_treatments', label: '11. Fluoride Treatments', fmt: 'int', tip: 'Total Fluoride (D1208/D1206). Filtered by Pedo providers.' },
      { k: 'case_acceptance_same_day', label: '12. Case Acceptance - Same Day', fmt: 'pct', tip: 'All patients that have production added that day or have scheduled production that day divided by all patients that got a Tx plan that day.' },
      { k: 'case_acceptance_rolling_90_days', label: '13. Case Acceptance - Rolling 90 Days', fmt: 'pct', tip: 'All patients that had production added or scheduled within 90 days from the day it was presented divided by all patients that got a Tx plan that day.' },
      { k: 'total_production', label: '14. Total Production', fmt: 'money', tip: 'The total production filtered by Pedo Providers.' },
      { k: 'production_per_day', label: '15. Production per Day', fmt: 'money', tip: 'The total production filtered by Pedo and divided by the number of working days by Pedo providers.' },
      { k: 'total_consults', label: '16. Total Consults', fmt: 'int', tip: 'The total number of Consults (D9310) per patient in the given date range. Filtered by Pedo providers.' },
      { k: 'consults_per_day', label: '17. Consults per day', fmt: 'dec2', tip: 'The total number of Consults (D9310) per patient in the given date range divided by the number of working days. Filtered by Pedo providers.' },
      { k: 'patient_visits', label: '18. Patient Visits', fmt: 'int', tip: 'Patients visits filtered by Pedo provider.' },
      { k: 'patients_per_day', label: '19. Patients per Day', fmt: 'dec1', tip: 'Average number of patient visits per day for a Pedo toggled provider.' },
      { k: 'total_working_days', label: '20. Total Working Days', fmt: 'int', tip: 'Total # of Working Pedo Days (Any day with more than $100 in production by a Pedo toggled provider).' },
      { k: 'production_per_patient', label: '21. Production per Patient', fmt: 'money', tip: 'The total production filtered by Pedo and divided by the number of Patient Visits by Pedo providers.' },
    ];

    /* ── Formatting ────────────────────────────────────────────────────────── */
    function fmtKpi(val, fmt) {
      if (val === null || val === undefined) return '—';
      var n = parseFloat(val) || 0;
      switch (fmt) {
        case 'pct': return (n % 1 === 0 ? n : n.toFixed(2)) + '%';
        case 'money':
          var abs = Math.abs(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
          return n < 0 ? '$ (' + abs + ')' : '$ ' + abs;
        case 'dec1': return n.toFixed(1);
        case 'dec2': return n.toFixed(2);
        case 'int': return Math.round(n).toLocaleString();
        case 'comma': return Math.round(n).toLocaleString();
        case 'signed': return (n >= 0 ? '' : '') + Math.round(n).toLocaleString();
        default: return String(val);
      }
    }

    /* ── Render grids ──────────────────────────────────────────────────────── */
    function renderGrid(containerId, cards, data) {
      var $c = document.getElementById(containerId);
      if (!$c) return;
      var html = '';
      cards.forEach(function (card) {
        var val = data ? data[card.k] : null;
        html += '<div class="kpi-card">';
        html += '<div class="flex items-start justify-between mb-2">';
        html += '<span class="text-xs text-gray-500 leading-tight pr-2">' + escHtml(card.label) + '</span>';
        html += '<span class="kpi-tip-wrap flex-shrink-0">'
          + '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" '
          + 'stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="cursor-default">'
          + '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line>'
          + '<line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
          + '<div class="tip-box">' + escHtml(card.tip) + '</div>'
          + '</span>';
        html += '</div>';
        html += '<div class="flex items-end justify-between">';
        if (val === null || val === undefined) {
          html += '<span class="skel" style="width:80px;height:22px"></span>';
        } else {
          html += '<span class="text-[17px] font-extrabold text-gray-900 tabular-nums leading-tight">' + fmtKpi(val, card.fmt) + '</span>';
        }
        html += '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" '
          + 'stroke="#d1d5db" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">'
          + '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>';
        html += '</div>';
        html += '</div>';
      });
      $c.innerHTML = html;
    }

    function escHtml(s) {
      return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function showSkeletons() {
      renderGrid('hygiene-grid', HYGIENE_CARDS, null);
      renderGrid('doctor-grid', DOCTOR_CARDS, null);
      renderGrid('office-grid', OFFICE_CARDS, null);
      renderGrid('endo-grid', ENDO_CARDS, null);
      renderGrid('perio-grid', PERIO_CARDS, null);
      renderGrid('ortho-grid', ORTHO_CARDS, null);
      renderGrid('os-grid', OS_CARDS, null);
      renderGrid('pedo-grid', PEDO_CARDS, null);
    }

    /* ── Data fetch — 3 parallel independent requests ──────────────────────── */
    var _kpiPending = 0;

    function fetchSection(path, gridId, cards, start, end) {
      var qs = '?start_date=' + start + '&end_date=' + end;
      _kpiPending++;
      fetch(_kpiBase + path + qs)
        .then(function (r) { return r.json(); })
        .then(function (d) {
          renderGrid(gridId, cards, d);
        })
        .catch(function () {
          renderGrid(gridId, cards, {});
        })
        .finally(function () {
          _kpiPending--;
          if (_kpiPending === 0) {
            document.getElementById('kpiLoading').classList.add('hidden');
          }
        });
    }

    function fetchKpis(start, end) {
      _kpiStart = start;
      _kpiEnd = end;
      _kpiPending = 0;
      showSkeletons();
      _kpiProvidersData = { doctor: null, hygiene: null };
      var el = document.getElementById('kpiLoading');
      if (el) el.classList.remove('hidden');

      fetchSection('/kpis/hygiene', 'hygiene-grid', HYGIENE_CARDS, start, end);
      fetchSection('/kpis/doctor', 'doctor-grid', DOCTOR_CARDS, start, end);
      fetchSection('/kpis/office', 'office-grid', OFFICE_CARDS, start, end);
      fetchSection('/kpis/endo', 'endo-grid', ENDO_CARDS, start, end);
      fetchSection('/kpis/perio', 'perio-grid', PERIO_CARDS, start, end);
      fetchSection('/kpis/ortho', 'ortho-grid', ORTHO_CARDS, start, end);
      fetchSection('/kpis/os', 'os-grid', OS_CARDS, start, end);
      fetchSection('/kpis/pedo', 'pedo-grid', PEDO_CARDS, start, end);

      fetchProvidersSection('/kpis/doctor-providers', 'doctor', start, end);
      fetchProvidersSection('/kpis/hygiene-providers', 'hygiene', start, end);

      fetchSpecialtyProvidersSection('/kpis/endo-providers', 'endo', start, end);
      fetchSpecialtyProvidersSection('/kpis/perio-providers', 'perio', start, end);
      fetchSpecialtyProvidersSection('/kpis/ortho-providers', 'ortho', start, end);
      fetchSpecialtyProvidersSection('/kpis/os-providers', 'os', start, end);
      fetchSpecialtyProvidersSection('/kpis/pedo-providers', 'pedo', start, end);
    }

    /* ── Render Providers Table ────────────────────────────────────────────── */
    var currentProviderSubtab = 'doctor';
    var _kpiProvidersData = { doctor: null, hygiene: null };

    var currentSpecialtyProviderSubtab = 'endo';
    var _kpiSpecialtyProvidersData = { endo: null, perio: null, ortho: null, os: null, pedo: null };

    // Function to fetch specialty providers
    function fetchSpecialtyProvidersSection(path, type, start, end) {
      var qs = '?start_date=' + start + '&end_date=' + end;
      _kpiPending++;
      fetch(_kpiBase + path + qs)
        .then(function (r) { return r.json(); })
        .then(function (d) { _kpiSpecialtyProvidersData[type] = d; })
        .catch(function () { _kpiSpecialtyProvidersData[type] = null; })
        .finally(function () {
          _kpiPending--;
          if (_kpiPending === 0) {
            var el = document.getElementById('kpiLoading');
            if (el) el.classList.add('hidden');
            if (!document.getElementById('tab-specialty-providers').classList.contains('hidden')) {
              renderSpecialtyProvidersTable();
            }
          }
        });
    }

    function fetchProvidersSection(path, type, start, end) {
      var qs = '?start_date=' + start + '&end_date=' + end;
      _kpiPending++;
      fetch(_kpiBase + path + qs)
        .then(function (r) { return r.json(); })
        .then(function (d) { _kpiProvidersData[type] = d; })
        .catch(function () { _kpiProvidersData[type] = null; })
        .finally(function () {
          _kpiPending--;
          if (_kpiPending === 0) {
            var el = document.getElementById('kpiLoading');
            if (el) el.classList.add('hidden');
            if (!document.getElementById('tab-providers').classList.contains('hidden')) {
              renderProvidersTable();
            }
          }
        });
    }

    function switchSpecialtyProviderTab(tabName) {
      currentSpecialtyProviderSubtab = tabName;
      document.querySelectorAll('.specialty-tab-btn').forEach(function (btn) {
        btn.classList.add('border-transparent', 'text-gray-500');
        btn.classList.remove('border-indigo-500', 'text-indigo-600');
      });
      document.getElementById('tab-btn-' + tabName).classList.remove('border-transparent', 'text-gray-500');
      document.getElementById('tab-btn-' + tabName).classList.add('border-indigo-500', 'text-indigo-600');
      renderSpecialtyProvidersTable();
    }

    function renderSpecialtyProvidersTable() {
      var cardsKey = currentSpecialtyProviderSubtab.toUpperCase() + '_CARDS';
      var cards = window[cardsKey] || [];
      var $tr = document.getElementById('specialty-providers-thead-tr');
      if (!$tr) return;

      var thHtml = '<th class="px-4 py-5 text-left font-bold text-gray-900 border-r border-gray-100 bg-white sticky left-0 z-10 w-32 border-b" style="min-width: 150px; box-shadow: 2px 0 5px rgba(0,0,0,0.03);">Location</th>';
      thHtml += '<th class="px-4 py-5 text-left font-bold text-gray-900 border-r border-gray-100 bg-white sticky left-[150px] z-10 w-48 border-b" style="min-width: 150px; box-shadow: 2px 0 5px rgba(0,0,0,0.03);">Provider</th>';

      cards.forEach(function (card, idx) {
        var isFirstCard = idx === 0;
        var pl = isFirstCard ? 'padding-left: 1.5rem;' : '';
        thHtml += '<th class="px-4 py-5 text-center font-bold text-gray-800 border-r border-gray-100 align-top" style="vertical-align: top; ' + pl + ' max-width: 180px; min-width: 140px;">';
        thHtml += '<div class="flex items-center justify-center gap-1.5 mb-1 h-full h-12 overflow-hidden">';
        thHtml += '<span class="text-xs break-words whitespace-normal leading-tight text-center">' + escHtml(card.label) + '</span>';

        // Tooltip
        thHtml += '<span class="kpi-tip-wrap inline-flex items-center" style="transform: translateY(1px);">';
        thHtml += '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="cursor-default"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
        thHtml += '<div class="tip-box font-normal text-left" style="width: 260px; left: 50%; transform: translateX(-50%); top: 100%; margin-top: 8px;">' + escHtml(card.tip) + '</div>';
        thHtml += '</span>';

        thHtml += '</div>';
        thHtml += '</th>';
      });
      $tr.innerHTML = thHtml;

      var dataCache = _kpiSpecialtyProvidersData[currentSpecialtyProviderSubtab];
      var $tbody = document.getElementById('specialty-providers-tbody');
      var $tfoot = document.getElementById('specialty-providers-tfoot');

      if (!dataCache) {
        $tbody.innerHTML = '<tr><td colspan="100%" class="p-8 text-center text-gray-400">Loading data...</td></tr>';

        var avgTrL = '<td colspan="2" class="p-3 pr-4 text-right border-r border-white font-bold bg-gray-200 sticky left-0 z-10 box-shadow-none">Avg:</td>';
        var totTrL = '<td colspan="2" class="p-3 pr-4 text-right border-r border-white font-bold text-gray-500 bg-gray-200 sticky left-0 z-10 box-shadow-none">Total:</td>';
        cards.forEach(function () {
          avgTrL += '<td class="p-3 border-r border-white"></td>';
          totTrL += '<td class="p-3 border-r border-white"></td>';
        });
        $tfoot.innerHTML = '<tr>' + avgTrL + '</tr><tr>' + totTrL + '</tr>';
        return;
      }

      var html = '';
      var provs = dataCache.providers || [];
      if (provs.length === 0) {
        $tbody.innerHTML = '<tr><td colspan="100%" class="p-8 text-center text-gray-400">No data found for this period.</td></tr>';
      } else {
        provs.forEach(function (row) {
          html += '<tr>';
          html += '<td class="p-3 px-4 border-b border-r border-gray-100 sticky left-0 z-10 bg-white" style="box-shadow: 2px 0 5px rgba(0,0,0,0.03);"><div class="text-sm font-medium text-gray-900">' + escHtml(row.Location) + '</div></td>';
          html += '<td class="p-3 px-4 border-b border-r border-gray-100 sticky left-[150px] z-10 bg-white" style="box-shadow: 2px 0 5px rgba(0,0,0,0.03);"><div class="text-sm text-gray-700 whitespace-nowrap">' + escHtml(row.Provider) + '</div></td>';
          cards.forEach(function (card, idx) {
            var v = row[card.k];
            var isFirstCard = idx === 0;
            var pl = isFirstCard ? 'padding-left: 1.5rem;' : '';
            html += '<td class="p-3 border-b border-r border-gray-100 text-center" style="' + pl + '">';
            if (v === undefined || v === null) {
              html += '<span class="text-gray-300">-</span>';
            } else {
              html += '<span class="text-[15px] tabular-nums text-gray-900">' + fmtKpi(v, card.fmt) + '</span>';
            }
            html += '</td>';
          });
          html += '</tr>';
        });
        $tbody.innerHTML = html;
      }

      var avgTr = '<td colspan="2" class="p-3 pr-4 text-right border-r border-white font-bold bg-gray-200 sticky left-0 z-10 box-shadow-none">Avg:</td>';
      var totTr = '<td colspan="2" class="p-3 pr-4 text-right border-r border-white font-bold text-gray-500 bg-gray-200 sticky left-0 z-10 box-shadow-none">Total:</td>';
      cards.forEach(function (card, idx) {
        var isFirstCard = idx === 0;
        var pl = isFirstCard ? 'padding-left: 1.5rem;' : '';
        var vAvg = (dataCache.avg && dataCache.avg[card.k] !== undefined) ? dataCache.avg[card.k] : null;
        var vTot = (dataCache.total && dataCache.total[card.k] !== undefined) ? dataCache.total[card.k] : null;

        avgTr += '<td class="p-3 border-r border-white text-center font-bold" style="' + pl + '">';
        avgTr += (vAvg === null) ? '-' : fmtKpi(vAvg, card.fmt);
        avgTr += '</td>';

        totTr += '<td class="p-3 border-r border-white text-center font-bold text-gray-600" style="' + pl + '">';
        totTr += (vTot === null) ? '-' : fmtKpi(vTot, card.fmt);
        totTr += '</td>';
      });

      $tfoot.innerHTML = '<tr>' + avgTr + '</tr><tr>' + totTr + '</tr>';
    }

    function renderProvidersTable() {
      var cards = currentProviderSubtab === 'doctor' ? DOCTOR_CARDS : HYGIENE_CARDS;
      var $tr = document.getElementById('providers-thead-tr');
      if (!$tr) return;

      var thHtml = '<th class="px-4 py-5 text-left font-bold text-gray-900 border-r border-gray-100 bg-white sticky left-0 z-10 w-32 border-b" style="min-width: 150px; box-shadow: 2px 0 5px rgba(0,0,0,0.03);">Location</th>';
      thHtml += '<th class="px-4 py-5 text-left font-bold text-gray-900 border-r border-gray-100 bg-white sticky left-[150px] z-10 w-48 border-b" style="min-width: 150px; box-shadow: 2px 0 5px rgba(0,0,0,0.03);">Provider</th>';

      cards.forEach(function (card, idx) {
        var isFirstCard = idx === 0;
        var pl = isFirstCard ? 'padding-left: 1.5rem;' : '';
        thHtml += '<th class="px-4 py-5 text-center font-bold text-gray-800 border-r border-gray-100 align-top" style="vertical-align: top; ' + pl + ' max-width: 180px; min-width: 140px;">';
        thHtml += '<div class="flex items-center justify-center gap-1.5 mb-1 h-full h-12 overflow-hidden">';
        thHtml += '<span class="text-xs break-words whitespace-normal leading-tight text-center">' + escHtml(card.label) + '</span>';

        // Tooltip
        thHtml += '<span class="kpi-tip-wrap inline-flex items-center" style="transform: translateY(1px);">';
        thHtml += '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="cursor-default"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
        thHtml += '<div class="tip-box font-normal text-left" style="width: 260px; left: 50%; transform: translateX(-50%); top: 100%; margin-top: 8px;">' + escHtml(card.tip) + '</div>';
        thHtml += '</span>';

        thHtml += '</div>';
        thHtml += '</th>';
      });
      $tr.innerHTML = thHtml;

      var dataCache = _kpiProvidersData[currentProviderSubtab];
      var $tbody = document.getElementById('providers-tbody');
      var $tfoot = document.getElementById('providers-tfoot');

      if (!dataCache) {
        $tbody.innerHTML = '<tr><td colspan="100%" class="p-8 text-center text-gray-400">Loading data...</td></tr>';

        var avgTrL = '<td colspan="2" class="p-3 pr-4 text-right border-r border-white font-bold bg-gray-200 sticky left-0 z-10 box-shadow-none">Avg:</td>';
        var totTrL = '<td colspan="2" class="p-3 pr-4 text-right border-r border-white font-bold text-gray-500 bg-gray-200 sticky left-0 z-10 box-shadow-none">Total:</td>';
        cards.forEach(function () {
          avgTrL += '<td class="p-3 border-r border-white"></td>';
          totTrL += '<td class="p-3 border-r border-white"></td>';
        });
        $tfoot.innerHTML = '<tr>' + avgTrL + '</tr><tr>' + totTrL + '</tr>';
        return;
      }

      var rowsHtml = '';
      if (dataCache.providers && dataCache.providers.length > 0) {
        dataCache.providers.forEach(function (prov) {
          var tr = '<tr class="group hover:bg-gray-50">';
          tr += '<td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-100 bg-white sticky left-0 z-[5] group-hover:bg-gray-50" style="box-shadow: 2px 0 5px rgba(0,0,0,0.03);">' + escHtml(prov.location) + '</td>';
          tr += '<td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-100 bg-white sticky left-[150px] z-[5] group-hover:bg-gray-50" style="box-shadow: 2px 0 5px rgba(0,0,0,0.03);">' + escHtml(prov.provider) + '</td>';
          cards.forEach(function (card, idx) {
            var isFirstCard = idx === 0;
            var pl = isFirstCard ? 'padding-left: 1.5rem;' : '';
            var val = prov[card.k];
            rowsHtml += '<td class="px-4 py-3 text-center whitespace-nowrap text-sm text-gray-700 border-r border-gray-100 bg-transparent" style="' + pl + '">' + fmtKpi(val, card.fmt) + '</td>';
          });
          rowsHtml += '</tr>';
        });
      } else {
        rowsHtml = '<tr><td colspan="100%" class="p-8 text-center text-gray-500">No providers found in this date range.</td></tr>';
      }
      $tbody.innerHTML = rowsHtml;

      var vAvgRow = '<td colspan="2" class="p-3 pr-4 text-right border-r border-white font-bold bg-gray-200 sticky left-0 z-10" style="box-shadow: 2px 0 5px rgba(0,0,0,0.03);">Avg:</td>';
      var vTotRow = '<td colspan="2" class="p-3 pr-4 text-right border-r border-white font-bold text-gray-500 bg-gray-200 sticky left-0 z-10" style="box-shadow: 2px 0 5px rgba(0,0,0,0.03);">Total:</td>';

      cards.forEach(function (card, idx) {
        var isFirstCard = idx === 0;
        var pl = isFirstCard ? 'padding-left: 1.5rem;' : '';
        var vAvg = dataCache.avg ? dataCache.avg[card.k] : null;
        var vTot = dataCache.total ? dataCache.total[card.k] : null;

        vAvgRow += '<td class="p-3 text-center border-r border-white" style="' + pl + '">' + fmtKpi(vAvg, card.fmt) + '</td>';
        if (card.fmt === 'pct' || card.fmt === 'dec2' || card.fmt === 'dec1') {
          vTotRow += '<td class="p-3 text-center border-r border-white text-gray-500" style="' + pl + '">--</td>';
        } else {
          vTotRow += '<td class="p-3 text-center border-r border-white text-gray-500" style="' + pl + '">' + fmtKpi(vTot, card.fmt) + '</td>';
        }
      });
      $tfoot.innerHTML = '<tr>' + vAvgRow + '</tr><tr>' + vTotRow + '</tr>';
    }

    /* ── Init ──────────────────────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
      // Tab switching — URL-driven + deep-linkable (DDS.tabs.deeplink).
      function activateKpiTab(tab) {
        document.querySelectorAll('.kpi-tab-btn').forEach(function (b) {
          b.classList.remove('border-emerald-500', 'text-emerald-600', 'font-bold');
          b.classList.add('border-transparent');
        });
        var btn = document.querySelector('.kpi-tab-btn[data-tab="' + tab + '"]');
        if (btn) { btn.classList.add('border-emerald-500', 'text-emerald-600', 'font-bold'); btn.classList.remove('border-transparent'); }
        document.querySelectorAll('.kpi-tab-content').forEach(function (t) { t.classList.add('hidden'); });
        var panel = document.getElementById('tab-' + tab);
        if (panel) panel.classList.remove('hidden');
        if (tab === 'providers') renderProvidersTable();
      }
      var kpiTabs = DDS.tabs.deeplink('tab', activateKpiTab);
      document.querySelectorAll('.kpi-tab-btn').forEach(function (btn) {
        btn.addEventListener('click', function () { kpiTabs.go(btn.dataset.tab); });
      });
      // Deep-link: honor ?tab= on load, else the default 'main' tab.
      activateKpiTab(kpiTabs.initial || 'main');

      // Providers Sub-tabs
      document.querySelectorAll('.kpi-subtab-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
          document.querySelectorAll('.kpi-subtab-btn').forEach(function (b) {
            b.classList.remove('font-bold', 'text-gray-900', 'border-gray-900');
            b.classList.add('font-medium', 'text-gray-500', 'border-transparent');
          });
          btn.classList.add('font-bold', 'text-gray-900', 'border-gray-900');
          btn.classList.remove('font-medium', 'text-gray-500', 'border-transparent');
          currentProviderSubtab = btn.dataset.subtab;
          renderProvidersTable();
        });
      });

      // Update button reads current daterangepicker selection
      document.getElementById('kpiUpdateBtn').addEventListener('click', function () {
        var drp = $('#kpiDateRange').data('daterangepicker');
        if (!drp) return;
        fetchKpis(drp.startDate.format('YYYY-MM-DD'), drp.endDate.format('YYYY-MM-DD'));
        if (!document.getElementById('tab-providers').classList.contains('hidden')) {
          renderProvidersTable();
        }
      });

      // Initial load — wait for moment to be available
      var _tryInit = setInterval(function () {
        if (typeof moment === 'undefined') return;
        clearInterval(_tryInit);
        fetchKpis(moment().startOf('year').format('YYYY-MM-DD'), moment().format('YYYY-MM-DD'));
      }, 30);
    });
  </script>

</x-app-layout>