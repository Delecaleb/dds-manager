<x-guest-layout>
    <div class="col-span-1 bg-[#f8fafc] lg:bg-white flex flex-col justify-between p-8 sm:p-12 lg:p-16 overflow-y-auto">
            
            <!-- Mobile Header Only Display Vector -->
            <div class="flex lg:hidden items-center gap-2 mb-6">
                <div class="w-7 h-7 rounded bg-[#001f3f] flex items-center justify-center text-emerald-400">
                    <i data-lucide="link-2" class="w-4 h-4"></i>
                </div>
                <span class="text-sm font-bold text-slate-800 tracking-tight">Unified Dental Platform</span>
            </div>

            <div class="my-auto space-y-5 w-full max-w-md mx-auto">
                <!-- Portal Titles -->
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Create Account</h2>
                    <p class="text-xs text-slate-400 mt-1.5">
                        Already have a practice node registered? <a href="#" class="text-sky-600 font-semibold hover:underline">Sign In Instead</a>
                    </p>
                </div>

                <!-- MAIN EXECUTION SIGNUP FORM -->
                <form method="POST" class="space-y-3.5">
                    @csrf
                    <!-- FULL NAME INPUT ROW -->
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px]">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" placeholder="Dr. Jane Doe" class="jarvis-input pl-9" required>
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="user" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>
                    </div>

                    <!-- CORPORATE EMAIL INPUT ROW -->
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px]">
                            Clinic Email Address <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="email" placeholder="j.doe@clinic.com" class="jarvis-input pl-9" required>
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>
                    </div>

                    <!-- PRACTICE IDENTITY DRIVER (SELECT) -->
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px]">
                            Practice Location / Node Assigned <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select class="jarvis-input appearance-none pl-9 pr-8 text-left bg-white font-medium text-slate-700 cursor-pointer" required>
                                <option value="" disabled selected>Select assigned target node...</option>
                                <option value="8mile">8 Mile Location</option>
                                <option value="detroit_main">Detroit Dental Head</option>
                                <option value="grand_river">Grand River Branch</option>
                            </select>
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                            </div>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>
                    </div>

                    <!-- SECURE CHOSEN PASSKEY ROW -->
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px]">
                            Create Secure Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" placeholder="••••••••" class="jarvis-input pl-9" required>
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="key-round" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>
                    </div>

                    <!-- REGULATORY AND PRIVACY SYSTEM CHECKBOX -->
                    <div class="flex items-start pt-1 gap-2.5 cursor-pointer">
                        <input type="checkbox" id="terms" class="mt-0.5 w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-0 accent-[#001f3f]" required>
                        <label for="terms" class="font-medium text-slate-500 text-[11px] leading-tight select-none">
                            I verify that I am an authorized clinical agent and agree to the HIPAA Compliance Policy data processing agreements.
                        </label>
                    </div>

                    <!-- SUBMIT ACCOUNT CREATION TRIGGER -->
                    <div class="pt-2">
                        <button type="submit" class="w-full bg-[#001f3f] hover:opacity-90 text-white font-bold text-xs py-3 px-4 rounded shadow-sm transition-all tracking-wider uppercase flex items-center justify-center gap-2">
                            <span>Register Practice Profile</span>
                            <i data-lucide="user-plus" class="w-3.5 h-3.5 text-emerald-400"></i>
                        </button>
                    </div>

                </form>
            </div>

            <!-- Mobile/Universal Base Footer Element Segment -->
            <div class="text-center text-slate-400 text-[10px] pt-6 border-t border-slate-100 mt-6 lg:mt-0">
                &copy; 2026 Unified Dental Specialists Engine. Core Version v4.12
            </div>

        </div>
</x-guest-layout>
