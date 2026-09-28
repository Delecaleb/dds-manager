{{-- Settings save button: shown in place, disabled until agent configuration is persisted. --}}
<div class="flex justify-end">
    <button type="button" disabled title="Saving arrives with the agent configuration backend"
        class="px-4 py-2 rounded-lg bg-blue-300 text-white text-[13px] font-semibold cursor-not-allowed">{{ $label ?? 'Save changes' }}</button>
</div>
