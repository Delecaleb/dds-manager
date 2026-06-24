<x-app-layout>
    <header class="bg-white border-b border-gray-100 px-8 py-4 flex justify-between items-center">
    <div class="flex items-center space-x-2">
      <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Scheduler</h1>
    </div>
    <a href="#" class="flex items-center bg-[#002b24] text-emerald-400 font-semibold px-4 py-2 rounded-full text-sm hover:opacity-90 transition">
      <i class="fa-solid fa-book-open mr-2"></i>
      <span>Quick view</span>
    </a>
  </header>

  <section class="bg-white border-b border-gray-200 px-8 py-4 flex flex-wrap items-center justify-between gap-4">
    <div class="flex flex-wrap items-center gap-3">
      <div class="relative">
        <input type="date" value="2026-06-23" class="border border-gray-300 rounded px-3 py-1.5 text-sm bg-white focus:outline-none focus:border-emerald-500 shadow-sm text-gray-700 font-medium">
      </div>

      <select class="border border-gray-300 rounded px-4 py-1.5 text-sm bg-white focus:outline-none focus:border-emerald-500 shadow-sm font-medium text-gray-700 min-w-[120px]">
        <option>8 Mile</option>
        <option>Detroit Main</option>
      </select>

      <select class="border border-gray-300 rounded px-4 py-1.5 text-sm bg-white focus:outline-none focus:border-emerald-500 shadow-sm font-medium text-gray-700">
        <option>Line of Business: All</option>
        <option>Invisalign</option>
        <option>Hygiene</option>
      </select>

      <button class="bg-white border border-emerald-500 text-emerald-600 px-5 py-1.5 rounded text-sm font-semibold hover:bg-emerald-50 transition shadow-sm">
        Refresh
      </button>
    </div>

    <div class="flex bg-gray-100 p-1 rounded-md border border-gray-200 shadow-sm">
      <button class="px-4 py-1 text-xs font-medium text-gray-500 hover:text-gray-900">Month</button>
      <button class="px-4 py-1 text-xs font-medium text-gray-500 hover:text-gray-900">Week</button>
      <button class="px-4 py-1 text-xs font-medium bg-white text-emerald-600 rounded shadow-xs font-semibold">Day</button>
    </div>
  </section>

  <main class="p-6 flex gap-6">
    
    <div class="flex-1 bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
      
      <div class="grid grid-cols-[80px_repeat(5,_1fr)] border-b border-gray-200 bg-gray-50 text-center font-bold text-gray-700 text-sm tracking-wide">
        <div class="py-3 border-r border-gray-200 text-xs font-semibold text-gray-400 uppercase">Time</div>
        <div class="py-3 border-r border-gray-200 flex flex-col justify-center items-center">
          <span class="text-xs text-gray-400 font-medium">DR-2</span>
          <span class="text-gray-900">Dr. Adams</span>
        </div>
        <div class="py-3 border-r border-gray-200 flex flex-col justify-center items-center">
          <span class="text-xs text-gray-400 font-medium">DR-3</span>
          <span class="text-gray-900">Dr. Foster</span>
        </div>
        <div class="py-3 border-r border-gray-200 flex flex-col justify-center items-center">
          <span class="text-xs text-gray-400 font-medium">DR-4</span>
          <span class="text-gray-900">Dr. Hatchett</span>
        </div>
        <div class="py-3 border-r border-gray-200 flex flex-col justify-center items-center">
          <span class="text-xs text-gray-400 font-medium">DR-1</span>
          <span class="text-gray-900 bg-emerald-50 text-emerald-700 px-2 rounded">Dr. Elias (Kathy)</span>
        </div>
        <div class="py-3 flex flex-col justify-center items-center">
          <span class="text-xs text-gray-400 font-medium">DR-5</span>
          <span class="text-gray-900">Dr. Mason</span>
        </div>
      </div>

      <div class="relative h-[600px] overflow-y-auto">
        
        <div class="absolute inset-0 grid grid-cols-[80px_repeat(5,_1fr)] auto-rows-[60px] pointer-events-none">
          <div class="border-b border-r border-gray-100 flex items-start justify-center pt-2 text-xs font-medium text-gray-400 bg-gray-50/50">09:00 AM</div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-gray-100"></div>

          <div class="border-b border-r border-gray-100 flex items-start justify-center pt-2 text-xs font-medium text-gray-400 bg-gray-50/50">09:30 AM</div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-gray-100"></div>

          <div class="border-b border-r border-gray-100 flex items-start justify-center pt-2 text-xs font-medium text-gray-400 bg-gray-50/50">10:00 AM</div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-gray-100"></div>

          <div class="border-b border-r border-gray-100 flex items-start justify-center pt-2 text-xs font-medium text-gray-400 bg-gray-50/50">10:30 AM</div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-gray-100"></div>

          <div class="border-b border-r border-gray-100 flex items-start justify-center pt-2 text-xs font-medium text-gray-400 bg-gray-50/50">11:00 AM</div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-r border-gray-100"></div>
          <div class="border-b border-gray-100"></div>
        </div>

        <div class="absolute inset-0 grid grid-cols-[80px_repeat(5,_1fr)] auto-rows-[60px]">
          
          <div></div> 

          <div class="relative p-1">
            <div class="absolute top-[120px] left-1 right-1 h-[85px] bg-emerald-50 border-l-4 border-emerald-500 rounded p-2 shadow-xs cursor-pointer hover:shadow-md transition group z-10">
              <div class="flex justify-between items-start">
                <span class="text-xs font-bold text-emerald-900 truncate block group-hover:text-emerald-600">Jones, Lakia</span>
                <span class="text-[10px] bg-emerald-200 text-emerald-800 px-1 rounded font-semibold">Invis</span>
              </div>
              <p class="text-[11px] text-emerald-700 font-medium mt-0.5"><i class="fa-solid fa-phone mr-1 scale-90 opacity-70"></i>(248) 763-9759</p>
              <span class="text-[10px] text-emerald-600 block mt-1 font-semibold">Confirmed • Rep5</span>
            </div>
          </div>

          <div class="relative p-1">
            <div class="absolute top-[30px] left-1 right-1 h-[60px] bg-sky-50 border-l-4 border-sky-500 rounded p-2 shadow-xs cursor-pointer hover:shadow-md transition z-10">
              <span class="text-xs font-bold text-sky-900 truncate block">Foster, Latoya</span>
              <span class="text-[10px] text-sky-600 block mt-1 font-medium">9:30 - 10:00 AM</span>
            </div>
          </div>

          <div class="relative p-1">
            <div class="absolute top-[0px] left-1 right-1 h-[60px] bg-purple-50 border-l-4 border-purple-500 rounded p-2 shadow-xs cursor-pointer hover:shadow-md transition z-10">
              <span class="text-xs font-bold text-purple-900 truncate block">Hatchett jr, Taurez</span>
              <span class="text-[10px] text-purple-600 block mt-1 font-medium">General • Confirmed</span>
            </div>
          </div>

          <div class="relative p-1">
            <div class="absolute top-[60px] left-1 w-[47%] h-[80px] bg-teal-50 border-l-4 border-teal-500 rounded p-1.5 shadow-xs cursor-pointer hover:shadow-md transition z-10">
              <span class="text-[11px] font-bold text-teal-900 line-clamp-1">Reed, Destiny</span>
              <span class="text-[10px] text-teal-600 block font-medium">9:30 AM</span>
            </div>
            <div class="absolute top-[80px] right-1 w-[47%] h-[80px] bg-amber-50 border-l-4 border-amber-500 rounded p-1.5 shadow-xs cursor-pointer hover:shadow-md transition z-20">
              <span class="text-[11px] font-bold text-amber-900 line-clamp-1">Glover, Khamren</span>
              <span class="text-[10px] text-amber-600 block font-medium">10:00 AM</span>
              <span class="text-[9px] bg-amber-200 text-amber-800 px-1 rounded font-bold">Conflict</span>
            </div>
          </div>

          <div class="relative p-1">
            <div class="absolute top-[0px] left-1 right-1 h-[60px] bg-emerald-50 border-l-4 border-emerald-500 rounded p-2 shadow-xs cursor-pointer hover:shadow-md transition z-10">
              <span class="text-xs font-bold text-emerald-900 truncate block">Mason, Terrance</span>
              <span class="text-[10px] text-emerald-600 block mt-1 font-medium">Confirmed via LVM</span>
            </div>
          </div>

        </div>

      </div>
    </div>

    <div class="w-80 bg-white rounded-xl shadow-xs border border-gray-200 p-4 flex flex-col justify-between">
      <div>
        <div class="flex justify-between items-center pb-3 border-b border-gray-100">
          <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wide">Appointment Details</h3>
          <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
        </div>

        <div class="mt-4 bg-gray-50 border border-gray-200 rounded-lg p-3">
          <div class="flex justify-between items-start">
            <h4 class="text-sm font-bold text-gray-900">Jones, Lakia</h4>
            <span class="text-[11px] font-semibold text-gray-400">ID: 22057</span>
          </div>
          <p class="text-xs text-gray-500 mt-1"><span class="font-semibold text-gray-700">Provider:</span> Kathy Elias (DR-1)</p>
          <p class="text-xs text-gray-500"><span class="font-semibold text-gray-700">Time:</span> 10:00 AM - 10:45 AM</p>
          
          <div class="mt-3 pt-3 border-t border-gray-200">
            <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block">System Verification Log</span>
            <p class="text-xs text-gray-600 mt-1 bg-white p-2 rounded border border-gray-100 italic">
              "06/18/26 - appt. confirmed - Rep5 06/17/26 - LVM - Rep5"
            </p>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-2 text-center">
          <div class="p-2 border border-gray-100 rounded-md bg-emerald-50/40">
            <span class="text-[10px] text-gray-400 font-medium uppercase block">Production</span>
            <span class="text-sm font-bold text-emerald-700">$32,565.00</span>
          </div>
          <div class="p-2 border border-gray-100 rounded-md bg-teal-50/40">
            <span class="text-[10px] text-gray-400 font-medium uppercase block">Collections</span>
            <span class="text-sm font-bold text-teal-700">$49,215.78</span>
          </div>
        </div>
      </div>

      <div class="pt-4 border-t border-gray-100 space-y-2">
        <button class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-2 rounded text-sm transition shadow-xs">
          Modify Appointment
        </button>
        <button class="w-full bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 font-medium py-2 rounded text-xs transition">
          View Patient Full Ledger
        </button>
      </div>
    </div>

  </main>
</x-app-layout>