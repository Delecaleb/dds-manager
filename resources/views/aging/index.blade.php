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

      <!-- Each tab is its own table + its own DataTable instance, lazily
           loaded the first time its tab is selected -- not one shared
           table with a mode filter. -->
      <div id="agingTabs">

        <div id="tabpanel-responsible_party" class="tab-panel">
          <x-data-table id="agingTable-responsible_party" min-width="2200px" max-height="600px">
            <x-slot:head>
              <tr>
                <th class="dt-col-sticky px-4 py-3 text-gray-900 font-extrabold min-w-[180px]">Guarantor</th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[110px]">Guarantor ID</th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[240px]">Patient</th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[140px]">Patient ID</th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[110px]">Office</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Current</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 30</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 60</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 90</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 120
                </th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 180
                </th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 240
                </th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 365
                </th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[120px]">Credit Balance</th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[110px]">Contract</th>
                <th class="px-4 py-3 text-emerald-900 bg-emerald-50 min-w-[110px]">Total</th>
              </tr>
            </x-slot:head>
            <x-slot:foot>
              <tr class="bg-gray-50 font-bold text-gray-900 text-xs">
                <td class="dt-col-sticky bg-gray-50 px-4 py-3.5 border-r border-gray-200 text-right">Overall Total:</td>
                <td class="border-r border-gray-200"></td>
                <td class="border-r border-gray-200"></td>
                <td class="border-r border-gray-200"></td>
                <td class="border-r border-gray-200"></td>
                <td id="foot-current-responsible_party" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-30-responsible_party" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-60-responsible_party" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-90-responsible_party" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-120-responsible_party" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-180-responsible_party" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-240-responsible_party" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-365-responsible_party" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-credit-responsible_party" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-contract-responsible_party" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-total-responsible_party" class="px-4 py-3.5">—</td>
              </tr>
            </x-slot:foot>
          </x-data-table>
        </div>

        <div id="tabpanel-by_office" class="tab-panel hidden">
          <x-data-table id="agingTable-by_office" min-width="1400px" max-height="600px">
            <x-slot:head>
              <tr>
                <th class="dt-col-sticky px-4 py-3 text-gray-900 font-extrabold min-w-[60px]">#</th>
                <th class="dt-col-sticky px-4 py-3 border-r border-gray-200 text-gray-900 font-extrabold min-w-[200px]">
                  Location</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[120px]">Current</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[120px]">Over 30</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[120px]">Over 60</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[120px]">Over 90</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[120px]">Over 120
                </th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[120px]">Over 180
                </th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[120px]">Over 240
                </th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[120px]">Over 365
                </th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[120px]">Credit Balance</th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[110px]">Contract</th>
                <th class="px-4 py-3 text-emerald-900 bg-emerald-50 min-w-[120px]">Total</th>
              </tr>
            </x-slot:head>
            <x-slot:foot>
              <tr class="bg-gray-50 font-bold text-gray-900 text-xs">
                <td class="dt-col-sticky bg-gray-50 border-r border-gray-200"></td>
                <td class="dt-col-sticky bg-gray-50 px-4 py-3.5 border-r border-gray-200 text-right">Overall Total:</td>
                <td id="foot-current-by_office" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-30-by_office" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-60-by_office" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-90-by_office" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-120-by_office" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-180-by_office" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-240-by_office" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-365-by_office" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-credit-by_office" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-contract-by_office" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-total-by_office" class="px-4 py-3.5">—</td>
              </tr>
            </x-slot:foot>
          </x-data-table>
        </div>

        <div id="tabpanel-by_patient" class="tab-panel hidden">
          <x-data-table id="agingTable-by_patient" min-width="2200px" max-height="600px">
            <x-slot:head>
              <tr>
                <th class="dt-col-sticky px-4 py-3 text-gray-900 font-extrabold min-w-[180px]">Guarantor</th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[110px]">Guarantor ID</th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[240px]">Patient</th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[140px]">Patient ID</th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[110px]">Office</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Current</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 30</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 60</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 90</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 120
                </th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 180
                </th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 240
                </th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 365
                </th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[120px]">Credit Balance</th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[110px]">Contract</th>
                <th class="px-4 py-3 text-emerald-900 bg-emerald-50 min-w-[110px]">Total</th>
              </tr>
            </x-slot:head>
            <x-slot:foot>
              <tr class="bg-gray-50 font-bold text-gray-900 text-xs">
                <td class="dt-col-sticky bg-gray-50 px-4 py-3.5 border-r border-gray-200 text-right">Overall Total:</td>
                <td class="border-r border-gray-200"></td>
                <td class="border-r border-gray-200"></td>
                <td class="border-r border-gray-200"></td>
                <td class="border-r border-gray-200"></td>
                <td id="foot-current-by_patient" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-30-by_patient" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-60-by_patient" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-90-by_patient" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-120-by_patient" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-180-by_patient" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-240-by_patient" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-365-by_patient" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-credit-by_patient" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-contract-by_patient" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-total-by_patient" class="px-4 py-3.5">—</td>
              </tr>
            </x-slot:foot>
          </x-data-table>
        </div>

        <div id="tabpanel-by_insurance" class="tab-panel hidden">
          <x-data-table id="agingTable-by_insurance" min-width="2200px" max-height="600px">
            <x-slot:head>
              <tr>
                <th class="dt-col-sticky px-4 py-3 text-gray-900 font-extrabold min-w-[180px]">Guarantor</th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[110px]">Guarantor ID</th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[240px]">Patient</th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[140px]">Patient ID</th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[110px]">Office</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Current</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 30</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 60</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 90</th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 120
                </th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 180
                </th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 240
                </th>
                <th class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[100px]">Over 365
                </th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[120px]">Credit Balance</th>
                <th class="px-4 py-3 border-r border-gray-200 min-w-[110px]">Contract</th>
                <th class="px-4 py-3 text-emerald-900 bg-emerald-50 min-w-[110px]">Total</th>
              </tr>
            </x-slot:head>
            <x-slot:foot>
              <tr class="bg-gray-50 font-bold text-gray-900 text-xs">
                <td class="dt-col-sticky bg-gray-50 px-4 py-3.5 border-r border-gray-200 text-right">Overall Total:</td>
                <td class="border-r border-gray-200"></td>
                <td class="border-r border-gray-200"></td>
                <td class="border-r border-gray-200"></td>
                <td class="border-r border-gray-200"></td>
                <td id="foot-current-by_insurance" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-30-by_insurance" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-60-by_insurance" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-90-by_insurance" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-120-by_insurance" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-180-by_insurance" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-240-by_insurance" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-365-by_insurance" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-credit-by_insurance" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-contract-by_insurance" class="px-4 py-3.5 border-r border-gray-200">—</td>
                <td id="foot-total-by_insurance" class="px-4 py-3.5">—</td>
              </tr>
            </x-slot:foot>
          </x-data-table>
        </div>

      </div>

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
              <td class="dt-col-sticky bg-gray-200 px-4 py-3.5 border-r border-gray-300">Overall Total:</td>
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
        <div
          class="mb-6 text-xs text-blue-600 bg-blue-50 border border-blue-200 rounded-lg p-3.5 flex items-center shadow-xs">
          <i class="fa-solid fa-circle-info mr-2.5 text-blue-500 text-sm"></i>
          <span>Values display Guarantor balances. Individual Patient Aging values can be viewed by selecting the
            breakout button next to the Guarantor name.</span>
        </div>

        <x-data-table id="patientTable" min-width="1400px" max-height="600px">
          <x-slot:head>
            <tr>
              <th
                class="dt-col-sticky px-4 py-3 text-gray-900 font-extrabold bg-white border-r border-gray-200 min-w-[200px]">
                <div class="flex items-center justify-between">
                  <span>Guarantor</span>
                  <i class="fa-solid fa-arrows-up-down text-[10px] text-gray-400"></i>
                </div>
              </th>
              <th class="dt-col-sticky px-4 py-3 border-r border-gray-200 text-gray-900 font-extrabold min-w-[120px]">
                Guarantor ID</th>
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
              <td class="dt-col-sticky bg-gray-200 px-4 py-3.5 border-r border-gray-300">Overall Total:</td>
              <td class="border-r border-gray-300"></td>
              <td class="border-r border-gray-300"></td>
              <td class="border-r border-gray-300"></td>
              <td id="patient-foot-current" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-30" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-60" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-90" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-120" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-180" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-240" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-365" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-credit" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-contract" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="patient-foot-total" class="px-4 py-3.5">—</td>
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
            <select
              class="border border-gray-300 rounded px-2 py-1 text-sm bg-white focus:outline-none focus:border-emerald-500 font-medium text-gray-700 w-44">
              <option value="all">INSURANCE: 0 selected</option>
            </select>
            <button
              class="bg-emerald-500 text-white font-semibold flex items-center justify-center hover:bg-emerald-600 transition px-4 py-1 rounded shadow-xs text-sm">
              Get Report
            </button>
            <div class="relative">
              <input type="text" id="insuranceSearch" placeholder="Search..."
                class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:border-emerald-500 pr-8 w-40">
              <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2 text-gray-400 text-xs"></i>
            </div>
            <button
              class="border border-emerald-500 text-emerald-600 font-semibold px-4 py-1 rounded text-sm hover:bg-emerald-50 transition shadow-xs">
              Export CSV
            </button>
          </div>
        </div>

        <x-data-table id="insuranceTable" min-width="1400px" max-height="600px">
          <x-slot:head>
            <tr>
              <th
                class="dt-col-sticky px-4 py-3 text-gray-900 font-extrabold bg-white border-r border-gray-200 min-w-[200px]">
                <div class="flex items-center justify-between">
                  <span>Guarantor</span>
                  <i class="fa-solid fa-arrows-up-down text-[10px] text-gray-400"></i>
                </div>
              </th>
              <th class="dt-col-sticky px-4 py-3 border-r border-gray-200 text-gray-900 font-extrabold min-w-[120px]">
                Guarantor ID</th>
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
              <td id="insurance-foot-30" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-60" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-90" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-120" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-180" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-240" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-365" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-credit" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-contract" class="px-4 py-3.5 border-r border-gray-300">—</td>
              <td id="insurance-foot-total" class="px-4 py-3.5">—</td>
            </tr>
          </x-slot:foot>
        </x-data-table>
      </div>

    </div>
  </main>

  <style>
    /* DataTables pagination styling — applies to every tab's table wrapper */
    #agingTabs .dt-layout-row:last-child {
      padding: 1rem 0 0;
    }

    #agingTabs .dt-paging button {
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

    #agingTabs .dt-paging button.current {
      background: #059669;
      color: #fff;
      border-color: #059669;
    }

    #agingTabs .dt-paging button:hover:not(.current) {
      background: #f9fafb;
    }

    #agingTabs .dt-info {
      font-size: 0.75rem;
      color: #6b7280;
    }

    #agingTabs .dt-length label {
      font-size: 0.75rem;
      color: #6b7280;
    }

    #agingTabs .dt-length select {
      border: 1px solid #d1d5db;
      border-radius: 0.375rem;
      padding: 0.125rem 0.375rem;
      font-size: 0.75rem;
      margin: 0 4px;
    }
  </style>

  <script>
    const baseUrl = "{{ url('') }}";

    const FULL_COLUMNS = [
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
      { data: 'guarantor_id', className: 'px-4 py-3 border-r border-gray-200 text-gray-500' },
      // Patient / Patient ID are a GROUP_CONCAT family list attached after
      // pagination, and Office is a single constant label — none has a
      // pre-LIMIT SQL sort key, so they're intentionally not orderable.
      { data: 'family_names', orderable: false, className: 'px-4 py-3 border-r border-gray-200 text-gray-900 truncate max-w-[240px]' },
      { data: 'family_ids', orderable: false, className: 'px-4 py-3 border-r border-gray-200 text-gray-500' },
      { data: 'office', orderable: false, defaultContent: '—', className: 'px-4 py-3 border-r border-gray-200 text-gray-500' },
      { data: 'bal_current', className: 'px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold' },
      { data: 'bal_30', className: 'px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold' },
      { data: 'bal_60', className: 'px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold' },
      { data: 'bal_90', className: 'px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold' },
      { data: 'bal_120', className: 'px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold' },
      { data: 'bal_180', className: 'px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold' },
      { data: 'bal_240', className: 'px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold' },
      { data: 'bal_365', className: 'px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold' },
      { data: 'credit_balance', className: 'px-4 py-3 border-r border-gray-200 text-gray-700' },
      { data: 'contract', className: 'px-4 py-3 border-r border-gray-200 text-gray-700' },
      { data: 'total', className: 'px-4 py-3 bg-emerald-50/40 text-emerald-700 font-semibold' },
    ];

    const formatOfficeCurrency = (val) => {
      if (val === null || val === undefined) return '';
      return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val);
    };

    const renderOfficeCell = (data, type, row) => {
      if (type !== 'display') return data.total;

      let colorClass = 'bg-emerald-50 text-emerald-900'; // Default Green (Top 20%)
      if (data.total > 100000) colorClass = 'bg-red-50 text-red-900';
      else if (data.total > 50000) colorClass = 'bg-amber-50 text-amber-900';

      return `<div class="flex flex-col gap-0 py-2 -mx-4 -my-3 px-4 ${colorClass} h-full justify-center min-h-[72px]">
        <div class="font-bold leading-tight">${formatOfficeCurrency(data.total)}</div>
        <div class="opacity-80 text-xs leading-tight">${formatOfficeCurrency(data.patient)}</div>
        <div class="opacity-80 text-xs leading-tight">${formatOfficeCurrency(data.insurance)}</div>
      </div>`;
    };

    const OFFICE_COLUMNS = [
      {
        data: null,
        orderable: false,
        render: (data, type, row, meta) => meta.row + 1,
        className: 'dt-col-sticky px-4 py-3 text-center font-bold text-gray-500'
      },
      {
        data: 'office',
        orderable: false,
        render: (data, type, row) => {
          if (type !== 'display') return data;
          return `<div class="flex flex-col gap-0 py-2 -mx-4 -my-3 px-4 h-full justify-center min-h-[72px] bg-white text-gray-900">
            <div class="flex items-center justify-between">
              <span class="font-bold">${data}</span>
              <button class="text-gray-400 hover:text-emerald-500 transition p-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7"/><path d="M7 7h10v10"/></svg>
              </button>
            </div>
            <div class="text-gray-500 text-xs leading-tight">- Patient</div>
            <div class="text-gray-500 text-xs leading-tight">- Insurance</div>
          </div>`;
        },
        className: 'dt-col-sticky border-r border-gray-200'
      },
      { data: 'bal_current', render: renderOfficeCell, className: 'p-0 border-r border-gray-200' },
      { data: 'bal_30', render: renderOfficeCell, className: 'p-0 border-r border-gray-200' },
      { data: 'bal_60', render: renderOfficeCell, className: 'p-0 border-r border-gray-200' },
      { data: 'bal_90', render: renderOfficeCell, className: 'p-0 border-r border-gray-200' },
      { data: 'bal_120', render: renderOfficeCell, className: 'p-0 border-r border-gray-200' },
      { data: 'bal_180', render: renderOfficeCell, className: 'p-0 border-r border-gray-200' },
      { data: 'bal_240', render: renderOfficeCell, className: 'p-0 border-r border-gray-200' },
      { data: 'bal_365', render: renderOfficeCell, className: 'p-0 border-r border-gray-200' },
      { data: 'credit_balance', render: renderOfficeCell, className: 'p-0 border-r border-gray-200' },
      { data: 'contract', render: renderOfficeCell, className: 'p-0 border-r border-gray-200' },
      { data: 'total', render: renderOfficeCell, className: 'p-0 text-emerald-900 bg-emerald-50' },
    ];

    const TAB_CONFIG = {
      responsible_party: { columns: FULL_COLUMNS, hasExtendedTotals: true },
      by_office: { columns: OFFICE_COLUMNS, hasExtendedTotals: true },
      by_patient: { columns: FULL_COLUMNS, hasExtendedTotals: true },
      by_insurance: { columns: FULL_COLUMNS, hasExtendedTotals: true },
    };

    const tables = {};
    let activeMode = 'responsible_party';

    function updateFooter(mode, totals) {
      if (!totals) return;
      $('#foot-current-' + mode).text(totals.current);
      $('#foot-30-' + mode).text(totals.thirty);
      $('#foot-60-' + mode).text(totals.sixty);
      $('#foot-90-' + mode).text(totals.ninety);
      $('#foot-total-' + mode).text(totals.grand);

      if (TAB_CONFIG[mode].hasExtendedTotals) {
        $('#foot-120-' + mode).text(totals.onetwenty ?? '—');
        $('#foot-180-' + mode).text(totals.oneeighty ?? '—');
        $('#foot-240-' + mode).text(totals.twofourty ?? '—');
        $('#foot-365-' + mode).text(totals.threesixfive ?? '—');
        $('#foot-credit-' + mode).text(totals.credit ?? '—');
        $('#foot-contract-' + mode).text(totals.contract ?? '—');
      }
    }

    // Lazily creates (once) and returns the DataTable instance for a tab.
    // Called every time a tab is selected -- the *first* call initializes
    // it (which fires its own ajax load); later calls just return the
    // existing instance so tab switches don't re-init, but still show
    // each tab's own independently-loaded data.
    function getTable(mode) {
      if (tables[mode]) {
        return tables[mode];
      }

      tables[mode] = DDS.dataTable(document.getElementById('agingTable-' + mode), {
        processing: true,
        serverSide: true,
        searching: false,
        order: [[15, 'desc']], // Total, descending — matches the previous fixed sort
        pageLength: 20,
        lengthChange: true, // this table shows a length selector
        lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, 'All']],
        ajax: {
          url: baseUrl + '/aging/data',
          type: 'GET',
          data: function (d) {
            d.search = { value: $('#agingSearch').val() };
            d.mode = mode;
            d.as_of_date = $('#asOfDate').val();
            d.credits = $('#creditsFilter').val();
          },
          dataSrc: function (json) {
            updateFooter(mode, json.totals);
            return json.data;
          },
        },
        columns: TAB_CONFIG[mode].columns,
        drawCallback: function () {
          $('#agingTable-' + mode + ' tbody tr').addClass('hover:bg-gray-50/80 transition');
        },
        language: {
          processing: '<div class="text-xs text-gray-500 py-2">Loading...</div>',
          emptyTable: '<div class="text-xs text-gray-400 py-4 text-center">No aging records found.</div>',
        },
      });

      return tables[mode];
    }

    $(document).ready(function () {

      // Initial tab is activated at the end (honors ?tab= deep-link).

      // ── Search / filters reload only the currently active tab's table ──
      let searchTimer;
      $('#agingSearch').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => tables[activeMode] && tables[activeMode].ajax.reload(), 400);
      });

      $('#refreshBtn').on('click', () => tables[activeMode] && tables[activeMode].ajax.reload());
      $('#creditsFilter').on('change', () => tables[activeMode] && tables[activeMode].ajax.reload());

      // ── Tab switching: URL-driven + deep-linkable (DDS.tabs.deeplink) ──
      //    Panels are pre-rendered; we show/hide + lazily init the tab's DataTable,
      //    and sync the active tab to ?tab= so it survives reload/back-forward/share.
      function activateTab(mode) {
        activeMode = mode;
        $('.tab-btn')
          .removeClass('border-emerald-500 text-emerald-600 font-bold')
          .addClass('border-transparent');
        $('.tab-btn[data-tab="' + mode + '"]')
          .removeClass('border-transparent')
          .addClass('border-emerald-500 text-emerald-600 font-bold');

        $('.tab-panel').addClass('hidden');
        $('#tabpanel-' + mode).removeClass('hidden');

        const wasAlreadyLoaded = !!tables[mode];
        const table = getTable(mode);
        if (wasAlreadyLoaded) table.ajax.reload();
        else table.columns.adjust();
      }

      const agingTabs = DDS.tabs.deeplink('tab', activateTab);
      $('.tab-btn').on('click', function () { agingTabs.go($(this).data('tab')); });

      // Deep-link: honor ?tab= on load, else the default active tab.
      activateTab(agingTabs.initial || activeMode);

    });
  </script>

  <x-app-components.patient-modal />

</x-app-layout>