<x-marketing-layout>
    <x-slot:title>Online Bookings</x-slot:title>
    <x-slot:subtitle>View and manage appointments booked through your online scheduling system</x-slot:subtitle>

    @php
        $short = fn (?string $t) => $t ? \Carbon\CarbonImmutable::parse($t)->format('M j \a\t g:i A') : '—';
    @endphp

    <div class="p-6 max-w-[1600px]">
        <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)] gap-4 items-start">
            <x-marketing.panel title="Bookings" icon="calendar-check">
                <x-data-table id="fdBookings" min-width="640px" max-height="640px">
                    <x-slot:head>
                        <tr>
                            <th class="px-4 py-3">Patient</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Appointment</th>
                            <th class="px-4 py-3">Created</th>
                        </tr>
                    </x-slot:head>
                    @foreach($bookings as $booking)
                        @php $on = $selected && $selected['id'] === $booking['id']; @endphp
                        <tr class="cursor-pointer {{ $on ? 'bg-blue-50/60 shadow-[inset_3px_0_0_#2563eb]' : 'hover:bg-slate-50' }}"
                            onclick="window.location = @js(request()->fullUrlWithQuery(['booking' => $booking['id']]))">
                            <td class="px-4 py-3 text-[13px] font-medium text-slate-900">{{ $booking['name'] }}</td>
                            <td class="px-4 py-3"><x-front-desk.pill :outcome="$booking['status']" /></td>
                            <td class="px-4 py-3 text-slate-700" data-order="{{ $booking['appointment'] }}">{{ $short($booking['appointment']) }}</td>
                            <td class="px-4 py-3 text-slate-500" data-order="{{ $booking['created'] }}">{{ \Carbon\CarbonImmutable::parse($booking['created'])->format('M j') }}</td>
                        </tr>
                    @endforeach
                </x-data-table>
            </x-marketing.panel>

            <section class="bg-white border border-slate-200 rounded-xl shadow-sm xl:sticky xl:top-4">
                <header class="flex items-center justify-between gap-3 px-5 py-3.5 border-b border-slate-100">
                    <h2 class="text-[13px] font-bold text-slate-900">Booking Details</h2>
                    @if($selected)
                        <x-front-desk.pill :outcome="$selected['status']" />
                    @endif
                </header>

                @if(! $selected)
                    <x-marketing.empty icon="calendar-check" title="No online bookings yet"
                        message="Appointments patients book through your online scheduling link appear here."
                        waiting-on="Online scheduling (Settings › Online Scheduling)" />
                @else
                    <div class="p-5 space-y-5">
                        <div>
                            <h3 class="text-[12px] font-semibold text-slate-700 mb-2">Summary</h3>
                            <p class="rounded-lg bg-slate-50 px-4 py-3 text-[12px] text-slate-700 leading-relaxed">{{ $selected['summary'] }}</p>
                        </div>
                        <div>
                            <h3 class="text-[12px] font-semibold text-slate-700 mb-1">Patient Information</h3>
                            <dl>
                                <x-front-desk.field label="Name" :value="$selected['name']" />
                                <x-front-desk.field label="Phone" :value="$selected['phone']" />
                                <x-front-desk.field label="Email" :value="$selected['email']" />
                                <x-front-desk.field label="Date of Birth" :value="$selected['dob']" />
                            </dl>
                        </div>
                        <div>
                            <h3 class="text-[12px] font-semibold text-slate-700 mb-1">Appointment Details</h3>
                            <dl>
                                <x-front-desk.field label="Service" :value="$selected['service']" />
                                <x-front-desk.field label="Provider" :value="$selected['provider']" />
                                <x-front-desk.field label="Appointment" :value="\Carbon\CarbonImmutable::parse($selected['appointment'])->format('M j, Y \a\t g:i A')" />
                                <x-front-desk.field label="Created" :value="\Carbon\CarbonImmutable::parse($selected['created'])->format('M j, Y \a\t g:i A')" />
                                <x-front-desk.field label="Booking ID" :value="$selected['id']" />
                            </dl>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            DDS.dataTable(document.getElementById('fdBookings'), { pageLength: 50 });
        });
    </script>
</x-marketing-layout>
