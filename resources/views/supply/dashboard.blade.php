@extends('layouts.Supply.app')

@section('content')

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

<div class="relative w-full min-h-screen rounded-3xl overflow-hidden bg-gradient-to-br from-[#f9f4f2] via-[#f6efec] to-[#f2e8e5] p-4 md:p-8 antialiased subpixel-antialiased font-body">

    {{-- Ambient glow blobs --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="glass-blob absolute -top-24 -left-16 h-80 w-80 rounded-full bg-red-300/25 blur-3xl"></div>
        <div class="glass-blob absolute top-1/4 -right-10 h-96 w-96 rounded-full bg-rose-300/20 blur-3xl" style="animation-delay:-5s"></div>
        <div class="glass-blob absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-amber-200/20 blur-3xl" style="animation-delay:-9s"></div>
    </div>

    <div class="relative z-10 max-w-[1400px] mx-auto">

        {{-- Top Right Profile Bar --}}
        <div class="h-11 mb-4 -mt-4 md:-mt-6 flex items-center justify-end gap-3" x-data="{ profileOpen: false }">

            {{-- Profile + Dropdown --}}
            <div class="relative">
                <button
                    @click="profileOpen = !profileOpen"
                    @click.outside="profileOpen = false"
                    class="flex items-center gap-2 pl-1.5 pr-2.5 h-11 bg-white/70 backdrop-blur-xl border border-white/70 rounded-full shadow-sm hover:shadow-md hover:border-red-200 transition-all duration-300">

                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[var(--brand)] to-red-700 text-white flex items-center justify-center font-bold text-sm flex-shrink-0 overflow-hidden ring-2 ring-red-100">
                        @if(auth()->user() && auth()->user()->profile_photo_path)
                        <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}" alt="Profile photo" class="w-full h-full object-cover">
                        @else
                        {{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 1)) }}
                        @endif
                    </div>

                    <div class="hidden sm:block text-left leading-tight">
                        <p class="text-sm font-bold text-[var(--ink)]">{{ auth()->user()->name ?? 'Supply Officer' }}</p>
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
                    class="absolute right-0 mt-2 w-52 bg-white/90 backdrop-blur-xl border border-white/70 rounded-2xl shadow-xl py-2 z-50">

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
        <div class="relative mb-8">
            <div class="relative rounded-[2rem] p-10 overflow-hidden bg-white/60 backdrop-blur-2xl border border-red-200/60 shadow-xl shadow-red-900/10">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <p class="text-red-600 font-bold uppercase tracking-widest text-xs">INFRA-INV System</p>
                        </div>
                        <h1 class="text-4xl md:text-5xl font-black tracking-tight bg-gradient-to-r from-slate-800 via-slate-700 to-red-800 bg-clip-text text-transparent font-display">Supply Officer</h1>
                        <p class="text-slate-600 mt-2 font-medium text-lg">Inventory & Procurement</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Stats Cards — Fusion-style vibrant gradient cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

            {{-- Pending PR Requests --}}
            <a href="{{ route('supply.precurement') }}" class="relative overflow-hidden rounded-3xl p-8 flex flex-col items-center justify-center text-center
                  bg-gradient-to-br from-red-500 via-red-600 to-rose-700
                  shadow-lg shadow-red-500/25 hover:shadow-xl hover:shadow-red-500/40
                  hover:-translate-y-1 transition-all duration-300 no-underline group">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-all duration-500"></div>
                <div class="relative mb-3 bg-white/15 backdrop-blur-md text-white p-3 rounded-2xl ring-1 ring-white/25 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h2 class="relative text-6xl font-black text-white tracking-tighter font-display">{{ $newPRCount ?? 0 }}</h2>
                <p class="relative text-sm font-bold text-red-100 mt-2 uppercase tracking-wide">Pending PR Requests</p>
            </a>

            {{-- Total Individual Items for Inspection --}}
            <a href="{{ route('supply.UploadActualItem') }}" class="relative overflow-hidden rounded-3xl p-8 flex flex-col items-center justify-center text-center
                  bg-gradient-to-br from-rose-500 via-pink-600 to-fuchsia-700
                  shadow-lg shadow-rose-500/25 hover:shadow-xl hover:shadow-rose-500/40
                  hover:-translate-y-1 transition-all duration-300 no-underline group">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-all duration-500"></div>
                <div class="relative mb-3 bg-white/15 backdrop-blur-md text-white p-3 rounded-2xl ring-1 ring-white/25 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h2 class="relative text-6xl font-black text-white tracking-tighter font-display">{{ $totalItemsForInspection ?? 0 }}</h2>
                <p class="relative text-sm font-bold text-rose-100 mt-2 uppercase tracking-wide">Total Items to Inspect</p>
            </a>

            {{-- Pending POs Awaiting Joint Inspection --}}
            <a href="{{ route('supply.UploadActualItem') }}" class="relative overflow-hidden rounded-3xl p-8 flex flex-col items-center justify-center text-center
                  bg-gradient-to-br from-amber-500 via-orange-500 to-red-600
                  shadow-lg shadow-orange-500/25 hover:shadow-xl hover:shadow-orange-500/40
                  hover:-translate-y-1 transition-all duration-300 no-underline group">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-all duration-500"></div>
                <div class="relative mb-3 bg-white/15 backdrop-blur-md text-white p-3 rounded-2xl ring-1 ring-white/25 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h2 class="relative text-6xl font-black text-white tracking-tighter font-display">{{ $pendingPOCount ?? 0 }}</h2>
                <p class="relative text-sm font-bold text-orange-100 mt-2 uppercase tracking-wide">Pending POs for Inspection</p>
            </a>
        </div>

        {{-- Main Action Rows --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">

            {{-- Procurement Queue --}}
            <a href="{{ route('supply.precurement') }}" class="lg:col-span-1 flex items-center justify-between p-6 bg-white/70 backdrop-blur-xl border border-white/70 rounded-2xl shadow-[0_8px_32px_rgba(31,41,55,0.08)] hover:shadow-xl hover:border-red-200 hover:-translate-y-1 transition-all duration-300 no-underline group">
                <div class="flex items-center gap-4">
                    <div class="bg-red-50 p-3 rounded-xl text-red-600 group-hover:bg-gradient-to-br group-hover:from-red-500 group-hover:to-red-700 group-hover:text-white group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="no-underline">
                        <h3 class="text-xl font-bold text-slate-800 whitespace-nowrap">Procurement Queue</h3>
                        <p class="text-xs text-slate-500 font-semibold">Create & Manage POs</p>
                    </div>
                </div>
                @if(isset($newPRCount) && $newPRCount > 0)
                <span class="bg-red-500 text-white px-3 py-1 rounded-full text-[10px] font-bold shadow-sm shrink-0">
                    {{ $newPRCount }} New
                </span>
                @endif
            </a>

            {{-- Warehouse Management --}}
            <a href="{{ route('supply.Warehouse') }}" class="lg:col-span-1 flex items-center justify-between p-6 bg-white/70 backdrop-blur-xl border border-white/70 rounded-2xl shadow-[0_8px_32px_rgba(31,41,55,0.08)] hover:shadow-xl hover:border-red-200 hover:-translate-y-1 transition-all duration-300 no-underline group">
                <div class="flex items-center gap-4">
                    <div class="bg-red-50 p-3 rounded-xl text-red-600 group-hover:bg-gradient-to-br group-hover:from-red-500 group-hover:to-red-700 group-hover:text-white group-hover:scale-110 group-hover:-rotate-3 transition-all duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div class="no-underline">
                        <h3 class="text-xl font-bold text-slate-800">Warehouse</h3>
                        <p class="text-xs text-slate-500 font-semibold">Storage & Inventory</p>
                    </div>
                </div>
                <div class="text-slate-300 group-hover:text-red-500 group-hover:translate-x-1 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            {{-- Joint Inspection --}}
            <a href="{{ route('supply.UploadActualItem') }}" class="lg:col-span-1 flex items-center justify-between p-6 bg-white/70 backdrop-blur-xl border border-white/70 rounded-2xl shadow-[0_8px_32px_rgba(31,41,55,0.08)] hover:shadow-xl hover:border-red-200 hover:-translate-y-1 transition-all duration-300 no-underline group">
                <div class="flex items-center gap-4">
                    <div class="bg-red-50 p-3 rounded-xl text-red-600 group-hover:bg-gradient-to-br group-hover:from-red-500 group-hover:to-red-700 group-hover:text-white group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div class="no-underline">
                        <h3 class="text-xl font-bold text-slate-800 whitespace-nowrap">Inspection</h3>
                        <p class="text-xs text-slate-500 font-semibold">Upload DR & Photos</p>
                    </div>
                </div>
                @if(isset($pendingPOCount) && $pendingPOCount > 0)
                <span class="bg-orange-500 text-white px-3 py-1 rounded-full text-[10px] font-bold shadow-sm shrink-0">
                    {{ $pendingPOCount }} Pending
                </span>
                @endif
            </a>
        </div>

        {{-- Pending PRs List --}}
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-800 font-display">Awaiting Purchase Orders</h2>
            @if(isset($pendingPRs) && count($pendingPRs) > 0)
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide bg-white/70 backdrop-blur-xl px-3 py-1 rounded-full border border-white/70">{{ count($pendingPRs) }} item(s)</span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            @forelse($pendingPRs as $pr)
            <a href="{{ route('supply.precurement') }}"
                class="flex items-center justify-between p-5 bg-white/70 backdrop-blur-xl border border-white/70 rounded-[1.5rem] shadow-[0_8px_32px_rgba(31,41,55,0.06)] hover:border-red-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 group cursor-pointer">

                <div class="flex items-center gap-4">
                    <div class="p-3 bg-gradient-to-br from-red-500 to-rose-600 text-white rounded-xl shadow-md shadow-red-500/25 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-red-700 font-mono">{{ $pr->pr_number }}</h3>
                        <p class="text-xs text-slate-500 font-medium">Requested: {{ $pr->created_at->diffForHumans() }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="px-3 py-1.5 bg-gradient-to-r from-amber-400 to-orange-400 text-white text-[10px] font-bold rounded-full uppercase tracking-wider shadow-sm">
                        Awaiting PO
                    </span>
                    <div class="text-slate-300 group-hover:text-red-500 group-hover:translate-x-1 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>

            </a>
            @empty
            <div class="col-span-full p-8 text-center bg-white/50 backdrop-blur-xl rounded-[1.5rem] border border-dashed border-red-200">
                <p class="text-sm text-slate-600 font-semibold">🎉 Great! No pending Purchase Requests in the queue.</p>
            </div>
            @endforelse

        </div>
    </div>
</div>


{{-- ===================================================================
     Floating Help Bubble (Messenger-style, draggable) — Supply Office guide
     Tap = open/close the guide. Drag = move it anywhere; it snaps to the
     nearest left/right edge and remembers where you left it.
     =================================================================== --}}
@php
$helpItems = [
[
'title' => 'What is on my dashboard?',
'intro' => 'Your sidebar has Dashboard, Procurement Queue, Warehouse Management, Distribution, Projects, Weekly Audit, Reports, and Work Notes. The three cards show pending PR requests, items still to inspect, and POs waiting on inspection. <strong>Awaiting Purchase Orders</strong> below them lists the requests that still need a PO.',
'steps' => [],
],
[
'title' => 'How do I turn a request into a PO?',
'intro' => '',
'steps' => [
'Click <strong>Procurement Queue</strong>. Under “Select a Purchase Request to begin”, click <strong>Open Request</strong> on the request you want.',
'Enter the <strong>P.O. Number</strong> and date. Future dates are not allowed, but you can backdate up to one month.',
'Add the <strong>Supplier</strong>, choose the <strong>Mode of Procurement</strong> (for example, Small Value), then fill in the <strong>Delivery Term</strong> and <strong>Payment Term</strong>.',
'Upload a signed copy of the PO to verify the number.',
'In Procurement Items, click <strong>+ Add Row</strong> for each item. Fill in the stock number, description, quantity, and unit cost. The Amount and Grand Total update automatically.',
'Click the green <strong>Complete Transaction</strong> button. You can use View PR, View PO, or Extract PO Items first to double-check.',
],
],
[
'title' => 'How do I log a delivery for inspection?',
'intro' => 'Do this before the Inspector can review anything.',
'steps' => [
'Open the <strong>Inspection Queue</strong>. This is where you upload the delivery photos and the Delivery Receipt (DR).',
'Search by PO number or scroll the list. You will see the supplier, creation date, item count, and status.',
'Open a PO, confirm the <strong>Inspected Quantity</strong> matches what actually arrived, then click <strong>Choose File</strong> to upload the delivery photo.',
'Click <strong>Submit for Units</strong>. The PO now waits in the Inspector’s queue.',
],
],
[
'title' => 'How do I store approved items?',
'intro' => '',
'steps' => [
'After the Inspector approves items, a card called “New Items Arrived from Inspector” shows what was approved and when.',
'Select the item, pick a storage location from the dropdown, and click <strong>Assign Selected Items</strong>.',
],
],
[
'title' => 'How do I check available stock?',
'intro' => '',
'steps' => [
'Click <strong>Warehouse Management</strong>, then <strong>Available Stock</strong>. Items are grouped by storage location and update live.',
'Use the search bar or the warehouse filter to find a specific item or location.',
],
],
[
'title' => 'How do I send stock to a project?',
'intro' => '',
'steps' => [
'Open <strong>Distribution &amp; Outflow</strong>. Available Warehouse Stock is on the left and Deployment Destination is on the right.',
'Select the item, choose a project from <strong>Select Target Project</strong> (or add one with the + button), and set the quantity with the stepper.',
'Review the Outflow Summary Preview, then click <strong>Confirm Outflow &amp; Deduct</strong>. The button stays grey until both a project and a quantity are chosen.',
'A printable receipt opens. Choose your printer and click <strong>Print</strong>, or <strong>Cancel</strong> if you only wanted to preview it.',
],
],
[
'title' => 'How do I manage projects?',
'intro' => '',
'steps' => [
'Click <strong>Projects</strong> to see every project with its status, items distributed, and total value.',
'Click <strong>View Materials List</strong> on a project to see what was sent, with per-unit and total costs. You can print the list too.',
'Click <strong>+ Add Project</strong>, type the project name, and confirm. It then appears as a destination in Distribution &amp; Outflow.',
],
],
[
'title' => 'How do I update my profile or log out?',
'intro' => 'Click your name at the top right, then choose <strong>Profile Settings</strong> to update <strong>Personal Information</strong> or change your password under <strong>Password Manager</strong>. Choose <strong>Logout</strong> from the same menu when you are done.',
'steps' => [],
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
        aria-label="Supply Officer guide"
        class="fixed z-50 flex flex-col bg-white rounded-3xl shadow-2xl shadow-red-900/25 border border-slate-100 overflow-hidden">

        <div class="flex items-start justify-between gap-3 px-5 py-4 bg-gradient-to-br from-red-600 to-rose-700 text-white">
            <div>
                <h2 class="text-base font-extrabold leading-tight font-display">Supply Officer Guide</h2>
                <p class="text-xs text-red-100 mt-0.5">How to use your portal. Drag the bubble to move it.</p>
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
        class="fixed z-50 rounded-full bg-gradient-to-br from-red-500 to-rose-700 text-white flex items-center justify-center shadow-xl shadow-red-500/40 hover:shadow-2xl hover:shadow-red-500/50 focus-visible:outline focus-visible:outline-4 focus-visible:outline-amber-400 select-none cursor-grab active:cursor-grabbing">
        <svg x-show="!helpOpen" class="w-7 h-7 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
        </svg>
        <svg x-show="helpOpen" x-cloak class="w-6 h-6 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

@endsection