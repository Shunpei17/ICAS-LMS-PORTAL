<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','Admin Portal') | ICAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --portal-admin-primary: {{ $portalTheme['admin'] ?? '#16a34a' }};
            --portal-faculty-primary: {{ $portalTheme['faculty'] ?? '#f59e0b' }};
            --portal-student-primary: {{ $portalTheme['student'] ?? '#7c3aed' }};
        }

        .admin-primary { background-color: var(--portal-admin-primary) !important; }
        .admin-text-primary { color: var(--portal-admin-primary) !important; }

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
            ['label' => 'Dashboard', 'routeName' => 'admin.dashboard', 'route' => route('admin.dashboard'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>'],
            ['label' => 'Announcements', 'routeName' => 'admin.announcements.index', 'route' => route('admin.announcements.index'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19a1 1 0 001.447.894L18 17h2a1 1 0 001-1V8a1 1 0 00-1-1h-2l-5.553-2.894A1 1 0 0011 5.882zM7 10v4m-3-3v2a1 1 0 001 1h2V10H5a1 1 0 00-1 1z"></path></svg>', 'badge' => $newAnnouncementsCount ?? 0],
            ['label' => 'Attendance', 'routeName' => 'admin.attendance', 'route' => route('admin.attendance'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-7 9l2 2 4-4"></path></svg>'],
            ['label' => 'Grades', 'routeName' => 'admin.grades', 'route' => route('admin.grades'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>'],
            ['label' => 'Manage Enrollment per Subject', 'routeName' => 'admin.enrollments', 'route' => route('admin.enrollments'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>'],
            ['label' => 'Classrooms', 'routeName' => 'admin.classrooms', 'route' => route('admin.classrooms'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1v1H9V7zm5 0h1v1h-1V7zm-5 4h1v1H9v-1zm5 0h1v1h-1v-1zm-5 4h1v1H9v-1zm5 0h1v1h-1v-1z"></path></svg>'],
            ['label' => 'Faculty Directory', 'routeName' => 'admin.faculty', 'route' => route('admin.faculty'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>'],
            ['label' => 'Documents', 'routeName' => 'admin.documents', 'route' => route('admin.documents'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>'],
            ['label' => 'Forum', 'routeName' => 'admin.forum', 'route' => route('admin.forum'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>'],
            ['label' => 'Audit Trail', 'routeName' => 'admin.audit-trail', 'route' => route('admin.audit-trail'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>'],
            ['label' => 'System Monitor', 'routeName' => 'admin.system-monitoring', 'route' => route('admin.system-monitoring'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>'],
            ['label' => 'Users', 'routeName' => 'admin.users', 'route' => route('admin.users'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>'],
            ['label' => 'My Profile', 'routeName' => 'admin.profile', 'route' => route('admin.profile'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>'],
            ['label' => 'System Backups', 'routeName' => 'admin.maintenance', 'route' => route('admin.maintenance'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>'],
            ['label' => 'Settings', 'routeName' => 'admin.settings', 'route' => route('admin.settings'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>'],
        ];
    @endphp

    @php
        $admin = auth()->user();
        $initials = $admin ? collect(explode(' ', trim($admin->name)))->map(fn($segment) => strtoupper(substr($segment, 0, 1)))->join('') : 'AD';
    @endphp

    <div class="h-screen flex flex-col md:flex-row" x-data="{ sidebarOpen: false }">
        <!-- Mobile Header -->
        <div class="md:hidden flex items-center justify-between admin-primary text-white p-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-white/20 grid place-items-center text-white font-bold">{{ $initials }}</div>
                <span class="font-semibold">Admin Portal</span>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="p-2 bg-white/10 rounded-md focus:outline-none focus:ring-2 focus:ring-white">
                <svg x-show="!sidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                <svg x-cloak x-show="sidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Sidebar Overlay -->
        <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 md:hidden"></div>

        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-80 admin-primary px-6 py-8 flex flex-col justify-between transform transition-transform duration-300 md:relative md:translate-x-0 overflow-y-auto shadow-xl md:shadow-none">
            <div class="space-y-10">
                <div class="flex items-center gap-3">
                    <div class="h-14 w-14 rounded-3xl bg-white/20 grid place-items-center text-white text-2xl font-bold">{{ $initials }}</div>
                    <div>
                        <p class="text-base font-semibold text-white">Admin Portal</p>
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
                    <div class="h-12 w-12 rounded-full bg-white/20 grid place-items-center text-white text-sm font-semibold">AD</div>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-green-100">{{ Auth::user()->email }}</p>
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
                <h1 class="text-4xl font-bold text-slate-900">Welcome, {{ $admin->name ?? 'Admin' }}!</h1>
                <p class="mt-3 text-slate-500">@yield('pageDescription','Manage system operations, users, and reports.')</p>
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
    <script>
        // Live fragment refresher: looks for elements with `data-live-key` and polls the current page
        (function () {
            const POLL_INTERVAL = 7000; // ms

            async function refreshFragment(key) {
                try {
                    const res = await fetch(window.location.href, { cache: 'no-store' });
                    if (!res.ok) return;
                    const text = await res.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(text, 'text/html');
                    const newNode = doc.querySelector('[data-live-key="' + key + '"]');
                    const oldNode = document.querySelector('[data-live-key="' + key + '"]');
                    if (newNode && oldNode && newNode.innerHTML !== oldNode.innerHTML) {
                        oldNode.innerHTML = newNode.innerHTML;
                    }
                } catch (e) {
                    // ignore network errors
                }
            }

            function startPolling() {
                const nodes = Array.from(document.querySelectorAll('[data-live-key]'));
                if (nodes.length === 0) return;
                setInterval(function () {
                    nodes.forEach(n => refreshFragment(n.getAttribute('data-live-key')));
                }, POLL_INTERVAL);
            }

            document.addEventListener('DOMContentLoaded', startPolling);
        })();
    </script>
    <script src="https://js.pusher.com/8.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.11.3/dist/echo.iife.js"></script>
    <script>
        // Optional: Laravel Echo listener (requires BROADCAST_DRIVER configured and Pusher keys)
        (function () {
            try {
                if (!window.Pusher) return;

                Pusher.logToConsole = false;

                const pusherKey = '{{ env('PUSHER_APP_KEY', '') }}';
                const pusherCluster = '{{ env('PUSHER_APP_CLUSTER', '') }}';

                if (!pusherKey) return;

                const echo = new window.Echo({
                    broadcaster: 'pusher',
                    key: pusherKey,
                    cluster: pusherCluster || undefined,
                    forceTLS: true,
                    encrypted: true,
                });

                echo.channel('admin').listen('AdminModelChanged', function (payload) {
                    // on any admin model change, refresh all live fragments immediately
                    document.querySelectorAll('[data-live-key]').forEach(function (el) {
                        const key = el.getAttribute('data-live-key');
                        // reuse existing refresh logic: fetch current page and swap fragment
                        fetch(window.location.href, { cache: 'no-store' }).then(r => r.text()).then(text => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(text, 'text/html');
                            const newNode = doc.querySelector('[data-live-key="' + key + '"]');
                            if (newNode) el.innerHTML = newNode.innerHTML;
                        }).catch(() => {});
                    });
                });
            } catch (e) {
                // silent
            }
        })();
    </script>
</body>
</html>
