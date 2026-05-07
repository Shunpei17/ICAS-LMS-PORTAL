@extends('layouts.admin')

@section('title', 'My Profile')
@section('pageDescription', 'View and manage your administrator profile information.')

@section('content')
    <div class="space-y-6" x-data="adminProfile()">
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

        {{-- Profile Header Card --}}
        <section class="rounded-3xl bg-gradient-to-r from-green-500 to-emerald-600 p-8 shadow-md text-white relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDM0djZoNnYtNmgtNnptMC0xMHY2aDZ2LTZoLTZ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-40"></div>
            <div class="relative flex flex-wrap items-center gap-6">
                {{-- Avatar --}}
                <div class="relative group">
                    @if($admin->profile_photo)
                        <img src="{{ asset('storage/' . $admin->profile_photo) }}" alt="Profile Photo" class="h-24 w-24 rounded-3xl object-cover border-4 border-white/30 shadow-lg">
                    @else
                        @php
                            $initials = collect(explode(' ', trim($admin->name)))->map(fn($s) => strtoupper(substr($s, 0, 1)))->join('');
                        @endphp
                        <div class="h-24 w-24 rounded-3xl bg-white/20 border-4 border-white/30 shadow-lg grid place-items-center text-3xl font-bold text-white">
                            {{ $initials }}
                        </div>
                    @endif
                    <button @click="$refs.photoInput.click()" class="absolute -bottom-1 -right-1 h-8 w-8 rounded-full bg-white text-green-600 shadow-md grid place-items-center hover:bg-green-50 transition" title="Change photo">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </button>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">{{ $admin->title ? $admin->title . ' ' : '' }}{{ $admin->name }}</h1>
                    <p class="text-green-100 text-sm mt-1">{{ $admin->email }}</p>
                    <div class="flex flex-wrap items-center gap-2 mt-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/20 px-3 py-1 text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span> Administrator
                        </span>
                        @if($admin->designation)
                            <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">{{ $admin->designation }}</span>
                        @endif
                        @if($admin->department)
                            <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">{{ $admin->department }}</span>
                        @endif
                    </div>
                </div>
            </div>
            {{-- Hidden photo upload form --}}
            <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" id="photoForm" class="hidden">
                @csrf
                <input type="file" x-ref="photoInput" name="profile_photo" accept="image/*" @change="document.getElementById('photoForm').submit()">
            </form>
        </section>

        {{-- Profile Fields Grid --}}
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Left Column: Account Info (Auto-filled, Read-only) --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-5">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-green-50 text-green-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </span>
                    <h2 class="text-lg font-bold text-slate-900">Account Information</h2>
                    <span class="ml-auto rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Auto-filled</span>
                </div>
                <div class="space-y-4">
                    <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Full Name</p>
                        <p class="text-sm font-bold text-slate-900">{{ $admin->name }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Email Address</p>
                        <p class="text-sm font-bold text-slate-900">{{ $admin->email }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Admin Unique Number</p>
                        <p class="text-sm font-bold text-slate-900">{{ $admin->admin_number ?: 'N/A' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Role</p>
                        <p class="text-sm font-bold text-slate-900 capitalize">{{ $admin->role }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Account Created</p>
                        <p class="text-sm font-bold text-slate-900">{{ $admin->created_at?->format('F j, Y') ?? 'N/A' }}</p>
                    </div>
                </div>
            </section>

            {{-- Right Column: Editable Profile Fields --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-5">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </span>
                    <h2 class="text-lg font-bold text-slate-900">Profile Details</h2>
                    <span class="ml-auto rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-600 uppercase tracking-wider">Editable</span>
                </div>

                <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4" id="profileForm">
                    @csrf

                    {{-- Title --}}
                    @php $fields = [
                        ['key'=>'title','label'=>'Title','type'=>'select','options'=>[''=>'— Select —','Dr.'=>'Dr.','Mr.'=>'Mr.','Ms.'=>'Ms.','Mrs.'=>'Mrs.','Prof.'=>'Prof.','Engr.'=>'Engr.']],
                        ['key'=>'designation','label'=>'Official Designation','type'=>'text','placeholder'=>'e.g. Director, Registrar'],
                        ['key'=>'department','label'=>'Department','type'=>'text','placeholder'=>'e.g. Office of the Registrar'],
                        ['key'=>'office_hours','label'=>'Office Hours','type'=>'text','placeholder'=>'e.g. Mon–Fri, 8:00 AM – 5:00 PM'],
                        ['key'=>'gender','label'=>'Gender','type'=>'select','options'=>[''=>'— Select —','Male'=>'Male','Female'=>'Female','Other'=>'Other','Prefer not to say'=>'Prefer not to say']],
                        ['key'=>'address','label'=>'Address','type'=>'textarea','placeholder'=>'Complete mailing address'],
                    ]; @endphp

                    @foreach($fields as $f)
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 group hover:border-green-300 hover:bg-green-50/30 transition-all" x-data="{ editing: false }">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ $f['label'] }}</p>
                                <button type="button" @click="editing = !editing" class="inline-flex items-center gap-1 text-xs font-semibold transition" :class="editing ? 'text-emerald-600' : 'text-slate-400 hover:text-green-600'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    <span x-text="editing ? 'Done' : 'Edit'"></span>
                                </button>
                            </div>

                            {{-- Display value --}}
                            <p x-show="!editing" class="text-sm font-bold text-slate-900" x-cloak>
                                {{ $admin->{$f['key']} ?: '—' }}
                            </p>

                            {{-- Edit input --}}
                            <div x-show="editing" x-cloak>
                                @if($f['type'] === 'select')
                                    <select name="{{ $f['key'] }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                                        @foreach($f['options'] as $val => $lbl)
                                            <option value="{{ $val }}" @selected(old($f['key'], $admin->{$f['key']}) === $val)>{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                @elseif($f['type'] === 'textarea')
                                    <textarea name="{{ $f['key'] }}" rows="2" placeholder="{{ $f['placeholder'] ?? '' }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old($f['key'], $admin->{$f['key']}) }}</textarea>
                                @else
                                    <input type="text" name="{{ $f['key'] }}" value="{{ old($f['key'], $admin->{$f['key']}) }}" placeholder="{{ $f['placeholder'] ?? '' }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <button type="submit" class="w-full rounded-2xl bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700 shadow-sm">
                        Save Profile Changes
                    </button>
                </form>
            </section>
        </div>

        {{-- Announcements Created Section --}}
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-violet-50 text-violet-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19a1 1 0 001.447.894L18 17h2a1 1 0 001-1V8a1 1 0 00-1-1h-2l-5.553-2.894A1 1 0 0011 5.882zM7 10v4m-3-3v2a1 1 0 001 1h2V10H5a1 1 0 00-1 1z"></path></svg>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Announcements Created</h2>
                        <p class="text-xs text-slate-500">Only your personal announcements are shown here.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-bold text-violet-700">{{ $myAnnouncements->count() }} total</span>
                    <a href="{{ route('admin.announcements.index') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                        View All Announcements →
                    </a>
                </div>
            </div>

            <div class="space-y-3">
                @forelse($myAnnouncements as $announcement)
                    @php
                        $audienceLabels = ['all' => 'All', 'faculty' => 'Faculty', 'student' => 'Students'];
                        $audienceColors = ['all' => 'bg-emerald-100 text-emerald-700', 'faculty' => 'bg-amber-100 text-amber-700', 'student' => 'bg-violet-100 text-violet-700'];
                    @endphp
                    <article class="rounded-2xl border border-slate-100 bg-slate-50 p-5 hover:border-green-300 hover:bg-green-50/20 transition-all group">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-green-700 transition-colors truncate">{{ $announcement->title }}</h3>
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $audienceColors[$announcement->audience] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $audienceLabels[$announcement->audience] ?? 'All' }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">{{ $announcement->created_at?->format('M j, Y \a\t g:i A') }}</p>
                                <p class="mt-2 text-sm text-slate-600 line-clamp-2">{{ Str::limit($announcement->content, 150) }}</p>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if($announcement->attachment_path)
                                    <a href="{{ asset('storage/' . $announcement->attachment_path) }}" target="_blank" class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-sky-200 bg-sky-50 text-sky-600 hover:bg-sky-100 transition" title="View Attachment">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    </a>
                                @endif
                                <a href="{{ route('admin.announcements.index', ['edit' => $announcement->id]) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-green-300">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    Manage
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <article class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 p-8 text-center">
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19a1 1 0 001.447.894L18 17h2a1 1 0 001-1V8a1 1 0 00-1-1h-2l-5.553-2.894A1 1 0 0011 5.882zM7 10v4"></path></svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-700">No announcements yet</p>
                        <p class="mt-1 text-xs text-slate-500">Announcements you create will appear here.</p>
                        <a href="{{ route('admin.announcements.index') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-2xl bg-green-600 px-4 py-2 text-xs font-semibold text-white hover:bg-green-700 transition">
                            Create Your First Announcement →
                        </a>
                    </article>
                @endforelse
            </div>
        </section>
    </div>

    <script>
        function adminProfile() {
            return {};
        }
    </script>
@endsection
