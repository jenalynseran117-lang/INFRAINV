@extends('layouts.Admin.app')

@section('content')
<div class="w-full max-w-[1400px] mx-auto p-4 md:p-8 antialiased">

    {{-- Top Right Profile Bar --}}
    <div class="h-11 mb-4 -mt-4 md:-mt-6 flex items-center justify-end gap-3" x-data="{ profileOpen: false }">

        {{-- Profile + Dropdown --}}
        <div class="relative">
            <button
                @click="profileOpen = !profileOpen"
                @click.outside="profileOpen = false"
                class="flex items-center gap-2 pl-1.5 pr-2.5 h-11 bg-white border border-slate-100 rounded-full shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-300">

                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[var(--brand)] to-blue-700 text-white flex items-center justify-center font-bold text-sm flex-shrink-0 overflow-hidden ring-2 ring-blue-100">
                    @if(auth()->user() && auth()->user()->profile_photo_path)
                    <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}" alt="Profile photo" class="w-full h-full object-cover">
                    @else
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    @endif
                </div>

                <div class="hidden sm:block text-left leading-tight">
                    <p class="text-sm font-bold text-[var(--ink)]">{{ auth()->user()->name ?? 'Admin' }}</p>
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

    {{-- Header --}}
    <div class="relative mb-10">
        <div class="absolute -top-16 -left-16 w-72 h-72 bg-blue-500/40 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-16 right-10 w-72 h-72 bg-indigo-400/30 rounded-full blur-3xl"></div>

        <div class="relative rounded-[2rem] p-10 overflow-hidden bg-gradient-to-br from-blue-200/50 via-blue-100/40 to-indigo-200/40 backdrop-blur-2xl border border-blue-300/60 shadow-2xl shadow-blue-900/20">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <p class="text-blue-600 font-bold uppercase tracking-widest text-xs">INFRA-INV System</p>
            </div>
            <h1 class="text-4xl md:text-6xl font-black tracking-tight bg-gradient-to-r from-slate-800 via-slate-700 to-blue-800 bg-clip-text text-transparent">
                Admin Aide
            </h1>
            <p class="text-slate-600 mt-3 font-medium text-lg">Operations & Logistics Control</p>
        </div>
    </div>

    {{-- Main Action Cards - Fusion-style colorful gradient cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">

        {{-- Create New PR --}}
        <a href="{{ route('admin.PRManagement') }}"
            class="relative group no-underline rounded-3xl p-8 overflow-hidden
                  bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-700
                  shadow-xl shadow-blue-500/30
                  hover:shadow-2xl hover:shadow-blue-500/50 hover:-translate-y-1.5
                  transition-all duration-300">

            {{-- Decorative glow blobs --}}
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-all duration-500"></div>
            <div class="absolute -bottom-14 -left-10 w-40 h-40 bg-black/10 rounded-full blur-2xl"></div>

            <div class="relative flex items-start justify-between">
                <div class="bg-white/15 backdrop-blur-md p-4 rounded-2xl text-white ring-1 ring-white/25 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" />
                    </svg>
                </div>

                <div class="text-white/40 group-hover:text-white group-hover:translate-x-1.5 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>

            <div class="relative mt-8">
                <h3 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">Create New PR</h3>
                <p class="text-blue-100 font-medium mt-1.5">Upload PR and POW</p>
            </div>

            <div class="relative mt-6 inline-flex items-center gap-2 text-xs font-bold text-white/90 bg-white/10 backdrop-blur-sm px-3 py-1.5 rounded-full ring-1 ring-white/20">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                Ready to file
            </div>
        </a>

        {{-- Receiving Portal --}}
        <a href="{{ route('admin.Receiving') }}"
            class="relative group no-underline rounded-3xl p-8 overflow-hidden
                  bg-gradient-to-br from-emerald-500 via-teal-600 to-emerald-700
                  shadow-xl shadow-emerald-500/30
                  hover:shadow-2xl hover:shadow-emerald-500/50 hover:-translate-y-1.5
                  transition-all duration-300">

            {{-- Decorative glow blobs --}}
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-all duration-500"></div>
            <div class="absolute -bottom-14 -left-10 w-40 h-40 bg-black/10 rounded-full blur-2xl"></div>

            <div class="relative flex items-start justify-between">
                <div class="bg-white/15 backdrop-blur-md p-4 rounded-2xl text-white ring-1 ring-white/25 group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    </svg>
                </div>

                <div class="text-white/40 group-hover:text-white group-hover:translate-x-1.5 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>

            <div class="relative mt-8">
                <h3 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">Receiving Portal</h3>
                <p class="text-emerald-100 font-medium mt-1.5">View DR and Photos</p>
            </div>

            <div class="relative mt-6 inline-flex items-center gap-2 text-xs font-bold text-white/90 bg-white/10 backdrop-blur-sm px-3 py-1.5 rounded-full ring-1 ring-white/20">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-300 animate-pulse"></span>
                Awaiting deliveries
            </div>
        </a>

    </div>

</div>


{{-- ===================================================================
     Floating Help Bubble (Messenger-style, draggable) — Admin Aide guide
     Tap = open/close the guide. Drag = move it anywhere; it snaps to the
     nearest left/right edge and remembers where you left it.
     =================================================================== --}}
@php
$helpItems = [
[
'title' => 'What can I do on my dashboard?',
'intro' => 'Your sidebar has Dashboard, PR Management, Receiving Portal, Projects, Weekly Audit, Reports, and Work Notes. The two big shortcuts, <strong>Create New PR</strong> and <strong>Receiving Portal</strong>, are the tasks you will do most.',
'steps' => [],
],
[
'title' => 'How do I create a new PR?',
'intro' => '',
'steps' => [
'Click <strong>PR Management</strong> in the sidebar. The document viewer is on the left and <em>Enter PR Details</em> is on the right.',
'Click <strong>Click to Upload Purchase Request</strong> and choose a PDF, PNG, or JPG of the request.',
'Type the rest of the <strong>Purchase Request Number</strong>. The prefix is already filled in.',
'Wait for the green “File uploaded successfully!” message, then review your entry.',
'Click <strong>Final Submit</strong> to lock it in, or <strong>Cancel</strong> if something is wrong.',
],
],
[
'title' => 'How do I check incoming deliveries?',
'intro' => '',
'steps' => [
'Click <strong>Receiving Portal</strong>. The top counters show POs waiting to be received, items under inspection, and items already received.',
'Review the <strong>Un-Opened POs</strong> table: PO number, description, status, date, quantity, unit cost, and total cost. Use the search bar to find a specific PO.',
'Switch to <strong>Under Inspection</strong> to see what the Inspector is checking. If it is empty, you will see “No deliveries found.”',
],
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
        aria-label="Admin Aide guide"
        class="fixed z-50 flex flex-col bg-white rounded-3xl shadow-2xl shadow-blue-900/25 border border-slate-100 overflow-hidden">

        <div class="flex items-start justify-between gap-3 px-5 py-4 bg-gradient-to-br from-blue-600 to-indigo-700 text-white">
            <div>
                <h2 class="text-base font-extrabold leading-tight">Admin Aide Guide</h2>
                <p class="text-xs text-blue-100 mt-0.5">How to use your portal. Drag the bubble to move it.</p>
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
        class="fixed z-50 rounded-full bg-gradient-to-br from-blue-500 to-indigo-700 text-white flex items-center justify-center shadow-xl shadow-blue-500/40 hover:shadow-2xl hover:shadow-blue-500/50 focus-visible:outline focus-visible:outline-4 focus-visible:outline-amber-400 select-none cursor-grab active:cursor-grabbing">
        <svg x-show="!helpOpen" class="w-7 h-7 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
        </svg>
        <svg x-show="helpOpen" x-cloak class="w-6 h-6 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

@endsection