@extends('layouts.admin')
@section('title', 'Forum Moderation')
@section('pageDescription', 'Monitor, moderate, and manage all forum posts and replies.')
@section('content')
<div class="space-y-6">
    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-4">
        @foreach($stats as $stat)
            @php
                $c = match($stat['color'] ?? 'slate'){
                    'rose'=>['bg-rose-50','border-rose-200','text-rose-700'],
                    'emerald'=>['bg-emerald-50','border-emerald-200','text-emerald-700'],
                    default=>['bg-white','border-slate-200','text-slate-900']
                };
            @endphp
            <div class="rounded-3xl {{ $c[0] }} border {{ $c[1] }} shadow-sm p-6">
                <p class="text-xs uppercase tracking-widest font-semibold text-slate-500">{{ $stat['label'] }}</p>
                <p class="mt-3 text-4xl font-black {{ $c[2] }}">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6" data-live-key="admin.forum.section">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">All Forum Posts</h2>
                <p class="text-sm text-slate-500 mt-1">Review and moderate student and faculty forum activity.</p>
            </div>
            <div class="flex gap-3">
                <select class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-green-400">
                    <option>All Roles</option><option>Student</option><option>Faculty</option><option>Admin</option>
                </select>
                <select class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-green-400">
                    <option>All Types</option><option>Posts</option><option>Replies</option>
                </select>
                <input type="date" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-green-400">
            </div>
        </div>

        <div class="space-y-3">
            @foreach($threads as $thread)
                @php
                    $flagged = ($thread->status ?? '') === 'flagged';
                    $author = $thread->user->name ?? 'Unknown User';
                    $role = $thread->user->role ?? 'Student';
                    $time = $thread->created_at?->format('M j Y h:i A') ?? '';
                @endphp

                <article class="rounded-2xl border {{ $flagged ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200 bg-slate-50' }} p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                <span class="rounded-full {{ $flagged ? 'bg-rose-100 text-rose-700' : 'bg-green-100 text-green-700' }} px-2.5 py-0.5 text-xs font-semibold">Post</span>
                                <span class="rounded-full bg-sky-100 text-sky-700 px-2.5 py-0.5 text-xs font-semibold">{{ $thread->category ?? 'General' }}</span>
                                @if($flagged)
                                    <span class="rounded-full bg-rose-100 text-rose-700 px-2.5 py-0.5 text-xs font-bold">🚩 Flagged</span>
                                @endif
                            </div>
                            <p class="font-semibold text-slate-900 text-sm">{{ $thread->title }}</p>
                            <p class="text-xs text-slate-500 mt-0.5 truncate max-w-lg">{{ \Illuminate\Support\Str::limit($thread->content, 180) }}</p>
                            <div class="flex items-center gap-2 mt-2 text-xs text-slate-400">
                                <span class="font-semibold text-slate-700">{{ $author }}</span>
                                @php $rBadge = match($role){'faculty'=>'bg-sky-100 text-sky-700','admin'=>'bg-violet-100 text-violet-700',default=>'bg-slate-100 text-slate-600'}; @endphp
                                <span class="rounded-full {{ $rBadge }} px-2 py-0.5 font-semibold">{{ ucfirst($role) }}</span>
                                <span>{{ $time }}</span>
                            </div>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            @if($flagged)
                                <form method="POST" action="{{ route('admin.forum.delete', $thread->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-xl bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700 transition">Delete</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.forum.toggleHide', $thread->id) }}">
                                @csrf
                                <button type="submit" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">Hide</button>
                            </form>
                            @unless($flagged)
                                <form method="POST" action="{{ route('admin.forum.flag', $thread->id) }}">
                                    @csrf
                                    <button type="submit" class="rounded-xl border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition">Remove</button>
                                </form>
                            @endunless
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</div>
@endsection