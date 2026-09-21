@extends('layouts.Admin.app')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- SheetJS for previewing uploaded Excel/CSV files --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

@if (session()->has('success'))
<style>
    .custom-success-icon-wrap {
        display: flex;
        justify-content: center;
        margin-top: 4px;
    }

    .custom-success-checkmark {
        width: 84px;
        height: 84px;
    }

    .custom-success-checkmark__circle {
        fill: none;
        stroke: #16a34a;
        stroke-width: 3;
        stroke-dasharray: 151;
        stroke-dashoffset: 151;
        animation: custom-success-circle 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }

    .custom-success-checkmark__check {
        fill: none;
        stroke: #16a34a;
        stroke-width: 4;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-dasharray: 36;
        stroke-dashoffset: 36;
        animation: custom-success-check 0.35s 0.55s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }

    @keyframes custom-success-circle {
        to {
            stroke-dashoffset: 0;
        }
    }

    @keyframes custom-success-check {
        to {
            stroke-dashoffset: 0;
        }
    }
</style>
<script>
    Swal.fire({
        html: `
            <div class="custom-success-icon-wrap">
                <svg class="custom-success-checkmark" viewBox="0 0 52 52">
                    <circle class="custom-success-checkmark__circle" cx="26" cy="26" r="24" />
                    <path class="custom-success-checkmark__check" d="M14 27l7 7 16-16" />
                </svg>
            </div>
            <h2 style="margin-top:14px;font-size:1.15rem;font-weight:700;color:#1f2937;">Successfully!</h2>
            <p style="margin-top:4px;color:#6b7280;font-size:0.9rem;">{{ session('success') }}</p>
        `,
        showConfirmButton: false,
        timer: 1800,
        allowOutsideClick: false,
        showClass: {
            popup: '',
            icon: ''
        },
        hideClass: {
            popup: ''
        }
    }).then(() => {
        window.location.reload();
    });
</script>
@endif

<div class="space-y-6">

    {{-- Page Title --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-800">PR Management</h1>
        <p class="text-sm text-gray-500">{{ now()->setTimezone('Asia/Manila')->format('F d, Y - h:i A') }}</p>
    </div>

    <form action="{{ route('admin.PRManagement') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- LEFT COLUMN: DOCUMENT PREVIEW (80% width on large screens) --}}
            <div class="lg:col-span-8 bg-white rounded-2xl shadow-sm border p-4 min-h-[600px] flex flex-col">
                <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="bg-blue-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs">1</span>
                    Document Viewer
                </h2>

                <div class="relative flex-1 border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center overflow-hidden bg-gray-50">

                    {{-- UPLOAD PLACEHOLDER: Only visible if no file is selected --}}
                    <label id="uploadPlaceholder" for="prFile" class="flex flex-col items-center justify-center text-center cursor-pointer p-10 hover:bg-gray-100 transition w-full h-full">
                        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 16V4m0 0l-4 4m4-4l4 4M4 20h16"></path>
                            </svg>
                        </div>
                        <p class="font-bold text-gray-700">Click to Upload Purchase Request</p>
                        <p class="text-xs text-gray-400 mt-1">Supports PDF, JPG, PNG, XLSX</p>
                    </label>

                    {{-- IMAGE PREVIEW --}}
                    <img id="imagePreview" class="hidden w-full h-full object-contain" alt="PR Preview" />

                    {{-- PDF PREVIEW (using iframe) --}}
                    <iframe id="pdfPreview" class="hidden w-full h-full border-none" src=""></iframe>

                    {{-- EXCEL / CSV PREVIEW (rendered as a table inside an iframe) --}}
                    <iframe id="excelPreview" class="hidden w-full h-full border-none bg-white" src=""></iframe>

                    <input type="file" id="prFile" name="pr_management" accept=".jpg,.jpeg,.png,.pdf,.xlsx,.xls,.csv" class="hidden" required>
                </div>

                {{-- VALIDATION ERROR: shows if the backend rejects the uploaded file --}}
                @error('pr_management')
                <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- RIGHT COLUMN: DATA ENTRY (40% width on large screens) --}}
            <div class="lg:col-span-4 space-y-6">

                {{-- PR Number Card --}}
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <span class="bg-blue-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs">2</span>
                        Enter PR Details
                    </h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Purchase Request Number</label>
                        <input type="text"
                            name="pr_number"
                            id="pr_number"
                            required
                            disabled
                            class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none border-gray-300 text-lg font-mono text-inv-navy disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed"
                            value="SO_A_"
                            maxlength="17"
                            pattern="SO_A_\d{4}_\d{2}_\d{3}"
                            title="Format must be SO_A_YYYY_MM_XXX (e.g., SO_A_2026_00_000)">
                        <p id="prNumberGateNotice" class="text-xs text-amber-600 font-semibold mt-2">Upload the PR document first to fill in this field.</p>
                        <p id="prNumberHelp" class="hidden text-xs text-gray-500 mt-2 italic">Refer to the document </p>

                        {{-- LIVE DUPLICATE CHECK: shown via AJAX as soon as a full PR number is typed --}}
                        <p id="prNumberWarning" class="hidden text-xs text-red-600 font-semibold mt-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                            </svg>
                            This PR Number is already used. Please enter a different one.
                        </p>
                        <p id="prNumberChecking" class="hidden text-xs text-gray-400 italic mt-1">Checking PR number...</p>

                        {{-- VALIDATION ERROR: shows if pr_number fails backend validation --}}
                        @error('pr_number')
                        <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="fileInfo" class="hidden mt-6 p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <p class="text-[10px] uppercase tracking-wider text-blue-500 font-bold mb-1">Active File</p>
                        <p id="fileNameDisplay" class="text-sm text-blue-800 font-medium truncate"></p>
                        <button type="button" onclick="resetFile()" class="text-red-500 text-xs font-bold mt-2 hover:underline">
                            Change File
                        </button>
                    </div>
                </div>

                {{-- Action Card --}}
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <button type="submit" id="submitBtn" class="w-full py-4 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold shadow-lg transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span> Final Submit</span>
                    </button>
                    <a href="{{ route('dashboard') }}" class="block w-full mt-3 py-2 text-center text-gray-500 hover:text-gray-700 font-medium transition-all">
                        Cancel
                    </a>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
    const prInput = document.getElementById('prFile');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const imagePreview = document.getElementById('imagePreview');
    const pdfPreview = document.getElementById('pdfPreview');
    const excelPreview = document.getElementById('excelPreview');
    const fileInfo = document.getElementById('fileInfo');
    const fileNameDisplay = document.getElementById('fileNameDisplay');

    const EXCEL_EXTS = ['xlsx', 'xls', 'csv'];

    // Hides all three preview surfaces before showing the one that matches the uploaded file
    function hideAllPreviews() {
        imagePreview.classList.add('hidden');
        pdfPreview.classList.add('hidden');
        excelPreview.classList.add('hidden');
    }

    // Parses the workbook and renders its first sheet as an HTML table inside the iframe,
    // wrapped in its own minimal stylesheet so it doesn't inherit page styling.
    function previewExcelFile(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            let workbook, sheet, tableHtml;
            try {
                const data = new Uint8Array(e.target.result);
                workbook = XLSX.read(data, {
                    type: 'array'
                });
                sheet = workbook.Sheets[workbook.SheetNames[0]];
                tableHtml = XLSX.utils.sheet_to_html(sheet, {
                    editable: false
                });
            } catch (err) {
                excelPreview.srcdoc = `<p style="font-family:sans-serif;color:#dc2626;padding:16px;">Could not read this file. Make sure it's a valid Excel or CSV file.</p>`;
                excelPreview.classList.remove('hidden');
                return;
            }

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

            excelPreview.srcdoc = styledHtml;
            hideAllPreviews();
            excelPreview.classList.remove('hidden');
        };
        reader.readAsArrayBuffer(file);
    }

    prInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        // Display filename in the sidebar
        fileNameDisplay.textContent = file.name;
        fileInfo.classList.remove('hidden');
        uploadPlaceholder.classList.add('hidden');

        // Unlock the PR Number field now that a document has been provided
        const prNumberInput = document.getElementById('pr_number');
        const prNumberGateNotice = document.getElementById('prNumberGateNotice');
        const prNumberHelp = document.getElementById('prNumberHelp');
        prNumberInput.disabled = false;
        if (prNumberGateNotice) prNumberGateNotice.classList.add('hidden');
        if (prNumberHelp) prNumberHelp.classList.remove('hidden');

        const ext = file.name.split('.').pop().toLowerCase();

        if (ext === 'pdf' || file.type === "application/pdf") {
            const fileURL = URL.createObjectURL(file);
            hideAllPreviews();
            pdfPreview.src = fileURL;
            pdfPreview.classList.remove('hidden');
        } else if (EXCEL_EXTS.includes(ext)) {
            previewExcelFile(file);
        } else if (file.type.startsWith("image/")) {
            const fileURL = URL.createObjectURL(file);
            hideAllPreviews();
            imagePreview.src = fileURL;
            imagePreview.classList.remove('hidden');
        }
    });

    function resetFile() {
        prInput.value = "";
        uploadPlaceholder.classList.remove('hidden');
        hideAllPreviews();
        imagePreview.src = "";
        pdfPreview.src = "";
        excelPreview.src = "";
        fileInfo.classList.add('hidden');

        // Re-lock the PR Number field since there's no document to reference anymore
        const prNumberInput = document.getElementById('pr_number');
        const prNumberGateNotice = document.getElementById('prNumberGateNotice');
        const prNumberHelp = document.getElementById('prNumberHelp');
        const prWarning = document.getElementById('prNumberWarning');
        const prChecking = document.getElementById('prNumberChecking');
        const submitBtnEl = document.getElementById('submitBtn');
        prNumberInput.value = "SO_A_";
        prNumberInput.disabled = true;
        if (prNumberGateNotice) prNumberGateNotice.classList.remove('hidden');
        if (prNumberHelp) prNumberHelp.classList.add('hidden');
        if (prWarning) prWarning.classList.add('hidden');
        if (prChecking) prChecking.classList.add('hidden');
        if (submitBtnEl) submitBtnEl.disabled = false;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const prInput = document.getElementById('pr_number');
        const prWarning = document.getElementById('prNumberWarning');
        const prChecking = document.getElementById('prNumberChecking');
        const submitBtn = document.getElementById('submitBtn');

        const FULL_PATTERN = /^SO_A_\d{4}_\d{2}_\d{3}$/;
        let debounceTimer = null;
        let isDuplicate = false;

        // Hides both the "checking..." and "duplicate" messages
        function clearPrMessages() {
            prWarning.classList.add('hidden');
            prChecking.classList.add('hidden');
        }

        // Builds the "SO_A_YYYY_MM_XXX" string from a raw digit string (max 9 digits).
        function formatFromDigits(numbersOnly) {
            let formatted = 'SO_A_';
            if (numbersOnly.length > 0) {
                formatted += numbersOnly.substring(0, 4);
            }
            if (numbersOnly.length >= 4) {
                formatted += '_' + numbersOnly.substring(4, 6);
            }
            if (numbersOnly.length >= 6) {
                formatted += '_' + numbersOnly.substring(6, 9);
            }
            return formatted;
        }

        // Counts how many digit characters appear before a given cursor position
        // in the currently displayed (formatted) string.
        function digitsBeforePosition(value, pos) {
            let count = 0;
            for (let i = 0; i < pos && i < value.length; i++) {
                if (/\d/.test(value[i])) count++;
            }
            return count;
        }

        // Given a target digit count (how many digits should sit before the cursor
        // after formatting), returns the caret position inside the newly formatted string.
        function caretPositionForDigitCount(formatted, digitCount) {
            if (digitCount <= 0) return 5; // right after the 'SO_A_' prefix
            let seen = 0;
            for (let i = 5; i < formatted.length; i++) {
                if (/\d/.test(formatted[i])) {
                    seen++;
                    if (seen === digitCount) return i + 1;
                }
            }
            return formatted.length;
        }

        function applyFormattedValue(numbersOnly, caretDigitCount) {
            const formatted = formatFromDigits(numbersOnly);
            prInput.value = formatted;
            const caretPos = caretPositionForDigitCount(formatted, caretDigitCount);
            prInput.setSelectionRange(caretPos, caretPos);
            runDuplicateCheck(formatted);
        }

        function runDuplicateCheck(formatted) {
            clearTimeout(debounceTimer);
            if (FULL_PATTERN.test(formatted)) {
                debounceTimer = setTimeout(() => checkDuplicate(formatted), 400);
            } else {
                clearPrMessages();
                isDuplicate = false;
                submitBtn.disabled = false;
            }
        }

        // Calls the backend to see if this PR number is already used.
        // Only fires once the number is fully formatted (SO_A_YYYY_MM_XXX).
        function checkDuplicate(value) {
            prChecking.classList.remove('hidden');
            prWarning.classList.add('hidden');

            fetch(`{{ route('admin.checkPrNumber') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ??
                            document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        pr_number: value
                    })
                })
                .then(res => res.json())
                .then(data => {
                    prChecking.classList.add('hidden');
                    isDuplicate = !!data.exists;
                    if (isDuplicate) {
                        prWarning.classList.remove('hidden');
                        submitBtn.disabled = true;
                    } else {
                        prWarning.classList.add('hidden');
                        submitBtn.disabled = false;
                    }
                })
                .catch(() => {
                    // If the check itself fails (network issue), don't block submission —
                    // backend validation is still the final safety net.
                    prChecking.classList.add('hidden');
                    submitBtn.disabled = false;
                });
        }

        // Pangharang kung sakaling subukan nilang gamitin ang 'Backspace'/'Delete' sa mismong
        // prefix, AT tinitiyak na isang pindot lang ay isang digit lang ang matatanggal
        // (kahit nasa tabi ng auto-inserted na underscore ang cursor).
        prInput.addEventListener('keydown', function(e) {
            const isBackspace = e.key === 'Backspace';
            const isDelete = e.key === 'Delete';
            if (!isBackspace && !isDelete) return;

            const value = e.target.value;
            const start = e.target.selectionStart;
            const end = e.target.selectionEnd;
            const numbersOnly = value.substring(5).replace(/\D/g, '');

            // If the user has a range selected, let the normal 'input' handler
            // reformat after the browser performs the deletion — just guard the prefix.
            if (start !== end) {
                if (start < 5) e.preventDefault();
                return;
            }

            e.preventDefault();

            if (isBackspace) {
                if (start <= 5) return; // nothing before the prefix to delete
                const digitCount = digitsBeforePosition(value, start);
                if (digitCount === 0) return;
                const newDigits = numbersOnly.substring(0, digitCount - 1) + numbersOnly.substring(digitCount);
                applyFormattedValue(newDigits, digitCount - 1);
            } else { // Delete (forward)
                if (start < 5) {
                    // Deleting from within/at the edge of the prefix — remove the first digit instead.
                    const newDigits = numbersOnly.substring(1);
                    applyFormattedValue(newDigits, 0);
                    return;
                }
                const digitCount = digitsBeforePosition(value, start);
                if (digitCount >= numbersOnly.length) return; // nothing after cursor to delete
                const newDigits = numbersOnly.substring(0, digitCount) + numbersOnly.substring(digitCount + 1);
                applyFormattedValue(newDigits, digitCount);
            }
        });

        prInput.addEventListener('input', function(e) {
            let value = e.target.value;

            // 1. Siguraduhin na hindi mabubura ang 'SO_A_' sa simula
            if (!value.startsWith('SO_A_')) {
                value = 'SO_A_' + value.replace(/^SO_A_*/, '');
            }

            // Kuhanin lang ang mga numero pagkatapos ng 'SO_A_'
            let numbersOnly = value.substring(5).replace(/\D/g, '');

            applyFormattedValue(numbersOnly, numbersOnly.length);
        });
    });
</script>

@endsection