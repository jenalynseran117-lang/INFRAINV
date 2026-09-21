<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Supply Office') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|space-grotesk:500,600,700|jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ asset('build/bootstrap/bootstrap.v5.3.2.min.css') }}">

    <style>
        [x-cloak] {
            display: none !important;
        }

        :root {
            --canvas-bg: #F3F4F7;
            --ink: #0F1A30;
            --ink-soft: #1B2A4A;
            --brand: #7F1D1D;
            --signal: #E07A1F;
        }

        body {
            background-color: var(--canvas-bg) !important;
            color: #1F2937;
        }

        h1,
        h2,
        h3,
        .font-display {
            font-family: 'Space Grotesk', 'Figtree', sans-serif;
            letter-spacing: -0.01em;
        }

        .blueprint-grid {
            background-image: radial-gradient(rgba(255, 255, 255, 0.18) 1px, transparent 1px);
            background-size: 15px 15px;
        }

        .supply-sidebar {
            background: linear-gradient(180deg, #F87171 0%, #B91C1C 50%, #7F1D1D 100%);
        }

        .sidebar-transition {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-transition {
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-link {
            position: relative;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .nav-link.is-active {
            background-color: var(--canvas-bg) !important;
            color: #0F1A30 !important;
            font-weight: 700;
            border-top-left-radius: 9999px;
            border-bottom-left-radius: 9999px;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            margin-right: -1px !important;
            width: calc(100% + 1px) !important;
            box-shadow: none !important;
        }

        .nav-link.is-active svg,
        .nav-link.is-active span {
            color: #0F1A30 !important;
        }

        .nav-link.is-active::before {
            content: '';
            position: absolute;
            top: -16px;
            right: 0;
            width: 16px;
            height: 16px;
            background: radial-gradient(circle at 0 0, transparent 16px, var(--canvas-bg) 16px);
            pointer-events: none;
            z-index: 10;
        }

        .nav-link.is-active::after {
            content: '';
            position: absolute;
            bottom: -16px;
            right: 0;
            width: 16px;
            height: 16px;
            background: radial-gradient(circle at 0 100%, transparent 16px, var(--canvas-bg) 16px);
            pointer-events: none;
            z-index: 10;
        }

        aside::-webkit-scrollbar {
            width: 4px;
        }

        aside::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }
    </style>
</head>

<body class="font-sans antialiased" x-data="{ 
    sidebarOpen: false, 
    collapsed: localStorage.getItem('supply_sidebar_collapsed') === 'true',
    hasNewMessage: {{ ($unreadWorkNotes ?? 0) > 0 ? 'true' : 'false' }},
    toggleCollapse() {
        this.collapsed = !this.collapsed;
        localStorage.setItem('supply_sidebar_collapsed', this.collapsed);
    }
}">

    <div class="min-h-screen relative flex" style="background-color: var(--canvas-bg);">

        {{-- Mobile Top Bar --}}
        <div class="lg:hidden fixed top-0 left-0 right-0 bg-[#F87171] border-b border-white/10 z-50 flex items-center justify-between px-4 py-3 text-white">
            <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-white/10 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path x-show="sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-red-300"></span>
                <span class="text-base font-bold font-display tracking-tight">Supply Office</span>
            </div>
            <div class="w-6"></div>
        </div>

        {{-- Mobile Backdrop --}}
        <div x-show="sidebarOpen" class="lg:hidden fixed inset-0 bg-black/60 z-[55]" @click="sidebarOpen = false" x-cloak></div>

        {{-- Collapsible Floating-Style Sidebar --}}
        <aside
            class="supply-sidebar sidebar-transition fixed top-0 bottom-0 left-0 z-[60] flex flex-col justify-between py-5 my-3 ml-3 rounded-l-3xl shadow-2xl text-white"
            :class="{
                'w-16 pl-0 pr-0': collapsed,
                'w-60 pl-3 pr-0': !collapsed,
                'translate-x-0': sidebarOpen,
                '-translate-x-full lg:translate-x-0': !sidebarOpen
            }"
            x-cloak>

            {{-- Collapse / Expand Arrow Button --}}
            <button
                @click="toggleCollapse()"
                class="hidden lg:flex absolute -right-3 top-7 w-7 h-7 bg-[#B91C1C] text-white border border-white/30 rounded-full items-center justify-center hover:bg-amber-500 transition-all duration-200 z-20 shadow-md"
                :title="collapsed ? 'Expand menu' : 'Collapse menu'">
                <svg class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': collapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            {{-- Top Content --}}
            <div class="space-y-6 w-full">

                {{-- Header Title --}}
                <div class="py-2.5 blueprint-grid rounded-2xl bg-white/10 border border-white/10 transition-all" :class="{ 'px-3 mr-3': !collapsed, 'mx-2 flex justify-center': collapsed }">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl text-[#B91C1C] flex items-center justify-center font-bold text-xl flex-shrink-0 shadow-sm">
                            <img src="{{ asset('PICTURE/csulogo.png') }}" alt="CSU Logo" class="w-full h-full object-contain">
                        </div>
                        <div x-show="!collapsed" class="whitespace-nowrap overflow-hidden">
                            <p class="text-[10px] font-bold text-red-200 uppercase tracking-[0.15em]">INFRA-INV System</p>
                            <h1 class="text-base font-bold font-display leading-tight">Supply Office</h1>
                        </div>
                    </div>
                </div>

                {{-- Navigation Links --}}
                <nav class="space-y-2 pt-2 w-full">

                    {{-- Dashboard --}}
                    <a href="{{ route('supply.dashboard') }}"
                        class="nav-link flex items-center gap-3 py-3 text-sm font-bold {{ request()->routeIs('supply.dashboard') ? 'is-active' : 'text-white/80 hover:bg-white/10 hover:text-white rounded-l-full' }}"
                        :class="{ 'px-4 mr-3': !collapsed, 'px-0 justify-center w-full': collapsed }"
                        style="text-decoration: none;">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke-width="2.5" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"></path>
                        </svg>
                        <span x-show="!collapsed" class="truncate">Dashboard</span>
                    </a>

                    {{-- Procurement Queue --}}
                    <a href="{{ route('supply.precurement') }}"
                        class="nav-link flex items-center gap-3 py-3 text-sm font-bold {{ request()->routeIs('supply.precurement') ? 'is-active' : 'text-white/80 hover:bg-white/10 hover:text-white rounded-l-full' }}"
                        :class="{ 'px-4 mr-3': !collapsed, 'px-0 justify-center w-full': collapsed }"
                        style="text-decoration: none;">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke-width="2.5" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"></path>
                        </svg>
                        <span x-show="!collapsed" class="truncate">Procurement Queue</span>
                    </a>

                    {{-- Warehouse Management --}}
                    <a href="{{ route('supply.Warehouse') }}"
                        class="nav-link flex items-center gap-3 py-3 text-sm font-bold {{ request()->routeIs('supply.Warehouse') ? 'is-active' : 'text-white/80 hover:bg-white/10 hover:text-white rounded-l-full' }}"
                        :class="{ 'px-4 mr-3': !collapsed, 'px-0 justify-center w-full': collapsed }"
                        style="text-decoration: none;">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke-width="2.5" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"></path>
                        </svg>
                        <span x-show="!collapsed" class="truncate">Warehouse Management</span>
                    </a>

                    {{-- Distribution --}}
                    <a href="{{ route('supply.Distribution') }}"
                        class="nav-link flex items-center gap-3 py-3 text-sm font-bold {{ request()->routeIs('supply.Distribution') ? 'is-active' : 'text-white/80 hover:bg-white/10 hover:text-white rounded-l-full' }}"
                        :class="{ 'px-4 mr-3': !collapsed, 'px-0 justify-center w-full': collapsed }"
                        style="text-decoration: none;">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke-width="2.5" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h11.25v8.25H3V7.5zM14.25 10.5h3l2.25 2.25v3h-5.25V10.5zM6.75 18a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zM18 18a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"></path>
                        </svg>
                        <span x-show="!collapsed" class="truncate">Distribution</span>
                    </a>

                    {{-- Projects --}}
                    <a href="{{ route('supply.Project') }}"
                        class="nav-link flex items-center gap-3 py-3 text-sm font-bold {{ request()->routeIs('supply.Project') ? 'is-active' : 'text-white/80 hover:bg-white/10 hover:text-white rounded-l-full' }}"
                        :class="{ 'px-4 mr-3': !collapsed, 'px-0 justify-center w-full': collapsed }"
                        style="text-decoration: none;">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke-width="2.5" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"></path>
                        </svg>
                        <span x-show="!collapsed" class="truncate">Projects</span>
                    </a>

                    {{-- Weekly Audit --}}
                    <a href="{{ route('supply.WeekAudit') }}"
                        class="nav-link flex items-center gap-3 py-3 text-sm font-bold {{ request()->routeIs('supply.WeekAudit') ? 'is-active' : 'text-white/80 hover:bg-white/10 hover:text-white rounded-l-full' }}"
                        :class="{ 'px-4 mr-3': !collapsed, 'px-0 justify-center w-full': collapsed }"
                        style="text-decoration: none;">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke-width="2.5" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878m0 0a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122"></path>
                        </svg>
                        <span x-show="!collapsed" class="truncate">Weekly Audit</span>
                    </a>

                    {{-- Reports --}}
                    <a href="{{ route('supply.Report') }}"
                        class="nav-link flex items-center gap-3 py-3 text-sm font-bold {{ request()->routeIs('supply.Report') ? 'is-active' : 'text-white/80 hover:bg-white/10 hover:text-white rounded-l-full' }}"
                        :class="{ 'px-4 mr-3': !collapsed, 'px-0 justify-center w-full': collapsed }"
                        style="text-decoration: none;">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke-width="2.5" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"></path>
                        </svg>
                        <span x-show="!collapsed" class="truncate">Reports</span>
                    </a>

                    {{-- Work Notes --}}
                    <a href="{{ route('supply.Messageworknotes') }}"
                        @click="hasNewMessage = false"
                        class="nav-link flex items-center justify-between py-3 text-sm font-bold {{ request()->routeIs('supply.Messageworknotes') ? 'is-active' : 'text-white/80 hover:bg-white/10 hover:text-white rounded-l-full' }}"
                        :class="{ 'px-4 mr-3': !collapsed, 'px-0 justify-center w-full': collapsed }"
                        style="text-decoration: none;">
                        <div class="flex items-center gap-3 min-w-0" :class="{ 'justify-center w-full': collapsed }">
                            <div class="relative">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"></path>
                                </svg>
                                <span x-show="hasNewMessage && collapsed" class="absolute -top-1 -right-1 flex h-2.5 w-2.5" x-cloak>
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                                </span>
                            </div>
                            <span x-show="!collapsed" class="truncate">Work Notes</span>
                        </div>
                        <span x-show="hasNewMessage && !collapsed" class="w-2.5 h-2.5 rounded-full bg-red-500 flex-shrink-0 mr-3" x-cloak></span>
                    </a>

                </nav>
            </div>

            {{-- Logout Section --}}
            <div class="pt-4 border-t border-white/10 w-full" :class="{ 'px-3': !collapsed, 'px-0': collapsed }">
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-3 py-3 rounded-xl text-sm font-medium text-white/80 hover:bg-red-500/20 hover:text-white transition"
                        :class="{ 'px-4': !collapsed, 'px-0 justify-center': collapsed }">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"></path>
                        </svg>
                        <span x-show="!collapsed" class="truncate">Logout</span>
                    </button>
                </form>
            </div>

        </aside>

        {{-- Main Content Area --}}
        <main
            class="main-transition flex-1 p-6 pt-20 lg:pt-8"
            style="background-color: var(--canvas-bg);"
            :class="{
                'lg:ml-20': collapsed,
                'lg:ml-64': !collapsed
            }">

            @yield('content')
        </main>

    </div>

</body>

</html>