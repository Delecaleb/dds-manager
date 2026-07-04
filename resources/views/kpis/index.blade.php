<x-app-layout>

<style>
  @keyframes skel-pulse{0%,100%{opacity:1}50%{opacity:.4}}
  .skel{display:inline-block;background:#e5e7eb;border-radius:.375rem;animation:skel-pulse 1.5s ease-in-out infinite}

  /* KPI card */
  .kpi-card{padding:14px 16px 12px;border-bottom:1px solid #f1f5f9}
  .kpi-card:nth-child(5n){border-right:none}
  .kpi-section{border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;margin-bottom:24px}
  .kpi-section-hdr{display:flex;align-items:center;gap:10px;padding:14px 18px 12px;border-bottom:3px solid currentColor;background:#fff}
  .kpi-grid{display:grid;grid-template-columns:repeat(5,1fr);background:#fff}
  .kpi-card{padding:14px 16px 12px;border-right:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9;position:relative}
  .kpi-card:nth-child(5n){border-right:none}

  /* Tooltip */
  .kpi-tip-wrap{position:relative;display:inline-flex}
  .kpi-tip-wrap .tip-box{
    display:none;position:absolute;top:calc(100% + 6px);left:50%;transform:translateX(-50%);
    background:#1e293b;color:#f1f5f9;font-size:11px;line-height:1.4;padding:7px 10px;
    border-radius:7px;width:190px;white-space:normal;z-index:50;pointer-events:none;
    box-shadow:0 4px 16px rgba(0,0,0,.18)
  }
  .kpi-tip-wrap:hover .tip-box{display:block}
</style>

<!-- ── Header ─────────────────────────────────────────────────────────────── -->
<header class="bg-white border-b border-gray-100 px-8 py-4 flex justify-between items-center">
  <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">KPIs</h1>
  <a href="#" class="flex items-center bg-[#002b24] text-emerald-400 font-semibold px-4 py-2 rounded-full text-sm hover:opacity-90 transition">
    <i class="fa-solid fa-book-open mr-2"></i> Quick Start Guide
  </a>
</header>

<!-- ── Filter bar ─────────────────────────────────────────────────────────── -->
<section class="bg-white border-b border-gray-200 px-8 py-3 flex flex-wrap items-center gap-3">
  <x-daterange-picker id="kpiDateRange" />

  <select id="kpiLocation" class="border border-gray-300 rounded px-4 py-1.5 text-sm bg-white focus:outline-none focus:border-emerald-500 shadow-sm font-medium text-gray-700">
    <option value="all">All Locations</option>
    <option value="0" selected>8 Mile</option>
  </select>

  <button id="kpiUpdateBtn"
    class="bg-white border border-emerald-500 text-emerald-600 px-5 py-1.5 rounded text-sm font-semibold hover:bg-emerald-50 transition shadow-sm">
    Update
  </button>

  <span id="kpiLoading" class="hidden text-xs text-slate-400 ml-2 flex items-center gap-1.5">
    <svg class="animate-spin w-3.5 h-3.5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
    </svg>
    Loading…
  </span>
</section>

<!-- ── Tabs ───────────────────────────────────────────────────────────────── -->
<section class="px-8 bg-white border-b border-gray-200 flex gap-6 text-sm font-medium text-gray-500">
  <button class="kpi-tab-btn border-b-2 border-emerald-500 text-emerald-600 font-bold pb-3 pt-4" data-tab="main">Main</button>
  <button class="kpi-tab-btn border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4" data-tab="specialty">Specialty</button>
  <button class="kpi-tab-btn border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4" data-tab="providers">Providers</button>
  <button class="kpi-tab-btn border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4" data-tab="specialty-providers">Specialty Providers</button>
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
    <div class="kpi-section-hdr text-indigo-700" style="border-color:#6366f1">
      <span class="text-base font-extrabold tracking-tight">Doctor</span>
    </div>
    <div class="kpi-grid" id="doctor-grid">
      <!-- rendered by JS -->
    </div>
  </div>

  <!-- Office Section -->
  <div class="kpi-section">
    <div class="kpi-section-hdr text-teal-700" style="border-color:#14b8a6">
      <span class="text-base font-extrabold tracking-tight">Office</span>
    </div>
    <div class="kpi-grid" id="office-grid">
      <!-- rendered by JS -->
    </div>
  </div>

</main>

<!-- ── Other tabs (stubs) ─────────────────────────────────────────────────── -->
<div id="tab-specialty"          class="kpi-tab-content hidden p-8 text-sm text-slate-400">Specialty KPIs — coming soon.</div>
<div id="tab-providers"          class="kpi-tab-content hidden p-8 text-sm text-slate-400">Providers KPIs — coming soon.</div>
<div id="tab-specialty-providers" class="kpi-tab-content hidden p-8 text-sm text-slate-400">Specialty Providers KPIs — coming soon.</div>

<!-- FontAwesome (free CDN) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">

<script>
var _kpiBase = "{{ url('') }}";
var _kpiStart, _kpiEnd;

/* ── Card definitions ──────────────────────────────────────────────────── */
var HYGIENE_CARDS = [
  {k:'perio_pct',             label:'Perio %',                              fmt:'pct',   tip:'% of hygiene visits that included periodontal treatment (SRP, perio maint.)'},
  {k:'fluoride_per_day',      label:'# of Fluoride app. per day',           fmt:'dec1',  tip:'Average number of fluoride applications (D1203–D1209) per work day'},
  {k:'avg_prod_per_day',      label:'Avg. Prod. per Day',                   fmt:'money', tip:'Average total hygiene production per work day'},
  {k:'avg_prod_per_prov_day', label:'Avg Production per Provider Per Day',  fmt:'money', tip:'Average hygiene production per hygiene provider per work day'},
  {k:'prod_per_visit',        label:'Production per patient visit',          fmt:'money', tip:'Average hygiene production per patient visit (distinct patient-day)'},
  {k:'fmx_per_day',           label:'Avg. Fmx per day',                     fmt:'dec1',  tip:'Average full-mouth X-rays and panoramics (D0210, D0330) per work day'},
  {k:'srp_per_day',           label:'Avg. SRP per day',                     fmt:'dec2',  tip:'Scaling & Root Planing procedures (D4341, D4342) per work day'},
  {k:'visits_per_day',        label:'Number of visits per day',             fmt:'dec1',  tip:'Average number of hygiene patient visits per work day'},
  {k:'reappt',                label:'Hygiene Reappointment',                fmt:'pct',   tip:'% of completed hygiene appointments where the next appointment was scheduled'},
  {k:'perio_reappt',          label:'Perio Reappointment',                  fmt:'pct',   tip:'% of perio treatment appointments with a follow-up appointment scheduled'},
  {k:'adult_retention_12m',   label:'Adult Hygiene Retention (12 months)',  fmt:'pct',   tip:'% of active adult patients seen for hygiene at least once in the last 12 months'},
  {k:'adult_retention_6m',    label:'Adult Hygiene Retention (6 months)',   fmt:'pct',   tip:'% of active adult patients seen for hygiene at least once in the last 6 months'},
  {k:'child_retention_12m',   label:'Child Hygiene Retention (12 months)',  fmt:'pct',   tip:'% of active child patients seen for hygiene at least once in the last 12 months'},
  {k:'child_retention_6m',    label:'Child Hygiene Retention (6 months)',   fmt:'pct',   tip:'% of active child patients seen for hygiene at least once in the last 6 months'},
  {k:'sealants',              label:'Sealants',                             fmt:'int',   tip:'Total sealant procedures (D1351, D1352) completed in the period'},
  {k:'whitening',             label:'Whitening Procedures',                 fmt:'int',   tip:'Total whitening / bleaching procedures completed in the period'},
  {k:'antimicrobial',         label:'Antimicrobial Placement',              fmt:'int',   tip:'Total local antimicrobial placements (D4381) completed'},
  {k:'prod_per_proc',         label:'Hygiene Production per Procedure',     fmt:'money', tip:'Average fee per completed hygiene procedure'},
  {k:'visits_with_tx_pct',    label:'% of Hygiene Visits with TX Plan',    fmt:'pct',   tip:'% of hygiene patient visits where the patient has any planned (TP) procedures in the period'},
  {k:'tx_plans_per_day',      label:'# of Tx plan per Day',                fmt:'dec1',  tip:'Average number of distinct treatment-plan entries per work day'},
  {k:'avg_prod_per_hour',     label:'Average Hygiene Production per Hour',  fmt:'money', tip:'Average hygiene production per hour of chair time (based on appointment pattern length)'},
  {k:'case_acceptance',       label:'Case Acceptance Rate',                 fmt:'pct',   tip:'% of hygiene procedures with a Completed status vs. all hygiene procedure statuses'},
];

var DOCTOR_CARDS = [
  {k:'case_acceptance_same_day', label:'Case Acceptance – Same Day',           fmt:'pct',   tip:'% of same-day treatment that was accepted and completed at the visit'},
  {k:'case_acceptance_rate',     label:'Case Acceptance Rate',                       fmt:'pct',   tip:'% of doctor procedures Completed vs. all doctor procedure statuses'},
  {k:'new_pt_tx_dollars',        label:'$ New Patients Receiving Treatment Plans',   fmt:'money', tip:'Total treatment plan $ presented to new patients in the period'},
  {k:'existing_pt_tx_dollars',   label:'$ Existing Patients Receiving Treatment Plans',fmt:'money',tip:'Total treatment plan $ presented to existing patients in the period'},
  {k:'avg_apt_time_mins',        label:'Avg Time per Doctor Appointment (minutes)',   fmt:'dec2',  tip:'Average length of a completed doctor appointment in minutes (based on appointment pattern)'},
  {k:'avg_prod_per_hour',        label:'Average Doctor Production per Hour',          fmt:'money', tip:'Average doctor production per hour of chair time'},
  {k:'avg_prod_per_apt',         label:'Average Production per Doctor Appointment',   fmt:'money', tip:'Average doctor production per completed doctor appointment'},
  {k:'same_day_tx_per_new_pt',   label:'Same Day Treatment per New Patient',          fmt:'money', tip:'Average value of same-day treatment completed at a new patient visit'},
  {k:'avg_prod_per_prov_day',    label:'Avg Production per Provider Per Day',         fmt:'money', tip:'Average doctor production per provider per work day'},
  {k:'avg_tx_per_existing_pt',   label:'Avg. Treatment plan ($) per Existing Pts.',  fmt:'money', tip:'Average treatment plan dollar value per existing patient who received a plan'},
  {k:'avg_tx_per_new_pt',        label:'Avg. Treatment plan ($) per New Pts.',        fmt:'money', tip:'Average treatment plan dollar value per new patient who received a plan'},
  {k:'pct_new_pt_with_tx',       label:'% of new patients w/ treatment plans',        fmt:'pct',   tip:'% of new patients in the period who received a treatment plan'},
  {k:'pct_existing_pt_with_tx',  label:'% of existing patients w/ treatment plans',   fmt:'pct',   tip:'% of existing patients in the period who received a treatment plan'},
  {k:'reappt',                   label:'Doctor Reappoint',                            fmt:'pct',   tip:'% of completed doctor appointments where the next appointment was scheduled'},
  {k:'prod_per_exam',            label:'Doctor Production per Exam',                  fmt:'money', tip:'Total doctor production divided by the number of evaluation/exam procedures completed'},
  {k:'total_production',         label:'Total Doctor Production',                     fmt:'money', tip:'Total production from all non-hygiene completed procedures in the period'},
];

var OFFICE_CARDS = [
  {k:'patient_retention',    label:'Patient Retention',                     fmt:'pct',   tip:'% of active patients seen for any treatment in the last 18 months'},
  {k:'tx_plans_per_day',     label:'# of Treatment Plans per Day',          fmt:'dec1',  tip:'Average number of distinct treatment plan entries per work day'},
  {k:'co_pay_collection',    label:'Co-Pay Collection',                     fmt:'pct',   tip:'Total payments collected as a % of total production billed in the period'},
  {k:'unscheduled_tx',       label:'Unscheduled Tx $',                      fmt:'money', tip:'Total dollar value of all outstanding treatment-planned (TP) procedures — current snapshot'},
  {k:'new_pt_fmx_pct',       label:'New Patients Fmx %',                   fmt:'pct',   tip:'% of new patients who had a full-mouth X-ray or panoramic taken at their first visit'},
  {k:'no_show_rate',         label:'No Show Rate',                          fmt:'pct',   tip:'% of appointments in the period that have a Broken status'},
  {k:'reactivation_list',    label:'Patient Reactivation List',             fmt:'comma', tip:'Number of active patients who have not had a completed procedure in the last 18 months'},
  {k:'patient_attrition',    label:'Patient Attrition',                     fmt:'comma', tip:'Active patients with no visits in the period and no future scheduled appointment'},
  {k:'patient_growth',       label:'Patient Growth',                        fmt:'signed',tip:'New patients in the period minus patients who went inactive in the period'},
  {k:'active_patients',      label:'# of Active Patients',                  fmt:'comma', tip:'Total number of patients with an Active status in Open Dental'},
  {k:'active_in_recare_pct', label:'% of Active Patients in Hygiene Recare',fmt:'pct',  tip:'% of active patients who have an active, non-disabled recall with a future due date'},
];

/* ── Formatting ────────────────────────────────────────────────────────── */
function fmtKpi(val, fmt) {
  if (val === null || val === undefined) return '—';
  var n = parseFloat(val) || 0;
  switch (fmt) {
    case 'pct':    return (n % 1 === 0 ? n : n.toFixed(2)) + '%';
    case 'money':
      var abs = Math.abs(n).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
      return n < 0 ? '$ (' + abs + ')' : '$ ' + abs;
    case 'dec1':   return n.toFixed(1);
    case 'dec2':   return n.toFixed(2);
    case 'int':    return Math.round(n).toLocaleString();
    case 'comma':  return Math.round(n).toLocaleString();
    case 'signed': return (n >= 0 ? '' : '') + Math.round(n).toLocaleString();
    default:       return String(val);
  }
}

/* ── Render grids ──────────────────────────────────────────────────────── */
function renderGrid(containerId, cards, data) {
  var $c = document.getElementById(containerId);
  if (!$c) return;
  var html = '';
  cards.forEach(function(card) {
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
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function showSkeletons() {
  renderGrid('hygiene-grid', HYGIENE_CARDS, null);
  renderGrid('doctor-grid',  DOCTOR_CARDS,  null);
  renderGrid('office-grid',  OFFICE_CARDS,  null);
}

/* ── Data fetch — 3 parallel independent requests ──────────────────────── */
var _kpiPending = 0;

function fetchSection(path, gridId, cards, start, end) {
  var qs = '?start_date=' + start + '&end_date=' + end;
  _kpiPending++;
  fetch(_kpiBase + path + qs)
    .then(function(r) { return r.json(); })
    .then(function(d) {
      renderGrid(gridId, cards, d);
    })
    .catch(function() {
      renderGrid(gridId, cards, {});
    })
    .finally(function() {
      _kpiPending--;
      if (_kpiPending === 0) {
        document.getElementById('kpiLoading').classList.add('hidden');
      }
    });
}

function fetchKpis(start, end) {
  _kpiStart = start;
  _kpiEnd   = end;
  _kpiPending = 0;
  showSkeletons();
  document.getElementById('kpiLoading').classList.remove('hidden');

  fetchSection('/kpis/hygiene', 'hygiene-grid', HYGIENE_CARDS, start, end);
  fetchSection('/kpis/doctor',  'doctor-grid',  DOCTOR_CARDS,  start, end);
  fetchSection('/kpis/office',  'office-grid',  OFFICE_CARDS,  start, end);
}

/* ── Init ──────────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function() {
  // Tab switching
  document.querySelectorAll('.kpi-tab-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.kpi-tab-btn').forEach(function(b) {
        b.classList.remove('border-emerald-500','text-emerald-600','font-bold');
        b.classList.add('border-transparent');
      });
      btn.classList.add('border-emerald-500','text-emerald-600','font-bold');
      btn.classList.remove('border-transparent');
      document.querySelectorAll('.kpi-tab-content').forEach(function(t) { t.classList.add('hidden'); });
      var tab = document.getElementById('tab-' + btn.dataset.tab);
      if (tab) tab.classList.remove('hidden');
    });
  });

  // Update button reads current daterangepicker selection
  document.getElementById('kpiUpdateBtn').addEventListener('click', function() {
    var drp = $('#kpiDateRange').data('daterangepicker');
    if (!drp) return;
    fetchKpis(drp.startDate.format('YYYY-MM-DD'), drp.endDate.format('YYYY-MM-DD'));
  });

  // Initial load — wait for moment to be available
  var _tryInit = setInterval(function() {
    if (typeof moment === 'undefined') return;
    clearInterval(_tryInit);
    fetchKpis(moment().startOf('year').format('YYYY-MM-DD'), moment().format('YYYY-MM-DD'));
  }, 30);
});
</script>

</x-app-layout>
