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

    <div class="relative min-w-[200px]">
      <select id="officeSelect"
        class="w-full appearance-none bg-white border border-gray-300 rounded px-3 py-1.5 text-sm font-medium text-gray-700 focus:outline-none focus:border-[#00c58e] shadow-xs cursor-pointer pr-8">
        <option value="all">All Locations</option>
        @if(isset($offices) && $offices->count())
          @foreach($offices as $off)
            <option value="{{ $off->id }}" {{ (isset($activeOfficeId) && $off->id == $activeOfficeId) ? 'selected' : '' }}>
              {{ $off->name }}
            </option>
          @endforeach
        @else
          <option value="{{ $activeOfficeId ?? 1 }}" selected>{{ \App\Models\Office::getActiveOffice()?->name ?? 'Main Office' }}</option>
        @endif
      </select>
      <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-gray-400">
        <i data-lucide="chevron-down" class="w-4 h-4"></i>
      </div>
    </div>

    @if(isset($clinics) && count($clinics) > 1)
    <div class="relative min-w-[180px]" id="clinicSelectWrapper">
      <select id="clinicSelect"
        class="w-full appearance-none bg-white border border-gray-300 rounded px-3 py-1.5 text-sm font-medium text-gray-700 focus:outline-none focus:border-[#00c58e] shadow-xs cursor-pointer pr-8">
        <option value="all" {{ ($activeClinicNum ?? null) === null ? 'selected' : '' }}>All Clinics</option>
        @foreach($clinics as $cNum => $cName)
          <option value="{{ $cNum }}" {{ ($activeClinicNum ?? null) !== null && (string)$cNum === (string)$activeClinicNum ? 'selected' : '' }}>{{ $cName }}</option>
        @endforeach
      </select>
      <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-gray-400">
        <i data-lucide="chevron-down" class="w-4 h-4"></i>
      </div>
    </div>
    @endif

    <button id="refreshBtn"
      class="bg-white border border-[#00c58e] text-[#00c58e] px-5 py-1.5 rounded text-sm font-bold hover:bg-emerald-50 transition shadow-xs cursor-pointer">
      Refresh
    </button>
  </section>

  <!-- ── TABS ───────────────────────────────────────────── -->
  <section class="px-8 bg-white border-b border-gray-200 flex flex-nowrap overflow-x-auto gap-6 text-sm font-bold text-gray-400 dds-tab-nav">
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
          <input type="text" id="searchInput" placeholder="Search"
            class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-[#00c58e] pr-8 w-64 h-[34px]">
          <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2.5 text-gray-400 text-xs"></i>
        </div>
        <button id="exportCsvBtn"
          class="border border-[#00c58e] text-[#00c58e] font-bold px-4 py-1.5 rounded text-sm hover:bg-emerald-50 transition shadow-xs h-[34px]">
          Export CSV
        </button>
      </div>
        <!-- Summary Tab View -->
      <div id="summaryTabContent" class="flex flex-col">
        <!-- Skeleton Loader Section (Replaces table while loading) -->
        <div id="summarySkeleton" class="w-full bg-white overflow-hidden" style="min-height: 300px;">
          <x-table-skeleton />
        </div>

        <!-- Summary Table Container (Hidden while loading) -->
        <div id="summaryTableContainer" class="hidden w-full overflow-x-auto border border-gray-100 rounded-sm">
          <table id="summaryTable" class="w-full text-left border-collapse text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-4 py-3 font-semibold text-gray-600 border-r border-gray-200 w-1/4">Location</th>
                <th class="px-4 py-3 font-semibold text-gray-600 border-r border-gray-200 w-1/2">Payment Type</th>
                <th class="px-6 py-3 font-semibold bg-[#00c58e]/10 text-emerald-800 w-1/4 text-right">Amount</th>
              </tr>
            </thead>
            <tbody id="depositTbody" class="divide-y divide-gray-100">
            </tbody>
            <tfoot id="depositTfoot">
              <tr class="bg-gray-200/60 font-bold text-gray-800">
                <td colspan="2" class="px-4 py-3 text-right">Total:</td>
                <td id="tableTotalAmount" class="px-6 py-3 min-w-[150px] text-right">$ 0.00</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <x-table-pagination id="summary" :default-length="10" />
      </div>

      <!-- Detail Tab View -->
      <div id="detailTabContent" class="hidden flex flex-col">
        <!-- Skeleton Loader Section (Replaces table while loading) -->
        <div id="detailSkeleton" class="w-full bg-white overflow-hidden" style="min-height: 480px;">
          <x-table-skeleton />
        </div>

        <!-- Detail Table Container (Hidden while loading) -->
        <div id="detailTableContainer" class="hidden w-full overflow-x-auto border border-gray-100 rounded-sm">
          <table id="detailTable" class="dds-table w-full text-left border-collapse text-sm min-w-[1600px]">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-4 py-3 font-semibold text-gray-600 border-r border-gray-200 dds-stick bg-gray-50 min-w-[140px]">
                  <span class="flex items-center"><input type="checkbox" id="selectAllDetails" class="mr-2 rounded border-gray-300 text-[#00c58e]" onclick="event.stopPropagation()">Office</span>
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
                <th class="px-4 py-3 font-semibold bg-[#00c58e]/10 text-emerald-800 text-right">Amount</th>
              </tr>
            </thead>
            <tbody id="detailTbody" class="divide-y divide-gray-100">
            </tbody>
            <tfoot id="detailTfoot">
              <tr class="bg-gray-200/60 font-bold text-gray-800 text-right">
                <td colspan="12" class="px-4 py-3 text-right">Total:</td>
                <td id="detailTableTotalAmount" class="px-4 py-3 min-w-[120px] text-right">$ 0.00</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <x-table-pagination id="detail" :default-length="20" />
      </div>

    </div>
  </main>

  <script>
    var _depositsData = [];
    var _detailsData = [];
    var _totalAmount = 0;
    var _currentTab = 'summary';

    var summaryTable = null;
    var detailTable = null;

    function showSummaryLoading() {
      $('#summarySkeleton').removeClass('hidden');
      $('#summaryTableContainer').addClass('hidden');
    }

    function hideSummaryLoading() {
      $('#summarySkeleton').addClass('hidden');
      $('#summaryTableContainer').removeClass('hidden');
    }

    function showDetailLoading() {
      $('#detailSkeleton').removeClass('hidden');
      $('#detailTableContainer').addClass('hidden');
    }

    function hideDetailLoading() {
      $('#detailSkeleton').addClass('hidden');
      $('#detailTableContainer').removeClass('hidden');
    }

    function initSummaryTable() {
      if (summaryTable) return;

      summaryTable = DDS.dataTable(document.getElementById('summaryTable'), {
        data: _depositsData,
        processing: false,
        serverSide: false,
        paging: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        searching: true,
        ordering: true,
        dom: 'rt',
        layout: { topStart: null, topEnd: null, bottomStart: null, bottomEnd: null },
        columns: [
          {
            data: 'location',
            name: 'location',
            render: function (data) {
              return data
                ? `<div class="flex items-center justify-between text-gray-600"><span>${data}</span><i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-300"></i></div>`
                : '—';
            }
          },
          {
            data: 'type',
            name: 'type',
            render: function (data) {
              return data || 'Uncategorized Payment';
            }
          },
          {
            data: 'amount',
            name: 'amount',
            className: 'text-right',
            render: function (data, type) {
              if (type === 'sort' || type === 'type') return parseFloat(data) || 0;
              return DDS.fmt.money(data);
            }
          }
        ],
        createdRow: function (row, data, dataIndex) {
          $(row).addClass('hover:bg-gray-50 transition even:bg-gray-50/50');
          $('td', row).addClass('px-4 py-3 font-medium text-xs text-gray-700 border-r border-white');
          $('td:eq(0)', row).addClass('min-w-[200px] w-1/4');
          $('td:eq(1)', row).addClass('w-1/2 text-gray-600');
          $('td:eq(2)', row).addClass('min-w-[150px] w-1/4 text-emerald-800 bg-[#00c58e]/10 text-right font-medium');
        },
        drawCallback: function () {
          hideSummaryLoading();
        },
        footerCallback: function (row, data, start, end, display) {
          var api = this.api();
          var total = api.column(2, { search: 'applied' }).data().reduce(function (a, b) {
            return (parseFloat(a) || 0) + (parseFloat(b) || 0);
          }, 0);
          $('#tableTotalAmount').text(DDS.fmt.money(total));
        }
      });

      DDS.bindPagination(summaryTable, 'summary', { onLoading: showSummaryLoading });
    }

    function initDetailTable() {
      if (detailTable) return;

      detailTable = DDS.dataTable(document.getElementById('detailTable'), {
        data: _detailsData,
        processing: false,
        serverSide: false,
        paging: true,
        pageLength: 20,
        lengthChange: false,
        info: false,
        searching: true,
        ordering: true,
        dom: 'rt',
        layout: { topStart: null, topEnd: null, bottomStart: null, bottomEnd: null },
        columns: [
          {
            data: 'office',
            name: 'office',
            className: 'dds-stick bg-white',
            render: function (data) {
              return `<div class="flex items-center"><input type="checkbox" class="detail-row-chk mr-2 rounded border-gray-300 text-[#00c58e]"><span class="mr-2">${data || ''}</span><i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-300"></i></div>`;
            }
          },
          {
            data: 'patient_name',
            name: 'patient_name',
            render: function (data) {
              return data ? `<div class="flex items-center justify-between"><span>${data}</span><i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-300"></i></div>` : '';
            }
          },
          {
            data: 'patient_id',
            name: 'patient_id',
            render: function (data) {
              return (data !== null && data !== undefined && data !== '') ? `<div class="flex items-center justify-between"><span>${data}</span><i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-300"></i></div>` : '';
            }
          },
          {
            data: 'provider',
            name: 'provider',
            render: function (data) {
              return data ? `<div class="flex items-center justify-between"><span>${data}</span><i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-300"></i></div>` : '';
            }
          },
          { data: 'provider_id', name: 'provider_id', defaultContent: '' },
          { data: 'date', name: 'date', defaultContent: '' },
          { data: 'payment_type', name: 'payment_type', defaultContent: '' },
          { data: 'type', name: 'type', defaultContent: '' },
          { data: 'insurance', name: 'insurance', defaultContent: '' },
          { data: 'bank', name: 'bank', defaultContent: '' },
          { data: 'check_number', name: 'check_number', defaultContent: '' },
          { data: 'unallocated', name: 'unallocated', defaultContent: '' },
          {
            data: 'amount',
            name: 'amount',
            className: 'text-right',
            render: function (data, type) {
              if (type === 'sort' || type === 'type') return parseFloat(data) || 0;
              return DDS.fmt.money(data);
            }
          }
        ],
        createdRow: function (row, data, dataIndex) {
          $(row).addClass('hover:bg-gray-50 transition even:bg-white text-xs text-gray-600');
          $('td', row).addClass('px-4 py-2 border-r border-gray-100');
          $('td:eq(0)', row).addClass('dds-stick bg-white flex items-center shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]');
          $('td:eq(12)', row).addClass('bg-[#00c58e]/5 text-gray-800 font-medium text-right');
        },
        drawCallback: function () {
          hideDetailLoading();
        },
        footerCallback: function (row, data, start, end, display) {
          var api = this.api();
          var total = api.column(12, { search: 'applied' }).data().reduce(function (a, b) {
            return (parseFloat(a) || 0) + (parseFloat(b) || 0);
          }, 0);
          $('#detailTableTotalAmount').text(DDS.fmt.money(total));
        }
      });

      DDS.bindPagination(detailTable, 'detail', { onLoading: showDetailLoading });
    }

    function fetchDeposits(start, end) {
      if (!start || !end) {
        var picker = $('#drp').data('daterangepicker');
        if (picker) {
          start = picker.startDate.format('YYYY-MM-DD');
          end = picker.endDate.format('YYYY-MM-DD');
        } else {
          var params = new URLSearchParams(window.location.search);
          start = params.get('start_date') || moment().startOf('month').format('YYYY-MM-DD');
          end = params.get('end_date') || moment().format('YYYY-MM-DD');
        }
      }

      showSummaryLoading();
      showDetailLoading();

      var officeId = $('#officeSelect').val();
      var clinicNum = $('#clinicSelect').length ? $('#clinicSelect').val() : null;
      var params = { start_date: start, end_date: end };
      if (officeId && officeId !== '') {
        params.office_id = officeId;
      }
      if (clinicNum && clinicNum !== '') {
        params.clinic_num = clinicNum;
      }

      $.get('{{ route("deposits.data") }}', params)
        .done(function (res) {
          _depositsData = res.deposits || [];
          _detailsData = res.details || [];
          _totalAmount = res.summary ? res.summary.total_amount : 0;

          if (!summaryTable) {
            initSummaryTable();
          } else {
            summaryTable.clear().rows.add(_depositsData).draw();
          }

          if (!detailTable) {
            initDetailTable();
          } else {
            detailTable.clear().rows.add(_detailsData).draw();
          }
        })
        .fail(function () {
          hideSummaryLoading();
          hideDetailLoading();
          if (summaryTable) {
            summaryTable.clear().draw();
          }
          if (detailTable) {
            detailTable.clear().draw();
          }
        });
    }

    function downloadCsv(filename, rows) {
      var csvFile = new Blob([rows.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
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
        var rows = ['Location,Payment Type,Amount'];
        var data = summaryTable ? summaryTable.rows({ search: 'applied' }).data().toArray() : _depositsData;
        var total = 0;
        data.forEach(function (dep) {
          var amt = parseFloat(dep.amount) || 0;
          total += amt;
          rows.push([
            '"' + (dep.location || '').replace(/"/g, '""') + '"',
            '"' + (dep.type || '').replace(/"/g, '""') + '"',
            amt
          ].join(','));
        });
        rows.push(['"Total"', '""', total].join(','));
        downloadCsv('deposit-summary.csv', rows);
      } else {
        var headers = ['Office', 'Patient Name', 'Patient ID', 'Provider', 'Provider ID', 'Date', 'Payment Type', 'Type', 'Insurance', 'Bank', 'Check Number', 'Unallocated', 'Amount'];
        var rows = [headers.join(',')];
        var data = detailTable ? detailTable.rows({ search: 'applied' }).data().toArray() : _detailsData;
        var total = 0;
        data.forEach(function (det) {
          var amt = parseFloat(det.amount) || 0;
          total += amt;
          rows.push([
            '"' + (det.office || '').replace(/"/g, '""') + '"',
            '"' + (det.patient_name || '').replace(/"/g, '""') + '"',
            '"' + (det.patient_id != null ? det.patient_id : '') + '"',
            '"' + (det.provider || '').replace(/"/g, '""') + '"',
            '"' + (det.provider_id != null ? det.provider_id : '') + '"',
            '"' + (det.date || '').replace(/"/g, '""') + '"',
            '"' + (det.payment_type || '').replace(/"/g, '""') + '"',
            '"' + (det.type || '').replace(/"/g, '""') + '"',
            '"' + (det.insurance || '').replace(/"/g, '""') + '"',
            '"' + (det.bank || '').replace(/"/g, '""') + '"',
            '"' + (det.check_number || '').replace(/"/g, '""') + '"',
            '"' + (det.unallocated || '').replace(/"/g, '""') + '"',
            amt
          ].join(','));
        });
        rows.push(['"Total"', '""', '""', '""', '""', '""', '""', '""', '""', '""', '""', '""', total].join(','));
        downloadCsv('deposit-details.csv', rows);
      }
    });

    $('#summaryTab').on('click', function () {
      _currentTab = 'summary';
      $(this).addClass('border-[#00c58e] text-gray-900').removeClass('border-transparent hover:text-gray-700');
      $('#detailTab').removeClass('border-[#00c58e] text-gray-900').addClass('border-transparent hover:text-gray-700');
      $('#summaryTabContent').removeClass('hidden');
      $('#detailTabContent').addClass('hidden');
      if (summaryTable) {
        var q = $('#searchInput').val() || '';
        summaryTable.search(q).columns.adjust().draw();
      }
    });

    $('#detailTab').on('click', function () {
      _currentTab = 'detail';
      $(this).addClass('border-[#00c58e] text-gray-900').removeClass('border-transparent hover:text-gray-700');
      $('#summaryTab').removeClass('border-[#00c58e] text-gray-900').addClass('border-transparent hover:text-gray-700');
      $('#summaryTabContent').addClass('hidden');
      $('#detailTabContent').removeClass('hidden');
      if (detailTable) {
        var q = $('#searchInput').val() || '';
        detailTable.search(q).columns.adjust().draw();
      }
    });

    $('#searchInput').on('keyup input', function () {
      var val = this.value;
      if (_currentTab === 'summary' && summaryTable) {
        summaryTable.search(val).draw();
      } else if (_currentTab === 'detail' && detailTable) {
        detailTable.search(val).draw();
      }
    });

    $(document).on('change', '#selectAllDetails', function () {
      var checked = $(this).is(':checked');
      $('.detail-row-chk').prop('checked', checked);
    });

    $(document).on('change', '.detail-row-chk', function () {
      var total = $('.detail-row-chk').length;
      var checked = $('.detail-row-chk:checked').length;
      $('#selectAllDetails').prop('checked', total > 0 && total === checked);
    });

    window.onDrpApply = function (start, end) { fetchDeposits(start, end); };

    document.addEventListener('daterange:changed', function (e) {
      if (e.detail && e.detail.start && e.detail.end) {
        fetchDeposits(e.detail.start, e.detail.end);
      }
    });

    $('#officeSelect').on('change', function () {
      fetchDeposits();
    });

    $('#clinicSelect').on('change', function () {
      fetchDeposits();
    });

    $('#refreshBtn').on('click', function () {
      fetchDeposits();
    });

    $(document).ready(function () {
      fetchDeposits();
    });
  </script>
</x-app-layout>