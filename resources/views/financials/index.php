<x-app-layout>
 <!-- Top Header Navigation -->
  <header class="bg-white border-b border-gray-100 px-8 py-4 flex justify-between items-center">
    <div class="flex items-center space-x-2">
      <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Aging</h1>
    </div>
    <!-- Quick Start Guide Badge matching layout system -->
    <a href="#" class="flex items-center bg-[#002b24] text-emerald-400 font-semibold px-4 py-2 rounded-full text-sm hover:opacity-90 transition">
      <i class="fa-solid fa-book-open mr-2"></i>
      <span>Quick Start Guide</span>
    </a>
  </header>

  <!-- Filter Controls Section -->
  <section class="bg-white border-b border-gray-200 px-8 py-4 flex flex-wrap items-center justify-between gap-4">
    <div class="flex flex-wrap items-center gap-3">
      <!-- Date Input -->
      <div class="relative flex items-center border border-gray-300 rounded px-3 py-1.5 bg-white shadow-sm">
        <i class="fa-regular fa-calendar text-gray-400 mr-2 text-sm"></i>
        <input type="text" value="Jan 01, 2011" class="text-sm font-medium text-gray-700 outline-none w-24">
      </div>

      <!-- Location Dropdown -->
      <select class="border border-gray-300 rounded px-4 py-1.5 text-sm bg-white focus:outline-none focus:border-emerald-500 shadow-sm font-medium text-gray-700">
        <option selected>8 Mile</option>
        <option>Detroit Main</option>
      </select>

      <button class="bg-white border border-emerald-500 text-emerald-600 px-5 py-1.5 rounded text-sm font-semibold hover:bg-emerald-50 transition shadow-sm">
        Refresh
      </button>
    </div>
  </section>

  <!-- Secondary Workspace Tab Filters -->
  <section class="px-8 bg-white border-b border-gray-200 flex gap-6 text-sm font-medium text-gray-500">
    <button class="border-b-2 border-emerald-500 text-emerald-600 font-bold pb-3 pt-4">Responsible Party</button>
    <button class="border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4">By Office</button>
    <button class="border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4">By Patient</button>
    <button class="border-b-2 border-transparent hover:text-gray-700 pb-3 pt-4">By Insurance</button>
  </section>

  <!-- Main Workstation Layout Container -->
  <main class="p-6">
    
    <!-- Info Banner Component -->
    <div class="mb-6 text-xs text-blue-600 bg-blue-50 border border-blue-200 rounded-lg p-3.5 flex items-center shadow-xs">
      <i class="fa-solid fa-circle-info mr-2.5 text-blue-500 text-sm"></i>
      <span>Values display Guarantor balances. Individual Patient Aging values can be viewed by selecting the breakout button next to the Guarantor name.</span>
    </div>

    <!-- Main Card Shell Wrapper -->
    <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden p-4">
      
      <!-- Tier Selector Indicators, Dropdowns & Search Context -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div class="flex items-center gap-1">
          <span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1 rounded">Top 20%</span>
          <span class="bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-1 rounded">Mid Tier</span>
          <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-1 rounded">Bottom 20%</span>
        </div>

        <div class="flex items-center gap-2 ml-auto">
          <!-- Credits Context Filter -->
          <select class="border border-gray-300 rounded px-3 py-1 text-sm bg-white focus:outline-none focus:border-emerald-500 font-medium text-gray-700">
            <option>Include Credits</option>
            <option>Exclude Credits</option>
          </select>
          <!-- Search Box -->
          <div class="relative">
            <input type="text" placeholder="Search..." class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:border-emerald-500 pr-8 w-48">
            <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2 text-gray-400 text-xs"></i>
          </div>
          <!-- Action Export CTA -->
          <button class="border border-emerald-500 text-emerald-600 font-semibold px-4 py-1 rounded text-sm hover:bg-emerald-50 transition shadow-xs">
            Export CSV
          </button>
        </div>
      </div>

      <!-- Scrollable Matrix Window Layer with Sticky Anchor -->
      <div class="w-full overflow-x-auto border border-gray-200 rounded-lg max-h-[600px] overflow-y-auto">
        <table class="w-full text-left border-collapse min-w-[1800px]">
          
          <!-- Column Table Headers System -->
          <thead class="sticky top-0 z-30 bg-gray-100 text-gray-700 text-xs font-bold uppercase tracking-wider shadow-xs border-b border-gray-200">
            <tr>
              <!-- FIXED STICKY COLUMN: Guarantor Name Column -->
              <th scope="col" class="sticky left-0 bg-gray-100 px-4 py-3 border-r border-gray-300 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] z-40 text-gray-900 font-extrabold min-w-[180px]">
                <div class="flex items-center justify-between">
                  <span>Guarantor</span>
                  <i class="fa-solid fa-arrows-up-down text-[10px] text-gray-400"></i>
                </div>
              </th>
              <th scope="col" class="px-4 py-3 border-r border-gray-200 min-w-[110px]">Guarantor ID</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-200 min-w-[220px]">Patient</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-200 min-w-[110px]">Patient ID</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-200 min-w-[100px]">Office</th>
              
              <!-- Financial Data Header Bucket System -->
              <th scope="col" class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[90px]">Current</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[90px]">Over 30</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[90px]">Over 60</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[90px]">Over 90</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[90px]">Over 120</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[90px]">Over 180</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-200 text-emerald-900 bg-emerald-50 min-w-[90px]">Over 240</th>
              <th scope="col" class="px-4 py-3 text-emerald-900 bg-emerald-50 min-w-[90px]">Over 360</th>
            </tr>
          </thead>

          <!-- Table Body Columns mapping from the snapshot references -->
          <tbody class="divide-y divide-gray-200 text-xs font-medium text-gray-600 bg-white">
            
            <!-- Row Sample 1: Hadley, Gloria -->
            <tr class="hover:bg-gray-50/80 transition">
              <!-- Sticky Left Anchored Cell Element -->
              <td class="sticky left-0 bg-white font-bold text-gray-900 px-4 py-3 border-r border-gray-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] z-20 flex items-center justify-between">
                <span>Hadley, Gloria</span>
                <button class="text-gray-400 hover:text-emerald-600 ml-1"><i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></button>
              </td>
              <td class="px-4 py-3 border-r border-gray-200 text-gray-500">9504</td>
              <td class="px-4 py-3 border-r border-gray-200 text-gray-900">Hadley, Albert</td>
              <td class="px-4 py-3 border-r border-gray-200 text-gray-500">9505</td>
              <td class="px-4 py-3 border-r border-gray-200">8 Mile</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
            </tr>

            <!-- Row Sample 2: Harper, Christin -->
            <tr class="hover:bg-gray-50/80 transition">
              <!-- Sticky Left Anchored Cell Element -->
              <td class="sticky left-0 bg-white font-bold text-gray-900 px-4 py-3 border-r border-gray-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] z-20 flex items-center justify-between">
                <span>Harper, Christin</span>
                <button class="text-gray-400 hover:text-emerald-600 ml-1"><i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></button>
              </td>
              <td class="px-4 py-3 border-r border-gray-200 text-gray-500">18303</td>
              <td class="px-4 py-3 border-r border-gray-200 text-gray-900 truncate max-w-[220px]">Bryant, Carmen | Bryant, Deante | Harper, Christin</td>
              <td class="px-4 py-3 border-r border-gray-200 text-gray-500">18303,18304...</td>
              <td class="px-4 py-3 border-r border-gray-200">8 Mile</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
            </tr>

            <!-- Row Sample 3: Hill, Raheem -->
            <tr class="hover:bg-gray-50/80 transition">
              <!-- Sticky Left Anchored Cell Element -->
              <td class="sticky left-0 bg-white font-bold text-gray-900 px-4 py-3 border-r border-gray-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] z-20 flex items-center justify-between">
                <span>Hill, Raheem</span>
                <button class="text-gray-400 hover:text-emerald-600 ml-1"><i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></button>
              </td>
              <td class="px-4 py-3 border-r border-gray-200 text-gray-500">14218</td>
              <td class="px-4 py-3 border-r border-gray-200 text-gray-900">Hill, Raheem</td>
              <td class="px-4 py-3 border-r border-gray-200 text-gray-500">14218</td>
              <td class="px-4 py-3 border-r border-gray-200">8 Mile</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 border-r border-gray-200 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
              <td class="px-4 py-3 bg-emerald-50/40 text-emerald-700 font-semibold">$ 0</td>
            </tr>

            <!-- Grand Summary Calculation Row -->
            <tr class="bg-gray-100 font-bold text-gray-900 shadow-inner">
              <td class="sticky left-0 bg-gray-100 px-4 py-3.5 border-r border-gray-300 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] z-20 text-right">
                Total:
              </td>
              <td class="border-r border-gray-200"></td>
              <td class="border-r border-gray-200"></td>
              <td class="border-r border-gray-200"></td>
              <td class="border-r border-gray-200"></td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-900">$ 0</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-900">$ 0</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-900">$ 0</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-900">$ 0</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-900">$ 0</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-900">$ 0</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-900">$ 0</td>
              <td class="px-4 py-3.5 text-gray-900">$ 0</td>
            </tr>
          </tbody>

        </table>
      </div>

      <!-- Table Pagination Layout Footer -->
      <div class="p-4 border-t border-gray-200 bg-gray-50 flex flex-wrap items-center justify-between gap-4 text-xs font-medium text-gray-500 mt-2">
        <div class="flex items-center gap-2">
          <span>Items per page:</span>
          <select class="bg-white border border-gray-300 rounded px-1.5 py-1 text-xs font-medium focus:outline-none shadow-xs">
            <option>20</option>
            <option>50</option>
          </select>
          <span class="ml-2">141-160 of 419 items</span>
        </div>
        
        <div class="flex items-center gap-1">
          <select class="bg-white border border-gray-300 rounded px-1.5 py-1 text-xs font-medium focus:outline-none shadow-xs mr-2">
            <option>8</option>
          </select>
          <span>of 21 pages</span>
          <button class="p-1 px-2.5 border border-gray-300 rounded-md bg-white hover:bg-gray-50 text-gray-600 ml-2 shadow-xs transition-colors"><i class="fa-solid fa-chevron-left text-[10px]"></i></button>
          <button class="p-1 px-2.5 border border-gray-300 rounded-md bg-white hover:bg-gray-50 text-gray-600 shadow-xs transition-colors"><i class="fa-solid fa-chevron-right text-[10px]"></i></button>
        </div>
      </div>

    </div>
  </main>

</x-app-layout>