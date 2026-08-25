<x-app-layout>
    <div class="p-6 max-w-[1600px] mx-auto space-y-6">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-[#001f3f] text-emerald-400 flex items-center justify-center shadow-inner">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Admin & Access Privilege Management</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Manage user credentials, role assignments, and granular module access.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.create') }}"
                    class="inline-flex items-center gap-2 bg-[#001f3f] hover:bg-[#002b57] text-white px-4 py-2.5 rounded-lg text-xs font-semibold shadow-sm transition-all tracking-wide">
                    <i data-lucide="user-plus" class="w-4 h-4 text-emerald-400"></i>
                    <span>Add New User</span>
                </a>
            </div>
        </div>

        <!-- Feedback Alerts -->
        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-xs flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-lg text-xs flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-octagon" class="w-4 h-4 text-rose-600 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        @endif

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Users</p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($stats['total']) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Super Admins</p>
                    <h3 class="text-2xl font-bold text-purple-700 mt-1">{{ number_format($stats['super_admins']) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="shield-alert" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Active Accounts</p>
                    <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['active']) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="user-check" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Inactive Accounts</p>
                    <h3 class="text-2xl font-bold text-slate-500 mt-1">{{ number_format($stats['inactive']) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                    <i data-lucide="user-x" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <div class="relative w-full sm:w-64">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or email..."
                        class="app-input pl-9 text-xs w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    </div>
                </div>

                <select name="role" onchange="this.form.submit()" class="app-input text-xs w-full sm:w-40 cursor-pointer">
                    <option value="">All Roles</option>
                    @foreach ($roles as $key => $roleData)
                        <option value="{{ $key }}" {{ $roleFilter === $key ? 'selected' : '' }}>
                            {{ $roleData['name'] }}
                        </option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()" class="app-input text-xs w-full sm:w-36 cursor-pointer">
                    <option value="">All Statuses</option>
                    <option value="1" {{ $statusFilter === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ $statusFilter === '0' ? 'selected' : '' }}>Inactive</option>
                </select>

                <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-3 py-2 rounded-md text-xs transition-colors">
                    Filter
                </button>

                @if ($search || $roleFilter || $statusFilter !== null)
                    <a href="{{ route('admin.users.index') }}" class="text-xs text-rose-600 hover:underline flex items-center gap-1 font-medium">
                        <i data-lucide="rotate-ccw" class="w-3 h-3"></i> Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 uppercase tracking-wider text-[10px] font-bold">
                            <th class="py-3.5 px-4">User</th>
                            <th class="py-3.5 px-4">Role</th>
                            <th class="py-3.5 px-4">Module Privileges</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Last Login</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($users as $u)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 text-slate-700 font-bold flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($u->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                <span>{{ $u->name }}</span>
                                                @if ($u->id === auth()->id())
                                                    <span class="bg-sky-50 text-sky-700 border border-sky-200 text-[9px] font-bold px-1.5 py-0.5 rounded">You</span>
                                                @endif
                                            </div>
                                            <p class="text-slate-400 text-[11px]">{{ $u->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $u->getRoleBadgeClass() }}">
                                        @if ($u->isSuperAdmin())
                                            <i data-lucide="crown" class="w-3 h-3 text-purple-600"></i>
                                        @endif
                                        {{ $u->getRoleName() }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($u->isSuperAdmin())
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-200 font-semibold text-[10px]">
                                            <i data-lucide="sparkles" class="w-3 h-3 text-purple-600"></i> All Modules (Full Access)
                                        </span>
                                    @else
                                        @php
                                            $modCount = $u->modules->count();
                                        @endphp
                                        @if ($modCount > 0)
                                            <div class="flex flex-wrap gap-1 items-center max-w-md">
                                                <span class="bg-slate-100 text-slate-800 font-bold px-2 py-0.5 rounded text-[10px] border border-slate-200">
                                                    {{ $modCount }} {{ Str::plural('Module', $modCount) }}
                                                </span>
                                                @foreach ($u->modules->take(4) as $um)
                                                    @if (isset($allModules[$um->module_key]))
                                                        <span class="bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded text-[9px] font-medium border border-blue-100">
                                                            {{ $allModules[$um->module_key]['name'] }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                                @if ($modCount > 4)
                                                    <span class="text-[10px] text-slate-400">+{{ $modCount - 4 }} more</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-amber-600 font-medium italic text-[11px]">No module access assigned</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($u->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 text-[11px]">
                                    @if ($u->last_login_at)
                                        <span title="{{ $u->last_login_at->toDayDateTimeString() }}">{{ $u->last_login_at->diffForHumans() }}</span>
                                    @else
                                        <span class="text-slate-300">Never</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <a href="{{ route('admin.users.edit', $u) }}"
                                            class="p-1.5 rounded-md text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit User & Permissions">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>

                                        @if ($u->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.toggle-status', $u) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="p-1.5 rounded-md transition-colors {{ $u->is_active ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50' }}"
                                                    title="{{ $u->is_active ? 'Deactivate User' : 'Activate User' }}"
                                                    onclick="return confirm('Are you sure you want to {{ $u->is_active ? 'deactivate' : 'activate' }} this user?')">
                                                    @if ($u->is_active)
                                                        <i data-lucide="user-x" class="w-4 h-4"></i>
                                                    @else
                                                        <i data-lucide="user-check" class="w-4 h-4"></i>
                                                    @endif
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline"
                                                onsubmit="return confirm('Are you sure you want to permanently delete user {{ $u->name }}? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Delete User">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="users" class="w-8 h-8 text-slate-300"></i>
                                        <p class="text-xs">No users found matching the selected filter criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
