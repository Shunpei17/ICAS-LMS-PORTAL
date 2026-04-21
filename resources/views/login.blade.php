<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ICAS Enrollment Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-900: #0d3f23;
            --brand-700: #1f7a40;
            --brand-500: #2fa15a;
            --sun-400: #f2c94c;
            --cursor-x: 50vw;
            --cursor-y: 50vh;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background:
                radial-gradient(circle at 12% 14%, rgba(255, 255, 255, 0.32), rgba(255, 255, 255, 0) 40%),
                radial-gradient(circle at 88% 82%, rgba(242, 201, 76, 0.26), rgba(242, 201, 76, 0) 36%),
                linear-gradient(145deg, #0d3f23 0%, #176e3a 54%, #f2c94c 132%);
        }

        .display-font {
            font-family: 'Sora', sans-serif;
        }

        .animated-backdrop {
            pointer-events: none;
            position: fixed;
            inset: 0;
            z-index: -20;
            overflow: hidden;
        }

        .bg-grid {
            position: absolute;
            inset: -30%;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.08) 1px, transparent 1px);
            background-size: 34px 34px;
            opacity: 0.32;
            transform: rotate(-5deg);
            animation: grid-pan 22s linear infinite;
        }

        .bg-orb {
            position: absolute;
            border-radius: 9999px;
            filter: blur(1px);
            mix-blend-mode: screen;
        }

        .orb-white {
            width: 44vmax;
            height: 44vmax;
            top: -16vmax;
            left: -10vmax;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0));
            animation: orb-float-a 20s ease-in-out infinite;
        }

        .orb-green {
            width: 34vmax;
            height: 34vmax;
            bottom: -12vmax;
            left: 16vmax;
            background: radial-gradient(circle, rgba(47, 161, 90, 0.62), rgba(47, 161, 90, 0));
            animation: orb-float-b 18s ease-in-out infinite;
        }

        .orb-dark {
            width: 36vmax;
            height: 36vmax;
            top: 18vmax;
            right: -10vmax;
            background: radial-gradient(circle, rgba(13, 63, 35, 0.68), rgba(13, 63, 35, 0));
            animation: orb-float-c 24s ease-in-out infinite;
        }

        .orb-yellow {
            width: 30vmax;
            height: 30vmax;
            top: -6vmax;
            right: 24vmax;
            background: radial-gradient(circle, rgba(242, 201, 76, 0.55), rgba(242, 201, 76, 0));
            animation: orb-float-d 16s ease-in-out infinite;
        }

        .cursor-reactor {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(320px circle at var(--cursor-x) var(--cursor-y), rgba(255, 255, 255, 0.24), rgba(255, 255, 255, 0) 58%),
                radial-gradient(220px circle at var(--cursor-x) var(--cursor-y), rgba(242, 201, 76, 0.18), rgba(242, 201, 76, 0) 70%);
            opacity: 1;
            transition: opacity 0.28s ease;
        }

        body.no-pointer-react .cursor-reactor {
            opacity: 0.42;
        }

        .page-shell {
            position: relative;
        }

        .home-back-link {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 20;
            min-width: 46px;
            height: 46px;
            border-radius: 14px;
            border: none;
            background: transparent;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0 12px;
            box-shadow: none;
            backdrop-filter: none;
            transition: transform 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
        }

        .home-back-link:hover {
            transform: translateY(-1px);
            background: transparent;
            border-color: transparent;
        }

        .home-back-link:focus-visible {
            outline: 2px solid rgba(255, 255, 255, 0.86);
            outline-offset: 2px;
        }

        .hero-panel {
            background: transparent;
            border: none;
            box-shadow: none;
            backdrop-filter: none;
        }

        .auth-panel {
            position: relative;
            overflow: hidden;
            background: linear-gradient(170deg, rgba(255, 255, 255, 0.24), rgba(255, 255, 255, 0.08));
            border: 1px solid rgba(255, 255, 255, 0.34);
            box-shadow: 0 34px 84px -44px rgba(0, 0, 0, 0.85);
            transform-style: preserve-3d;
            transition: transform 0.14s ease-out;
        }

        .auth-panel::before {
            content: '';
            position: absolute;
            left: -34%;
            right: -34%;
            top: -20px;
            height: 180px;
            background: linear-gradient(95deg, rgba(255, 255, 255, 0), rgba(255, 255, 255, 0.24), rgba(242, 201, 76, 0.36), rgba(255, 255, 255, 0));
            transform: rotate(-5deg);
            pointer-events: none;
        }

        .field-wrap {
            position: relative;
        }

        .field-icon {
            color: rgba(255, 255, 255, 0.9);
            width: 34px;
            height: 34px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: grid;
            place-items: center;
        }

        .role-btn {
            border: 1px solid transparent;
            transition: all 0.24s ease;
        }

        .role-btn-active {
            background: rgba(255, 255, 255, 0.96);
            color: #195e34;
            box-shadow: 0 12px 24px -20px rgba(0, 0, 0, 0.8);
            border-color: rgba(255, 255, 255, 0.9);
        }

        .role-btn-idle {
            color: rgba(236, 253, 245, 0.94);
        }

        .role-btn-idle:hover {
            background: rgba(255, 255, 255, 0.14);
            border-color: rgba(255, 255, 255, 0.26);
        }

        .auth-input {
            border: 1px solid rgba(255, 255, 255, 0.22);
            background: rgba(255, 255, 255, 0.13);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        .auth-input:focus {
            border-color: rgba(255, 255, 255, 0.72);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.18);
        }

        .primary-action {
            background: linear-gradient(135deg, #ffffff 0%, #f9ffd8 132%);
            color: var(--brand-700);
            box-shadow: 0 16px 30px -24px rgba(9, 38, 21, 0.9);
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }

        .primary-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 24px 38px -24px rgba(8, 31, 18, 0.95);
            filter: saturate(1.04);
        }

        .auth-input::placeholder {
            color: rgba(255, 255, 255, 0.72);
        }

        body.page-enter {
            opacity: 0;
            transform: translate3d(0, 16px, 0) scale(0.995);
            filter: blur(2px);
        }

        body.page-enter.page-enter-active {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
            filter: blur(0);
            transition: opacity 0.48s ease, transform 0.48s cubic-bezier(0.22, 1, 0.36, 1), filter 0.48s ease;
        }

        body.page-exit {
            opacity: 0;
            transform: translate3d(0, -10px, 0) scale(0.995);
            filter: blur(2px);
            transition: opacity 0.24s ease, transform 0.24s ease, filter 0.24s ease;
            pointer-events: none;
        }

        @media (prefers-reduced-motion: reduce) {
            .bg-grid,
            .bg-orb {
                animation: none;
            }

            body.page-enter,
            body.page-enter.page-enter-active,
            body.page-exit {
                opacity: 1;
                transform: none;
                filter: none;
                transition: none;
            }
        }

        @keyframes grid-pan {
            0% {
                transform: translate3d(0, 0, 0) rotate(-5deg);
            }
            50% {
                transform: translate3d(-16px, -12px, 0) rotate(-5deg);
            }
            100% {
                transform: translate3d(-32px, -24px, 0) rotate(-5deg);
            }
        }

        @keyframes orb-float-a {
            0%,
            100% {
                transform: translate3d(0, 0, 0);
            }
            50% {
                transform: translate3d(22px, 26px, 0);
            }
        }

        @keyframes orb-float-b {
            0%,
            100% {
                transform: translate3d(0, 0, 0);
            }
            50% {
                transform: translate3d(-26px, -18px, 0);
            }
        }

        @keyframes orb-float-c {
            0%,
            100% {
                transform: translate3d(0, 0, 0);
            }
            50% {
                transform: translate3d(16px, -24px, 0);
            }
        }

        @keyframes orb-float-d {
            0%,
            100% {
                transform: translate3d(0, 0, 0);
            }
            50% {
                transform: translate3d(-20px, 20px, 0);
            }
        }
    </style>
</head>
<body class="min-h-screen overflow-x-hidden text-slate-900 lg:h-screen lg:overflow-hidden page-enter">
    <div aria-hidden="true" class="animated-backdrop">
        <div class="bg-grid"></div>
        <div class="bg-orb orb-white"></div>
        <div class="bg-orb orb-green"></div>
        <div class="bg-orb orb-dark"></div>
        <div class="bg-orb orb-yellow"></div>
        <div class="cursor-reactor"></div>
    </div>

    <main class="page-shell mx-auto flex min-h-screen w-full max-w-7xl items-start px-4 py-6 sm:px-6 sm:py-8 lg:h-screen lg:items-center lg:px-8 lg:py-4">
        <a href="{{ route('home') }}" data-page-transition aria-label="Back to homepage" class="home-back-link">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            <span class="text-sm font-semibold tracking-[0.08em]">Back</span>
        </a>
        <div class="grid w-full gap-6 lg:grid-cols-[1.2fr_0.8fr] lg:gap-6">
            <section class="hero-panel order-2 hidden w-full rounded-[2.4rem] p-6 text-white sm:p-8 lg:order-1 lg:block lg:p-8">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-emerald-100">
                    Enrollment Portfolio 2026
                </p>

                <h1 class="display-font mt-4 text-2xl font-bold leading-tight text-white xl:text-3xl">
                    Build your semester plan in one focused dashboard.
                </h1>

                <p class="mt-3 max-w-lg text-sm leading-relaxed text-emerald-50/90">
                    Track course availability, stay aligned with admission timelines, and keep your academic records synchronized across faculty and student services.
                </p>

                <div class="mt-5 grid max-w-xl gap-2 grid-cols-3">
                    <article class="rounded-2xl bg-white/10 p-3">
                        <p class="display-font text-xl font-bold text-white">1,240</p>
                        <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.1em] text-emerald-50/80">Students</p>
                    </article>
                    <article class="rounded-2xl bg-white/10 p-3">
                        <p class="display-font text-xl font-bold text-white">98%</p>
                        <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.1em] text-emerald-50/80">On Time</p>
                    </article>
                    <article class="rounded-2xl bg-white/10 p-3">
                        <p class="display-font text-xl font-bold text-white">42</p>
                        <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.1em] text-emerald-50/80">Modules</p>
                    </article>
                </div>

                <div class="mt-5 space-y-2 text-sm text-emerald-50/85">
                    <p>Unified access for student, faculty, and admin workflows.</p>
                    <p>Fast module enrollment with real-time progress tracking.</p>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-emerald-50/95">
                    <span class="rounded-full bg-white/10 px-3 py-1">Admission Window: Open</span>
                    <span class="rounded-full bg-white/10 px-3 py-1">Priority Cutoff: Apr 30</span>
                </div>
            </section>

            <section id="auth-panel" class="auth-panel order-1 mx-auto w-full max-w-[560px] rounded-[2.4rem] p-5 text-white backdrop-blur-md sm:p-8 lg:order-2 lg:max-w-[490px] lg:justify-self-end lg:p-6">
                <div class="text-center">
                    <img src="{{ asset('images/icas-logo.png') }}" alt="ICAS Philippines Logo" class="mx-auto h-16 w-auto object-contain sm:h-20">
                    <p class="mt-3 text-xs font-bold uppercase tracking-[0.2em] text-white/80">Learning Management System</p>
                    <h2 class="display-font mt-3 text-2xl font-bold">Sign In</h2>
                    <p class="mt-2 text-sm text-white/85">Continue to your enrollment portfolio and academic workspace.</p>
                </div>

                <div class="mt-5 rounded-2xl bg-white/10 p-4 lg:hidden">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-emerald-100/85">Enrollment Highlights</p>
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        <div class="rounded-xl bg-white/10 px-2 py-2 text-center">
                            <p class="display-font text-sm font-bold text-white">1,240</p>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.08em] text-emerald-50/80">Students</p>
                        </div>
                        <div class="rounded-xl bg-white/10 px-2 py-2 text-center">
                            <p class="display-font text-sm font-bold text-white">98%</p>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.08em] text-emerald-50/80">On Time</p>
                        </div>
                        <div class="rounded-xl bg-white/10 px-2 py-2 text-center">
                            <p class="display-font text-sm font-bold text-white">42</p>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.08em] text-emerald-50/80">Modules</p>
                        </div>
                    </div>
                </div>

                @if(session('status'))
                    <div class="mt-6 rounded-2xl bg-white/95 px-4 py-3 text-sm font-medium text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mt-6 rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <ul class="list-disc pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-5" id="loginForm">
                    @csrf
                    <input type="hidden" name="role" id="selected-role" value="{{ old('role', 'student') }}">

                    <div class="grid grid-cols-3 gap-1 rounded-full bg-black/15 p-1">
                        <button type="button" onclick="setRole('student')" id="btn-student" data-role-btn="student" class="role-btn role-btn-idle inline-flex h-10 items-center justify-center gap-1.5 rounded-full px-2 py-2 text-xs font-bold uppercase tracking-[0.08em] sm:text-sm">
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span>Student</span>
                        </button>
                        <button type="button" onclick="setRole('faculty')" id="btn-faculty" data-role-btn="faculty" class="role-btn role-btn-idle inline-flex h-10 items-center justify-center gap-1.5 rounded-full px-2 py-2 text-xs font-bold uppercase tracking-[0.08em] sm:text-sm">
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                            <span>Faculty</span>
                        </button>
                        <button type="button" onclick="setRole('admin')" id="btn-admin" data-role-btn="admin" class="role-btn role-btn-idle inline-flex h-10 items-center justify-center gap-1.5 rounded-full px-2 py-2 text-xs font-bold uppercase tracking-[0.08em] sm:text-sm">
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            <span>Admin</span>
                        </button>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="field-wrap relative">
                            <div class="field-icon pointer-events-none absolute inset-y-0 left-0 my-auto ml-3">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <input type="email" name="email" autocomplete="off" value="{{ old('email') }}" placeholder="email@school.edu" class="auth-input h-12 w-full rounded-2xl py-3 pl-14 pr-4 text-sm text-white outline-none transition" required>
                        </div>

                        <div class="field-wrap relative">
                            <div class="field-icon pointer-events-none absolute inset-y-0 left-0 my-auto ml-3">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <input type="password" name="password" placeholder="Enter your password" class="auth-input h-12 w-full rounded-2xl py-3 pl-14 pr-4 text-sm text-white outline-none transition" required>
                        </div>
                    </div>

                    <button type="submit" class="primary-action mt-5 inline-flex h-11 w-full items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-bold uppercase tracking-[0.12em] active:scale-[0.99]">
                        Sign In
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </button>

                    <p class="mt-4 text-center text-sm text-white/90">
                        <a href="{{ route('password.request') }}" class="font-semibold hover:text-white">Forgot your password?</a>
                    </p>
                </form>

                <p class="mt-6 text-center text-sm text-white/90">
                    New to ICAS?
                    <a href="{{ route('register') }}" data-page-transition class="font-bold text-white underline decoration-white/60 underline-offset-4 hover:decoration-white">Create account</a>
                </p>

                <button type="button" onclick="fillDemo()" class="mt-4 h-11 w-full rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/15">
                    Use demo credentials
                </button>
            </section>
        </div>
    </main>

    <script>
        function setRole(role) {
            document.getElementById('selected-role').value = role;

            document.querySelectorAll('[data-role-btn]').forEach(function (button) {
                const isActive = button.getAttribute('data-role-btn') === role;

                button.classList.toggle('role-btn-active', isActive);
                button.classList.toggle('role-btn-idle', !isActive);
            });
        }

        function fillDemo() {
            const currentRole = document.getElementById('selected-role').value;
            const emailInput = document.querySelector('input[name="email"]');
            const passwordInput = document.querySelector('input[name="password"]');

            const demoData = {
                student: 'student@school.edu',
                faculty: 'faculty@school.edu',
                admin: 'admin@school.edu',
            };

            emailInput.value = demoData[currentRole] ?? demoData.student;
            passwordInput.value = 'password123';
        }

        document.addEventListener('DOMContentLoaded', function () {
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const hasFinePointer = window.matchMedia('(pointer: fine)').matches;
            const authPanel = document.getElementById('auth-panel');

            if (prefersReducedMotion) {
                document.body.classList.add('page-enter-active');
            } else {
                window.requestAnimationFrame(function () {
                    document.body.classList.add('page-enter-active');
                });
            }

            if (!hasFinePointer || prefersReducedMotion) {
                document.body.classList.add('no-pointer-react');
            }

            if (!prefersReducedMotion) {
                window.addEventListener('pointermove', function (event) {
                    document.documentElement.style.setProperty('--cursor-x', event.clientX + 'px');
                    document.documentElement.style.setProperty('--cursor-y', event.clientY + 'px');
                });
            }

            if (authPanel && hasFinePointer && !prefersReducedMotion) {
                authPanel.addEventListener('pointermove', function (event) {
                    const rect = authPanel.getBoundingClientRect();
                    const relativeX = (event.clientX - rect.left) / rect.width;
                    const relativeY = (event.clientY - rect.top) / rect.height;
                    const rotateY = (relativeX - 0.5) * 7;
                    const rotateX = (0.5 - relativeY) * 7;

                    authPanel.style.transform = 'perspective(1100px) rotateX(' + rotateX.toFixed(2) + 'deg) rotateY(' + rotateY.toFixed(2) + 'deg)';
                });

                authPanel.addEventListener('pointerleave', function () {
                    authPanel.style.transform = 'perspective(1100px) rotateX(0deg) rotateY(0deg)';
                });
            }

            setRole(@json(old('role', 'student')));

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
                    document.body.classList.add('page-exit');

                    window.setTimeout(function () {
                        window.location.href = link.href;
                    }, 240);
                });
            });
        });
    </script>
</body>
</html>