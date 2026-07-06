<x-app-layout>
  <!-- Top Header Navigation -->
  <header class="bg-white border-b border-gray-100 px-8 py-4 flex justify-between items-center">
    <div class="flex items-center space-x-2">
      <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Aging</h1>
    </div>
    <a href="#"
      class="flex items-center bg-[#002b24] text-emerald-400 font-semibold px-4 py-2 rounded-full text-sm hover:opacity-90 transition">
      <i class="fa-solid fa-book-open mr-2"></i>
      <span>Quick Start Guide</span>
    </a>
  </header>

  <!-- Filter Controls Section -->
  <section class="bg-white border-b border-gray-200 px-8 py-4 flex flex-wrap items-center justify-between gap-4">
    <div class="flex flex-wrap items-center gap-3">
      <div class="relative flex items-center border border-gray-300 rounded px-3 py-1.5 bg-white shadow-sm">
        <i class="fa-regular fa-calendar text-gray-400 mr-2 text-sm"></i>
        <input type="text" id="asOfDate" value="{{ date('M d, Y') }}"
          class="text-sm font-medium text-gray-700 outline-none w-28">
      </div>
      <select
        class="border border-gray-300 rounded px-4 py-1.5 text-sm bg-white focus:outline-none focus:border-emerald-500 shadow-sm font-medium text-gray-700">
        <option selected>8 Mile</option>
      </select>
      <button id="refreshBtn"
        class="bg-white border border-emerald-500 text-emerald-600 px-5 py-1.5 rounded text-sm font-semibold hover:bg-emerald-50 transition shadow-sm">
        Refresh
      </button>
    </div>
  </section>

  <!-- Tab Bar -->
  <section class="px-8 bg-white border-b border-gray-200 flex gap-6 text-sm font-medium text-gray-500">
    <button class="tab-btn border-b-2 border-emerald-500 text-emerald-600 font-bold pb-3 pt-4"
      data-tab="responsible_party">Responsible Party</button>
    <button class="tab-btn border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4" data-tab="by_office">By
      Office</button>
    <button class="tab-btn border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4" data-tab="by_patient">By
      Patient</button>
    <button class="tab-btn border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4" data-tab="by_insurance">By
      Insurance</button>
  </section>

  <!-- Main Content -->
  <main class="p-6">

    <!-- Info Banner -->
    <div
      class="mb-6 text-xs text-blue-600 bg-blue-50 border border-blue-200 rounded-lg p-3.5 flex items-center shadow-xs">
      <i class="fa-solid fa-circle-info mr-2.5 text-blue-500 text-sm"></i>
      <span>Values display Guarantor balances. Individual Patient Aging values can be viewed by selecting the breakout
        button next to the Guarantor name.</span>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden p-4">

      <!-- Toolbar Row -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div class="flex items-center gap-1">
          <span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1 rounded">Top 20%</span>
          <span class="bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-1 rounded">Mid Tier</span>
          <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-1 rounded">Bottom 20%</span>
        </div>
        <div class="flex items-center gap-2 ml-auto">
          <select id="creditsFilter"
            class="border border-gray-300 rounded px-3 py-1 text-sm bg-white focus:outline-none focus:border-emerald-500 font-medium text-gray-700">
            <option value="include">Include Credits</option>
            <option value="exclude">Exclude Credits</option>
          </select>
          <div class="relative">
            <input type="text" id="agingSearch" placeholder="Search..."
              class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:border-emerald-500 pr-8 w-48">
            <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2 text-gray-400 text-xs"></i>
          </div>
          <button id="exportBtn"
            class="border border-emerald-500 text-emerald-600 font-semibold px-4 py-1 rounded text-sm hover:bg-emerald-50 transition shadow-xs">
            Export CSV
          </button>
        </div>
      </div>

      <!-- Table -->
      <x-data-table id="agingTable" min-width="1200px" max-height="600px">
        <x-slot:head>
          <tr>
            <th class="dt-col-sticky px-4 py-3 text-gray-900 font-extrabold min-w-[180px]">
              <div class="flex items-center justify-between">
                <span>Guarantor</span>
                <i class="fa-solid fa-arrows-up-down text-[10px] text-gray-400"></i>
              </div>
            </th>
            <th class="px-4 py-3 border-r border-gray-200 min-w-[110px]">Guarantor ID</th>
            <th class="px-4 py-3 border-r border-gray-200 min-w-[240px]">Patient(s)</th>
            <th class="px-4 py-3 border-r border-gray-200 min-w-[140px]">Patient ID(s)</th>
            <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Current</th>
            <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 30</th>
            <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 60</th>
            <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 90</th>
            <th class="px-4 py-3 text-emerald-900 bg-emerald-50 min-w-[110px]">Total</th>
          </tr>
        </x-slot:head>
        <x-slot:foot>
          <tr class="bg-gray-50 font-bold text-gray-900 text-xs">
            <td class="dt-col-sticky bg-gray-50 px-4 py-3.5 border-r border-gray-200 text-right">Total:</td>
            <td class="border-r border-gray-200"></td>
            <td class="border-r border-gray-200"></td>
            <td class="border-r border-gray-200"></td>
            <td id="foot-current" class="px-4 py-3.5 border-r border-gray-200">—</td>
            <td id="foot-30" class="px-4 py-3.5 border-r border-gray-200">—</td>
            <td id="foot-60" class="px-4 py-3.5 border-r border-gray-200">—</td>
            <td id="foot-90" class="px-4 py-3.5 border-r border-gray-200">—</td>
            <td id="foot-total" class="px-4 py-3.5">—</td>
          </tr>
        </x-slot:foot>
      </x-data-table>

      <!-- By Office Container -->
      <div id="byOfficeContainer" class="hidden">

        <!-- Info Banner (Office specific) -->
        <div
          class="mb-6 text-xs text-blue-600 bg-blue-50 border border-blue-200 rounded-lg p-3.5 flex items-center shadow-xs">
          <i class="fa-solid fa-circle-info mr-2.5 text-blue-500 text-sm"></i>
          <span>By Office Aging gives an overview of the office's aging in each of the standard buckets</span>
        </div>

        <x-data-table id="officeTable" min-width="1200px" max-height="600px">
          <x-slot:head>
            <tr>
              <th class="dt-col-sticky px-4 py-3 text-gray-900 font-extrabold min-w-[80px]">#</th>
              <th class="dt-col-sticky px-4 py-3 border-r border-gray-200 text-gray-900 font-extrabold min-w-[200px]">
                Location</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Current</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 30</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 60</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 90</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 120</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 180</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 240</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 365</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[120px]">Credit Balance</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Contract</th>
              <th class="px-4 py-3 text-center min-w-[100px]">Total</th>
            </tr>
          </x-slot:head>
          <x-slot:foot>
            <tr class="bg-gray-200 font-bold text-gray-900 text-xs text-right">
              <td class="dt-col-sticky bg-gray-200 border-r border-gray-300"></td>
              <td class="dt-col-sticky bg-gray-200 px-4 py-3.5 border-r border-gray-300">Total:</td>
              <td id="office-foot-current" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="office-foot-30" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="office-foot-60" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="office-foot-90" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="office-foot-120" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="office-foot-180" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="office-foot-240" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="office-foot-365" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="office-foot-credit" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="office-foot-contract" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="office-foot-total" class="px-4 py-3.5">—</td>
            </tr>
          </x-slot:foot>
        </x-data-table>
      </div>

      <!-- By Patient Container -->
      <div id="byPatientContainer" class="hidden">
        
        <!-- Info Banner (Patient specific) -->
        <div class="mb-6 text-xs text-blue-600 bg-blue-50 border border-blue-200 rounded-lg p-3.5 flex items-center shadow-xs">
          <i class="fa-solid fa-circle-info mr-2.5 text-blue-500 text-sm"></i>
          <span>Values display Guarantor balances. Individual Patient Aging values can be viewed by selecting the breakout button next to the Guarantor name.</span>
        </div>
        
        <x-data-table id="patientTable" min-width="1400px" max-height="600px">
          <x-slot:head>
            <tr>
              <th class="dt-col-sticky px-4 py-3 text-gray-900 font-extrabold bg-white border-r border-gray-200 min-w-[200px]">
                <div class="flex items-center justify-between">
                  <span>Guarantor</span>
                  <i class="fa-solid fa-arrows-up-down text-[10px] text-gray-400"></i>
                </div>
              </th>
              <th class="dt-col-sticky px-4 py-3 border-r border-gray-200 text-gray-900 font-extrabold min-w-[120px]">Guarantor ID</th>
              <th class="px-4 py-3 border-r border-gray-200 text-gray-900 font-extrabold min-w-[200px]">Patient</th>
              <th class="px-4 py-3 border-r border-gray-200 text-gray-900 font-extrabold min-w-[120px]">Patient ID</th>
              <th class="px-4 py-3 border-r border-gray-200 text-gray-900 font-extrabold min-w-[100px]">Office</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Current</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 30</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 60</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 90</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 120</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 180</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 240</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 365</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[120px]">Credit Balance</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Contract</th>
              <th class="px-4 py-3 text-center min-w-[100px]">Total</th>
            </tr>
          </x-slot:head>
          <x-slot:foot>
            <tr class="bg-gray-200 font-bold text-gray-900 text-xs text-right">
              <td class="dt-col-sticky bg-gray-200 border-r border-gray-300"></td>
              <td class="dt-col-sticky bg-gray-200 px-4 py-3.5 border-r border-gray-300">Total:</td>
              <td class="border-r border-gray-300"></td>
              <td class="border-r border-gray-300"></td>
              <td class="border-r border-gray-300"></td>
              <td id="patient-foot-current" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-30"      class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-60"      class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-90"      class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-120"     class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-180"     class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-240"     class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-365"     class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-credit"  class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-contract" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-total"   class="px-4 py-3.5">—</td>
            </tr>
          </x-slot:foot>
        </x-data-table>
      </div>

      <!-- By Insurance Container -->
      <div id="byInsuranceContainer" class="hidden">
        
        <!-- Toolbar Row -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4 mt-2">
          <div class="flex items-center gap-1">
            <span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1 rounded">Top 20%</span>
            <span class="bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-1 rounded">Mid Tier</span>
            <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-1 rounded">Bottom 20%</span>
          </div>
          <div class="flex items-center gap-2 ml-auto">
            <select class="border border-gray-300 rounded px-2 py-1 text-sm bg-white focus:outline-none focus:border-emerald-500 font-medium text-gray-700 w-44">
                <option value="all">INSURANCE: 0 selected</option>
            </select>
            <button class="bg-emerald-500 text-white font-semibold flex items-center justify-center hover:bg-emerald-600 transition px-4 py-1 rounded shadow-xs text-sm">
                Get Report
            </button>
            <div class="relative">
              <input type="text" id="insuranceSearch" placeholder="Search..." class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:border-emerald-500 pr-8 w-40">
              <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2 text-gray-400 text-xs"></i>
            </div>
            <button class="border border-emerald-500 text-emerald-600 font-semibold px-4 py-1 rounded text-sm hover:bg-emerald-50 transition shadow-xs">
              Export CSV
            </button>
          </div>
        </div>

        <x-data-table id="insuranceTable" min-width="1400px" max-height="600px">
          <x-slot:head>
            <tr>
              <th class="dt-col-sticky px-4 py-3 text-gray-900 font-extrabold bg-white border-r border-gray-200 min-w-[200px]">
                <div class="flex items-center justify-between">
                  <span>Guarantor</span>
                  <i class="fa-solid fa-arrows-up-down text-[10px] text-gray-400"></i>
                </div>
              </th>
              <th class="dt-col-sticky px-4 py-3 border-r border-gray-200 text-gray-900 font-extrabold min-w-[120px]">Guarantor ID</th>
              <th class="px-4 py-3 border-r border-gray-200 text-gray-900 font-extrabold min-w-[200px]">Patient</th>
              <th class="px-4 py-3 border-r border-gray-200 text-gray-900 font-extrabold min-w-[120px]">Patient ID</th>
              <th class="px-4 py-3 border-r border-gray-200 text-gray-900 font-extrabold min-w-[150px]">Insurance</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Current</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 30</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 60</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 90</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 120</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 180</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 240</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Over 365</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[120px]">Credit Balance</th>
              <th class="px-4 py-3 border-r border-gray-200 text-center min-w-[100px]">Contract</th>
              <th class="px-4 py-3 text-center min-w-[100px]">Total</th>
            </tr>
          </x-slot:head>
          <x-slot:foot>
            <tr class="bg-gray-200 font-bold text-gray-900 text-xs text-right">
              <td class="dt-col-sticky bg-gray-200 border-r border-gray-300"></td>
              <td class="dt-col-sticky bg-gray-200 px-4 py-3.5 border-r border-gray-300">Total:</td>
              <td class="border-r border-gray-300"></td>
              <td class="border-r border-gray-300"></td>
              <td class="border-r border-gray-300"></td>
              <td id="insurance-foot-current" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-30"      class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-60"      class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-90"      class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-120"     class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-180"     class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-240"     class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-365"     class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-credit"  class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-contract" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-total"   class="px-4 py-3.5">—</td>
            </tr>
          </x-slot:foot>
        </x-data-table>
      </div>

    </div>
  </main>

  <style>
    /* DataTables pagination styling */
    #agingTable_wrapper .dt-layout-row:last-child {
      padding: 1rem 0 0;
    }

    #agingTable_wrapper .dt-paging button {
      border: 1px solid #d1d5db;
      border-radius: 0.375rem;
      padding: 0.25rem 0.625rem;
      font-size: 0.75rem;
      font-weight: 500;
      color: #374151;
      background: #fff;
      margin: 0 1px;
      line-height: 1.4;
    }

    #agingTable_wrapper .dt-paging button.current {
      background: #059669;
      color: #fff;
      border-color: #059669;
    }

    #agingTable_wrapper .dt-paging button:hover:not(.current) {
      background: #f9fafb;
    }

    #agingTable_wrapper .dt-info {
      font-size: 0.75rem;
      color: #6b7280;
    }

    #agingTable_wrapper .dt-length label {
      font-size: 0.75rem;
      color: #6b7280;
    }

    #agingTable_wrapper .dt-length select {
      border: 1px solid #d1d5db;
      border-radius: 0.375rem;
      padding: 0.125rem 0.375rem;
      font-size: 0.75rem;
      margin: 0 4px;
    }
  </style>

  <script>
    const baseUrl = "{{ url('') }}";
    let agingTable;
    let agingMode = 'responsible_party';

    $(document).ready(function () {

      agingTable = $('#agingTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: false,   // we drive search from our own input
        ordering: false,
        pageLength: 20,
        lengthMenu: [10, 20, 50, 100],
        ajax: {
          url: baseUrl + '/aging/data',
          type: 'GET',
          data: function (d) {
            d.search = { value: $('#agingSearch').val() };
            d.mode = agingMode;
          },
          dataSrc: function (json) {
            // Update footer totals from the response
            if (json.totals) {
              $('#foot-current').text(json.totals.current);
              $('#foot-30').text(json.totals.thirty);
              $('#foot-60').text(json.totals.sixty);
              $('#foot-90').text(json.totals.ninety);
              $('#foot-total').text(json.totals.grand);
            }
            return json.data;
          },
        },
        columns: [
          {
            data: 'guarantor_name',
            render: function (data, type, row) {
              if (type !== 'display') return data;
              return '<div class="flex items-center justify-between">'
                + '<span class="font-bold text-gray-900">' + data + '</span>'
                + '<button onclick="openPatient(' + (row.patient_id || row.guarantor_id) + ')" class="text-gray-400 hover:text-emerald-500 transition-colors p-1 ml-1">'
                + '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7"/><path d="M7 7h10v10"/></svg>'
                + '</button>'
                + '</div>';
            },
            className: 'dt-col-sticky px-4 py-3',
          },
          {
            data: 'guarantor_id',
            className: 'px-4 py-3 border-r border-gray-200 text-gray-500',
          },
          {
            data: 'family_names',
            className: 'px-4 py-3 border-r border-gray-200 text-gray-900 truncate max-w-[240px]',
          },
          {
            data: 'family_ids',
            className: 'px-4 py-3 border-r border-gray-200 text-gray-500',
          },
          {
            data: 'bal_current',
            className: 'px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold',
          },
          {
            data: 'bal_30',
            className: 'px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold',
          },
          {
            data: 'bal_60',
            className: 'px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold',
          },
          {
            data: 'bal_90',
            className: 'px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold',
          },
          {
            data: 'total',
            className: 'px-4 py-3 bg-emerald-50/40 text-emerald-700 font-semibold',
          },
        ],
        drawCallback: function () {
          // Re-apply hover class because DataTables replaces TR elements
          $('#agingTable tbody tr').addClass('hover:bg-gray-50/80 transition');
        },
        language: {
          processing: '<div class="text-xs text-gray-500 py-2">Loading...</div>',
          emptyTable: '<div class="text-xs text-gray-400 py-4 text-center">No aging records found.</div>',
        },
      });

      // ── Search ───────────────────────────────────────────────────────────
      let searchTimer;
      $('#agingSearch').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => agingTable.ajax.reload(), 400);
      });

      // ── Refresh ──────────────────────────────────────────────────────────
      $('#refreshBtn').on('click', () => agingTable.ajax.reload());

      // ── Tab switching (mode-aware aging data requests) ────────────────
      $('.tab-btn').on('click', function () {
        $('.tab-btn')
          .removeClass('border-emerald-500 text-emerald-600 font-bold')
          .addClass('border-transparent');

        $(this)
          .removeClass('border-transparent')
          .addClass('border-emerald-500 text-emerald-600 font-bold');

        agingMode = $(this).data('tab');

        if (agingMode === 'by_office') {
          $('#agingTable_wrapper').hide();
          $('#byPatientContainer').addClass('hidden');
          $('#byInsuranceContainer').addClass('hidden');
          $('#byOfficeContainer').removeClass('hidden');
          officeTable.ajax.reload();
        } else if (agingMode === 'by_patient') {
          $('#agingTable_wrapper').hide();
          $('#byOfficeContainer').addClass('hidden');
          $('#byInsuranceContainer').addClass('hidden');
          $('#byPatientContainer').removeClass('hidden');
          patientTable.ajax.reload();
        } else if (agingMode === 'by_insurance') {
          $('#agingTable_wrapper').hide();
          $('#byOfficeContainer').addClass('hidden');
          $('#byPatientContainer').addClass('hidden');
          $('#byInsuranceContainer').removeClass('hidden');
          insuranceTable.ajax.reload();
        } else {
          $('#byOfficeContainer').addClass('hidden');
          $('#byPatientContainer').addClass('hidden');
          $('#byInsuranceContainer').addClass('hidden');
          $('#agingTable_wrapper').show();
          agingTable.ajax.reload();
        }
      });

      // ── Office Table Initialization ──────────────────────────────────────────
      const getValClass = (valStr) => {
        if (!valStr || valStr === '$ 0.00' || valStr === '$ 0') return 'text-gray-500';
        if (valStr.includes('(')) return 'text-red-700 font-semibold';
        return 'text-gray-900 font-semibold'; // or could just be black
      };

      const renderColorBg = (data, isParent) => {
        let extra = isParent ? ' bg-red-100' : 'bg-white';
        return `<div class="w-full h-full p-2.5 ${extra}">${data}</div>`;
      };
      const renderColorBgGreen = (data, isParent) => {
        let extra = isParent ? ' bg-emerald-100' : 'bg-white';
        return `<div class="w-full h-full p-2.5 ${extra}">${data}</div>`;
      };
      const renderColorBgNeutral = (data, isParent) => {
        let extra = isParent ? ' bg-gray-200' : 'bg-white';
        return `<div class="w-full h-full p-2.5 ${extra}">${data}</div>`;
      };

      let officeTable = $('#officeTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: false,
        ordering: false,
        pageLength: 20,
        lengthMenu: [10, 20, 50, 100],
        createdRow: function (row, data, dataIndex) {
          // Apply different styling on parent/child rows for 'by_office'
          $(row).addClass('text-xs border-b border-gray-200');
          $(row).find('td').addClass('!p-0 border-r border-gray-200 text-right'); // remove internal padding
          $(row).find('td:nth-child(1), td:nth-child(2)').removeClass('text-right').addClass('text-left');
        },
        ajax: {
          url: baseUrl + '/aging/data',
          type: 'GET',
          data: function (d) {
            d.search = { value: $('#agingSearch').val() };
            d.mode = 'by_office';
          },
          dataSrc: function (json) {
            if (json.totals) {
              $('#office-foot-current').text(json.totals.current);
              $('#office-foot-30').text(json.totals.thirty);
              $('#office-foot-60').text(json.totals.sixty);
              $('#office-foot-90').text(json.totals.ninety);
              $('#office-foot-120').text(json.totals.one_twenty);
              $('#office-foot-180').text(json.totals.one_eighty);
              $('#office-foot-240').text(json.totals.two_forty);
              $('#office-foot-365').text(json.totals.three_sixty_five);
              $('#office-foot-credit').text(json.totals.credit);
              $('#office-foot-contract').text(json.totals.contract);
              $('#office-foot-total').text(json.totals.total);
            }
            return json.data;
          },
        },
        columns: [
          {
            data: 'row_number',
            render: function (data, type, row) {
              return `<div class="w-full h-full p-2.5 bg-gray-50 flex items-center justify-center">${data || ''}</div>`;
            },
            className: 'dt-col-sticky'
          },
          {
            data: 'location_name',
            render: function (data, type, row) {
              if (row.is_parent) {
                return `<div class="w-full h-full p-2.5 bg-gray-50 font-bold flex items-center justify-between">
                            ${data}
                            <i class="fa-solid fa-arrow-up-right-from-square text-gray-400 ml-2"></i>
                          </div>`;
              }
              return `<div class="w-full h-full p-1.5 pl-4 bg-white text-gray-600">${data}</div>`;
            },
            className: 'dt-col-sticky'
          },
          {
            data: 'bal_current',
            render: (data, t, row) => renderColorBg(data, row.is_parent)
          },
          {
            data: 'bal_30',
            render: (data, t, row) => renderColorBg(data, row.is_parent)
          },
          {
            data: 'bal_60',
            render: (data, t, row) => renderColorBg(data, row.is_parent)
          },
          {
            data: 'bal_90',
            render: (data, t, row) => renderColorBg(data, row.is_parent)
          },
          {
            data: 'bal_120',
            render: (data, t, row) => renderColorBg(data, row.is_parent)
          },
          {
            data: 'bal_180',
            render: (data, t, row) => renderColorBg(data, row.is_parent)
          },
          {
            data: 'bal_240',
            render: (data, t, row) => renderColorBg(data, row.is_parent)
          },
          {
            data: 'bal_365',
            render: (data, t, row) => renderColorBgGreen(data, row.is_parent)
          },
          {
            data: 'credit_balance',
            render: (data, t, row) => renderColorBgNeutral(data, row.is_parent)
          },
          {
            data: 'contract',
            render: (data, t, row) => renderColorBgGreen(data, row.is_parent)
          },
          {
            data: 'total',
            render: (data, t, row) => `<div class="w-full h-full p-2.5 font-bold ${row.is_parent ? 'bg-red-200' : 'bg-white'}">${data}</div>`
          }
        ]
      });

      // ── Patient Table Initialization ──────────────────────────────────────────
      const parseAmount = (valStr) => {
          if (!valStr) return 0;
          return parseFloat(valStr.replace(/[^0-9.-]+/g,"")) || 0;
      };

      const renderTierBg = (data, type, row) => {
          if (type !== 'display') return data;
          let val = parseAmount(data);
          // Zero -> green. Low -> yellow. High -> red. (Mocking tier rendering logic)
          let bgClass = 'bg-emerald-100'; // bottom tier amounts usually green per requirement/image (actually green means good, low balance is good here maybe)
          
          if(data && data.includes('(')) {
              // credits are often red or green depending on view, let's keep it neutral or light green
              bgClass = 'bg-emerald-100 text-emerald-800'; 
          } else {
              if (val > 0 && val <= 100) bgClass = 'bg-amber-100 text-amber-800';
              else if (val > 100) bgClass = 'bg-red-100 text-red-800';
              else bgClass = 'bg-emerald-100 text-emerald-800'; 
          }
          
          // Exception for total block (often red in screenshot for highest balances)
          return `<div class="w-full h-full p-2.5 ${bgClass} text-xs flex items-center font-medium">${data}</div>`;
      };
      
      const renderTierBgTotal = (data, t, row) => {
          if (t !== 'display') return data;
          let val = parseAmount(data);
          let bgClass = 'bg-red-200 text-red-900';
          if (val === 0 || data.includes('(')) bgClass = 'bg-emerald-100 text-emerald-800';
          else if (val <= 100) bgClass = 'bg-amber-200 text-amber-900 text-amber-900';
          return `<div class="w-full h-full p-2.5 ${bgClass} text-xs flex items-center font-bold">${data}</div>`;
      };

      let patientTable = $('#patientTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: false,
        ordering: false,
        pageLength: 20,
        lengthMenu: [10, 20, 50, 100],
        createdRow: function (row, data, dataIndex) {
            $(row).addClass('text-xs border-b border-gray-200');
            $(row).find('td').addClass('!p-0 border-r border-gray-200 text-left');
        },
        ajax: {
          url: baseUrl + '/aging/data',
          type: 'GET',
          data: function (d) {
            d.search = { value: $('#agingSearch').val() };
            d.mode = 'by_patient';
          },
          dataSrc: function (json) {
            if (json.totals) {
              $('#patient-foot-current').text(json.totals.current);
              $('#patient-foot-30').text(json.totals.thirty);
              $('#patient-foot-60').text(json.totals.sixty);
              $('#patient-foot-90').text(json.totals.ninety);
              $('#patient-foot-120').text(json.totals.one_twenty);
              $('#patient-foot-180').text(json.totals.one_eighty);
              $('#patient-foot-240').text(json.totals.two_forty);
              $('#patient-foot-365').text(json.totals.three_sixty_five);
              $('#patient-foot-credit').text(json.totals.credit);
              $('#patient-foot-contract').text(json.totals.contract);
              $('#patient-foot-total').text(json.totals.total);
            }
            return json.data;
          },
        },
        columns: [
          {
            data: 'guarantor_name',
            render: function (data, type, row) {
              if (type !== 'display') return data;
              return `<div class="w-full h-full p-2.5 bg-white text-gray-700 font-medium flex items-center justify-between">
                        ${data}
                        <button onclick="openPatient(${row.patient_id || row.guarantor_id})" class="text-gray-400 hover:text-emerald-500 transition-colors p-1 ml-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7"/><path d="M7 7h10v10"/></svg>
                        </button>
                      </div>`;
            },
            className: 'dt-col-sticky'
          },
          {
            data: 'guarantor_id',
            render: (d) => `<div class="w-full h-full p-2.5 bg-white text-gray-500 flex items-center">${d}</div>`,
            className: 'dt-col-sticky'
          },
          { data: 'family_names', render: (d) => `<div class="w-full h-full p-2.5 bg-white text-gray-700 flex items-center">${d}</div>` },
          { data: 'family_ids', render: (d) => `<div class="w-full h-full p-2.5 bg-white text-gray-500 flex items-center">${d}</div>` },
          { data: 'office', render: (d) => `<div class="w-full h-full p-2.5 bg-white text-gray-500 flex items-center">${d}</div>` },
          { data: 'bal_current', render: renderTierBg },
          { data: 'bal_30', render: renderTierBg },
          { data: 'bal_60', render: renderTierBg },
          { data: 'bal_90', render: renderTierBg },
          { data: 'bal_120', render: renderTierBg },
          { data: 'bal_180', render: renderTierBg },
          { data: 'bal_240', render: renderTierBg },
          { data: 'bal_365', render: renderTierBg },
          { data: 'credit_balance', render: renderTierBg },
          { data: 'contract', render: renderTierBg },
          { data: 'total', render: renderTierBgTotal }
        ]
      });

      // ── Insurance Table Initialization ─────────────────────────────────────────
      let insuranceTable = $('#insuranceTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: false,
        ordering: false,
        pageLength: 20,
        lengthMenu: [10, 20, 50, 100],
        createdRow: function (row, data, dataIndex) {
            $(row).addClass('text-xs border-b border-gray-200');
            $(row).find('td').addClass('!p-0 border-r border-gray-200 text-left');
        },
        ajax: {
          url: baseUrl + '/aging/data',
          type: 'GET',
          data: function (d) {
            // Using separate search input if needed or standard one
            d.search = { value: $('#insuranceSearch').val() || $('#agingSearch').val() };
            d.mode = 'by_insurance';
          },
          dataSrc: function (json) {
            if (json.totals) {
              $('#insurance-foot-current').text(json.totals.current);
              $('#insurance-foot-30').text(json.totals.thirty);
              $('#insurance-foot-60').text(json.totals.sixty);
              $('#insurance-foot-90').text(json.totals.ninety);
              $('#insurance-foot-120').text(json.totals.one_twenty);
              $('#insurance-foot-180').text(json.totals.one_eighty);
              $('#insurance-foot-240').text(json.totals.two_forty);
              $('#insurance-foot-365').text(json.totals.three_sixty_five);
              $('#insurance-foot-credit').text(json.totals.credit);
              $('#insurance-foot-contract').text(json.totals.contract);
              $('#insurance-foot-total').text(json.totals.total);
            }
            return json.data;
          },
        },
        columns: [
          {
            data: 'guarantor_name',
            render: function (data, type, row) {
              if (type !== 'display') return data;
              return `<div class="w-full h-full p-2.5 bg-white text-gray-700 font-medium flex items-center justify-between">
                        ${data}
                        <button onclick="openPatient(${row.patient_id || row.guarantor_id})" class="text-gray-400 hover:text-emerald-500 transition-colors p-1 ml-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7"/><path d="M7 7h10v10"/></svg>
                        </button>
                      </div>`;
            },
            className: 'dt-col-sticky'
          },
          {
            data: 'guarantor_id',
            render: (d) => `<div class="w-full h-full p-2.5 bg-white text-gray-500 flex items-center">${d}</div>`,
            className: 'dt-col-sticky'
          },
          { data: 'family_names', render: (d) => `<div class="w-full h-full p-2.5 bg-white text-gray-700 flex items-center">${d}</div>` },
          { data: 'family_ids', render: (d) => `<div class="w-full h-full p-2.5 bg-white text-gray-500 flex items-center">${d}</div>` },
          { data: 'insurance', render: (d) => `<div class="w-full h-full p-2.5 bg-white text-gray-500 flex items-center">${d}</div>` },
          { data: 'bal_current', render: renderTierBg },
          { data: 'bal_30', render: renderTierBg },
          { data: 'bal_60', render: renderTierBg },
          { data: 'bal_90', render: renderTierBg },
          { data: 'bal_120', render: renderTierBg },
          { data: 'bal_180', render: renderTierBg },
          { data: 'bal_240', render: renderTierBg },
          { data: 'bal_365', render: renderTierBg },
          { data: 'credit_balance', render: renderTierBg },
          { data: 'contract', render: renderTierBg },
          { data: 'total', render: renderTierBgTotal }
        ]
      });
      
      let iSearchTimer;
      $('#insuranceSearch').on('input', function () {
        clearTimeout(iSearchTimer);
        iSearchTimer = setTimeout(() => insuranceTable.ajax.reload(), 400);
      });

    });
  </script>

  <x-patient-modal />

</x-app-layout>