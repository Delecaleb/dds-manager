<p class="text-[13px] text-slate-600 -mt-2">Configure actions that your AI receptionist can perform.</p>

@foreach([
    'new' => ['New patient bookable appointments', 'The types of appointments that can be booked for new patients.'],
    'existing' => ['Existing patient bookable appointments', 'The types of appointments that can be booked for existing patients.'],
    'actions' => ['Existing patient actions', 'Other actions that can be performed for existing patients.'],
] as $key => [$title, $subtitle])
    <x-marketing.panel :title="$title" :subtitle="$subtitle">
        @forelse($settings[$key] as $action)
            <details class="group rounded-lg border border-slate-200 mb-3 last:mb-0" @if($loop->first) open @endif>
                <summary class="flex items-center gap-2 px-4 py-3 cursor-pointer list-none text-[13px] font-medium text-slate-800">
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-90"></i>{{ $action['name'] }}
                </summary>
                <div class="px-4 pb-4 space-y-4 text-[12px]">
                    @isset($action['duration'])
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            @foreach(['Appointment duration' => $action['duration'], 'Services' => $action['services'], 'Procedure Codes' => $action['codes']] as $label => $value)
                                <div>
                                    <div class="font-medium text-slate-700 mb-1">{{ $label }}</div>
                                    <div class="rounded-lg bg-slate-50 border border-slate-100 px-3 py-2 text-slate-700">{{ $value }}</div>
                                </div>
                            @endforeach
                        </div>

                        <div>
                            <div class="font-semibold text-slate-800">Blocked intervals</div>
                            <p class="text-slate-500">Time intervals that are not available for booking.</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($action['blocked'] as $interval)
                                    <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-800">{{ $interval }}</span>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <div class="font-semibold text-slate-800">Providers by column</div>
                            <p class="text-slate-500 mb-2">Which providers are available for scheduling in each column.</p>
                            <x-data-table id="fdProviders{{ $key }}{{ $loop->index }}" min-width="360px">
                                <x-slot:head>
                                    <tr><th class="px-4 py-2.5">Column</th><th class="px-4 py-2.5">Providers</th></tr>
                                </x-slot:head>
                                @foreach($action['providers'] as [$column, $provider])
                                    <tr>
                                        <td class="px-4 py-2.5">{{ $column }}</td>
                                        <td class="px-4 py-2.5"><span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">{{ $provider }}</span></td>
                                    </tr>
                                @endforeach
                            </x-data-table>
                        </div>
                    @endisset

                    <div>
                        <div class="font-semibold text-slate-800">Call flow</div>
                        <p class="text-slate-500 mb-2">These are the primary steps and questions that will be followed while performing this action.</p>
                        <ol class="rounded-lg border border-slate-200 p-4 space-y-2.5">
                            @foreach($action['flow'] as $i => [$step, $detail])
                                <li class="flex gap-3">
                                    <span class="text-slate-400 tabular-nums w-5 shrink-0">{{ $i + 1 }}.</span>
                                    <span><span class="block font-medium text-slate-800">{{ $step }}</span><span class="block text-slate-500">{{ $detail }}</span></span>
                                </li>
                            @endforeach
                        </ol>
                    </div>

                    @foreach(['template' => 'Post call patient text message template', 'template_es' => 'Post call patient text message template (Spanish)'] as $field => $label)
                        @if(! empty($action[$field]))
                            <div>
                                <div class="font-semibold text-slate-800 mb-1">{{ $label }}</div>
                                <div class="rounded-lg bg-slate-50 border border-slate-100 px-3 py-2 text-slate-700 leading-relaxed">{{ $action[$field] }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </details>
        @empty
            <x-marketing.empty icon="list-checks" title="Nothing configured"
                message="Each action defines what the AI can book or change on a call, and the questions it asks along the way."
                waiting-on="Agent configuration backend" />
        @endforelse
    </x-marketing.panel>
@endforeach
