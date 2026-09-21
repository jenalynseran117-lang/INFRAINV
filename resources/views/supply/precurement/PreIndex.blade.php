@extends('layouts.Supply.app')

@section('content')
<div class="w-full max-w-[1920px] mx-auto p-2 md:p-6 antialiased">

    {{-- 1. LIST VIEW --}}
    <div id="list-view" class="animate-in fade-in duration-500">
        <div class="mb-8">
            <h1 class="text-4xl font-black text-slate-900 tracking-tighter uppercase">Procurement Process</h1>
            <p class="text-slate-600 font-bold uppercase text-sm tracking-[0.2em] mt-2">Select a Purchase Request to begin</p>
        </div>

        @if($prs->isEmpty())
        <div class="bg-white rounded-[3rem] p-24 text-center border border-slate-100 shadow-sm">
            <p class="text-slate-500 font-black uppercase tracking-widest text-sm">No pending requests in the queue.</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($prs as $pr)
            @php
            $ext = strtolower(pathinfo($pr->file, PATHINFO_EXTENSION));
            $fileUrl = Storage::url($pr->file);
            @endphp
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-2xl transition-all group border-b-4 hover:border-b-blue-600">
                <span class="px-4 py-1.5 bg-blue-50 text-blue-600 text-sm font-black rounded-full uppercase tracking-tighter">PR #{{ $pr->pr_number ?? $pr->id }}</span>
                <h3 class="text-lg font-bold text-slate-800 mt-6 mb-2 uppercase tracking-tight">Material Request</h3>
                <div class="flex items-center gap-1.5 mb-8 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-xs font-bold uppercase tracking-wide" title="{{ optional($pr->created_at)->timezone('Asia/Manila')->format('F d, Y g:i A') }}">
                        {{ $pr->created_at ? $pr->created_at->timezone('Asia/Manila')->format('M d, Y \a\t g:i A') : 'Date unavailable' }}
                    </span>
                </div>
                <button type="button"
                    onclick="startProcessing('{{ $pr->id }}', '{{ $pr->pr_number ?? $pr->id }}', '{{ $ext }}', '{{ $fileUrl }}')"
                    class="w-full py-4 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-lg">
                    Open Request
                </button>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- 2. FOCUS MODE --}}
    <div id="processing-view" class="hidden animate-in slide-in-from-bottom-10 duration-700">
        <form action="{{ route('supply.PrStore') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="management_id" id="selected-pr-id">
            <input type="file" name="signed_po" id="signed_po" class="hidden" accept=".pdf,image/*,.xlsx,.xls,.csv" onchange="handlePOUpload(this)">

            {{-- Header Controls --}}
            <div class="flex items-center justify-between mb-2 bg-white px-4 py-2 rounded-2xl shadow-md border border-slate-50">
                <div class="flex items-center gap-3">
                    <button type="button" onclick="exitProcessing()" class="p-2 bg-slate-100 hover:bg-red-500 hover:text-white rounded-xl transition text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <div class="flex items-center gap-2 border-l-2 pl-3">
                        <h2 id="focus-pr-title" class="text-sm font-black text-slate-900 tracking-tighter uppercase">PR #0000</h2>
                        <button type="button" id="btn-view-pr" class="hidden px-3 py-1.5 bg-slate-800 text-white text-[11px] font-black rounded-lg uppercase tracking-widest hover:bg-blue-600 transition shadow-md">View PR</button>
                        <div id="po-action-container" class="flex gap-2">
                            <button type="button" id="btn-upload-trigger" onclick="document.getElementById('signed_po').click()" class="px-3 py-1.5 bg-blue-600 text-white text-[11px] font-black rounded-lg uppercase tracking-widest hover:bg-blue-700 transition shadow-md flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Upload PO
                            </button>
                            <button type="button" id="btn-view-po" class="hidden px-3 py-1.5 bg-emerald-600 text-white text-[11px] font-black rounded-lg uppercase tracking-widest hover:bg-emerald-700 transition">View PO</button>
                        </div>
                        <button type="button" id="btn-extract-text" onclick="extractPOItemDescriptions()"
                            class="px-3 py-1.5 bg-amber-500 text-white text-[11px] font-black rounded-lg uppercase tracking-widest hover:bg-amber-600 transition shadow-md flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Extract PO Items
                        </button>
                    </div>
                </div>
                <div id="current-preview-label" class="text-[11px] font-black text-blue-600 uppercase tracking-widest bg-blue-50 px-3 py-1.5 rounded-lg">Document Ready</div>
            </div>

            @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-2xl">
                <p class="text-sm font-black text-red-600 uppercase tracking-widest mb-2">Please fix the following:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                    <li class="text-sm font-bold text-red-600">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">

                {{-- DOCUMENT VIEWER WITH INTEGRATED ZOOM --}}
                <div class="lg:col-span-7 bg-slate-900 rounded-[2.5rem] overflow-hidden shadow-2xl h-[calc(100vh-100px)] sticky top-2 border-[8px] border-slate-800 group relative">

                    {{-- FLOATING ZOOM CONTROLS --}}
                    <div class="absolute right-6 top-1/2 -translate-y-1/2 z-50 flex flex-col gap-2 p-2 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 shadow-2xl opacity-40 group-hover:opacity-100 transition-opacity">
                        <button type="button" onclick="adjustZoom(0.2)" class="w-10 h-10 flex items-center justify-center bg-white rounded-xl shadow-lg hover:bg-blue-600 hover:text-white transition-all text-slate-800 font-bold text-xl">+</button>
                        <div id="zoom-level" class="text-sm font-black text-white text-center py-1">100%</div>
                        <button type="button" onclick="adjustZoom(-0.2)" class="w-10 h-10 flex items-center justify-center bg-white rounded-xl shadow-lg hover:bg-blue-600 hover:text-white transition-all text-slate-800 font-bold text-xl">-</button>
                        <button type="button" onclick="resetZoom()" class="mt-2 w-10 h-10 flex items-center justify-center bg-slate-800 text-white rounded-xl shadow-lg hover:bg-red-500 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>

                    {{-- VIEWER CONTAINER --}}
                    <div id="viewer-container" class="h-full w-full overflow-auto custom-scrollbar bg-slate-950 flex justify-center items-center relative cursor-grab active:cursor-grabbing p-4">
                        <div id="document-content" class="origin-center transition-transform duration-200 flex justify-center items-center w-full h-full min-h-[400px]">
                            <div class="text-center">
                                <p class="text-slate-500 font-black uppercase text-sm tracking-widest">Preview Area</p>
                            </div>
                        </div>
                    </div>

                    {{-- OCR loading overlay --}}
                    <div id="ocr-loading-overlay" class="hidden absolute inset-0 bg-slate-950/80 z-40 flex-col items-center justify-center gap-3">
                        <div class="w-10 h-10 border-4 border-amber-500 border-t-transparent rounded-full animate-spin"></div>
                        <p id="ocr-loading-text" class="text-white text-sm font-black uppercase tracking-widest">Reading document...</p>
                    </div>
                </div>

                {{-- RIGHT FORM PANEL --}}
                <div class="lg:col-span-5 bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 p-8 overflow-y-auto h-[calc(100vh-100px)] custom-scrollbar">
                    <div class="space-y-8">

                        <div id="ocr-panel" class="hidden space-y-2 sticky top-0 z-20 -mx-8 px-8 pt-2 pb-4 bg-white/95 backdrop-blur-sm border-b border-amber-100 shadow-md">
                            <div class="flex justify-between items-center px-2">
                                <label class="text-sm font-black text-amber-600 uppercase tracking-widest">Item Descriptions Found on PO</label>
                                <button type="button" onclick="document.getElementById('ocr-panel').classList.add('hidden')"
                                    class="text-sm font-black text-slate-700 uppercase hover:text-red-500">Close</button>
                            </div>
                            <textarea id="ocr-output" readonly
                                class="w-full h-48 p-4 text-base font-mono font-bold rounded-2xl border border-amber-100 bg-amber-50 text-slate-900 shadow-inner focus:ring-2 focus:ring-amber-400 leading-relaxed"
                                placeholder="Extracted item descriptions will appear here..."></textarea>
                            <p class="text-sm font-bold text-slate-600 px-2">Copy the descriptions you need into the item rows below manually. Accuracy depends on photo quality — double check before entering.</p>
                        </div>

                        <div id="upload-po-gate-notice" class="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                            <p class="text-[13px] font-black text-amber-700 uppercase tracking-wide">Upload the PO first to fill in these fields.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-1">
                                <div class="flex items-center justify-between ml-3 mb-1">
                                    <label class="text-sm font-black text-slate-600 uppercase tracking-widest">P.O. Number</label>
                                    <div class="flex items-center gap-2">
                                        <span id="pr-format-icon"></span>
                                        <span id="po-match-icon"></span>
                                    </div>
                                </div>
                                <input type="text"
                                    name="po_number"
                                    id="pr_number"
                                    required
                                    disabled
                                    autocomplete="off"
                                    inputmode="numeric"
                                    class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none border-gray-300 text-lg font-mono text-inv-navy disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed"
                                    value="SO_A_"
                                    maxlength="17"
                                    pattern="SO_A_\d{4}_\d{2}_\d{3}"
                                    title="Format must be SO_A_YYYY_MM_XXX (e.g., SO_A_2026_07_000)"
                                    oninput="formatPoNumberInput(event)"
                                    onfocus="if(!this.value) this.value='SO_A_';"
                                    onblur="validatePoNumberFormat(this)">
                                <p id="po-match-status" class="text-[13px] font-bold text-slate-600 mt-1 ml-3">Upload a signed PO to verify this number.</p>
                                <p id="po-duplicate-status" class="hidden text-[13px] font-bold text-red-500 mt-1 ml-3">This P.O. Number is already in use — please enter a different one.</p>
                            </div>
                            <div class="col-span-1">
                                <label class="text-sm font-black text-slate-600 uppercase tracking-widest ml-3 mb-1 block">Date</label>
                                <input type="date" name="po_date" id="po_date" required disabled
                                    class="w-full p-3 rounded-xl bg-slate-50 border-none font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 shadow-inner disabled:text-slate-400 disabled:cursor-not-allowed"
                                    oninput="validatePoDate(this)">
                                <p class="text-[13px] font-bold text-slate-600 mt-1 ml-3">No future dates — up to 1 month in the past is allowed.</p>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-black text-slate-600 uppercase tracking-widest ml-3 mb-1 block">Supplier</label>
                            <input type="text"
                                name="supplier"
                                id="supplier"
                                required
                                disabled
                                autocomplete="off"
                                class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none border-gray-300 text-lg font-bold text-inv-navy disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">
                        </div>

                        {{-- Item Management --}}
                        <div class="space-y-4">
                            <div class="flex justify-between items-center px-2">
                                <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">Procurement Items</h4>
                            </div>
                            <p class="text-[13px] font-bold text-slate-600 px-2 -mt-2">Tip: Click "Upload PO" and choose an Excel/CSV file to preview it in the document reader. Item rows are entered manually — use "+ Add Row" and fill in each description, qty, and cost.</p>

                            <div id="item-cards-container" class="space-y-3">
                                <div class="item-card bg-slate-50 rounded-2xl p-6 border border-slate-100 relative group transition-all hover:border-blue-200 shadow-sm space-y-4">
                                    <div class="grid grid-cols-12 gap-4">
                                        <div class="col-span-2">
                                            <label class="text-[13px] font-black text-slate-600 mb-1.5 block uppercase">Stock #</label>
                                            <input type="text" name="stock_no[]" readonly class="stock-counter w-full p-3.5 rounded-xl border-none text-base font-black bg-blue-50 text-blue-600 shadow-sm text-center" value="1">
                                        </div>
                                        <div class="col-span-10">
                                            <label class="text-[13px] font-black text-slate-600 mb-1.5 block uppercase">Description</label>
                                            <input type="text" name="description[]" disabled class="w-full p-3.5 rounded-xl border-none text-base font-bold shadow-sm focus:ring-2 focus:ring-blue-500 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <label class="text-[13px] font-black text-slate-600 mb-1.5 block uppercase">Qty</label>
                                            <input type="number" name="quantity[]" oninput="calculateCard(this)" disabled class="qty w-full p-3.5 rounded-xl border-none text-base font-black text-blue-600 text-center shadow-sm disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">
                                        </div>
                                        <div>
                                            <label class="text-[13px] font-black text-slate-600 mb-1.5 block uppercase">Unit Cost</label>
                                            <input type="number" step="0.01" name="unit_cost[]" oninput="calculateCard(this)" disabled class="cost w-full p-3.5 rounded-xl border-none text-base font-bold text-right shadow-sm disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">
                                        </div>
                                        <div>
                                            <label class="text-[13px] font-black text-blue-500 mb-1.5 block uppercase">Sub-Total (₱)</label>
                                            <input type="text" name="amount[]" readonly class="amount w-full p-3.5 rounded-xl border-none bg-blue-100/50 font-black text-blue-700 text-base text-right" value="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end px-2">
                                <button type="button" id="btn-add-row" onclick="addItemCard()" disabled class="px-5 py-2.5 bg-slate-900 text-white text-[13px] font-black rounded-xl uppercase tracking-widest hover:bg-blue-600 transition shadow-xl disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-slate-900">+ Add Row</button>
                            </div>

                            <div class="bg-blue-600 p-8 rounded-[2rem] flex justify-between items-center shadow-2xl border-b-8 border-blue-800">
                                <span class="text-white font-black uppercase text-sm tracking-[0.2em]">Grand Total</span>
                                <div class="flex items-baseline gap-2 text-white">
                                    <span class="text-sm font-bold opacity-70">₱</span>
                                    <input type="text" id="total-cost" name="total_cost" readonly class="bg-transparent border-none text-4xl font-black focus:ring-0 text-right w-56" value="0.00">
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="submit-btn" disabled class="w-full bg-slate-900 text-white py-6 rounded-[2rem] font-black text-sm uppercase tracking-[0.5em] shadow-2xl hover:bg-emerald-600 transition-all transform active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-slate-900">Complete Transaction</button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/tesseract.js/5.0.3/tesseract.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    let currentPRFile = "";
    let currentPRExt = "";
    let currentPOFile = null;
    let currentPOPreview = null;
    let zoomLevel = 1;
    let extractedPoNumberFromFile = null;
    let poUploaded = false;

    const existingPoNumbers = @json($purchaseOrders->pluck('po_number')->filter()->values());
    const normalizedExistingPoNumbers = existingPoNumbers.map((n) => normalizePoNumber(n));

    const IMAGE_EXTS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    const SPREADSHEET_EXTS = ['xlsx', 'xls', 'csv'];

    function startProcessing(id, prNumber, ext, fileUrl) {
        currentPRFile = fileUrl;
        currentPRExt = ext;
        document.getElementById('list-view').classList.add('hidden');
        document.getElementById('processing-view').classList.remove('hidden');
        document.getElementById('selected-pr-id').value = id;
        document.getElementById('focus-pr-title').innerText = 'PR #' + prNumber;
        displayInViewer(fileUrl, ext, "Purchase Request");
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    function adjustZoom(amount) {
        zoomLevel = Math.min(Math.max(0.5, zoomLevel + amount), 4);
        applyZoom();
    }

    function resetZoom() {
        zoomLevel = 1;
        applyZoom();
    }

    function applyZoom() {
        const content = document.getElementById('document-content');
        content.style.transform = `scale(${zoomLevel})`;
        document.getElementById('zoom-level').innerText = Math.round(zoomLevel * 100) + "%";
    }

    async function displayInViewer(url, ext, name) {
        const content = document.getElementById('document-content');
        document.getElementById('current-preview-label').innerText = "PREVIEW: " + name;
        resetZoom();

        const extClean = (ext || '').toLowerCase();

        if (IMAGE_EXTS.includes(extClean)) {
            content.innerHTML = `<img src="${url}" class="shadow-2xl border-4 border-slate-700 bg-white max-w-full max-h-[80vh] object-contain rounded-lg">`;
        } else if (extClean === 'pdf') {
            content.innerHTML = `<iframe src="${url}#toolbar=0" class="shadow-2xl border-4 border-slate-700 bg-white w-full h-[75vh] rounded-lg border-none"></iframe>`;
        } else if (SPREADSHEET_EXTS.includes(extClean)) {
            content.innerHTML = `<div class="text-center text-white"><div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div><p class="font-bold text-sm uppercase">Loading Spreadsheet...</p></div>`;
            try {
                const response = await fetch(url);
                const arrayBuffer = await response.arrayBuffer();
                const workbook = XLSX.read(arrayBuffer, { type: 'array' });
                const sheet = workbook.Sheets[workbook.SheetNames[0]];
                displaySpreadsheetPreview(sheet, name);
            } catch (err) {
                content.innerHTML = `<div class="p-8 bg-white rounded-2xl shadow-xl text-center"><p class="text-slate-800 font-bold mb-4">Could not load spreadsheet preview.</p><a href="${url}" target="_blank" class="px-4 py-2 bg-blue-600 text-white font-bold text-xs rounded-xl uppercase">Download File</a></div>`;
            }
        } else {
            content.innerHTML = `<div class="p-8 bg-white rounded-2xl shadow-xl text-center"><p class="text-slate-800 font-bold mb-4">Cannot preview file format (.${extClean})</p><a href="${url}" target="_blank" class="px-4 py-2 bg-blue-600 text-white font-bold text-xs rounded-xl uppercase">Download File</a></div>`;
        }
    }

    function displaySpreadsheetPreview(sheet, fileName) {
        const content = document.getElementById('document-content');
        document.getElementById('current-preview-label').innerText = "PREVIEW: " + fileName;
        resetZoom();

        const tableHtml = XLSX.utils.sheet_to_html(sheet, {
            editable: false
        });
        const styledHtml = `
            <html>
            <head>
                <style>
                    body { margin: 0; padding: 16px; font-family: system-ui, sans-serif; background: #fff; }
                    table { border-collapse: collapse; width: 100%; font-size: 12px; }
                    td, th { border: 1px solid #cbd5e1; padding: 6px 10px; text-align: left; white-space: nowrap; }
                    tr:nth-child(even) { background: #f8fafc; }
                </style>
            </head>
            <body>${tableHtml}</body>
            </html>`;

        content.innerHTML = '';
        const iframe = document.createElement('iframe');
        iframe.className = "shadow-2xl border-4 border-slate-700 bg-white w-full h-[75vh] rounded-lg border-none";
        content.appendChild(iframe);
        iframe.srcdoc = styledHtml;
    }

    function showPOPreview() {
        if (!currentPOPreview) return;

        if (currentPOPreview.type === 'spreadsheet') {
            displaySpreadsheetPreview(currentPOPreview.content, currentPOPreview.fileName);
        } else {
            displayInViewer(currentPOPreview.content, currentPOPreview.type, "Signed PO");
        }
    }

    function showToast(message, type = 'info', anchorEl = null) {
        document.querySelectorAll('.mini-toast').forEach((t) => t.remove());

        const colors = {
            info: 'bg-slate-900 text-white',
            error: 'bg-red-500 text-white',
            success: 'bg-emerald-600 text-white',
            warning: 'bg-amber-500 text-white'
        };

        const toast = document.createElement('div');
        toast.className = `mini-toast fixed z-[9999] px-4 py-3 rounded-xl shadow-2xl text-sm font-bold max-w-xs leading-snug animate-in fade-in slide-in-from-top-2 duration-200 ${colors[type] || colors.info}`;
        toast.innerText = message;

        const anchor = anchorEl || document.getElementById('btn-upload-trigger');
        if (anchor) {
            const rect = anchor.getBoundingClientRect();
            toast.style.top = (rect.bottom + window.scrollY + 8) + 'px';
            toast.style.left = (rect.left + window.scrollX) + 'px';
        } else {
            toast.style.top = '20px';
            toast.style.right = '20px';
        }

        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    function preprocessImageForOCR(imgSrc) {
        return new Promise((resolve) => {
            const img = new Image();
            img.crossOrigin = "anonymous";
            img.onload = () => {
                const scale = 2;
                const canvas = document.createElement('canvas');
                canvas.width = img.width * scale;
                canvas.height = img.height * scale;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const data = imageData.data;
                const threshold = 150;

                for (let i = 0; i < data.length; i += 4) {
                    const avg = (data[i] + data[i + 1] + data[i + 2]) / 3;
                    const value = avg > threshold ? 255 : 0;
                    data[i] = data[i + 1] = data[i + 2] = value;
                }

                ctx.putImageData(imageData, 0, 0);
                resolve(canvas.toDataURL());
            };
            img.onerror = () => resolve(imgSrc);
            img.src = imgSrc;
        });
    }

    async function runOCR(imageSrc, onProgress) {
        const processed = await preprocessImageForOCR(imageSrc);
        const result = await Tesseract.recognize(processed, 'eng', {
            logger: onProgress
        });
        return result.data.text.trim();
    }

    function looksLikeDescriptionLine(line) {
        const trimmed = line.trim();
        if (trimmed.length < 3) return false;
        if (!/[a-zA-Z]{2,}/.test(trimmed)) return false;

        const skipKeywords = [
            'purchase request', 'po no', 'p.o. no', 'p.o.no', 'date', 'fund cluster',
            'responsibility center', 'total', 'quantity', 'unit cost', 'stock',
            'property no', 'signature', 'approved by', 'requested by', 'noted by',
            'name:', 'section', 'office', 'grand total'
        ];
        const lower = trimmed.toLowerCase();
        if (skipKeywords.some((k) => lower.includes(k))) return false;

        return true;
    }

    async function extractPOItemDescriptions() {
        const extractBtn = document.getElementById('btn-extract-text');

        if (currentPOPreview && currentPOPreview.type === 'spreadsheet') {
            showToast("This is a spreadsheet preview, not a scannable image — enter item descriptions manually or copy from the spreadsheet.", 'info', extractBtn);
            return;
        }

        if (!currentPOFile) {
            showToast("Please upload a signed PO image first.", 'warning', extractBtn);
            return;
        }

        const ext = currentPOFile.name.split('.').pop().toLowerCase();
        if (!IMAGE_EXTS.includes(ext)) {
            showToast("Item description extraction only works on image uploads (JPG/PNG) — this file is a " + ext.toUpperCase() + ".", 'warning', extractBtn);
            return;
        }

        const overlay = document.getElementById('ocr-loading-overlay');
        const loadingText = document.getElementById('ocr-loading-text');
        const panel = document.getElementById('ocr-panel');
        const output = document.getElementById('ocr-output');

        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        panel.classList.remove('hidden');
        output.value = "";

        try {
            const poUrl = URL.createObjectURL(currentPOFile);
            const text = await runOCR(poUrl, (m) => {
                loadingText.innerText = m.status === 'recognizing text' ?
                    'Reading PO... ' + Math.round(m.progress * 100) + '%' :
                    m.status;
            });

            const lines = text.split('\n').map((l) => l.trim()).filter(looksLikeDescriptionLine);

            if (lines.length === 0) {
                output.value = "(No item descriptions detected on the uploaded PO — try a clearer photo, or enter them manually.)";
                return;
            }

            output.value = lines.join('\n');
        } catch (err) {
            output.value = "Could not read text from the uploaded PO: " + err.message;
        } finally {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }
    }

    const PR_PREFIX = 'SO_A_';

    function formatPoNumberInput(e) {
        const input = e.target;
        let digits = input.value.slice(PR_PREFIX.length).replace(/\D/g, '').slice(0, 9);

        let formatted = PR_PREFIX;
        if (digits.length > 0) formatted += digits.slice(0, 4);
        if (digits.length > 4) formatted += '_' + digits.slice(4, 6);
        if (digits.length > 6) formatted += '_' + digits.slice(6, 9);

        input.value = formatted;
        updatePrFormatIcon(input);
        checkPoMatch();
        updatePoDuplicateUI(input);
    }

    function updatePrFormatIcon(input) {
        const icon = document.getElementById('pr-format-icon');
        const fullPattern = new RegExp('^' + PR_PREFIX + '\\d{4}_\\d{2}_\\d{3}$');
        const isComplete = input.value.length === PR_PREFIX.length + 11;

        if (!isComplete) {
            icon.innerHTML = '';
            return;
        }

        icon.innerHTML = fullPattern.test(input.value) ?
            '<svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>' :
            '<svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>';
    }

    function validatePoNumberFormat(input) {
        if (!input.value.startsWith(PR_PREFIX)) {
            input.value = PR_PREFIX;
            updatePrFormatIcon(input);
        }
        updatePoDuplicateUI(input);
    }

    function normalizePoNumber(str) {
        return (str || '').toUpperCase().replace(/[\s\-\.\/]/g, '');
    }

    function extractPoNumberFromText(text) {
        const patterns = [
            /P\.?\s*O\.?\s*(?:No\.?|Number|#)?\s*[:\-]?\s*([A-Z0-9][A-Z0-9\-\/]{2,})/i
        ];
        for (const pattern of patterns) {
            const match = text.match(pattern);
            if (match && match[1]) return match[1].trim();
        }
        return null;
    }

    function checkPoMatch() {
        const icon = document.getElementById('po-match-icon');
        const status = document.getElementById('po-match-status');
        const typedValue = document.getElementById('pr_number').value;

        if (!extractedPoNumberFromFile) {
            icon.innerHTML = '';
            status.innerText = 'Upload a signed PO to verify this number.';
            status.className = 'text-[13px] font-bold text-slate-600 mt-1 ml-3';
            return;
        }

        if (!typedValue) {
            icon.innerHTML = '';
            status.innerText = 'Enter the P.O. Number to compare against the uploaded file.';
            status.className = 'text-[13px] font-bold text-slate-600 mt-1 ml-3';
            return;
        }

        const isMatch = normalizePoNumber(typedValue) === normalizePoNumber(extractedPoNumberFromFile);

        if (isMatch) {
            icon.innerHTML = '<svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>';
            status.innerText = 'Matches the number found on the uploaded PO.';
            status.className = 'text-[13px] font-bold text-emerald-600 mt-1 ml-3';
        } else {
            icon.innerHTML = '<svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>';
            status.innerText = 'Does not match "' + extractedPoNumberFromFile + '" found on the uploaded PO.';
            status.className = 'text-[13px] font-bold text-red-500 mt-1 ml-3';
        }
    }

    function isPoNumberDuplicate(value) {
        const isComplete = value.length === PR_PREFIX.length + 11;
        return isComplete && normalizedExistingPoNumbers.includes(normalizePoNumber(value));
    }

    function updatePoDuplicateUI(input) {
        const dupStatus = document.getElementById('po-duplicate-status');
        const duplicate = isPoNumberDuplicate(input.value);

        dupStatus.classList.toggle('hidden', !duplicate);
        input.classList.toggle('border-red-500', duplicate);
        input.classList.toggle('ring-2', duplicate);
        input.classList.toggle('ring-red-500', duplicate);

        updateSubmitAvailability();
        return duplicate;
    }

    function updateSubmitAvailability() {
        const submitBtn = document.getElementById('submit-btn');
        const poNumberInput = document.getElementById('pr_number');
        if (!submitBtn || !poNumberInput) return;
        submitBtn.disabled = !poUploaded || isPoNumberDuplicate(poNumberInput.value);
    }

    async function verifyPoFromUpload(imageSrc) {
        const status = document.getElementById('po-match-status');
        status.innerText = 'Reading uploaded PO to verify number...';
        status.className = 'text-[13px] font-bold text-amber-600 mt-1 ml-3';

        try {
            const text = await runOCR(imageSrc, () => {});
            extractedPoNumberFromFile = extractPoNumberFromText(text);

            if (!extractedPoNumberFromFile) {
                status.innerText = 'Could not detect a P.O. number on the uploaded file — enter it manually to compare, or check the document quality.';
                status.className = 'text-[13px] font-bold text-amber-600 mt-1 ml-3';
                document.getElementById('po-match-icon').innerHTML = '';
                return;
            }
            checkPoMatch();
        } catch (err) {
            status.innerText = 'Could not verify P.O. number: ' + err.message;
            status.className = 'text-[13px] font-bold text-red-500 mt-1 ml-3';
        }
    }

    function setFormFieldsEnabled(enabled) {
        poUploaded = enabled;

        document.getElementById('pr_number').disabled = !enabled;
        document.getElementById('po_date').disabled = !enabled;
        document.getElementById('supplier').disabled = !enabled;
        document.getElementById('btn-add-row').disabled = !enabled;

        document.querySelectorAll('#item-cards-container input:not(.stock-counter)').forEach((el) => {
            el.disabled = !enabled;
        });

        const gate = document.getElementById('upload-po-gate-notice');
        if (gate) gate.classList.toggle('hidden', enabled);

        updateSubmitAvailability();
    }

    function getMinPoDateString() {
        const d = new Date();
        d.setMonth(d.getMonth() - 1);
        return d.toISOString().split('T')[0];
    }

    function getMaxPoDateString() {
        const d = new Date();
        return d.toISOString().split('T')[0];
    }

    function initPoDateRestriction() {
        const dateInput = document.getElementById('po_date');
        if (!dateInput) return;
        dateInput.min = getMinPoDateString();
        dateInput.max = getMaxPoDateString();
    }

    function validatePoDate(input) {
        if (input.value && input.max && input.value > input.max) {
            showToast('P.O. Date cannot be in the future.', 'error', input);
            input.value = '';
            return;
        }
        if (input.value && input.min && input.value < input.min) {
            showToast('P.O. Date cannot be more than 1 month in the past.', 'error', input);
            input.value = '';
        }
    }

    function importSpreadsheetItems(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            let workbook, sheet;
            try {
                const data = new Uint8Array(e.target.result);
                workbook = XLSX.read(data, {
                    type: 'array'
                });
                sheet = workbook.Sheets[workbook.SheetNames[0]];
            } catch (err) {
                showToast("Could not read that file. Make sure it's a valid Excel (.xlsx/.xls) or CSV file.", 'error', document.getElementById('btn-upload-trigger'));
                return;
            }

            currentPOFile = null;
            currentPOPreview = {
                type: 'spreadsheet',
                content: sheet,
                fileName: file.name
            };
            setFormFieldsEnabled(true);
            showPOPreview();
            document.getElementById('btn-upload-trigger').classList.add('hidden');
            document.getElementById('btn-view-po').classList.remove('hidden');
            document.getElementById('btn-view-pr').classList.remove('hidden');

            extractedPoNumberFromFile = null;
            document.getElementById('po-match-icon').innerHTML = '';
            document.getElementById('po-match-status').innerText = 'Spreadsheet previews can\'t be auto-verified — enter the P.O. Number manually.';
            document.getElementById('po-match-status').className = 'text-[13px] font-bold text-amber-600 mt-1 ml-3';
        };
        reader.readAsArrayBuffer(file);
    }

    function addItemCard() {
        const container = document.getElementById('item-cards-container');
        const nextStockNum = container.getElementsByClassName('item-card').length + 1;

        const card = document.createElement('div');
        card.className = "item-card bg-slate-50 rounded-2xl p-6 border border-slate-100 relative group transition-all hover:border-blue-200 shadow-sm space-y-4 animate-in slide-in-from-right-4";
        card.innerHTML = `
            <button type="button" onclick="removeCard(this)" class="absolute -right-2 -top-2 bg-red-500 text-white w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold shadow-lg hover:scale-110 transition-transform">×</button>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-2"><label class="text-[13px] font-black text-slate-600 mb-1.5 block uppercase">Stock #</label>
                <input type="text" name="stock_no[]" readonly class="stock-counter w-full p-3.5 rounded-xl border-none text-base font-black bg-blue-50 text-blue-600 shadow-sm text-center" value="${nextStockNum}"></div>
                <div class="col-span-10"><label class="text-[13px] font-black text-slate-600 mb-1.5 block uppercase">Description</label>
                <input type="text" name="description[]" class="w-full p-3.5 rounded-xl border-none text-base font-bold shadow-sm focus:ring-2 focus:ring-blue-500"></div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div><label class="text-[13px] font-black text-slate-600 mb-1.5 block uppercase">Qty</label>
                <input type="number" name="quantity[]" oninput="calculateCard(this)" class="qty w-full p-3.5 rounded-xl border-none text-base font-black text-blue-600 text-center shadow-sm"></div>
                <div><label class="text-[13px] font-black text-slate-600 mb-1.5 block uppercase">Unit Cost</label>
                <input type="number" step="0.01" name="unit_cost[]" oninput="calculateCard(this)" class="cost w-full p-3.5 rounded-xl border-none text-base font-bold text-right shadow-sm"></div>
                <div><label class="text-[13px] font-black text-blue-500 mb-1.5 block uppercase">Sub-Total (₱)</label>
                <input type="text" name="amount[]" readonly class="amount w-full p-3.5 rounded-xl border-none bg-blue-100/50 font-black text-blue-700 text-base text-right" value="0.00"></div>
            </div>`;
        container.appendChild(card);
    }

    function removeCard(btn) {
        btn.closest('.item-card').remove();
        reindexStockNumbers();
        calculateGrandTotal();
    }

    function reindexStockNumbers() {
        const counters = document.getElementsByClassName('stock-counter');
        for (let i = 0; i < counters.length; i++) {
            counters[i].value = i + 1;
        }
    }

    function calculateCard(input) {
        const card = input.closest('.item-card');
        const qty = card.querySelector('.qty').value || 0;
        const cost = card.querySelector('.cost').value || 0;
        const amount = parseFloat(qty) * parseFloat(cost);
        card.querySelector('.amount').value = amount.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        const amounts = document.getElementsByClassName('amount');
        let total = 0;
        for (let i = 0; i < amounts.length; i++) {
            total += parseFloat(amounts[i].value.replace(/,/g, '') || 0);
        }
        document.getElementById('total-cost').value = total.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function handlePOUpload(input) {
        if (!(input.files && input.files[0])) return;
        const file = input.files[0];
        const ext = file.name.split('.').pop().toLowerCase();

        if (SPREADSHEET_EXTS.includes(ext)) {
            // NOTE: We used to clear input.value here after extracting items,
            // which meant the spreadsheet Supply uploaded was never actually
            // submitted with the form — po_attachment ended up null even
            // though a file was uploaded. Now we leave the native file input
            // populated so it still gets sent to PrStore() as the attachment,
            // and the inspector can view/download the file Supply provided.
            importSpreadsheetItems(file);
            return;
        }

        currentPOFile = file;
        setFormFieldsEnabled(true);
        const poUrl = URL.createObjectURL(currentPOFile);
        currentPOPreview = {
            type: ext,
            content: poUrl,
            fileName: file.name
        };

        document.getElementById('btn-upload-trigger').classList.add('hidden');
        document.getElementById('btn-view-po').classList.remove('hidden');
        document.getElementById('btn-view-pr').classList.remove('hidden');
        showPOPreview();

        extractedPoNumberFromFile = null;
        document.getElementById('po-match-icon').innerHTML = '';

        if (IMAGE_EXTS.includes(ext)) {
            verifyPoFromUpload(poUrl);
        } else {
            document.getElementById('po-match-status').innerText = 'Automatic verification only works on image uploads (JPG/PNG) — this is a PDF, so please check the number manually.';
            document.getElementById('po-match-status').className = 'text-[13px] font-bold text-amber-600 mt-1 ml-3';
        }
    }

    document.getElementById('btn-view-pr').addEventListener('click', () => displayInViewer(currentPRFile, currentPRExt, "Purchase Request"));
    document.getElementById('btn-view-po').addEventListener('click', showPOPreview);

    initPoDateRestriction();
    setFormFieldsEnabled(false);

    function exitProcessing() {
        if (confirm("Discard entries and exit?")) location.reload();
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #0F172A;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #334155;
        border-radius: 10px;
    }

    #document-content img {
        image-rendering: -webkit-optimize-contrast;
        pointer-events: none;
    }
</style>
@endsection