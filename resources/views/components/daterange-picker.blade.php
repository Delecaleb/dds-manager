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
  .daterangepicker .ranges ul{width:160px}
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

  // Restore the range from the URL (?start_date&end_date) so deep-links/reloads persist it.
  var _params = new URLSearchParams(window.location.search);
  var _startParam = _params.get('start_date');
  var _endParam = _params.get('end_date');

  function _init() {
    if (typeof $ === 'undefined' || !$.fn.daterangepicker) { setTimeout(_init, 30); return; }
    $('#' + _id).daterangepicker({
      startDate: _startParam ? moment(_startParam, 'YYYY-MM-DD') : moment().startOf('month'),
      endDate:   _endParam ? moment(_endParam, 'YYYY-MM-DD') : moment(),
      ranges: {
        'Today':           [moment(),                                                       moment()],
        'Yesterday':       [moment().subtract(1, 'days'),                                   moment().subtract(1, 'days')],
        'This week':       [moment().startOf('week'),                                       moment().endOf('week')],
        'Last week':       [moment().subtract(1, 'week').startOf('week'),                   moment().subtract(1, 'week').endOf('week')],
        'Month to date':   [moment().startOf('month'),                                      moment()],
        'This month':      [moment().startOf('month'),                                      moment().endOf('month')],
        'Last month':      [moment().subtract(1, 'month').startOf('month'),                 moment().subtract(1, 'month').endOf('month')],
        'Quarter to date': [moment().startOf('quarter'),                                    moment()],
        'This quarter':    [moment().startOf('quarter'),                                    moment().endOf('quarter')],
        'Last quarter':    [moment().subtract(1, 'quarter').startOf('quarter'),            moment().subtract(1, 'quarter').endOf('quarter')],
        'This year':       [moment().startOf('year'),                                       moment().endOf('year')],
        'Year to date':    [moment().startOf('year'),                                       moment()],
        'Last year':       [moment().subtract(1, 'year').startOf('year'),                   moment().subtract(1, 'year').endOf('year')],
        'Last 12 Months':  [moment().subtract(11, 'months').startOf('month'),               moment()],
      },
      locale: {
        format: 'MMM D, YYYY', separator: ' – ',
        applyLabel: 'Apply', cancelLabel: 'Clear',
      },
      alwaysShowCalendars: true,
      showDropdowns: true,
      linkedCalendars: false,
    }, function(start, end) {
      var s = start.format('YYYY-MM-DD'), e = end.format('YYYY-MM-DD');
      // Canonical: dispatch an event any consumer can listen to via DDS.onDateRange(id, cb).
      document.dispatchEvent(new CustomEvent('daterange:changed', { detail: { id: _id, start: s, end: e } }));
      // Back-compat: still call a named global if on-apply was provided.
      if (_cb && typeof window[_cb] === 'function') window[_cb](s, e);
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', _init);
  else _init();
})();
</script>
