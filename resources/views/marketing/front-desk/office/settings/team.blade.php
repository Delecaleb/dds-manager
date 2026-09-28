{{-- Manage Team isn't in the reference screenshots: a members list until its content is confirmed. --}}
<x-marketing.panel title="Team" subtitle="People who can manage the AI front desk for your organization.">
    @if($settings['members'] === [])
        <x-marketing.empty icon="users" title="No team members yet" waiting-on="Reference screen for Manage Team" />
    @else
        <x-data-table id="fdTeam" min-width="480px">
            <x-slot:head>
                <tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Role</th></tr>
            </x-slot:head>
            @foreach($settings['members'] as $member)
                <tr>
                    <td class="px-4 py-3 text-[13px] text-slate-800">{{ $member['name'] }}</td>
                    <td class="px-4 py-3 text-[13px] text-slate-600">{{ $member['email'] }}</td>
                    <td class="px-4 py-3"><x-front-desk.pill :label="$member['role']" /></td>
                </tr>
            @endforeach
        </x-data-table>
    @endif
</x-marketing.panel>
