@extends('layouts.Supply.app')

@section('content')

{{--
    Build a per-project materials summary from the already-loaded
    `distributions.items` relationship (see SupplyController@Projectindex),
    grouped by item name, with a per-warehouse quantity breakdown and the
    real total value (quantity * unit_cost per distributed line, summed).
--}}
@php
$projectMaterials = [];

foreach ($projects as $p) {
$items = $p->distributions
->flatMap(function ($d) { return $d->items; })
->groupBy('item_name')
->map(function ($group) {
$totalQty = $group->sum('quantity');
$totalValue = $group->sum(function ($i) { return $i->quantity * $i->unit_cost; });
$avgCost = $totalQty > 0 ? $totalValue / $totalQty : 0;

$locations = $group
->groupBy('storage_location')
->map(function ($g2, $loc) {
return ['location' => $loc ?: 'Unknown', 'quantity' => $g2->sum('quantity')];
})
->values();

return [
'name' => $group->first()->item_name,
'quantity' => $totalQty,
'unit_cost' => (float) $avgCost,
'total_value' => (float) $totalValue,
'locations' => $locations,
];
})
->values();

$projectMaterials[$p->id] = [
'name' => $p->name,
'items' => $items,
];
}
@endphp

{{--
    Feeds window.__projectMaterials for the print functions below
    (per-project + "Print All"), using Blade's @json() directive so the
    PHP array is safely serialized as a JS object.
--}}
<script>
  window.__projectMaterials = @json($projectMaterials);
</script>

{{--
    Print support: everything except #printArea is hidden on @media print.
    We build the printable HTML on demand (per project, or all projects)
    and drop it into #printArea right before calling window.print().
--}}
<style>
  #printArea {
    position: fixed;
    top: 0;
    left: 0;
    width: 0;
    height: 0;
    overflow: hidden;
  }
</style>
<style media="print">
  body * {
    visibility: hidden;
  }

  #printArea,
  #printArea * {
    visibility: visible;
  }

  #printArea {
    display: block !important;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: auto;
    overflow: visible;
    padding: 24px;
  }

  #printArea .print-project+.print-project {
    margin-top: 32px;
    page-break-before: always;
  }

  #printArea table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
  }

  #printArea th,
  #printArea td {
    border: 1px solid #ccc;
    padding: 6px 8px;
    text-align: left;
  }

  #printArea th {
    background: #f3f4f6;
  }
</style>

<div id="printArea"></div>

<script>
  function peso(n) {
    return '₱' + Number(n || 0).toLocaleString('en-PH', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
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

{{--
    IMPORTANT: keep this wrapper simple — no `relative`/`overflow-hidden`
    full-page div here. That's what was fighting the sidebar's fixed
    positioning in the layout before. `p-6` + `bg-gray-50` + `min-h-screen`
    is all the outer div needs.
--}}
<div class="p-6 sm:p-8 bg-gray-50 min-h-screen font-sans" x-data="{ showAddModal: false, showMaterialsModal: false, activeProject: null }">

  <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
      <h1 class="text-3xl font-extrabold text-gray-900 mb-1 tracking-tight">Supply Projects</h1>
      <p class="text-sm text-gray-500 font-medium">{{ $projects->count() }} project(s) on record</p>
    </div>

    <div class="flex items-center gap-3">
      @if ($projects->isNotEmpty())
      <button type="button" @click="printAllProjects()"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl shadow-sm hover:shadow transition-all duration-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
        </svg>
        Print All
      </button>
      @endif

      <button type="button" @click="showAddModal = true"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-slate-900 to-slate-700 hover:from-blue-600 hover:to-blue-500 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Add Project
      </button>
    </div>
  </div>

  @if (session('success'))
  <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium rounded-xl">
    {{ session('success') }}
  </div>
  @endif

  @if ($projects->isEmpty())
  <div class="bg-white rounded-2xl border border-dashed border-gray-200 p-14 text-center">
    <p class="text-sm font-bold text-gray-600">No projects yet.</p>
    <p class="text-xs text-gray-400 mt-1">Click "Add Project" to create the first one.</p>
  </div>
  @else
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

    @foreach ($projects as $project)
    @php
    $statusStyles = match ($project->status) {
    'active' => ['badge' => 'bg-pink-500 text-white', 'bar' => 'bg-pink-500', 'accent' => 'from-pink-400 to-rose-500'],
    'completed' => ['badge' => 'bg-blue-100 text-blue-700', 'bar' => 'bg-blue-500', 'accent' => 'from-blue-400 to-indigo-500'],
    'on-hold' => ['badge' => 'bg-amber-100 text-amber-700', 'bar' => 'bg-amber-500', 'accent' => 'from-amber-400 to-orange-500'],
    default => ['badge' => 'bg-slate-100 text-slate-600', 'bar' => 'bg-slate-400', 'accent' => 'from-slate-300 to-slate-400'],
    };
    @endphp

    <div class="relative bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">

      {{-- top accent bar --}}
      <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r {{ $statusStyles['accent'] }}"></div>

      <div class="flex justify-between items-start mb-2 gap-4 mt-1">
        <div>
          <h2 class="text-lg font-bold text-gray-900 leading-tight">{{ $project->name }}</h2>
          @if ($project->location)
          <p class="text-xs text-gray-400 font-medium mt-0.5">{{ $project->location }}</p>
          @endif
        </div>
        <span class="{{ $statusStyles['badge'] }} text-xs font-bold px-2.5 py-1 rounded-full capitalize shrink-0">{{ $project->status }}</span>
      </div>

      <p class="text-sm text-gray-500 font-medium mb-6">{{ $project->items_distributed_count }} items distributed</p>

      <div class="mt-auto">
        @if (!is_null($project->progress_percent))
        <div class="flex justify-between items-center mb-2">
          <span class="text-sm text-gray-600 font-medium">Progress</span>
          <span class="text-sm font-bold text-gray-800">{{ $project->progress_percent }}%</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-2.5 mb-6 overflow-hidden">
          <div class="{{ $statusStyles['bar'] }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $project->progress_percent }}%"></div>
        </div>
        @else
        <p class="text-xs text-gray-400 font-medium mb-6 bg-gray-50 px-3 py-2 rounded-lg">No budget set — progress not tracked.</p>
        @endif

        <div class="flex justify-between items-center mb-6 bg-gray-50 px-4 py-3 rounded-xl">
          <span class="text-sm text-gray-600 font-medium">Total Value:</span>
          <span class="text-sm font-bold text-gray-900">₱{{ number_format($project->total_distributed_value, 2) }}</span>
        </div>

        <button type="button"
          @click="showMaterialsModal = true; activeProject = {{ $project->id }}"
          class="w-full py-2.5 px-4 border border-gray-200 rounded-xl text-sm font-semibold text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition-colors">
          View Materials List
        </button>
      </div>
    </div>
    @endforeach

  </div>
  @endif

  {{-- Add Project Modal --}}
  <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm" x-cloak>
    <div class="bg-white p-6 rounded-2xl shadow-xl border border-slate-100 w-full max-w-sm mx-4" @click.away="showAddModal = false">
      <h3 class="text-sm font-bold text-slate-800 mb-4">Add New Project</h3>

      <form method="POST" action="{{ route('supply.Project.store') }}" class="space-y-4">
        @csrf

        <div>
          <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1">Project Name</label>
          <input type="text" name="name" required
            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-400 outline-none transition-all text-sm">
        </div>

        <div>
          <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1">Location <span class="normal-case text-slate-300">(optional)</span></label>
          <input type="text" name="location"
            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-400 outline-none transition-all text-sm">
        </div>

        <div>
          <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1">Budget (₱) <span class="normal-case text-slate-300">(optional — used for Progress %)</span></label>
          <input type="number" name="budget" min="0" step="0.01"
            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-400 outline-none transition-all text-sm">
        </div>

        <div class="flex justify-end gap-2 text-xs font-semibold pt-2">
          <button type="button" @click="showAddModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-gradient-to-r from-slate-900 to-slate-700 hover:from-blue-600 hover:to-blue-500 text-white rounded-lg shadow-sm transition-all">Save Project</button>
        </div>
      </form>
    </div>
  </div>

  {{-- View Materials List Modal --}}
  <div x-show="showMaterialsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4" x-cloak>
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 w-full max-w-lg max-h-[85vh] flex flex-col"
      @click.away="showMaterialsModal = false">

      <div class="p-6 border-b border-slate-100 flex justify-between items-start gap-4 shrink-0">
        <div>
          <h3 class="text-sm font-bold text-slate-800" x-text="(window.__projectMaterials[activeProject] || {}).name || 'Materials List'"></h3>
          <p class="text-xs text-slate-400 mt-0.5">Items distributed to this project</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <button type="button" @click="printProject(activeProject)"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
            </svg>
            Print
          </button>
          <button type="button" @click="showMaterialsModal = false" class="text-slate-400 hover:text-slate-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <div class="p-6 overflow-y-auto space-y-4">
        <template x-for="item in ((window.__projectMaterials[activeProject] || {}).items || [])" :key="item.name">
          <div class="pb-4 border-b border-slate-100 last:border-0 last:pb-0">
            <div class="flex justify-between items-start gap-4 mb-1.5">
              <span class="text-sm font-bold text-slate-800" x-text="item.name"></span>
              <span class="text-sm font-bold text-slate-900 shrink-0" x-text="item.quantity + ' pcs'"></span>
            </div>

            <div class="flex flex-wrap gap-1.5 mb-1.5">
              <template x-for="loc in item.locations" :key="loc.location">
                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-slate-500 bg-slate-50 border border-slate-200 px-2 py-0.5 rounded-lg">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span x-text="loc.location"></span>
                  <span class="text-slate-400">&middot;</span>
                  <span x-text="loc.quantity + ' pcs'"></span>
                </span>
              </template>
            </div>

            <p class="text-xs text-slate-400">
              <span x-text="'₱' + item.unit_cost.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
              <span> / unit &middot; </span>
              <span class="font-semibold text-slate-500" x-text="'₱' + item.total_value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' total'"></span>
            </p>
          </div>
        </template>

        <template x-if="!((window.__projectMaterials[activeProject] || {}).items || []).length">
          <div class="text-center py-8">
            <p class="text-sm font-bold text-slate-600">No materials distributed yet.</p>
            <p class="text-xs text-slate-400 mt-1">Items released to this project from Distribution will show up here.</p>
          </div>
        </template>
      </div>
    </div>
  </div>

</div>
@endsection