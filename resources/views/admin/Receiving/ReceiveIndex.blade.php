@extends('layouts.Admin.app')

@section('content')

<style>
  @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&display=swap');
  .font-display { font-family: 'Space Grotesk', sans-serif; }
  .font-body { font-family: 'Inter', sans-serif; }

  @keyframes glass-drift {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(24px, -18px) scale(1.06); }
  }
  .glass-blob { animation: glass-drift 14s ease-in-out infinite; }

  @media (prefers-reduced-motion: reduce) {
    .glass-blob { animation: none; }
  }
</style>

<div class="relative w-full rounded-3xl overflow-hidden bg-gradient-to-br from-[#f7f5f2] via-[#f2eee8] to-[#eee7e0] p-6 sm:p-10 font-body">

  {{-- Ambient glow blobs --}}
  <div class="pointer-events-none absolute inset-0 overflow-hidden">
    <div class="glass-blob absolute -top-24 -left-16 h-80 w-80 rounded-full bg-orange-300/30 blur-3xl"></div>
    <div class="glass-blob absolute top-1/4 -right-10 h-96 w-96 rounded-full bg-rose-300/25 blur-3xl" style="animation-delay:-5s"></div>
    <div class="glass-blob absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-blue-200/30 blur-3xl" style="animation-delay:-9s"></div>
  </div>

  <div class="relative z-10">

    {{-- Header --}}
    <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
      <div>
        <h1 class="text-3xl font-bold text-gray-800 font-display tracking-tight">Receiving Portal</h1>
        <p class="text-sm text-gray-500 mt-1">Manage incoming materials and confirm delivery status</p>
      </div>
    </div>

    {{-- Summary Cards — Fusion-style vibrant gradient stat cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">

      {{-- For Receiving --}}
      <a href="{{ route('admin.Receiving', ['filter' => 'for_receiving', 'search' => $search]) }}"
        class="relative overflow-hidden rounded-2xl p-6
               bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-600
               shadow-lg shadow-blue-500/25 hover:shadow-xl hover:shadow-blue-500/40
               hover:-translate-y-1 transition-all duration-300 group
               {{ $filter === 'for_receiving' ? 'ring-4 ring-blue-200' : '' }}">

        <div class="absolute -top-8 -right-8 w-32 h-32 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-all duration-500"></div>

        <div class="relative flex items-start justify-between">
          <div>
            <p class="text-xs uppercase tracking-wider text-blue-100 font-bold">For Receiving</p>
            <h2 class="text-5xl font-bold text-white mt-2 font-display">{{ $forReceivingCount }}</h2>
            <p class="text-sm text-blue-100 mt-1.5 font-medium">POs awaiting delivery</p>
          </div>
          <div class="bg-white/15 backdrop-blur-md p-2.5 rounded-xl text-white ring-1 ring-white/25 group-hover:scale-110 transition-transform duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
          </div>
        </div>
      </a>

      {{-- Under Inspection --}}
      <a href="{{ route('admin.Receiving', ['filter' => 'under_inspection', 'search' => $search]) }}"
        class="relative overflow-hidden rounded-2xl p-6
               bg-gradient-to-br from-orange-400 via-orange-500 to-amber-600
               shadow-lg shadow-orange-500/25 hover:shadow-xl hover:shadow-orange-500/40
               hover:-translate-y-1 transition-all duration-300 group
               {{ $filter === 'under_inspection' ? 'ring-4 ring-orange-200' : '' }}">

        <div class="absolute -top-8 -right-8 w-32 h-32 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-all duration-500"></div>

        <div class="relative flex items-start justify-between">
          <div>
            <p class="text-xs uppercase tracking-wider text-orange-100 font-bold">Under Inspection</p>
            <h2 class="text-5xl font-bold text-white mt-2 font-display">{{ $underInspectionCount }}</h2>
            <p class="text-sm text-orange-100 mt-1.5 font-medium">Items being verified</p>
          </div>
          <div class="bg-white/15 backdrop-blur-md p-2.5 rounded-xl text-white ring-1 ring-white/25 group-hover:scale-110 transition-transform duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v3m0 0v3m0-3h3m-3 0h-3" />
            </svg>
          </div>
        </div>
      </a>

      {{-- Received --}}
      <a href="{{ route('admin.Receiving', ['filter' => 'received', 'search' => $search]) }}"
        class="relative overflow-hidden rounded-2xl p-6
               bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-600
               shadow-lg shadow-emerald-500/25 hover:shadow-xl hover:shadow-emerald-500/40
               hover:-translate-y-1 transition-all duration-300 group
               {{ $filter === 'received' ? 'ring-4 ring-emerald-200' : '' }}">

        <div class="absolute -top-8 -right-8 w-32 h-32 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-all duration-500"></div>

        <div class="relative flex items-start justify-between">
          <div>
            <p class="text-xs uppercase tracking-wider text-emerald-100 font-bold">Received</p>
            <h2 class="text-5xl font-bold text-white mt-2 font-display">{{ $receivedCount }}</h2>
            <p class="text-sm text-emerald-100 mt-1.5 font-medium">Successfully received</p>
          </div>
          <div class="bg-white/15 backdrop-blur-md p-2.5 rounded-xl text-white ring-1 ring-white/25 group-hover:scale-110 transition-transform duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </a>

    </div>

    {{-- Filter bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
      <div class="flex items-center gap-2">
        <h2 class="text-lg font-bold text-gray-800 font-display">
          @switch($filter)
            @case('for_receiving') For Receiving @break
            @case('under_inspection') Under Inspection @break
            @case('received') Received @break
            @default Un-Opened POs
          @endswitch
        </h2>
        @if($filter !== 'all')
          <a href="{{ route('admin.Receiving', ['search' => $search]) }}"
            class="text-xs font-semibold text-gray-400 hover:text-gray-600 underline transition-colors">
            Clear filter
          </a>
        @endif
      </div>

      <form method="GET" action="{{ route('admin.Receiving') }}" class="flex items-center gap-2">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <div class="relative">
          <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0a7.5 7.5 0 10-10.6 0 7.5 7.5 0 0010.6 0z" />
          </svg>
          <input
            type="text"
            name="search"
            value="{{ $search }}"
            placeholder="Search PO number, description, or stock no."
            class="w-full sm:w-80 pl-9 pr-4 py-2 text-sm bg-white/60 backdrop-blur-xl border border-white/70 rounded-xl text-gray-700 placeholder-gray-400 focus:ring-2 focus:ring-blue-300 focus:border-blue-300 focus:bg-white/90 outline-none transition-all">
        </div>
        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-orange-500 to-rose-500 hover:from-orange-400 hover:to-rose-400 rounded-xl shadow-lg shadow-orange-200 transition-all hover:-translate-y-0.5">
          Search
        </button>
        @if($search !== '')
          <a href="{{ route('admin.Receiving', ['filter' => $filter]) }}"
            class="text-xs font-semibold text-gray-400 hover:text-gray-600 underline whitespace-nowrap transition-colors">
            Clear search
          </a>
        @endif
      </form>
    </div>

    {{-- Receiving Table --}}
    <div class="bg-white/60 backdrop-blur-xl rounded-2xl border border-white/70 shadow-[0_8px_32px_rgba(31,41,55,0.08)] overflow-hidden">
      <table class="w-full text-sm text-left border-collapse">
        <thead>
          <tr class="bg-white/50 border-b border-gray-200/70 text-gray-500">
            <th class="py-4 px-6 font-semibold">PO Number</th>
            <th class="font-semibold px-2">Description</th>
            <th class="font-semibold px-2">Status</th>
            <th class="font-semibold px-2">Date</th>
            <th class="font-semibold px-2">Quantity</th>
            <th class="font-semibold px-2">Unit Cost</th>
            <th class="font-semibold px-2 pr-6">Total Cost</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200/60">
          @forelse($latestDeliveries as $delivery)
          <tr class="hover:bg-white/80 cursor-pointer transition-colors text-gray-700" onclick="openPoModal({{ $delivery->id }})">
            <td class="py-4 px-6 font-semibold text-gray-800">{{ $delivery->po_number }}</td>
            <td class="px-2">{{ Str::limit($delivery->description, 30) }}</td>
            <td class="px-2">
              @if($delivery->status === 'approved')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold text-emerald-700 bg-emerald-50 rounded-full">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Received
                </span>
              @elseif($delivery->status === 'rejected')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold text-red-700 bg-red-50 rounded-full">
                  <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rejected
                </span>
              @elseif($delivery->delivery_photo)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold text-orange-700 bg-orange-50 rounded-full">
                  <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Under Inspection
                </span>
              @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold text-blue-700 bg-blue-50 rounded-full">
                  <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> For Receiving
                </span>
              @endif
            </td>
            <td class="px-2">{{ \Carbon\Carbon::parse($delivery->po_date)->format('M d, Y') }}</td>
            <td class="px-2">{{ number_format($delivery->quantity, 0) }}</td>
            <td class="px-2">{{ number_format($delivery->unit_cost, 2) }}</td>
            <td class="px-2 pr-6 font-semibold text-gray-800">{{ number_format($delivery->total_cost, 2) }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="py-10 text-center text-gray-400 font-bold">
              No deliveries found{{ $search !== '' ? ' matching "' . $search . '"' : '' }}.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
</div>

{{-- PO Detail Modal --}}
<div id="poModalBackdrop" class="hidden fixed inset-0 bg-gray-500/30 backdrop-blur-sm z-40" onclick="closePoModal()"></div>

<div id="poModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
  <div class="bg-white/80 backdrop-blur-2xl rounded-2xl border border-white/70 shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-y-auto font-body" onclick="event.stopPropagation()">

    <div class="flex items-start justify-between p-6 border-b border-gray-200/70">
      <div>
        <p id="poModalNumber" class="text-xl font-bold text-gray-800 font-display"></p>
        <p id="poModalStatus" class="text-xs font-bold mt-1"></p>
      </div>
      <button onclick="closePoModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none transition-colors">&times;</button>
    </div>

    <div class="p-6 space-y-4">
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
          <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold">Stock No.</p>
          <p id="poModalStockNo" class="font-medium text-gray-800"></p>
        </div>
        <div>
          <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold">PO Date</p>
          <p id="poModalDate" class="font-medium text-gray-800"></p>
        </div>
        <div>
          <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold">Requested By</p>
          <p id="poModalRequestedBy" class="font-medium text-gray-800"></p>
        </div>
        <div>
          <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold">Total Cost</p>
          <p id="poModalTotalCost" class="font-medium text-gray-800"></p>
        </div>
      </div>

      <div>
        <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold mb-1">Description</p>
        <p id="poModalDescription" class="text-sm text-gray-600"></p>
      </div>

      <div>
        <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold mb-2">Items</p>
        <table class="w-full text-xs text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-200/70 text-gray-500">
              <th class="py-2 font-semibold">Description</th>
              <th class="font-semibold">Qty</th>
              <th class="font-semibold">Unit Cost</th>
            </tr>
          </thead>
          <tbody id="poModalItems" class="divide-y divide-gray-200/60 text-gray-700"></tbody>
        </table>
      </div>

      <div id="poModalAttachments" class="flex gap-3 pt-2"></div>
    </div>
  </div>
</div>

<script>
  const poData = {
    @foreach($latestDeliveries as $delivery)
    {{ $delivery->id }}: {
      po_number: @json($delivery->po_number),
      status: @json($delivery->status),
      stock_no: @json($delivery->stock_no ?? 'N/A'),
      po_date: @json(\Carbon\Carbon::parse($delivery->po_date)->format('M d, Y')),
      requested_by: @json(optional($delivery->user)->name ?? 'N/A'),
      description: @json($delivery->description ?? ''),
      total_cost: @json(number_format($delivery->total_cost, 2)),
      po_attachment: @json($delivery->po_attachment ? asset('storage/' . $delivery->po_attachment) : null),
      delivery_photo: @json($delivery->delivery_photo ? asset('storage/' . $delivery->delivery_photo) : null),
      items: @json(is_array($delivery->items) ? $delivery->items : (json_decode($delivery->items ?? '[]', true) ?: [])),
    },
    @endforeach
  };

  const STATUS_BADGES = {
    approved: { label: 'Received', class: 'text-emerald-700 bg-emerald-50' },
    rejected: { label: 'Rejected', class: 'text-red-700 bg-red-50' },
    under_inspection: { label: 'Under Inspection', class: 'text-orange-700 bg-orange-50' },
    for_receiving: { label: 'For Receiving', class: 'text-blue-700 bg-blue-50' },
  };

  function openPoModal(id) {
    const po = poData[id];
    if (!po) return;

    document.getElementById('poModalNumber').textContent = po.po_number;

    let bucket = po.status;
    if (po.status === 'pending_inspection') {
      bucket = po.delivery_photo ? 'under_inspection' : 'for_receiving';
    }
    const badge = STATUS_BADGES[bucket] || STATUS_BADGES.for_receiving;
    const statusEl = document.getElementById('poModalStatus');
    statusEl.textContent = badge.label;
    statusEl.className = 'text-xs font-bold mt-1 inline-block px-2 py-1 rounded-full ' + badge.class;

    document.getElementById('poModalStockNo').textContent = po.stock_no;
    document.getElementById('poModalDate').textContent = po.po_date;
    document.getElementById('poModalRequestedBy').textContent = po.requested_by;
    document.getElementById('poModalTotalCost').textContent = po.total_cost;
    document.getElementById('poModalDescription').textContent = po.description || 'N/A';

    const itemsBody = document.getElementById('poModalItems');
    itemsBody.innerHTML = '';
    if (Array.isArray(po.items) && po.items.length > 0) {
      po.items.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td class="py-2">${item.description ?? 'N/A'}</td>
          <td>${item.quantity ?? 0}</td>
          <td>${Number(item.unit_cost ?? 0).toFixed(2)}</td>
        `;
        itemsBody.appendChild(row);
      });
    } else {
      itemsBody.innerHTML = '<tr><td colspan="3" class="py-3 text-center text-gray-400">No item breakdown available.</td></tr>';
    }

    const attachmentsEl = document.getElementById('poModalAttachments');
    attachmentsEl.innerHTML = '';
    if (po.po_attachment) {
      attachmentsEl.innerHTML += `<a href="${po.po_attachment}" target="_blank" class="text-xs font-semibold text-blue-600 hover:underline">View PO Attachment</a>`;
    }
    if (po.delivery_photo) {
      attachmentsEl.innerHTML += `<a href="${po.delivery_photo}" target="_blank" class="text-xs font-semibold text-blue-600 hover:underline">View Delivery Photo</a>`;
    }

    document.getElementById('poModalBackdrop').classList.remove('hidden');
    document.getElementById('poModal').classList.remove('hidden');
  }

  function closePoModal() {
    document.getElementById('poModalBackdrop').classList.add('hidden');
    document.getElementById('poModal').classList.add('hidden');
  }
</script>

@endsection