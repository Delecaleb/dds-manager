<x-app-layout>
    <div class="p-6 max-w-[1200px] mx-auto space-y-6">

        <!-- Breadcrumb & Header -->
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.index') }}"
                    class="p-2 rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Edit User: {{ $user->name }}</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Update credentials, role assignments, and module access permissions.</p>
                </div>
            </div>

            @if ($user->id !== auth()->id())
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                    onsubmit="return confirm('Are you sure you want to permanently delete user {{ $user->name }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-3 py-2 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        <span>Delete User</span>
                    </button>
                </form>
            @endif
        </div>

        @if ($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-xs space-y-1 shadow-sm">
                <div class="flex items-center gap-2 font-bold mb-1">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                    <span>Please resolve the following issues:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 pl-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- User Credentials & Profile Card -->
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-5">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="user" class="w-4 h-4 text-blue-600"></i>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Account Profile & Role</h2>
                    </div>
                    @if ($user->id === auth()->id())
                        <span class="bg-sky-50 text-sky-700 border border-sky-200 text-[10px] font-bold px-2 py-0.5 rounded">
                            Your Current Account
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Full Name -->
                    <div class="space-y-1.5">
                        <label for="name" class="block font-bold text-slate-700 text-xs">
                            Full Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                            class="app-input w-full text-xs" required>
                    </div>

                    <!-- Email Address -->
                    <div class="space-y-1.5">
                        <label for="email" class="block font-bold text-slate-700 text-xs">
                            Email Address <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                            class="app-input w-full text-xs" required>
                    </div>

                    <!-- Password (Optional Reset) -->
                    <div class="space-y-1.5">
                        <label for="password" class="block font-bold text-slate-700 text-xs">
                            Change Password <span class="text-slate-400 font-normal">(leave blank to keep current)</span>
                        </label>
                        <input type="password" id="password" name="password" placeholder="••••••••"
                            class="app-input w-full text-xs" minlength="8">
                    </div>

                    <!-- Role Selector -->
                    <div class="space-y-1.5">
                        <label for="role" class="block font-bold text-slate-700 text-xs">
                            System Role <span class="text-rose-500">*</span>
                        </label>
                        <select id="role" name="role" class="app-input w-full text-xs cursor-pointer" onchange="toggleSuperAdminNotice(this.value)"
                            {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                            @foreach ($roles as $key => $roleData)
                                <option value="{{ $key }}" {{ old('role', $user->role) === $key ? 'selected' : '' }}>
                                    {{ $roleData['name'] }} — {{ $roleData['description'] }}
                                </option>
                            @endforeach
                        </select>
                        @if ($user->id === auth()->id())
                            <input type="hidden" name="role" value="{{ $user->role }}">
                            <p class="text-[10px] text-slate-400">You cannot change your own Super Admin role.</p>
                        @endif
                    </div>
                </div>

                <!-- Account Status Checkbox -->
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $user->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                            {{ $user->id === auth()->id() ? 'disabled' : '' }}
                            class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-0 accent-emerald-600">
                        <span class="text-xs font-semibold text-slate-800">Account is Active</span>
                    </label>
                    @if ($user->id === auth()->id())
                        <input type="hidden" name="is_active" value="1">
                        <span class="text-[11px] text-slate-400">You cannot deactivate your own account.</span>
                    @else
                        <span class="text-[11px] text-slate-400">Inactive accounts are blocked from signing in.</span>
                    @endif
                </div>
            </div>

            <!-- Super Admin Notice Banner -->
            <div id="superAdminNotice" class="hidden bg-purple-50 border border-purple-200 rounded-xl p-4 text-purple-900 text-xs flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                    <i data-lucide="crown" class="w-4 h-4"></i>
                </div>
                <div>
                    <h4 class="font-bold">Super Admin Role Active</h4>
                    <p class="text-purple-700 text-[11px] mt-0.5">Super Administrators have automatic, unrestricted access to all current and future modules. Granular module selection is not required.</p>
                </div>
            </div>

            <!-- Granular Module Permissions Matrix -->
            <div id="modulePermissionsCard" class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-6">
                <div class="border-b border-slate-100 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="grid" class="w-4 h-4 text-blue-600"></i>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Module Access Privileges</h2>
                            <p class="text-xs text-slate-400">Toggle the specific modules this user can view and interact with.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="selectAllModules(true)"
                            class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-semibold transition-colors">
                            Select All
                        </button>
                        <button type="button" onclick="selectAllModules(false)"
                            class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-semibold transition-colors">
                            Deselect All
                        </button>
                    </div>
                </div>

                <!-- Module Categories Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ($categories as $catKey => $category)
                        <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/40 space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                                <h3 class="text-xs font-bold text-slate-900 tracking-wide uppercase">{{ $category['label'] }}</h3>
                                <button type="button" onclick="toggleCategory('{{ $catKey }}')"
                                    class="text-[10px] text-blue-600 hover:underline font-semibold">
                                    Toggle
                                </button>
                            </div>

                            <div class="space-y-2.5">
                                @foreach ($category['modules'] as $modKey => $mod)
                                    @php
                                        $isChecked = in_array($modKey, old('modules', $assignedModuleKeys));
                                    @endphp
                                    <label class="flex items-start gap-3 p-2 rounded-lg bg-white border border-slate-200/80 hover:border-blue-200 cursor-pointer transition-colors">
                                        <input type="checkbox" name="modules[]" value="{{ $modKey }}" data-category="{{ $catKey }}"
                                            {{ $isChecked ? 'checked' : '' }}
                                            class="module-checkbox mt-0.5 w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-0 accent-blue-600">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-1.5">
                                                <i data-lucide="{{ $mod['icon'] }}" class="w-3.5 h-3.5 text-blue-600 shrink-0"></i>
                                                <span class="text-xs font-bold text-slate-900">{{ $mod['name'] }}</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ $mod['description'] }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.users.index') }}"
                    class="px-4 py-2.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="px-5 py-2.5 rounded-lg bg-[#001f3f] hover:bg-[#002b57] text-white text-xs font-bold shadow-sm transition-all flex items-center gap-2 cursor-pointer">
                    <i data-lucide="save" class="w-4 h-4 text-emerald-400"></i>
                    <span>Save Changes</span>
                </button>
            </div>

        </form>

    </div>

    <script>
        function toggleSuperAdminNotice(role) {
            const notice = document.getElementById('superAdminNotice');
            const modCard = document.getElementById('modulePermissionsCard');
            if (role === 'super_admin') {
                notice.classList.remove('hidden');
                modCard.classList.add('opacity-50', 'pointer-events-none');
            } else {
                notice.classList.add('hidden');
                modCard.classList.remove('opacity-50', 'pointer-events-none');
            }
        }

        function selectAllModules(checked) {
            document.querySelectorAll('.module-checkbox').forEach(cb => {
                cb.checked = checked;
            });
        }

        function toggleCategory(catKey) {
            const checkboxes = document.querySelectorAll(`[data-category="${catKey}"]`);
            const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
            checkboxes.forEach(cb => {
                cb.checked = anyUnchecked;
            });
        }

        // Initialize state on page load
        document.addEventListener('DOMContentLoaded', () => {
            const roleSelect = document.getElementById('role');
            if (roleSelect) {
                toggleSuperAdminNotice(roleSelect.value);
            }
        });
    </script>
</x-app-layout>
