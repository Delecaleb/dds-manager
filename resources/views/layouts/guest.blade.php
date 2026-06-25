<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DDS Manager - Practice Portal Registration</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Exact input framework overrides from the DDS Manager EOD/Huddle layouts */
        .app-input {
            border: 1px solid #0ea5e9; /* Teal-blue border profile */
            padding: 0.625rem 0.75rem;
            font-size: 0.875rem;
            color: #334155;
            border-radius: 0.25rem;
            background-color: #ffffff;
            width: 100%;
            transition: all 0.15s ease;
        }
        .app-input:focus {
            outline: none;
            border-color: #0284c7;
            box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.15);
        }
    </style>
</head>
<body class="bg-white text-[#334155] font-sans antialiased text-xs min-h-screen">

    <!-- SPLIT SCREEN LAYOUT CONTAINER -->
    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-3">
        
        <!-- LEFT 2/3 COLUMN: IMAGE & DENTAL BRANDED MARKETING CANVAS (Hidden on Mobile, Visible on Desktop) -->
        <div class="hidden lg:flex lg:col-span-2 relative bg-[#001f3f] p-12 flex-col justify-between text-white overflow-hidden">
            <!-- Background Decorative Geometric Blend -->
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#0ea5e9_1px,transparent_1px)] [background-size:16px_16px]"></div>
            <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl"></div>
            
            <!-- Branding Header Token Alignment -->
            <div class="relative z-10 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded bg-emerald-400 flex items-center justify-center text-[#001f3f]">
                    <i data-lucide="link-2" class="w-5 h-5 stroke-[2.5]"></i>
                </div>
                <span class="text-lg font-bold tracking-tight text-white">DDS Manager</span>
            </div>

            <!-- Core Creative Teaser Vector (Envato Concept Adaptation) -->
            <div class="relative z-10 max-w-xl space-y-4 my-auto">
                <span class="bg-emerald-500/20 text-emerald-400 font-bold px-3 py-1 rounded-full uppercase tracking-wider text-[10px]">
                    Enterprise Onboarding Module
                </span>
                <h1 class="text-4xl font-extrabold tracking-tight text-white leading-tight">
                    Empower your clinical workspace with metrics that matter.
                </h1>
                <p class="text-slate-300 text-sm leading-relaxed">
                    Join hundreds of synchronized clinics managing daily operational net production targets, clinical team logs, treatment plans, and automatic end-of-day reporting engines through a single interface token.
                </p>
                
            </div>

            <!-- Footer Compliance Notation Component -->
            <div class="relative z-10 flex items-center justify-between text-[11px] text-slate-400 border-t border-white/5 pt-4">
                <span>&copy; 2026 Unified Dental Specialists Engine.</span>
                <span class="font-mono text-emerald-500/80 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block animate-pulse"></span> SECURE ENGINE RUNNING
                </span>
            </div>
        </div>

        <!-- RIGHT 1/3 COLUMN: REGISTRATION FORM COMPONENT (Full Screen on Mobile) -->
        {{$slot}}

    </div>

    <!-- Lucide Icon Factory Activation -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>