<x-app-layout>
    <header
        class="bg-white border-b border-gray-200 sticky top-0 z-50 px-6 py-4 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-6">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Front Office</h1>
            <div class="flex items-center gap-3">
                <input type="month" id="frontOfficeMonth" value="{{ date('Y-m') }}"
                    class="appearance-none bg-gray-100 border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 text-gray-700">

                <div class="relative">
                    <select
                        class="appearance-none bg-gray-100 border border-gray-300 rounded-lg pl-3 pr-8 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 text-gray-700">
                        <option>8 Mile</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
                <button id="updateStatsBtn"
                    class="bg-white hover:bg-gray-50 text-emerald-600 border border-emerald-500 font-medium text-sm px-4 py-1.5 rounded-lg transition-colors">
                    Update
                </button>
            </div>
        </div>

        <button
            class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm transition-colors">
            <i class="fa-solid fa-book-open"></i> Quick Start Guide
        </button>
    </header>

    <nav class="bg-white border-b border-gray-200 px-6 flex gap-6 text-sm font-medium text-gray-500">
        <a href="{{ route('front-office.index') }}"
            class="fo-nav-link border-b-2 py-3.5 px-1 transition-colors {{ ($activeTab ?? 'schedule') === 'schedule' ? 'border-emerald-500 text-emerald-600' : 'border-transparent hover:text-gray-700' }}">Schedule</a>
        <a href="{{ route('front-office.tasks') }}"
            class="fo-nav-link border-b-2 py-3.5 px-1 transition-colors {{ ($activeTab ?? '') === 'tasks' ? 'border-emerald-500 text-emerald-600' : 'border-transparent hover:text-gray-700' }}">Tasks</a>
        <a href="{{ route('front-office.collections') }}"
            class="fo-nav-link border-b-2 py-3.5 px-1 transition-colors {{ ($activeTab ?? '') === 'collections' ? 'border-emerald-500 text-emerald-600' : 'border-transparent hover:text-gray-700' }}">Collections</a>
        <a href="{{ route('front-office.kpis') }}"
            class="fo-nav-link border-b-2 py-3.5 px-1 transition-colors {{ ($activeTab ?? '') === 'kpis' ? 'border-emerald-500 text-emerald-600' : 'border-transparent hover:text-gray-700' }}">KPIs</a>
        <a href="{{ route('front-office.performance') }}"
            class="fo-nav-link border-b-2 py-3.5 px-1 transition-colors {{ ($activeTab ?? '') === 'performance' ? 'border-emerald-500 text-emerald-600' : 'border-transparent hover:text-gray-700' }}">Performance</a>
    </nav>

    <div id="foContentContainer">
        @if(($activeTab ?? 'schedule') === 'tasks')
            @include('front-office.partials.tasks')
        @else
            @include('front-office.partials.schedule')
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // SPA Tab Switching Engine
            $('.fo-nav-link').on('click', function (e) {
                e.preventDefault();
                let $this = $(this);
                let url = $this.attr('href');

                if ($this.hasClass('border-emerald-500')) return; // already active

                // Update styling
                $('.fo-nav-link').removeClass('border-emerald-500 text-emerald-600').addClass('border-transparent hover:text-gray-700');
                $this.removeClass('border-transparent hover:text-gray-700').addClass('border-emerald-500 text-emerald-600');

                // Push history state to URL bar
                history.pushState(null, '', url);

                // Fetch new tab content & replace
                $('#foContentContainer').html('<div class="p-16 flex justify-center items-center"><div class="animate-pulse text-emerald-600 font-semibold text-lg flex items-center gap-2"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</div></div>');

                $.get(url, function (html) {
                    $('#foContentContainer').html(html);
                }).fail(function () {
                    $('#foContentContainer').html('<div class="p-8 text-center text-red-500">Failed to load content. Please try again.</div>');
                });
            });

            window.addEventListener('popstate', function () {
                // When back button is pressed, reload page to restore correct state easily 
                window.location.reload();
            });
        });
    </script>
</x-app-layout>