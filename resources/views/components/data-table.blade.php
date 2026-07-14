{{--
  x-data-table — Consistent DataTables wrapper component
  ────────────────────────────────────────────────────────
  Props:
    id         (required) — table element id, used by DataTables
    min-width  (default: '900px')  — minimum table width before horizontal scroll kicks in
    max-height (default: null)     — enables vertical scroll; omit for no max-height

  Named slots:
    head  — <tr> row(s) for the table header  (rendered inside <thead>)
    foot  — <tr> row(s) for the table footer  (rendered inside <tfoot>)

  CSS helper class (use on first-column td/th via DataTables `className`):
    dt-col-sticky        — makes any cell sticky-left with consistent shadow/border

  Inline usage example:
    <x-data-table id="myTable" min-width="1100px" max-height="600px">
        <x-slot:head>
            <tr>
                <th class="dt-col-sticky px-4 py-3 min-w-[180px]">Name</th>
                <th class="px-4 py-3">Value</th>
            </tr>
        </x-slot:head>
        <x-slot:foot>
            <tr>
                <td class="dt-col-sticky bg-gray-50 px-4 py-3 text-right">Total:</td>
                <td id="foot-value" class="px-4 py-3">—</td>
            </tr>
        </x-slot:foot>
    </x-data-table>
--}}

@props([
    'id',
    'minWidth' => '900px',
    'maxHeight' => null,
])

@php
    $containerStyle = $maxHeight ? "max-height: {$maxHeight}" : '';
@endphp

<div
    class="w-full overflow-x-auto border border-gray-200 rounded-lg {{ $maxHeight ? 'overflow-y-auto' : '' }}"
    @if($containerStyle) style="{{ $containerStyle }}" @endif
>
    <table
        id="{{ $id }}"
        class="w-full text-left border-collapse"
        style="min-width: {{ $minWidth }}"
    >
        @if($head->isNotEmpty())
        <thead class="sticky top-0 z-30 bg-gray-50 text-gray-700 text-xs font-bold uppercase tracking-wide border-b border-gray-200 shadow-sm">
            {{ $head }}
        </thead>
        @endif

        <tbody class="divide-y divide-gray-100 text-xs font-medium text-gray-600 bg-white">
            {{ $slot }}
        </tbody>

        @if(isset($foot) && $foot->isNotEmpty())
        <tfoot class="sticky bottom-0 z-20 bg-gray-50 shadow-[0_-1px_2px_rgba(0,0,0,0.1)] border-t border-gray-200">
            {{ $foot }}
        </tfoot>
        @endif
    </table>
</div>

<style>
/* ── Sticky-first-column helper ─────────────────────────────────────────────
   Apply to first-column cells via DataTables `className` option:
     columns: [{ data: 'name', className: 'dt-col-sticky px-4 py-3 ...' }, ...]
   Or directly on <th>/<td> in static tables.
────────────────────────────────────────────────────────────────────────────── */
.dt-col-sticky {
    position: sticky !important;
    left: 0;
    background: inherit;
    box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.10);
    border-right: 1px solid #e5e7eb !important;
    z-index: 15;
}
thead .dt-col-sticky { z-index: 35; background: #f9fafb; }
tfoot .dt-col-sticky { z-index: 25; background: #f9fafb; }
tr:hover .dt-col-sticky { background: #f8fafc; }
</style>
