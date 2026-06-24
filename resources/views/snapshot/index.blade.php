<x-app-layout>
    <header class="bg-white border-b border-gray-100 px-8 py-4 flex justify-between items-center">
    <div class="flex items-center space-x-2">
      <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Snapshot</h1>
    </div>
    <a href="#" class="flex items-center bg-[#002b24] text-emerald-400 font-semibold px-4 py-2 rounded-full text-sm hover:opacity-90 transition">
      View
    </a>
  </header>

  <section class="bg-white border-b border-gray-200 px-8 py-4 flex flex-wrap items-center justify-between gap-4">
    <div class="flex flex-wrap items-center gap-3">
      <div class="relative flex items-center border border-gray-300 rounded px-3 py-1.5 bg-white shadow-sm">
        <i class="fa-regular fa-calendar text-gray-400 mr-2 text-sm"></i>
        <span class="text-sm font-medium text-gray-700">Jun 01, 2026 - Jun 24, 2026</span>
      </div>

      <select class="border border-gray-300 rounded px-4 py-1.5 text-sm bg-white focus:outline-none focus:border-emerald-500 shadow-sm font-medium text-gray-700">
        <option selected>8 Mile</option>
        <option>Detroit Main</option>
      </select>

      <select class="border border-gray-300 rounded px-4 py-1.5 text-sm bg-white focus:outline-none focus:border-emerald-500 shadow-sm font-medium text-gray-700">
        <option selected>Detroit Dental Specialist</option>
        <option>All Brands</option>
      </select>

      <button class="bg-white border border-emerald-500 text-emerald-600 px-5 py-1.5 rounded text-sm font-semibold hover:bg-emerald-50 transition shadow-sm">
        Refresh
      </button>
    </div>

    <div class="text-xs text-blue-600 bg-blue-50 border border-blue-200 rounded-md px-3 py-1.5 flex items-center">
      <i class="fa-solid fa-circle-info mr-2 animate-pulse"></i>
      <span>You can add/edit additional views here in the Snapshot <a href="#" class="underline font-bold text-blue-700">Configuration Settings</a>. <span class="ml-1 bg-red-100 text-red-700 font-bold px-1 py-0.5 rounded text-[10px]">New</span></span>
    </div>
  </section>

  <section class="px-8 pt-4 bg-white border-b border-gray-200">
    <div class="flex space-x-6">
      <button class="border-b-2 border-emerald-500 text-emerald-600 font-bold text-sm pb-3">Default</button>
    </div>
  </section>

  <main class="p-6">
    <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden p-4">
      
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-1">
          <span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1 rounded">Top 20%</span>
          <span class="bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-1 rounded">Mid Tier</span>
          <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-1 rounded">Bottom 20%</span>
        </div>

        <div class="flex items-center gap-2">
          <div class="relative">
            <input type="text" placeholder="Search..." class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:border-emerald-500 pr-8">
            <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2 text-gray-400 text-xs"></i>
          </div>
          <button class="border border-emerald-500 text-emerald-600 font-semibold px-4 py-1 rounded text-sm hover:bg-emerald-50 transition">
            Export CSV
          </button>
        </div>
      </div>

      <div class="w-full overflow-x-auto max-h-[500px] overflow-y-auto border border-gray-200 rounded-lg">
        <table class="w-full text-left border-collapse min-w-[2400px]">
          
          <thead class="sticky top-0 z-30 bg-gray-200 text-gray-700 text-xs font-bold uppercase tracking-wider shadow-sm">
            <tr>
              <th scope="col" class="sticky left-0 bg-gray-200 px-4 py-3 border-r border-gray-300 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] z-40 text-gray-900 font-extrabold min-w-[140px]">
                Location
              </th>
              
              <th scope="col" class="px-4 py-3 border-r border-gray-300 text-emerald-900 bg-emerald-100/50">Total Collections</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300 text-emerald-900 bg-emerald-100/50">Collection Rate</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300 text-emerald-900 bg-emerald-100/50">Patient Collections</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300">Production Per Patient</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300">Production Per Hour</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300">Production Per Day</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300">Booked/Net Production</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300">Invisalign Production</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300">Gross Production Goal</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300">Adjustment</th>
              
              <th scope="col" class="px-4 py-3 border-r border-gray-300 bg-blue-50 text-blue-900">Total Appointments</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300 bg-blue-50 text-blue-900">Patient Visits</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300 bg-blue-50 text-blue-900">New Patient Visits</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300">New Patient Retention</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300">Retention Rate</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300 text-red-900 bg-red-50">Cancellation Rate</th>
              
              <th scope="col" class="px-4 py-3 border-r border-gray-300 bg-amber-50 text-amber-900">Patient Total AR</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300 bg-amber-50 text-amber-900">Patient Current</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300 bg-amber-50 text-amber-900">Patient Over 30 Days</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300 bg-amber-50 text-amber-900">Patient Over 60 Days</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300 bg-amber-50 text-amber-900">Patient Over 90 Days</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300">Tx Plan Acceptance Rate</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300">Tx Plan Value Accepted</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300">Hyg. Reappointment Rate</th>
              <th scope="col" class="px-4 py-3 border-r border-gray-300">Negative Balances</th>
              <th scope="col" class="px-4 py-3">Days Open</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-200 text-sm font-medium text-gray-600">
            
            <tr class="hover:bg-gray-50/80 transition">
              <td class="sticky left-0 bg-white font-bold text-gray-900 px-4 py-3.5 border-r border-gray-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] z-20">
                8 Mile
              </td>
              
              <td class="px-4 py-3.5 border-r border-gray-200 text-emerald-700 bg-emerald-50/30">$55,547.56</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-emerald-700 bg-emerald-50/30">168.99%</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-emerald-700 bg-emerald-50/30">$55,547.56</td>
              <td class="px-4 py-3.5 border-r border-gray-200">$228.27</td>
              <td class="px-4 py-3.5 border-r border-gray-200">$821.75</td>
              <td class="px-4 py-3.5 border-r border-gray-200">$4,695.74</td>
              <td class="px-4 py-3.5 border-r border-gray-200 font-semibold text-gray-900">$32,870.17</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-teal-600">$33,675.15</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-gray-500">$109,285.71</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-red-600 font-semibold">-$4,194.83</td>
              
              <td class="px-4 py-3.5 border-r border-gray-200 bg-blue-50/20 text-blue-800 font-semibold">144</td>
              <td class="px-4 py-3.5 border-r border-gray-200 bg-blue-50/20 text-blue-800">161</td>
              <td class="px-4 py-3.5 border-r border-gray-200 bg-blue-50/20 text-blue-800 font-bold">32</td>
              <td class="px-4 py-3.5 border-r border-gray-200">25</td>
              <td class="px-4 py-3.5 border-r border-gray-200">13.95%</td>
              <td class="px-4 py-3.5 border-r border-gray-200 bg-red-50/30 text-red-700 font-semibold">51.16%</td>
              
              <td class="px-4 py-3.5 border-r border-gray-200 bg-amber-50/20 text-amber-900">$447,508.65</td>
              <td class="px-4 py-3.5 border-r border-gray-200 bg-amber-50/20 text-gray-600">$13,901.84</td>
              <td class="px-4 py-3.5 border-r border-gray-200 bg-amber-50/20 text-gray-600">$7,635.22</td>
              <td class="px-4 py-3.5 border-r border-gray-200 bg-amber-50/20 text-gray-600">$11,607.39</td>
              <td class="px-4 py-3.5 border-r border-gray-200 bg-amber-50/20 text-red-600 font-bold">$446,343.51</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-emerald-600">86.04%</td>
              <td class="px-4 py-3.5 border-r border-gray-200">$32,915</td>
              <td class="px-4 py-3.5 border-r border-gray-200">74.45%</td>
              <td class="px-4 py-3.5 border-r border-gray-200 text-purple-600">-$31,979.31</td>
              <td class="px-4 py-3.5 font-bold text-gray-900">17 Days</td>
            </tr>

            <tr class="bg-gray-100 font-bold text-gray-900 shadow-inner">
              <td class="sticky left-0 bg-gray-100 px-4 py-3.5 border-r border-gray-300 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] z-20">
                Total Summary
              </td>
              <td class="px-4 py-3.5 border-r border-gray-300 text-emerald-800">$55,547.56</td>
              <td class="px-4 py-3.5 border-r border-gray-300 text-emerald-800">168.99%</td>
              <td class="px-4 py-3.5 border-r border-gray-300 text-emerald-800">$55,547.56</td>
              <td class="px-4 py-3.5 border-r border-gray-300">$228.27</td>
              <td class="px-4 py-3.5 border-r border-gray-300">$821.75</td>
              <td class="px-4 py-3.5 border-r border-gray-300">$4,695.74</td>
              <td class="px-4 py-3.5 border-r border-gray-300">$32,870.17</td>
              <td class="px-4 py-3.5 border-r border-gray-300 text-teal-700">$33,675.15</td>
              <td class="px-4 py-3.5 border-r border-gray-300">$109,285.71</td>
              <td class="px-4 py-3.5 border-r border-gray-300 text-red-700">-$4,194.83</td>
              <td class="px-4 py-3.5 border-r border-gray-300 text-blue-900">144</td>
              <td class="px-4 py-3.5 border-r border-gray-300 text-blue-900">161</td>
              <td class="px-4 py-3.5 border-r border-gray-300 text-blue-900">32</td>
              <td class="px-4 py-3.5 border-r border-gray-300">25</td>
              <td class="px-4 py-3.5 border-r border-gray-300">13.95%</td>
              <td class="px-4 py-3.5 border-r border-gray-300 text-red-700">51.16%</td>
              <td class="px-4 py-3.5 border-r border-gray-300 text-amber-900">$447,508.65</td>
              <td class="px-4 py-3.5 border-r border-gray-300">$13,901.84</td>
              <td class="px-4 py-3.5 border-r border-gray-300">$7,635.22</td>
              <td class="px-4 py-3.5 border-r border-gray-300">$11,607.39</td>
              <td class="px-4 py-3.5 border-r border-gray-300 text-red-700">$446,343.51</td>
              <td class="px-4 py-3.5 border-r border-gray-300 text-emerald-700">86.04%</td>
              <td class="px-4 py-3.5 border-r border-gray-300">$32,915</td>
              <td class="px-4 py-3.5 border-r border-gray-300">74.45%</td>
              <td class="px-4 py-3.5 border-r border-gray-300 text-purple-700">-$31,979.31</td>
              <td class="px-4 py-3.5 font-extrabold">17 Days</td>
            </tr>

          </tbody>
        </table>
      </div>

    </div>
  </main>
</x-app-layout>