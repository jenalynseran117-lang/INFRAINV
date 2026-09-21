@extends('layouts.Admin.app')

@section('content')

{{-- $projectMaterials is built server-side by InventoryReporting::buildProjectMaterials()
     and passed in from the controller — see adminbuttonCRTL@ProjectIndex. --}}
<script>
    window.__projectMaterials = {{ Illuminate\Support\Js::from($projectMaterials) }};
</script>

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
    #printArea .print-project + .print-project { margin-top: 32px; page-break-before: always; }
    #printArea table { width: 100%; border-collapse: collapse; font-size: 12px; }
    #printArea th, #printArea td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
    #printArea th { background: #f3f4f6; }
</style>

<div id="printArea"></div>

<script>
    function peso(n) {
        return '₱' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function projectPrintHTML(project) {
        const rows = (project.items || []).map(item => {
            const locs = (item.locations || [])
                .map(l => `${l.location}: ${l.quantity} pcs`)
                .join(', ');
            return `
                <tr>
                    <td>${item.name}</td>
                    <td>${item.quantity} pcs</td>
                    <td>${locs || '—'}</td>
                    <td>${peso(item.unit_cost)}</td>
                    <td>${peso(item.total_value)}</td>
                </tr>`;
        }).join('');

        return `
            <div class="print-project">
                <h2 style="font-size:18px;font-weight:700;margin-bottom:2px;">${project.name}</h2>
                <p style="font-size:11px;color:#666;margin-bottom:12px;">Materials distributed &middot; Printed ${new Date().toLocaleString('en-PH')}</p>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Locations</th>
                            <th>Unit Cost</th>
                            <th>Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows || '<tr><td colspan="5">No materials distributed yet.</td></tr>'}
                    </tbody>
                </table>
            </div>`;
    }

    function printProject(projectId) {
        const project = window.__projectMaterials[projectId];
        if (!project) return;
        document.getElementById('printArea').innerHTML = projectPrintHTML(project);
        requestAnimationFrame(() => requestAnimationFrame(() => window.print()));
    }

    function printAllProjects() {
        const html = Object.values(window.__projectMaterials)
            .map(p => projectPrintHTML(p))
            .join('');
        document.getElementById('printArea').innerHTML = html || '<p>No projects yet.</p>';
        requestAnimationFrame(() => requestAnimationFrame(() => window.print()));
    }
</script>

<div class="relative w-full rounded-3xl overflow-hidden bg-gradient-to-br from-[#f7f5f2] via-[#f2eee8] to-[#eee7e0] p-6 sm:p-10 font-body min-h-screen"
     x-data="{ showMaterialsModal: false, activeProject: null }">

  {{-- Ambient glow blobs --}}
  <div class="pointer-events-none absolute inset-0 overflow-hidden">
    <div class="glass-blob absolute -top-24 -left-16 h-80 w-80 rounded-full bg-blue-300/25 blur-3xl"></div>
    <div class="glass-blob absolute top-1/3 -right-10 h-96 w-96 rounded-full bg-emerald-200/25 blur-3xl" style="animation-delay:-5s"></div>
    <div class="glass-blob absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-orange-200/20 blur-3xl" style="animation-delay:-9s"></div>
  </div>

  <div class="relative z-10">

    <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-3xl font-bold text-gray-800 font-display tracking-tight">Projects</h1>
        <p class="text-sm text-gray-500 mt-1">Track materials distributed across active sites</p>
      </div>

      @if ($projects->isNotEmpty())
        <button type="button" @click="printAllProjects()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/70 backdrop-blur-xl border border-white/70 hover:bg-white text-gray-700 text-sm font-bold rounded-xl shadow-[0_8px_32px_rgba(31,41,55,0.08)] hover:shadow-md transition-all duration-300">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
          </svg>
          Print All
        </button>
      @endif
    </div>

    @if ($projects->isEmpty())
      <div class="bg-white/60 backdrop-blur-xl border border-dashed border-gray-300/70 rounded-2xl p-12 text-center">
        <p class="text-sm font-bold text-gray-500">No projects yet.</p>
      </div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($projects as $project)
          @php
            $allItems = $project->distributions->flatMap->items;
            $itemsDistributed = $allItems->sum('quantity');
            $totalValue = $allItems->sum(fn($i) => $i->quantity * $i->unit_cost);

            $progress = null;
            if ($project->budget > 0) {
                $progress = min(100, round(($totalValue / $project->budget) * 100));
            }

            $statusStyles = match (strtolower($project->status ?? '')) {
                'active'    => ['badge' => 'bg-emerald-500 text-white', 'accent' => 'from-emerald-500 to-teal-600', 'bar' => 'from-emerald-500 to-teal-500'],
                'completed' => ['badge' => 'bg-blue-500 text-white', 'accent' => 'from-blue-500 to-indigo-600', 'bar' => 'from-blue-500 to-indigo-500'],
                'on_hold', 'on-hold' => ['badge' => 'bg-amber-500 text-white', 'accent' => 'from-amber-500 to-orange-600', 'bar' => 'from-amber-500 to-orange-500'],
                default     => ['badge' => 'bg-gray-500 text-white', 'accent' => 'from-gray-400 to-gray-500', 'bar' => 'from-gray-400 to-gray-500'],
            };
          @endphp

          <div class="relative overflow-hidden bg-white/70 backdrop-blur-xl rounded-2xl border border-white/70 shadow-[0_8px_32px_rgba(31,41,55,0.08)] hover:shadow-[0_8px_32px_rgba(31,41,55,0.14)] hover:-translate-y-1 transition-all duration-300 p-6 flex flex-col">

            {{-- Top accent bar --}}
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r {{ $statusStyles['accent'] }}"></div>

            <div class="flex justify-between items-start mb-2 gap-4 mt-1.5">
              <h2 class="text-xl font-bold text-gray-900 leading-tight font-display">{{ $project->name }}</h2>
              <span class="{{ $statusStyles['badge'] }} text-xs font-bold px-3 py-1 rounded-full capitalize shadow-sm shrink-0">{{ $project->status ?? 'unknown' }}</span>
            </div>
            <p class="text-sm text-gray-600 font-semibold mb-6">{{ number_format($itemsDistributed) }} items distributed</p>

            <div class="mt-auto">
              @if (!is_null($progress))
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm text-gray-600 font-semibold">Progress</span>
                  <span class="text-sm font-bold text-gray-800">{{ $progress }}%</span>
                </div>
                <div class="w-full bg-gray-200/70 rounded-full h-2.5 mb-6 overflow-hidden">
                  <div class="bg-gradient-to-r {{ $statusStyles['bar'] }} h-2.5 rounded-full" style="width: {{ $progress }}%"></div>
                </div>
              @else
                <p class="text-xs text-gray-500 font-medium mb-6 bg-gray-100/70 px-3 py-2 rounded-lg">No budget set — progress not available.</p>
              @endif

              <div class="flex justify-between items-center mb-6 bg-gray-50/70 px-4 py-3 rounded-xl">
                <span class="text-sm text-gray-600 font-semibold">Total Value:</span>
                <span class="text-base font-bold text-gray-900">₱{{ number_format($totalValue, 2) }}</span>
              </div>

              <button type="button"
                      @click="showMaterialsModal = true; activeProject = {{ $project->id }}"
                      class="w-full py-3 px-4 bg-gradient-to-r {{ $statusStyles['accent'] }} rounded-xl text-sm font-bold text-white shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                View Materials List
              </button>
            </div>
          </div>
        @endforeach
      </div>
    @endif

    {{-- View Materials List Modal --}}
    <div x-show="showMaterialsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4" x-cloak>
      <div class="bg-white/90 backdrop-blur-2xl rounded-2xl shadow-2xl border border-white/70 w-full max-w-lg max-h-[85vh] flex flex-col font-body"
           @click.away="showMaterialsModal = false">

        <div class="p-6 border-b border-gray-200/70 flex justify-between items-start gap-4 shrink-0">
          <div>
            <h3 class="text-base font-bold text-gray-800 font-display" x-text="(window.__projectMaterials[activeProject] || {}).name || 'Materials List'"></h3>
            <p class="text-xs text-gray-500 mt-0.5 font-medium">Items distributed to this project</p>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <button type="button" @click="printProject(activeProject)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/70 border border-gray-200 rounded-lg text-xs font-bold text-gray-700 hover:bg-white transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
              </svg>
              Print
            </button>
            <button type="button" @click="showMaterialsModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <div class="p-6 overflow-y-auto space-y-4">
          <template x-for="item in ((window.__projectMaterials[activeProject] || {}).items || [])" :key="item.name">
            <div class="pb-4 border-b border-gray-200/70 last:border-0 last:pb-0">
              <div class="flex justify-between items-start gap-4 mb-1.5">
                <span class="text-sm font-bold text-gray-800" x-text="item.name"></span>
                <span class="text-sm font-bold text-gray-900 shrink-0" x-text="item.quantity + ' pcs'"></span>
              </div>

              <div class="flex flex-wrap gap-1.5 mb-1.5">
                <template x-for="loc in item.locations" :key="loc.location">
                  <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-700 bg-blue-50 border border-blue-100 px-2 py-1 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span x-text="loc.location"></span>
                    <span class="text-blue-300">&middot;</span>
                    <span x-text="loc.quantity + ' pcs'"></span>
                  </span>
                </template>
              </div>

              <p class="text-xs text-gray-500 font-medium">
                <span x-text="'₱' + item.unit_cost.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                <span> / unit &middot; </span>
                <span class="font-bold text-gray-700" x-text="'₱' + item.total_value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' total'"></span>
              </p>
            </div>
          </template>

          <template x-if="!((window.__projectMaterials[activeProject] || {}).items || []).length">
            <div class="text-center py-8">
              <p class="text-sm font-bold text-gray-700">No materials distributed yet.</p>
              <p class="text-xs text-gray-500 mt-1 font-medium">Items released to this project from Distribution will show up here.</p>
            </div>
          </template>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection