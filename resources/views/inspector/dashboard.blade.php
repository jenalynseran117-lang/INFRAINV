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
@endsection