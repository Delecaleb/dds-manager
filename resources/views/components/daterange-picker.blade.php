{{--
  x-daterange-picker — reusable Dan Grossman daterangepicker component

  Props:
    id        (string) — input element id, must be unique if multiple pickers on same page
    on-apply  (string) — name of a global JS function to call when user applies a range:
                         fn(startYMD: string, endYMD: string)
                         If null, the picker updates the input text only (use for "Update" button flows).
    class     (string) — extra classes on the outer wrapper div

  CDN assets (moment.js + daterangepicker) load only once per page via @once.
  jQuery must already be on the page (loaded by the app layout).
--}}
@props([
    'id'      => 'drp',
    'onApply' => null,
    'class'   => '',
])

@once('drp-css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<style>
  .daterangepicker .ranges li.active{background-color:#059669!important}
  .daterangepicker .ranges li:hover{background-color:#d1fae5!important;color:#065f46!important}
  .daterangepicker td.active,.daterangepicker td.active:hover{background-color:#059669!important;border-color:#059669!important}
  .daterangepicker td.in-range{background-color:#d1fae5!important;color:#065f46}
  .daterangepicker td.today{border-color:#10b981!important}
  .daterangepicker .drp-buttons .btn-primary{background-color:#10b981;border-color:#059669;border-radius:6px}
  .daterangepicker .drp-buttons .btn-primary:hover{background-color:#059669}
  .daterangepicker .drp-buttons .btn-default{border-radius:6px}
  .daterangepicker select.monthselect,.daterangepicker select.yearselect{border:1px solid #d1d5db;border-radius:4px;font-size:13px}
  .daterangepicker .calendar-table th,.daterangepicker .calendar-table td{font-size:12px}
  .daterangepicker{font-family:ui-sans-serif,system-ui,-apple-system,sans-serif;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,.12)}
  .daterangepicker .ranges ul{width:140px}
  .daterangepicker .ranges li{font-size:12px;font-weight:500;border-radius:6px;margin:2px 6px;padding:6px 10px}
</style>
@endonce

<div class="relative flex items-center border border-slate-200 rounded-lg bg-slate-50 px-3 py-1.5 gap-2 hover:border-emerald-400 hover:bg-white transition-colors cursor-pointer {{ $class }}">
  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
       class="text-slate-400 flex-shrink-0">
    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
    <line x1="16" y1="2" x2="16" y2="6"></line>
    <line x1="8" y1="2" x2="8" y2="6"></line>
    <line x1="3" y1="10" x2="21" y2="10"></line>
  </svg>
  <input type="text" id="{{ $id }}" readonly
    class="bg-transparent text-sm font-medium text-slate-700 focus:outline-none cursor-pointer min-w-[200px]"
    placeholder="Select date range…">
</div>

@once('drp-js')
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
@endonce

<script>
(function() {
  var _id = '{{ $id }}';
  var _cb = @if($onApply) '{{ $onApply }}' @else null @endif;

  function _init() {
    if (typeof $ === 'undefined' || !$.fn.daterangepicker) { setTimeout(_init, 30); return; }
    $('#' + _id).daterangepicker({
      startDate: moment().startOf('month'),
      endDate:   moment(),
      ranges: {
        'Today':        [moment(),                                        moment()],
        'Yesterday':    [moment().subtract(1,'days'),                    moment().subtract(1,'days')],
        'Last 7 Days':  [moment().subtract(6,'days'),                    moment()],
        'Last 30 Days': [moment().subtract(29,'days'),                   moment()],
        'This Month':   [moment().startOf('month'),                      moment()],
        'Last Month':   [moment().subtract(1,'month').startOf('month'),  moment().subtract(1,'month').endOf('month')],
        'This Year':    [moment().startOf('year'),                       moment()],
      },
      locale: {
        format: 'MMM D, YYYY', separator: ' – ',
        applyLabel: 'Apply', cancelLabel: 'Clear',
      },
      alwaysShowCalendars: true,
      showDropdowns: true,
      linkedCalendars: false,
    }, function(start, end) {
      if (_cb && typeof window[_cb] === 'function') {
        window[_cb](start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
      }
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', _init);
  else _init();
})();
</script>
