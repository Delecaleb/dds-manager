<x-app-layout>
    <div class="p-6 max-w-[1600px] mx-auto space-y-6">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-[#001f3f] text-emerald-400 flex items-center justify-center shadow-inner">
                    <i data-lucide="code-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Developer Portal</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Your read-only API keys. Each key reads one location's bookings.</p>
                </div>
            </div>
            <button type="button" data-open-create-key
                class="inline-flex justify-center items-center gap-2 bg-[#001f3f] hover:bg-[#002b57] text-white px-4 py-2.5 rounded-lg text-xs font-semibold shadow-sm tracking-wide">
                <i data-lucide="plus" class="w-4 h-4 text-emerald-400"></i>
                <span>Create API key</span>
            </button>
        </div>

        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-xs flex items-center gap-2 shadow-sm">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Keys first, usage beside them from md up; stacked (keys on top) on small screens.
             min-w-0 lets the wide keys table scroll inside its card instead of stretching the grid. --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6 items-start">

        <!-- My keys -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm min-w-0 xl:col-span-3">
            <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900">My API keys</h2>
                <span class="text-[11px] text-slate-500">{{ $keys->count() }} {{ \Illuminate\Support\Str::plural('key', $keys->count()) }}</span>
            </div>
            @if ($keys->isEmpty())
                <div class="px-5 py-6 text-xs text-slate-500 flex flex-col sm:flex-row sm:items-center gap-3">
                    <span>You have no keys yet. Create one to start calling the API.</span>
                    <button type="button" data-open-create-key class="self-start px-3 py-1.5 text-[11px] font-bold rounded-lg border border-[#001f3f] text-[#001f3f] bg-white hover:bg-slate-50">Create API key</button>
                </div>
            @else
                <div class="p-3">
                    @include('admin.developers._keys', ['keys' => $keys, 'id' => 'myApiKeys', 'mode' => 'developer'])
                </div>
            @endif
        </div>

        <div class="min-w-0 xl:col-span-2">
            @include('developer._usage', ['scope' => 'All your keys'])
        </div>

        </div>

        {{-- Create key form, opened in a DDS.modal by the "Create API key" buttons (and reopened with its errors after a failed submit). --}}
        <template id="createKeyTemplate">
            <div class="dds-modal" role="dialog" aria-modal="true" aria-labelledby="createKeyTitle">
                <div class="dds-modal-panel w-full max-w-lg">
                    <div class="flex justify-between items-center p-5 border-b border-gray-100">
                        <h4 id="createKeyTitle" class="text-sm font-bold text-gray-900">Create an API key</h4>
                        <button type="button" data-dds-close class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                    </div>
            <form method="POST" action="{{ route('developer.keys.store') }}" class="p-5 space-y-4">
                @csrf
                @if ($errors->any())
                    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-3 py-2 rounded-lg text-xs">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <label class="block">
                    <span class="text-[11px] font-semibold text-slate-600 uppercase tracking-wider">Name</span>
                    <input type="text" name="name" required maxlength="120" value="{{ old('name') }}" placeholder="Booking website — Ann Arbor"
                        class="mt-1 w-full text-sm rounded-lg border border-slate-200 px-3 py-2">
                </label>

                <label class="block">
                    <span class="text-[11px] font-semibold text-slate-600 uppercase tracking-wider">Location</span>
                    <select name="location" required class="mt-1 w-full text-sm rounded-lg border border-slate-200 px-3 py-2 bg-white">
                        <option value="">Choose a location…</option>
                        @foreach ($locations as $locationKey => $location)
                            <option value="{{ $locationKey }}" @selected(old('location') === (string) $locationKey)>{{ $location->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-[11px] font-semibold text-slate-600 uppercase tracking-wider">Expires (optional)</span>
                    <input type="date" name="expires_at" value="{{ old('expires_at') }}" min="{{ now()->addDay()->toDateString() }}"
                        class="mt-1 w-full text-sm rounded-lg border border-slate-200 px-3 py-2">
                </label>

                <fieldset class="space-y-2">
                    <span class="text-[11px] font-semibold text-slate-600 uppercase tracking-wider">Permissions</span>
                    <label class="flex items-start gap-2 text-xs text-slate-500">
                        <input type="checkbox" checked disabled class="mt-0.5 accent-emerald-600">
                        <span>{{ $abilities[\App\Models\ApiKey::ABILITY_BOOKINGS] }}</span>
                    </label>
                    <label class="flex items-start gap-2 text-xs text-slate-700">
                        <input type="hidden" name="patient_contact" value="0">
                        <input type="checkbox" name="patient_contact" value="1" @checked(old('patient_contact', '1') === '1') class="mt-0.5 accent-emerald-600">
                        <span>{{ $abilities[\App\Models\ApiKey::ABILITY_PATIENT_CONTACT] }}
                            <span class="block text-[11px] text-amber-700">Protected health information. Leave off for keys that only need open/busy time.</span></span>
                    </label>
                </fieldset>

                <button type="submit" class="w-full inline-flex justify-center items-center gap-2 bg-[#001f3f] hover:bg-[#002b57] text-white px-4 py-2.5 rounded-lg text-xs font-semibold">
                    <i data-lucide="plus" class="w-4 h-4 text-emerald-400"></i> Create key
                </button>
            </form>
                </div>
            </div>
        </template>

        <!-- Quick reference -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4 text-xs text-slate-700">
            <h2 class="text-sm font-bold text-slate-900">Quick reference</h2>
            <p>Send the key as a Bearer token from your server. Data is the synced copy of OpenDental (not live); times are the office's local time, without an offset.</p>
<pre class="bg-slate-900 text-slate-100 rounded-lg p-4 overflow-x-auto"><code>curl -H "Authorization: Bearer dds_…" "{{ $apiBase }}/bookings?start={{ now()->toDateString() }}&end={{ now()->addDays(13)->toDateString() }}&status=scheduled"</code></pre>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <p class="font-bold text-slate-900 mb-1">Endpoints</p>
                    <ul class="space-y-1 font-mono">
                        <li>GET /api/v1/me</li>
                        <li>GET /api/v1/bookings</li>
                        <li>GET /api/v1/bookings/{id}</li>
                    </ul>
                </div>
                <div>
                    <p class="font-bold text-slate-900 mb-1">/bookings parameters</p>
                    <ul class="space-y-1">
                        <li><code>start</code>, <code>end</code> — required, YYYY-MM-DD, at most {{ $maxRangeDays }} days</li>
                        <li><code>status</code> — comma list: {{ implode(', ', array_map(fn ($s) => $s->slug(), \App\Enums\AppointmentStatus::cases())) }}</li>
                        <li><code>provider</code> — comma list of provider ids</li>
                        <li><code>updated_since</code> — YYYY-MM-DDTHH:MM:SS (office time); only bookings changed after it</li>
                        <li><code>per_page</code> — up to {{ $maxPerPage }}; follow <code>links.next</code> for the next page</li>
                    </ul>
                </div>
            </div>
            <p class="text-slate-500">Bookings deleted in OpenDental disappear from results; a caller reading only <code>updated_since</code> changes is not told about them.</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-dev-keys]').forEach(function (table) {
                DDS.dataTable(table, { paging: false, info: false, searching: false });
            });
        });

        // "Create API key" buttons open the form in a modal; after a failed submit it reopens with its errors.
        (function () {
            var template = document.getElementById('createKeyTemplate');

            function openCreateKey() {
                var modal = DDS.modal.openHtml(template.innerHTML);
                var first = modal && modal.querySelector('input[name="name"]');
                if (first) first.focus();
            }

            document.addEventListener('click', function (e) {
                if (e.target.closest('[data-open-create-key]')) openCreateKey();
            });

            @if ($errors->any())
                document.addEventListener('DOMContentLoaded', openCreateKey);
            @endif
        })();

        // Copy buttons: data-copy-target names the input holding the text.
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-copy-target]');
            if (!btn) return;
            var input = document.getElementById(btn.getAttribute('data-copy-target'));
            if (!input) return;
            input.select();
            (navigator.clipboard ? navigator.clipboard.writeText(input.value) : Promise.reject())
                .catch(function () { document.execCommand('copy'); })
                .then(function () { btn.textContent = 'Copied'; });
        });

        // Copy key / Regenerate: both ask for the password, then show the key in the same modal.
        (function () {
            var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            function esc(s) {
                return String(s).replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }

            function showKey(modal, key, note) {
                modal.querySelector('[data-key-modal-body]').innerHTML =
                    '<div class="space-y-3">' +
                    '<div class="flex gap-2"><input id="revealedApiKey" type="text" readonly value="' + esc(key) + '" ' +
                    'class="flex-1 font-mono text-xs rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800" onclick="this.select()">' +
                    '<button type="button" data-copy-target="revealedApiKey" class="px-4 py-2 rounded-lg text-xs font-semibold bg-[#001f3f] text-white hover:bg-[#002b57]">Copy</button></div>' +
                    '<p class="text-[11px] text-slate-500">' + esc(note) + '</p></div>';
            }

            document.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-key-action]');
                if (!btn) return;

                var regenerate = btn.getAttribute('data-key-action') === 'regenerate';
                var name = btn.getAttribute('data-key-name');
                var html =
                    '<div class="dds-modal" role="dialog" aria-modal="true"><div class="dds-modal-panel w-full max-w-md">' +
                    '<div class="flex justify-between items-center p-5 border-b border-gray-100">' +
                    '<h4 class="text-sm font-bold text-gray-900">' + (regenerate ? 'Regenerate key' : 'Copy key') + ' — ' + esc(name) + '</h4>' +
                    '<button type="button" data-dds-close class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button></div>' +
                    '<div class="p-5" data-key-modal-body>' +
                    '<form class="space-y-3" data-key-modal-form>' +
                    (regenerate ? '<p class="text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">The current key stops working immediately. Anything using it must be updated with the new one.</p>' : '') +
                    '<label class="block"><span class="text-[11px] font-semibold text-slate-600 uppercase tracking-wider">Confirm your password</span>' +
                    '<input type="password" name="password" required autocomplete="current-password" class="mt-1 w-full text-sm rounded-lg border border-slate-200 px-3 py-2"></label>' +
                    '<p class="text-xs text-rose-700 hidden" data-key-modal-error></p>' +
                    '<button type="submit" class="w-full px-4 py-2.5 rounded-lg text-xs font-semibold text-white ' + (regenerate ? 'bg-amber-600 hover:bg-amber-700' : 'bg-[#001f3f] hover:bg-[#002b57]') + '">' +
                    (regenerate ? 'Regenerate key' : 'Show key') + '</button>' +
                    '</form></div></div></div>';

                var modal = DDS.modal.openHtml(html);
                var form = modal.querySelector('[data-key-modal-form]');
                var error = modal.querySelector('[data-key-modal-error]');
                form.querySelector('input[name="password"]').focus();

                form.addEventListener('submit', function (ev) {
                    ev.preventDefault();
                    var submit = form.querySelector('button[type="submit"]');
                    submit.disabled = true;
                    error.classList.add('hidden');

                    fetch(btn.getAttribute('data-url'), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: JSON.stringify({ password: form.password.value })
                    }).then(function (r) {
                        return r.json().catch(function () { return {}; }).then(function (body) { return { status: r.status, body: body }; });
                    }).then(function (res) {
                        if (res.status === 200 && res.body.key) {
                            if (regenerate) {
                                var cell = document.querySelector('[data-key-masked="' + btn.getAttribute('data-key-id') + '"]');
                                if (cell && res.body.masked) cell.textContent = res.body.masked;
                            }
                            showKey(modal, res.body.key, regenerate
                                ? 'This is your new key. The old one no longer works.'
                                : 'Keep it on your server only. Never put it in browser code or a public repository.');
                            return;
                        }
                        var message = res.status === 429
                            ? 'Too many attempts. Wait a minute and try again.'
                            : ((res.body.errors && res.body.errors.password && res.body.errors.password[0]) || res.body.message || 'Something went wrong. Please try again.');
                        error.textContent = message;
                        error.classList.remove('hidden');
                        submit.disabled = false;
                        form.password.value = '';
                        form.password.focus();
                    }).catch(function () {
                        error.textContent = 'Could not reach the server. Please try again.';
                        error.classList.remove('hidden');
                        submit.disabled = false;
                    });
                });
            });
        })();
    </script>
</x-app-layout>
