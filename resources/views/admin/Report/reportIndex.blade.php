@extends('layouts.Admin.app')

@section('content')
@php
  // Items Received / Items Distributed are always populated now — they
  // default to the current week (see resolveReportRange()) until the user
  // submits a valid custom 7-day range. $isCustomRange and $rangeWasRejected
  // come straight from the controller, since only it knows whether the
  // request's dates were accepted, rejected, or absent.
  $reportGenerated = $isCustomRange;
@endphp

{{--
    Print support: everything except #printArea is hidden on @media print.
--}}
<style>
    #printArea { position: fixed; top: 0; left: 0; width: 0; height: 0; overflow: hidden; }
</style>
<style media="print">
    body * { visibility: hidden; }
    #printArea, #printArea * { visibility: visible; }
    #printArea {
        display: block !important;
        position: absolute; top: 0; left: 0;
        width: 100%; height: auto; overflow: visible;
        padding: 24px;
    }
    #printArea table { width: 100%; border-collapse: collapse; font-size: 13px; }
    #printArea th, #printArea td { border: 1px solid #ccc; padding: 8px 10px; text-align: left; }
    #printArea th { background: #f3f4f6; }
</style>

<div id="printArea"></div>

<script>
    function printReport() {
        const html = `
            <h2 style="font-size:18px;font-weight:700;margin-bottom:2px;">Weekly Audit Trail</h2>
            <p style="font-size:11px;color:#666;margin-bottom:16px;">
                Range: {{ $rangeStart->format('M d, Y') }} - {{ $rangeEnd->format('M d, Y') }}
                &middot; Printed {{ now()->format('M d, Y g:i A') }}
            </p>
            <table>
                <tbody>
                    <tr><th>Items Received</th><td>+{{ number_format($itemsReceived) }} items</td></tr>
                    <tr><th>Items Distributed</th><td>-{{ number_format($itemsDistributed) }} items</td></tr>
                    <tr><th>Total Items in Inventory</th><td>{{ number_format($totalInventory) }} items</td></tr>
                </tbody>
            </table>

            @if (!empty($receivedBreakdown))
            <h3 style="font-size:14px;font-weight:700;margin:20px 0 8px;">Items Received — Breakdown</h3>
            <table>
                <thead><tr><th>Item</th><th>Quantity</th></tr></thead>
                <tbody>
                    @foreach ($receivedBreakdown as $row)
                    <tr><td>{{ $row['name'] }}</td><td>{{ number_format($row['quantity']) }} pcs</td></tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            @if (!empty($distributedBreakdown))
            <h3 style="font-size:14px;font-weight:700;margin:20px 0 8px;">Items Distributed — Breakdown</h3>
            <table>
                <thead><tr><th>Item</th><th>Quantity</th></tr></thead>
                <tbody>
                    @foreach ($distributedBreakdown as $row)
                    <tr><td>{{ $row['name'] }}</td><td>{{ number_format($row['quantity']) }} pcs</td></tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            @if (!empty($inventoryBreakdown))
            <h3 style="font-size:14px;font-weight:700;margin:20px 0 8px;">Total Items in Inventory — Breakdown</h3>
            <table>
                <thead><tr><th>Item</th><th>Quantity</th></tr></thead>
                <tbody>
                    @foreach ($inventoryBreakdown as $row)
                    <tr><td>{{ $row['name'] }}</td><td>{{ number_format($row['quantity']) }} pcs</td></tr>
                    @endforeach
                </tbody>
            </table>
            @endif`;
        document.getElementById('printArea').innerHTML = html;
        requestAnimationFrame(() => requestAnimationFrame(() => window.print()));
    }
</script>

<div class="p-6 bg-gray-50 min-h-screen font-sans">

  <div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Reports</h1>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- DYNAMIC COLOR SHIFT: Magiging bg-emerald-50 at border-emerald-200 ang card kapag may generated report na --}}
    <div x-data="{ 
        startDate: '{{ request('start_date') }}', 
        endDate: '{{ request('end_date') }}', 
        showPopup: false, 
        popupMessage: '',
        validateAndSubmit(e) {
            if (!this.startDate || !this.endDate) {
                e.preventDefault();
                this.popupMessage = 'Please select the correct Start Date and End Date.';
                this.showPopup = true;
                return;
            }

            const today = new Date();
            today.setHours(23, 59, 59, 999);
            const start = new Date(this.startDate);
            const end = new Date(this.endDate);

            if (start > today || end > today) {
                e.preventDefault();
                this.popupMessage = "You can't generate a report for dates beyond today.";
                this.showPopup = true;
                return;
            }

            const differenceInTime = end.getTime() - start.getTime();
            const differenceInDays = Math.ceil(differenceInTime / (1000 * 3600 * 24)) + 1; 

            if (differenceInDays !== 7) {
                e.preventDefault(); 
                this.popupMessage = `The date range you selected is ${differenceInDays < 0 ? 0 : differenceInDays} days. It must be exactly 7 days for the Weekly Audit (e.g., Sunday to Saturday).`;
                this.showPopup = true;
            }
        }
    }" class="lg:col-span-1 rounded-2xl shadow-sm p-6 self-start transition-all duration-300 {{ $reportGenerated ? 'bg-emerald-50/60 border border-emerald-200' : 'bg-white border border-gray-100' }}">
      
      <h2 class="text-lg font-bold text-gray-900 mb-6">Generate Report</h2>

      <form action="{{ route('admin.Report') }}" method="GET" @submit="validateAndSubmit($event)">
        <div class="mb-6">
          <label class="block text-sm font-semibold text-gray-700 mb-3">Date Range</label>
          <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative w-full">
              <input type="date" id="start_date" name="start_date" x-model="startDate" max="{{ now()->toDateString() }}" class="w-full border border-gray-200 rounded-xl p-3 text-sm text-gray-600 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none transition-all">
            </div>
            <div class="relative w-full">
              <input type="date" id="end_date" name="end_date" x-model="endDate" max="{{ now()->toDateString() }}" class="w-full border border-gray-200 rounded-xl p-3 text-sm text-gray-600 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none transition-all">
            </div>
          </div>
        </div>

        @if ($rangeWasRejected)
          <p class="text-xs text-red-500 font-medium mb-4">
            That range ({{ request('start_date') }} to {{ request('end_date') }}) couldn't be generated — it extends beyond today, or the dates are invalid.
          </p>
        @endif

        {{-- DYNAMIC BUTTON COLOR: Mag-g-green (bg-emerald-600) kapag may totoong nabuong report --}}
        <button type="submit" class="w-full font-bold py-3.5 rounded-xl transition-all shadow-lg active:scale-95 {{ $reportGenerated ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-[#050510] text-white hover:bg-gray-800' }}">
          {{ $reportGenerated ? 'Report Generated ✓' : 'Generate Report' }}
        </button>
      </form>

      <button type="button" onclick="printReport()"
              class="w-full mt-3 inline-flex items-center justify-center gap-2 py-2.5 px-4 border border-emerald-200 bg-white hover:bg-emerald-50 text-emerald-700 text-sm font-semibold rounded-xl transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
        </svg>
        Print Report
      </button>

      {{-- POP-UP NOTIFICATION MODAL --}}
      <div x-show="showPopup" 
           x-transition:enter="transition ease-out duration-300"
           x-transition:enter-start="opacity-0 scale-90"
           x-transition:enter-end="opacity-100 scale-100"
           x-transition:leave="transition ease-in duration-200"
           x-transition:leave-start="opacity-100 scale-100"
           x-transition:leave-end="opacity-0 scale-90"
           class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm" 
           x-cloak>
          
          <div class="bg-white p-6 rounded-2xl shadow-xl border border-slate-100 w-full max-w-sm mx-4 text-center">
              <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                  <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
              </div>

              <h3 class="text-base font-bold text-slate-800 mb-2">Wrong Date Range</h3>
              <p class="text-xs text-slate-500 leading-relaxed mb-5" x-text="popupMessage"></p>
              
              <button type="button" @click="showPopup = false" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition-all">
                  OK
              </button>
          </div>
      </div>

    </div>

    <div class="lg:col-span-2 bg-[#f0faf4] rounded-2xl border border-emerald-100 p-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-2">
        <h2 class="text-xl font-bold text-gray-900">Weekly Audit Trail</h2>
        @if ($reportGenerated)
          <span class="text-xs font-bold px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full border border-emerald-200">
            Filtered: {{ $rangeStart->format('M d, Y') }} - {{ $rangeEnd->format('M d, Y') }}
          </span>
        @elseif ($rangeWasRejected)
          <span class="text-xs font-bold px-3 py-1 bg-red-50 text-red-600 rounded-full border border-red-200">
            Invalid range requested — showing current week instead
          </span>
        @else
          <span class="text-xs font-bold px-3 py-1 bg-gray-100 text-gray-600 rounded-full border border-gray-200">
            Current week: {{ $rangeStart->format('M d, Y') }} - {{ $rangeEnd->format('M d, Y') }}
          </span>
        @endif
      </div>

      <div class="space-y-4">
        <div class="bg-white rounded-2xl px-6 py-5 shadow-sm border border-emerald-50/50">
          <div class="flex justify-between items-center">
            <span class="text-gray-600 font-medium text-lg">Items Received:</span>
            <span class="font-bold text-emerald-600 text-lg">+{{ number_format($itemsReceived) }} items</span>
          </div>
          @if (!empty($receivedBreakdown))
            <ul class="mt-3 pt-3 border-t border-gray-100 space-y-1.5">
              @foreach ($receivedBreakdown as $row)
                <li class="flex justify-between items-center text-sm">
                  <span class="text-gray-500">{{ $row['name'] }}</span>
                  <span class="font-semibold text-gray-700">{{ number_format($row['quantity']) }} pcs</span>
                </li>
              @endforeach
            </ul>
          @endif
        </div>

        <div class="bg-white rounded-2xl px-6 py-5 shadow-sm border border-emerald-50/50">
          <div class="flex justify-between items-center">
            <span class="text-gray-600 font-medium text-lg">Items Distributed:</span>
            <span class="font-bold text-red-500 text-lg">-{{ number_format($itemsDistributed) }} items</span>
          </div>
          @if (!empty($distributedBreakdown))
            <ul class="mt-3 pt-3 border-t border-gray-100 space-y-1.5">
              @foreach ($distributedBreakdown as $row)
                <li class="flex justify-between items-center text-sm">
                  <span class="text-gray-500">{{ $row['name'] }}</span>
                  <span class="font-semibold text-gray-700">{{ number_format($row['quantity']) }} pcs</span>
                </li>
              @endforeach
            </ul>
          @endif
        </div>

        <div class="bg-white rounded-2xl px-6 py-5 shadow-sm border border-emerald-50/50">
          <div class="flex justify-between items-center">
            <span class="text-gray-600 font-medium text-lg">Total Items in Inventory:</span>
            <span class="font-bold text-gray-900 text-lg">{{ number_format($totalInventory) }} items</span>
          </div>
          @if (!empty($inventoryBreakdown))
            <ul class="mt-3 pt-3 border-t border-gray-100 space-y-1.5">
              @foreach ($inventoryBreakdown as $row)
                <li class="flex justify-between items-center text-sm">
                  <span class="text-gray-500">{{ $row['name'] }}</span>
                  <span class="font-semibold text-gray-700">{{ number_format($row['quantity']) }} pcs</span>
                </li>
              @endforeach
            </ul>
          @endif
        </div>

        @unless ($reportGenerated)
          <p class="text-xs text-gray-400 pt-2">Showing the current week by default. Select a valid 7-day date range (not beyond today) and click "Generate Report" to filter to a different week.</p>
        @endunless
      </div>
    </div>

  </div>
</div>
@endsection