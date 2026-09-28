{{--
  Notification recipients list + "add recipient" row, shared by Settings › Notifications
  and Settings › Online Scheduling. Expects $tableId, $recipients, $types, $emptyText.
--}}
@if($recipients === [])
    <p class="rounded-lg bg-slate-50 border border-slate-100 px-4 py-3 text-[13px] text-slate-600">{{ $emptyText }}</p>
@else
    <x-data-table :id="$tableId" min-width="560px">
        <x-slot:head>
            <tr>
                <th class="px-4 py-3">Notification Type</th>
                <th class="px-4 py-3">Method</th>
                <th class="px-4 py-3">Destination</th>
                <th class="px-4 py-3"><span class="sr-only">Remove</span></th>
            </tr>
        </x-slot:head>
        @foreach($recipients as $recipient)
            <tr>
                <td class="px-4 py-3 text-[13px] text-slate-800">{{ $recipient['type'] }}</td>
                <td class="px-4 py-3"><x-front-desk.pill :label="$recipient['method']" /></td>
                <td class="px-4 py-3 text-[13px] text-slate-800">{{ $recipient['destination'] }}</td>
                <td class="px-4 py-3 text-right"><i data-lucide="trash-2" class="w-4 h-4 inline text-slate-400"></i></td>
            </tr>
        @endforeach
    </x-data-table>
@endif

<div class="mt-5">
    <div class="text-[13px] font-semibold text-slate-800 mb-2">Add notification recipient</div>
    <div class="flex flex-wrap gap-2">
        <input type="text" placeholder="Enter phone or email" aria-label="Phone or email"
            class="flex-1 min-w-[220px] rounded-lg border border-slate-200 px-3 py-2 text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select aria-label="Notification type" class="rounded-lg border border-slate-200 px-2.5 py-2 text-[12px]">
            @foreach($types as $type)
                <option>{{ $type }}</option>
            @endforeach
        </select>
        <button type="button" disabled title="Saving arrives with the agent configuration backend"
            class="px-3 py-2 rounded-lg border border-slate-200 text-[12px] font-semibold text-slate-400 cursor-not-allowed">+ Add</button>
    </div>
</div>
