<x-app-layout>
    <div class="min-h-screen flex flex-col relative">
        
        <div class="bg-white border-b border-slate-200 px-8 pt-6 pb-0 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Patient Portal</h1>
                <button class="bg-[#001f3f] text-emerald-400 font-semibold text-xs px-4 py-2 rounded-full flex items-center gap-2 shadow-sm">
                    <i data-lucide="book-open" class="w-4 h-4"></i> Quick Start Guide
                </button>
            </div>

            <div class="flex items-center gap-3 mb-6">
                <div class="relative w-48">
                    <select class="w-full appearance-none bg-white border border-slate-300 rounded px-3 py-1.5 text-sm font-medium text-slate-700 focus:outline-none focus:border-emerald-500 cursor-pointer">
                        <option value="8mile">8 Mile</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-500">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>
                <button id="refreshPatients" class="border border-emerald-500 text-slate-800 text-sm font-semibold px-5 py-1.5 rounded bg-white hover:bg-slate-50 transition-colors">
                    Refresh
                </button>
            </div>

            <div class="flex gap-6 border-b border-slate-200 text-sm font-medium">
                <a href="#" class="border-b-2 border-emerald-500 text-slate-900 pb-3 font-bold">Patients</a>
                <a href="#" class="text-slate-400 hover:text-slate-600 pb-3">Reminders</a>
                <a href="#" class="text-slate-400 hover:text-slate-600 pb-3">Performance</a>
            </div>
        </div>

        <div class="px-8 py-4 bg-[#f1f5f9] text-xs font-semibold text-emerald-600 border-b border-slate-200">
            <span class="cursor-pointer hover:underline">Additional Filters (0)</span>
        </div>

        <div class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <button class="flex items-center gap-2 text-emerald-600 font-bold text-sm hover:opacity-80">
                    My Lists <i data-lucide="chevron-down" class="w-4 h-4"></i>
                </button>
                <button class="border border-emerald-500 text-slate-800 text-sm font-medium px-4 py-1.5 rounded bg-white flex items-center gap-1">
                    <span class="text-emerald-500 font-bold">+</span> Add Filter
                </button>

                <div class="relative inline-block text-left ml-2">
                    <button id="columnToggleBtn" class="border border-slate-300 text-slate-700 text-sm font-medium px-4 py-1.5 rounded bg-white flex items-center gap-1 hover:bg-slate-50">
                        <i data-lucide="eye" class="w-4 h-4"></i> Columns <i data-lucide="chevron-down" class="w-3 h-3"></i>
                    </button>
                    <div id="columnToggleMenu" class="hidden absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-30 max-h-60 overflow-y-auto p-2">
                        <div class="space-y-1" id="columnCheckboxesContainer">
                            </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <button class="border border-red-500 text-red-500 text-sm font-medium px-4 py-1.5 rounded bg-white">New</button>
                <button class="bg-emerald-400 text-white text-sm font-medium px-4 py-1.5 rounded opacity-60 cursor-not-allowed">Save List</button>
            </div>
        </div>

        <div class="bg-white px-8 py-4 flex flex-wrap items-center justify-end gap-3">
            <button class="border border-emerald-500 text-slate-800 text-xs font-bold px-4 py-2 rounded bg-white hover:bg-slate-50">
                Create Reminders (3)
            </button>
            <button id="resetBtn" class="border border-emerald-500 text-slate-800 text-xs font-bold px-4 py-2 rounded bg-white hover:bg-slate-50">
                Reset
            </button>
            <div class="flex items-center gap-1 w-full md:w-auto">
                <input type="text" id="searchInput" placeholder="Search" class="w-full pl-3 pr-8 py-2 border border-slate-300 rounded text-xs focus:outline-none focus:border-emerald-500">
                <button id="searchBtn" class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold px-6 py-2 rounded transition-colors">
                    Search
                </button>
            </div>
            <button id="exportBtn" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded transition-colors flex items-center gap-1 shadow-sm">
                <i data-lucide="download" class="w-3.5 h-3.5"></i> Export Excel
            </button>
            <button class="border border-emerald-500 text-emerald-600 p-2 rounded bg-white hover:bg-slate-50">
                <i data-lucide="more-horizontal" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="flex-1 px-8 pb-8">
            <div class="bg-white border border-slate-200 rounded shadow-sm overflow-hidden flex flex-col">
                
                <div class="overflow-x-auto custom-table-scrollbar relative">
                    <x-table-skeleton />
                    <table id="patientsTable" class="w-full text-left border-collapse table-auto">
                        <thead>
                            <tr class="bg-slate-50 text-slate-700 font-bold text-xs border-b border-slate-200">
                                <th class="p-3 bg-slate-50 sticky left-0 z-20 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)] border-r border-slate-200 min-w-[260px] max-w-[260px]">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" class="w-4 h-4 text-emerald-500 border-slate-300 rounded focus:ring-0">
                                        <span class="flex items-center gap-1 cursor-pointer select-none">
                                            <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Patient Name
                                        </span>
                                    </div>
                                </th>
                                <th class="p-3 min-w-[100px] text-xs font-bold"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Patient ID</span></th>
                                <th class="p-3 min-w-[120px] text-xs font-bold"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Phone</span></th>
                                <th class="p-3 min-w-[160px] text-xs font-bold"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Email</span></th>
                                <th class="p-3 min-w-[100px] text-xs font-bold"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Birthdate</span></th>
                                <th class="p-3 min-w-[120px] text-xs font-bold"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> City</span></th>
                                <th class="p-3 min-w-[80px] text-xs font-bold"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> State</span></th>
                                <th class="p-3 min-w-[120px] text-xs font-bold"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> First Visit</span></th>
                                <th class="p-3 min-w-[120px] text-xs font-bold"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Last Visit</span></th>
                                <th class="p-3 min-w-[150px] text-xs font-bold"><span class="flex items-center gap-1 cursor-pointer"><i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Lifetime Prod</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700 bg-white">
                        </tbody>
                    </table>
                </div>

                <div id="custom-pagination-container" class="p-4 bg-white border-t border-slate-100 flex items-center justify-between">
                </div>

            </div>
        </div>

        <div id="exportModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center transition-opacity">
            <div class="bg-white rounded-lg border border-slate-200 shadow-xl w-full max-w-md overflow-hidden transform transition-transform scale-100 p-6">
                <div class="flex items-center gap-3 mb-4 text-slate-900">
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                        <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold">Export Options</h3>
                </div>
                
                <div class="mb-5">
                    <label for="exportFileName" class="block text-xs font-semibold text-slate-600 mb-1.5">File Name</label>
                    <div class="relative flex items-center">
                        <input type="text" id="exportFileName" class="w-full pl-3 pr-16 py-2 border border-slate-300 rounded text-xs font-medium text-slate-800 focus:outline-none focus:border-emerald-500">
                        <span class="absolute right-3 text-xs font-semibold text-slate-400 pointer-events-none">.xlsx</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">You can customize the export filename above before downloading.</p>
                </div>

                <div class="flex items-center justify-end gap-2.5">
                    <button id="cancelExportBtn" class="border border-slate-300 text-slate-700 text-xs font-bold px-4 py-2 rounded bg-white hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button id="confirmExportBtn" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-5 py-2 rounded transition-colors flex items-center gap-1 shadow-sm">
                        Continue Export
                    </button>
                </div>
            </div>
        </div>

            <div id="patientModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center transition-opacity">
                <div class="bg-white rounded-lg border border-slate-200 shadow-xl w-full max-w-2xl overflow-hidden transform transition-transform scale-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-slate-900" id="patientModalTitle">Patient Details</h3>
                        <button id="closePatientModal" class="text-slate-500 hover:text-slate-700">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <!-- Tab navigation -->
                    <div class="border-b border-slate-200 mb-4">
                        <nav class="flex space-x-4" id="patientTabNav">
                            <button data-tab="overview" class="pb-2 border-b-2 border-emerald-500 text-emerald-600 font-medium">Overview</button>
                            <button data-tab="history" class="pb-2 text-slate-600 hover:text-emerald-600">History</button>
                            <button data-tab="notes" class="pb-2 text-slate-600 hover:text-emerald-600">Notes</button>
                        </nav>
                    </div>
                    <!-- Tab panels -->
                    <div id="patientTabContent">
                        <div id="overview" class="tab-panel">
                            <p class="text-sm text-slate-700" id="patientOverviewContent">Loading...</p>
                        </div>
                        <div id="history" class="tab-panel hidden">
                            <p class="text-sm text-slate-700" id="patientHistoryContent">Loading...</p>
                        </div>
                        <div id="notes" class="tab-panel hidden">
                            <p class="text-sm text-slate-700" id="patientNotesContent">Loading...</p>
                        </div>
                    </div>
                </div>
            </div>

    </div>

    <style>
        .dt-paging { display: flex; gap: 0.25rem; }
        .dt-paging .dt-paging-button {
            padding: 0.375rem 0.75rem !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            border-radius: 0.375rem !important;
            border: 1px solid #e2e8f0 !important;
            background: white !important;
            color: #334155 !important;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .dt-paging .dt-paging-button:hover:not(.disabled) {
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }
        .dt-paging .dt-paging-button.current {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
            color: white !important;
        }
        .dt-paging .dt-paging-button.disabled { opacity: 0.4; cursor: not-allowed; }
    </style>

    <script>
    let table;

    $(document).ready(function() {
        table = $('#patientsTable').DataTable({
            processing: true,
            serverSide: true,
            paging: true,
            pageLength: 10,
            lengthChange: false,
            pagingType: 'simple_numbers',
            searching: true, 
            info: false,
            layout: { topStart: null, topEnd: null, bottomStart: null, bottomEnd: 'paging' },
            ajax: {
                url: "{{ route('patients.data') }}",
                type: "GET",
                beforeSend: function() { $("#tableSkeleton").removeClass('hidden'); },
                complete: function() { $("#tableSkeleton").addClass('hidden'); }
            },
            columns: [
                {
                    data: 'name',
                    render: function(data, type, row) {
                        return `
                        <div class="p-3 bg-white sticky left-0 group-hover:bg-slate-50/80 z-10 border-r border-slate-200 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)] flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" class="w-4 h-4 text-emerald-500 border-slate-300 rounded focus:ring-0">
                                <span class="font-medium">${data}</span>
                            </div>
                            <button onclick="openPatient(${row.id})" class="text-slate-400 hover:text-emerald-500 transition-colors p-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7"/><path d="M7 7h10v10"/></svg>
                            </button>
                        </div>
                        `;
                    }
                },
                { data: 'id' },
                { data: 'phone' },
                { data: 'email' },
                { data: 'birthdate' },
                { data: 'city' },
                { data: 'state' },
                { data: 'first_visit' },
                { data: 'last_visit' },
                {
                    data: 'lifetime_production',
                    render: function(data) { return '$' + Number(data).toLocaleString(); }
                }
            ],
            order: [[0, 'asc']],
            drawCallback: function() {
                lucide.createIcons();
                $('#custom-pagination-container').append($('.dt-paging'));
            },
            initComplete: function() {
                let container = $('#columnCheckboxesContainer');
                table.columns().every(function(index) {
                    let title = $(this.header()).text().trim();
                    if(title === "") return;
                    let checked = this.visible() ? 'checked' : '';
                    container.append(`
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded text-xs text-slate-700 cursor-pointer select-none">
                            <input type="checkbox" data-column="${index}" ${checked} class="col-toggle-chk w-3.5 h-3.5 text-emerald-500 border-slate-300 rounded focus:ring-0">
                            <span>${title}</span>
                        </label>
                    `);
                });
            }
        });

        // Column Visibility 
        $(document).on('change', '.col-toggle-chk', function() {
            let colIndex = $(this).data('column');
            table.column(colIndex).visible(!table.column(colIndex).visible());
        });

        $('#columnToggleBtn').on('click', function(e) {
            e.stopPropagation();
            $('#columnToggleMenu').toggleClass('hidden');
        });
        $(document).on('click', function() { $('#columnToggleMenu').addClass('hidden'); });
        $('#columnToggleMenu').on('click', function(e) { e.stopPropagation(); });

        // Custom search mechanism
        $("#searchBtn").on('click', function(){
            table.search($("#searchInput").val()).draw();
        });
        $("#searchInput").on('keypress', function(e){
            if(e.which == 13){ $("#searchBtn").click(); }
        });

        // NEW: Reset Button Interaction
        $("#resetBtn").on('click', function() {
            $("#searchInput").val(""); // Clear input window text
            table.search("").draw(); // Clear search query matrix inside DataTables & hit remote server route afresh
        });

        // NEW: Export Trigger Modal Behavior
        $("#exportBtn").click(function(){
            // Set dynamic predefined export filename format: patient_export_YYYY-MM-DD
            let today = new Date().toISOString().split('T')[0];
            $("#exportFileName").val("patient_export_" + today);
            
            // Pop open modal
            $("#exportModal").removeClass('hidden');
        });

        // Close modal configurations
        $("#cancelExportBtn").click(function(){
            $("#exportModal").addClass('hidden');
        });

        // Action continuation configuration
        $("#confirmExportBtn").click(function(){
            let currentSearchValue = $("#searchInput").val();
            let customName = $("#exportFileName").val() || "patient_export";

            $("#exportModal").addClass('hidden'); // Hide popup interface immediately

            $.ajax({
                url: '/patients/export',
                method: 'POST',
                data: {
                    _token: "{{csrf_token()}}",
                    search: currentSearchValue,
                    filename: customName // Pass requested target file name to your server backend controller framework
                },
                success: function(file){
                    window.location = file.url;
                }
            });
        });
    });

        // Updated openPatient to fetch data and show modal with tabs
        function openPatient(id){
            // $.ajax({
                // url:'/patients/'+id,
                // type:'GET',
                // success:function(response){
                    // Populate modal title
                    $('#patientModalTitle').text('Patient: ' + (response.name || ''));
                    // Build overview content (basic fields)
                    let overviewHtml = `<ul class="list-disc list-inside space-y-1">
                        <li><strong>ID:</strong> ${response.id || ''}</li>
                        <li><strong>Name:</strong> ${response.name || ''}</li>
                        <li><strong>Email:</strong> ${response.email || ''}</li>
                        <li><strong>Phone:</strong> ${response.phone || ''}</li>
                        <li><strong>City:</strong> ${response.city || ''}</li>
                        <li><strong>State:</strong> ${response.state || ''}</li>
                    </ul>`;
                    $('#patientOverviewContent').html(overviewHtml);
                    // History and notes could be separate arrays in response
                    $('#patientHistoryContent').html(response.history ? response.history.join('<br>') : 'No history available.');
                    $('#patientNotesContent').html(response.notes ? response.notes.join('<br>') : 'No notes available.');
                    // Show modal
                    $('#patientModal').removeClass('hidden');
            //     },
            //     error:function(){
            //         alert('Failed to load patient data.');
            //     }
            // });
        }

        // Tab navigation logic
        $('#patientTabNav button').on('click', function(){
            const tab = $(this).data('tab');
            // Update active tab styling
            $('#patientTabNav button').removeClass('border-b-2 border-emerald-500 text-emerald-600').addClass('text-slate-600');
            $(this).addClass('border-b-2 border-emerald-500 text-emerald-600');
            // Show/hide panels
            $('.tab-panel').addClass('hidden');
            $(`#${tab}`).removeClass('hidden');
        });

        // Close modal
        $('#closePatientModal').on('click', function(){
            $('#patientModal').addClass('hidden');
        });

    $("#refreshPatients").click(function(){ table.ajax.reload(); });
    </script>
</x-app-layout>