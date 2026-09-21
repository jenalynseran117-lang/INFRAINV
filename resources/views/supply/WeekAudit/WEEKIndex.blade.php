@extends('layouts.Supply.app')

@section('content')
{{-- Soft neutral canvas so the colored cards and spine have room to pop --}}
<div class="w-full min-h-screen bg-slate-50 p-4 md:p-8 antialiased">

  {{-- Header Section --}}
  <div class="max-w-4xl mx-auto mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-5">
    <div class="flex items-center gap-4">
      <div class="h-11 w-11 rounded-xl bg-pink-50 border border-pink-100 text-pink-600 flex items-center justify-center flex-shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>
      <div>
        <h1 class="text-2xl font-semibold text-slate-900 tracking-tight">Weekly Audit Trail</h1>
        <p class="text-sm text-slate-500 mt-0.5">Tracking inventory movements and procurement actions</p>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <div class="flex items-center gap-1 bg-white border border-slate-200 rounded-full p-1 shadow-sm">
        <a href="{{ request()->url() }}?week={{ $prevWeekStart->toDateString() }}"
          class="h-8 w-8 flex items-center justify-center rounded-full text-slate-400 hover:bg-pink-50 hover:text-pink-600 transition-colors"
          aria-label="Previous week">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
        </a>

        <span class="text-sm font-semibold text-slate-700 px-2 whitespace-nowrap">
          {{ $weekStart->format('M d') }} – {{ $weekEnd->format('M d, Y') }}
        </span>

        @if ($nextWeekStart)
        <a href="{{ request()->url() }}?week={{ $nextWeekStart->toDateString() }}"
          class="h-8 w-8 flex items-center justify-center rounded-full text-slate-400 hover:bg-pink-50 hover:text-pink-600 transition-colors"
          aria-label="Next week">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </a>
        @else
        <span class="h-8 w-8 flex items-center justify-center rounded-full text-slate-200 cursor-not-allowed" aria-label="No future weeks" title="Future weeks aren't available yet">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </span>
        @endif
      </div>

      @unless ($isCurrentWeek)
      <a href="{{ request()->url() }}"
        class="text-xs font-semibold text-white bg-pink-500 hover:bg-pink-600 px-3.5 py-2 rounded-full shadow-sm shadow-pink-200 transition-colors">
        This week
      </a>
      @endunless
    </div>
  </div>

  {{-- Audit Timeline --}}
  <div class="max-w-4xl mx-auto">
    <div class="relative">

      {{-- Continuous spine running behind every day --}}
      <div class="absolute left-[7px] top-2 bottom-2 w-px bg-slate-200" aria-hidden="true"></div>

      <div class="space-y-9">
        @foreach ($days as $day)
        @php $hasEvents = count($day['events']) > 0; @endphp
        <div class="relative pl-9">
          {{-- Day marker sits on the spine — lit up pink when the day had activity --}}
          <span class="absolute left-0 top-1 h-3.5 w-3.5 rounded-full {{ $hasEvents ? 'bg-pink-500 ring-4 ring-pink-100' : 'bg-white border-2 border-slate-300' }}"></span>
          <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">{{ $day['label'] }}</h2>

          <div class="space-y-2.5">
            @forelse ($day['events'] as $event)
            @php
            $isOutflow = $event['type'] === 'outflow';
            $badgeText = $isOutflow ? 'Distribution Outflow' : 'Warehouse Inflow';
            @endphp

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl border px-5 py-4 -translate-x-0 hover:-translate-y-0.5 transition-all duration-200 {{ $isOutflow ? 'border-emerald-100 bg-emerald-50/50 hover:border-emerald-300 hover:bg-emerald-100/70 hover:shadow-md hover:shadow-emerald-100' : 'border-blue-100 bg-blue-50/50 hover:border-blue-300 hover:bg-blue-100/70 hover:shadow-md hover:shadow-blue-100' }}">
              <div class="flex items-center gap-4 min-w-0">
                <div class="h-10 w-10 rounded-full flex items-center justify-center flex-shrink-0 text-white {{ $isOutflow ? 'bg-emerald-500' : 'bg-blue-500' }}">
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
                    <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full {{ $isOutflow ? 'text-emerald-700 bg-emerald-100' : 'text-blue-700 bg-blue-100' }}">{{ $badgeText }}</span>
                    <span class="text-[11px] text-slate-400">{{ $event['time']->format('g:i A') }}</span>
                  </div>
                  <h3 class="text-sm font-semibold text-slate-800 mt-1">{{ $event['title'] }}</h3>
                  <p class="text-xs text-slate-500 mt-0.5">{{ $event['subtitle'] }}</p>
                </div>
              </div>

              <div class="flex items-center gap-1.5 text-xs pl-14 sm:pl-0 flex-shrink-0">
                <span class="text-slate-400">Handled by</span>
                <span class="font-semibold text-slate-700">{{ $event['handled_by'] }}</span>
              </div>
            </div>
            @empty
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white/60 px-5 py-3.5">
              <p class="text-xs text-slate-400">No inflow or outflow recorded.</p>
            </div>
            @endforelse
          </div>
        </div>
        @endforeach

        {{-- End-of-week inventory summary — only appears once the week reaches Saturday --}}
        <div class="relative pl-9">
          <span class="absolute left-0 top-1 h-3.5 w-3.5 rounded-full {{ $showSummary ? 'bg-pink-500 ring-4 ring-pink-100' : 'bg-white border-2 border-slate-300' }}"></span>
          <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">End-of-Week Summary</h2>

          @if ($showSummary)
          <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50/60 to-white p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
              <div class="rounded-xl bg-white border border-emerald-100 px-5 py-4 flex items-center gap-4">
                <div class="h-10 w-10 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0l-6 6m6-6l6 6" />
                  </svg>
                </div>
                <div>
                  <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Items Received</p>
                  <p class="text-2xl font-bold text-emerald-600">+{{ number_format($weekSummary['items_received']) }}</p>
                </div>
              </div>
              <div class="rounded-xl bg-white border border-rose-100 px-5 py-4 flex items-center gap-4">
                <div class="h-10 w-10 rounded-full bg-rose-500 text-white flex items-center justify-center flex-shrink-0">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m0 0l-6-6m6 6l6-6" />
                  </svg>
                </div>
                <div>
                  <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Items Distributed</p>
                  <p class="text-2xl font-bold text-rose-500">-{{ number_format($weekSummary['items_distributed']) }}</p>
                </div>
              </div>
            </div>

            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">Used / Released Per Project</h3>

            @if ($weekSummary['by_project']->isEmpty())
            <p class="text-sm text-slate-400">No items were released to any project this week.</p>
            @else
            <div class="space-y-2">
              @foreach ($weekSummary['by_project'] as $projectName => $totals)
              <div class="flex justify-between items-center rounded-xl bg-white border border-emerald-50 px-5 py-3.5 hover:border-pink-200 hover:bg-pink-50/60 transition-colors duration-200">
                <span class="text-sm font-medium text-slate-700">{{ $projectName }}</span>
                <div class="text-right">
                  <span class="text-sm font-bold text-pink-600">{{ number_format($totals['quantity']) }} pcs</span>
                  <span class="block text-xs text-slate-400">₱{{ number_format($totals['value'], 2) }}</span>
                </div>
              </div>
              @endforeach
            </div>
            @endif
          </div>
          @else
          <div class="rounded-2xl border border-dashed border-slate-200 bg-white/60 px-5 py-4">
            <p class="text-sm font-semibold text-slate-500">Summary not available yet.</p>
            <p class="text-xs text-slate-400 mt-1">Generated once the week reaches Saturday, {{ $weekEnd->format('M d, Y') }}.</p>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

</div>
@endsection