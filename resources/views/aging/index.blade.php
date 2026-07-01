<x-app-layout>
  <!-- Top Header Navigation -->
  <header class="bg-white border-b border-gray-100 px-8 py-4 flex justify-between items-center">
    <div class="flex items-center space-x-2">
      <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Aging</h1>
    </div>
    <a href="#" class="flex items-center bg-[#002b24] text-emerald-400 font-semibold px-4 py-2 rounded-full text-sm hover:opacity-90 transition">
      <i class="fa-solid fa-book-open mr-2"></i>
      <span>Quick Start Guide</span>
    </a>
  </header>

  <!-- Filter Controls Section -->
  <section class="bg-white border-b border-gray-200 px-8 py-4 flex flex-wrap items-center justify-between gap-4">
    <div class="flex flex-wrap items-center gap-3">
      <div class="relative flex items-center border border-gray-300 rounded px-3 py-1.5 bg-white shadow-sm">
        <i class="fa-regular fa-calendar text-gray-400 mr-2 text-sm"></i>
        <input type="text" id="asOfDate" value="{{ date('M d, Y') }}" class="text-sm font-medium text-gray-700 outline-none w-28">
      </div>
      <select class="border border-gray-300 rounded px-4 py-1.5 text-sm bg-white focus:outline-none focus:border-emerald-500 shadow-sm font-medium text-gray-700">
        <option selected>8 Mile</option>
      </select>
      <button id="refreshBtn" class="bg-white border border-emerald-500 text-emerald-600 px-5 py-1.5 rounded text-sm font-semibold hover:bg-emerald-50 transition shadow-sm">
        Refresh
      </button>
    </div>
  </section>

  <!-- Tab Bar -->
  <section class="px-8 bg-white border-b border-gray-200 flex gap-6 text-sm font-medium text-gray-500">
    <button class="tab-btn border-b-2 border-emerald-500 text-emerald-600 font-bold pb-3 pt-4" data-tab="responsible_party">Responsible Party</button>
    <button class="tab-btn border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4" data-tab="by_office">By Office</button>
    <button class="tab-btn border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4" data-tab="by_patient">By Patient</button>
    <button class="tab-btn border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4" data-tab="by_insurance">By Insurance</button>
  </section>

  <!-- Main Content -->
  <main class="p-6">

    <!-- Info Banner -->
    <div class="mb-6 text-xs text-blue-600 bg-blue-50 border border-blue-200 rounded-lg p-3.5 flex items-center shadow-xs">
      <i class="fa-solid fa-circle-info mr-2.5 text-blue-500 text-sm"></i>
      <span>Values display Guarantor balances. Individual Patient Aging values can be viewed by selecting the breakout button next to the Guarantor name.</span>
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
          <select id="creditsFilter" class="border border-gray-300 rounded px-3 py-1 text-sm bg-white focus:outline-none focus:border-emerald-500 font-medium text-gray-700">
            <option value="include">Include Credits</option>
            <option value="exclude">Exclude Credits</option>
          </select>
          <div class="relative">
            <input type="text" id="agingSearch" placeholder="Search..." class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:border-emerald-500 pr-8 w-48">
            <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2 text-gray-400 text-xs"></i>
          </div>
          <button id="exportBtn" class="border border-emerald-500 text-emerald-600 font-semibold px-4 py-1 rounded text-sm hover:bg-emerald-50 transition shadow-xs">
            Export CSV
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="w-full overflow-x-auto border border-gray-200 rounded-lg max-h-[600px] overflow-y-auto">
        <table id="agingTable" class="w-full text-left border-collapse min-w-[1200px]">
          <thead class="sticky top-0 z-30 bg-gray-100 text-gray-700 text-xs font-bold uppercase tracking-wider shadow-xs border-b border-gray-200">
            <tr>
              <th class="sticky left-0 bg-gray-100 px-4 py-3 border-r border-gray-300 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] z-40 text-gray-900 font-extrabold min-w-[180px]">
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
          </thead>
          <tbody class="divide-y divide-gray-200 text-xs font-medium text-gray-600 bg-white">
            <!-- DataTables fills this -->
          </tbody>
          <tfoot>
            <tr class="bg-gray-100 font-bold text-gray-900 text-xs">
              <td class="sticky left-0 bg-gray-100 px-4 py-3.5 border-r border-gray-300 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] z-20 text-right">Total:</td>
              <td class="border-r border-gray-200"></td>
              <td class="border-r border-gray-200"></td>
              <td class="border-r border-gray-200"></td>
              <td id="foot-current" class="px-4 py-3.5 border-r border-gray-200">—</td>
              <td id="foot-30"      class="px-4 py-3.5 border-r border-gray-200">—</td>
              <td id="foot-60"      class="px-4 py-3.5 border-r border-gray-200">—</td>
              <td id="foot-90"      class="px-4 py-3.5 border-r border-gray-200">—</td>
              <td id="foot-total"   class="px-4 py-3.5">—</td>
            </tr>
          </tfoot>
        </table>
      </div>

    </div>
  </main>

  <style>
    /* DataTables pagination styling */
    #agingTable_wrapper .dt-layout-row:last-child { padding: 1rem 0 0; }
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
    #agingTable_wrapper .dt-paging button:hover:not(.current) { background: #f9fafb; }
    #agingTable_wrapper .dt-info { font-size: 0.75rem; color: #6b7280; }
    #agingTable_wrapper .dt-length label { font-size: 0.75rem; color: #6b7280; }
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
              return `<div class="flex items-center justify-between">
                        <span class="font-bold text-gray-900 sticky left-0">${data}</span>
                        <button class="text-gray-400 hover:text-emerald-600 ml-1">
                          <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </button>
                      </div>`;
            },
            className: 'sticky left-0 bg-white px-4 py-3 border-r border-gray-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] z-20',
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

      // ── Tab switching (other tabs are stubs) ─────────────────────────────
      $('.tab-btn').on('click', function () {
        $('.tab-btn')
          .removeClass('border-emerald-500 text-emerald-600 font-bold')
          .addClass('border-transparent');
        $(this)
          .removeClass('border-transparent')
          .addClass('border-emerald-500 text-emerald-600 font-bold');
      });

    });
  </script>

</x-app-layout>
