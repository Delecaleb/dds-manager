<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="col-span-1 bg-[#f8fafc] lg:bg-white flex flex-col justify-between p-8 sm:p-12 lg:p-16">
            
            <!-- Mobile Header Only Display Vector -->
            <div class="flex lg:hidden items-center gap-2 mb-8">
                <div class="w-7 h-7 rounded bg-[#001f3f] flex items-center justify-center text-emerald-400">
                    <i data-lucide="link-2" class="w-4 h-4"></i>
                </div>
                <span class="text-sm font-bold text-slate-800 tracking-tight">Unified Dental Platform</span>
            </div>

            <div class="my-auto space-y-6 w-full max-w-md mx-auto">
                <!-- Portal Titles -->
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Sign In</h2>
                    <p class="text-xs text-slate-400 mt-1.5">
                        Authorized clinical personnel access node. Need support? <a href="#" class="text-sky-600 font-semibold hover:underline">Contact Administrator</a>
                    </p>
                </div>

                <!-- ATTENTION NOTICE ALERT BOX STRIP -->
                <div class="bg-sky-50 border border-sky-100 rounded text-slate-700 p-3.5 font-medium text-[11px] leading-relaxed">
                    Please provide verified organization practice credentials to complete database verification queries or update transaction states.
                </div>

                <!-- MAIN EXECUTION FORM ACTION -->
                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    <!-- USER IDENTITY INPUT ROW -->
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px]">
                            Identity Username <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input id="username" name="username" type="text" value="{{ old('username') }}" placeholder="user" class="app-input pl-9" required autofocus>
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="user" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>
                        @error('username')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- PASSKEY ACCESS MATRIX ROW -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px]">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <a href="#" class="text-[10px] font-bold text-sky-600 hover:underline">Forgot?</a>
                        </div>
                        <div class="relative">
                            <input id="password" name="password" type="password" placeholder="••••••••" class="app-input pl-9" required>
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="key-round" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>
                        @error('password')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- PERSISTENCE SYSTEM CONFIG CHECKBOX -->
                    <div class="flex items-center pt-1">
                        <label class="flex items-center gap-2.5 cursor-pointer font-medium text-slate-600">
                            <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-0 accent-[#001f3f]">
                            <span>Keep me synchronized on this practice profile</span>
                        </label>
                    </div>

                    <!-- SUBMIT AUTHENTICATION PROCESS TRIPPERS -->
                    <div class="pt-2">
                        <button type="submit" class="w-full bg-[#001f3f] hover:opacity-90 text-white font-bold text-xs py-3 px-4 rounded shadow-sm transition-all tracking-wider uppercase flex items-center justify-center gap-2">
                            <span>Authenticate Access</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-emerald-400"></i>
                        </button>
                    </div>

                </form>
            </div>

            <!-- Mobile Footer Element Segment -->
            <div class="lg:hidden text-center text-slate-400 text-[10px] pt-8 border-t border-slate-100 mt-auto">
                &copy; 2026 Unified Dental Specialists Engine. v4.12
            </div>

        </div>
</x-guest-layout>
