@extends('layouts.Admin.app')

@section('content')

<div class="w-full">

  {{-- Header --}}
  <header class="mb-8 md:mb-12">
    <div>
      <h1 class="text-2xl md:text-4xl lg:text-5xl font-black text-[#283E70] tracking-tighter">
        Distribution Page
      </h1>

    </div>
  </header>

  {{-- Stats Cards --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-8 md:mb-12">

    <!-- Draft Table Card -->
    <a href="{{ route('admin.DraftTable') }}"
      class="block bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:scale-105 transition">
      <span class="text-gray-400 font-bold uppercase text-xs md:text-sm">Draft Table</span>
      <h2 class="text-4xl md:text-5xl lg:text-6xl font-black text-[#283E70] mt-3">100</h2>
      <p class="text-gray-500 text-xs md:text-sm mt-2">none</p>
    </a>

    <!-- Project Selection Card -->
    <a href="{{ route('admin.ProjectSelection') }}"
      class="block bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:scale-105 transition">
      <span class="text-gray-400 font-bold uppercase text-xs md:text-sm">Project Selection</span>
      <h2 class="text-4xl md:text-5xl lg:text-6xl font-black text-orange-500 mt-3">06</h2>
      <p class="text-gray-500 text-xs md:text-sm mt-2">Checking</p>
    </a>


  </div>


</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

@endsection