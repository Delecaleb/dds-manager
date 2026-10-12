<x-app-layout>
    <div class="p-6 max-w-[1600px] mx-auto space-y-6">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-[#001f3f] text-emerald-400 flex items-center justify-center shadow-inner">
                    <i data-lucide="key-round" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Developer API</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Every read-only API key, by location, with its owner. Revoking a key stops it immediately.</p>
                </div>
            </div>
            <a href="{{ route('admin.users.create') }}"
                class="inline-flex items-center gap-2 bg-[#001f3f] hover:bg-[#002b57] text-white px-4 py-2.5 rounded-lg text-xs font-semibold shadow-sm tracking-wide">
                <i data-lucide="user-plus" class="w-4 h-4 text-emerald-400"></i>
                <span>Add a developer login</span>
            </a>
        </div>

        <div class="bg-indigo-50 border border-indigo-200 text-indigo-900 px-4 py-3 rounded-lg text-xs">
            Developers create and manage their own keys in the <strong>Developer Portal</strong>. To give someone access, create their login with
            the role <strong>Developer</strong> and tick the <strong>Developer Portal</strong> module.
        </div>

        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-xs flex items-center gap-2 shadow-sm">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @include('developer._usage', ['scope' => 'Every key, all developers'])

        @foreach ($byLocation as $group)
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900">{{ $group['location']->name }}</h2>
                    <span class="text-[11px] text-slate-500">{{ count($group['keys']) }} {{ \Illuminate\Support\Str::plural('key', count($group['keys'])) }}</span>
                </div>
                @if ($group['keys'] === [])
                    <p class="px-5 py-4 text-xs text-slate-500">No keys for this location.</p>
                @else
                    <div class="p-3">
                        @include('admin.developers._keys', ['keys' => $group['keys'], 'id' => 'devKeys'.$loop->index, 'mode' => 'admin'])
                    </div>
                @endif
            </div>
        @endforeach

        @if ($orphaned !== [])
            <div class="bg-white rounded-xl border border-amber-200 shadow-sm">
                <div class="px-5 py-3 border-b border-amber-100">
                    <h2 class="text-sm font-bold text-amber-900">Location no longer active</h2>
                </div>
                <div class="p-3">
                    @include('admin.developers._keys', ['keys' => $orphaned, 'id' => 'devKeysOrphaned', 'mode' => 'admin'])
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-dev-keys]').forEach(function (table) {
                DDS.dataTable(table, { paging: false, info: false, searching: false });
            });
        });
    </script>
</x-app-layout>
