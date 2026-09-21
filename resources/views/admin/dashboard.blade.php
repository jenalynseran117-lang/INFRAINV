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
@endsection