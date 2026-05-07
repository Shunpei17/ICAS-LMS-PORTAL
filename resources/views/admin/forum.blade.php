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
        </div>

        @if(session('status'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="space-y-4">
            @forelse($threads as $thread)
                @php
                    $isFlagged = $thread->is_flagged;
                    $isHidden = !$thread->is_visible;
                    $author = $thread->user->name ?? 'Unknown User';
                    $role = $thread->user->role ?? 'Student';
                    $time = $thread->created_at?->format('M j, Y g:i A') ?? '';
                @endphp

                <div class="group relative rounded-2xl border {{ $isFlagged ? 'border-rose-200 bg-rose-50/20' : ($isHidden ? 'border-slate-200 bg-slate-50/50 grayscale' : 'border-slate-100 bg-white hover:border-green-200 hover:shadow-md') }} p-5 transition-all">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="rounded-full bg-slate-100 text-slate-600 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider">{{ $thread->category }}</span>
                                @if($isFlagged)
                                    <span class="rounded-full bg-rose-100 text-rose-700 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider">🚩 Flagged</span>
                                @endif
                                @if($isHidden)
                                    <span class="rounded-full bg-slate-200 text-slate-600 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider">Hidden</span>
                                @endif
                                <span class="text-[10px] text-slate-400 font-semibold">{{ $thread->replies->count() }} Replies</span>
                            </div>
                            
                            <a href="{{ route('admin.forum.show', $thread->id) }}" class="block group-hover:text-green-700 transition">
                                <h3 class="font-bold text-slate-900 text-base mb-1">{{ $thread->title }}</h3>
                                <p class="text-sm text-slate-500 line-clamp-2">{{ $thread->content }}</p>
                            </a>

                            <div class="flex items-center gap-2 mt-4 text-xs">
                                <div class="h-6 w-6 rounded-full bg-slate-100 grid place-items-center font-bold text-slate-500 text-[10px]">
                                    {{ strtoupper(substr($author, 0, 1)) }}
                                </div>
                                <span class="font-bold text-slate-700">{{ $author }}</span>
                                <span class="text-slate-300">•</span>
                                <span class="text-slate-400">{{ $time }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('admin.forum.toggleHide', $thread->id) }}">
                                @csrf
                                <button type="submit" class="p-2 rounded-xl border border-slate-200 bg-white text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition shadow-sm" title="{{ $isHidden ? 'Make Visible' : 'Hide Post' }}">
                                    @if($isHidden)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                                    @endif
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.forum.delete', $thread->id) }}" onsubmit="return confirm('Permanently delete this post?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-xl border border-rose-100 bg-white text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition shadow-sm" title="Delete Post">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                            
                            <a href="{{ route('admin.forum.show', $thread->id) }}" class="p-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800 transition shadow-sm" title="Review Post">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-200 p-12 text-center text-slate-400">
                    No forum discussions found.
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $threads->links() }}
        </div>
    </section>
</div>
@endsection