@extends('layouts.admin')

@section('title', 'Announcements Management')
@section('pageDescription', 'Create, update, and publish announcements for faculty and students.')

@section('content')
    <div class="space-y-6">
        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">
                        {{ $editingAnnouncement ? 'Edit Announcement' : 'Create Announcement' }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">Set title, content, target audience, date, and optional attachment.</p>
                </div>

                @if($editingAnnouncement)
                    <a href="{{ route('admin.announcements.index') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Cancel Edit
                    </a>
                @endif
            </div>

            <form
                method="POST"
                action="{{ $editingAnnouncement ? route('announcements.update', $editingAnnouncement) : route('announcements.store') }}"
                enctype="multipart/form-data"
                class="mt-6 grid gap-4"
            >
                @csrf

                @if($editingAnnouncement)
                    @method('PUT')
                @endif

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="title" class="mb-2 block text-sm font-semibold text-slate-700">Title</label>
                        <input
                            id="title"
                            type="text"
                            name="title"
                            value="{{ old('title', $editingAnnouncement?->title) }}"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                            required
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label for="content" class="mb-2 block text-sm font-semibold text-slate-700">Description</label>
                        <textarea
                            id="content"
                            name="content"
                            rows="5"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                            required
                        >{{ old('content', $editingAnnouncement?->content) }}</textarea>
                    </div>

                    <div>
                        <label for="announcement_date" class="mb-2 block text-sm font-semibold text-slate-700">Date</label>
                        <input
                            id="announcement_date"
                            type="date"
                            name="announcement_date"
                            value="{{ old('announcement_date', $editingAnnouncement?->created_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                            required
                        />
                    </div>

                    <div>
                        <label for="audience" class="mb-2 block text-sm font-semibold text-slate-700">Visibility</label>
                        <select
                            id="audience"
                            name="audience"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                            required
                        >
                            @php
                                $selectedAudience = old('audience', $editingAnnouncement?->audience ?? 'all');
                            @endphp
                            <option value="all" @selected($selectedAudience === 'all')>All</option>
                            <option value="faculty" @selected($selectedAudience === 'faculty')>Faculty only</option>
                            <option value="student" @selected($selectedAudience === 'student')>Students only</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="attachment" class="mb-2 block text-sm font-semibold text-slate-700">Attachment (Optional)</label>
                        <input
                            id="attachment"
                            type="file"
                            name="attachment"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200"
                        />

                        @if($editingAnnouncement?->attachment_path)
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <a
                                    href="{{ route('file.show', ['type' => 'announcement_attachment', 'id' => $editingAnnouncement->id]) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="text-sm font-semibold text-sky-700 hover:text-sky-900"
                                >
                                    View current attachment
                                </a>

                                <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                                    <input type="checkbox" name="remove_attachment" value="1" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900" />
                                    Remove current attachment
                                </label>
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    <button type="submit" class="rounded-2xl bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                        {{ $editingAnnouncement ? 'Update Announcement' : 'Publish Announcement' }}
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-center justify-between gap-3">
                <h2 class="text-xl font-bold text-slate-900">Published Announcements</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $announcements->count() }} total</span>
            </div>

            <div class="space-y-4">
                @forelse($announcements as $announcement)
                    @php
                        $isNewest = $loop->first;
                        $audienceLabels = [
                            'all' => 'All',
                            'faculty' => 'Faculty',
                            'student' => 'Students',
                        ];
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

                            <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-bold text-slate-700">
                                {{ $audienceLabels[$announcement->audience] ?? 'All' }}
                            </span>
                        </div>

                        <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $announcement->content }}</p>

                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            @if($announcement->attachment_path)
                                <a
                                    href="{{ route('file.show', ['type' => 'announcement_attachment', 'id' => $announcement->id]) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-100"
                                >
                                    Open Attachment
                                </a>
                            @endif

                            <a href="{{ route('admin.announcements.index', ['edit' => $announcement->id]) }}" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                                Edit
                            </a>

                            <form method="POST" action="{{ route('announcements.destroy', $announcement) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete this announcement?')" class="rounded-xl border border-rose-300 bg-white px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <article class="rounded-3xl border border-slate-100 bg-slate-50 p-5">
                        <p class="text-sm font-semibold text-slate-700">No announcements yet.</p>
                        <p class="mt-1 text-sm text-slate-500">Publish your first announcement to keep users informed.</p>
                    </article>
                @endforelse
            </div>
        </section>
    </div>
@endsection
