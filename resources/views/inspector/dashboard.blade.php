@extends('layouts.Inspector.app')

@section('content')

<style>
    @keyframes glass-drift {

        0%,
        100% {
            transform: translate(0, 0) scale(1);
        }

        50% {
            transform: translate(20px, -14px) scale(1.05);
        }
    }

    .glass-blob {
        animation: glass-drift 12s ease-in-out infinite;
    }

    @media (prefers-reduced-motion: reduce) {
        .glass-blob {
            animation: none;
        }
    }
</style>

<div class="w-full max-w-[1400px] mx-auto p-4 md:p-8 antialiased">

    {{-- Top Right Profile Bar --}}
    <div class="h-11 mb-4 -mt-4 md:-mt-6 flex items-center justify-end gap-3" x-data="{ profileOpen: false }">

        {{-- Profile + Dropdown --}}
        <div class="relative">
            <button
                @click="profileOpen = !profileOpen"
                @click.outside="profileOpen = false"
                class="flex items-center gap-2 pl-1.5 pr-2.5 h-11 bg-white border border-slate-100 rounded-full shadow-sm hover:shadow-md hover:border-orange-200 transition-all">

                <div class="w-8 h-8 rounded-full bg-[var(--brand)] text-white flex items-center justify-center font-bold text-sm flex-shrink-0 overflow-hidden ring-2 ring-orange-100">
                    @if(auth()->user() && auth()->user()->profile_photo_path)
                    <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}" alt="Profile photo" class="w-full h-full object-cover">
                    @else
                    {{ strtoupper(substr(auth()->user()->name ?? 'I', 0, 1)) }}
                    @endif
                </div>

                <div class="hidden sm:block text-left leading-tight">
                    <p class="text-sm font-bold text-[var(--ink)]">{{ auth()->user()->name ?? 'Inspector' }}</p>
                </div>

                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180': profileOpen }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            {{-- Dropdown Menu --}}
            <div
                x-show="profileOpen"
                x-transition
                x-cloak
                class="absolute right-0 mt-2 w-52 bg-white border border-slate-100 rounded-2xl shadow-xl py-2 z-50">

                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-[var(--brand)] no-underline transition-colors">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
                    </svg>
                    Profile Settings
                </a>

                <div class="my-1 border-t border-slate-100"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-slate-600 hover:bg-red-50 hover:text-red-500 transition-colors">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"></path>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Header (Glassmorphism) --}}
    <div class="relative mb-8">

        {{-- Blurred color blobs sitting behind the glass card, now gently drifting --}}
        <div class="glass-blob absolute -top-10 -left-10 w-64 h-64 bg-orange-300/40 rounded-full blur-3xl"></div>
        <div class="glass-blob absolute -bottom-10 right-10 w-64 h-64 bg-amber-400/30 rounded-full blur-3xl" style="animation-delay:-4s"></div>
        <div class="glass-blob absolute top-10 right-1/3 w-48 h-48 bg-orange-200/30 rounded-full blur-3xl" style="animation-delay:-8s"></div>

        <div class="relative rounded-[2rem] p-10 overflow-hidden bg-orange-100/40 backdrop-blur-2xl border border-orange-200/60 shadow-xl shadow-orange-900/10">
            <p class="text-orange-700 font-semibold uppercase tracking-widest text-xs mb-1">INFRA-INV System</p>
            <h1 class="text-4xl font-bold tracking-tight text-slate-800">Inspector</h1>
            <p class="text-slate-600 mt-1 font-normal">Quality Assurance &amp; Verification</p>
        </div>
    </div>

    {{--
        Stat cards — restyled after the "Fusion" dashboard reference: solid
        vibrant gradient tiles with white text instead of white-card +
        colored-border, for higher contrast and a punchier look at a glance.
    --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">

        {{-- FOR INSPECTION (PENDING) --}}
        <a href="{{ route('inspector.inspect', ['status' => 'for_inspection']) }}"
            class="relative overflow-hidden rounded-3xl p-8 flex flex-col items-center justify-center text-center bg-gradient-to-br from-orange-400 via-orange-500 to-amber-500 shadow-lg shadow-orange-300/50 hover:shadow-xl hover:shadow-orange-300/60 hover:scale-[1.02] hover:-translate-y-0.5 transition-all duration-300 no-underline">

            {{-- soft decorative circle, Fusion-card style --}}
            <div class="absolute -top-8 -right-8 h-32 w-32 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-10 -left-6 h-28 w-28 rounded-full bg-white/10"></div>

            <div class="relative mb-3 h-12 w-12 rounded-2xl bg-white/20 text-white flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </div>

            <h2 id="pendingCount" class="relative text-6xl font-extrabold text-white tracking-tighter drop-shadow-sm">
                {{ $stats['pending'] ?? 0 }}
            </h2>

            <p class="relative text-sm font-bold text-white/90 mt-2 uppercase tracking-wide leading-tight">
                For <br> Inspection
            </p>
        </a>

        {{-- APPROVED --}}
        <a href="{{ route('inspector.inspect', ['status' => 'approved']) }}"
            class="relative overflow-hidden rounded-3xl p-8 flex flex-col items-center justify-center text-center bg-gradient-to-br from-fuchsia-500 via-purple-500 to-indigo-500 shadow-lg shadow-purple-300/50 hover:shadow-xl hover:shadow-purple-300/60 hover:scale-[1.02] hover:-translate-y-0.5 transition-all duration-300 no-underline">

            <div class="absolute -top-8 -right-8 h-32 w-32 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-10 -left-6 h-28 w-28 rounded-full bg-white/10"></div>

            <div class="relative mb-3 h-12 w-12 rounded-2xl bg-white/20 text-white flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <h2 id="approvedCount" class="relative text-6xl font-extrabold text-white tracking-tighter drop-shadow-sm">
                {{ $stats['approved'] ?? 0 }}
            </h2>

            <p class="relative text-sm font-bold text-white/90 mt-2 uppercase tracking-wide">
                Approved
            </p>
        </a>

    </div>

    {{-- Main Navigation Rows --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        {{-- Link 1 - Inspection Portal --}}
        <a href="{{ route('inspector.inspect', ['status' => 'for_inspection']) }}" class="flex items-center justify-between p-7 bg-white border border-slate-100 rounded-3xl shadow-sm hover:shadow-lg hover:border-orange-200 hover:-translate-y-0.5 transition-all duration-300 no-underline group">
            <div class="flex items-center gap-5">
                <div class="bg-orange-50 p-4 rounded-2xl text-[#E44D01] group-hover:bg-orange-100 group-hover:scale-105 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-slate-800">Inspection Portal</h3>
                    <p class="text-slate-400 font-medium">Pending Review</p>
                </div>
            </div>
            <span class="bg-orange-50 text-[#E44D01] px-5 py-1.5 rounded-full text-xs font-bold border border-orange-100 group-hover:bg-orange-100 transition-colors">
                {{ $stats['pending'] ?? 0 }} For Inspection
            </span>
        </a>

        {{-- Link 2 - Stock Activation --}}
        <a href="{{ route('inspector.inspect') }}" class="flex items-center justify-between p-7 bg-white border border-slate-100 rounded-3xl shadow-sm hover:shadow-lg hover:border-purple-200 hover:-translate-y-0.5 transition-all duration-300 no-underline group">
            <div class="flex items-center gap-5">
                <div class="bg-purple-50 p-4 rounded-2xl text-purple-600 group-hover:bg-purple-100 group-hover:scale-105 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-slate-800">Stock Activation</h3>
                    <p class="text-slate-400 font-medium">Approve/Reject Items</p>
                </div>
            </div>
            <div class="text-slate-300 group-hover:text-purple-500 group-hover:translate-x-1 transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </a>
    </div>

</div>


{{-- ===================================================================
     Floating Help Bubble (Messenger-style, draggable) — Inspector guide
     Tap = open/close the guide. Drag = move it anywhere; it snaps to the
     nearest left/right edge and remembers where you left it.
     =================================================================== --}}
@php
$helpItems = [
[
'title' => 'What is on my dashboard?',
'intro' => 'Your dashboard has two tiles: <strong>For Inspection</strong> shows how many items are waiting for your review, and <strong>Approved</strong> shows how many you have already cleared. Below them are two shortcuts: <strong>Inspection Portal</strong> for pending reviews, and <strong>Stock Activation</strong> for approving or rejecting items.',
'steps' => [],
],
[
'title' => 'How do I inspect a delivery?',
'intro' => '',
'steps' => [
'Click <strong>Inspection Portal</strong>. You will see three tabs, <strong>All</strong>, <strong>For Inspection</strong>, and <strong>Approved</strong>, each with its own counter, plus PO cards showing the supplier, date, item count, and grand total.',
'Click <strong>Inspect Details</strong> on the PO you want to check. A window opens with the uploaded delivery document. Click <strong>View / Zoom Document</strong> to look at it closely.',
'Review each line item: description, quantity pending approval, unit cost, and subtotal. Compare them with the delivery photo that was uploaded.',
'Click the green <strong>Approve</strong> button next to each item, one by one.',
'Click <strong>Close</strong> when you are done. The For Inspection count goes down and the Approved count goes up.',
],
],
[
'title' => 'What happens after I approve an item?',
'intro' => 'Approved items automatically move to the Warehouse queue for storage. Once every item on a PO is approved, the whole PO’s status changes to “Approved” on its own. Supply Office then places the items into storage.',
'steps' => [],
],
[
'title' => 'How do I update my profile or log out?',
'intro' => '',
'steps' => [
'Click your name at the top right, then choose <strong>Profile Settings</strong>.',
'Use <strong>Personal Information</strong> to update your details, or <strong>Password Manager</strong> to change your password.',
'Choose <strong>Logout</strong> from the same menu, or from the bottom of the sidebar, when you are done.',
],
],
];
@endphp

<div x-data="{
        items: @js($helpItems),
        helpOpen: false,
        open: 0,
        side: 'right',
        x: 0, y: 0, w: 1024, h: 768, size: 56, minX: 8,
        dragging: false, moved: false,
        offX: 0, offY: 0, sx: 0, sy: 0,

        init() {
            this.helpOpen = false;
            this.$nextTick(() => document.body.appendChild(this.$el));
            this.w = window.innerWidth;
            this.h = window.innerHeight;
            this.refreshBounds();
            this.side = 'right';
            this.y = this.clampY(this.h - this.size - 24);
        },
        onResize() {
            this.w = window.innerWidth;
            this.h = window.innerHeight;
            this.refreshBounds();
            this.y = this.clampY(this.y);
        },
        refreshBounds() {
            let right = 0;
            document.querySelectorAll('aside, nav, [class*=sidebar]').forEach(el => {
                const r = el.getBoundingClientRect();
                if (r.width > 0 && r.left < 60 && r.width < 420 && r.height > this.h * 0.5 && r.right > right) right = r.right;
            });
            this.minX = Math.min(right > 0 ? right + 12 : 8, Math.max(8, this.w * 0.35));
        },
        clampY(v) { return Math.min(Math.max(v, 8), this.h - this.size - 8); },
        posX() { return (this.dragging && this.moved) ? this.x : (this.side === 'left' ? this.minX : this.w - this.size - 24); },

        startDrag(e) {
            this.refreshBounds();
            this.dragging = true;
            this.moved = false;
            this.sx = e.clientX;
            this.sy = e.clientY;
            this.x = this.posX();
            this.offX = e.clientX - this.x;
            this.offY = e.clientY - this.y;
        },
        onMove(e) {
            if (!this.dragging) return;
            if (!this.moved && Math.abs(e.clientX - this.sx) < 5 && Math.abs(e.clientY - this.sy) < 5) return;
            this.moved = true;
            this.x = Math.min(Math.max(e.clientX - this.offX, this.minX), this.w - this.size - 8);
            this.y = this.clampY(e.clientY - this.offY);
        },
        endDrag() {
            if (!this.dragging) return;
            const wasMoved = this.moved;
            this.dragging = false;
            if (wasMoved) {
                this.side = (this.x + this.size / 2) < (this.minX + this.w) / 2 ? 'left' : 'right';
            } else {
                this.helpOpen = !this.helpOpen;
            }
            this.moved = false;
        },
        cancelDrag() { this.dragging = false; this.moved = false; },

        btnStyle() {
            return {
                left: this.posX() + 'px', top: this.y + 'px', right: 'auto', bottom: 'auto',
                width: this.size + 'px', height: this.size + 'px', touchAction: 'none',
                transition: this.dragging ? 'none' : 'left .25s ease, top .25s ease'
            };
        },
        panelStyle() {
            const pw = Math.min(400, this.w - this.minX - 12);
            const bx = this.posX();
            let left = (bx + this.size / 2 < (this.minX + this.w) / 2) ? bx : bx + this.size - pw;
            left = Math.min(Math.max(left, this.minX), this.w - pw - 12);
            const above = (this.y + this.size / 2) > this.h / 2;
            const avail = above ? this.y - 20 : this.h - this.y - this.size - 20;
            const mh = Math.max(220, Math.min(560, avail));
            return {
                left: left + 'px', width: pw + 'px', maxHeight: mh + 'px',
                top: above ? 'auto' : (this.y + this.size + 10) + 'px',
                bottom: above ? (this.h - this.y + 10) + 'px' : 'auto'
            };
        }
     }"
    @resize.window="onResize()"
    @pageshow.window="if ($event.persisted) helpOpen = false"
    @transitionend.window="refreshBounds()"
    @click.window="$nextTick(() => refreshBounds())"
    @pointermove.window="onMove($event)"
    @pointerup.window="endDrag()"
    @pointercancel.window="cancelDrag()"
    @keydown.escape.window="helpOpen = false"
    x-cloak>

    {{-- Guide panel --}}
    <div x-show="helpOpen"
        x-transition.opacity
        x-cloak
        style="display:none"
        :style="panelStyle()"
        role="dialog"
        aria-label="Inspector guide"
        class="fixed z-50 flex flex-col bg-white rounded-3xl shadow-2xl shadow-purple-900/25 border border-slate-100 overflow-hidden">

        <div class="flex items-start justify-between gap-3 px-5 py-4 bg-gradient-to-br from-orange-500 to-purple-600 text-white">
            <div>
                <h2 class="text-base font-extrabold leading-tight">Inspector Guide</h2>
                <p class="text-xs text-orange-100 mt-0.5">How to use your portal. Drag the bubble to move it.</p>
            </div>
            <button @click="helpOpen = false" aria-label="Close guide"
                class="w-8 h-8 -mr-1 flex items-center justify-center rounded-full text-white/80 hover:text-white hover:bg-white/15 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="overflow-y-auto p-4 space-y-3">
            <template x-for="(item, i) in items" :key="i">
                <div class="border border-slate-100 rounded-2xl overflow-hidden">
                    <button @click="open = open === i ? null : i"
                        class="w-full flex items-center justify-between gap-3 px-4 py-3.5 text-left hover:bg-slate-50 transition-colors">
                        <span class="font-bold text-sm text-[var(--ink)]" x-text="item.title"></span>
                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0 transition-transform duration-200" :class="{ 'rotate-180': open === i }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === i" x-transition x-cloak class="px-4 pb-4 text-sm text-slate-600 leading-relaxed">
                        <p x-show="item.intro" x-html="item.intro" class="mb-2"></p>
                        <ol x-show="item.steps.length" class="list-decimal ml-5 space-y-1.5">
                            <template x-for="(step, n) in item.steps" :key="n">
                                <li x-html="step"></li>
                            </template>
                        </ol>
                    </div>
                </div>
            </template>
        </div>

        <p class="px-5 py-3 border-t border-slate-100 text-xs text-slate-400">
            Still stuck? Contact your system administrator.
        </p>
    </div>

    {{-- Floating bubble --}}
    <button type="button"
        @pointerdown.prevent="startDrag($event)"
        @click="if ($event.detail === 0) helpOpen = !helpOpen"
        style="right:24px;bottom:24px;width:56px;height:56px"
        :style="btnStyle()"
        :aria-expanded="helpOpen.toString()"
        :aria-label="helpOpen ? 'Close help guide' : 'Open help guide'"
        class="fixed z-50 rounded-full bg-gradient-to-br from-orange-500 to-purple-600 text-white flex items-center justify-center shadow-xl shadow-orange-500/40 hover:shadow-2xl hover:shadow-orange-500/50 focus-visible:outline focus-visible:outline-4 focus-visible:outline-amber-400 select-none cursor-grab active:cursor-grabbing">
        <svg x-show="!helpOpen" class="w-7 h-7 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
        </svg>
        <svg x-show="helpOpen" x-cloak class="w-6 h-6 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

@endsection