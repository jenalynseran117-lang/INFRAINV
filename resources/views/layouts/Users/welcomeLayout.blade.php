<body class="bg-white text-gray-900 overflow-x-hidden">

    <style>
        header {
            transition: transform 0.35s ease, background-color 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        }
        /* Nakatago ang header pag naka-scroll pababa */
        header.header-hidden {
            transform: translateY(-100%);
        }
    </style>

    {{-- HEADER --}}
    <header class="fixed top-0 left-0 w-full h-20 bg-transparent backdrop-blur-sm z-50">
        <div class="max-w-7xl mx-auto px-6 h-full flex items-center justify-between">

            <!-- LEFT: Logo -->
            <div class="flex items-center gap-3">
                <img src="{{ asset('PICTURE/LOGOS.png') }}" alt="Logo"
                    class="w-9 h-9 object-contain">
                <span class="text-2xl font-black text-[#283E70] tracking-tighter">
                    INFRA-INV<span class="text-blue-600"></span>
                </span>
            </div>

            <!-- RIGHT: Navigation -->
            <div class="flex-gap justify-between font-medium text-black space-x-2">

                <a href="#about" class="hover:text-blue-400 transition">About Us</a>
                <a href="#services" class="hover:text-blue-400 transition">Services</a>
            </div>
        </div>
    </header>

    {{-- PAGE CONTENT --}}
    <main class="pt-20">
        @yield('content')
    </main>

    {{-- Hide header pag scroll pababa, ipakita ulit pag scroll pataas --}}
    <script>
        (function () {
            var header = document.querySelector('header');
            if (!header) return;

            var lastScrollY = window.scrollY;
            var hideThreshold = 80; // huwag nang itago habang malapit pa sa taas

            window.addEventListener('scroll', function () {
                var currentScrollY = window.scrollY;

                if (currentScrollY > lastScrollY && currentScrollY > hideThreshold) {
                    // pababa yung scroll -> itago
                    header.classList.add('header-hidden');
                } else {
                    // pataas yung scroll o malapit pa sa taas -> ipakita
                    header.classList.remove('header-hidden');
                }

                lastScrollY = currentScrollY;
            }, { passive: true });
        })();
    </script>

</body>