@extends('layouts.admin')
@section('title', 'User Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.users') }}" class="flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to User Management
        </a>
        <div class="flex gap-3">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="rounded-2xl bg-amber-100 px-5 py-2.5 text-sm font-bold text-amber-700 hover:bg-amber-200 transition">Edit User</a>
            <form method="POST" action="{{ route('admin.users.delete', $user->id) }}" onsubmit="return confirm('Permanently delete this user?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-2xl bg-rose-50 px-5 py-2.5 text-sm font-bold text-rose-600 hover:bg-rose-100 transition">Delete Account</button>
            </form>
        </div>
    </div>

    <div class="rounded-3xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        {{-- Header Section --}}
        <div class="bg-slate-50 border-b border-slate-200 p-8">
            <div class="flex items-center gap-6">
                <div class="h-20 w-20 rounded-3xl bg-green-600 text-white grid place-items-center text-3xl font-black shadow-lg shadow-green-200">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-900">{{ $user->name }}</h2>
                    <div class="flex items-center gap-3 mt-2">
                        @php 
                            $rb = match($user->role) { 'admin' => 'bg-violet-100 text-violet-700', 'faculty' => 'bg-sky-100 text-sky-700', default => 'bg-slate-100 text-slate-600' };
                            $sb = match($user->status) { 'active' => 'bg-emerald-100 text-emerald-700', 'pending' => 'bg-amber-100 text-amber-700', default => 'bg-rose-100 text-rose-700' };
                        @endphp
                        <span class="inline-flex rounded-full {{ $rb }} px-3 py-1 text-xs font-bold uppercase tracking-widest">{{ $user->role }}</span>
                        <span class="inline-flex rounded-full {{ $sb }} px-3 py-1 text-xs font-bold uppercase tracking-widest">{{ $user->status }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-8">
            <div class="grid gap-8 md:grid-cols-2">
                {{-- Common Info --}}
                <div class="space-y-6">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Account Information</h3>
                    
                    <div class="grid gap-4">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Email Address</p>
                            <p class="text-sm font-bold text-slate-900">{{ $user->email }}</p>
                        </div>

                        @if($user->role === 'admin')
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Admin Unique Number</p>
                                <p class="text-sm font-bold text-slate-900">{{ $user->admin_number ?? 'N/A' }}</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Department</p>
                                <p class="text-sm font-bold text-slate-900">{{ $user->department ?? 'N/A' }}</p>
                            </div>
                        @endif

                        @if($user->role === 'student')
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Student Number</p>
                                <p class="text-sm font-bold text-slate-900">{{ $user->student_number ?? 'N/A' }}</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Academic Level</p>
                                <p class="text-sm font-bold text-slate-900">{{ $user->academic_level ?? 'N/A' }}</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Course</p>
                                <p class="text-sm font-bold text-slate-900">{{ $user->course ?? 'N/A' }}</p>
                            </div>
                            @if($user->academic_level === 'Senior High School')
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Strand</p>
                                    <p class="text-sm font-bold text-slate-900">{{ $user->strand ?? 'N/A' }}</p>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Verification Documents (Only for Manual Students) --}}
                @if($user->role === 'student' && ($user->receipt_proof || $user->student_id_proof))
                    <div class="space-y-6">
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Verification Documents</h3>
                        
                        <div class="grid gap-4">
                            @if($user->receipt_proof)
                                <div class="p-5 rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">Receipt / Enrollment Proof</p>
                                    <a href="{{ asset('storage/' . $user->receipt_proof) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-green-50 px-4 py-2 text-xs font-bold text-green-700 hover:bg-green-100 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        View Document
                                    </a>
                                </div>
                            @endif

                            @if($user->student_id_proof)
                                <div class="p-5 rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">Student ID Proof</p>
                                    <a href="{{ asset('storage/' . $user->student_id_proof) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-sky-50 px-4 py-2 text-xs font-bold text-sky-700 hover:bg-sky-100 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        View ID Card
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif($user->role === 'student')
                    <div class="flex flex-col items-center justify-center p-8 rounded-3xl border border-dashed border-slate-200 text-slate-400">
                        <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <p class="text-sm font-semibold">CSV Imported Account</p>
                        <p class="text-xs mt-1 italic text-center">No physical documents available for this system-generated account.</p>
                    </div>
                @endif
            </div>

            <div class="mt-12 pt-8 border-t border-slate-100 flex items-center justify-between">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Account Created: {{ $user->created_at->format('M d, Y @ h:i A') }}
                </div>
                
                <form method="POST" action="{{ route('admin.users.activate', $user->id) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $user->status === 'active' ? 'inactive' : 'active' }}">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-2xl {{ $user->status === 'active' ? 'bg-rose-600' : 'bg-green-600' }} px-6 py-3 text-sm font-bold text-white transition hover:opacity-90 shadow-lg">
                        {{ $user->status === 'active' ? 'Deactivate Account' : 'Activate Account' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
