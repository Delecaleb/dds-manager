<div class="bg-transparent space-y-5">
    <div class="mt-4">
        @include('operations.tabs.table', ['spec' => $spec, 'subtabs' => $subtabs ?? [], 'tab' => $tab, 'activeSubtab' => $activeSubtab ?? 'default'])
    </div>
</div>