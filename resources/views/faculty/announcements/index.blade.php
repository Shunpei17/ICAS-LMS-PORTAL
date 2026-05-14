@extends('layouts.faculty')

@section('title', 'Announcements')
@section('pageDescription', 'Latest notices for faculty members.')

@section('content')
    <div class="space-y-6" x-data="{ showModal: false }">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-center justify-between gap-3">
                <h2 class="text-xl font-bold text-slate-900">Faculty Announcements</h2>
                <div class="flex items-center gap-3">
                    <button 
                        @click="showModal = true"
                        class="rounded-xl bg-green-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                    >
                        Add Announcement
                    </button>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $announcements->count() }} available</span>
                </div>
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

                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700">
                                {{ $announcement->audience === 'all' ? 'All Departments' : 'Faculty' }}
                            </span>
                        </div>

                        <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $announcement->content }}</p>

                        @if($announcement->attachment_path)
                            <div class="mt-4">
                                    <a
                                        href="{{ route('file.show', ['type' => 'announcement_attachment', 'id' => $announcement->id]) }}"
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
                        <p class="mt-1 text-sm text-slate-500">Faculty announcements will appear here once published by admin.</p>
                    </article>
                @endforelse
            </div>
        </section>

        <!-- Add Announcement Modal -->
        <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/50 p-4 backdrop-blur-sm" x-cloak>
            <div 
                @click.away="showModal = false"
                x-show="showModal"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-lg rounded-3xl bg-white p-6 shadow-xl"
            >
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-slate-900">Add Announcement</h3>
                    <button @click="showModal = false" class="text-slate-400 transition hover:text-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 rounded-lg p-1">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('faculty.announcements.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="title" class="mb-1 block text-sm font-semibold text-slate-700">Title</label>
                            <input type="text" id="title" name="title" required class="block w-full rounded-xl border border-slate-200 p-3 text-sm focus:border-green-500 focus:ring-green-500">
                        </div>

                        <div>
                            <label for="content" class="mb-1 block text-sm font-semibold text-slate-700">Content/Description</label>
                            <textarea id="content" name="content" rows="4" required class="block w-full rounded-xl border border-slate-200 p-3 text-sm focus:border-green-500 focus:ring-green-500"></textarea>
                        </div>

                        <div>
                            <label for="attachment" class="mb-1 block text-sm font-semibold text-slate-700">Attachment (Optional)</label>
                            <input type="file" id="attachment" name="attachment" class="block w-full rounded-xl border border-slate-200 p-2 text-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showModal = false" class="rounded-xl px-4 py-2 text-sm font-bold text-slate-600 transition hover:bg-slate-100">Cancel</button>
                        <button type="submit" class="rounded-xl bg-green-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-green-700">Publish</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
