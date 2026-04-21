<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','Student Portal') | ICAS</title>
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
            ['label' => 'Dashboard', 'routeName' => 'student.dashboard', 'route' => route('student.dashboard'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>'],
            ['label' => 'Announcements', 'routeName' => 'student.announcements.index', 'route' => route('student.announcements.index'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19a1 1 0 001.447.894L18 17h2a1 1 0 001-1V8a1 1 0 00-1-1h-2l-5.553-2.894A1 1 0 0011 5.882zM7 10v4m-3-3v2a1 1 0 001 1h2V10H5a1 1 0 00-1 1z"></path></svg>', 'badge' => $newAnnouncementsCount ?? 0],
            ['label' => 'Enrollment', 'routeName' => 'student.enrollment', 'route' => route('student.enrollment'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>'],
            ['label' => 'My Grades', 'routeName' => 'student.grades', 'route' => route('student.grades'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>'],
            ['label' => 'Classrooms', 'routeName' => 'student.classrooms', 'route' => route('student.classrooms'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>'],
            ['label' => 'Documents', 'routeName' => 'student.documents', 'route' => route('student.documents'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>'],
            ['label' => 'Forum', 'routeName' => 'student.forum', 'route' => route('student.forum'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>'],
        ];
    @endphp

    <div class="h-screen flex flex-col md:flex-row" x-data="{ sidebarOpen: false }">
        <!-- Mobile Header -->
        <div class="md:hidden flex items-center justify-between bg-green-600 text-white p-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-white/20 grid place-items-center text-white font-bold">S</div>
                <span class="font-semibold">Student Portal</span>
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
                    <div class="h-14 w-14 rounded-3xl bg-white/20 grid place-items-center text-white text-2xl font-bold">S</div>
                    <div>
                        <p class="text-base font-semibold text-white">Student Portal</p>
                        <p class="text-xs text-green-100">ICAS</p>
                    </div>
                </div>

                <nav class="space-y-2">
                    @foreach($navItems as $item)
                        <a href="{{ $item['route'] }}" class="flex items-center justify-between gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ $currentRoute === $item['routeName'] ? 'bg-white/20 text-white shadow-sm' : 'text-green-100 hover:bg-white/10' }}">
                            <span class="flex items-center gap-3">
                                <span>{!! $item['icon'] !!}</span>
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

            <div class="rounded-3xl bg-white/10 p-5">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-white/20 grid place-items-center text-white text-sm font-semibold">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</div>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-green-100">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button type="submit" class="w-full rounded-3xl bg-white px-4 py-3 text-sm font-semibold text-green-700 hover:bg-green-50 transition">Logout</button>
                </form>
            </div>
        </aside>

        <main class="flex-1 p-4 sm:p-6 md:p-8 w-full max-w-full overflow-y-auto" data-portal-content>
            <div class="max-w-7xl mx-auto">
                <header class="mb-8" data-portal-header>
                    <h1 class="text-3xl font-bold text-slate-900">@yield('title','Welcome, Student!')</h1>
                    <p class="mt-2 text-slate-500">@yield('pageDescription','Here\'s your academic overview.')</p>
                </header>

                @yield('content')
            </div>
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