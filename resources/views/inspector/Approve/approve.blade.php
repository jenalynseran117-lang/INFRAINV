@extends('layouts.Inspector.app')

@section('content')

<div class="space-y-4 mt-10">

    @forelse($purchaseOrders as $po)

    <div class="p-6 bg-white border rounded-2xl shadow-sm">

        <h3 class="text-xl font-bold text-slate-800">
            {{ $po->po_number }}
        </h3>

        <p class="text-slate-500">
            {{ $po->description ?? 'No description available' }}
        </p>

        <span class="text-xs font-bold text-emerald-600 uppercase">
            {{ $po->status }}
        </span>

    </div>

    @empty

    <div class="text-center text-gray-400 py-10">
        No approved purchase orders found.
    </div>

    @endforelse

</div>

@endsection