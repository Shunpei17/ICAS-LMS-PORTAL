@extends('layouts.admin')
@section('title', 'Forum Post Review')
@section('pageDescription', 'Review full discussion context and replies.')

@section('content')
<div class="space-y-6">
    <a href="{{ route('admin.forum') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Back to Forum Moderation
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Post Details --}}
        <div class="lg:col-span-2 space-y-6">
            <article class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <span class="rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-bold uppercase tracking-wider">{{ $forumThread->category }}</span>
                    @if($forumThread->is_flagged)
                        <span class="rounded-full bg-rose-100 text-rose-700 px-3 py-1 text-xs font-bold uppercase tracking-wider">🚩 Flagged</span>
                    @endif
                    @if(!$forumThread->is_visible)
                        <span class="rounded-full bg-slate-100 text-slate-600 px-3 py-1 text-xs font-bold uppercase tracking-wider">Hidden</span>
                    @endif
                </div>

                <h1 class="text-2xl font-bold text-slate-900 mb-4">{{ $forumThread->title }}</h1>
                <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed mb-8">
                    {!! nl2br(e($forumThread->content)) !!}
                </div>

                <div class="flex items-center justify-between pt-6 border-t border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-slate-200 grid place-items-center font-bold text-slate-600">
                            {{ strtoupper(substr($forumThread->user->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 text-sm">{{ $forumThread->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $forumThread->created_at->format('M j, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </article>

            {{-- Replies Section --}}
            <section class="space-y-4">
                <h3 class="text-lg font-bold text-slate-900">Replies ({{ $forumThread->replies->count() }})</h3>
                
                @forelse($forumThread->replies as $reply)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
                        <div class="flex items-start gap-4">
                            <div class="h-8 w-8 rounded-full bg-slate-200 grid place-items-center font-bold text-slate-500 text-xs">
                                {{ strtoupper(substr($reply->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-bold text-slate-900 text-sm">{{ $reply->user->name }}</span>
                                    <span class="text-[10px] text-slate-400 font-semibold uppercase">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-slate-600 leading-relaxed">
                                    {{ $reply->content }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-slate-400">
                        No replies yet for this discussion.
                    </div>
                @endforelse
            </section>
        </div>

        {{-- Moderation Sidebar --}}
        <div class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-bold text-slate-900 mb-4">Moderation Actions</h3>
                <div class="space-y-3">
                    <form method="POST" action="{{ route('admin.forum.toggleHide', $forumThread->id) }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">
                            @if($forumThread->is_visible)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                                Hide Discussion
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                Make Visible
                            @endif
                        </button>
                    </form>

                    @if(!$forumThread->is_flagged)
                        <form method="POST" action="{{ route('admin.forum.flag', $forumThread->id) }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700 hover:bg-rose-100 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 01-2 2zm9-13.5V9"></path></svg>
                                Flag for Review
                            </button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('admin.forum.delete', $forumThread->id) }}" onsubmit="return confirm('Are you sure you want to permanently delete this discussion and all its replies? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-3 text-sm font-bold text-white hover:bg-rose-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Permanently Delete
                        </button>
                    </form>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Post Metadata</h4>
                <div class="space-y-4">
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 uppercase">Author Role</p>
                        <p class="text-sm font-semibold text-slate-900">{{ ucfirst($forumThread->user->role) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 uppercase">Total Replies</p>
                        <p class="text-sm font-semibold text-slate-900">{{ $forumThread->replies->count() }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 uppercase">Total Views</p>
                        <p class="text-sm font-semibold text-slate-900">{{ number_format($forumThread->views) }}</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
