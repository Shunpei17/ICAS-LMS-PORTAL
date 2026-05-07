@extends('layouts.student')
@section('title', 'Forum')
@section('pageDescription', 'Discuss with classmates and instructors. Ask questions, share insights.')
@section('content')
<div class="space-y-6" x-data="{ newPost: false }">
    {{-- Header --}}
    <section class="rounded-3xl bg-gradient-to-r from-green-500 to-emerald-600 p-6 shadow-md text-white">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold">Student Forum</h2>
                <p class="mt-1 text-green-100 text-sm">Ask questions, share insights, and discuss with your peers and faculty.</p>
            </div>
            <button @click="newPost = !newPost"
                    class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-2.5 text-sm font-bold text-green-700 hover:bg-green-50 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Post
            </button>
        </div>
    </section>

    {{-- New Post Form --}}
    <div x-show="newPost" x-cloak x-transition class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
        <h3 class="text-base font-bold text-slate-900 mb-4">Start a New Discussion</h3>
        <form action="{{ route('student.forum.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Title</label>
                <input type="text" name="title" required placeholder="What's your topic?" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Course / Topic</label>
                    <select name="category" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                        <option value="General">General</option>
                        <option value="Advanced Mathematics">Advanced Mathematics</option>
                        <option value="Physics I">Physics I</option>
                        <option value="World History">World History</option>
                        <option value="Announcements">Announcements</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Post Type</label>
                    <div class="flex items-center gap-4 py-3">
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="radio" name="type" value="discussion" checked class="text-green-600 focus:ring-green-500"> Discussion
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="radio" name="type" value="question" class="text-green-600 focus:ring-green-500"> Question
                        </label>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Message</label>
                <textarea name="content" required rows="4" placeholder="Write your question or discussion post here..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 resize-none"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="rounded-2xl bg-green-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-green-700 transition">Post Discussion</button>
                <button type="button" @click="newPost = false" class="rounded-2xl border border-slate-200 px-6 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Cancel</button>
            </div>
        </form>
    </div>

    @if(session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 shadow-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[1fr_280px]">
        {{-- Threads --}}
        <div class="space-y-4">
            @forelse($threads as $thread)
                <article class="rounded-3xl bg-white border border-slate-100 shadow-sm transition-all hover:border-green-200 hover:shadow-md" x-data="{ open: false }">
                    <div class="p-6">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-3">
                                    <span class="rounded-full bg-slate-100 text-slate-600 px-3 py-1 text-[10px] font-bold uppercase tracking-widest">{{ $thread->category }}</span>
                                    @if($thread->is_flagged)
                                        <span class="rounded-full bg-rose-50 text-rose-600 px-3 py-1 text-[10px] font-bold uppercase tracking-widest">🚩 Reported</span>
                                    @endif
                                </div>
                                <h4 class="text-lg font-bold text-slate-900 mb-2 leading-tight">{{ $thread->title }}</h4>
                                <p class="text-sm text-slate-600 leading-relaxed">{{ $thread->content }}</p>
                                
                                <div class="flex items-center gap-2 mt-4 text-xs text-slate-400">
                                    <div class="h-6 w-6 rounded-full bg-slate-100 grid place-items-center font-bold text-slate-500 text-[10px]">
                                        {{ strtoupper(substr($thread->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <span class="font-bold text-slate-700">{{ $thread->user->name }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span>{{ $thread->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-6 mt-6 pt-4 border-t border-slate-50">
                            <button @click="open = !open" class="flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-green-600 transition group">
                                <div class="p-1.5 rounded-lg bg-slate-50 group-hover:bg-green-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                </div>
                                {{ $thread->replies->count() }} {{ $thread->replies->count() === 1 ? 'Reply' : 'Replies' }}
                            </button>
                            <form action="{{ route('student.forum.report', $thread->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-rose-600 transition group">
                                    <div class="p-1.5 rounded-lg group-hover:bg-rose-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 01-2 2zm9-13.5V9"></path></svg>
                                    </div>
                                    Report
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Replies --}}
                    <div x-show="open" x-cloak x-transition class="border-t border-slate-50 bg-slate-50/50 rounded-b-3xl p-6 space-y-4">
                        @foreach($thread->replies as $reply)
                            <div class="flex gap-4">
                                <div class="h-8 w-8 flex-shrink-0 rounded-full bg-slate-200 text-slate-600 grid place-items-center text-[10px] font-bold">
                                    {{ strtoupper(substr($reply->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="flex-1 rounded-2xl bg-white border border-slate-100 p-4 shadow-sm">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="font-bold text-slate-900 text-xs">{{ $reply->user->name }}</span>
                                        <span class="text-[10px] text-slate-400 font-semibold">{{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm text-slate-600">{{ $reply->content }}</p>
                                </div>
                            </div>
                        @endforeach

                        {{-- Reply compose --}}
                        <div class="flex gap-4 pt-2">
                            <div class="h-8 w-8 flex-shrink-0 rounded-full bg-green-600 text-white grid place-items-center text-[10px] font-bold">
                                {{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 1)) }}
                            </div>
                            <form action="{{ route('student.forum.reply', $thread->id) }}" method="POST" class="flex-1 flex gap-2">
                                @csrf
                                <input type="text" name="content" required placeholder="Write a reply…" class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 shadow-sm transition-all">
                                <button type="submit" class="rounded-2xl bg-slate-900 px-6 py-2.5 text-xs font-bold text-white hover:bg-slate-800 transition shadow-sm">Send</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-200 p-12 text-center text-slate-400">
                    <p class="font-semibold text-slate-500">No discussions yet.</p>
                    <p class="text-sm mt-1">Be the first to start a conversation!</p>
                </div>
            @endforelse

            <div class="mt-8">
                {{ $threads->links() }}
            </div>
        </div>

        {{-- Sidebar --}}
        <aside class="space-y-6">
            <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    Guidelines
                </h3>
                <ul class="space-y-3 text-xs text-slate-500">
                    <li class="flex items-start gap-2 leading-relaxed">Be respectful to all members</li>
                    <li class="flex items-start gap-2 leading-relaxed">Stay on topic and course-relevant</li>
                    <li class="flex items-start gap-2 leading-relaxed">No spam or inappropriate content</li>
                    <li class="flex items-start gap-2 leading-relaxed">Help others when you can</li>
                </ul>
            </section>
            
            <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-bold text-slate-900 mb-4">Active Topics</h3>
                <div class="space-y-3">
                    @forelse($topics as $topic)
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-slate-700">{{ $topic['title'] }}</span>
                            <span class="rounded-full bg-slate-100 text-slate-600 px-2.5 py-1 font-bold">{{ $topic['count'] }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">No active topics found.</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection