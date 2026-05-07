@extends('layouts.student')

@section('title', 'Classrooms')
@section('pageDescription', 'Browse available classrooms and manage your enrolled courses.')

@section('content')
    <div class="space-y-6">
        @if(session('status'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-sm font-medium text-emerald-800">{{ session('status') }}</p>
            </div>
        @endif
        @if($errors->any())
            <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-sm font-medium text-rose-800">{{ $errors->first() }}</p>
            </div>
        @endif

        {{-- Header --}}
        <section class="rounded-3xl bg-gradient-to-r from-green-500 to-emerald-600 p-6 shadow-md text-white">
            <h2 class="text-2xl font-bold">Classrooms</h2>
            <p class="mt-1 text-green-100 text-sm">
                You are enrolled in <strong>{{ count($myClassrooms) }}</strong> classroom{{ count($myClassrooms) !== 1 ? 's' : '' }}.
                <strong>{{ count($openClassrooms) }}</strong> more available to join.
            </p>
        </section>

        {{-- My Classrooms --}}
        @if(count($myClassrooms) > 0)
            <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-1">My Classrooms</h3>
                <p class="text-sm text-slate-500 mb-5">Classrooms you have joined this term.</p>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($myClassrooms as $room)
                        @php
                            $statusBadge = match($room['enrollment_status'] ?? 'pending') {
                                'enrolled' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Enrolled'],
                                'dropped'  => ['bg' => 'bg-rose-100',    'text' => 'text-rose-700',    'label' => 'Dropped'],
                                default    => ['bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'label' => 'Pending'],
                            };
                        @endphp
                        <article class="rounded-3xl border border-emerald-200 bg-emerald-50/40 p-5 flex flex-col">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="h-10 w-10 rounded-2xl bg-green-600 text-white grid place-items-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                </div>
                                <span class="inline-flex rounded-full {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }} px-3 py-1 text-xs font-bold flex-shrink-0">
                                    {{ $statusBadge['label'] }}
                                </span>
                            </div>

                            <h4 class="text-base font-bold text-slate-900">{{ $room['name'] }}</h4>
                            <p class="text-xs font-mono text-slate-500 mt-0.5">{{ $room['code'] }}</p>

                            <div class="mt-3 space-y-1 text-xs text-slate-600">
                                <p><span class="font-semibold text-slate-800">Instructor:</span> {{ $room['faculty_name'] }}</p>
                                @if($room['schedule'])
                                    <p><span class="font-semibold text-slate-800">Schedule:</span> {{ $room['schedule'] }}</p>
                                @endif
                                @if($room['section'])
                                    <p><span class="font-semibold text-slate-800">Section:</span>
                                        <span class="rounded-md bg-sky-100 text-sky-700 px-1.5 py-0.5 font-semibold">{{ $room['section'] }}</span>
                                    </p>
                                @endif
                            </div>

                            @if($room['grade'] !== null)
                                <div class="mt-4 rounded-2xl bg-white border border-slate-200 p-3 text-center">
                                    <p class="text-2xl font-black text-emerald-600">{{ $room['grade'] }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Current Grade</p>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Available Classrooms --}}
        <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-1">Available Classrooms</h3>
            <p class="text-sm text-slate-500 mb-5">Active classrooms open for enrollment. Click <strong>Join</strong> to enroll.</p>

            @if(count($openClassrooms) > 0)
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($openClassrooms as $room)
                        <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5 hover:border-green-300 hover:bg-green-50/30 transition-all flex flex-col">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="h-10 w-10 rounded-2xl bg-slate-200 text-slate-500 grid place-items-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1v1H9V7zm5 0h1v1h-1V7zm-5 4h1v1H9v-1zm5 0h1v1h-1v-1z"></path></svg>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"></path></svg>
                                    {{ $room['student_count'] }}
                                </span>
                            </div>

                            <h4 class="text-base font-bold text-slate-900">{{ $room['name'] }}</h4>
                            <p class="text-xs font-mono text-slate-500 mt-0.5">{{ $room['code'] }}</p>

                            @if($room['description'])
                                <p class="mt-2 text-xs text-slate-500 line-clamp-2">{{ $room['description'] }}</p>
                            @endif

                            <div class="mt-3 space-y-1 text-xs text-slate-600">
                                <p><span class="font-semibold text-slate-800">Instructor:</span> {{ $room['faculty_name'] }}</p>
                                @if($room['schedule'])
                                    <p><span class="font-semibold text-slate-800">Schedule:</span> {{ $room['schedule'] }}</p>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('student.classrooms.enroll', $room['id']) }}" class="mt-auto pt-4">
                                @csrf
                                @if($isEnrollmentPeriod)
                                    <button type="submit"
                                            class="w-full rounded-2xl bg-green-600 px-4 py-2 text-sm font-bold text-white hover:bg-green-700 transition flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        Join Classroom
                                    </button>
                                @else
                                    <button type="button" disabled
                                            class="w-full rounded-2xl bg-slate-200 px-4 py-2 text-sm font-bold text-slate-400 cursor-not-allowed flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        Enrollment Closed
                                    </button>
                                @endif
                            </form>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center">
                    <svg class="mx-auto w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-sm font-medium text-slate-700">You have joined all available classrooms!</p>
                    <p class="mt-1 text-xs text-slate-400">Check back later when new classrooms are opened.</p>
                </div>
            @endif
        </section>
    </div>
@endsection