@extends('layouts.Supply.app')

@section('content')
<div id="procurement-root" class="w-full max-w-[1920px] mx-auto p-2 md:p-6 antialiased">

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
            $fileUrl = route('supply.prFile', $pr->id);
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
    <div id="processing-view" class="hidden animate-in slide-in-from-bottom-10 duration-700" style="overflow-x:clip">
        <form id="po-form" action="{{ route('supply.PrStore') }}" method="POST" enctype="multipart/form-data" novalidate onsubmit="return validateBeforeSubmit(event)">
            @csrf
            <input type="hidden" name="management_id" id="selected-pr-id">

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
                        <button type="button" id="btn-view-pr" class="px-3 py-1.5 bg-slate-800 text-white text-[11px] font-black rounded-lg uppercase tracking-widest hover:bg-blue-600 transition shadow-md">View PR</button>
                        <div id="po-action-container" class="flex gap-2">
                            <button type="button" id="btn-upload-trigger" onclick="triggerPoUpload()" class="px-3 py-1.5 bg-blue-600 text-white text-[11px] font-black rounded-lg uppercase tracking-widest hover:bg-blue-700 transition shadow-md flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                <span id="upload-btn-label">Upload PO</span>
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
                <div class="lg:col-span-6 min-w-0 bg-slate-900 rounded-[2.5rem] overflow-hidden shadow-2xl h-[calc(100vh-100px)] sticky top-2 border-[8px] border-slate-800 group relative">
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

                    <div id="viewer-container" class="h-full w-full overflow-auto custom-scrollbar bg-slate-950 flex justify-center items-center relative cursor-grab active:cursor-grabbing p-4">
                        <div id="document-content" class="origin-center transition-transform duration-200 flex justify-center items-center w-full h-full min-h-[400px]">
                            <div class="text-center">
                                <p class="text-slate-500 font-black uppercase text-sm tracking-widest">Preview Area</p>
                            </div>
                        </div>
                    </div>

                    <div id="ocr-loading-overlay" class="hidden absolute inset-0 bg-slate-950/80 z-40 flex-col items-center justify-center gap-3">
                        <div class="w-10 h-10 border-4 border-amber-500 border-t-transparent rounded-full animate-spin"></div>
                        <p id="ocr-loading-text" class="text-white text-sm font-black uppercase tracking-widest">Reading document...</p>
                    </div>
                </div>

                {{-- RIGHT FORM PANEL --}}
                <div class="lg:col-span-6 min-w-0 bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 p-5 overflow-y-auto h-[calc(100vh-100px)] custom-scrollbar">
                    <div class="flex flex-col gap-4 min-h-full">

                        {{-- PO TABS (one tab per PO under this PR) --}}
                        <div id="po-tabs" class="sticky -top-5 z-30 -mx-5 -mt-5 px-5 pt-5 pb-3 bg-white/95 backdrop-blur-sm border-b border-slate-100 flex flex-nowrap items-center gap-2 overflow-x-auto custom-scrollbar"></div>

                        <div id="ocr-panel" class="hidden space-y-2 sticky top-[58px] z-20 -mx-5 px-5 pt-2 pb-4 bg-white/95 backdrop-blur-sm border-b border-amber-100 shadow-md">
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

                        {{-- PO PANELS are injected here by JS --}}
                        <div id="po-panels"></div>

                        {{-- Sticky footer: overall total + submit --}}
                        <div class="mt-auto sticky -bottom-5 z-30 -mx-5 -mb-5 px-5 pt-4 pb-5 bg-white/95 backdrop-blur-sm border-t border-slate-100 flex items-center gap-4">
                            <div class="shrink-0">
                                <span id="overall-po-count" class="block text-[11px] font-black text-slate-400 uppercase tracking-widest">1 PO</span>
                                <span class="text-xs font-black text-slate-500 uppercase">All POs ₱ <span id="overall-total" class="text-xl text-slate-900 font-black">0.00</span></span>
                            </div>
                            <button type="submit" id="submit-btn" disabled class="flex-1 bg-slate-900 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-[0.3em] shadow-xl hover:bg-emerald-600 transition-all active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-slate-900">Complete Transaction</button>
                        </div>
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
    let zoomLevel = 1;

    // ---- Multi-PO state: one entry per PO tab under this PR ----
    let poSeq = 0;
    let activeUid = null;
    const poStates = {}; // uid -> { file, preview, extractedNumber, uploaded, itemSeq }

    const existingPoNumbers = @json($purchaseOrders->pluck('po_number')->filter()->values());
    const normalizedExistingPoNumbers = existingPoNumbers.map((n) => normalizePoNumber(n));

    const IMAGE_EXTS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    const SPREADSHEET_EXTS = ['xlsx', 'xls', 'csv'];
    const PR_PREFIX = 'SO_A_';

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

    // =====================================================================
    //  MULTI-PO LOGIC
    // =====================================================================
    const CHECK_SVG = '<svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>';
    const CROSS_SVG = '<svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>';
    const STATUS_BASE = 'text-xs font-bold mt-1 leading-snug ';
    const ITEM_COLS = '2.25rem minmax(0,1fr) 4.5rem 6.5rem 7rem 1.75rem';

    const panelEl = (uid) => document.getElementById('po-panel-' + uid);
    const q = (uid, sel) => panelEl(uid).querySelector(sel);
    const uidList = () => Object.keys(poStates).map(Number);

    function poPanelHtml(uid) {
        return `
        <div id="po-panel-${uid}" class="po-panel hidden space-y-5" data-uid="${uid}">
            <input type="file" name="pos[${uid}][signed_po]" class="po-file-input hidden"
                accept=".pdf,image/*,.xlsx,.xls,.csv" onchange="handlePOUpload(this, ${uid})">

            {{-- Empty state: shown until this PO's file is uploaded --}}
            <div class="po-gate-notice flex flex-col items-center text-center gap-3 py-14 px-6 border-2 border-dashed border-amber-300 bg-amber-50/60 rounded-3xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <p class="text-sm font-black text-amber-700 uppercase tracking-wide">Upload this PO to start</p>
                <p class="text-xs font-bold text-slate-500 max-w-xs">The details and items for this PO unlock after its file is uploaded.</p>
                <button type="button" onclick="triggerPoUpload()" class="px-5 py-2.5 bg-blue-600 text-white text-xs font-black rounded-xl uppercase tracking-widest hover:bg-blue-700 transition shadow-md">Upload PO File</button>
            </div>

            <div class="po-body hidden space-y-5">
                <div class="min-w-0 px-1">
                    <p class="po-title text-[11px] font-black text-blue-600 uppercase tracking-widest">PO</p>
                    <p class="po-filename text-sm font-extrabold text-slate-700 truncate"></p>
                </div>

                {{-- PO DETAILS --}}
                <section class="bg-slate-50 rounded-2xl border border-slate-100 p-4 space-y-3">
                    <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest">PO Details</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">P.O. Number</label>
                                <div class="flex items-center gap-1"><span class="pr-format-icon"></span><span class="po-match-icon"></span></div>
                            </div>
                            <input type="text" name="pos[${uid}][po_number]" disabled autocomplete="off" inputmode="numeric"
                                class="po-number w-full px-3 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none border-gray-300 text-base font-mono text-inv-navy bg-white disabled:bg-slate-100 disabled:text-slate-400"
                                value="SO_A_" maxlength="17"
                                title="Format must be SO_A_YYYY_MM_XXX (e.g., SO_A_2026_07_000)"
                                oninput="formatPoNumberInput(event, ${uid})"
                                onfocus="if(!this.value) this.value='SO_A_';"
                                onblur="validatePoNumberFormat(this, ${uid})">
                            <p class="po-match-status ${STATUS_BASE} text-slate-500">Upload a signed PO to verify this number.</p>
                            <p class="po-duplicate-status hidden text-xs font-bold text-red-500 mt-1">This P.O. Number is already in use.</p>
                        </div>
                        <div>
                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1 block">Date</label>
                            <input type="date" name="pos[${uid}][po_date]" disabled
                                class="po-date w-full px-3 py-2.5 rounded-xl bg-white border border-gray-300 font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none disabled:bg-slate-100 disabled:text-slate-400"
                                oninput="validatePoDate(this)">
                            <p class="text-xs font-bold text-slate-500 mt-1">No future dates; up to 1 month back.</p>
                        </div>
                    </div>
                    <div>
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1 block">Supplier</label>
                        <input type="text" name="pos[${uid}][supplier]" disabled autocomplete="off"
                            class="po-supplier w-full px-3 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none border-gray-300 bg-white text-base font-bold text-inv-navy disabled:bg-slate-100 disabled:text-slate-400">
                    </div>
                </section>

                {{-- PROCUREMENT ITEMS (separate for every PO) --}}
                <section class="space-y-2">
                    <div class="flex items-center justify-between px-1">
                        <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest">Procurement Items</h4>
                        <span class="po-item-count text-[11px] font-black text-slate-400 uppercase tracking-widest">1 item</span>
                    </div>
                    <div class="grid gap-2 px-2 text-[10px] font-black text-slate-400 uppercase tracking-wider" style="grid-template-columns:${ITEM_COLS}">
                        <span class="text-center">#</span><span>Description</span><span class="text-center">Qty</span><span class="text-right">Unit Cost</span><span class="text-right">Subtotal</span><span></span>
                    </div>
                    <div class="item-cards-container space-y-2"></div>
                    <button type="button" disabled onclick="addItemCard(${uid})"
                        class="btn-add-row w-full py-3 rounded-xl border-2 border-dashed border-slate-300 text-slate-600 text-xs font-black uppercase tracking-widest hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition disabled:opacity-40 disabled:cursor-not-allowed">+ Add Item</button>

                    <div class="flex items-center justify-between bg-blue-600 rounded-2xl px-5 py-4 text-white shadow-lg">
                        <span class="font-black uppercase text-xs tracking-[0.2em]">PO Total</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-sm font-bold opacity-70">₱</span>
                            <input type="text" name="pos[${uid}][total_cost]" readonly value="0.00"
                                class="po-total bg-transparent border-none text-2xl font-black focus:ring-0 text-right w-44 p-0">
                        </div>
                    </div>
                </section>
            </div>
        </div>`;
    }

    function itemCardHtml(uid, k, n, disabled) {
        const dis = disabled ? 'disabled' : '';
        const dCls = 'disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed';
        const inp = 'w-full px-2.5 py-2.5 rounded-lg border border-slate-200 bg-white text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none';
        const rm = n > 1 ?
            `<button type="button" onclick="removeCard(this, ${uid})" title="Remove item" class="w-7 h-7 rounded-full bg-white border border-slate-200 text-slate-400 hover:bg-red-500 hover:border-red-500 hover:text-white text-sm font-bold transition">×</button>` :
            '<span></span>';
        return `
        <div class="item-card grid gap-2 items-center bg-slate-50 rounded-xl px-2 py-2 border border-slate-100 hover:border-blue-200 transition" style="grid-template-columns:${ITEM_COLS}">
            <input type="text" name="pos[${uid}][items][${k}][stock_no]" readonly value="${n}" class="stock-counter w-full py-2.5 rounded-lg bg-blue-50 text-blue-600 text-sm font-black text-center border-none">
            <input type="text" name="pos[${uid}][items][${k}][description]" ${dis} placeholder="Item description" class="item-desc ${inp} font-bold ${dCls}">
            <input type="number" min="0" name="pos[${uid}][items][${k}][quantity]" ${dis} placeholder="0" oninput="calculateCard(this, ${uid})" class="qty ${inp} text-center font-black text-blue-600 ${dCls}">
            <input type="number" min="0" step="0.01" name="pos[${uid}][items][${k}][unit_cost]" ${dis} placeholder="0.00" oninput="calculateCard(this, ${uid})" class="cost ${inp} text-right font-bold ${dCls}">
            <input type="text" name="pos[${uid}][items][${k}][amount]" readonly value="0.00" class="amount w-full py-2.5 px-2 rounded-lg bg-blue-100/50 text-blue-700 text-sm font-black text-right border-none">
            ${rm}
        </div>`;
    }

    function updateItemCount(uid) {
        const n = q(uid, '.item-cards-container').getElementsByClassName('item-card').length;
        q(uid, '.po-item-count').innerText = n + (n === 1 ? ' item' : ' items');
    }

    // ---------- PO tabs ----------
    function addPo(openPicker = true) {
        const uid = ++poSeq;
        poStates[uid] = { file: null, preview: null, extractedNumber: null, uploaded: false, itemSeq: 0 };

        document.getElementById('po-panels').insertAdjacentHTML('beforeend', poPanelHtml(uid));
        const dateInput = q(uid, '.po-date');
        dateInput.min = getMinPoDateString();
        dateInput.max = getMaxPoDateString();

        addItemCard(uid, true);
        setPoFieldsEnabled(uid, false);
        setActivePo(uid);
        updateOverallSummary();

        if (openPicker && uidList().length > 1) triggerPoUpload();
    }

    function removePo(uid) {
        if (uidList().length <= 1) return;
        if (!confirm('Remove this PO and everything entered for it?')) return;

        panelEl(uid).remove();
        delete poStates[uid];
        if (activeUid === uid) {
            setActivePo(uidList()[0]);
        } else {
            renderTabs();
        }
        refreshAllDuplicateUI();
        updateOverallSummary();
    }

    function renderTabs() {
        const uids = uidList();
        const bar = document.getElementById('po-tabs');
        bar.innerHTML = uids.map((uid, i) => {
            const st = poStates[uid];
            const active = uid === activeUid;
            const num = q(uid, '.po-number').value;
            const complete = num.length === PR_PREFIX.length + 11;
            const sub = complete ? num : (st.uploaded ? 'Fill in details' : 'Awaiting upload');
            const dot = st.uploaded ? 'bg-emerald-400' : 'bg-amber-400';
            const tabCls = active ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-400';
            const subCls = active ? 'text-slate-300' : 'text-slate-400';
            const rm = uids.length > 1 ?
                `<button type="button" onclick="removePo(${uid})" title="Remove this PO" class="ml-1 w-6 h-6 shrink-0 rounded-full text-slate-400 hover:bg-red-500 hover:text-white text-sm font-bold transition">×</button>` : '';
            panelEl(uid).querySelector('.po-title').innerText = 'PO ' + (i + 1) + ' of ' + uids.length;
            return `<div class="flex items-center shrink-0">
                <button type="button" onclick="setActivePo(${uid})" class="px-3 py-1.5 rounded-xl border text-left flex items-center gap-2 transition ${tabCls}">
                    <span class="w-2 h-2 rounded-full shrink-0 ${dot}"></span>
                    <span class="leading-tight">
                        <span class="block text-[12px] font-black uppercase tracking-widest">PO ${i + 1}</span>
                        <span class="block text-[10px] font-bold font-mono ${subCls}">${sub}</span>
                    </span>
                </button>${rm}
            </div>`;
        }).join('') + `<button type="button" onclick="addPo()" class="shrink-0 px-4 py-3 rounded-xl text-[12px] font-black uppercase tracking-widest border-2 border-dashed border-blue-300 text-blue-600 hover:bg-blue-50 transition">+ Add PO</button>`;
    }

    function setActivePo(uid) {
        activeUid = uid;
        document.querySelectorAll('.po-panel').forEach((p) => p.classList.toggle('hidden', Number(p.dataset.uid) !== uid));
        document.getElementById('ocr-panel').classList.add('hidden');
        renderTabs();
        updateHeaderButtons();

        const st = poStates[uid];
        if (st.preview) {
            showPOPreview();
        } else if (currentPRFile) {
            displayInViewer(currentPRFile, currentPRExt, "Purchase Request");
        }
    }

    function updateHeaderButtons() {
        const st = poStates[activeUid];
        document.getElementById('upload-btn-label').innerText = st && st.uploaded ? 'Replace PO' : 'Upload PO';
        document.getElementById('btn-view-po').classList.toggle('hidden', !(st && st.uploaded));
    }

    function triggerPoUpload() {
        if (activeUid === null) return;
        q(activeUid, '.po-file-input').click();
    }

    function showPOPreview() {
        const st = poStates[activeUid];
        if (!st || !st.preview) return;
        const p = st.preview;
        if (p.type === 'spreadsheet') {
            displaySpreadsheetPreview(p.content, p.fileName);
        } else {
            displayInViewer(p.content, p.type, "Signed PO " + (uidList().indexOf(activeUid) + 1));
        }
    }

    // ---------- field enabling / date rules ----------
    function setPoFieldsEnabled(uid, enabled) {
        const p = panelEl(uid);
        poStates[uid].uploaded = enabled;
        p.querySelector('.po-number').disabled = !enabled;
        p.querySelector('.po-date').disabled = !enabled;
        p.querySelector('.po-supplier').disabled = !enabled;
        p.querySelector('.btn-add-row').disabled = !enabled;
        p.querySelectorAll('.item-cards-container input:not(.stock-counter):not(.amount)').forEach((el) => {
            el.disabled = !enabled;
        });
        p.querySelector('.po-gate-notice').classList.toggle('hidden', enabled);
        p.querySelector('.po-body').classList.toggle('hidden', !enabled);
        renderTabs();
        updateHeaderButtons();
        updateSubmitAvailability();
    }

    function getMinPoDateString() {
        const d = new Date();
        d.setMonth(d.getMonth() - 1);
        return d.toISOString().split('T')[0];
    }

    function getMaxPoDateString() {
        return new Date().toISOString().split('T')[0];
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

    // ---------- P.O. number handling (per PO) ----------
    function formatPoNumberInput(e, uid) {
        const input = e.target;
        let digits = input.value.slice(PR_PREFIX.length).replace(/\D/g, '').slice(0, 9);

        let formatted = PR_PREFIX;
        if (digits.length > 0) formatted += digits.slice(0, 4);
        if (digits.length > 4) formatted += '_' + digits.slice(4, 6);
        if (digits.length > 6) formatted += '_' + digits.slice(6, 9);

        input.value = formatted;
        updatePrFormatIcon(input, uid);
        checkPoMatch(uid);
        refreshAllDuplicateUI();
        renderTabs();
    }

    function updatePrFormatIcon(input, uid) {
        const icon = q(uid, '.pr-format-icon');
        const fullPattern = new RegExp('^' + PR_PREFIX + '\\d{4}_\\d{2}_\\d{3}$');
        const isComplete = input.value.length === PR_PREFIX.length + 11;
        icon.innerHTML = !isComplete ? '' : (fullPattern.test(input.value) ? CHECK_SVG : CROSS_SVG);
    }

    function validatePoNumberFormat(input, uid) {
        if (!input.value.startsWith(PR_PREFIX)) {
            input.value = PR_PREFIX;
            updatePrFormatIcon(input, uid);
        }
        refreshAllDuplicateUI();
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

    function setMatchStatus(uid, text, colorCls) {
        const s = q(uid, '.po-match-status');
        s.innerText = text;
        s.className = 'po-match-status ' + STATUS_BASE + colorCls;
    }

    function checkPoMatch(uid) {
        const icon = q(uid, '.po-match-icon');
        const typed = q(uid, '.po-number').value;
        const extracted = poStates[uid].extractedNumber;

        if (!extracted) {
            icon.innerHTML = '';
            setMatchStatus(uid, 'Upload a signed PO to verify this number.', 'text-slate-600');
            return;
        }
        if (!typed) {
            icon.innerHTML = '';
            setMatchStatus(uid, 'Enter the P.O. Number to compare against the uploaded file.', 'text-slate-600');
            return;
        }
        if (normalizePoNumber(typed) === normalizePoNumber(extracted)) {
            icon.innerHTML = CHECK_SVG.replace('h-4 w-4', 'h-5 w-5');
            setMatchStatus(uid, 'Matches the number found on the uploaded PO.', 'text-emerald-600');
        } else {
            icon.innerHTML = CROSS_SVG.replace('h-4 w-4', 'h-5 w-5');
            setMatchStatus(uid, 'Does not match "' + extracted + '" found on the uploaded PO.', 'text-red-500');
        }
    }

    // Duplicate = already in the database OR typed on another PO tab of this same PR
    function isPoNumberDuplicate(value, uid) {
        const isComplete = value.length === PR_PREFIX.length + 11;
        if (!isComplete) return false;
        const norm = normalizePoNumber(value);
        if (normalizedExistingPoNumbers.includes(norm)) return true;
        return uidList().some((other) => other !== uid && normalizePoNumber(q(other, '.po-number').value) === norm);
    }

    function refreshAllDuplicateUI() {
        uidList().forEach((uid) => {
            const input = q(uid, '.po-number');
            const dup = isPoNumberDuplicate(input.value, uid);
            q(uid, '.po-duplicate-status').classList.toggle('hidden', !dup);
            input.classList.toggle('border-red-500', dup);
            input.classList.toggle('ring-2', dup);
            input.classList.toggle('ring-red-500', dup);
        });
        updateSubmitAvailability();
    }

    function updateSubmitAvailability() {
        const submitBtn = document.getElementById('submit-btn');
        if (!submitBtn) return;
        const uids = uidList();
        const allUploaded = uids.length > 0 && uids.every((uid) => poStates[uid].uploaded);
        const anyDup = uids.some((uid) => poStates[uid].uploaded && isPoNumberDuplicate(q(uid, '.po-number').value, uid));
        submitBtn.disabled = !allUploaded || anyDup;
    }

    async function verifyPoFromUpload(uid, imageSrc) {
        setMatchStatus(uid, 'Reading uploaded PO to verify number...', 'text-amber-600');
        try {
            const text = await runOCR(imageSrc, () => {});
            if (!poStates[uid]) return; // tab was removed while OCR ran
            poStates[uid].extractedNumber = extractPoNumberFromText(text);

            if (!poStates[uid].extractedNumber) {
                setMatchStatus(uid, 'Could not detect a P.O. number on the uploaded file — enter it manually to compare, or check the document quality.', 'text-amber-600');
                q(uid, '.po-match-icon').innerHTML = '';
                return;
            }
            checkPoMatch(uid);
        } catch (err) {
            if (poStates[uid]) setMatchStatus(uid, 'Could not verify P.O. number: ' + err.message, 'text-red-500');
        }
    }

    // ---------- upload handling (per PO) ----------
    function handlePOUpload(input, uid) {
        if (!(input.files && input.files[0])) return;
        const file = input.files[0];
        const ext = file.name.split('.').pop().toLowerCase();
        const st = poStates[uid];

        st.file = file;
        q(uid, '.po-filename').innerText = file.name;
        st.extractedNumber = null;
        q(uid, '.po-match-icon').innerHTML = '';

        if (SPREADSHEET_EXTS.includes(ext)) {
            // Keep the native file input populated so the spreadsheet is still
            // submitted with the form as this PO's attachment.
            const reader = new FileReader();
            reader.onload = (e) => {
                let sheet;
                try {
                    const wb = XLSX.read(new Uint8Array(e.target.result), { type: 'array' });
                    sheet = wb.Sheets[wb.SheetNames[0]];
                } catch (err) {
                    showToast("Could not read that file. Make sure it's a valid Excel (.xlsx/.xls) or CSV file.", 'error', document.getElementById('btn-upload-trigger'));
                    return;
                }
                st.preview = { type: 'spreadsheet', content: sheet, fileName: file.name };
                setPoFieldsEnabled(uid, true);
                setActivePo(uid);
                setMatchStatus(uid, "Spreadsheet previews can't be auto-verified — enter the P.O. Number manually.", 'text-amber-600');
            };
            reader.readAsArrayBuffer(file);
            return;
        }

        const poUrl = URL.createObjectURL(file);
        st.preview = { type: ext, content: poUrl, fileName: file.name };
        setPoFieldsEnabled(uid, true);
        setActivePo(uid);

        if (IMAGE_EXTS.includes(ext)) {
            verifyPoFromUpload(uid, poUrl);
        } else {
            setMatchStatus(uid, 'Automatic verification only works on image uploads (JPG/PNG) — this is a PDF, so please check the number manually.', 'text-amber-600');
        }
    }

    // ---------- item rows (per PO) ----------
    function addItemCard(uid, initial = false) {
        const st = poStates[uid];
        const container = q(uid, '.item-cards-container');
        const n = container.getElementsByClassName('item-card').length + 1;
        const k = ++st.itemSeq;
        container.insertAdjacentHTML('beforeend', itemCardHtml(uid, k, n, initial));
        if (!initial) container.lastElementChild.classList.add('animate-in', 'slide-in-from-right-4');
        updateItemCount(uid);
    }

    function removeCard(btn, uid) {
        btn.closest('.item-card').remove();
        reindexStockNumbers(uid);
        calculatePoTotal(uid);
        updateItemCount(uid);
    }

    function reindexStockNumbers(uid) {
        q(uid, '.item-cards-container').querySelectorAll('.stock-counter').forEach((el, i) => {
            el.value = i + 1;
        });
    }

    const fmtMoney = (n) => n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function calculateCard(input, uid) {
        const card = input.closest('.item-card');
        const qty = parseFloat(card.querySelector('.qty').value) || 0;
        const cost = parseFloat(card.querySelector('.cost').value) || 0;
        card.querySelector('.amount').value = fmtMoney(qty * cost);
        calculatePoTotal(uid);
    }

    function calculatePoTotal(uid) {
        let total = 0;
        q(uid, '.item-cards-container').querySelectorAll('.amount').forEach((el) => {
            total += parseFloat(el.value.replace(/,/g, '') || 0);
        });
        q(uid, '.po-total').value = fmtMoney(total);
        updateOverallSummary();
    }

    function updateOverallSummary() {
        const uids = uidList();
        let sum = 0;
        uids.forEach((uid) => {
            sum += parseFloat(q(uid, '.po-total').value.replace(/,/g, '') || 0);
        });
        document.getElementById('overall-total').innerText = fmtMoney(sum);
        document.getElementById('overall-po-count').innerText = uids.length + (uids.length === 1 ? ' PO' : ' POs');
    }

    // ---------- OCR "Extract PO Items" (uses the active PO tab) ----------
    async function extractPOItemDescriptions() {
        const extractBtn = document.getElementById('btn-extract-text');
        const st = poStates[activeUid];

        if (st.preview && st.preview.type === 'spreadsheet') {
            showToast("This is a spreadsheet preview, not a scannable image — enter item descriptions manually or copy from the spreadsheet.", 'info', extractBtn);
            return;
        }
        if (!st.file) {
            showToast("Please upload a signed PO image for this PO first.", 'warning', extractBtn);
            return;
        }
        const ext = st.file.name.split('.').pop().toLowerCase();
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
            const text = await runOCR(st.preview.content, (m) => {
                loadingText.innerText = m.status === 'recognizing text' ?
                    'Reading PO... ' + Math.round(m.progress * 100) + '%' :
                    m.status;
            });
            const lines = text.split('\n').map((l) => l.trim()).filter(looksLikeDescriptionLine);
            output.value = lines.length === 0 ?
                "(No item descriptions detected on the uploaded PO — try a clearer photo, or enter them manually.)" :
                lines.join('\n');
        } catch (err) {
            output.value = "Could not read text from the uploaded PO: " + err.message;
        } finally {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }
    }

    // ---------- submit validation across ALL POs ----------
    function validateBeforeSubmit(e) {
        const uids = uidList();
        for (let i = 0; i < uids.length; i++) {
            const uid = uids[i];
            const label = 'PO ' + (i + 1);
            const fail = (msg, el) => {
                e.preventDefault();
                setActivePo(uid);
                showToast(label + ': ' + msg, 'error', el || document.getElementById('submit-btn'));
                if (el && el.focus) el.focus();
                return false;
            };

            if (!poStates[uid].uploaded) return fail('upload the signed PO file first.');

            const num = q(uid, '.po-number');
            if (!new RegExp('^' + PR_PREFIX + '\\d{4}_\\d{2}_\\d{3}$').test(num.value)) return fail('P.O. Number must look like SO_A_YYYY_MM_XXX.', num);
            if (isPoNumberDuplicate(num.value, uid)) return fail('this P.O. Number is already in use.', num);

            const date = q(uid, '.po-date');
            if (!date.value) return fail('P.O. Date is required.', date);

            const supplier = q(uid, '.po-supplier');
            if (!supplier.value.trim()) return fail('Supplier is required.', supplier);

            const cards = q(uid, '.item-cards-container').querySelectorAll('.item-card');
            for (const card of cards) {
                const stock = card.querySelector('.stock-counter').value;
                const desc = card.querySelector('.item-desc');
                const qty = card.querySelector('.qty');
                const cost = card.querySelector('.cost');
                if (!desc.value.trim()) return fail('item #' + stock + ' needs a description.', desc);
                if (!(parseFloat(qty.value) > 0)) return fail('item #' + stock + ' needs a quantity above 0.', qty);
                if (!(parseFloat(cost.value) >= 0) || cost.value === '') return fail('item #' + stock + ' needs a unit cost.', cost);
            }
        }
        return true;
    }

    document.getElementById('btn-view-pr').addEventListener('click', () => displayInViewer(currentPRFile, currentPRExt, "Purchase Request"));
    document.getElementById('btn-view-po').addEventListener('click', showPOPreview);

    // ---------------------------------------------------------------
    //  Keep this page clear of the fixed sidebar.
    //  Finds the fixed/sticky sidebar and, if it covers the left edge of
    //  this page's content, pads the page so everything stays visible.
    //  Re-runs on resize, sidebar resize, and after any click (sidebar toggle).
    // ---------------------------------------------------------------
    function findSidebar() {
        const candidates = document.querySelectorAll('aside, nav, div');
        for (const el of candidates) {
            if (el.closest('#procurement-root')) continue;
            const cs = getComputedStyle(el);
            if (cs.position !== 'fixed' && cs.position !== 'sticky') continue;
            const r = el.getBoundingClientRect();
            if (r.left < 80 && r.width > 50 && r.width < 500 && r.height > window.innerHeight * 0.6) return el;
        }
        return null;
    }

    function fixSidebarOverlap() {
        const root = document.getElementById('procurement-root');
        if (!root) return;
        root.style.paddingLeft = '';
        const sb = findSidebar();
        if (!sb) return;
        const overlap = sb.getBoundingClientRect().right - root.getBoundingClientRect().left;
        if (overlap > 0) root.style.paddingLeft = (overlap + 16) + 'px';
    }

    window.addEventListener('resize', fixSidebarOverlap);
    document.addEventListener('click', () => setTimeout(fixSidebarOverlap, 400));
    window.addEventListener('load', fixSidebarOverlap);
    fixSidebarOverlap();
    const _sb = findSidebar();
    if (_sb && 'ResizeObserver' in window) new ResizeObserver(fixSidebarOverlap).observe(_sb);

    addPo(false); // start with PO 1
    updateSubmitAvailability();

    function exitProcessing() {
        if (confirm("Discard entries and exit?")) location.reload();
    }
</script>

<style>
    .item-card input[type=number]::-webkit-outer-spin-button,
    .item-card input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .item-card input[type=number] { -moz-appearance: textfield; }
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