@extends('layouts.faculty')

@section('title', 'Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    {{-- Password Management Section --}}
    <section class="rounded-3xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-2xl bg-green-100 text-green-600 grid place-items-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 00-2 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Password Management</h2>
                    <p class="text-sm text-slate-500">Update your account credentials to maintain high security.</p>
                </div>
            </div>
        </div>

        <div class="p-8">
            @if(session('status'))
                <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-100 p-4 flex items-center gap-3 text-emerald-700 text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('faculty.settings.password') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="current_password" class="block text-xs font-bold text-slate-400 uppercase tracking-widest">Current Password</label>
                        <input type="password" name="current_password" id="current_password" required 
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-green-500 focus:outline-none transition-all">
                        @error('current_password')
                            <p class="text-xs text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="new_password" class="block text-xs font-bold text-slate-400 uppercase tracking-widest">New Password</label>
                        <input type="password" name="new_password" id="new_password" required 
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-green-500 focus:outline-none transition-all">
                        @error('new_password')
                            <p class="text-xs text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="new_password_confirmation" class="block text-xs font-bold text-slate-400 uppercase tracking-widest">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" required 
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-green-500 focus:outline-none transition-all">
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4 border border-slate-100">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Standard Password Requirements:</p>
                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <li class="flex items-center gap-2 text-xs text-slate-600">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Minimum of 8 characters
                        </li>
                        <li class="flex items-center gap-2 text-xs text-slate-600">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            At least one uppercase letter
                        </li>
                        <li class="flex items-center gap-2 text-xs text-slate-600">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            At least one lowercase letter
                        </li>
                        <li class="flex items-center gap-2 text-xs text-slate-600">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            At least one numerical digit
                        </li>
                        <li class="flex items-center gap-2 text-xs text-slate-600">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            At least one special character
                        </li>
                    </ul>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="rounded-2xl bg-green-600 px-8 py-3 text-sm font-bold text-white hover:bg-green-700 transition-all shadow-lg shadow-green-200">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- System Information --}}
    <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-8">
        <h3 class="text-lg font-bold text-slate-900 mb-2">Account Information</h3>
        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Full Name</p>
                <p class="text-sm text-slate-700">{{ auth()->user()->name }}</p>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Email Address</p>
                <p class="text-sm text-slate-700">{{ auth()->user()->email }}</p>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Account Role</p>
                <span class="inline-flex rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-bold uppercase tracking-tight">
                    {{ auth()->user()->role }}
                </span>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Last Login</p>
                <p class="text-sm text-slate-700 font-mono">{{ now()->format('M d, Y h:i A') }}</p>
            </div>
        </div>
    </section>
</div>
@endsection
