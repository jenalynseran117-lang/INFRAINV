@extends('layouts.Inspector.app')

@section('content')

<div class="space-y-4 mt-10">

  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-rose-500">
      Rejected Purchase Orders
    </h2>

    <span class="text-sm font-bold text-slate-500">
      Total: {{ $purchaseOrders->count() }}
    </span>
  </div>

  @forelse($purchaseOrders as $po)

  <div class="p-6 bg-white border border-red-100 rounded-2xl shadow-sm hover:shadow-md transition">

    {{-- PO NUMBER --}}
    <h3 class="text-xl font-bold text-slate-800">
      {{ $po->po_number }}
    </h3>

    {{-- DESCRIPTION --}}
    <p class="text-slate-500 mt-1">
      {{ $po->description }}
    </p>

    {{-- STATUS BADGE --}}
    <div class="mt-4">
      <span class="text-xs font-bold text-white bg-rose-500 px-3 py-1 rounded-full uppercase tracking-wider">
        {{ $po->status }}
      </span>
    </div>

  </div>

  @empty

  <div class="text-center text-gray-400 py-10 bg-white border rounded-2xl">
    No rejected purchase orders found.
  </div>

  @endforelse

</div>

@endsection