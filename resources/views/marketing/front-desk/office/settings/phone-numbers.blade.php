<p class="text-[13px] text-slate-600 -mt-2">Configure and manage your phone numbers and call transfer logic.</p>

<x-marketing.panel title="AI Numbers" subtitle="Numbers that connect callers directly to your AI assistant.">
    @if($settings['agent'] === [])
        <x-marketing.empty icon="phone" title="No AI numbers yet" message="Numbers are provisioned with the call platform." waiting-on="Call platform integration" />
    @else
        <x-data-table id="fdAgentNumbers" min-width="420px">
            <x-slot:head>
                <tr><th class="px-4 py-3">Phone number</th><th class="px-4 py-3">Designation</th></tr>
            </x-slot:head>
            @foreach($settings['agent'] as $number)
                <tr>
                    <td class="px-4 py-3 text-[13px] tabular-nums text-slate-800">{{ $number['number'] }}</td>
                    <td class="px-4 py-3">
                        <x-front-desk.pill :label="$number['designation'] === 'live' ? 'Real patient calls' : 'Testing calls'" :tone="$number['designation'] === 'live' ? 'emerald' : 'sky'" />
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    @endif
</x-marketing.panel>

<x-marketing.panel title="Transfer Numbers" subtitle="Numbers your AI assistant can transfer calls and notifications to.">
    @if($settings['transfer'] === [])
        <x-marketing.empty icon="phone-forwarded" title="No transfer numbers yet" message="Where the AI sends a caller who needs a person." />
    @else
        <x-data-table id="fdTransferNumbers" min-width="520px">
            <x-slot:head>
                <tr><th class="px-4 py-3">Phone number</th><th class="px-4 py-3">Forward SMS</th><th class="px-4 py-3">Forward reasons</th></tr>
            </x-slot:head>
            @foreach($settings['transfer'] as $number)
                <tr>
                    <td class="px-4 py-3 text-[13px] tabular-nums text-slate-800">{{ $number['number'] }}</td>
                    <td class="px-4 py-3"><x-front-desk.pill :label="$number['sms'] ? 'Yes' : 'No'" :tone="$number['sms'] ? 'emerald' : 'slate'" /></td>
                    <td class="px-4 py-3">
                        @foreach($number['reasons'] as $reason)
                            <x-front-desk.pill :label="$reason" tone="sky" />
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    @endif
</x-marketing.panel>
