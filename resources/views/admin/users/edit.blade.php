@extends('layouts.admin')
@section('title', 'Edit User')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.users.show', $user->id) }}" class="flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to User Details
        </a>
    </div>

    <div class="rounded-3xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 p-6">
            <h2 class="text-xl font-bold text-slate-900">Edit User: {{ $user->name }}</h2>
            <p class="text-sm text-slate-500">Update account information and role-specific details.</p>
        </div>

        <form method="POST" action="{{ route('admin.users.edit', $user->id) }}" class="p-8 space-y-6">
            @csrf
            @method('PATCH')

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-1">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 focus:bg-white focus:border-green-500 focus:ring-1 focus:ring-green-500 transition" required>
                    @error('name') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Account Role</label>
                    <select name="role" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 focus:bg-white focus:border-green-500 focus:ring-1 focus:ring-green-500 transition" required>
                        @foreach(['student', 'faculty', 'admin'] as $role)
                            <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="border-t border-slate-100 pt-6">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Role-Specific Information</h3>
                
                <div class="grid gap-6 md:grid-cols-2">
                    @if($user->role === 'student')
                        <div class="space-y-1">
                            <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Academic Level</label>
                            <select name="academic_level" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 focus:bg-white focus:border-green-500 focus:ring-1 focus:ring-green-500 transition">
                                <option value="">Select level</option>
                                @foreach(['Senior High School','1st Year College','2nd Year College','3rd Year College'] as $lvl)
                                    <option value="{{ $lvl }}" @selected(old('academic_level', $user->academic_level) === $lvl)>{{ $lvl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Course / Program</label>
                            <input type="text" name="course" value="{{ old('course', $user->course) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 focus:bg-white focus:border-green-500 focus:ring-1 focus:ring-green-500 transition">
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Strand (SHS Only)</label>
                            <input type="text" name="strand" value="{{ old('strand', $user->strand) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 focus:bg-white focus:border-green-500 focus:ring-1 focus:ring-green-500 transition">
                        </div>
                    @endif

                    @if($user->role === 'admin')
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Department</label>
                            <input type="text" name="department" value="{{ old('department', $user->department) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 focus:bg-white focus:border-green-500 focus:ring-1 focus:ring-green-500 transition">
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.users.show', $user->id) }}" class="px-6 py-3 text-sm font-bold text-slate-500 hover:text-slate-700 transition">Cancel</a>
                <button type="submit" class="rounded-2xl bg-green-600 px-8 py-3 text-sm font-bold text-white shadow-lg shadow-green-200 hover:bg-green-700 transition">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
