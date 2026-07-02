<x-app-layout>

  <!-- Flatpickr date-range picker -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

  <style>
    .flatpickr-day.selected,.flatpickr-day.startRange,.flatpickr-day.endRange,
    .flatpickr-day.selected:hover,.flatpickr-day.startRange:hover,.flatpickr-day.endRange:hover
      { background:#059669; border-color:#059669; }
    .flatpickr-day.inRange
      { background:#d1fae5; border-color:#d1fae5; box-shadow:-5px 0 0 #d1fae5,5px 0 0 #d1fae5; }
    .flatpickr-day.today { border-color:#059669; }
    @keyframes skel-pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
    .skel { background:#e5e7eb; border-radius:.375rem; animation:skel-pulse 1.5s ease-in-out infinite; display:inline-block; }
  </style>

  <header class="bg-white border-b border-gray-100 px-8 py-4 flex justify-between items-center">
    <div class="flex items-center space-x-2">
      <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Financials</h1>
    </div>
  </header>

  <section class="bg-white border-b border-gray-200 px-8 py-4">
    <div class="flex flex-wrap items-center gap-3">
      <div class="relative flex items-center border border-gray-300 rounded px-3 py-1.5 bg-white shadow-sm">
        <i class="fa-regular fa-calendar text-gray-400 mr-2 text-sm"></i>
        <input type="text" id="dateRange" readonly placeholder="Select date range&hellip;"
               class="text-sm bg-white focus:outline-none font-medium text-gray-700 w-52 cursor-pointer">
      </div>
      <select class="border border-gray-300 rounded px-4 py-1.5 text-sm bg-white focus:outline-none focus:border-emerald-500 shadow-sm font-medium text-gray-700">
        <option selected>8 Mile</option>
      </select>
      <button id="updateBtn" class="bg-white border border-emerald-500 text-emerald-600 px-5 py-1.5 rounded text-sm font-semibold hover:bg-emerald-50 transition shadow-sm">
        Update
      </button>
      <span id="fetchError" class="hidden text-xs text-red-600 font-medium">
        <i class="fa-solid fa-triangle-exclamation mr-1"></i>Failed to load data.
      </span>
    </div>
  </section>

  <section class="px-8 bg-white border-b border-gray-200 flex gap-6 text-sm font-medium text-gray-500">
    <button class="border-b-2 border-emerald-500 text-emerald-600 font-bold pb-3 pt-4">Summary</button>
    <button class="border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4">Score Cards</button>
  </section>

  <main class="p-6 space-y-6 max-w-[1600px] mx-auto">

    <div class="font-bold border-b p-3 text-sm text-gray-700">Revenue</div>
    <section class="grid grid-cols-1 md:grid-cols-4 gap-6">

      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Gross Production</p>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="gross-production">
              <span class="skel h-8 w-32"></span>
            </h4>
          </div>
          <div class="p-3 bg-emerald-50 rounded-lg text-emerald-600">
            <i class="fa-solid fa-wallet text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
          Completed procedures in the selected period
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Net Production</p>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="net-production">
              <span class="skel h-8 w-32"></span>
            </h4>
          </div>
          <div class="p-3 bg-teal-50 rounded-lg text-teal-600">
            <i class="fa-solid fa-calendar-day text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
          Gross minus adjustments and write-offs
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Adjustment</p>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="adjustment">
              <span class="skel h-8 w-28"></span>
            </h4>
          </div>
          <div class="p-3 bg-red-50 rounded-lg text-red-500">
            <i class="fa-solid fa-sliders text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
          <span id="adjustment-rate" class="text-red-600 font-semibold">
            <span class="skel h-3 w-14"></span>
          </span>
          adjustment rate
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Collection</p>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="collection-rate">
              <span class="skel h-8 w-24"></span>
            </h4>
          </div>
          <div class="p-3 bg-amber-50 rounded-lg text-amber-600">
            <i class="fa-solid fa-percent text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
          Target: 80.00% &nbsp;|&nbsp;
          <span id="collections-amt" class="font-semibold text-gray-700">
            <span class="skel h-3 w-20"></span>
          </span> collected
        </div>
      </div>
    </section>

    <div class="font-bold border-b p-3 text-sm text-gray-700">Patients</div>
    <section class="grid grid-cols-1 md:grid-cols-5 gap-6">

      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Patient Visits</p>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="patient-visits">
              <span class="skel h-8 w-16"></span>
            </h4>
          </div>
          <div class="p-3 bg-blue-50 rounded-lg text-blue-600">
            <i class="fa-solid fa-user-plus text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
          
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">New Patient Visits</p>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="new-patient-visits">
              <span class="skel h-8 w-16"></span>
            </h4>
          </div>
          <div class="p-3 bg-blue-50 rounded-lg text-blue-600">
            <i class="fa-solid fa-user-plus text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
          
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Patient Scheduled</p>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="patients-scheduled">
              <span class="skel h-8 w-16"></span>
            </h4>
          </div>
          <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600">
            <i class="fa-solid fa-user-check text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
          Appointments scheduled in the period
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">New Patient Scheduled</p>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="new-patients-scheduled">
              <span class="skel h-8 w-16"></span>
            </h4>
          </div>
          <div class="p-3 bg-gray-50 rounded-lg text-gray-600">
            <i class="fa-solid fa-user-slash text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
          First-time patients booked in the period
        </div>
      </div>

    </section>
  </main>

  <script>
    const baseUrl      = "{{ url('') }}";
    const today        = new Date();
    const firstOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);

    function fmtDate(d) {
      return d.toISOString().substring(0, 10);
    }
    function fmtMoney(v) {
      return '$ ' + Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function showSkeletons() {
      $('#gross-production').html('<span class="skel h-8 w-32"></span>');
      $('#net-production').html('<span class="skel h-8 w-32"></span>');
      $('#adjustment').html('<span class="skel h-8 w-28"></span>');
      $('#adjustment-rate').html('<span class="skel h-3 w-14"></span>');
      $('#collection-rate').html('<span class="skel h-8 w-24"></span>');
      $('#collections-amt').html('<span class="skel h-3 w-20"></span>');
      $('#patient-visits, #patients-scheduled, #new-patients-scheduled')
        .html('<span class="skel h-8 w-16"></span>');
      $('#fetchError').addClass('hidden');
    }

    function populate(data) {
      $('#gross-production').text(fmtMoney(data.gross_production));
      $('#net-production').text(fmtMoney(data.net_production));
      $('#adjustment').text(fmtMoney(data.adjustments));
      $('#adjustment-rate').text(data.adjustment_rate + '%');
      $('#collection-rate').text(data.collection_rate + '%');
      $('#collections-amt').text(fmtMoney(data.collections));
      if (data.patient_visits         != null) $('#patient-visits').text(data.patient_visits);
      if (data.patients_scheduled     != null) $('#patients-scheduled').text(data.patients_scheduled);
      if (data.new_patients_scheduled != null) $('#new-patients-scheduled').text(data.new_patients_scheduled);
    }

    function fetchAnalytics(start, end) {
      showSkeletons();
      $.get(baseUrl + '/financials/data', { start_date: start, end_date: end })
        .done(function (data) { populate(data); })
        .fail(function () {
          ['#gross-production','#net-production','#adjustment','#collection-rate',
           '#patient-visits','#patients-scheduled','#new-patients-scheduled']
            .forEach(function (id) { $(id).text('--'); });
          $('#adjustment-rate, #collections-amt').text('--');
          $('#fetchError').removeClass('hidden');
        });
    }

    const picker = flatpickr('#dateRange', {
      mode: 'range',
      dateFormat: 'Y-m-d',
      altInput: true,
      altFormat: 'M j, Y',
      defaultDate: [fmtDate(firstOfMonth), fmtDate(today)],
      onChange: function (selectedDates) {
        if (selectedDates.length === 2) {
          fetchAnalytics(fmtDate(selectedDates[0]), fmtDate(selectedDates[1]));
        }
      },
    });

    $('#updateBtn').on('click', function () {
      const dates = picker.selectedDates;
      if (dates.length === 2) fetchAnalytics(fmtDate(dates[0]), fmtDate(dates[1]));
    });

    $(document).ready(function () {
      fetchAnalytics(fmtDate(firstOfMonth), fmtDate(today));
    });
  </script>

</x-app-layout>
