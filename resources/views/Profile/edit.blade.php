@php
    $layout = match(auth()->user()->role ?? 'supply') {
        'admin' => 'layouts.Admin.app',
        'inspector' => 'layouts.Inspector.app',
        'supply' => 'layouts.Supply.app',
    };
    $isAdmin = (auth()->user()->role ?? '') === 'admin';
@endphp

@extends($layout)

@section('content')
<div class="w-full max-w-[1400px] mx-auto p-4 md:p-8 antialiased subpixel-antialiased" x-data="{ tab: 'personal' }">

    {{-- Header (Dynamic Glass Treatment based on Role) --}}
    <div class="relative mb-8">
        <div class="absolute -top-10 -left-10 w-64 h-64 {{ $isAdmin ? 'bg-blue-400/50' : 'bg-red-400/50' }} rounded-full blur-3xl"></div>
        <div class="absolute -bottom-10 right-10 w-64 h-64 {{ $isAdmin ? 'bg-sky-300/40' : 'bg-rose-300/40' }} rounded-full blur-3xl"></div>
        <div class="absolute top-10 right-1/3 w-48 h-48 {{ $isAdmin ? 'bg-blue-200/30' : 'bg-red-200/30' }} rounded-full blur-3xl"></div>

        <div class="relative rounded-[2rem] p-10 overflow-hidden {{ $isAdmin ? 'bg-blue-100/40 border-blue-200/60 shadow-blue-900/10' : 'bg-red-100/40 border-red-200/60 shadow-red-900/10' }} backdrop-blur-2xl border shadow-xl">
            <p class="{{ $isAdmin ? 'text-blue-600' : 'text-red-600' }} font-semibold uppercase tracking-widest text-xs mb-1">INFRA-INV System</p>
            <h1 class="text-4xl font-bold tracking-tight text-slate-800">Profile Settings</h1>
            <p class="text-slate-600 mt-1 font-normal">Manage your personal information and account security</p>
        </div>
    </div>

    @if (session('status') === 'profile-updated')
    <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold rounded-2xl px-5 py-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Profile updated successfully.
    </div>
    @endif

    @if (session('status') === 'password-updated')
    <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold rounded-2xl px-5 py-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Password updated successfully.
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-6 items-start">

        {{-- Tab Navigation --}}
        <div class="bg-white/60 backdrop-blur-xl {{ $isAdmin ? 'border-blue-100' : 'border-red-100' }} border rounded-[1.75rem] shadow-sm p-3 flex lg:flex-col gap-2 overflow-x-auto lg:overflow-visible">

            <button type="button" @click="tab = 'personal'"
                class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all whitespace-nowrap"
                :class="tab === 'personal' ? 'bg-[var(--brand)] text-white shadow-md' : 'text-slate-500 hover:bg-slate-50 hover:text-[var(--brand)]'">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
                </svg>
                Personal Information
            </button>

            <button type="button" @click="tab = 'security'"
                class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all whitespace-nowrap"
                :class="tab === 'security' ? 'bg-[var(--brand)] text-white shadow-md' : 'text-slate-500 hover:bg-slate-50 hover:text-[var(--brand)]'">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"></path>
                </svg>
                Password Manager
            </button>

            <div class="hidden lg:block my-1 border-t {{ $isAdmin ? 'border-blue-100' : 'border-red-100' }}"></div>

            <form method="POST" action="{{ route('logout') }}" class="lg:w-full">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-slate-500 hover:bg-red-50 hover:text-red-600 transition-all whitespace-nowrap">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"></path>
                    </svg>
                    Logout
                </button>
            </form>
        </div>

        {{-- Panels --}}
        <div>

            {{-- Personal Information Panel --}}
            <div x-show="tab === 'personal'" x-cloak
                class="bg-white/70 backdrop-blur-xl border {{ $isAdmin ? 'border-blue-100' : 'border-red-100' }} rounded-[1.75rem] shadow-sm p-8">

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-8">
                    @csrf
                    @method('PATCH')

                    {{-- Avatar --}}
                    <div class="flex items-center gap-5">
                        <div class="w-20 h-20 rounded-full bg-[var(--brand)] text-white flex items-center justify-center font-bold text-2xl overflow-hidden border-4 border-white shadow-md flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Profile photo</p>
                            <p class="text-xs text-slate-400">Photo uploads aren't available yet.</p>
                        </div>
                    </div>

                    {{-- Name + Role --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Name *</label>
                            <input id="name" name="name" type="text"
                                value="{{ old('name', auth()->user()->name ?? '') }}"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:ring-2 focus:ring-blue-300 outline-none transition-all">
                            @error('name')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Role</label>
                            <div class="w-full bg-slate-100 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-500 font-semibold capitalize">
                                {{ auth()->user()->role ?? 'User' }}
                            </div>
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Email *</label>
                        <input id="email" name="email" type="email"
                            value="{{ old('email', auth()->user()->email ?? '') }}"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:ring-2 focus:ring-blue-300 outline-none transition-all">
                        @error('email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit"
                            class="bg-[var(--brand)] hover:brightness-110 text-white text-sm font-bold px-8 py-3 rounded-full shadow-md transition-all active:scale-95">
                            Update Changes
                        </button>
                    </div>
                </form>
            </div>

            {{-- Password Manager Panel --}}
            <div x-show="tab === 'security'" x-cloak
                class="bg-white/70 backdrop-blur-xl border {{ $isAdmin ? 'border-blue-100' : 'border-red-100' }} rounded-[1.75rem] shadow-sm p-8">

                <h3 class="text-lg font-bold text-slate-800 mb-1">Change Password</h3>
                <p class="text-xs text-slate-400 mb-6">Use a strong password you're not using anywhere else.</p>

                <form method="POST" action="{{ route('password.update') }}" class="space-y-6 max-w-lg">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Current Password *</label>
                        <input id="current_password" name="current_password" type="password"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:ring-2 focus:ring-blue-300 outline-none transition-all">
                        @error('current_password', 'updatePassword')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">New Password *</label>
                        <input id="password" name="password" type="password"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:ring-2 focus:ring-blue-300 outline-none transition-all">
                        @error('password', 'updatePassword')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Confirm New Password *</label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:ring-2 focus:ring-blue-300 outline-none transition-all">
                        @error('password_confirmation', 'updatePassword')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit"
                            class="bg-[var(--brand)] hover:brightness-110 text-white text-sm font-bold px-8 py-3 rounded-full shadow-md transition-all active:scale-95">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection