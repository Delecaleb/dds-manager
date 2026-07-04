<x-app-layout>

  <header class="bg-white border-b border-gray-100 px-8 py-4 flex justify-between items-center">
    <div class="flex items-center space-x-2">
      <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Deposit Slip</h1>
    </div>
  </header>

  <section class="bg-white border-b border-gray-200 px-8 py-4 flex flex-wrap items-center justify-between gap-4">
    <div class="flex flex-wrap items-center gap-3">
      <div class="relative flex items-center border border-gray-300 rounded px-3 py-1.5 bg-white shadow-xs">
        <i class="fa-regular fa-calendar text-gray-400 mr-2 text-sm"></i>
        <span class="text-sm font-medium text-gray-700">June 2026</span>
      </div>

      <select class="border border-gray-300 rounded px-4 py-1.5 text-sm bg-white focus:outline-none focus:border-emerald-500 shadow-xs font-medium text-gray-700">
        <option selected>8 Mile</option>
      </select>

      <button class="bg-white border border-emerald-500 text-emerald-600 px-5 py-1.5 rounded text-sm font-semibold hover:bg-emerald-50 transition shadow-xs">
        Update
      </button>
    </div>
  </section>

  <section class="px-8 bg-white border-b border-gray-200 flex gap-6 text-sm font-medium text-gray-500">
    <button class="border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4">Schedule</button>
    <button class="border-b-2 border-emerald-500 text-emerald-600 font-bold pb-3 pt-4">Tasks</button>
    <button class="border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4">Collections</button>
    <button class="border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4">KPIs</button>
    <button class="border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4">Performance</button>
  </section>

  <main class="p-6 space-y-6 max-w-[1600px] mx-auto">

    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-xs flex items-center justify-between">
        <div>
          <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Completed Tasks</span>
          <span class="text-2xl font-black text-gray-900 mt-1 block">1,240</span>
        </div>
        <span class="text-emerald-500 bg-emerald-50 p-2.5 rounded-lg"><i class="fa-solid fa-square-check text-lg"></i></span>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-xs flex items-center justify-between">
        <div>
          <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Pending Actions</span>
          <span class="text-2xl font-black text-gray-900 mt-1 block">42</span>
        </div>
        <span class="text-amber-500 bg-amber-50 p-2.5 rounded-lg"><i class="fa-solid fa-clock text-lg"></i></span>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-xs flex items-center justify-between">
        <div>
          <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Collection Rate</span>
          <span class="text-2xl font-black text-gray-900 mt-1 block">86.04%</span>
        </div>
        <span class="text-blue-500 bg-blue-50 p-2.5 rounded-lg"><i class="fa-solid fa-chart-line text-lg"></i></span>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-xs flex items-center justify-between">
        <div>
          <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Outstanding Value</span>
          <span class="text-2xl font-black text-gray-900 mt-1 block">$32,915</span>
        </div>
        <span class="text-purple-500 bg-purple-50 p-2.5 rounded-lg"><i class="fa-solid fa-hand-holding-dollar text-lg"></i></span>
      </div>
    </section>

    <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden p-4">
      
      <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div class="flex items-center gap-1">
          <button class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1 rounded transition hover:opacity-80">Top 20%</button>
          <button class="bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-1 rounded transition hover:opacity-80">Mid Tier</button>
          <button class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-1 rounded transition hover:opacity-80">Bottom 20%</button>
        </div>

        <div class="flex items-center gap-2">
          <div class="relative">
            <input type="text" placeholder="Search..." class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:border-emerald-500 pr-8 w-48">
            <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2 text-gray-400 text-xs"></i>
          </div>
          <button class="border border-emerald-500 text-emerald-600 font-semibold px-4 py-1 rounded text-sm hover:bg-emerald-50 transition shadow-xs">
            Export CSV
          </button>
        </div>
      </div>

      <div class="w-full overflow-x-auto border border-gray-200 rounded-lg max-h-[550px] overflow-y-auto">
        <table class="w-full text-left border-collapse min-w-[1600px]">
          
          <thead class="sticky top-0 z-30 bg-gray-100 text-gray-700 text-xs font-bold uppercase tracking-wider border-b border-gray-200 shadow-xs">
            <tr>
              <th scope="col" class="sticky left-0 bg-gray-100 px-4 py-3.5 border-r border-gray-300 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] z-40 text-gray-900 font-extrabold min-w-[180px]">
                <div class="flex items-center justify-between">
                  <span>Patient</span>
                  <i class="fa-solid fa-arrows-up-down text-[10px] text-gray-400"></i>
                </div>
              </th>
              <th scope="col" class="px-4 py-3.5 border-r border-gray-200 min-w-[140px]">Task Type</th>
              <th scope="col" class="px-4 py-3.5 border-r border-gray-200 min-w-[140px]">Status</th>
              <th scope="col" class="px-4 py-3.5 border-r border-gray-200 min-w-[110px]">Phone</th>
              <th scope="col" class="px-4 py-3.5 border-r border-gray-200 min-w-[160px]">Provider</th>
              <th scope="col" class="px-4 py-3.5 border-r border-gray-200 min-w-[110px] bg-emerald-50 text-emerald-900">Value Amount</th>
              <th scope="col" class="px-4 py-3.5 border-r border-gray-200 min-w-[120px]">Appt Date</th>
              <th scope="col" class="px-4 py-3.5 border-r border-gray-200 min-w-[300px]">Task Notes / Parameters</th>
              <th scope="col" class="px-4 py-3.5 text-center min-w-[100px]">Action</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-200 text-xs font-medium text-gray-600 bg-white">
            
            <tr class="hover:bg-gray-50/80 transition">
              <td class="sticky left-0 bg-white font-bold text-gray-900 px-4 py-3.5 border-r border-gray-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] z-20 flex items-center justify-between">
                <span>Williamson, Elaine</span>
                <button class="text-gray-400 hover:text-emerald-600 ml-1"><i class="fa-solid fa-external-link text-[10px]"></i></button>
              </td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-700">Broken Treatment Call</td>
              <td class="px-4 py-3.5 border-r border-gray-200">
                <span class="px-2 py-0.5 bg-amber-50 text-amber-700 rounded text-[10px] font-bold border border-amber-200 tracking-wide">PENDING</span>
              </td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-500 font-normal">(248) 259-4343</td>
              <td class="px-4 py-3.5 border-r border-gray-200">Mason Haddow</td>
              <td class="px-4 py-3.5 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-bold">$ 840.00</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-500">Jun 19, 2026</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-400 font-normal max-w-xs truncate">Patient requested a callback after discussing with spouse.</td>
              <td class="px-4 py-3.5 text-center">
                <button class="text-gray-400 hover:text-gray-600 px-2 py-1 border border-gray-200 bg-gray-50 rounded-md transition"><i class="fa-solid fa-ellipsis"></i></button>
              </td>
            </tr>

            <tr class="hover:bg-gray-50/80 transition">
              <td class="sticky left-0 bg-white font-bold text-gray-900 px-4 py-3.5 border-r border-gray-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] z-20 flex items-center justify-between">
                <span>Wallace, Kevin</span>
                <button class="text-gray-400 hover:text-emerald-600 ml-1"><i class="fa-solid fa-external-link text-[10px]"></i></button>
              </td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-700">Unscheduled Follow-up</td>
              <td class="px-4 py-3.5 border-r border-gray-200">
                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[10px] font-bold border border-emerald-200 tracking-wide">COMPLETED</span>
              </td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-500 font-normal">(734) 201-2807</td>
              <td class="px-4 py-3.5 border-r border-gray-200">Kathy Elias</td>
              <td class="px-4 py-3.5 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-bold">$ 100.00</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-500">Jun 16, 2026</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-400 font-normal max-w-xs truncate">Broken Bracket treatment option presented.</td>
              <td class="px-4 py-3.5 text-center">
                <button class="text-gray-400 hover:text-gray-600 px-2 py-1 border border-gray-200 bg-gray-50 rounded-md transition"><i class="fa-solid fa-ellipsis"></i></button>
              </td>
            </tr>

            <tr class="hover:bg-gray-50/80 transition">
              <td class="sticky left-0 bg-white font-bold text-gray-900 px-4 py-3.5 border-r border-gray-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] z-20 flex items-center justify-between">
                <span>Powell, Aaliyah</span>
                <button class="text-gray-400 hover:text-emerald-600 ml-1"><i class="fa-solid fa-external-link text-[10px]"></i></button>
              </td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-700">Insurance Pre-Auth Check</td>
              <td class="px-4 py-3.5 border-r border-gray-200">
                <span class="px-2 py-0.5 bg-red-50 text-red-700 rounded text-[10px] font-bold border border-red-200 tracking-wide">OVERDUE</span>
              </td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-500 font-normal">(248) 219-4711</td>
              <td class="px-4 py-3.5 border-r border-gray-200">Kathy Elias</td>
              <td class="px-4 py-3.5 border-r border-gray-200 bg-emerald-50/40 text-gray-400 font-bold">$ 0.00</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-500">Jun 02, 2026</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-400 font-normal max-w-xs truncate">Delta Dental claims validation parameters still pending review.</td>
              <td class="px-4 py-3.5 text-center">
                <button class="text-gray-400 hover:text-gray-600 px-2 py-1 border border-gray-200 bg-gray-50 rounded-md transition"><i class="fa-solid fa-ellipsis"></i></button>
              </td>
            </tr>

          </tbody>
        </table>
      </div>

      <div class="p-4 border-t border-gray-200 bg-gray-50 flex flex-wrap items-center justify-between gap-4 text-xs font-medium text-gray-500 mt-2">
        <div class="flex items-center gap-2">
          <span>Items per page:</span>
          <select class="bg-white border border-gray-300 rounded px-1.5 py-1 text-xs font-medium focus:outline-none shadow-xs">
            <option>20</option>
            <option>50</option>
          </select>
          <span class="ml-2">1-3 of 84 items</span>
        </div>
        
        <div class="flex items-center gap-1">
          <select class="bg-white border border-gray-300 rounded px-1.5 py-1 text-xs font-medium focus:outline-none shadow-xs mr-2">
            <option>1</option>
          </select>
          <span>of 5 pages</span>
          <button class="p-1 px-2.5 border border-gray-300 rounded-md bg-white hover:bg-gray-50 text-gray-600 ml-2 shadow-xs transition-colors"><i class="fa-solid fa-chevron-left text-[10px]"></i></button>
          <button class="p-1 px-2.5 border border-gray-300 rounded-md bg-white hover:bg-gray-50 text-gray-600 shadow-xs transition-colors"><i class="fa-solid fa-chevron-right text-[10px]"></i></button>
        </div>
      </div>

    </div>
  </main>
</x-app-layout>