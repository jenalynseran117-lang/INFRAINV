@extends('layouts.Supply.app')

@section('content')
<input type="hidden" id="distCsrfToken" value="{{ csrf_token() }}">
<script>
  // Real combined warehouse stock (summed across ALL storage locations), from SupplyController@DistributionIndex
  window.__distAvailableStock = @json($availableStock ?? []);
  // Real Projects, from the `projects` table
  window.__distProjects = @json($projects ?? []);
</script>

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

<div class="relative w-full min-h-screen rounded-3xl overflow-hidden bg-gradient-to-br from-[#f9f4f2] via-[#f6efec] to-[#f2e8e5] p-4 md:p-8 antialiased font-body"
  x-data="{
        items: (window.__distAvailableStock || []).map(g => ({
            id: g.id,
            name: g.name,
            totalStock: g.totalQty,
            // One row per storage location this item currently sits in.
            // `available` = how much is there, `release` = what the user
            // is choosing to pull FROM THAT SPECIFIC WAREHOUSE.
            locations: Object.values((g.sources || []).reduce((acc, s) => {
                const loc = s.location || 'Unknown';
                if (!acc[loc]) acc[loc] = { location: loc, available: 0, release: '' };
                acc[loc].available += s.qty;
                return acc;
            }, {}))
        })),
        projects: window.__distProjects || [],
        selectedProject: '',
        submitting: false,

        showAddProjectModal: false,
        newProjectName: '',
        newProjectLocation: '',
        newProjectBudget: '',
        savingProject: false,

        itemReleaseTotal(item) {
            return item.locations.reduce((sum, l) => sum + (l.release ? parseInt(l.release) : 0), 0);
        },

        isActive(item) {
            return this.itemReleaseTotal(item) > 0;
        },

        countActive() {
            return this.items.filter(i => this.isActive(i)).length;
        },

        // Hindi pwedeng lumagpas sa available ng SPECIFIC warehouse na 'yon, at hindi pwedeng negative.
        clampLocQty(loc) {
            let val = loc.release;
            if (val === '' || val === null) return;
            val = parseInt(val);
            if (isNaN(val) || val < 0) val = 0;
            if (val > loc.available) val = loc.available;
            loc.release = val === 0 ? '' : val;
        },

        async addProject() {
            if (this.newProjectName.trim() === '' || this.savingProject) return;
            this.savingProject = true;
            try {
                const res = await fetch('{{ route('supply.Project.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.getElementById('distCsrfToken').value,
                    },
                    body: JSON.stringify({
                        name: this.newProjectName,
                        location: this.newProjectLocation,
                        budget: this.newProjectBudget || null,
                    }),
                });
                if (!res.ok) throw new Error('failed');
                const project = await res.json();

                this.projects.push(project);
                this.selectedProject = project.id;

                this.newProjectName = '';
                this.newProjectLocation = '';
                this.newProjectBudget = '';
                this.showAddProjectModal = false;
            } catch (e) {
                alert('Failed to add project. Please try again.');
            } finally {
                this.savingProject = false;
            }
        },

        // Builds a printable receipt (item / warehouse pulled from / qty)
        // and immediately opens the browser print dialog for it.
        // NOTE: this whole block lives inside an HTML attribute delimited
        // by double quote characters, so nothing here may contain a
        // literal double quote character or it will break out early and
        // leak raw markup onto the page — single quotes only, everywhere.
        printReleaseReceipt(data) {
            const rows = (data.items || []).map(i => `
                <tr>
                  <td style='padding:8px;border-bottom:1px solid #e2e8f0;'>${i.name}</td>
                  <td style='padding:8px;border-bottom:1px solid #e2e8f0;'>${i.location}</td>
                  <td style='padding:8px;border-bottom:1px solid #e2e8f0;text-align:right;'>${i.quantity}</td>
                </tr>
            `).join('');

            const html = `
                <html>
                <head>
                  <title>Outflow Receipt</title>
                  <style>
                    body { font-family: Arial, sans-serif; padding: 32px; color: #1e293b; }
                    h1 { font-size: 18px; margin-bottom: 4px; }
                    p { font-size: 12px; color: #64748b; margin: 2px 0; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th { text-align: left; font-size: 11px; text-transform: uppercase; color: #94a3b8; padding: 8px; border-bottom: 2px solid #1e293b; }
                  </style>
                </head>
                <body>
                  <h1>Distribution &amp; Outflow Receipt</h1>
                  <p>Project: ${data.project_name || '-'}</p>
                  <p>Released: ${data.released_at || ''}</p>
                  <p>Reference #: ${data.distribution_id || ''}</p>
                  <table>
                    <thead>
                      <tr><th>Item</th><th>Warehouse / Location</th><th style='text-align:right;'>Qty Released</th></tr>
                    </thead>
                    <tbody>${rows}</tbody>
                  </table>
                </body>
                </html>
            `;

            const printWindow = window.open('', '_blank', 'width=800,height=600');
            if (!printWindow) {
                alert('Please allow pop-ups to print the outflow receipt.');
                return;
            }
            printWindow.document.write(html);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        },

        async confirmOutflow() {
            // Flatten: only the warehouse rows where the user actually typed a qty.
            const payloadItems = this.items.flatMap(item =>
                item.locations
                    .filter(loc => loc.release !== '' && parseInt(loc.release) > 0)
                    .map(loc => ({ name: item.name, location: loc.location, quantity: parseInt(loc.release) }))
            );

            if (payloadItems.length === 0 || !this.selectedProject || this.submitting) return;
            this.submitting = true;
            try {
                const res = await fetch('{{ route('supply.Distribution.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.getElementById('distCsrfToken').value,
                    },
                    body: JSON.stringify({
                        project_id: this.selectedProject,
                        items: payloadItems,
                    }),
                });
                if (!res.ok) throw new Error('failed');
                const data = await res.json();

                this.printReleaseReceipt(data);
                window.location.reload();
            } catch (e) {
                alert('Failed to release items. Please try again.');
                this.submitting = false;
            }
        }
     }">

  {{-- Ambient glow blobs --}}
  <div class="pointer-events-none absolute inset-0 overflow-hidden">
    <div class="glass-blob absolute -top-24 -left-16 h-80 w-80 rounded-full bg-red-300/20 blur-3xl"></div>
    <div class="glass-blob absolute top-1/3 -right-10 h-96 w-96 rounded-full bg-blue-200/20 blur-3xl" style="animation-delay:-5s"></div>
    <div class="glass-blob absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-emerald-200/20 blur-3xl" style="animation-delay:-9s"></div>
  </div>

  <div class="relative z-10">

    {{-- Header --}}
    <div class="max-w-[1400px] mx-auto mb-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-3xl font-black text-gray-800 tracking-tight font-display">Distribution & Outflow</h1>
        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-1">Inventory Management</p>
      </div>
      <div class="flex items-center gap-2 bg-white/70 backdrop-blur-xl px-4 py-2.5 rounded-2xl border border-white/70 shadow-[0_8px_32px_rgba(31,41,55,0.08)]">
        <span class="relative flex h-3 w-3">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
        </span>
        <span class="text-xs font-bold text-gray-700 tracking-wider uppercase">System Live: Warehouse-Alpha</span>
      </div>
    </div>

    {{-- Main Layout Grid --}}
    <div class="max-w-[1400px] mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

      {{-- KALIWANG COLUMN: Listahan ng mga Available Stock --}}
      <div class="lg:col-span-8 space-y-6">
        <div class="flex justify-between items-center px-2">
          <h2 class="text-xs font-black text-gray-600 uppercase tracking-widest">Available Warehouse Stock</h2>
          <span class="text-xs font-bold text-red-700 bg-red-100 px-2.5 py-1 rounded-xl">
            <span x-text="countActive()"></span> Item(s) Selected to Deduct
          </span>
        </div>

        <div class="space-y-4">
          <template x-for="item in items" :key="item.id">
            {{-- DESIGN TRICK: Mag-h-highlight (border-red-400, ring, and slight scale) kung may ilalagay na quantity --}}
            <div :class="isActive(item) ? 'border-red-400 ring-2 ring-red-100 shadow-md' : 'border-white/70 bg-white/70'"
              class="item-card p-6 backdrop-blur-xl rounded-[2.5rem] border shadow-sm transition-all duration-300">

              <div class="flex items-center gap-4 mb-4">
                {{-- Icon Box --}}
                <div :class="isActive(item) ? 'bg-gradient-to-br from-red-500 to-rose-600 text-white shadow-md shadow-red-500/30' : 'bg-gray-100 text-gray-400'"
                  class="h-14 w-14 rounded-[1.5rem] flex items-center justify-center transition-all duration-300 shrink-0">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 11m8 4V4M4 11v10l8 4" />
                  </svg>
                </div>

                {{-- Item Text Details --}}
                <div class="min-w-0">
                  <h3 class="font-bold text-gray-800 text-lg" x-text="item.name"></h3>
                  <span class="text-gray-600 text-xs font-bold" x-text="item.totalStock + ' units total across all warehouses'"></span>
                </div>

                {{-- Live total of what's being released for this item --}}
                <span class="ml-auto shrink-0 text-xs font-bold text-white bg-gradient-to-r from-red-500 to-rose-600 px-3 py-1.5 rounded-lg shadow-sm"
                  x-show="isActive(item)" x-text="'Releasing ' + itemReleaseTotal(item)"></span>
              </div>

              {{-- Per-warehouse rows --}}
              <div class="grid gap-2 sm:grid-cols-2">
                <template x-for="loc in item.locations" :key="loc.location">
                  <div :class="loc.release && parseInt(loc.release) > 0 ? 'border-red-300 bg-red-50/60' : 'border-gray-200 bg-gray-50/70'"
                    class="flex items-center justify-between gap-3 border rounded-xl px-3 py-2 transition-all">
                    <div class="flex items-center gap-2 min-w-0">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                      <div class="min-w-0">
                        <p class="text-xs font-bold text-gray-800 truncate" x-text="loc.location"></p>
                        <p class="text-[10px] text-gray-500 font-bold" x-text="loc.available + ' pcs available'"></p>
                      </div>
                    </div>
                    <input type="number"
                      x-model="loc.release"
                      @input="clampLocQty(loc)"
                      @blur="clampLocQty(loc)"
                      min="0"
                      :max="loc.available"
                      placeholder="0"
                      class="w-14 shrink-0 bg-white border border-gray-300 rounded-lg text-right font-black text-gray-800 outline-none text-sm px-2 py-1 focus:ring-2 focus:ring-red-400 focus:border-red-400 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                  </div>
                </template>
              </div>
            </div>
          </template>

          <template x-if="items.length === 0">
            <div class="bg-white/50 backdrop-blur-xl rounded-3xl border border-dashed border-gray-300/70 p-8 text-center">
              <p class="text-sm font-bold text-gray-700">No warehouse stock available right now.</p>
              <p class="text-xs text-gray-500 font-medium mt-1">Items appear here once they're placed into a storage location.</p>
            </div>
          </template>
        </div>
      </div>

      {{-- KANANG COLUMN: Target Destination Setup --}}
      <div class="lg:col-span-4 bg-white/70 backdrop-blur-xl rounded-3xl border border-white/70 p-6 shadow-[0_8px_32px_rgba(31,41,55,0.08)] sticky top-6">
        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 font-display">Deployment Destination</h3>

        <div class="mb-6">
          <label class="block text-xs font-bold text-gray-600 uppercase tracking-widest mb-2">Select Target Project</label>
          <div class="flex gap-2 items-center">
            <select x-model="selectedProject" class="flex-1 min-w-0 px-4 py-3 bg-white border border-gray-300 rounded-xl text-sm text-gray-800 font-bold focus:ring-2 focus:ring-red-400 focus:border-red-400 outline-none transition-all">
              <option value="" disabled selected>Choose destination project...</option>
              <template x-for="p in projects" :key="p.id">
                <option :value="p.id" x-text="p.name"></option>
              </template>
            </select>

            <button type="button" @click="showAddProjectModal = true"
              class="p-3 bg-gray-800 hover:bg-gray-900 text-white rounded-xl transition-all shrink-0" title="Add project">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
              </svg>
            </button>
          </div>
        </div>

        {{-- Preview Panel --}}
        <div class="mb-6 p-4 bg-gray-50/70 rounded-2xl border border-gray-200/70" x-show="countActive() > 0" x-transition>
          <h4 class="text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-2">Outflow Summary Preview</h4>
          <div class="space-y-2">
            <template x-for="item in items" :key="item.id">
              <template x-for="loc in item.locations" :key="item.id + '-' + loc.location">
                <div class="flex justify-between text-xs font-bold text-gray-700" x-show="loc.release && parseInt(loc.release) > 0">
                  <span x-text="item.name + ' — ' + loc.location"></span>
                  <span class="font-bold text-red-600" x-text="'-' + loc.release"></span>
                </div>
              </template>
            </template>
          </div>
        </div>

        {{-- Button --}}
        <button type="button"
          @click="confirmOutflow()"
          :disabled="countActive() === 0 || !selectedProject || submitting"
          :class="(countActive() > 0 && selectedProject && !submitting) ? 'bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-400 hover:to-rose-500 text-white cursor-pointer shadow-md shadow-red-500/25 hover:shadow-lg hover:-translate-y-0.5' : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
          class="w-full font-bold py-3.5 rounded-xl transition-all duration-300 text-xs uppercase tracking-widest text-center">
          <span x-text="submitting ? 'Processing...' : 'Confirm Outflow & Deduct'"></span>
        </button>
        <p class="text-[10px] text-gray-500 font-medium text-center mt-2">A printable receipt will open automatically after confirming.</p>
      </div>

    </div>

    {{-- Add Project Modal --}}
    <div x-show="showAddProjectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm" x-cloak>
      <div class="bg-white/90 backdrop-blur-2xl p-6 rounded-2xl shadow-2xl border border-white/70 w-full max-w-sm mx-4" @click.away="showAddProjectModal = false">
        <h3 class="text-sm font-bold text-gray-800 mb-4 font-display">Add New Project</h3>

        <div class="space-y-4">
          <div>
            <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-wide mb-1">Project Name</label>
            <input type="text" x-model="newProjectName" placeholder="e.g., Barangay Road Improvement"
              class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-400 focus:border-red-400 outline-none transition-all text-sm">
          </div>

          <div>
            <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-wide mb-1">Location <span class="normal-case text-gray-400">(optional)</span></label>
            <input type="text" x-model="newProjectLocation"
              class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-400 focus:border-red-400 outline-none transition-all text-sm">
          </div>

          <div>
            <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-wide mb-1">Budget (₱) <span class="normal-case text-gray-400">(optional)</span></label>
            <input type="number" x-model="newProjectBudget" min="0" step="0.01"
              class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-400 focus:border-red-400 outline-none transition-all text-sm">
          </div>
        </div>

        <div class="flex justify-end gap-2 text-xs font-bold pt-5">
          <button type="button" @click="showAddProjectModal = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
          <button type="button" @click="addProject()" :disabled="newProjectName.trim() === '' || savingProject"
            class="px-4 py-2 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-400 hover:to-rose-500 text-white rounded-lg shadow-md shadow-red-500/25 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
            <span x-text="savingProject ? 'Saving...' : 'Save Project'"></span>
          </button>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection