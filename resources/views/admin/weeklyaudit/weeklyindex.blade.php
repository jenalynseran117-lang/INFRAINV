@extends('layouts.Admin.app')

@section('content')

<style>
  @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&display=swap');

  .font-display {
    font-family: 'Space Grotesk', sans-serif;
  }

  .font-body {
    font-family: 'Inter', sans-serif;
  }

  @keyframes glass-drift {

    0%,
    100% {
      transform: translate(0, 0) scale(1);
    }

    50% {
      transform: translate(24px, -18px) scale(1.06);
    }
  }

  .glass-blob {
    animation: glass-drift 14s ease-in-out infinite;
  }

  @media (prefers-reduced-motion: reduce) {
    .glass-blob {
      animation: none;
    }
  }
</style>

<div class="relative w-full min-h-screen rounded-3xl overflow-hidden bg-gradient-to-br from-[#f7f5f2] via-[#f2eee8] to-[#eee7e0] p-6 sm:p-10 font-body">

  {{-- Ambient glow blobs --}}
  <div class="pointer-events-none absolute inset-0 overflow-hidden">
    <div class="glass-blob absolute -top-24 -left-16 h-80 w-80 rounded-full bg-amber-300/25 blur-3xl"></div>
    <div class="glass-blob absolute top-1/3 -right-10 h-96 w-96 rounded-full bg-emerald-200/25 blur-3xl" style="animation-delay:-5s"></div>
    <div class="glass-blob absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-blue-200/25 blur-3xl" style="animation-delay:-9s"></div>
  </div>

  <div class="relative z-10 max-w-4xl mx-auto">

    {{-- Header Section --}}
    <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-5">
      <div class="flex items-center gap-4">
        <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center flex-shrink-0 shadow-lg shadow-amber-500/30">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
        <div>
          <h1 class="text-3xl font-bold text-gray-800 font-display tracking-tight">Weekly Audit Trail</h1>
          <p class="text-sm text-gray-600 font-medium mt-1">Tracking inventory movements and procurement actions</p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <div class="flex items-center gap-1 bg-white/70 backdrop-blur-xl border border-white/70 rounded-full p-1 shadow-[0_8px_32px_rgba(31,41,55,0.08)]">
          <a href="{{ request()->url() }}?week={{ $prevWeekStart->toDateString() }}"
            class="h-9 w-9 flex items-center justify-center rounded-full text-gray-500 hover:bg-amber-100 hover:text-amber-600 transition-colors"
            aria-label="Previous week">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
          </a>

          <span class="text-sm font-bold text-gray-800 px-2 whitespace-nowrap">
            {{ $weekStart->format('M d') }} – {{ $weekEnd->format('M d, Y') }}
          </span>

          @if ($nextWeekStart)
          <a href="{{ request()->url() }}?week={{ $nextWeekStart->toDateString() }}"
            class="h-9 w-9 flex items-center justify-center rounded-full text-gray-500 hover:bg-amber-100 hover:text-amber-600 transition-colors"
            aria-label="Next week">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </a>
          @else
          <span class="h-9 w-9 flex items-center justify-center rounded-full text-gray-300 cursor-not-allowed" aria-label="No future weeks" title="Future weeks aren't available yet">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </span>
          @endif
        </div>

        @unless ($isCurrentWeek)
        <a href="{{ request()->url() }}"
          class="text-xs font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 px-4 py-2.5 rounded-full shadow-md shadow-amber-500/30 hover:-translate-y-0.5 transition-all duration-300">
          This week
        </a>
        @endunless
      </div>
    </div>

    {{-- Audit Timeline --}}
    <div class="relative">

      {{-- Continuous spine --}}
      <div class="absolute left-[7px] top-2 bottom-2 w-px bg-gray-300/70" aria-hidden="true"></div>

      <div class="space-y-9">
        @foreach ($days as $day)
        @php $hasEvents = count($day['events']) > 0; @endphp
        <div class="relative pl-9">
          {{-- Day marker --}}
          <span class="absolute left-0 top-1 h-3.5 w-3.5 rounded-full {{ $hasEvents ? 'bg-amber-500 ring-4 ring-amber-200' : 'bg-white border-2 border-gray-300' }}"></span>
          <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">{{ $day['label'] }}</h2>

          <div class="space-y-2.5">
            @forelse ($day['events'] as $event)
            @php
            $isOutflow = $event['type'] === 'outflow';
            $badgeText = $isOutflow ? 'Distribution Outflow' : 'Warehouse Inflow';
            @endphp

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl border backdrop-blur-xl px-5 py-4 hover:-translate-y-0.5 transition-all duration-200 {{ $isOutflow ? 'border-emerald-200 bg-emerald-50/70 hover:border-emerald-300 hover:bg-emerald-100/80 hover:shadow-md hover:shadow-emerald-200/60' : 'border-blue-200 bg-blue-50/70 hover:border-blue-300 hover:bg-blue-100/80 hover:shadow-md hover:shadow-blue-200/60' }}">
              <div class="flex items-center gap-4 min-w-0">
                <div class="h-10 w-10 rounded-full flex items-center justify-center flex-shrink-0 text-white shadow-md {{ $isOutflow ? 'bg-gradient-to-br from-emerald-500 to-teal-600 shadow-emerald-500/30' : 'bg-gradient-to-br from-blue-500 to-indigo-600 shadow-blue-500/30' }}">
                  @if ($isOutflow)
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                  </svg>
                  @else
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 17l-4 4m0 0l-4-4m4 4V3" />
                  </svg>
                  @endif
                </div>
                <div class="min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[10px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-full {{ $isOutflow ? 'text-white bg-emerald-500' : 'text-white bg-blue-500' }}">{{ $badgeText }}</span>
                    <span class="text-[11px] text-gray-500 font-semibold">{{ $event['time']->format('g:i A') }}</span>
                  </div>
                  <h3 class="text-sm font-bold text-gray-800 mt-1.5">{{ $event['title'] }}</h3>
                  <p class="text-xs text-gray-600 font-medium mt-0.5">{{ $event['subtitle'] }}</p>
                </div>
              </div>

              <div class="flex items-center gap-1.5 text-xs pl-14 sm:pl-0 flex-shrink-0">
                <span class="text-gray-500 font-medium">Handled by</span>
                <span class="font-bold text-gray-800">{{ $event['handled_by'] }}</span>
              </div>
            </div>
            @empty
            <div class="rounded-2xl border border-dashed border-gray-300/70 bg-white/50 backdrop-blur-xl px-5 py-3.5">
              <p class="text-xs text-gray-500 font-medium">No inflow or outflow recorded.</p>
            </div>
            @endforelse
          </div>
        </div>
        @endforeach

        {{-- End-of-week inventory summary --}}
        <div class="relative pl-9">
          <span class="absolute left-0 top-1 h-3.5 w-3.5 rounded-full {{ $showSummary ? 'bg-amber-500 ring-4 ring-amber-200' : 'bg-white border-2 border-gray-300' }}"></span>
          <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">End-of-Week Summary</h2>

          @if ($showSummary)
          <div class="rounded-2xl border border-emerald-200/70 bg-white/60 backdrop-blur-xl shadow-[0_8px_32px_rgba(31,41,55,0.08)] p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
              <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 px-5 py-4 flex items-center gap-4 shadow-md shadow-emerald-500/25">
                <div class="h-11 w-11 rounded-full bg-white/20 backdrop-blur-md text-white flex items-center justify-center flex-shrink-0 ring-1 ring-white/30">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0l-6 6m6-6l6 6" />
                  </svg>
                </div>
                <div>
                  <p class="text-[11px] font-bold text-emerald-100 uppercase tracking-wide">Items Received</p>
                  <p class="text-2xl font-bold text-white font-display">+{{ number_format($weekSummary['items_received']) }}</p>
                </div>
              </div>
              <div class="rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 px-5 py-4 flex items-center gap-4 shadow-md shadow-rose-500/25">
                <div class="h-11 w-11 rounded-full bg-white/20 backdrop-blur-md text-white flex items-center justify-center flex-shrink-0 ring-1 ring-white/30">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m0 0l-6-6m6 6l6-6" />
                  </svg>
                </div>
                <div>
                  <p class="text-[11px] font-bold text-rose-100 uppercase tracking-wide">Items Distributed</p>
                  <p class="text-2xl font-bold text-white font-display">-{{ number_format($weekSummary['items_distributed']) }}</p>
                </div>
              </div>
            </div>

            <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wide mb-3">Used / Released Per Project</h3>

            @if ($weekSummary['by_project']->isEmpty())
            <p class="text-sm text-gray-500 font-medium">No items were released to any project this week.</p>
            @else
            <div class="space-y-2">
              @foreach ($weekSummary['by_project'] as $projectName => $totals)
              <div class="flex justify-between items-center rounded-xl bg-white/70 border border-gray-200/70 px-5 py-3.5 hover:border-amber-300 hover:bg-amber-50/70 transition-colors duration-200">
                <span class="text-sm font-bold text-gray-800">{{ $projectName }}</span>
                <div class="text-right">
                  <span class="text-sm font-bold text-amber-600">{{ number_format($totals['quantity']) }} pcs</span>
                  <span class="block text-xs text-gray-500 font-medium">₱{{ number_format($totals['value'], 2) }}</span>
                </div>
              </div>
              @endforeach
            </div>
            @endif
          </div>
          @else
          <div class="rounded-2xl border border-dashed border-gray-300/70 bg-white/50 backdrop-blur-xl px-5 py-4">
            <p class="text-sm font-bold text-gray-600">Summary not available yet.</p>
            <p class="text-xs text-gray-500 font-medium mt-1">Generated once the week reaches Saturday, {{ $weekEnd->format('M d, Y') }}.</p>
          </div>
          @endif
        </div>
      </div>
    </div>

  </div>
</div>
@endsection