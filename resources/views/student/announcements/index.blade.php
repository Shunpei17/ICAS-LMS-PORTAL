@extends('layouts.student')

@section('title', 'Announcements')
@section('pageDescription', 'Latest notices and reminders for students.')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-center justify-between gap-3">
                <h2 class="text-xl font-bold text-slate-900">Student Announcements</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $announcements->count() }} available</span>
            </div>

            <div class="space-y-4">
                @forelse($announcements as $announcement)
                    @php
                        $isNewest = $loop->first;
                    @endphp

                    <article class="rounded-3xl border {{ $isNewest ? 'border-amber-300 bg-amber-50/50' : 'border-slate-100 bg-slate-50' }} p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">{{ $announcement->title }}</h3>
                                <p class="mt-1 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                    {{ $announcement->created_at?->format('F j, Y') }}
                                    @if($isNewest)
                                        <span class="ml-2 rounded-full bg-amber-200 px-2 py-1 text-[10px] text-amber-900">Newest</span>
                                    @endif
                                </p>
                            </div>

                            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700">
                                {{ $announcement->audience === 'all' ? 'All Students' : 'Student Only' }}
                            </span>
                        </div>

                        <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $announcement->content }}</p>

                        @if($announcement->attachment_path)
                            <div class="mt-4">
                                <a
                                    href="{{ asset('storage/' . $announcement->attachment_path) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-100"
                                >
                                    Open Attachment
                                </a>
                            </div>
                        @endif
                    </article>
                @empty
                    <article class="rounded-3xl border border-slate-100 bg-slate-50 p-5">
                        <p class="text-sm font-semibold text-slate-700">No announcements available.</p>
                        <p class="mt-1 text-sm text-slate-500">Student announcements will appear here once published by admin.</p>
                    </article>
                @endforelse
            </div>
        </section>
    </div>
@endsection
