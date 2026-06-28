<x-app-layout>
    <div class="min-h-screen flex flex-col">
        
        <!-- SUB-HEADER AREA (Patient Portal context from image_dffd4d.png) -->
        <div class="bg-white border-b border-slate-200 px-8 pt-6 pb-0 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Patient Portal</h1>
                <button class="bg-[#001f3f] text-emerald-400 font-semibold text-xs px-4 py-2 rounded-full flex items-center gap-2 shadow-sm">
                    <i data-lucide="book-open" class="w-4 h-4"></i> Quick Start Guide
                </button>
            </div>

            <!-- Context Engine Controls -->
            <div class="flex items-center gap-3 mb-6">
                <div class="relative w-48">
                    <select class="w-full appearance-none bg-white border border-slate-300 rounded px-3 py-1.5 text-sm font-medium text-slate-700 focus:outline-none focus:border-emerald-500 cursor-pointer">
                        <option value="8mile">8 Mile</option>
                        <option value="downtown">Downtown Dental</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-500">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>
                <button id="refreshPatients" class="border border-emerald-500 text-slate-800 text-sm font-semibold px-5 py-1.5 rounded bg-white hover:bg-slate-50 transition-colors">
                    Refresh
                </button>
            </div>

            <!-- Segment Navigation Tabs -->
            <div class="flex gap-6 border-b border-slate-200 text-sm font-medium">
                <a href="#" class="border-b-2 border-emerald-500 text-slate-900 pb-3 font-bold">Patients</a>
                <a href="#" class="text-slate-400 hover:text-slate-600 pb-3">Reminders</a>
                <a href="#" class="text-slate-400 hover:text-slate-600 pb-3">Performance</a>
            </div>
        </div>

        <!-- FILTER AND CONTROL TOOLBAR PANEL -->
        <div class="px-8 py-4 bg-[#f1f5f9] text-xs font-semibold text-emerald-600 border-b border-slate-200">
            <span class="cursor-pointer hover:underline">Additional Filters (0)</span>
        </div>

        <!-- RECONCILIATION FILTER BOX STRIP -->
        <div class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <button class="flex items-center gap-2 text-emerald-600 font-bold text-sm hover:opacity-80">
                    My Lists <i data-lucide="chevron-down" class="w-4 h-4"></i>
                </button>
                <button class="border border-emerald-500 text-slate-800 text-sm font-medium px-4 py-1.5 rounded bg-white flex items-center gap-1">
                    <span class="text-emerald-500 font-bold">+</span> Add Filter
                </button>
            </div>
            <div class="flex items-center gap-2">
                <button class="border border-red-500 text-red-500 text-sm font-medium px-4 py-1.5 rounded bg-white">New</button>
                <button class="bg-emerald-400 text-white text-sm font-medium px-4 py-1.5 rounded opacity-60 cursor-not-allowed">Save List</button>
            </div>
        </div>

        <!-- DATA CONTROL ROW (Actions Right Above Table Viewport) -->
        <div class="bg-white px-8 py-4 flex flex-wrap items-center justify-end gap-3">
            <button class="border border-emerald-500 text-slate-800 text-xs font-bold px-4 py-2 rounded bg-white">
                Create Reminders (3)
            </button>
            <button class="border border-emerald-500 text-slate-800 text-xs font-bold px-4 py-2 rounded bg-white">
                Reset
            </button>
            <input type="text" id="searchInput" placeholder="Search" class="w-full pl-3 pr-8 py-2 border border-slate-300 rounded text-xs focus:outline-none focus:border-emerald-500">
<button id="searchBtn" class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold px-6 py-2 rounded transition-colors">
                Search
            </button>
            <button class="border border-slate-300 text-slate-700 text-xs font-bold px-4 py-2 rounded bg-white hover:bg-slate-50">
                Export CSV
            </button>
            <button class="border border-emerald-500 text-emerald-600 p-2 rounded bg-white">
                <i data-lucide="more-horizontal" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- MAIN TABLE VIEWER WORKSPACE -->
        <div class="flex-1 px-8 pb-8">
            <div class="bg-white border border-slate-200 rounded shadow-sm overflow-hidden flex flex-col">
                
                <!-- STICKY HORIZONTAL SCROLL CONTAINER -->
                <div class="overflow-x-auto custom-table-scrollbar relative">
                    {{-- skeleton loader --}}
                    <x-table-skeleton />
                    <table id="patientsTable" class="w-full text-left border-collapse table-auto">
                        <thead>
                            <tr class="bg-slate-50 text-slate-700 font-bold text-xs border-b border-slate-200">
                                 
                                <!-- FIXED AXIS: COMBINED CHECKBOX & PATIENT NAME COLUMN -->
                                <th class="p-3 bg-slate-50 sticky left-0 z-20 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)] border-r border-slate-200 min-w-[260px] max-w-[260px]">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" class="w-4 h-4 text-emerald-500 border-slate-300 rounded focus:ring-0">
                                        <span class="flex items-center gap-1 cursor-pointer select-none">
                                            <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-400"></i> Patient Name
                                        </span>
                                    </div>
                                </th>
                                
                                <!-- SCROLLABLE COLUMNS FROM IMAGE -->
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

            </div>
        </div>

    </div>
    <script>

    let table;


    $(document).ready(function(){


table = $('#patientsTable').DataTable({


processing:true,

serverSide:true, paging:true, pageLength:10, lengthChange:false, pagingType:'simple_numbers', searching:true, info:false,


ajax:{


url:"{{ route('patients.data') }}",

type:"GET",


beforeSend:function(){

$("#tableSkeleton").removeClass('hidden');

},


complete:function(){

$("#tableSkeleton").addClass('hidden');

}

},

columns:[


{
data:'name',

render:function(data,row){

return `

<div class="p-3 bg-white sticky left-0 group-hover:bg-slate-50/80 z-10 border-r border-slate-200 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)]">

<input type="checkbox">

<span class="font-medium">

${data}

</span>


<button onclick="openPatient(${row.id})">

<i data-lucide="arrow-up-right"></i>

</button>


</div>

`;

}

},



{
data:'id',
title:'Patient ID'
},



{
data:'phone'
},



{
data:'email'
},



{
data:'birthdate'
},



{
data:'city'
},



{
data:'state'
},



{
data:'first_visit'
},



{
data:'last_visit'
},



{
data:'lifetime_production',

render:function(data){

return '$'+Number(data).toLocaleString();

}

}


],


order:[[0,'asc']],



drawCallback:function(){

lucide.createIcons();

}
});
});

function openPatient(id){


$.ajax({

url:'/patients/'+id,

type:'GET',


beforeSend(){

// open modal loader

},


success:function(response){


// populate patient modal


}


});


}

$("#refreshPatients").click(function(){


table.ajax.reload();


});

// Custom search functionality
$("#searchBtn").on('click', function(){
    table.search($("#searchInput").val()).draw();
});
$("#searchInput").on('keypress', function(e){
    if(e.which == 13){
        $("#searchBtn").click();
    }
});

$("#exportBtn").click(function(){


$.ajax({

url:'/patients/export',

method:'POST',

data:{
_token:"{{csrf_token()}}"
},

success:function(file){


window.location=file.url;


}


});


});
</script>
</x-app-layout>