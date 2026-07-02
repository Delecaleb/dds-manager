<x-app-layout>
  <header class="bg-white border-b border-gray-100 px-8 py-4 flex justify-between items-center">
    <div class="flex items-center space-x-2">
      <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Collections</h1>
    </div>
    <a href="#"
      class="flex items-center bg-[#002b24] text-emerald-400 font-semibold px-4 py-2 rounded-full text-sm hover:opacity-90 transition">
      <i class="fa-solid fa-book-open mr-2"></i>
      <span>Quick Start Guide</span>
    </a>
  </header>

  <section class="bg-white border-b border-gray-200 px-8 py-4 flex flex-wrap items-center justify-between gap-4">
    <div class="flex flex-wrap items-center gap-3">
      <div class="relative flex items-center border border-gray-300 rounded px-3 py-1.5 bg-white shadow-sm">
        <i class="fa-regular fa-calendar text-gray-400 mr-2 text-sm"></i>
        <span class="text-sm font-medium text-gray-700">June 2026</span>
      </div>

      <select
        class="border border-gray-300 rounded px-4 py-1.5 text-sm bg-white focus:outline-none focus:border-emerald-500 shadow-sm font-medium text-gray-700">
        <option selected>8 Mile</option>
        <option>Detroit Main</option>
      </select>

      <button
        class="bg-white border border-emerald-500 text-emerald-600 px-5 py-1.5 rounded text-sm font-semibold hover:bg-emerald-50 transition shadow-sm">
        Update
      </button>
    </div>
  </section>

  <section class="px-8 bg-white border-b border-gray-200 flex gap-6 text-sm font-medium text-gray-500">
    <button class="border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4">Schedule</button>
    <button class="border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4">Tasks</button>
    <button class="border-b-2 border-emerald-500 text-emerald-600 font-bold pb-3 pt-4">Collections</button>
    <button class="border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4">KPIs</button>
    <button class="border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4">Performance</button>
  </section>

  <main class="p-6 space-y-6 max-w-[1600px] mx-auto">
    <div class="fw-700 border-b p-3">Revenue</div>
    <section class="grid grid-cols-1 md:grid-cols-4 gap-6">

      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider">Gross Production</p>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="gross-production">$0</h4>
          </div>
          <div class="p-3 bg-emerald-50 rounded-lg text-emerald-600">
            <i class="fa-solid fa-wallet text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
          Gross production collections tracking active
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Net Production</p>
            <h4 class="text-3xl font-black text-gray-900 mt-2" id="net-production">$0</h4>
          </div>
          <div class="p-3 bg-teal-50 rounded-lg text-teal-600">
            <i class="fa-solid fa-calendar-day text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
          Calculated based on active days open
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider">Adjustment</p>
            <h4 class="text-3xl font-black text-gray-900 mt-2">0</h4>
          </div>
          <div class="p-3 bg-red-50 rounded-lg text-red-500">
            <i class="fa-solid fa-users-slash text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-red-600 font-semibold">
          No current outstanding recall alerts
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Collection</p>
            <h4 class="text-3xl font-black text-gray-900 mt-2">73.66%</h4>
          </div>
          <div class="p-3 bg-amber-50 rounded-lg text-amber-600">
            <i class="fa-solid fa-percent text-xl"></i>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
          Target threshold value: 80.00%
        </div>
      </div>
    </section>
  </main>
</x-app-layout>