@extends('layouts.Inspector.app')

@section('content')
@php
    // Dynamically calculate counts directly from the collection
    $forInspectionCount = $purchaseOrders->filter(fn($po) => in_array(strtolower($po->status ?? ''), ['pending', 'pending_inspection', 'for_inspection', 'ready_for_inspection', '']) || is_null($po->status))->count();
    $approvedCount      =$purchaseOrders->filter(fn($po) => strtolower($po->status ?? '') === 'approved')->count();

    // Per-PO status map fed into an Alpine store below. When an item is
    // approved via AJAX and the server reports the PO as fully approved,
    // we flip this map client-side so the card/tab/counters move to
    // "Approved" instantly — no page refresh needed.
    $initialPoStatuses = $purchaseOrders->mapWithKeys(function ($po) {
        $status = strtolower($po->status ?? '');
        return [$po->id => $status !== '' ? $status : 'pending'];
    });

    // Per-PO "is there anything to actually decide right now" map. A PO can
    // sit in the list with a delivery photo already submitted, yet have
    // nothing actionable — every delivered batch so far may already be
    // approved, with the item just waiting on Supply for the next batch.
    // We surface that on the card itself so the Inspector doesn't waste a
    // click opening "Inspect Details" for nothing.
    $initialPoActionable = $purchaseOrders->mapWithKeys(function ($po) {
        $items = is_array($po->items) ? $po->items : json_decode($po->items ?? '[]', true);

        $hasActionable = collect($items)->contains(function ($item) {
            if (empty($item['delivery_photo'] ?? null)) {
                return false;
            }
            $deliveredQty = (isset($item['deliveries']) && is_array($item['deliveries']))
                ? collect($item['deliveries'])->sum(fn ($d) => (float) ($d['quantity'] ?? 0))
                : (float) ($item['inspected_quantity'] ?? 0);
            $approvedQty = (float) ($item['approved_quantity'] ?? 0);

            return ($deliveredQty - $approvedQty) > 0;
        });

        return [$po->id => $hasActionable];
    });
@endphp

<div class="p-8 bg-[#f8fafc] min-h-screen font-sans" x-data="{}">

    <header class="mb-10 flex justify-between items-end">
        <div>
            <h1 class="text-4xl font-black text-[#283E70] tracking-tighter mb-2">
                Inspection Portal
            </h1>
            <p class="text-base text-gray-500 font-medium">
                Verify and inspect the details from the submitted Purchase Orders
            </p>
        </div>
      
    </header>

    {{-- STATS OVERVIEW CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div @click="$store.inspector.currentTab = 'for_inspection'" 
             :class="$store.inspector.currentTab === 'for_inspection' ? 'ring-2 ring-amber-500 bg-amber-50/50' : 'bg-white'"
             class="p-6 rounded-2xl border border-gray-100 shadow-sm cursor-pointer transition-all hover:shadow-md">
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs font-black uppercase tracking-wider text-amber-600">For Inspection</span>
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
            </div>
            <div class="text-3xl font-black text-gray-800" x-text="$store.inspector.forInspectionCount()">{{ $forInspectionCount }}</div>
            <p class="text-xs font-semibold text-gray-400 mt-1">Pending review</p>
        </div>

        <div @click="$store.inspector.currentTab = 'approved'" 
             :class="$store.inspector.currentTab === 'approved' ? 'ring-2 ring-emerald-500 bg-emerald-50/50' : 'bg-white'"
             class="p-6 rounded-2xl border border-gray-100 shadow-sm cursor-pointer transition-all hover:shadow-md">
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs font-black uppercase tracking-wider text-emerald-600">Approved</span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            </div>
            <div class="text-3xl font-black text-gray-800" x-text="$store.inspector.approvedCount()">{{ $approvedCount }}</div>
            <p class="text-xs font-semibold text-gray-400 mt-1">Passed inspection</p>
        </div>
    </div>

    {{-- FILTER TABS --}}
    <div class="flex gap-2 mb-6 border-b border-gray-200 pb-3">
        <button @click="$store.inspector.currentTab = 'all'" 
                :class="$store.inspector.currentTab === 'all' ? 'bg-[#283E70] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition">
            All (<span x-text="Object.keys($store.inspector.poStatus).length">{{ $purchaseOrders->count() }}</span>)
        </button>
        <button @click="$store.inspector.currentTab = 'for_inspection'" 
                :class="$store.inspector.currentTab === 'for_inspection' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition">
            For Inspection (<span x-text="$store.inspector.forInspectionCount()">{{ $forInspectionCount }}</span>)
        </button>
        <button @click="$store.inspector.currentTab = 'approved'" 
                :class="$store.inspector.currentTab === 'approved' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition">
            Approved (<span x-text="$store.inspector.approvedCount()">{{ $approvedCount }}</span>)
        </button>
    </div>

    <div class="grid grid-cols-12 gap-8">
        {{-- DYNAMIC PO CARDS --}}
        <div class="col-span-12 space-y-4">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-2">
                Purchase Orders List
            </h2>

            @if($purchaseOrders->isEmpty())
            <div class="bg-white rounded-3xl p-12 text-center border border-gray-100 shadow-sm">
                <p class="text-gray-400 font-bold text-sm">No Purchase Orders available for inspection.</p>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($purchaseOrders as $po)
                @php
                    $itemsData = is_string($po->items) ? json_decode($po->items, true) : ($po->items ?? []);
                    $itemCount = is_array($itemsData) ? count($itemsData) : 0;
                @endphp
                <div id="po-card-{{ $po->id }}"
                     x-show="$store.inspector.currentTab === 'all' ||
                            ($store.inspector.currentTab === 'for_inspection' && $store.inspector.forInspectionStatuses.includes($store.inspector.poStatus[{{ $po->id }}])) ||
                            ($store.inspector.currentTab === 'approved' && $store.inspector.poStatus[{{ $po->id }}] === 'approved')"
                     class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 relative group flex flex-col justify-between">

                    <div>
                        <div class="flex justify-between items-start mb-4 flex-wrap gap-2">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
                                    P.O. Number
                                </span>
                                <h3 class="text-xl font-extrabold text-gray-800 mt-2">
                                    {{ $po->po_number }}
                                </h3>
                                <p class="text-xs font-bold text-gray-400 mt-1">
                                    {{ $po->supplier_name ?? $po->supplier ?? 'No supplier on record' }}
                                </p>
                            </div>
                            <div class="shrink-0">
                                <span x-show="$store.inspector.poStatus[{{ $po->id }}] === 'approved'"
                                      class="px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-extrabold rounded-full uppercase tracking-wider whitespace-nowrap">Approved</span>
                                <span x-show="$store.inspector.poStatus[{{ $po->id }}] !== 'approved' && $store.inspector.poActionable[{{ $po->id }}]"
                                      class="px-3 py-1 bg-amber-100 text-amber-700 text-xs font-extrabold rounded-full uppercase tracking-wider whitespace-nowrap">Pending</span>
                                <span x-show="$store.inspector.poStatus[{{ $po->id }}] !== 'approved' && !$store.inspector.poActionable[{{ $po->id }}]"
                                      title="Everything delivered so far has already been approved. Nothing to inspect until Supply submits the next batch."
                                      class="px-3 py-1 bg-gray-100 text-gray-500 text-[10px] font-extrabold rounded-full uppercase tracking-wider whitespace-nowrap">Waiting for next delivery</span>
                            </div>
                        </div>

                        <div class="space-y-2 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400 font-medium">Date:</span>
                                <span class="text-gray-700 font-bold">{{ $po->po_date ? \Carbon\Carbon::parse($po->po_date)->format('M d, Y') : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400 font-medium">Items Count:</span>
                                <span class="text-gray-700 font-bold">{{ $itemCount }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400 font-medium">Grand Total:</span>
                                <span class="text-blue-600 font-black">₱{{ number_format((float)($po->total_cost ?? 0), 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <button onclick='openInspectModal({{ json_encode(array_merge($po->toArray(), ["po_attachment" => $po->po_attachment])) }})'
                        class="w-full py-3 bg-[#283E70] text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-blue-600 transition shadow-md flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Inspect Details
                    </button>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- INSPECTION MODAL --}}
    <div id="inspectModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden border border-gray-100 flex flex-col max-h-[90vh] animate-in fade-in zoom-in-95 duration-200">

            {{-- Modal Header --}}
            <div class="bg-[#283E70] p-6 text-white flex justify-between items-center">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-blue-200">Inspection Details</span>
                    <h2 id="modalPoNumber" class="text-2xl font-black tracking-tight">PO #0000</h2>
                    <p id="modalSupplier" class="text-xs font-bold text-blue-200 mt-1">Supplier</p>
                </div>
                <button onclick="closeInspectModal()" class="text-white/70 hover:text-white text-2xl font-bold">✕</button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 overflow-y-auto space-y-6 flex-1">
                <div class="flex justify-between items-center bg-gray-50 p-4 rounded-2xl border border-gray-100">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Uploaded Document</p>
                        <p id="modalFileName" class="text-sm font-extrabold text-gray-700">Document.pdf</p>
                    </div>
                    <button type="button" id="btnViewDocument" onclick="openZoom()" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-wider hover:bg-blue-700 transition disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-blue-600">
                        View / Zoom Document
                    </button>
                </div>

                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-gray-400 mb-3">Item Breakdown</h4>
                    <div class="border border-gray-100 rounded-2xl overflow-hidden">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 text-gray-400 text-[10px] font-black uppercase">
                                <tr>
                                    <th class="p-3">Stock #</th>
                                    <th class="p-3">Description</th>
                                    <th class="p-3 text-center">Qty</th>
                                    <th class="p-3 text-right">Unit Cost</th>
                                    <th class="p-3 text-right">Subtotal</th>
                                    <th class="p-3 text-center">Delivery Photo</th>
                                    <th class="p-3 text-center">Decision</th>
                                </tr>
                            </thead>
                            <tbody id="modalItemsTable" class="divide-y divide-gray-100 text-gray-700 font-medium">
                            </tbody>
                        </table>
                    </div>
                </div>

                <form id="statusForm" method="POST" action="" class="hidden">
                    @csrf
                    <input type="hidden" name="status" id="formAction">
                </form>
                <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

                <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4 text-sm text-gray-500 font-medium">
                    Approve each item individually using the button in the <strong>Decision</strong> column above. Approved items are sent to the Warehouse queue for storage placement. The PO's status updates to "Approved" automatically once every item has been approved.
                </div>
            </div>

            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end items-center">
                <button onclick="closeInspectModal()" class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-gray-300 transition">
                    Close
                </button>
            </div>

        </div>
    </div>

    {{-- ZOOM VIEWER MODAL --}}
    <div id="zoomViewer" class="fixed inset-0 bg-slate-950/90 z-[60] hidden flex flex-col p-6">
        <div class="flex justify-between items-center mb-4 text-white">
            <h3 class="text-lg font-black uppercase">Document Preview</h3>
            <button onclick="closeZoom()" class="text-white/80 hover:text-white text-2xl font-bold">✕</button>
        </div>
        <div class="flex-1 bg-slate-900 rounded-2xl overflow-hidden flex items-center justify-center p-2">
            <iframe id="zoomPdf" class="w-full h-full rounded-xl hidden" src=""></iframe>
            <img id="zoomImg" class="max-w-full max-h-full object-contain rounded-xl hidden" src="" />
            <div id="zoomFallback" class="hidden flex-col items-center justify-center gap-4 text-center">
                <p class="text-white/80 text-sm font-bold max-w-sm">
                    This file type can't be previewed inline. Open or download it directly instead.
                </p>
                <a id="zoomFallbackLink" href="#" target="_blank" rel="noopener"
                   class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-wider hover:bg-blue-700 transition">
                    Open / Download File
                </a>
            </div>
        </div>
    </div>

    {{-- ITEM PHOTO LIGHTBOX --}}
    <div id="itemPhotoViewer" class="fixed inset-0 bg-slate-950/90 z-[70] hidden flex flex-col p-6">
        <div class="flex justify-between items-center mb-4 text-white">
            <h3 id="itemPhotoTitle" class="text-lg font-black uppercase">Delivery Photo</h3>
            <button onclick="closeItemPhoto()" class="text-white/80 hover:text-white text-2xl font-bold">✕</button>
        </div>
        <div class="flex-1 bg-slate-900 rounded-2xl overflow-hidden flex items-center justify-center p-2">
            <img id="itemPhotoImg" class="max-w-full max-h-full object-contain rounded-xl" src="" />
        </div>
    </div>

    {{-- CUSTOM CONFIRM / ALERT POP-UP (replaces native browser dialogs) --}}
    <div id="appDialog" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[80] hidden flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
            <div class="p-6">
                <h3 id="appDialogTitle" class="text-base font-bold text-gray-800 mb-2">Title</h3>
                <p id="appDialogMessage" class="text-sm text-gray-400 font-medium leading-relaxed">Message</p>
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

</div>

<script>
    window.currentPreview = null;

    // ------- Custom confirm/alert pop-up (replaces native browser dialogs) -------
    let appDialogResolver = null;

    function showAppDialog({ type = 'confirm', title, message, okText = 'Continue' }) {
        return new Promise((resolve) => {
            appDialogResolver = resolve;

            document.getElementById('appDialogTitle').innerText = title;
            document.getElementById('appDialogMessage').innerText = message;

            const okBtn = document.getElementById('appDialogOkBtn');
            const cancelBtn = document.getElementById('appDialogCancelBtn');

            okBtn.innerText = okText;
            cancelBtn.classList.toggle('hidden', type === 'alert');

            okBtn.className = type === 'error'
                ? 'px-5 py-2.5 bg-red-600 text-white rounded-full font-bold text-sm hover:bg-red-700 transition'
                : 'px-5 py-2.5 bg-gray-900 text-white rounded-full font-bold text-sm hover:bg-black transition';

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
        return showAppDialog({ type: 'confirm', title, message, okText: 'Continue' });
    }

    function showAppAlert(message, title = 'Notice') {
        return showAppDialog({ type: 'alert', title, message, okText: 'Got it' });
    }

    function showAppError(message, title = 'Something went wrong') {
        return showAppDialog({ type: 'error', title, message, okText: 'Got it' });
    }
    // -------------------------------------------------------------------------------

    // Reactive store for PO status. Seeded from the server-rendered data;
    // updated in place (no refresh) whenever an item approval causes a PO
    // to become fully approved, so cards/counters/tabs move instantly.
    document.addEventListener('alpine:init', () => {
        Alpine.store('inspector', {
            currentTab: @json($statusFilter ?? 'all'),
            poStatus: @json($initialPoStatuses),
            poActionable: @json($initialPoActionable),
            forInspectionStatuses: ['pending', 'pending_inspection', 'for_inspection', 'ready_for_inspection'],
            forInspectionCount() {
                return Object.values(this.poStatus).filter(s => this.forInspectionStatuses.includes(s)).length;
            },
            approvedCount() {
                return Object.values(this.poStatus).filter(s => s === 'approved').length;
            },
        });
    });

    function openInspectModal(po) {
        window.currentPreview = po.po_attachment || '';
        window.currentPoId = po.id;

        // Reset per-item photo-viewed tracking every time the modal opens.
        // An item's Approve button stays locked until its delivery photo
        // has actually been opened in the lightbox at least once.
        window.viewedPhotos = new Set();
        window.currentItemsCache = {};

        document.getElementById('modalPoNumber').innerText = "PO #" + po.po_number;
        document.getElementById('modalSupplier').innerText = po.supplier_name || po.supplier || 'No supplier on record';
        document.getElementById('modalFileName').innerText = window.currentPreview ? window.currentPreview.split('/').pop() : 'No document attached';

        const viewBtn = document.getElementById('btnViewDocument');
        viewBtn.disabled = !window.currentPreview;
        viewBtn.title = window.currentPreview ? '' : 'No document was uploaded for this PO';

        const tableBody = document.getElementById('modalItemsTable');
        tableBody.innerHTML = '';

        let items = po.items;
        if (typeof items === 'string') {
            try { items = JSON.parse(items); } catch(e) { items = []; }
        }

        if (!items || items.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="7" class="p-4 text-center text-gray-400 font-bold">No items found for this PO.</td></tr>`;
        } else {
            items.forEach((item, index) => {
                // Deliveries can arrive in batches — show what has ACTUALLY
                // been received so far, not the full requested quantity.
                const neededQty = parseFloat(item.quantity) || 0;
                const deliveredQty = (Array.isArray(item.deliveries) && item.deliveries.length > 0)
                    ? item.deliveries.reduce((sum, d) => sum + (parseFloat(d.quantity) || 0), 0)
                    : (parseFloat(item.inspected_quantity) || 0);

                // Approval is tracked separately from delivery — a batch
                // that arrives AFTER a previous approval must NOT be
                // silently shown as already approved.
                const approvedQty = parseFloat(item.approved_quantity) || 0;
                const pendingApprovalQty = Math.max(0, deliveredQty - approvedQty);

                const subtotal = deliveredQty * (parseFloat(item.unit_cost) || 0);
                const isPartial = deliveredQty > 0 && deliveredQty < neededQty;

                // Qty cell: show delivered/needed, and call out any
                // delivered-but-not-yet-approved units explicitly.
                let qtyCell = isPartial
                    ? `<div class="font-bold">${deliveredQty} <span class="text-[10px] text-gray-400 font-semibold">/ ${neededQty}</span></div>`
                    : `${deliveredQty || neededQty}`;

                if (approvedQty > 0) {
                    qtyCell += `<div class="text-[10px] text-emerald-600 font-bold mt-0.5">Approved: ${approvedQty}</div>`;
                }
                if (pendingApprovalQty > 0) {
                    qtyCell += `<div class="text-[10px] text-amber-600 font-bold mt-0.5">Pending approval: ${pendingApprovalQty}</div>`;
                }

                const photoPath = item.delivery_photo || '';
                const photoCell = photoPath
                    ? `<button type="button" onclick="openItemPhoto('${photoPath}', '${(item.description || 'Item').replace(/'/g, "\\'")}', ${index})"
                           class="w-10 h-10 rounded-lg overflow-hidden border border-gray-200 hover:ring-2 hover:ring-blue-500 transition mx-auto block">
                           <img src="/storage/${photoPath}" class="w-full h-full object-cover" />
                       </button>`
                    : `<span class="text-[10px] font-bold text-gray-300 uppercase">Not submitted</span>`;

                // Remember this item's approval context so we can re-render
                // just this row (with the correct status/pending qty) once
                // its delivery photo gets viewed.
                window.currentItemsCache[index] = { inspectionStatus: item.inspection_status, pendingApprovalQty, hasPhoto: !!photoPath, neededQty, deliveredQty };

                const decisionCell = renderDecisionCell(item.inspection_status, index, !!photoPath, pendingApprovalQty, window.viewedPhotos.has(index));

                tableBody.innerHTML += `
                    <tr>
                        <td class="p-3 font-bold text-blue-600">${item.stock_no || (index + 1)}</td>
                        <td class="p-3">${item.description || 'N/A'}</td>
                        <td class="p-3 text-center">${qtyCell}</td>
                        <td class="p-3 text-right">₱${parseFloat(item.unit_cost || 0).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                        <td class="p-3 text-right font-black">₱${subtotal.toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                        <td class="p-3 text-center">${photoCell}</td>
                        <td class="p-3 text-center" id="decision-cell-${index}">${decisionCell}</td>
                    </tr>
                `;
            });
        }

        // Keep the card's "Pending" vs "Waiting for next delivery" badge in
        // sync with what's actually in this modal, in case anything on the
        // server changed since the page first loaded.
        const isActionable = Object.values(window.currentItemsCache).some(it => it.hasPhoto && it.pendingApprovalQty > 0);
        Alpine.store('inspector').poActionable[po.id] = isActionable;

        const form = document.getElementById('statusForm');
        form.action = "/inspector/purchase-order/" + po.id + "/status";

        document.getElementById('inspectModal').classList.remove('hidden');
    }

    function renderDecisionCell(inspectionStatus, index, hasPhoto, pendingApprovalQty = 0, photoViewed = false) {
        if (!hasPhoto) {
            return `<span class="text-[10px] font-bold text-gray-300 uppercase" title="Supply hasn't submitted a delivery photo for this item yet">Awaiting photo</span>`;
        }

        // Nothing newly delivered is waiting on a decision right now.
        if (pendingApprovalQty <= 0) {
            if (inspectionStatus === 'approved') {
                return `<span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-black rounded-full uppercase">Approved</span>`;
            }
            // Delivered-so-far has already been approved, but the item's
            // full requested quantity hasn't arrived yet — there's simply
            // nothing to act on until Supply submits the next batch.
            return `<span class="text-[10px] font-bold text-gray-400 uppercase" title="What's been delivered so far is already approved. Waiting for Supply to deliver the rest.">Waiting for next delivery</span>`;
        }
        if (!photoViewed) {
            return `
                <button type="button" onclick="openItemPhotoFromCell(${index})" title="Open the delivery photo before you can approve"
                    class="px-3 py-1.5 bg-amber-50 text-amber-600 text-[10px] font-black rounded-lg uppercase hover:bg-amber-100 transition">
                    View photo to unlock
                </button>
            `;
        }
        const label = pendingApprovalQty > 0
            ? `✓ Approve ${pendingApprovalQty} new`
            : `✓ Approve`;
        return `
            <button type="button" onclick="decideItem(${index})" title="Approve this item"
                class="px-3 py-1.5 bg-emerald-600 text-white text-[10px] font-black rounded-lg uppercase hover:bg-emerald-700 transition">${label}</button>
        `;
    }

    // Lets the "View photo to unlock" button in the Decision column open
    // the same lightbox as the thumbnail, using the cached item data.
    function openItemPhotoFromCell(index) {
        const cached = window.currentItemsCache && window.currentItemsCache[index];
        const row = document.getElementById('decision-cell-' + index)?.closest('tr');
        const thumbBtn = row ? row.querySelector('td:nth-child(6) button') : null;
        if (thumbBtn) {
            thumbBtn.click();
        }
    }

    async function decideItem(index) {
        if (!window.viewedPhotos || !window.viewedPhotos.has(index)) {
            await showAppAlert('Please open and check the delivery photo for this item before approving it.', 'Delivery photo not viewed yet');
            return;
        }

        const confirmed = await showAppConfirm('Approve this item and send it to the Warehouse queue?', 'Approve item');
        if (!confirmed) return;

        try {
            const res = await fetch(`/inspector/purchase-order/${window.currentPoId}/item/${index}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.getElementById('csrfToken').value,
                },
                body: JSON.stringify({ status: 'approved' })
            });
            const data = await res.json();

            if (!res.ok) {
                showAppError(data.message || 'Something went wrong.');
                return;
            }

            // Whether this item is now fully "Approved" depends on whether
            // everything NEEDED has been delivered, not just whether this
            // batch got approved — a 6-of-10 approval is still "waiting on
            // the next delivery", not done. The server is the source of
            // truth for the approval itself; we just need the needed/
            // delivered figures we already cached when the modal opened.
            const cached = window.currentItemsCache && window.currentItemsCache[index];
            const newInspectionStatus = (cached && cached.deliveredQty >= cached.neededQty) ? 'approved' : 'partial';

            const cell = document.getElementById('decision-cell-' + index);
            if (cell) cell.innerHTML = renderDecisionCell(newInspectionStatus, index, true, 0);

            if (cached) {
                cached.inspectionStatus = newInspectionStatus;
                cached.pendingApprovalQty = 0;
            }

            // Recompute whether this PO still has anything actionable and
            // reflect it on the card immediately — if that was the last
            // actionable item, the card should show "Waiting for next
            // delivery" instead of "Pending" without needing a refresh.
            const stillActionable = Object.values(window.currentItemsCache).some(it => it.hasPhoto && it.pendingApprovalQty > 0);
            Alpine.store('inspector').poActionable[window.currentPoId] = stillActionable;

            // The controller returns the PO's up-to-date overall status.
            // If every item is now approved, flip the store so the card
            // jumps to "Approved" and the tab counters update — no
            // page refresh needed.
            if (data.po_status) {
                Alpine.store('inspector').poStatus[window.currentPoId] = data.po_status;
            }
        } catch (e) {
            showAppError('Network error — please try again.');
        }
    }

    function closeInspectModal() {
        document.getElementById('inspectModal').classList.add('hidden');
    }

    function openZoom() {
        const viewer = document.getElementById('zoomViewer');
        const zoomPdf = document.getElementById('zoomPdf');
        const zoomImg = document.getElementById('zoomImg');
        const zoomFallback = document.getElementById('zoomFallback');
        const zoomFallbackLink = document.getElementById('zoomFallbackLink');

        zoomPdf.classList.add('hidden');
        zoomImg.classList.add('hidden');
        zoomFallback.classList.add('hidden');
        zoomFallback.classList.remove('flex');

        if (!window.currentPreview) return;

        const url = "/storage/" + window.currentPreview;
        const ext = window.currentPreview.toLowerCase().split('.').pop();
        const IMAGE_EXTS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (ext === 'pdf') {
            zoomPdf.src = url;
            zoomPdf.classList.remove('hidden');
        } else if (IMAGE_EXTS.includes(ext)) {
            zoomImg.src = url;
            zoomImg.classList.remove('hidden');
        } else {
            // Spreadsheets and any other non-previewable type: offer a direct link instead.
            zoomFallbackLink.href = url;
            zoomFallback.classList.remove('hidden');
            zoomFallback.classList.add('flex');
        }

        viewer.classList.remove('hidden');
    }

    function closeZoom() {
        document.getElementById('zoomViewer').classList.add('hidden');
    }

    function openItemPhoto(path, label, index) {
        document.getElementById('itemPhotoTitle').innerText = label || 'Delivery Photo';
        document.getElementById('itemPhotoImg').src = "/storage/" + path;
        document.getElementById('itemPhotoViewer').classList.remove('hidden');

        // Once the inspector actually opens a delivery photo, unlock that
        // item's Approve button by re-rendering just its decision cell.
        if (typeof index !== 'undefined' && index !== null) {
            window.viewedPhotos = window.viewedPhotos || new Set();
            window.viewedPhotos.add(index);

            const cached = window.currentItemsCache && window.currentItemsCache[index];
            const cell = document.getElementById('decision-cell-' + index);
            if (cell && cached) {
                cell.innerHTML = renderDecisionCell(cached.inspectionStatus, index, cached.hasPhoto, cached.pendingApprovalQty, true);
            }
        }
    }

    function closeItemPhoto() {
        document.getElementById('itemPhotoViewer').classList.add('hidden');
    }
</script>
@endsection