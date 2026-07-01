<x-app-layout>

{{-- FullCalendar Scheduler (includes resource-timegrid) --}}
<link href='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.15/index.global.min.css' rel='stylesheet'>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.15/index.global.min.js'></script>

<style>
    /* ---------- FullCalendar overrides ---------- */
    .fc .fc-toolbar                        { display: none !important; }
    .fc .fc-col-header-cell                { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
    .fc .fc-col-header-cell-cushion        { font-size: 0.75rem; font-weight: 700; color: #1e293b;
                                             text-decoration: none; padding: 8px 4px; }
    .fc .fc-timegrid-slot                  { height: 32px; }
    .fc .fc-timegrid-slot-label-cushion    { font-size: 0.68rem; color: #94a3b8; font-weight: 500; }
    .fc .fc-timegrid-now-indicator-line    { border-color: #ef4444; }
    .fc .fc-timegrid-now-indicator-arrow   { border-top-color: #ef4444; border-bottom-color: #ef4444; }
    .fc-event                              { cursor: pointer; border-radius: 4px !important;
                                             border: none !important; }
    .fc-event-main                         { overflow: hidden; }
    .fc-license-message                    { display: none !important; }
    /* Resource header column */
    .fc .fc-resource-timeline-divider      { width: 0; }
    .fc-datagrid-cell-cushion              { font-size: 0.7rem; }
    /* Scrollbar */
    .fc-scroller::-webkit-scrollbar        { width: 6px; height: 6px; }
    .fc-scroller::-webkit-scrollbar-track  { background: #f1f5f9; }
    .fc-scroller::-webkit-scrollbar-thumb  { background: #cbd5e1; border-radius: 4px; }
    /* Active view button */
    .view-btn.active                       { background: white; box-shadow: 0 1px 2px rgba(0,0,0,.1);
                                             color: #059669; font-weight: 700; }
    /* Skeleton progress bar */
    #skel-bar                              { transition: width 0.35s cubic-bezier(.4,0,.2,1); }
    #cal-skeleton                          { transition: opacity 0.45s ease; }
    @keyframes skel-pulse {
        0%, 100% { opacity: 1; }
        50%       { opacity: .45; }
    }
    .skel-pulse { animation: skel-pulse 1.6s ease-in-out infinite; }
</style>

<div class="flex flex-col bg-slate-50" style="min-height: calc(100vh - 64px);">

    {{-- ══════════════════ TOP TOOLBAR ══════════════════ --}}
    <div class="bg-white border-b border-slate-200 px-6 py-3 flex items-center justify-between gap-4 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="relative flex items-center border border-slate-300 rounded px-3 py-1.5 gap-2 bg-white shadow-sm">
                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"/>
                    <line x1="16" y1="2" x2="16" y2="6" stroke-width="2"/>
                    <line x1="8" y1="2" x2="8" y2="6" stroke-width="2"/>
                    <line x1="3" y1="10" x2="21" y2="10" stroke-width="2"/>
                </svg>
                <input type="date" id="calDate"
                    class="border-0 outline-none text-sm font-semibold text-slate-700 bg-transparent cursor-pointer"
                    value="{{ date('Y-m-d') }}">
            </div>

            <select id="clinicFilter"
                class="border border-slate-300 rounded px-3 py-1.5 text-sm font-medium text-slate-700 bg-white shadow-sm focus:outline-none focus:border-emerald-500 min-w-[120px]">
                <option>8 Mile</option>
            </select>

            <button id="refreshBtn"
                class="border border-emerald-500 text-emerald-600 px-5 py-1.5 rounded text-sm font-semibold hover:bg-emerald-50 transition shadow-sm">
                Refresh
            </button>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex bg-slate-100 rounded-md border border-slate-200 p-0.5 gap-0.5">
                <button class="view-btn px-4 py-1.5 text-xs font-medium rounded text-slate-500 transition-all" data-view="dayGridMonth">Month</button>
                <button class="view-btn px-4 py-1.5 text-xs font-medium rounded text-slate-500 transition-all" data-view="resourceTimeGridWeek">Week</button>
                <button class="view-btn active px-4 py-1.5 text-xs font-medium rounded transition-all" data-view="resourceTimeGridDay">Day</button>
            </div>
        </div>
    </div>

    {{-- ══════════════════ TABS ══════════════════ --}}
    <div class="bg-white border-b border-slate-200 px-6">
        <nav class="flex gap-6">
            <button id="tab-calendar"
                class="cal-tab py-2.5 text-sm font-bold text-slate-900 border-b-2 border-emerald-500">
                Appointments Calendar
            </button>
            <button id="tab-details"
                class="cal-tab py-2.5 text-sm font-medium text-slate-400 border-b-2 border-transparent hover:text-slate-600 transition-colors">
                Appointment Details
            </button>
            <button id="tab-capacity"
                class="cal-tab py-2.5 text-sm font-medium text-slate-400 border-b-2 border-transparent hover:text-slate-600 transition-colors">
                Appointment Capacity
            </button>
        </nav>
    </div>

    {{-- ══════════════════ STATS ROW ══════════════════ --}}
    <div class="bg-white border-b border-slate-200 px-6 py-3 flex items-center gap-10 flex-shrink-0">
        <div>
            <p class="text-xs text-slate-500 mb-0.5">Production</p>
            <p class="text-xl font-bold text-slate-900" id="stat-production">—</p>
        </div>
        <div>
            <p class="text-xs text-slate-500 mb-0.5">Scheduled Production</p>
            <p class="text-xl font-bold text-slate-900" id="stat-scheduled">—</p>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" id="activeColumnsToggle" class="sr-only peer" checked>
                <div class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:bg-emerald-500
                     after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                     after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all
                     peer-checked:after:translate-x-full"></div>
                <span class="ml-2 text-sm font-medium text-slate-600">Active Columns only</span>
            </label>
        </div>
    </div>

    {{-- ══════════════════ NAV BAR ══════════════════ --}}
    <div class="bg-white border-b border-slate-200 px-6 py-2 flex items-center justify-between flex-shrink-0">
        <button id="prevBtn"
            class="p-1.5 rounded border border-slate-300 hover:bg-slate-50 transition">
            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <div class="flex items-center gap-3 text-sm">
            <span id="calDateLabel" class="font-bold text-slate-900 text-base"></span>
            <span class="text-slate-300">|</span>
            <span id="liveTime" class="font-medium text-slate-500"></span>
        </div>
        <button id="nextBtn"
            class="p-1.5 rounded border border-slate-300 hover:bg-slate-50 transition">
            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>

    {{-- ══════════════════ CALENDAR + SIDEBAR ══════════════════ --}}
    <div class="flex flex-1 overflow-hidden bg-white">

        {{-- Calendar --}}
        <div id="calendar-wrap" class="flex-1 overflow-auto p-3 relative">
            <div id="calendar"></div>
        </div>

        {{-- Appointment Detail Sidebar (hidden until event clicked) --}}
        <div id="apt-sidebar"
            class="hidden w-80 border-l border-slate-200 bg-white flex-col overflow-y-auto flex-shrink-0">

            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Appointment Details</h3>
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <button onclick="closeSidebar()"
                        class="text-slate-400 hover:text-slate-700 transition-colors p-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <line x1="18" y1="6" x2="6" y2="18" stroke-width="2"/>
                            <line x1="6" y1="6" x2="18" y2="18" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div id="sidebar-body" class="p-4 flex-1">
                <p class="text-xs text-slate-400 text-center mt-8">Click an appointment to see details</p>
            </div>

        </div>
    </div>

</div>

<script>
const baseUrl = "{{ url('') }}";
let calendar;

// ── Skeleton builder ─────────────────────────────────────────────
function buildSkeleton() {
    const slotH    = 32;
    const numCols  = 5;
    const times    = ['6:00 AM','6:30 AM','7:00 AM','7:30 AM','8:00 AM','8:30 AM',
                      '9:00 AM','9:30 AM','10:00 AM','10:30 AM','11:00 AM','11:30 AM'];

    const headerCols = Array.from({ length: numCols }, (_, i) => `
        <div class="flex-1 flex flex-col items-center gap-1.5 py-3 border-r border-slate-200 last:border-0">
            <div class="h-2 w-8 bg-slate-200 rounded skel-pulse" style="animation-delay:${i*80}ms"></div>
            <div class="h-3 w-14 bg-slate-200 rounded skel-pulse" style="animation-delay:${i*80+40}ms"></div>
        </div>`).join('');

    const timeRows = times.map(t => `
        <div class="flex-shrink-0 flex items-start justify-end pr-2 pt-0.5 border-b border-slate-100" style="height:${slotH}px">
            <div class="h-2 w-10 bg-slate-100 rounded skel-pulse"></div>
        </div>`).join('');

    const gridRows = times.map(() =>
        `<div class="border-b border-slate-100 col-span-5" style="height:${slotH}px"></div>`
    ).join('');

    // Fake appointment blocks: { col 0-4, startSlot index, spanSlots }
    const fakeApts = [
        { col:0, start:6, span:2 }, { col:1, start:8, span:3 },
        { col:2, start:6, span:1 }, { col:3, start:7, span:2 },
        { col:4, start:8, span:4 }, { col:1, start:4, span:1 },
        { col:3, start:4, span:1 }, { col:0, start:9, span:2 },
    ].map(({ col, start, span }) => {
        const colW   = 100 / numCols;
        const leftPc = col * colW;
        const inner  = span > 1
            ? `<div class="h-2 w-14 bg-emerald-100 rounded mt-1 skel-pulse"></div>` : '';
        return `<div class="absolute rounded skel-pulse overflow-hidden"
                     style="top:${start*slotH+2}px;height:${span*slotH-4}px;
                            left:calc(${leftPc}% + 3px);width:calc(${colW}% - 6px);
                            background:linear-gradient(160deg,#d1fae5,#a7f3d0);
                            border-left:3px solid #10b981;">
                    <div class="p-1.5">
                        <div class="h-2.5 w-20 bg-emerald-200 rounded skel-pulse"></div>
                        ${inner}
                    </div>
                </div>`;
    }).join('');

    return `
<div id="cal-skeleton" class="absolute inset-0 z-30 bg-white flex flex-col overflow-hidden">

    {{-- Progress bar --}}
    <div class="flex-shrink-0 px-3 pt-3 pb-2">
        <div class="flex items-center justify-between mb-1.5">
            <span id="skel-label" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Initializing...</span>
            <span id="skel-pct" class="text-sm font-bold text-emerald-600 tabular-nums">0%</span>
        </div>
        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
            <div id="skel-bar" class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500" style="width:0%"></div>
        </div>
    </div>

    {{-- Column headers --}}
    <div class="flex-shrink-0 flex border-y border-slate-200 bg-slate-50">
        <div class="w-16 flex-shrink-0 border-r border-slate-200"></div>
        <div class="flex-1 flex">${headerCols}</div>
    </div>

    {{-- Grid body --}}
    <div class="flex flex-1 overflow-hidden">
        {{-- Time labels --}}
        <div class="w-16 flex-shrink-0 border-r border-slate-200 flex flex-col overflow-hidden bg-white">
            ${timeRows}
        </div>
        {{-- Appointment area --}}
        <div class="flex-1 overflow-hidden relative">
            <div class="absolute inset-0 grid pointer-events-none" style="grid-template-columns:repeat(${numCols},1fr)">
                ${gridRows}
            </div>
            ${fakeApts}
        </div>
    </div>
</div>`;
}

// ── Progress helpers ─────────────────────────────────────────────
function setProgress(pct, label) {
    const bar   = document.getElementById('skel-bar');
    const pctEl = document.getElementById('skel-pct');
    const lblEl = document.getElementById('skel-label');
    if (!bar) return;
    bar.style.width = pct + '%';
    if (pctEl) pctEl.textContent = pct + '%';
    if (lblEl && label) lblEl.textContent = label;
    if (pct >= 100) setTimeout(hideSkeleton, 420);
}

function hideSkeleton() {
    const skel = document.getElementById('cal-skeleton');
    if (!skel) return;
    skel.style.opacity = '0';
    setTimeout(() => skel.remove(), 460);
}

function showCalSkeleton(label) {
    let skel = document.getElementById('cal-skeleton');
    if (!skel) {
        document.getElementById('calendar-wrap').insertAdjacentHTML('afterbegin', buildSkeleton());
        skel = document.getElementById('cal-skeleton');
    }
    skel.style.opacity = '1';
    setProgress(5, label || 'Loading...');
}

const STATUS_MAP = {
    1: 'Scheduled', 2: 'Complete', 3: 'UnschedList',
    4: 'ASAP', 5: 'Broken', 6: 'Planned',
    7: 'PtNote', 8: 'PtNoteCompleted'
};

document.addEventListener('DOMContentLoaded', function () {

    const calEl = document.getElementById('calendar');

    // Show skeleton immediately before the calendar even starts constructing
    showCalSkeleton('Initializing...');

    calendar = new FullCalendar.Calendar(calEl, {
        schedulerLicenseKey: 'CC-Attribution-NonCommercialNoDerivatives',
        initialView: 'resourceTimeGridDay',
        initialDate: '{{ date("Y-m-d") }}',
        headerToolbar: false,
        nowIndicator: true,
        slotDuration: '00:30:00',
        slotMinTime: '06:00:00',
        slotMaxTime: '21:00:00',
        height: 'auto',
        expandRows: false,
        allDaySlot: false,
        resourceOrder: 'title',

        // ── Resources (provider columns) ──────────────────────────
        resources: function (info, success, fail) {
            setProgress(20, 'Loading providers...');
            const date = (info.startStr || info.start?.toISOString() || document.getElementById('calDate').value || '{{ date("Y-m-d") }}').substring(0, 10);
            fetch(baseUrl + '/calendar/resources?date=' + date)
                .then(r => r.json())
                .then(data => {
                    console.log('[FC Resources]', data);
                    setProgress(48, 'Loading appointments...');
                    success(data);
                })
                .catch(err => { setProgress(100, 'Failed to load providers'); fail(err); });
        },

        // ── Events (appointments) ─────────────────────────────────
        events: function (info, success, fail) {
            setProgress(55, 'Fetching appointments...');
            const start = (info.startStr || info.start?.toISOString() || '{{ date("Y-m-d") }}').substring(0, 10);
            const end   = info.end
                ? new Date(info.end - 1).toISOString().substring(0, 10)
                : start;
            fetch(baseUrl + '/calendar/data?start=' + start + '&end=' + end)
                .then(r => r.json())
                .then(data => {
                    console.log('[FC Events] count:', data.length);
                    if (data.length) console.log('[FC Events] sample:', { id: data[0].id, title: data[0].title, start: data[0].start, resourceId: data[0].resourceId });
                    setProgress(82, 'Rendering...');
                    success(data);
                    updateStats(data);
                })
                .catch(err => { setProgress(100, 'Failed to load appointments'); fail(err); });
        },

        // ── Loading complete → finish progress bar ────────────────
        loading: function (isLoading) {
            if (!isLoading) setProgress(100, 'Ready');
        },

        // ── Custom event card rendering ───────────────────────────
        eventContent: function (arg) {
            const ext  = arg.event.extendedProps;
            const npBadge = ext.isNewPatient
                ? '<span style="background:#d1fae5;color:#065f46;font-size:9px;padding:1px 5px;border-radius:3px;font-weight:700;margin-left:4px;">NP</span>'
                : '';
            const proc = ext.procedure
                ? `<div style="font-size:10px;margin-top:2px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;opacity:.85;">${ext.procedure}</div>`
                : '';
            const note = ext.note
                ? `<div style="font-size:9px;margin-top:1px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;opacity:.7;">${ext.note.substring(0, 55)}</div>`
                : '';
            return {
                html: `<div style="padding:4px 6px;height:100%;overflow:hidden;box-sizing:border-box;">
                           <div style="font-size:11px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                               ${arg.event.title}${npBadge}
                           </div>
                           ${proc}${note}
                       </div>`
            };
        },

        // ── Resource column headers ───────────────────────────────
        resourceLabelContent: function (arg) {
            return {
                html: `<div style="text-align:center;padding:6px 2px;">
                           <div style="font-size:11px;font-weight:700;color:#0f172a;">${arg.resource.title}</div>
                       </div>`
            };
        },

        // ── On event click — open sidebar ─────────────────────────
        eventClick: function (info) {
            showSidebar(info.event);
            // Highlight selected event
            document.querySelectorAll('.fc-event').forEach(el => el.style.opacity = '0.5');
            info.el.style.opacity = '1';
        },

        // ── Keep date picker in sync + re-fetch resources for new date ─
        datesSet: function (info) {
            const d = info.view.currentStart;
            document.getElementById('calDate').value = d.toISOString().split('T')[0];
            updateDateLabel(d);
            // Resources are provider columns for the CURRENT date; they must be
            // re-fetched whenever the visible date changes (FC does not do this automatically).
            calendar.refetchResources();
        },
    });

    calendar.render();

    // initialise date label & clock
    updateDateLabel(new Date('{{ date("Y-m-d") }}T00:00:00'));
    startClock();

    // ── Navigation buttons ────────────────────────────────────────
    document.getElementById('prevBtn').addEventListener('click', () => {
        showCalSkeleton('Loading...');
        calendar.prev();
    });
    document.getElementById('nextBtn').addEventListener('click', () => {
        showCalSkeleton('Loading...');
        calendar.next();
    });

    // ── Refresh ───────────────────────────────────────────────────
    document.getElementById('refreshBtn').addEventListener('click', () => {
        showCalSkeleton('Refreshing...');
        calendar.refetchEvents();
        calendar.refetchResources();
    });

    // ── Date picker ───────────────────────────────────────────────
    document.getElementById('calDate').addEventListener('change', function () {
        calendar.gotoDate(this.value);
    });

    // ── View toggle ───────────────────────────────────────────────
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            calendar.changeView(this.dataset.view);
        });
    });

    // ── Clicking outside events restores opacity ──────────────────
    document.getElementById('calendar-wrap').addEventListener('click', function (e) {
        if (!e.target.closest('.fc-event')) {
            document.querySelectorAll('.fc-event').forEach(el => el.style.opacity = '1');
        }
    });
});

// ── Date label formatter ──────────────────────────────────────────
function updateDateLabel(date) {
    document.getElementById('calDateLabel').textContent = date.toLocaleDateString('en-US', {
        weekday: 'long', month: 'long', day: 'numeric', year: 'numeric'
    });
}

// ── Live clock ───────────────────────────────────────────────────
function startClock() {
    function tick() {
        const now  = new Date();
        const h    = now.getHours();
        const m    = now.getMinutes().toString().padStart(2, '0');
        const ampm = h >= 12 ? 'PM' : 'AM';
        document.getElementById('liveTime').textContent = `${(h % 12) || 12}:${m} ${ampm}`;
    }
    tick();
    setInterval(tick, 30000);
}

// ── Stats bar ─────────────────────────────────────────────────────
function updateStats(events) {
    const scheduled = events.filter(e =>
        e.status === 1 || e.status === 'Scheduled'
    ).length;
    const total = events.length;
    document.getElementById('stat-production').textContent   = total + ' appointments';
    document.getElementById('stat-scheduled').textContent    = scheduled + ' scheduled';
}

// ── Sidebar: show ─────────────────────────────────────────────────
function showSidebar(event) {
    const ext = event.extendedProps;

    const fmtTime = dt => dt ? dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : '';
    const start   = fmtTime(event.start);
    const end     = fmtTime(event.end);

    const status     = STATUS_MAP[ext.status] || (ext.status ?? 'Unknown');
    const statusCls  = status === 'Complete' ? 'bg-emerald-100 text-emerald-700'
                     : status === 'Broken'   ? 'bg-red-100 text-red-700'
                     : status === 'ASAP'     ? 'bg-amber-100 text-amber-700'
                     : 'bg-blue-100 text-blue-700';

    const npBadge = ext.isNewPatient
        ? '<span class="ml-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold px-1.5 py-0.5 rounded">New Patient</span>'
        : '';

    const noteBlock = ext.note ? `
        <div class="mt-3 pt-3 border-t border-slate-100">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Notes</p>
            <p class="text-xs text-slate-600 bg-slate-50 p-2 rounded border border-slate-100 leading-relaxed italic">${ext.note}</p>
        </div>` : '';

    document.getElementById('sidebar-body').innerHTML = `
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 mb-4">
            <div class="flex items-start justify-between mb-2">
                <h4 class="text-sm font-bold text-slate-900 leading-tight">${event.title}${npBadge}</h4>
                <span class="text-[10px] text-slate-400 font-semibold whitespace-nowrap ml-2">ID: ${ext.patNum || '—'}</span>
            </div>
            <div class="space-y-1 text-xs text-slate-500">
                <p><span class="font-semibold text-slate-700">Provider:</span> ${ext.doctor || '—'}</p>
                <p><span class="font-semibold text-slate-700">Time:</span> ${start} – ${end}</p>
                <p><span class="font-semibold text-slate-700">Operatory:</span> ${ext.operator || '—'}</p>
                <p><span class="font-semibold text-slate-700">Procedure:</span> ${ext.procedure || '—'}</p>
                <p class="flex items-center gap-1">
                    <span class="font-semibold text-slate-700">Status:</span>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold ${statusCls}">${status}</span>
                </p>
            </div>
            ${noteBlock}
        </div>

        <div class="space-y-2">
            <a href="${baseUrl}/patients/${ext.patNum}"
               class="block w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-2 rounded text-sm text-center transition shadow-sm">
               View Patient
            </a>
            <button onclick="closeSidebar()"
                class="w-full bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-medium py-2 rounded text-xs transition">
                Close
            </button>
        </div>
    `;

    const sidebar = document.getElementById('apt-sidebar');
    sidebar.classList.remove('hidden');
    sidebar.classList.add('flex');
}

// ── Sidebar: close ────────────────────────────────────────────────
function closeSidebar() {
    const sidebar = document.getElementById('apt-sidebar');
    sidebar.classList.add('hidden');
    sidebar.classList.remove('flex');
    document.querySelectorAll('.fc-event').forEach(el => el.style.opacity = '1');
}
</script>

</x-app-layout>
