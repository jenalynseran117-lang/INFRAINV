@extends('layouts.Users.welcomeLayout')

@vite(['resources/css/app.css', 'resources/js/app.js'])

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800;900&family=Inter:wght@400;500;600&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">

{{-- Scripts: Alpine.js (para sa scroll effects) + Typed.js (para sa typewriter) --}}
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.14.1/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>

{{-- Standard reCAPTCHA v2 Script --}}
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

@section('content')

<style>
    :root {
        /* --- PREMIUM COLOR SYSTEM --- */
        --ink: #0B1229;
        /* near-black navy, replaces #283E70 for headings */
        --navy: #16233F;
        /* deep navy for buttons / primary surfaces */
        --navy-light: #283E70;
        /* original navy, kept for secondary accents */
        --gold: #C9A24B;
        /* premium accent — use sparingly */
        --gold-light: #E8D5A3;
        --paper: #F7F7F5;
        /* warm off-white instead of stark white */
    }

    /* ============================================= */
    /* ============  ENTRANCE PRELOADER  ============ */
    /* ============================================= */
    html.is-loading {
        overflow: hidden;
    }

    #preloader {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #FFFFFF;
        transition: opacity 0.7s ease, visibility 0.7s ease;
    }

    #preloader.fade-out {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    .preloader-loader {
        position: relative;
        width: 150px;
        height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .preloader-ring {
        position: absolute;
        inset: 0;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        border: 2px solid transparent;
        border-top-color: var(--gold);
        border-right-color: var(--gold-light);
        opacity: 0;
        animation: preloaderRingIn 0.6s ease forwards, preloaderSpin 2.4s linear infinite 0.6s;
    }

    .preloader-logo {
        position: relative;
        width: 100px;
        height: 100px;
        object-fit: contain;
        opacity: 0;
        transform: scale(0.5);
        filter: drop-shadow(0 0 18px rgba(201, 162, 75, 0.35));
        animation: preloaderPop 0.8s cubic-bezier(.34, 1.56, .64, 1) forwards 0.3s;
    }

    @keyframes preloaderRingIn {
        to {
            opacity: 1;
        }
    }

    @keyframes preloaderSpin {
        to {
            transform: rotate(360deg);
        }
    }

    @keyframes preloaderPop {
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .preloader-brand {
        margin-top: 28px;
        font-family: 'Manrope', sans-serif;
        font-weight: 800;
        font-size: 1.1rem;
        letter-spacing: 0.15em;
        color: var(--ink);
        opacity: 0;
        animation: preloaderFadeUp 0.6s ease forwards 0.9s;
    }

    .preloader-text {
        margin-top: 14px;
        min-height: 22px;
        font-size: 1.15rem;
        line-height: 1.8;
        word-spacing: 0.15em;
        color: var(--navy);
        text-align: center;
        padding: 0 24px;
        opacity: 0;
    }

    .preloader-text.show {
        animation: preloaderFadeUp 0.7s ease forwards;
    }

    .preloader-text .wavy-letter {
        display: inline-block;
        font-family: 'Dancing Script', cursive;
        font-weight: 700;
        font-size: 1.35em;
        letter-spacing: 0.03em;
        margin-right: 0.03em;
        animation: preloaderLetterWave 1.4s ease-in-out infinite;
    }

    .preloader-text .brand-word {
        display: inline-block;
        font-family: 'Manrope', sans-serif;
        font-weight: 800;
        letter-spacing: 0.02em;
        color: var(--ink);
    }

    @keyframes preloaderLetterWave {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-6px);
        }
    }

    @keyframes preloaderFadeUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .preloader-progress {
        margin-top: 24px;
        width: 180px;
        height: 3px;
        background: rgba(11, 18, 41, 0.1);
        border-radius: 999px;
        overflow: hidden;
    }

    .preloader-progress-bar {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, var(--gold), var(--gold-light));
        border-radius: 999px;
        transition: width 0.2s linear;
    }

    .preloader-star-loader {
        --s: 28px;
        margin-top: 20px;
        height: calc(var(--s)*0.9);
        width: calc(var(--s)*5);
        --v1: transparent, #000 0.5deg 108deg, #0000 109deg;
        --v2: transparent, #000 0.5deg 36deg, #0000 37deg;
        -webkit-mask:
            conic-gradient(from 54deg at calc(var(--s)*0.68) calc(var(--s)*0.57), var(--v1)),
            conic-gradient(from 90deg at calc(var(--s)*0.02) calc(var(--s)*0.35), var(--v2)),
            conic-gradient(from 126deg at calc(var(--s)*0.5) calc(var(--s)*0.7), var(--v1)),
            conic-gradient(from 162deg at calc(var(--s)*0.5) 0, var(--v2));
        -webkit-mask-size: var(--s) var(--s);
        -webkit-mask-composite: xor, destination-over;
        mask-composite: exclude, add;
        -webkit-mask-repeat: repeat-x;
        mask-repeat: repeat-x;
        background: linear-gradient(var(--gold) 0 0) left / 0% 100% rgba(11, 18, 41, 0.12) no-repeat;
        animation: preloaderStarFill 2s infinite linear;
    }

    @keyframes preloaderStarFill {

        90%,
        100% {
            background-size: 100% 100%;
        }
    }

    /* --- DYNAMIC BACKGROUND GRADIENT ANIMATION --- */
    .animated-bg {
        background-image:
            linear-gradient(180deg, rgba(247, 247, 245, 0.15) 0%, rgba(247, 247, 245, 0.35) 55%, rgba(247, 247, 245, 0.6) 100%),
            url('/PICTURE/morning-urban-landscape.jpg');
        background-size: cover;
        background-position: center top;
        background-attachment: fixed;
        background-repeat: no-repeat;
        background-color: var(--paper);
    }

    /* `background-attachment: fixed` ay kilalang cause ng scroll/keyboard
           jank sa mobile Chrome/Safari. I-disable sa mobile widths. */
    @media (max-width: 1023px) {
        .animated-bg {
            background-attachment: scroll;
        }
    }

    /* `background-attachment: fixed` ay kilalang cause ng scroll/keyboard
           jank sa mobile Chrome/Safari (nire-recalculate ng browser tuwing
           mag-sscroll o lalabas ang on-screen keyboard). I-disable na lang
           sa mobile widths kung saan mas madalas mangyari ito. */
    @media (max-width: 1023px) {
        .animated-bg {
            background-attachment: scroll;
        }
    }

    .animated-bg .login-card,
    .animated-bg .icon-badge {
        background: rgba(255, 255, 255, 0.85);
    }

    body {
        font-family: 'Inter', sans-serif;
        scroll-behavior: smooth;
        color: var(--ink);
    }

    h1,
    h2,
    h3,
    h4,
    .font-display {
        font-family: 'Manrope', sans-serif;
        letter-spacing: -0.02em;
    }

    /* --- ICON ANIMATIONS CONFIGURATION --- */
    .animate-float {
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    .animate-wiggle {
        animation: wiggle 2.5s ease-in-out infinite;
        display: inline-block;
    }

    @keyframes wiggle {

        0%,
        100% {
            transform: rotate(0deg);
        }

        25% {
            transform: rotate(-8deg);
        }

        75% {
            transform: rotate(8deg);
        }
    }

    .animate-spin-slow {
        animation: spinSlow 8s linear infinite;
        display: inline-block;
    }

    @keyframes spinSlow {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .animate-pulse-slow {
        animation: pulseSlow 2s ease-in-out infinite;
        display: inline-block;
    }

    @keyframes pulseSlow {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.15);
        }
    }

    .animate-bounce-slow {
        animation: bounceSlow 2s ease-in-out infinite;
        display: inline-block;
    }

    @keyframes bounceSlow {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-8px);
        }
    }

    .delay-1 {
        animation-delay: 0.5s;
    }

    .delay-2 {
        animation-delay: 1s;
    }

    .typed-cursor {
        color: var(--gold);
        /* Dating fixed 3rem lang -- mas malaki ito kaysa text sa mobile,
               kaya lumalaki ang line-box at umaapaw sa container. Ipinaayon
               na ito sa laki ng heading sa bawat breakpoint. */
        font-size: 1em;
    }

    /* --- HERO TITLE / TYPEWRITER: TUNAY na fixed height (hindi min-height),
           kaya kahit mag-wrap sa 2 linya ang mahabang string, hindi na
           kailanman tataas ang box -- zero reflow/shift. --- */
    .hero-title-wrap {
        height: 100px;
        overflow: hidden;
    }

    @media (min-width: 640px) {
        .hero-title-wrap {
            height: 140px;
        }
    }

    @media (min-width: 1024px) {
        .hero-title-wrap {
            height: 170px;
        }
    }

    .text-login {
        color: #ffffff;
    }

    /* --- GOLD UNDERLINE ACCENT --- */
    .accent-underline {
        position: relative;
        display: inline-block;
    }

    .accent-underline::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: -6px;
        width: 48px;
        height: 3px;
        background: linear-gradient(90deg, var(--gold), var(--gold-light));
        border-radius: 2px;
    }

    /* --- PREMIUM BUTTON --- */
    .btn-primary {
        background: var(--navy);
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-primary:hover {
        background: var(--ink);
        box-shadow: 0 8px 24px -6px rgba(11, 18, 41, 0.45);
        transform: translateY(-1px);
    }

    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(201, 162, 75, 0.25), transparent);
        transition: left 0.6s ease;
    }

    .btn-primary:hover::before {
        left: 100%;
    }

    /* --- LOGIN CARD --- */
    .login-card {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(14px);
        border: 1px solid rgba(201, 162, 75, 0.18);
        box-shadow:
            0 20px 60px -15px rgba(11, 18, 41, 0.18),
            0 2px 8px -2px rgba(11, 18, 41, 0.08);
    }

    .input-premium {
        background: #FBFBFA;
        border: 1px solid #E5E3DD;
        transition: all 0.2s ease;
    }

    .input-premium:focus {
        background: #ffffff;
        border-color: var(--gold);
        box-shadow: 0 0 0 3px rgba(201, 162, 75, 0.15);
    }

    /* --- HERO PORTRAIT --- */
    .duotone-frame {
        position: relative;
    }

    .duotone-frame::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(160deg, rgba(11, 18, 41, 0.35) 0%, rgba(11, 18, 41, 0.05) 55%, rgba(201, 162, 75, 0.12) 100%);
        mix-blend-mode: multiply;
        pointer-events: none;
    }

    /* --- FLOATING ICON BADGES --- */
    .icon-badge {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(201, 162, 75, 0.2);
        box-shadow: 0 12px 30px -8px rgba(11, 18, 41, 0.25);
    }

    /* --- BUBBLE EFFECT --- */
    .bubble-area {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
        pointer-events: none;
        overflow: hidden;
    }

    .bubbles {
        position: absolute;
        bottom: -150px;
        background: radial-gradient(circle at 30% 30%, rgba(201, 162, 75, 0.10), rgba(22, 35, 63, 0.08));
        border-radius: 50%;
        animation: rise infinite linear;
    }

    @keyframes rise {
        0% {
            bottom: -150px;
            transform: translateX(0) scale(0.5);
            opacity: 0;
        }

        10% {
            opacity: 0.8;
        }

        90% {
            opacity: 0.8;
        }

        100% {
            bottom: 110%;
            transform: translateX(100px) scale(1.2);
            opacity: 0;
        }
    }

    .bubbles:nth-child(1) {
        left: 5%;
        width: 80px;
        height: 80px;
        animation-duration: 12s;
        animation-delay: 0s;
    }

    .bubbles:nth-child(2) {
        left: 15%;
        width: 120px;
        height: 120px;
        animation-duration: 18s;
        animation-delay: 3s;
    }

    .bubbles:nth-child(3) {
        left: 25%;
        width: 60px;
        height: 60px;
        animation-duration: 14s;
        animation-delay: 1s;
    }

    .bubbles:nth-child(4) {
        left: 35%;
        width: 90px;
        height: 90px;
        animation-duration: 16s;
        animation-delay: 5s;
    }

    .bubbles:nth-child(5) {
        left: 45%;
        width: 150px;
        height: 150px;
        animation-duration: 22s;
        animation-delay: 2s;
    }

    .bubbles:nth-child(6) {
        left: 55%;
        width: 70px;
        height: 70px;
        animation-duration: 13s;
        animation-delay: 4s;
    }

    .bubbles:nth-child(7) {
        left: 65%;
        width: 100px;
        height: 100px;
        animation-duration: 17s;
        animation-delay: 7s;
    }

    .bubbles:nth-child(8) {
        left: 75%;
        width: 50px;
        height: 50px;
        animation-duration: 11s;
        animation-delay: 0.5s;
    }

    .bubbles:nth-child(9) {
        left: 85%;
        width: 110px;
        height: 110px;
        animation-duration: 19s;
        animation-delay: 3.5s;
    }

    .bubbles:nth-child(10) {
        left: 95%;
        width: 65px;
        height: 65px;
        animation-duration: 15s;
        animation-delay: 1.5s;
    }

    .bubbles:nth-child(11) {
        left: 10%;
        width: 45px;
        height: 45px;
        animation-duration: 10s;
        animation-delay: 6s;
    }

    .bubbles:nth-child(12) {
        left: 20%;
        width: 130px;
        height: 130px;
        animation-duration: 20s;
        animation-delay: 8s;
    }

    .bubbles:nth-child(13) {
        left: 30%;
        width: 55px;
        height: 55px;
        animation-duration: 12s;
        animation-delay: 2.5s;
    }

    .bubbles:nth-child(14) {
        left: 40%;
        width: 95px;
        height: 95px;
        animation-duration: 18s;
        animation-delay: 5.5s;
    }

    .bubbles:nth-child(15) {
        left: 50%;
        width: 140px;
        height: 140px;
        animation-duration: 24s;
        animation-delay: 9s;
    }

    .bubbles:nth-child(16) {
        left: 60%;
        width: 75px;
        height: 75px;
        animation-duration: 15s;
        animation-delay: 4.5s;
    }

    .bubbles:nth-child(17) {
        left: 70%;
        width: 105px;
        height: 105px;
        animation-duration: 19s;
        animation-delay: 10s;
    }

    .bubbles:nth-child(18) {
        left: 80%;
        width: 52px;
        height: 52px;
        animation-duration: 11s;
        animation-delay: 1.5s;
    }

    .bubbles:nth-child(19) {
        left: 90%;
        width: 115px;
        height: 115px;
        animation-duration: 21s;
        animation-delay: 12s;
    }

    .bubbles:nth-child(20) {
        left: 3%;
        width: 60px;
        height: 60px;
        animation-duration: 13s;
        animation-delay: 0s;
    }

    .bubbles:nth-child(21) {
        left: 97%;
        width: 100px;
        height: 100px;
        animation-duration: 17s;
        animation-delay: 14s;
    }

    /* --- SECTION DIVIDER --- */
    .section-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(201, 162, 75, 0.4), transparent);
        max-width: 200px;
        margin: 0 auto;
    }

    /* --- NUMBERED CARD LABEL --- */
    .card-index {
        font-family: 'Manrope', sans-serif;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        color: var(--gold);
        opacity: 0.6;
    }

    /* --- PREMIUM HEADER --- */
    header {
        /* Palaging transparent bago mag-scroll (o kapag nakabalik sa
               itaas) -- ino-override kahit anong bg class ang galing sa
               layout file (hal. bg-white). Sa .is-scrolled pa lang lalabas
               ang solid/frosted background. */
        background-color: transparent !important;
        transition: background-color 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        border-bottom: 1px solid transparent;
    }

    header.is-scrolled {
        /* Palaging transparent -- inalis na ang solid/frosted white
               background dati (rgba paper color). Manatiling see-through
               kahit naka-scroll na, subtle shadow/border na lang ang
               indicator na naka-scroll ka. */
        background-color: transparent !important;
        box-shadow: 0 4px 24px -8px rgba(11, 18, 41, 0.12);
        border-bottom-color: rgba(201, 162, 75, 0.15);
    }

    header a {
        font-size: 0.95rem;
        font-weight: 500;
        text-transform: none;
        letter-spacing: normal;
        color: var(--ink) !important;
        position: relative;
        padding-bottom: 4px;
    }

    header a::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 0;
        height: 2px;
        background: var(--gold);
        transition: width 0.25s ease;
    }

    header a:hover::after {
        width: 100%;
    }

    header a:hover {
        color: var(--navy) !important;
    }

    /* --- FLOWBLOX-STYLE CTA PILL --- */
    .btn-cta-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        background: var(--ink);
        color: #fff !important;
        font-size: 0.9rem;
        font-weight: 600;
        padding: 0.5rem 0.5rem 0.5rem 1.35rem;
        border-radius: 999px;
        text-transform: none;
        letter-spacing: normal;
        transition: all 0.25s ease;
    }

    .btn-cta-pill::after {
        display: none;
    }

    .btn-cta-pill:hover {
        background: var(--gold);
        color: var(--ink) !important;
        transform: translateY(-1px);
    }

    .btn-cta-arrow {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.8rem;
        height: 1.8rem;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.18);
        font-size: 1rem;
        transition: background 0.25s ease;
    }

    .btn-cta-pill:hover .btn-cta-arrow {
        background: rgba(11, 18, 41, 0.12);
    }

    /* Para hindi matabunan ng fixed header ang anchor targets */
    #about,
    #services,
    #login {
        scroll-margin-top: 6.5rem;
    }

    /* --- PREMIUM FOOTER --- */
    .footer-premium {
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.4), rgba(247, 247, 245, 0.9));
    }

    .footer-brand {
        font-family: 'Manrope', sans-serif;
        font-weight: 900;
        letter-spacing: -0.02em;
        color: var(--ink);
    }

    .footer-heading {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        color: var(--gold);
        margin-bottom: 1rem;
    }

    .footer-link {
        display: block;
        color: #6B7280;
        font-size: 0.9rem;
        margin-bottom: 0.6rem;
        transition: color 0.2s ease, padding-left 0.2s ease;
    }

    .footer-link:hover {
        color: var(--navy);
        padding-left: 4px;
    }
</style>

<script>
    window.addEventListener('scroll', function() {
        var header = document.querySelector('header');
        if (!header) return;
        if (window.scrollY > 40) {
            header.classList.add('is-scrolled');
        } else {
            header.classList.remove('is-scrolled');
        }
    });
</script>

<script>
    if (!sessionStorage.getItem('infrainv_preloader_shown')) {
        document.documentElement.classList.add('is-loading');
    }
</script>

{{-- ============================================= --}}
{{-- ==========  ENTRANCE PRELOADER  ============== --}}
{{-- ============================================= --}}
<div id="preloader">
    <div class="preloader-loader">
        <div class="preloader-ring"></div>
        <img class="preloader-logo" src="{{ asset('PICTURE/LOGOS.png') }}" alt="INFRA-INV">
    </div>
    <div class="preloader-brand"></div>
    <div class="preloader-text" id="preloaderText"></div>
    <div class="preloader-progress">
        <div class="preloader-progress-bar" id="preloaderProgressBar"></div>
    </div>
    <div class="preloader-star-loader"></div>
</div>

<script>
    (function() {
        const messages = [
            "Streamlining infrastructure inventory...",
            "Real-time tracking. Total control.",
            "Welcome to INFRA-INV."
        ];

        document.addEventListener('DOMContentLoaded', function() {
            const preloader = document.getElementById('preloader');

            // Isang beses lang mag-a-animate ang preloader kada tab session.
            // Sa susunod na pag-navigate/reload sa loob ng session, agad itong itatago.
            if (sessionStorage.getItem('infrainv_preloader_shown')) {
                if (preloader) preloader.style.display = 'none';
                document.documentElement.classList.remove('is-loading');
                return;
            }
            sessionStorage.setItem('infrainv_preloader_shown', '1');

            const textEl = document.getElementById('preloaderText');
            const progressBar = document.getElementById('preloaderProgressBar');

            // Ginagawang wavy letter-by-letter (Dancing Script) ang bawat message,
            // maliban sa salitang "INFRA-INV" na nananatili sa orihinal na font.
            function buildWavyMessage(text) {
                return text.split(/(INFRA-INV)/g).map(function(part) {
                    if (part === 'INFRA-INV') {
                        return '<span class="brand-word">INFRA-INV</span>';
                    }
                    return part.split('').map(function(ch, idx) {
                        if (ch === ' ') return ' ';
                        var delay = (idx % 14) * 0.06;
                        return '<span class="wavy-letter" style="animation-delay:' + delay + 's">' + ch + '</span>';
                    }).join('');
                }).join('');
            }

            let i = 0;

            function showNextMessage() {
                if (i >= messages.length || !textEl) return;
                textEl.classList.remove('show');
                void textEl.offsetWidth; // restart animation
                textEl.innerHTML = buildWavyMessage(messages[i]);
                textEl.classList.add('show');
                i++;
                if (i < messages.length) setTimeout(showNextMessage, 900);
            }
            setTimeout(showNextMessage, 1200);

            let progress = 0;
            const progressInterval = setInterval(function() {
                progress += Math.random() * 12;
                if (progress >= 100) {
                    progress = 100;
                    clearInterval(progressInterval);
                }
                if (progressBar) progressBar.style.width = progress + '%';
            }, 200);

            window.addEventListener('load', function() {
                setTimeout(function() {
                    if (preloader) preloader.classList.add('fade-out');
                    document.documentElement.classList.remove('is-loading');
                }, 3400);
            });
        });
    })();
</script>

<div class="animated-bg overflow-x-hidden relative">

    {{-- BUBBLE BACKGROUND CONTAINER --}}
    <div class="bubble-area">
        @for ($i = 0; $i < 21; $i++)
            <div class="bubbles">
    </div>
    @endfor
</div>

{{-- HERO SECTION --}}
<section class="pt-16 pb-20 px-6 lg:pt-20 lg:pb-32 bg-transparent relative z-10"
    x-data="{ heroShown: false }" x-init="setTimeout(() => heroShown = true, 150)">
    <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">

        {{-- Left Side: Content & Login --}}
        <div class="order-2 lg:order-1 flex flex-col justify-start">

            <span x-show="heroShown"
                x-transition:enter="transition ease-[cubic-bezier(0.4,0,0.2,1)] duration-1000 delay-[100ms]"
                x-transition:enter-start="opacity-0 -translate-y-10 blur-md"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="text-xs uppercase tracking-[0.25em] font-bold mb-4" style="color: var(--gold);">
                Building Repair and Infrastructure Office &amp; Supply Office
            </span>

            <div x-show="heroShown"
                x-transition:enter="transition ease-[cubic-bezier(0.4,0,0.2,1)] duration-1100 delay-[250ms]"
                x-transition:enter-start="opacity-0 translate-y-16 blur-xl"
                x-transition:enter-end="opacity-100 translate-y-0 blur-none"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="hero-title-wrap mb-6 flex flex-col justify-end">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-[1.15] m-0 p-0" style="color: var(--ink);">
                    <span id="typed"></span>
                </h1>
            </div>

            <p x-show="heroShown"
                x-transition:enter="transition ease-[cubic-bezier(0.4,0,0.2,1)] duration-1000 delay-[400ms]"
                x-transition:enter-start="opacity-0 translate-y-12 blur-md"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="text-lg sm:text-xl text-gray-600 mb-6 leading-relaxed max-w-lg">
                Simple, powerful, and connected inventory management for projects around the campus.
            </p>

            {{-- Trust Indicators --}}
            <div x-show="heroShown"
                x-transition:enter="transition ease-[cubic-bezier(0.4,0,0.2,1)] duration-1000 delay-[550ms]"
                x-transition:enter-start="opacity-0 translate-y-10"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="flex flex-wrap items-center gap-x-6 gap-y-2 mb-8">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full" style="background: var(--gold);"></span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Real-time Tracking</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full" style="background: var(--gold);"></span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Role-based Access</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full" style="background: var(--gold);"></span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Full Audit Trail</span>
                </div>
            </div>

            {{-- Login Form Card --}}
            <div id="login" x-show="heroShown"
                x-transition:enter="transition ease-[cubic-bezier(0.4,0,0.2,1)] duration-1100 delay-[700ms]"
                x-transition:enter-start="opacity-0 translate-y-20 scale-90 blur-md"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="login-card p-8 rounded-3xl max-w-md relative z-20">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg" style="background: rgba(201,162,75,0.12); color: var(--gold);">
                        🔐
                    </div>
                    <h2 class="text-xs uppercase tracking-widest font-bold" style="color: var(--navy);">
                        Sign in to your account
                    </h2>
                </div>
                <form id="loginForm" method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Status message (e.g. after password reset) --}}
                    @if (session('status'))
                    <p style="font-size:0.875rem;font-weight:500;color:#067647;background:#ecfdf3;border:1px solid #abefc6;padding:12px;border-radius:8px;">
                        {{ session('status') }}
                    </p>
                    @endif

                    {{-- Role --}}
                    <div>
                        <label class="block text-xs uppercase tracking-widest font-bold text-gray-500 mb-2 ml-1">
                            Select Role
                        </label>
                        <select name="role" required
                            class="input-premium w-full px-5 py-4 rounded-xl outline-none">
                            <option value="" disabled selected>Choose your role</option>
                            <option value="Admin Aide">Admin Aide</option>
                            <option value="Supply Office">Supply Office</option>
                            <option value="Inspector">Inspector</option>
                        </select>
                    </div>

                    {{-- Errors --}}
                    @if ($errors->any())
                    <p class="text-red-600 text-sm font-medium bg-red-50 border border-red-100 p-3 rounded-lg">
                        {{ $errors->first() }}
                    </p>
                    @endif

                    {{-- Email --}}
                    <div>
                        <label class="block text-xs uppercase tracking-widest font-bold text-gray-500 mb-2 ml-1">
                            Email Address
                        </label>
                        <input type="email" name="email" placeholder="you@example.com" value="{{ old('email') }}" required
                            class="input-premium w-full px-5 py-4 rounded-xl outline-none">
                    </div>

                    {{-- Password --}}
                    <div>
                        <label class="block text-xs uppercase tracking-widest font-bold text-gray-500 mb-2 ml-1">
                            Password
                        </label>
                        <input type="password" name="password" placeholder="••••••••" required
                            class="input-premium w-full px-5 py-4 rounded-xl outline-none">

                        {{-- Forgot password link --}}
                        <div style="text-align:right;margin-top:10px;">
                            <a href="{{ route('password.request') }}"
                                style="font-size:0.85rem;font-weight:600;color:var(--navy);text-decoration:underline;text-underline-offset:3px;">
                                Forgot password?
                            </a>
                        </div>
                    </div>

                    {{-- Standard reCAPTCHA v2 Checkbox ("I'm not a robot") --}}
                    <div class="my-4 flex justify-center">
                        <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                    </div>

                    {{-- Login Button --}}
                    <button type="submit" id="submitBtn"
                        class="btn-primary w-full py-4 rounded-xl text-login font-semibold">
                        Log in
                    </button>
                </form>
            </div>
        </div>

        {{-- Right Side: Illustration --}}
        <div x-show="heroShown"
            x-transition:enter="transition ease-[cubic-bezier(0.4,0,0.2,1)] duration-1200 delay-[300ms]"
            x-transition:enter-start="opacity-0 scale-75 blur-xl"
            x-transition:enter-end="opacity-100 scale-100 blur-none"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-end="opacity-0"
            class="order-1 lg:order-2 relative flex justify-center">
            <div class="duotone-frame w-80 h-80 lg:w-[450px] lg:h-[450px] rounded-full flex items-center justify-center overflow-hidden shadow-2xl" style="box-shadow: 0 30px 80px -20px rgba(11,18,41,0.35);">
                <img src="{{ asset('PICTURE/photo_2026-01-26_08-45-20.jpg') }}" class="w-full h-full object-cover" alt="Hero Illustration">
            </div>

            <div class="absolute inset-0 -z-10 flex items-center justify-center">
                <div class="w-72 h-72 lg:w-[420px] lg:h-[420px] rounded-full border border-dashed opacity-30" style="border-color: var(--gold);"></div>
            </div>

            {{-- Floating Elements --}}
            <div class="icon-badge absolute top-10 left-10 animate-float p-4 rounded-2xl text-4xl">
                <span class="animate-wiggle">📦</span>
            </div>
            <div class="icon-badge absolute bottom-20 left-0 animate-float delay-1 p-4 rounded-2xl text-4xl">
                <span class="animate-pulse-slow">💳</span>
            </div>
            <div class="icon-badge absolute top-20 right-0 animate-float delay-2 p-4 rounded-2xl text-4xl">
                <span class="animate-bounce-slow">🚚</span>
            </div>
            <div class="icon-badge absolute bottom-5 right-10 animate-float delay-1 p-4 rounded-2xl text-4xl">
                <span class="animate-wiggle delay-1">🏢</span>
            </div>
        </div>
    </div>
</section>

{{-- ABOUT US SECTION --}}
<section id="about" x-data="{ shown: false }" x-intersect.margin.-10%="shown = true" x-intersect:leave.margin.-10%="shown = false"
    class="py-24 px-6 bg-transparent overflow-hidden relative z-10">
    <div class="max-w-7xl mx-auto grid md:grid-cols-2 gap-16 items-center">
        <div x-show="shown"
            x-transition:enter="transition ease-[cubic-bezier(0.4,0,0.2,1)] duration-1000 delay-[200ms]"
            x-transition:enter-start="opacity-0 -translate-x-20 blur-xl"
            x-transition:enter-end="opacity-100 translate-x-0 blur-none">
            <h2 x-show="shown"
                x-transition:enter="transition ease-out duration-900 delay-[250ms]"
                x-transition:enter-start="opacity-0 translate-y-10 blur-md"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="text-xs uppercase tracking-[0.2em] font-bold mb-4" style="color: var(--gold);">Our Story</h2>
            <h3 x-show="shown"
                x-transition:enter="transition ease-out duration-1000 delay-[400ms]"
                x-transition:enter-start="opacity-0 translate-y-14 blur-lg"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="text-4xl font-black mb-6 leading-tight accent-underline" style="color: var(--ink);">
                Streamlining infrastructure inventory since 2026.
            </h3>
            <p x-show="shown"
                x-transition:enter="transition ease-out duration-1000 delay-[550ms]"
                x-transition:enter-start="opacity-0 translate-y-10 blur-md"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="text-lg text-gray-600 leading-relaxed mb-6 mt-6">
                Designed for precision and speed, <strong style="color: var(--navy);">INFRA-INV</strong> optimizes inventory management. We empower every level of our team — from Building Repair and Infrastructure Office to Supply Office — with the tools to maintain total control and real-time tracking of every item.
            </p>
        </div>
        <div x-show="shown"
            x-transition:enter="transition ease-[cubic-bezier(0.4,0,0.2,1)] duration-1000 delay-[500ms]"
            x-transition:enter-start="opacity-0 translate-x-20 blur-xl"
            x-transition:enter-end="opacity-100 translate-x-0 blur-none"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-end="opacity-0"
            class="p-10 rounded-3xl border shadow-inner"
            style="background: linear-gradient(145deg, rgba(22,35,63,0.05), rgba(201,162,75,0.06)); border-color: rgba(201,162,75,0.2);">
            <div class="space-y-4">
                <div class="flex items-center space-x-4 bg-white/85 p-4 rounded-2xl shadow-sm hover:translate-x-2 transition-transform cursor-default border border-white/60">
                    <span class="text-2xl animate-spin-slow">🎯</span>
                    <span class="font-bold text-gray-700">Accurate Real-time Tracking</span>
                </div>
                <div class="flex items-center space-x-4 bg-white/85 p-4 rounded-2xl shadow-sm hover:translate-x-2 transition-transform cursor-default border border-white/60">
                    <span class="text-2xl animate-pulse-slow">🛡️</span>
                    <span class="font-bold text-gray-700">Secure Role-based Access</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- SERVICES SECTION --}}
<section id="services" x-data="{ shown: false }" x-intersect.margin.-10%="shown = true" x-intersect:leave.margin.-10%="shown = false"
    class="py-24 px-6 bg-white/40 backdrop-blur-sm relative z-10 border-t border-b" style="border-color: rgba(201,162,75,0.15);">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
            <span x-show="shown"
                x-transition:enter="transition ease-out duration-900"
                x-transition:enter-start="opacity-0 translate-y-10 blur-md"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="text-xs uppercase tracking-[0.25em] font-bold" style="color: var(--gold);">Services</span>
            <h2 x-show="shown"
                x-transition:enter="transition ease-out duration-1000 delay-[150ms]"
                x-transition:enter-start="opacity-0 translate-y-14 blur-lg"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="text-3xl font-black mt-3" style="color: var(--ink);">What We Offer</h2>
            <p x-show="shown"
                x-transition:enter="transition ease-out duration-1000 delay-[300ms]"
                x-transition:enter-start="opacity-0 translate-y-10 blur-md"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="text-gray-500 mt-4">The all-in-one solution for your inventory management needs.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div x-show="shown"
                x-transition:enter="transition ease-[cubic-bezier(0.4,0,0.2,1)] duration-1000 delay-[100ms]"
                x-transition:enter-start="opacity-0 translate-y-16 scale-90 blur-xl"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100 blur-none"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="bg-white/90 backdrop-blur-sm p-8 rounded-2xl shadow-sm transition-all border border-gray-100 group hover:shadow-2xl">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl transition-colors" style="background: rgba(22,35,63,0.06); color: var(--navy);">
                        <span class="animate-wiggle">🏢</span>
                    </div>
                    <span class="card-index">01</span>
                </div>
                <h4 class="text-xl font-bold mb-3" style="color: var(--ink);">Warehouse Control</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Efficiently organizing all equipment across entire facilities.</p>
            </div>

            <div x-show="shown"
                x-transition:enter="transition ease-[cubic-bezier(0.4,0,0.2,1)] duration-1000 delay-[300ms]"
                x-transition:enter-start="opacity-0 translate-y-16 scale-90 blur-xl"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100 blur-none"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="bg-white/90 backdrop-blur-sm p-8 rounded-2xl shadow-sm transition-all border border-gray-100 group hover:shadow-2xl">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl transition-colors" style="background: rgba(22,35,63,0.06); color: var(--navy);">
                        <span class="animate-pulse-slow">📈</span>
                    </div>
                    <span class="card-index">02</span>
                </div>
                <h4 class="text-xl font-bold mb-3" style="color: var(--ink);">Audit Logs</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Maintaining a clear and detailed history of all transactions and activities.</p>
            </div>

            <div x-show="shown"
                x-transition:enter="transition ease-[cubic-bezier(0.4,0,0.2,1)] duration-1000 delay-[500ms]"
                x-transition:enter-start="opacity-0 translate-y-16 scale-90 blur-xl"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100 blur-none"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="bg-white/90 backdrop-blur-sm p-8 rounded-2xl shadow-sm transition-all border border-gray-100 group hover:shadow-2xl">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl transition-colors" style="background: rgba(22,35,63,0.06); color: var(--navy);">
                        <span class="animate-bounce-slow">🚚</span>
                    </div>
                    <span class="card-index">03</span>
                </div>
                <h4 class="text-xl font-bold mb-3" style="color: var(--ink);">Fulfillment</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Fast and efficient processing of orders from request to delivery.</p>
            </div>
        </div>
    </div>
</section>

{{-- FEATURES SECTION --}}
<section class="py-24 px-6 bg-transparent relative z-10" x-data="{ shown: false }" x-intersect.margin.-10%="shown = true" x-intersect:leave.margin.-10%="shown = false">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
            <span x-show="shown"
                x-transition:enter="transition ease-out duration-900"
                x-transition:enter-start="opacity-0 translate-y-10 blur-md"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="text-xs uppercase tracking-[0.25em] font-bold" style="color: var(--gold);">Capabilities</span>
            <h2 x-show="shown"
                x-transition:enter="transition ease-out duration-1000 delay-[150ms]"
                x-transition:enter-start="opacity-0 translate-y-14 blur-lg"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="text-3xl font-black mt-3" style="color: var(--ink);">Powerful Features</h2>
        </div>
        <div class="grid md:grid-cols-4 gap-8 text-center">
            <div x-show="shown"
                x-transition:enter="transition ease-[cubic-bezier(0.4,0,0.2,1)] duration-800 delay-[100ms]"
                x-transition:enter-start="opacity-0 translate-y-16 scale-75 blur-md"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100 blur-none"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="p-8 rounded-3xl bg-white/60 hover:bg-white/90 backdrop-blur-sm transition-all border border-white/40 group">
                <div class="text-4xl mb-4"><span class="animate-wiggle">📦</span></div>
                <h3 class="text-lg font-bold mb-2" style="color: var(--ink);">Inventory</h3>
            </div>
            <div x-show="shown"
                x-transition:enter="transition ease-[cubic-bezier(0.4,0,0.2,1)] duration-800 delay-[250ms]"
                x-transition:enter-start="opacity-0 translate-y-16 scale-75 blur-md"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100 blur-none"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="p-8 rounded-3xl bg-white/60 hover:bg-white/90 backdrop-blur-sm transition-all border border-white/40 group">
                <div class="text-4xl mb-4"><span class="animate-pulse-slow">🏢</span></div>
                <h3 class="text-lg font-bold mb-2" style="color: var(--ink);">Warehousing</h3>
            </div>
            <div x-show="shown"
                x-transition:enter="transition ease-[cubic-bezier(0.4,0,0.2,1)] duration-800 delay-[400ms]"
                x-transition:enter-start="opacity-0 translate-y-16 scale-75 blur-md"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100 blur-none"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="p-8 rounded-3xl bg-white/60 hover:bg-white/90 backdrop-blur-sm transition-all border border-white/40 group">
                <div class="text-4xl mb-4"><span class="animate-bounce-slow">🛒</span></div>
                <h3 class="text-lg font-bold mb-2" style="color: var(--ink);">Fulfillment</h3>
            </div>
            <div x-show="shown"
                x-transition:enter="transition ease-[cubic-bezier(0.4,0,0.2,1)] duration-800 delay-[550ms]"
                x-transition:enter-start="opacity-0 translate-y-16 scale-75 blur-md"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100 blur-none"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                class="p-8 rounded-3xl bg-white/60 hover:bg-white/90 backdrop-blur-sm transition-all border border-white/40 group">
                <div class="text-4xl mb-4"><span class="animate-spin-slow">🤖</span></div>
                <h3 class="text-lg font-bold mb-2" style="color: var(--ink);">Automation</h3>
            </div>
        </div>
    </div>
</section>

{{-- FOOTER --}}
<footer class="footer-premium border-t py-16 px-6 relative z-10" style="border-color: rgba(201,162,75,0.15);"
    x-data="{ shown: false }" x-intersect.margin.-10%="shown = true" x-intersect:leave.margin.-10%="shown = false">
    <div x-show="shown"
        x-transition:enter="transition ease-out duration-1000"
        x-transition:enter-start="opacity-0 translate-y-16 blur-md"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-end="opacity-0"
        class="max-w-7xl mx-auto grid md:grid-cols-4 gap-12 mb-12">
        <div class="md:col-span-2">
            <div class="flex items-center gap-3 mb-4">
                <img src="{{ asset('PICTURE/LOGOS.png') }}" alt="Logo" class="w-8 h-8 object-contain">
                <span class="footer-brand text-xl">INFRA-INV</span>
            </div>
            <p class="text-gray-500 text-sm leading-relaxed max-w-sm">
                Simple, powerful, and connected inventory management for infrastructure and supply projects around the campus.
            </p>
        </div>

        <div>
            <h5 class="footer-heading">More</h5>
            <a href="#about" class="footer-link">About Us</a>
            <a href="#services" class="footer-link">Services</a>
        </div>

        
    </div>

    <div class="section-divider mb-8"></div>

    <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center text-gray-500 text-xs uppercase tracking-wider gap-4">
        <p>&copy; 2026 INFRA-INV. All rights reserved.</p>
        <p style="color: var(--gold);">Building Repair and Infrastructure Office &amp; Supply Office</p>
    </div>
</footer>

{{-- Script for Typewriter --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new Typed('#typed', {
            strings: ['Manage inventory and fulfill orders the right way', 'Clean distribution of items.'],
            typeSpeed: 40,
            backSpeed: 20,
            startDelay: 500,
            showCursor: true,
            cursorChar: '|',
            loop: true
        });
    });
</script>

</div>

@endsection