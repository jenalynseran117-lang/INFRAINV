@extends('layouts.Supply.app')

@section('content')

<div class="mb-8 flex items-center justify-between">
  <div>
    <h1 class="text-3xl font-bold text-slate-800"> Inspection Queue</h1>
    <p class="text-sm text-slate-500 mt-1">Upload actual delivery pictures and Delivery Receipts (DR) for each item here before submitting to inspection.</p>
  </div>
</div>

@if(session('success'))
<div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-medium">
  {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm font-medium">
  {{ session('error') }}
</div>
@endif

{{-- ============================= --}}
{{-- 1. PO LIST VIEW --}}
{{-- ============================= --}}
<div id="po-list-view">

  <div class="mb-5 relative">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
    </svg>
    <input type="text" id="po-search" oninput="filterPOs()" placeholder="Search PO number..."
      class="w-full pl-11 pr-4 py-3 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
  </div>

  <div id="po-card-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($deliveryQueues as $po)
      @php
        $poItems = is_array($po->items) ? $po->items : json_decode($po->items ?? '[]', true);
        $totalQuantityNeeded = array_reduce($poItems, function($sum, $i) {
            return $sum + (int)($i['quantity'] ?? 0);
        }, 0);
        $submittedCount = collect($poItems)->filter(function ($i) {
            $need = (int) ($i['quantity'] ?? 0);
            $delivered = collect($i['deliveries'] ?? [])->sum('quantity');
            return $need > 0 && $delivered >= $need;
        })->count();
      @endphp

      <button type="button" onclick="openPO('{{ $po->id }}')" data-po-number="{{ strtolower($po->po_number) }}"
        class="po-card text-left bg-white border border-slate-200 shadow-sm rounded-2xl p-5 transition-all hover:shadow-md hover:border-purple-300 group">
        <div class="flex items-center justify-between mb-3">
          <span class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-bold rounded-lg tracking-wider uppercase">
            PO #: {{ $po->po_number }}
          </span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-300 group-hover:text-purple-500 group-hover:translate-x-0.5 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </div>
        <p class="text-xs text-slate-500 font-medium mb-1">
          Supplier: <span class="font-bold text-slate-700">{{ $po->supplier ?? 'N/A' }}</span>
        </p>
        <p class="text-xs text-slate-500 font-medium mb-4">
          Date Created: {{ \Carbon\Carbon::parse($po->po_date)->format('M d, Y') }}
        </p>
        <div class="flex items-center gap-2 flex-wrap">
          <span class="text-xs font-bold text-purple-700 bg-purple-50 border border-purple-200 px-3 py-1 rounded-full">
            {{ $totalQuantityNeeded }} units needed
          </span>
          <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
            Items: {{ count($poItems) }}
          </span>
          @if($submittedCount > 0)
          <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
            {{ $submittedCount }}/{{ count($poItems) }} submitted
          </span>
          @endif
        </div>
      </button>
    @empty
      <div class="col-span-full flex flex-col items-center justify-center py-20 bg-white border border-dashed border-slate-200 rounded-2xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <h3 class="text-base font-bold text-slate-700">No items awaiting delivery upload</h3>
        <p class="text-xs text-slate-400 mt-1">When you complete transactions in procurement, pending items will register here.</p>
      </div>
    @endforelse
  </div>

  <p id="po-no-results" class="hidden text-center text-sm text-slate-400 font-medium py-16">No matching PO found.</p>
</div>

{{-- ============================= --}}
{{-- 2. PO DETAIL VIEWS (one per PO, hidden until opened) --}}
{{-- ============================= --}}
@foreach($deliveryQueues as $po)
  @php
    $poItems = is_array($po->items) ? $po->items : json_decode($po->items ?? '[]', true);
    $totalQuantityNeeded = array_reduce($poItems, function($sum, $i) {
        return $sum + (int)($i['quantity'] ?? 0);
    }, 0);
  @endphp

  <div id="po-detail-{{ $po->id }}" class="po-detail hidden">

    <div class="flex items-center justify-between pb-4 mb-5">
      <div class="flex items-center gap-3">
        <button type="button" onclick="backToList()" class="p-2 bg-slate-100 hover:bg-purple-600 hover:text-white rounded-xl transition text-slate-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <span class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-bold rounded-lg tracking-wider uppercase">
          PO #: {{ $po->po_number }}
        </span>
        <span class="text-xs text-slate-500 font-medium">
          Supplier: <span class="font-bold text-slate-700">{{ $po->supplier ?? 'N/A' }}</span>
        </span>
        <span class="text-xs text-slate-500 font-medium">
          Date Created: {{ \Carbon\Carbon::parse($po->po_date)->format('M d, Y') }}
        </span>
      </div>

      <div class="flex items-center gap-2">
        <span class="text-xs font-bold text-purple-700 bg-purple-50 border border-purple-200 px-3 py-1 rounded-full">
          Total Needed for Inspection: {{ $totalQuantityNeeded }} units
        </span>
        <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
          Items: {{ count($poItems) }}
        </span>
      </div>
    </div>

    <div class="mb-5 relative">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
      </svg>
      <input type="text" oninput="filterItems('{{ $po->id }}', this.value)" placeholder="Search item description or stock no..."
        class="w-full pl-11 pr-4 py-3 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
    </div>

    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
      <div class="space-y-4" id="item-list-{{ $po->id }}">
        @forelse($poItems as $index => $item)
          @php
            $itemQty = (int)($item['quantity'] ?? 0);
            $unitCost = (float)($item['unit_cost'] ?? 0);
            $totalCost = (float)($item['amount'] ?? 0);
            $stockNo = $item['stock_no'] ?? ($index + 1);
            $description = $item['description'] ?? 'No description';

            $deliveries = $item['deliveries'] ?? [];
            $deliveredQty = collect($deliveries)->sum('quantity');
            $remainingQty = max(0, $itemQty - $deliveredQty);
            $isSubmitted = $remainingQty <= 0 && $deliveredQty > 0;
          @endphp

          <div class="item-row bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4"
            data-description="{{ strtolower($description) }}" data-stock="{{ strtolower($stockNo) }}">

            <div class="space-y-2 lg:w-1/2">
              <div class="flex items-center gap-2">
                <h4 class="text-base font-bold text-slate-800">
                  Item #{{ $index + 1 }}: <span class="text-slate-700">{{ $description }}</span>
                </h4>
              </div>

              <div class="flex items-center gap-2 flex-wrap">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-xs font-bold">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                  Needed: <span class="text-amber-900 font-extrabold">{{ $itemQty }} {{ $itemQty == 1 ? 'unit' : 'units' }}</span>
                </div>

                @if($deliveredQty > 0)
                <div class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800 text-xs font-bold">
                  Delivered: {{ $deliveredQty }}/{{ $itemQty }}
                </div>
                @endif

                @if($remainingQty > 0 && $deliveredQty > 0)
                <div class="inline-flex items-center gap-1 px-3 py-1 bg-rose-50 border border-rose-200 rounded-lg text-rose-700 text-xs font-bold">
                  Pending: {{ $remainingQty }}
                </div>
                @endif
              </div>

              <div class="text-xs text-slate-500 space-y-1 pt-1">
                <p><strong>Stock No:</strong> {{ $stockNo }}</p>
                <p>
                  <strong>Unit Cost:</strong> ₱{{ number_format($unitCost, 2) }} |
                  <strong>Total Value:</strong> ₱{{ number_format($totalCost, 2) }}
                </p>
              </div>

              @if(count($deliveries) > 0)
              <div class="pt-2 space-y-1">
                <p class="text-[11px] font-black text-slate-500 uppercase tracking-wide">Delivery history</p>
                @foreach($deliveries as $d)
                <div class="flex items-center gap-2 text-[11px] text-slate-500 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="font-bold text-slate-700">{{ $d['quantity'] ?? 0 }} {{ ($d['quantity'] ?? 0) == 1 ? 'unit' : 'units' }}</span>
                  <span>received</span>
                  @if(!empty($d['submitted_at']))
                  <span>&middot; {{ \Carbon\Carbon::parse($d['submitted_at'])->timezone('Asia/Manila')->format('M d, Y g:i A') }}</span>
                  @endif
                </div>
                @endforeach
              </div>
              @endif
            </div>

            <div class="lg:w-96 bg-white border border-slate-200 rounded-lg p-3 shadow-xs">
              @if($isSubmitted)
                <div class="flex flex-col items-center justify-center py-4 gap-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="text-xs font-bold text-emerald-700 uppercase tracking-wide">Fully Delivered</span>
                  <span class="text-[11px] text-slate-400">Inspected Qty: {{ $deliveredQty }}/{{ $itemQty }}</span>
                </div>
              @else
                <form action="{{ route('supply.uploadDeliverySpecs') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                  @csrf
                  <input type="hidden" name="po_id" value="{{ $po->id }}">
                  <input type="hidden" name="item_index" value="{{ $index }}">

                  @if($deliveredQty > 0)
                  <p class="text-[11px] font-bold text-rose-600 bg-rose-50 border border-rose-200 rounded-lg px-2.5 py-1.5">
                    {{ $remainingQty }} of {{ $itemQty }} unit(s) still pending delivery.
                  </p>
                  @endif

                  <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1 tracking-wide">
                      {{ $deliveredQty > 0 ? 'Quantity Received (this batch)' : 'Inspected Quantity' }}
                    </label>
                    <input type="number" name="inspected_quantity" value="{{ $remainingQty }}" min="1" max="{{ $remainingQty }}" required
                      class="block w-full text-xs text-slate-800 border border-slate-200 rounded-lg bg-slate-50 p-2 font-bold mb-2">
                  </div>

                  <div>
                    <label for="file_{{ $po->id }}_{{ $index }}" class="block text-xs font-bold text-slate-600 uppercase mb-1 tracking-wide">
                      Delivery Photo / DR
                    </label>
                    <input type="file" name="delivery_photo" id="file_{{ $po->id }}_{{ $index }}" required
                      class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 transition-all cursor-pointer border border-slate-200 rounded-lg bg-slate-50 p-1">
                  </div>

                  <button type="submit" class="w-full py-2 bg-purple-600 text-white font-semibold text-xs rounded-lg hover:bg-purple-700 transition-colors shadow-xs uppercase tracking-wider">
                    Submit ({{ $remainingQty }} {{ $remainingQty == 1 ? 'unit' : 'units' }})
                  </button>
                </form>
              @endif
            </div>

          </div>
        @empty
          <p class="text-xs text-slate-400 italic">No items found in this purchase order.</p>
        @endforelse
      </div>

      <p class="item-no-results hidden text-center text-sm text-slate-400 font-medium py-10">No matching item found.</p>
    </div>
  </div>
@endforeach

<script>
  function openPO(id) {
    document.getElementById('po-list-view').classList.add('hidden');
    document.querySelectorAll('.po-detail').forEach(el => el.classList.add('hidden'));
    const target = document.getElementById('po-detail-' + id);
    if (target) target.classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function backToList() {
    document.querySelectorAll('.po-detail').forEach(el => el.classList.add('hidden'));
    document.getElementById('po-list-view').classList.remove('hidden');
  }

  function filterPOs() {
    const query = document.getElementById('po-search').value.trim().toLowerCase();
    const cards = document.querySelectorAll('.po-card');
    let visibleCount = 0;

    cards.forEach(card => {
      const match = card.dataset.poNumber.includes(query);
      card.classList.toggle('hidden', !match);
      if (match) visibleCount++;
    });

    document.getElementById('po-no-results').classList.toggle('hidden', visibleCount !== 0);
  }

  function filterItems(poId, value) {
    const query = value.trim().toLowerCase();
    const container = document.getElementById('item-list-' + poId);
    const rows = container.querySelectorAll('.item-row');
    const noResults = container.parentElement.querySelector('.item-no-results');
    let visibleCount = 0;

    rows.forEach(row => {
      const match = row.dataset.description.includes(query) || row.dataset.stock.includes(query);
      row.classList.toggle('hidden', !match);
      if (match) visibleCount++;
    });

    if (noResults) noResults.classList.toggle('hidden', visibleCount !== 0 || rows.length === 0);
  }
</script>

@endsection