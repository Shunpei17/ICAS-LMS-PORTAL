@extends('layouts.admin')

@section('title', 'Create Classroom')
@section('pageDescription', 'Set up a new classroom and assign a faculty member.')

@section('content')
    <div class="max-w-2xl space-y-6">
        @if($errors->any())
            <div class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    @foreach($errors->all() as $error)
                        <p class="text-sm font-medium text-rose-800">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.classrooms') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Back
            </a>
            <h2 class="text-xl font-bold text-slate-900">Create New Classroom</h2>
        </div>

        <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-8">
            <form method="POST" action="{{ route('admin.classrooms.store') }}">
                @csrf

                <div class="space-y-6">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Classroom Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="name" name="name"
                               value="{{ old('name') }}"
                               placeholder="e.g. Advanced Mathematics — Section A"
                               required
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition">
                    </div>

                    {{-- Code --}}
                    <div>
                        <label for="code" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Classroom Code <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="code" name="code"
                               value="{{ old('code') }}"
                               placeholder="e.g. MATH301"
                               maxlength="20"
                               required
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-mono text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition">
                        <p class="mt-1.5 text-xs text-slate-400">Unique identifier used to link attendance and grade records. Max 20 characters.</p>
                    </div>

                    {{-- Schedule --}}
                    <div>
                        <label for="schedule" class="block text-sm font-semibold text-slate-700 mb-1.5">Schedule</label>
                        <input type="text" id="schedule" name="schedule"
                               value="{{ old('schedule') }}"
                               placeholder="e.g. Mon, Wed, Fri 9:00 AM"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition">
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-semibold text-slate-700 mb-1.5">Description</label>
                        <textarea id="description" name="description" rows="4"
                                  placeholder="Brief description of this classroom, topics covered, requirements, etc."
                                  class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition resize-none">{{ old('description') }}</textarea>
                    </div>

                    {{-- Faculty Assignment --}}
                    <div>
                        <label for="faculty_user_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Assign Faculty</label>
                        <select id="faculty_user_id" name="faculty_user_id"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition">
                            <option value="">Unassigned</option>
                            @foreach($faculties as $faculty)
                                <option value="{{ $faculty->id }}" {{ old('faculty_user_id') == $faculty->id ? 'selected' : '' }}>
                                    {{ $faculty->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-3">Status <span class="text-rose-500">*</span></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="active"
                                       {{ old('status', 'active') === 'active' ? 'checked' : '' }}
                                       class="accent-indigo-600 w-4 h-4">
                                <span class="text-sm font-medium text-slate-700">Active</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="inactive"
                                       {{ old('status') === 'inactive' ? 'checked' : '' }}
                                       class="accent-slate-500 w-4 h-4">
                                <span class="text-sm font-medium text-slate-700">Inactive</span>
                            </label>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="flex gap-3 pt-2">
                        <button type="submit"
                                class="rounded-2xl bg-indigo-600 px-8 py-3 text-sm font-bold text-white hover:bg-indigo-700 transition shadow-sm">
                            Create Classroom
                        </button>
                        <a href="{{ route('admin.classrooms') }}"
                           class="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </section>
    </div>
@endsection
