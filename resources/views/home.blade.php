<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ICAS LMS | Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
    <script>
        (function () {
            try {
                const savedTheme = localStorage.getItem('icas-home-theme');
                let theme = savedTheme;

                if (theme !== 'dark' && theme !== 'light') {
                    theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }

                document.documentElement.setAttribute('data-theme', theme);
            } catch (error) {
                document.documentElement.setAttribute('data-theme', 'light');
            }

            try {
                if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    document.documentElement.classList.add('motion-init');
                }
            } catch (error) {
                document.documentElement.classList.add('motion-init');
            }
        })();
    </script>
    <style>
        :root {
            --brand-900: #0f4f2a;
            --brand-700: #1f7a40;
            --brand-500: #2fa15a;
            --leaf-100: #e9f8ee;
        }

        html {
            color-scheme: light;
        }

        html[data-theme="dark"] {
            color-scheme: dark;
            --brand-900: #d1fae5;
            --brand-700: #86efac;
            --brand-500: #4ade80;
            --leaf-100: #132033;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        body.route-exit {
            opacity: 0;
            transform: translate3d(0, -10px, 0) scale(0.995);
            filter: blur(2px);
            transition: opacity 0.24s ease, transform 0.24s ease, filter 0.24s ease;
            pointer-events: none;
        }

        .display-font {
            font-family: 'Sora', sans-serif;
        }

        .theme-button-primary,
        .theme-button-secondary {
            transition: background-color 0.25s ease, border-color 0.25s ease, color 0.25s ease, box-shadow 0.25s ease;
        }

        .reveal-on-scroll {
            opacity: 1;
            transform: translate3d(0, 0, 0);
            filter: blur(0);
        }

        html.motion-init .reveal-on-scroll {
            opacity: 0;
            transform: translate3d(0, 28px, 0) scale(0.98);
            filter: blur(3px);
            transition: opacity 0.72s ease, transform 0.72s cubic-bezier(0.22, 1, 0.36, 1), filter 0.72s ease;
            will-change: opacity, transform, filter;
        }

        html.motion-init .reveal-on-scroll[data-reveal="left"] {
            transform: translate3d(-36px, 24px, 0) scale(0.98);
        }

        html.motion-init .reveal-on-scroll[data-reveal="right"] {
            transform: translate3d(36px, 24px, 0) scale(0.98);
        }

        html.motion-init .reveal-on-scroll.is-visible {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
            filter: blur(0);
        }

        @media (prefers-reduced-motion: reduce) {
            html.motion-init .reveal-on-scroll,
            html.motion-init .reveal-on-scroll[data-reveal="left"],
            html.motion-init .reveal-on-scroll[data-reveal="right"] {
                opacity: 1;
                transform: none;
                filter: none;
                transition: none;
            }

            body.route-exit {
                transition: none;
            }
        }

        html[data-theme="dark"] body {
            background-color: #0b1220;
            color: #e2e8f0;
        }

        html[data-theme="dark"] .bg-slate-50,
        html[data-theme="dark"] .bg-slate-100\/70,
        html[data-theme="dark"] .bg-white,
        html[data-theme="dark"] .bg-white\/90,
        html[data-theme="dark"] .bg-white\/95,
        html[data-theme="dark"] .bg-emerald-50 {
            background-color: #0f172a;
        }

        html[data-theme="dark"] .border-slate-200,
        html[data-theme="dark"] .border-slate-300,
        html[data-theme="dark"] .border-slate-400,
        html[data-theme="dark"] .border-emerald-100,
        html[data-theme="dark"] .border-emerald-200 {
            border-color: #334155;
        }

        html[data-theme="dark"] .text-slate-900 {
            color: #f8fafc;
        }

        html[data-theme="dark"] .text-slate-700 {
            color: #e2e8f0;
        }

        html[data-theme="dark"] .text-slate-600 {
            color: #cbd5e1;
        }

        html[data-theme="dark"] .text-slate-500 {
            color: #94a3b8;
        }

        html[data-theme="dark"] .text-slate-300 {
            color: #cbd5e1;
        }

        html[data-theme="dark"] .hover\:bg-slate-100:hover {
            background-color: #1e293b;
        }

        html[data-theme="dark"] .theme-button-primary {
            background: linear-gradient(135deg, #14532d, #166534);
            border-color: #22c55e;
            color: #ecfdf5;
            box-shadow: 0 0 0 1px rgba(74, 222, 128, 0.35), 0 12px 24px -14px rgba(34, 197, 94, 0.85), inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        html[data-theme="dark"] .theme-button-primary:hover {
            background-color: #15803d;
            border-color: #22c55e;
            color: #f0fdf4;
        }

        html[data-theme="dark"] .theme-button-secondary {
            background-color: rgba(20, 83, 45, 0.45);
            border-color: #16a34a;
            color: #bbf7d0;
            box-shadow: 0 0 0 1px rgba(74, 222, 128, 0.2), inset 0 0 0 1px rgba(34, 197, 94, 0.15);
        }

        html[data-theme="dark"] .theme-button-secondary:hover {
            background-color: rgba(21, 101, 52, 0.72);
            border-color: #4ade80;
            color: #dcfce7;
        }

        html[data-theme="dark"] #about > div[aria-hidden="true"] {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(15, 23, 42, 0.8), rgba(30, 41, 59, 0.95));
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">
    <div class="bg-slate-900 text-slate-200">
        <div class="mx-auto flex w-full max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-2 text-xs sm:px-6 lg:px-8">
            <p class="font-semibold uppercase tracking-[0.12em] text-emerald-200">ICAS Philippines Learning Management System</p>
            <p class="text-slate-300">Open Monday to Friday, 7:00 AM - 6:00 PM</p>
        </div>
    </div>

    <header class="border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                <img src="{{ asset('images/icas-logo.png') }}" alt="ICAS Logo" class="h-12 w-auto object-contain">
                <span class="display-font text-lg font-bold text-[var(--brand-900)]">ICAS LMS</span>
            </a>

            <nav class="hidden items-center gap-8 text-sm font-semibold text-slate-600 lg:flex">
                <a href="#about" class="transition hover:text-[var(--brand-700)]">About</a>
                <a href="#portal" class="transition hover:text-[var(--brand-700)]">Portal Access</a>
                <a href="#offerings" class="transition hover:text-[var(--brand-700)]">Offerings</a>
                <a href="#campuses" class="transition hover:text-[var(--brand-700)]">Campuses</a>
                <a href="#admissions" class="transition hover:text-[var(--brand-700)]">Admissions</a>
                <a href="#contact" class="transition hover:text-[var(--brand-700)]">Contact</a>
            </nav>

            <div class="flex items-center gap-2 sm:gap-3">
                <button id="theme-toggle" type="button" aria-label="Toggle light and dark mode" aria-pressed="false" class="theme-button-secondary inline-flex min-h-[44px] items-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 transition hover:border-slate-400 hover:bg-slate-100">
                    <svg id="theme-icon-moon" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 1012 21a8.966 8.966 0 008.354-5.646z"></path>
                    </svg>
                    <svg id="theme-icon-sun" class="hidden h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364 6.364l-1.414-1.414M7.05 7.05 5.636 5.636m12.728 0L16.95 7.05M7.05 16.95l-1.414 1.414M12 8a4 4 0 100 8 4 4 0 000-8z"></path>
                    </svg>
                </button>
                <a href="{{ route('login') }}" data-page-transition class="theme-button-secondary inline-flex min-h-[44px] items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-slate-400 hover:bg-slate-100">Login</a>
                <a href="{{ route('register') }}" data-page-transition class="theme-button-primary inline-flex min-h-[44px] items-center rounded-xl bg-[var(--brand-700)] px-4 py-2 text-sm font-bold text-white transition hover:bg-[var(--brand-900)]">Create Account</a>
            </div>
        </div>
    </header>

    <main>
        <section id="about" class="relative overflow-hidden">
            <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-br from-emerald-100/60 via-white to-emerald-200/50"></div>
            <div class="relative mx-auto grid w-full max-w-7xl gap-8 px-4 py-10 sm:px-6 md:py-14 lg:grid-cols-2 lg:items-center lg:gap-12 lg:px-8 lg:py-16">
                <div class="reveal-on-scroll" data-reveal="left">
                    <p class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] text-[var(--brand-700)]">Infotech College of Arts and Sciences</p>
                    <h1 class="display-font mt-4 text-3xl font-bold leading-tight text-[var(--brand-900)] sm:text-4xl lg:text-5xl">
                        Over 30 years of practical, industry-focused education in the Philippines.
                    </h1>
                    <p class="mt-4 max-w-xl text-base leading-relaxed text-slate-600 sm:text-lg">
                        ICAS is a private educational institution with branches in Sucat, Paranaque and Marcos Highway, Marikina, offering Senior High School, college degrees, and TESDA-accredited technical-vocational programs focused on ICT, hospitality, and business management.
                    </p>

                    <div class="mt-7 flex flex-wrap items-center gap-3">
                        <a href="{{ route('register') }}" data-page-transition class="theme-button-primary inline-flex min-h-[44px] items-center rounded-xl bg-[var(--brand-700)] px-6 py-3 text-sm font-extrabold uppercase tracking-[0.1em] text-white transition hover:bg-[var(--brand-900)]">Create Account</a>
                        <a href="{{ route('login') }}" data-page-transition class="theme-button-secondary inline-flex min-h-[44px] items-center rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-extrabold uppercase tracking-[0.1em] text-slate-700 transition hover:border-slate-400 hover:bg-slate-100">Sign In</a>
                    </div>

                    <div class="mt-7 grid max-w-lg grid-cols-3 gap-3">
                        <article class="rounded-2xl border border-emerald-100 bg-white p-3 shadow-sm">
                            <p class="display-font text-xl font-bold text-[var(--brand-900)]">30+</p>
                            <p class="mt-1 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Years in Operation</p>
                        </article>
                        <article class="rounded-2xl border border-emerald-100 bg-white p-3 shadow-sm">
                            <p class="display-font text-xl font-bold text-[var(--brand-900)]">10,000+</p>
                            <p class="mt-1 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Graduates</p>
                        </article>
                        <article class="rounded-2xl border border-emerald-100 bg-white p-3 shadow-sm">
                            <p class="display-font text-xl font-bold text-[var(--brand-900)]">3</p>
                            <p class="mt-1 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Learning Pathways</p>
                        </article>
                    </div>
                </div>

                <div class="relative reveal-on-scroll" data-reveal="right" data-reveal-delay="90">
                    <div class="rounded-3xl border border-emerald-200 bg-white/90 p-4 shadow-[0_30px_60px_-35px_rgba(15,79,42,0.45)] backdrop-blur sm:p-5">
                        <img src="{{ asset('images/building.jpg') }}" alt="ICAS LMS Preview" class="h-64 w-full rounded-2xl object-cover sm:h-80">
                        <div class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--brand-700)]">Institution Snapshot</p>
                            <ul class="mt-2 space-y-1 text-sm font-semibold text-slate-700">
                                <li>Established in the early 1990s with expansion in 1991.</li>
                                <li>College status and growth across Metro Manila and nearby provinces.</li>
                                <li>Community-focused and affordable education model.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="portal" class="border-y border-slate-200 bg-slate-100/70">
            <div class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-12">
                <div class="mx-auto max-w-3xl text-center reveal-on-scroll" data-reveal="up">
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--brand-700)]">Portal Access</p>
                    <h2 class="display-font mt-2 text-3xl font-bold text-[var(--brand-900)]">Login and Register are now on separate pages</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">
                        Choose Login if you already have an account, or Create Account if you are new to ICAS LMS.
                    </p>
                </div>

                <div class="mt-7 grid gap-4 md:grid-cols-2">
                    <article class="reveal-on-scroll rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" data-reveal="up" data-reveal-delay="70">
                        <p class="text-xs font-bold uppercase tracking-[0.1em] text-slate-500">For existing users</p>
                        <h3 class="display-font mt-2 text-2xl font-bold text-[var(--brand-900)]">Login</h3>
                        <p class="mt-2 text-sm text-slate-600">Student, Faculty, and Admin users can sign in on a dedicated login page.</p>
                        <a href="{{ route('login') }}" data-page-transition class="theme-button-secondary mt-5 inline-flex min-h-[44px] items-center rounded-xl border border-slate-300 px-5 py-2 text-sm font-bold text-slate-700 transition hover:border-slate-400 hover:bg-slate-100">Go to Login</a>
                    </article>

                    <article class="reveal-on-scroll rounded-3xl border border-emerald-200 bg-white p-6 shadow-sm sm:p-8" data-reveal="up" data-reveal-delay="140">
                        <p class="text-xs font-bold uppercase tracking-[0.1em] text-[var(--brand-700)]">For new users</p>
                        <h3 class="display-font mt-2 text-2xl font-bold text-[var(--brand-900)]">Create Account</h3>
                        <p class="mt-2 text-sm text-slate-600">New users can register on a dedicated page with complete role selection and onboarding fields.</p>
                        <a href="{{ route('register') }}" data-page-transition class="theme-button-primary mt-5 inline-flex min-h-[44px] items-center rounded-xl bg-[var(--brand-700)] px-5 py-2 text-sm font-bold text-white transition hover:bg-[var(--brand-900)]">Go to Register</a>
                    </article>
                </div>
            </div>
        </section>

        <section id="offerings" class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 md:py-12 lg:px-8">
            <div class="mb-6 reveal-on-scroll" data-reveal="up">
                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--brand-700)]">Academic Structure</p>
                <h2 class="display-font mt-2 text-3xl font-bold text-[var(--brand-900)]">Programs and training tracks at ICAS</h2>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <article class="reveal-on-scroll rounded-3xl border border-slate-200 bg-white p-6 shadow-sm" data-reveal="up" data-reveal-delay="50">
                    <h2 class="display-font text-xl font-bold text-[var(--brand-900)]">Senior High School</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">SHS strands include ICT, HE (Home Economics), HUMSS, GAS, and ABM.</p>
                </article>
                <article class="reveal-on-scroll rounded-3xl border border-slate-200 bg-white p-6 shadow-sm" data-reveal="up" data-reveal-delay="100">
                    <h2 class="display-font text-xl font-bold text-[var(--brand-900)]">College Degrees</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">Bachelor of Science in Information Technology (BSIT) and BS in Hospitality Management.</p>
                </article>
                <article class="reveal-on-scroll rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:col-span-2 lg:col-span-1" data-reveal="up" data-reveal-delay="150">
                    <h2 class="display-font text-xl font-bold text-[var(--brand-900)]">TESDA Courses</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">Cookery NC II, Housekeeping NC II, Bookkeeping NC III, and Events Management.</p>
                </article>
                <article class="reveal-on-scroll rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:col-span-2 lg:col-span-1" data-reveal="up" data-reveal-delay="200">
                    <h2 class="display-font text-xl font-bold text-[var(--brand-900)]">Career Readiness</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">Programs are designed to produce industry-ready workers in high-demand fields.</p>
                </article>
            </div>
        </section>

        <section id="campuses" class="border-y border-slate-200 bg-white">
            <div class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 md:py-12 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-3 reveal-on-scroll" data-reveal="up">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--brand-700)]">Campus Locations</p>
                        <h2 class="display-font mt-2 text-3xl font-bold text-[var(--brand-900)]">Current branches and access points</h2>
                    </div>
                    <a href="{{ route('register') }}" data-page-transition class="theme-button-primary inline-flex min-h-[44px] items-center rounded-xl bg-[var(--brand-700)] px-5 py-2 text-sm font-bold text-white transition hover:bg-[var(--brand-900)]">Join ICAS LMS</a>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-3">
                    <article class="reveal-on-scroll rounded-2xl bg-[var(--leaf-100)] p-5" data-reveal="up" data-reveal-delay="50">
                        <h3 class="display-font text-lg font-bold text-[var(--brand-900)]">Sucat Branch</h3>
                        <p class="mt-2 text-sm text-slate-700">8347 Dr. A. Santos Ave., Brgy San Antonio, Paranaque City</p>
                        <p class="mt-2 text-sm font-semibold text-[var(--brand-700)]">(02) 7-717-2982</p>
                    </article>
                    <article class="reveal-on-scroll rounded-2xl bg-[var(--leaf-100)] p-5" data-reveal="up" data-reveal-delay="100">
                        <h3 class="display-font text-lg font-bold text-[var(--brand-900)]">Marcos Highway Branch</h3>
                        <p class="mt-2 text-sm text-slate-700">3rd Flr. Buenviaje Bldg., Felix Ave. cor. Marcos Highway, Marikina City</p>
                        <p class="mt-2 text-sm font-semibold text-[var(--brand-700)]">Marikina/Pasig boundary location</p>
                    </article>
                    <article class="reveal-on-scroll rounded-2xl bg-[var(--leaf-100)] p-5" data-reveal="up" data-reveal-delay="150">
                        <h3 class="display-font text-lg font-bold text-[var(--brand-900)]">Historical Expansion</h3>
                        <p class="mt-2 text-sm text-slate-700">Earlier expansion included locations in Mandaluyong, Laguna, and Batangas during growth in the 1990s.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="admissions" class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 md:py-12 lg:px-8">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 reveal-on-scroll" data-reveal="up">
                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--brand-700)]">Admissions and Access</p>
                <h2 class="display-font mt-2 text-3xl font-bold text-[var(--brand-900)]">Flexible entry pathways for more learners</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <article class="reveal-on-scroll rounded-2xl bg-slate-50 p-4" data-reveal="up" data-reveal-delay="60">
                        <h3 class="font-bold text-slate-900">Open Admissions Options</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">ICAS accepts 2-year transferees and Alternative Learning System (ALS) graduates, with many programs offering no entrance exams.</p>
                    </article>
                    <article class="reveal-on-scroll rounded-2xl bg-slate-50 p-4" data-reveal="up" data-reveal-delay="120">
                        <h3 class="font-bold text-slate-900">Community and Affordability</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">The school is known as a community-focused and affordable option, with some campuses offering free tuition or low annual fees.</p>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <footer id="contact" class="bg-slate-950 text-slate-200">
        <div class="mx-auto w-full max-w-7xl px-4 py-8 text-sm sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-[1.5fr_1fr] md:items-center">
                <div>
                    <p class="font-semibold text-emerald-200">Infotech College of Arts and Sciences (ICAS)</p>
                    <p class="mt-2 text-slate-300">Sucat Branch: 8347 Dr. A. Santos Ave., Brgy San Antonio, Paranaque City</p>
                    <p class="text-slate-300">Phone: (02) 7-717-2982</p>
                    <p class="mt-1 text-slate-300">Marcos Highway Branch: 3rd Flr. Buenviaje Bldg., Felix Ave. cor. Marcos Highway, Marikina City</p>
                </div>

                <div class="flex flex-wrap items-center gap-5 md:justify-end">
                    <a href="{{ route('login') }}" data-page-transition class="font-semibold text-emerald-300 hover:text-emerald-200">Login</a>
                    <a href="{{ route('register') }}" data-page-transition class="font-semibold text-emerald-300 hover:text-emerald-200">Register</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const storageKey = 'icas-home-theme';
            const root = document.documentElement;
            const toggleButton = document.getElementById('theme-toggle');
            const moonIcon = document.getElementById('theme-icon-moon');
            const sunIcon = document.getElementById('theme-icon-sun');

            if (toggleButton && moonIcon && sunIcon) {
                function updateToggleState(theme) {
                    const isDark = theme === 'dark';

                    toggleButton.setAttribute('aria-pressed', isDark ? 'true' : 'false');
                    moonIcon.classList.toggle('hidden', isDark);
                    sunIcon.classList.toggle('hidden', !isDark);
                }

                const activeTheme = root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                updateToggleState(activeTheme);

                toggleButton.addEventListener('click', function () {
                    const nextTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                    root.setAttribute('data-theme', nextTheme);
                    localStorage.setItem(storageKey, nextTheme);
                    updateToggleState(nextTheme);
                });
            }

            const revealItems = Array.from(document.querySelectorAll('.reveal-on-scroll'));

            if (revealItems.length === 0) {
                return;
            }

            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            document.querySelectorAll('a[data-page-transition]').forEach(function (link) {
                link.addEventListener('click', function (event) {
                    if (prefersReducedMotion || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                        return;
                    }

                    const target = link.getAttribute('target');

                    if (target && target.toLowerCase() !== '_self') {
                        return;
                    }

                    event.preventDefault();
                    document.body.classList.add('route-exit');

                    window.setTimeout(function () {
                        window.location.href = link.href;
                    }, 240);
                });
            });

            if (prefersReducedMotion) {
                root.classList.remove('motion-init');
                revealItems.forEach(function (item) {
                    item.classList.add('is-visible');
                });
                return;
            }

            const revealObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                });
            }, {
                threshold: 0.2,
                rootMargin: '0px 0px -8% 0px',
            });

            revealItems.forEach(function (item) {
                const rawDelay = Number.parseInt(item.getAttribute('data-reveal-delay') || '0', 10);

                if (!Number.isNaN(rawDelay) && rawDelay > 0) {
                    item.style.transitionDelay = rawDelay + 'ms';
                }

                revealObserver.observe(item);
            });
        });
    </script>
</body>
</html>
