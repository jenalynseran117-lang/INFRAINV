@extends('layouts.Supply.app')

@section('content')
<input type="hidden" id="whCsrfToken" value="{{ csrf_token() }}">

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

<script>
  // Real data from approved-but-not-yet-warehoused items, supplied by SupplyController@WHIndex
  window.__whRawArrivals = @json(collect($newArrivals ?? [])->values());
  // Real storage locations that have actually been used, plus any added client-side via the modal
  window.__whAvailableStorages = @json($availableStorages ?? []);
  // Real current stock per location — same data the "Stocks by Storage Location" cards use,
  // now hydrated into Alpine state so a new placement can update it instantly.
  window.__whStockByLocation = @json($stockByLocation ?? []);

  // ------- Custom confirm/alert pop-up (replaces native browser dialogs) -------
  let appDialogResolver = null;

  function showAppDialog({
    type = 'confirm',
    title,
    message,
    okText = 'Continue'
  }) {
    return new Promise((resolve) => {
      appDialogResolver = resolve;

      document.getElementById('appDialogTitle').innerText = title;
      document.getElementById('appDialogMessage').innerText = message;

      const okBtn = document.getElementById('appDialogOkBtn');
      const cancelBtn = document.getElementById('appDialogCancelBtn');

      okBtn.innerText = okText;
      cancelBtn.classList.toggle('hidden', type === 'alert' || type === 'error');

      okBtn.className = type === 'error' ?
        'px-5 py-2.5 bg-red-600 text-white rounded-full font-bold text-sm hover:bg-red-700 transition' :
        'px-5 py-2.5 bg-gray-900 text-white rounded-full font-bold text-sm hover:bg-black transition';

      document.getElementById('appDialog').classList.remove('hidden');
    });
  }

  function closeAppDialog(result) {
    document.getElementById('appDialog').classList.add('hidden');
    if (appDialogResolver) {
      appDialogResolver(result);
      appDialogResolver = null;
    }
  }

  function showAppConfirm(message, title = 'Please confirm') {
    return showAppDialog({
      type: 'confirm',
      title,
      message,
      okText: 'Continue'
    });
  }

  function showAppAlert(message, title = 'Notice') {
    return showAppDialog({
      type: 'alert',
      title,
      message,
      okText: 'Got it'
    });
  }

  function showAppError(message, title = 'Something went wrong') {
    return showAppDialog({
      type: 'error',
      title,
      message,
      okText: 'Got it'
    });
  }
  // -------------------------------------------------------------------------------

  document.addEventListener('alpine:init', () => {
    Alpine.data('warehouseStock', () => ({
      rawArrivals: window.__whRawArrivals || [],
      // groups[] is built from rawArrivals below: same item name -> merged into one card
      // with quantities summed as NUMBERS and every underlying approval kept in .sources
      groups: [],
      storages: window.__whAvailableStorages || [],
      stockByLocation: window.__whStockByLocation || {},
      selectedItems: [],
      bulkStorageLocation: '',
      showAddStorageModal: false,
      newStorageName: '',
      submitting: false,
      searchQuery: '',
      warehouseFilter: '',

      init() {
        this.buildGroups();
      },

      normalizeKey(name) {
        return String(name)
          .replace(/\u00A0/g, ' ') // non-breaking space -> normal space
          .trim()
          .toLowerCase()
          .replace(/\s+/g, ' ');
      },

      buildGroups() {
        const map = {};
        this.rawArrivals.forEach(item => {
          const key = this.normalizeKey(item.name);
          const qty = Number(item.qty) || 0; // force numeric — fixes "010"/"01010" concatenation

          if (!map[key]) {
            map[key] = {
              id: key,
              name: item.name.trim(),
              qty: 0,
              qtyToStore: 0,
              sources: []
            };
          }
          map[key].qty += qty;
          map[key].qtyToStore += qty;
          map[key].sources.push({
            po_id: item.po_id,
            item_index: item.item_index,
            qty: qty,
            supplier: item.supplier,
            inspector: item.inspector,
            verified_at: item.verified_at
          });
        });
        this.groups = Object.values(map);
      },

      addStorage() {
        if (this.newStorageName.trim() === '') return;
        const newId = this.storages.length + 1;
        this.storages.push({
          id: newId,
          name: this.newStorageName
        });
        this.bulkStorageLocation = this.newStorageName;
        this.newStorageName = '';
        this.showAddStorageModal = false;
      },

      // "Stocks by Storage Location" filtering:
      //  - warehouseFilter narrows it down to a single location (blank = all).
      //  - searchQuery narrows the item names shown within each location left standing.
      // A location card only shows if it still has at least one matching item after both filters.
      filteredStockByLocation() {
        const query = this.searchQuery.trim().toLowerCase();
        const result = {};
        Object.entries(this.stockByLocation).forEach(([location, data]) => {
          if (this.warehouseFilter && location !== this.warehouseFilter) return;

          const items = Object.fromEntries(
            Object.entries(data.items).filter(([name]) => !query || name.toLowerCase().includes(query))
          );
          if (Object.keys(items).length === 0) return;

          result[location] = {
            items,
            last_updated: data.last_updated
          };
        });
        return result;
      },

      // Small relative-time formatter so "Stocks by Storage Location" can show
      // "Last updated: X ago" reactively, without needing a page reload to get
      // a fresh Carbon::diffForHumans() from the server.
      timeAgo(dateStr) {
        if (!dateStr) return '—';
        const iso = dateStr.includes('T') ? dateStr : dateStr.replace(' ', 'T');
        const diffSec = Math.floor((Date.now() - new Date(iso).getTime()) / 1000);
        if (diffSec < 5) return 'just now';
        if (diffSec < 60) return diffSec + ' seconds ago';
        const mins = Math.floor(diffSec / 60);
        if (mins < 60) return mins + ' minute' + (mins === 1 ? '' : 's') + ' ago';
        const hrs = Math.floor(mins / 60);
        if (hrs < 24) return hrs + ' hour' + (hrs === 1 ? '' : 's') + ' ago';
        const days = Math.floor(hrs / 24);
        return days + ' day' + (days === 1 ? '' : 's') + ' ago';
      },

      async processBulkAssignment() {
        if (this.selectedItems.length === 0) {
          await showAppAlert('Please select items first using the checkboxes.', 'No items selected');
          return;
        }
        if (!this.bulkStorageLocation) {
          await showAppAlert('Please choose a specific storage location where they will be placed.', 'Storage location required');
          return;
        }

        const selectedGroups = this.groups.filter(g => this.selectedItems.includes(g.id));
        const invalidQty = selectedGroups.find(g => !g.qtyToStore || g.qtyToStore < 1 || g.qtyToStore > g.qty);
        if (invalidQty) {
          await showAppAlert('Please enter a valid quantity (1 to ' + invalidQty.qty + ') for every selected item.', 'Invalid quantity');
          return;
        }

        // Break each merged group back down into its real po_id/item_index rows,
        // filling the oldest approvals first, up to the qty the user asked to store.
        // Keep the item name alongside each row too — needed below to update the
        // "Stocks by Storage Location" cards instantly.
        const items = [];
        selectedGroups.forEach(g => {
          let remaining = g.qtyToStore;
          g.sources.forEach(src => {
            if (remaining <= 0) return;
            const take = Math.min(src.qty, remaining);
            if (take > 0) {
              items.push({
                po_id: src.po_id,
                item_index: src.item_index,
                quantity: take,
                name: g.name
              });
              remaining -= take;
            }
          });
        });

        this.submitting = true;

        try {
          const res = await fetch('/supply/warehouse/assign', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.getElementById('whCsrfToken').value,
            },
            body: JSON.stringify({
              storage_location: this.bulkStorageLocation,
              items: items.map(({
                po_id,
                item_index,
                quantity
              }) => ({
                po_id,
                item_index,
                quantity
              }))
            })
          });

          const data = await res.json();

          if (!res.ok) {
            await showAppError(data.message || 'Something went wrong while assigning items.');
            this.submitting = false;
            return;
          }

          // Decrement each placed row's remaining qty instead of deleting it
          // outright — a row only leaves the New Arrivals queue once every
          // approved unit has actually been placed somewhere (qty hits 0).
          const placedByKey = new Map(items.map(i => [i.po_id + '_' + i.item_index, i.quantity]));
          this.rawArrivals = this.rawArrivals
            .map(item => {
              const key = item.po_id + '_' + item.item_index;
              if (!placedByKey.has(key)) return item;
              return {
                ...item,
                qty: item.qty - placedByKey.get(key)
              };
            })
            .filter(item => item.qty > 0);
          this.buildGroups();

          // Reflect the new placement in "Stocks by Storage Location" right away.
          const loc = this.bulkStorageLocation;
          if (!this.stockByLocation[loc]) {
            this.stockByLocation[loc] = {
              items: {},
              last_updated: null
            };
          }
          items.forEach(i => {
            const current = this.stockByLocation[loc].items[i.name] || 0;
            this.stockByLocation[loc].items[i.name] = current + i.quantity;
          });
          this.stockByLocation[loc].last_updated = new Date().toISOString();

          await showAppAlert(data.message || 'Items placed successfully!', 'Placed successfully');
          this.selectedItems = [];
          this.bulkStorageLocation = '';
        } catch (e) {
          await showAppError('Network error — please try again.');
        } finally {
          this.submitting = false;
        }
      }
    }));
  });
</script>

<div class="relative w-full min-h-screen rounded-3xl overflow-hidden bg-gradient-to-br from-[#f9f4f2] via-[#f6efec] to-[#f2e8e5] p-4 md:p-8 antialiased font-body" x-data="warehouseStock()">

  {{-- Ambient glow blobs --}}
  <div class="pointer-events-none absolute inset-0 overflow-hidden">
    <div class="glass-blob absolute -top-24 -left-16 h-80 w-80 rounded-full bg-red-300/20 blur-3xl"></div>
    <div class="glass-blob absolute top-1/3 -right-10 h-96 w-96 rounded-full bg-blue-200/20 blur-3xl" style="animation-delay:-5s"></div>
    <div class="glass-blob absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-amber-200/20 blur-3xl" style="animation-delay:-9s"></div>
  </div>

  <div class="relative z-10 max-w-[1400px] mx-auto">

    {{-- CUSTOM CONFIRM / ALERT POP-UP --}}
    <div id="appDialog" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[80] hidden flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
        <div class="p-6">
          <h3 id="appDialogTitle" class="text-base font-bold text-gray-800 mb-2 font-display">Title</h3>
          <p id="appDialogMessage" class="text-sm text-gray-500 font-medium leading-relaxed">Message</p>
        </div>
        <div class="px-6 pb-6 pt-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
          <button id="appDialogCancelBtn" type="button" onclick="closeAppDialog(false)"
            class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-full font-bold text-sm hover:bg-gray-100 transition">
            Cancel
          </button>
          <button id="appDialogOkBtn" type="button" onclick="closeAppDialog(true)"
            class="px-5 py-2.5 bg-gray-900 text-white rounded-full font-bold text-sm hover:bg-black transition">
            Continue
          </button>
        </div>
      </div>
    </div>

    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-800 tracking-tight font-display">Available Stock</h1>
      <p class="text-sm text-gray-600 font-medium mt-1">Place newly approved items into storage and track what's on hand</p>
    </div>

    <template x-if="groups.length > 0">
      <div class="mb-10 bg-white/60 backdrop-blur-xl rounded-3xl border border-amber-200/70 p-6 shadow-[0_8px_32px_rgba(31,41,55,0.08)]">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
          <div class="flex items-center gap-2.5">
            <span class="relative flex h-3 w-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
            </span>
            <h2 class="text-lg font-bold text-gray-800 font-display">New Items Arrived from Inspector</h2>
          </div>
          <div class="text-xs font-bold text-amber-800 bg-amber-100 px-3 py-1.5 rounded-xl">
            <span x-text="selectedItems.length"></span> out of <span x-text="groups.length"></span> items selected
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <template x-for="item in groups" :key="item.id">
            <div :class="selectedItems.includes(item.id) ? 'border-red-400 ring-2 ring-red-100 bg-red-50/40' : 'border-amber-200/70 bg-white/70'"
              class="relative rounded-2xl p-5 border backdrop-blur-xl shadow-sm flex items-start gap-4 select-none transition-all hover:shadow-md">

              <label :for="'check-' + item.id" class="flex items-center h-5 mt-1 cursor-pointer">
                <input :id="'check-' + item.id" type="checkbox" :value="item.id" x-model="selectedItems"
                  class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500 transition-all">
              </label>

              <div class="flex-1">
                <div class="flex justify-between items-start gap-2 mb-1.5">
                  <h3 class="font-bold text-gray-800 text-sm md:text-base" x-text="item.name"></h3>
                  <span class="bg-gray-800 text-white text-xs font-bold px-2.5 py-1 rounded-lg shrink-0" x-text="item.qty + ' pcs approved'"></span>
                </div>

                <div class="text-[11px] text-gray-500 leading-relaxed mb-3 space-y-0.5 font-medium">
                  <template x-for="src in item.sources" :key="src.po_id + '_' + src.item_index">
                    <div>
                      <span class="font-bold text-gray-600" x-text="src.qty"></span> pcs from <span class="text-gray-700 font-bold" x-text="src.supplier || 'Unknown supplier'"></span>
                      <span class="text-gray-300">•</span> Verified: <span class="text-gray-700 font-bold" x-text="src.inspector"></span>
                      <span class="text-gray-300">•</span> <span x-text="src.verified_at || 'Just now'"></span>
                    </div>
                  </template>
                </div>

                <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-wide mb-1.5">Qty to Store</label>
                <input type="number" min="1" :max="item.qty" x-model.number="item.qtyToStore"
                  class="w-24 px-2 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-bold text-gray-800 focus:ring-2 focus:ring-red-400 focus:border-red-400 outline-none transition-all">
              </div>
            </div>
          </template>
        </div>

        <div class="p-4 bg-white/70 backdrop-blur-xl rounded-2xl border border-amber-200/60 flex flex-col sm:flex-row gap-4 items-center justify-between shadow-sm">
          <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <div class="flex gap-2 items-center w-full sm:w-64">
              <select x-model="bulkStorageLocation" class="w-full px-3 py-2.5 bg-white border border-gray-300 rounded-xl text-xs text-gray-800 font-bold focus:ring-2 focus:ring-red-400 focus:border-red-400 outline-none transition-all">
                <option value="">Choose storage location...</option>
                <template x-for="storage in storages" :key="storage.id">
                  <option :value="storage.name" x-text="storage.name"></option>
                </template>
              </select>

              <button type="button" @click="showAddStorageModal = true" class="p-2.5 bg-gray-800 hover:bg-gray-900 text-white rounded-xl transition-all shrink-0" title="Add location">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
              </button>
            </div>
          </div>

          <div class="flex gap-2 w-full sm:w-auto justify-end shrink-0">
            <button type="button" @click="processBulkAssignment()"
              :disabled="selectedItems.length === 0 || submitting"
              class="px-6 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-400 hover:to-rose-500 text-white font-bold rounded-xl text-xs transition-all duration-300 tracking-wide shadow-md shadow-red-500/25 hover:shadow-lg hover:-translate-y-0.5 disabled:opacity-40 disabled:hover:translate-y-0 disabled:shadow-none w-full sm:w-auto text-center">
              <span x-text="submitting ? 'Placing...' : 'Assign Selected Items'"></span>
            </button>
          </div>
        </div>

      </div>
    </template>

    <template x-if="groups.length === 0">
      <div class="mb-10 bg-white/50 backdrop-blur-xl rounded-3xl border border-dashed border-gray-300/70 p-8 text-center">
        <p class="text-sm font-bold text-gray-700">No approved items waiting for placement right now.</p>
        <p class="text-xs text-gray-500 font-medium mt-1">Items appear here once the inspector approves them.</p>
      </div>
    </template>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
      <div class="md:col-span-2 relative">
        <input type="text" x-model="searchQuery" placeholder="Search items..." class="w-full bg-white/70 backdrop-blur-xl border border-white/70 rounded-xl px-5 py-3 pl-12 text-sm text-gray-800 font-medium placeholder-gray-400 focus:ring-2 focus:ring-red-400 focus:border-red-400 outline-none transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-4 top-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>

      <select x-model="warehouseFilter" class="bg-white/70 backdrop-blur-xl border border-white/70 rounded-xl px-4 py-3 text-sm text-gray-800 font-bold focus:ring-2 focus:ring-red-400 focus:border-red-400 outline-none">
        <option value="">All Warehouses</option>
        <template x-for="storage in storages" :key="storage.id">
          <option :value="storage.name" x-text="storage.name"></option>
        </template>
      </select>
    </div>

    <div class="border-t border-gray-300/60 pt-8">
      <h2 class="text-xl font-bold text-gray-800 mb-6 font-display">Stocks by Storage Location</h2>

      <template x-if="Object.keys(filteredStockByLocation()).length > 0">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <template x-for="[location, data] in Object.entries(filteredStockByLocation())" :key="location">
            <div class="bg-white/70 backdrop-blur-xl rounded-2xl border border-white/70 p-6 shadow-[0_8px_32px_rgba(31,41,55,0.08)]">
              <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-200/70">
                <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2 font-display">
                  <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                  <span x-text="location"></span>
                </h3>
                <span class="text-[11px] font-bold text-gray-500" x-text="'Last updated: ' + timeAgo(data.last_updated)"></span>
              </div>
              <div class="space-y-2.5">
                <template x-for="[itemName, qty] in Object.entries(data.items)" :key="itemName">
                  <div class="flex justify-between items-center text-sm p-3 bg-gray-50/70 rounded-xl">
                    <span class="font-bold text-gray-800" x-text="itemName"></span>
                    <span class="text-xs text-white font-bold bg-gray-700 px-2.5 py-1 rounded-lg" x-text="qty + ' pcs'"></span>
                  </div>
                </template>
              </div>
            </div>
          </template>
        </div>
      </template>

      <template x-if="Object.keys(filteredStockByLocation()).length === 0">
        <div class="bg-white/50 backdrop-blur-xl rounded-3xl border border-dashed border-gray-300/70 p-8 text-center">
          <template x-if="searchQuery || warehouseFilter">
            <div>
              <p class="text-sm font-bold text-gray-700">No matching stock found.</p>
              <p class="text-xs text-gray-500 font-medium mt-1">Try a different search term or warehouse.</p>
            </div>
          </template>
          <template x-if="!searchQuery && !warehouseFilter">
            <div>
              <p class="text-sm font-bold text-gray-700">No stock placed yet.</p>
              <p class="text-xs text-gray-500 font-medium mt-1">Items appear here once they're assigned to a storage location.</p>
            </div>
          </template>
        </div>
      </template>
    </div>

    <div x-show="showAddStorageModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm" x-cloak>
      <div class="bg-white/90 backdrop-blur-2xl p-6 rounded-2xl shadow-2xl border border-white/70 w-full max-w-sm mx-4" @click.away="showAddStorageModal = false">
        <h3 class="text-sm font-bold text-gray-800 mb-3 font-display">Add New Storage Location</h3>
        <input type="text" x-model="newStorageName" placeholder="e.g., Supply Storage B, Depot"
          class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-400 focus:border-red-400 outline-none transition-all text-sm mb-4">

        <div class="flex justify-end gap-2 text-xs font-bold">
          <button type="button" @click="showAddStorageModal = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
          <button type="button" @click="addStorage()" class="px-4 py-2 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-400 hover:to-rose-500 text-white rounded-lg shadow-md shadow-red-500/25 transition-all">Save Location</button>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection