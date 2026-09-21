@extends('layouts.Admin.app')

@section('content')
<style>
  /* Custom Scrollbar */
  ::-webkit-scrollbar {
    width: 6px;
  }

  ::-webkit-scrollbar-track {
    background: #f1f1f1;
  }

  ::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
  }

  ::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
  }

  /* Entry Animations */
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(15px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .item-card {
    opacity: 0;
    animation: fadeInUp 0.4s ease forwards;
  }

  /* Glass Effect */
  .glass-sidebar {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
  }

  /* Custom Focus */
  .custom-input:focus {
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
  }
</style>

<div class="w-full min-h-screen bg-[#F8FAFC] p-4 md:p-8">

  {{-- Header Section --}}
  <div class="max-w-[1400px] mx-auto mb-10 flex justify-between items-end">
    <div class="item-card" style="animation-delay: 100ms">
      <h1 class="text-4xl font-black text-slate-900 tracking-tight leading-none">Distribution & Outflow</h1>
      <p class="text-[11px] font-bold text-blue-500 uppercase tracking-[0.3em] mt-3 bg-blue-50 inline-block px-2 py-0.5 rounded">Inventory Management — Feb 16, 2026</p>
    </div>
    <div class="hidden md:block text-right item-card" style="animation-delay: 200ms">
      <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl shadow-sm border border-slate-100">
        <span class="relative flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
        </span>
        <span class="text-[10px] font-black text-slate-600 uppercase tracking-tighter">System Live: Warehouse-Alpha</span>
      </div>
    </div>
  </div>

  {{-- Main Grid --}}
  <div class="max-w-[1400px] mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

    {{-- Left Column: Draft Queue --}}
    <div class="lg:col-span-8 space-y-6">
      <div class="flex justify-between items-center px-2">
        <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.25em]">Current Draft Queue</h2>
        
      </div>

      <div class="space-y-4">
        @php
        $items = [
        ['name' => 'Cement 50kg', 'stock' => '150 bags', 'val' => '100', 'unit' => 'bags', 'ms' => 200],
        ['name' => 'Steel Bar 10mm', 'stock' => '85 pcs', 'val' => '55', 'unit' => 'pcs', 'ms' => 300],
        ['name' => 'Sand (Fine)', 'stock' => '12 cu.m', 'val' => '5', 'unit' => 'cu.m', 'ms' => 400],
        ];
        @endphp

        @foreach($items as $item)
        <div class="item-card group flex items-center justify-between p-6 bg-white rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-blue-500/5 transition-all duration-300" style="animation-delay: {{ $item['ms'] }}ms">
          <div class="flex items-center gap-6">
            <div class="h-14 w-14 bg-slate-50 rounded-[1.5rem] flex items-center justify-center text-slate-400 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <div>
              <h3 class="text-xl font-black text-slate-800 tracking-tight group-hover:text-blue-600 transition-colors">{{ $item['name'] }}</h3>
              <div class="flex items-center gap-2 mt-1">
                <span class="text-[9px] font-black bg-slate-100 text-slate-500 px-2 py-0.5 rounded uppercase">In Stock</span>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-tight">{{ $item['stock'] }}</p>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-6">
            <div class="flex flex-col items-end">
              <span class="text-[10px] font-black text-slate-300 uppercase mb-2 mr-1">Quantity to Release</span>
              <div class="flex items-center bg-slate-50 rounded-2xl px-5 py-3 border-2 border-transparent focus-within:border-blue-500 focus-within:bg-white transition-all">
                <input type="number" value="{{ $item['val'] }}" class="bg-transparent w-16 text-center font-black text-slate-800 outline-none text-lg">
                <span class="text-[10px] font-black text-blue-500 ml-2 uppercase">{{ $item['unit'] }}</span>
              </div>
            </div>
            {{-- Delete button was removed here --}}
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Right Column: Project Selection --}}
    <div class="lg:col-span-4 sticky top-8 item-card" style="animation-delay: 500ms">
      <div class="glass-sidebar p-8 rounded-[3.5rem] border border-white shadow-2xl shadow-slate-200 space-y-8">

        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Deployment Destination</label>
            <button type="button" class="text-[9px] font-black text-blue-600 bg-blue-50 px-3 py-1.5 rounded-xl hover:bg-blue-600 hover:text-white transition-all uppercase">
              + Add Project
            </button>
          </div>

          <div class="relative group">
            <select class="custom-input w-full p-6 bg-white border-2 border-slate-100 rounded-[2rem] font-black text-slate-700 appearance-none focus:border-blue-500 transition-all outline-none cursor-pointer shadow-sm">
              <option value="">Choose Project Site...</option>
              <option>Bridge Construction - Phase 1</option>
              <option>City Hall Renovation</option>
            </select>
            <div class="absolute inset-y-0 right-6 flex items-center pointer-events-none text-blue-500">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M19 9l-7 7-7-7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
          </div>
        </div>

        <div class="p-8 bg-slate-900 rounded-[2.5rem] text-white space-y-6 relative overflow-hidden shadow-xl shadow-slate-900/20">
          <div class="absolute -right-4 -top-4 h-24 w-24 bg-blue-500 rounded-full blur-3xl opacity-20"></div>
          <p class="text-[10px] font-black text-blue-400 uppercase tracking-[0.3em]">Review Totals</p>
          <div class="space-y-4">
            <div class="flex justify-between items-end border-b border-slate-800 pb-4">
              <span class="text-xs font-bold text-slate-500 uppercase">Items</span>
              <span class="text-3xl font-black tracking-tighter leading-none">03</span>
            </div>
            <div class="flex justify-between items-end border-b border-slate-800 pb-4">
              <span class="text-xs font-bold text-slate-500 uppercase">Quantity</span>
              <span class="text-3xl font-black tracking-tighter leading-none">160</span>
            </div>
          </div>
        </div>

        <div class="space-y-4 pt-2">
          <button class="group w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-6 px-6 rounded-[2.5rem] flex items-center justify-center gap-4 transition-all shadow-xl shadow-blue-200 active:scale-95">
            <span class="uppercase tracking-[0.25em] text-[11px]">Authorize Release</span>
            <div class="bg-blue-500 p-1 rounded-lg group-hover:translate-x-1 transition-transform">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </div>
          </button>
          <div class="flex items-center justify-center gap-2">
            <svg class="h-3 w-3 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
            </svg>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Secure Transaction</p>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection