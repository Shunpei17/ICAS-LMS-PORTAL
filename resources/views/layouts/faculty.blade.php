<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','Faculty Portal') | ICAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .portal-reveal {
            opacity: 1;
            transform: none;
            filter: none;
        }

        body.portal-motion-init .portal-reveal {
            opacity: 0;
            transform: translate3d(0, 20px, 0) scale(0.99);
            filter: blur(2px);
            transition: opacity 0.55s ease, transform 0.55s cubic-bezier(0.22, 1, 0.36, 1), filter 0.55s ease;
            will-change: opacity, transform, filter;
        }

        body.portal-motion-init .portal-reveal.is-visible {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
            filter: blur(0);
        }

        @media (prefers-reduced-motion: reduce) {
            body.portal-motion-init .portal-reveal {
                opacity: 1;
                transform: none;
                filter: none;
                transition: none;
                will-change: auto;
            }
        }
    </style>
</head>
<body class="bg-slate-50 h-screen overflow-hidden text-slate-900">
    @php
        $currentRoute = Route::currentRouteName();
        $navItems = [
            ['label' => 'Dashboard', 'routeName' => 'faculty.dashboard', 'route' => route('faculty.dashboard'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>'],
            ['label' => 'Announcements', 'routeName' => 'faculty.announcements.index', 'route' => route('faculty.announcements.index'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19a1 1 0 001.447.894L18 17h2a1 1 0 001-1V8a1 1 0 00-1-1h-2l-5.553-2.894A1 1 0 0011 5.882zM7 10v4m-3-3v2a1 1 0 001 1h2V10H5a1 1 0 00-1 1z"></path></svg>', 'badge' => $newAnnouncementsCount ?? 0],
            ['label' => 'Classrooms', 'routeName' => 'faculty.classrooms', 'route' => route('faculty.classrooms'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1v1H9V7zm5 0h1v1h-1V7zm-5 4h1v1H9v-1zm5 0h1v1h-1v-1zm-5 4h1v1H9v-1zm5 0h1v1h-1v-1z"></path></svg>'],
            ['label' => 'Grade Management', 'routeName' => 'faculty.grades', 'route' => route('faculty.grades'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>'],
            ['label' => 'Enrollments', 'routeName' => 'faculty.enrollments', 'route' => route('faculty.enrollments'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>', 'badge' => \App\Models\StudentModuleRecord::where('enrollment_status','pending')->count()],
            ['label' => 'Forum', 'routeName' => 'faculty.forum', 'route' => route('faculty.forum'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>'],
            ['label' => 'My Profile', 'routeName' => 'faculty.profile', 'route' => route('faculty.profile'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>'],
            ['label' => 'My Schedule', 'routeName' => 'faculty.schedule', 'route' => route('faculty.schedule'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>'],
            ['label' => 'Settings', 'routeName' => 'faculty.settings', 'route' => route('faculty.settings'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>'],
        ];
    @endphp

    <div class="h-screen flex flex-col md:flex-row" x-data="{ sidebarOpen: false }">
        <!-- Mobile Header -->
        <div class="md:hidden flex items-center justify-between bg-green-600 text-white p-4">
            <div class="flex items-center gap-3">
                @php
                    $faculty = auth()->user();
                    $initials = $faculty ? collect(explode(' ', trim($faculty->name)))->map(fn($segment) => strtoupper(substr($segment, 0, 1)))->join('') : 'F';
                @endphp
                <div class="h-10 w-10 rounded-xl bg-white/20 grid place-items-center text-white font-bold">{{ $initials }}</div>
                <span class="font-semibold">Faculty Portal</span>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="p-2 bg-white/10 rounded-md focus:outline-none focus:ring-2 focus:ring-white">
                <svg x-show="!sidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                <svg x-cloak x-show="sidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Sidebar Overlay -->
        <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 md:hidden"></div>

        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-80 bg-green-600 px-6 py-8 flex flex-col justify-between transform transition-transform duration-300 md:relative md:translate-x-0 overflow-y-auto shadow-xl md:shadow-none">
            <div class="space-y-10">
                <div class="flex items-center gap-3">
                    <div class="h-14 w-14 rounded-3xl bg-white/20 grid place-items-center text-white text-2xl font-bold">{{ $initials }}</div>
                    <div>
                        <p class="text-base font-semibold text-white">Faculty Portal</p>
                        <p class="text-[10px] font-bold text-green-200 uppercase tracking-widest mt-0.5 opacity-80">{{ $activeTerm }}</p>
                    </div>
                </div>

                <nav class="space-y-2">
                    @foreach($navItems as $item)
                        <a href="{{ $item['route'] }}" class="flex items-center justify-between gap-3 rounded-3xl px-4 py-3 text-sm font-semibold transition {{ $currentRoute === $item['routeName'] ? 'bg-white/20 text-white shadow-sm' : 'text-green-100 hover:bg-white/10' }}">
                            <span class="flex items-center gap-3">
                                <span class="text-lg">{!! $item['icon'] !!}</span>
                                {{ $item['label'] }}
                            </span>

                            @if(($item['badge'] ?? 0) > 0)
                                <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-amber-300 px-2 py-1 text-xs font-bold text-green-900">
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="rounded-3xl bg-white/10 p-6">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-white/20 grid place-items-center text-white text-sm font-semibold">{{ $initials }}</div>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ $faculty->name ?? 'Faculty Member' }}</p>
                        <p class="text-xs text-green-100">{{ $faculty->email ?? 'faculty@school.edu' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-5">
                    @csrf
                    <button type="submit" class="w-full rounded-3xl bg-white px-4 py-3 text-sm font-semibold text-green-700 hover:bg-green-50 transition">Logout</button>
                </form>
            </div>
        </aside>

        <main class="flex-1 p-4 sm:p-6 md:p-8 w-full max-w-full overflow-y-auto" data-portal-content>
            <header class="mb-8" data-portal-header>
                <h1 class="text-4xl font-bold text-slate-900">Welcome, {{ $faculty->name ?? 'Faculty' }}!</h1>
                <p class="mt-3 text-slate-500">{{ $pageDescription ?? 'Faculty Dashboard Overview' }}</p>
            </header>

            @yield('content')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const contentRoot = document.querySelector('[data-portal-content]');

            if (!contentRoot) {
                return;
            }

            const selectors = [
                '[data-portal-header]',
                'section',
                'article',
                'form',
                'table',
                '.rounded-3xl',
                '.rounded-2xl',
            ];
            const candidateNodes = Array.from(contentRoot.querySelectorAll(selectors.join(',')));
            const seen = new Set();
            const revealNodes = candidateNodes.filter(function (node) {
                if (seen.has(node)) {
                    return false;
                }

                seen.add(node);

                return !node.hasAttribute('data-no-portal-animate');
            });

            if (revealNodes.length === 0) {
                return;
            }

            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (!prefersReducedMotion) {
                document.body.classList.add('portal-motion-init');
            }

            revealNodes.forEach(function (node, index) {
                node.classList.add('portal-reveal');
                node.style.transitionDelay = Math.min(index * 45, 360) + 'ms';
            });

            if (prefersReducedMotion || !('IntersectionObserver' in window)) {
                revealNodes.forEach(function (node) {
                    node.classList.add('is-visible');
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
                threshold: 0.12,
                rootMargin: '0px 0px -8% 0px',
            });

            revealNodes.forEach(function (node) {
                revealObserver.observe(node);
            });
        });
    </script>
</body>
</html>