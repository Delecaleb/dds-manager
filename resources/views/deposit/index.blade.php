<x-app-layout>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- ── HEADER ─────────────────────────────────────────── -->
  <header class="bg-white border-b border-gray-100 px-8 py-5 flex justify-between items-center">
    <div class="flex items-center space-x-2">
      <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Deposit Slip</h1>
    </div>
  </header>

  <!-- ── FILTERS ────────────────────────────────────────── -->
  <section class="bg-white border-b border-gray-200 px-8 py-3 flex flex-wrap items-center gap-3">
    <x-daterange-picker on-apply="onDrpApply" />

    <select
      class="border border-gray-300 rounded px-4 py-1.5 text-sm bg-white focus:outline-none focus:border-[#00c58e] shadow-xs font-medium text-gray-700 min-w-[150px]">
      <option selected>8 Mile</option>
      <option>All Locations</option>
    </select>

    <button
      class="bg-white border border-[#00c58e] text-[#00c58e] px-5 py-1.5 rounded text-sm font-bold hover:bg-emerald-50 transition shadow-xs">
      Refresh
    </button>
  </section>

  <!-- ── TABS ───────────────────────────────────────────── -->
  <section class="px-8 bg-white border-b border-gray-200 flex gap-6 text-sm font-bold text-gray-400">
    <button id="summaryTab" class="border-b-4 border-[#00c58e] text-gray-900 pb-2 pt-4 transition">Summary</button>
    <button id="detailTab"
      class="border-b-4 border-transparent hover:text-gray-700 pb-2 pt-4 transition">Detail</button>
  </section>

  <!-- ── MAIN CONTENT ───────────────────────────────────── -->
  <main class="p-6 max-w-[1600px] mx-auto bg-gray-50/50 min-h-screen">
    <div class="bg-white shadow-sm border border-gray-200 p-6 rounded">

      <!-- Search & Export -->
      <div class="flex items-center justify-end gap-2 mb-6">
        <div class="relative">
          <input type="text" placeholder="Search"
            class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-[#00c58e] pr-8 w-64 h-[34px]">
          <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2.5 text-gray-400 text-xs"></i>
        </div>
        <button id="exportCsvBtn"
          class="border border-[#00c58e] text-[#00c58e] font-bold px-4 py-1.5 rounded text-sm hover:bg-emerald-50 transition shadow-xs h-[34px]">
          Export CSV
        </button>
      </div>

      <!-- Table area -->
      <div id="summaryContainer" class="w-full overflow-x-auto border border-gray-100 rounded-sm">
        <table class="w-full text-left border-collapse text-sm">
          <tbody id="depositTbody" class="divide-y divide-gray-100">
            <!-- Populated by JS -->
            <tr>
              <td colspan="3" class="text-center py-6 text-gray-400">Loading summary...</td>
            </tr>
          </tbody>
          <tfoot id="depositTfoot" class="hidden">
            <tr class="bg-gray-200/60 font-bold text-gray-800">
              <td colspan="2" class="px-4 py-3 text-right">Total:</td>
              <td id="tableTotalAmount" class="px-6 py-3 min-w-[150px]">$ 0.00</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Detail Table area -->
      <div id="detailContainer" class="hidden w-full overflow-x-auto border border-gray-100 rounded-sm">
        <table class="w-full text-left border-collapse text-sm min-w-[1600px]">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th
                class="px-4 py-3 font-semibold text-gray-600 border-r border-gray-200 dds-stick bg-gray-50 min-w-[120px]">
                <input type="checkbox" class="mr-2 rounded border-gray-300 text-[#00c58e]">Office
              </th>
              <th class="px-4 py-3 font-semibold text-gray-600 border-r border-gray-200">Patient Name</th>
              <th class="px-4 py-3 font-semibold text-gray-600 border-r border-gray-200">Patient ID</th>
              <th class="px-4 py-3 font-semibold text-gray-600 border-r border-gray-200">Provider</th>
              <th class="px-4 py-3 font-semibold text-gray-600 border-r border-gray-200">Provider ID</th>
              <th class="px-4 py-3 font-semibold text-gray-600 border-r border-gray-200">Date</th>
              <th class="px-4 py-3 font-semibold text-gray-600 border-r border-gray-200">Payment Type</th>
              <th class="px-4 py-3 font-semibold text-gray-600 border-r border-gray-200">Type</th>
              <th class="px-4 py-3 font-semibold text-gray-600 border-r border-gray-200">Insurance</th>
              <th class="px-4 py-3 font-semibold text-gray-600 border-r border-gray-200">Bank</th>
              <th class="px-4 py-3 font-semibold text-gray-600 border-r border-gray-200">Check Number</th>
              <th class="px-4 py-3 font-semibold text-gray-600 border-r border-gray-200">Unallocated</th>
              <th class="px-4 py-3 font-semibold bg-[#00c58e]/10 text-emerald-800">Amount</th>
            </tr>
          </thead>
          <tbody id="detailTbody" class="divide-y divide-gray-100">
            <tr>
              <td colspan="13" class="text-center py-6 text-gray-400">Loading details...</td>
            </tr>
          </tbody>
          <tfoot id="detailTfoot" class="hidden">
            <tr class="bg-gray-200/60 font-bold text-gray-800 text-right">
              <td colspan="12" class="px-4 py-3">Total:</td>
              <td id="detailTableTotalAmount" class="px-4 py-3 min-w-[120px] text-left">$ 0.00</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-4 flex flex-wrap items-center justify-between gap-4 text-xs font-medium text-gray-600 mt-4">
        <div class="flex items-center gap-2">
          <span>Items per page</span>
          <select class="bg-white border border-gray-300 rounded px-2 py-1 focus:outline-none shadow-xs font-semibold">
            <option>10</option>
            <option>20</option>
            <option>50</option>
          </select>
          <span class="ml-2" id="paginationSummary">1-0 of 0 items</span>
        </div>

        <div class="flex items-center gap-1">
          <select
            class="bg-white border border-gray-300 rounded px-2 py-1 focus:outline-none shadow-xs mr-2 font-semibold font-mono">
            <option>1</option>
          </select>
          <span>of 1 pages</span>
          <button
            class="p-1 px-2.5 border border-gray-300 rounded bg-white hover:bg-gray-50 text-gray-500 ml-3 shadow-xs transition-colors"><i
              class="fa-solid fa-angle-left text-xs"></i></button>
          <button
            class="p-1 px-2.5 border border-gray-300 rounded bg-white hover:bg-gray-50 text-gray-500 shadow-xs transition-colors"><i
              class="fa-solid fa-angle-right text-xs"></i></button>
        </div>
      </div>

    </div>
  </main>

  <script>
    var _depositsData = [];
    var _detailsData = [];
    var _totalAmount = 0;
    var _currentTab = 'summary';

    function fmtMoney(v) {
      return '$ ' + Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function renderSummary(data, total) {
      var tbody = $('#depositTbody');
      tbody.empty();

      if (!data || data.length === 0) {
        tbody.html('<tr><td colspan="3" class="text-center py-6 text-gray-400">No summary data found for this period.</td></tr>');
        $('#depositTfoot').addClass('hidden');
        if (_currentTab === 'summary') $('#paginationSummary').text('0 items');
        return;
      }

      data.forEach(function (dep) {
        var locHtml = '';
        if (dep.location) {
          locHtml = `<div class="flex items-center justify-between text-gray-600">
            <span>${dep.location}</span>
            <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-300"></i>
          </div>`;
        }

        var tr = `<tr class="hover:bg-gray-50 transition even:bg-gray-50/50">
          <td class="px-4 py-3 min-w-[200px] w-1/4 border-r border-white font-medium text-xs text-gray-700">${locHtml}</td>
          <td class="px-4 py-3 w-1/2 border-r border-white font-medium text-xs text-gray-600">${dep.type}</td>
          <td class="px-6 py-3 min-w-[150px] w-1/4 font-medium text-xs text-gray-700">${fmtMoney(dep.amount)}</td>
        </tr>`;
        tbody.append(tr);
      });

      $('#tableTotalAmount').text(fmtMoney(total));
      $('#depositTfoot').removeClass('hidden');
      if (_currentTab === 'summary') $('#paginationSummary').text(`1-${data.length} of ${data.length} items`);
    }

    function renderDetail(data, total) {
      var tbody = $('#detailTbody');
      tbody.empty();

      if (!data || data.length === 0) {
        tbody.html('<tr><td colspan="13" class="text-center py-6 text-gray-400">No detail data found for this period.</td></tr>');
        $('#detailTfoot').addClass('hidden');
        if (_currentTab === 'detail') $('#paginationSummary').text('0 items');
        return;
      }

      data.forEach(function (det) {
        var locHtml = `<input type="checkbox" class="mr-2 rounded border-gray-300 text-[#00c58e]">
          <span class="mr-2">${det.office}</span>
          <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-300"></i>`;

        var patientHtml = `<div class="flex items-center justify-between">
            <span>${det.patient_name}</span>
            <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-300"></i>
          </div>`;

        var patIdHtml = `<div class="flex items-center justify-between">
            <span>${det.patient_id}</span>
            <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-300"></i>
          </div>`;

        var provHtml = `<div class="flex items-center justify-between">
            <span>${det.provider}</span>
            <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-300"></i>
          </div>`;

        var tr = `<tr class="hover:bg-gray-50 transition even:bg-white text-xs text-gray-600">
          <td class="px-4 py-2 border-r border-gray-100 bg-white dds-stick flex items-center shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">${locHtml}</td>
          <td class="px-4 py-2 border-r border-gray-100">${patientHtml}</td>
          <td class="px-4 py-2 border-r border-gray-100">${patIdHtml}</td>
          <td class="px-4 py-2 border-r border-gray-100">${provHtml}</td>
          <td class="px-4 py-2 border-r border-gray-100">${det.provider_id}</td>
          <td class="px-4 py-2 border-r border-gray-100">${det.date}</td>
          <td class="px-4 py-2 border-r border-gray-100">${det.payment_type}</td>
          <td class="px-4 py-2 border-r border-gray-100">${det.type}</td>
          <td class="px-4 py-2 border-r border-gray-100">${det.insurance}</td>
          <td class="px-4 py-2 border-r border-gray-100">${det.bank}</td>
          <td class="px-4 py-2 border-r border-gray-100">${det.check_number}</td>
          <td class="px-4 py-2 border-r border-gray-100">${det.unallocated}</td>
          <td class="px-4 py-2 bg-[#00c58e]/5 text-gray-800 font-medium">${fmtMoney(det.amount)}</td>
        </tr>`;
        tbody.append(tr);
      });

      $('#detailTableTotalAmount').text(fmtMoney(total));
      $('#detailTfoot').removeClass('hidden');
      if (_currentTab === 'detail') $('#paginationSummary').text(`1-${data.length} of ${data.length} items`);
    }

    function fetchDeposits(start, end) {
      if (!start) start = moment().startOf('year').format('YYYY-MM-DD');
      if (!end) end = moment().endOf('year').format('YYYY-MM-DD');

      var skelSummary = '';
      for (let i = 0; i < 3; i++) {
        skelSummary += '<tr class="animate-pulse"><td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded w-3/4"></div></td><td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded w-1/2"></div></td><td class="px-6 py-3"><div class="h-4 bg-gray-200 rounded w-1/4"></div></td></tr>';
      }
      $('#depositTbody').html(skelSummary);
      $('#depositTfoot').addClass('hidden');

      var skelDetail = '';
      for (let i = 0; i < 5; i++) {
        skelDetail += '<tr class="animate-pulse">';
        for (let c = 0; c < 13; c++) skelDetail += '<td class="px-4 py-3"><div class="h-3 bg-gray-200 rounded w-full"></div></td>';
        skelDetail += '</tr>';
      }
      $('#detailTbody').html(skelDetail);
      $('#detailTfoot').addClass('hidden');

      $.get('{{ route("deposits.data") }}', { start_date: start, end_date: end })
        .done(function (res) {
          _depositsData = res.deposits || [];
          _detailsData = res.details || [];
          _totalAmount = res.summary.total_amount || 0;
          renderSummary(_depositsData, _totalAmount);
          renderDetail(_detailsData, _totalAmount);
        })
        .fail(function () {
          $('#depositTbody').html('<tr><td colspan="3" class="text-center py-6 text-red-500">Failed to load summary data.</td></tr>');
          $('#detailTbody').html('<tr><td colspan="13" class="text-center py-6 text-red-500">Failed to load detail data.</td></tr>');
        });
    }

    function downloadCsv(filename, rows) {
      var csvFile = new Blob([rows.join('\n')], { type: 'text/csv' });
      var downloadLink = document.createElement('a');
      downloadLink.download = filename;
      downloadLink.href = window.URL.createObjectURL(csvFile);
      downloadLink.style.display = 'none';
      document.body.appendChild(downloadLink);
      downloadLink.click();
      document.body.removeChild(downloadLink);
    }

    $('#exportCsvBtn').on('click', function () {
      if (_currentTab === 'summary') {
        var rows = ['Location,Type,Amount'];
        _depositsData.forEach(function (dep) {
          rows.push([
            '"' + (dep.location || '') + '"',
            '"' + (dep.type || '') + '"',
            dep.amount
          ].join(','));
        });
        rows.push(['"Total"', '""', _totalAmount].join(','));
        downloadCsv('deposit-summary.csv', rows);
      } else {
        var rows = ['Office,Patient Name,Patient ID,Provider,Provider ID,Date,Payment Type,Type,Insurance,Bank,Check Number,Unallocated,Amount'];
        _detailsData.forEach(function (det) {
          rows.push([
            '"' + (det.office || '') + '"',
            '"' + (det.patient_name || '') + '"',
            '"' + (det.patient_id || '') + '"',
            '"' + (det.provider || '') + '"',
            '"' + (det.provider_id || '') + '"',
            '"' + (det.date || '') + '"',
            '"' + (det.payment_type || '') + '"',
            '"' + (det.type || '') + '"',
            '"' + (det.insurance || '') + '"',
            '"' + (det.bank || '') + '"',
            '"' + (det.check_number || '') + '"',
            '"' + (det.unallocated || '') + '"',
            det.amount
          ].join(','));
        });
        rows.push(['"Total"', '', '', '', '', '', '', '', '', '', '', '', _totalAmount].join(','));
        downloadCsv('deposit-details.csv', rows);
      }
    });

    $('#summaryTab').on('click', function () {
      _currentTab = 'summary';
      $(this).addClass('border-[#00c58e] text-gray-900').removeClass('border-transparent hover:text-gray-700');
      $('#detailTab').removeClass('border-[#00c58e] text-gray-900').addClass('border-transparent hover:text-gray-700');
      $('#summaryContainer').show();
      $('#detailContainer').hide();
      $('#paginationSummary').text(`1-${_depositsData.length} of ${_depositsData.length} items`);
    });

    $('#detailTab').on('click', function () {
      _currentTab = 'detail';
      $(this).addClass('border-[#00c58e] text-gray-900').removeClass('border-transparent hover:text-gray-700');
      $('#summaryTab').removeClass('border-[#00c58e] text-gray-900').addClass('border-transparent hover:text-gray-700');
      $('#summaryContainer').hide();
      $('#detailContainer').show();
      $('#paginationSummary').text(`1-${_detailsData.length} of ${_detailsData.length} items`);
    });

    window.onDrpApply = function (start, end) { fetchDeposits(start, end); };

    $(document).ready(function () {
      fetchDeposits();
    });
  </script>
</x-app-layout>