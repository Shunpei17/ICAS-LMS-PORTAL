@extends('layouts.admin')
@section('title', 'System Backups')
@section('pageDescription', 'Manage database backups and system health.')

@section('content')
<div class="space-y-6" x-data="{ confirmRestore: false }">

    {{-- Alerts --}}
    @if(session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">✅ {{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">⚠️ {{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">{{ $errors->first() }}</div>
    @endif

    {{-- System Health Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Database Size</p>
            <p class="text-3xl font-extrabold text-slate-900">{{ $dbSizeMb }} <span class="text-base font-semibold text-slate-400">MB</span></p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Backup Storage Used</p>
            <p class="text-3xl font-extrabold text-slate-900">{{ $storageUsedMb }} <span class="text-base font-semibold text-slate-400">MB</span></p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Last Backup</p>
            <p class="text-lg font-bold text-slate-900">{{ $lastBackup ? $lastBackup->created_at->format('M j, Y g:i A') : 'Never' }}</p>
            @if($lastBackup)<p class="text-xs text-slate-400 mt-1">by {{ $lastBackup->initiated_by }}</p>@endif
        </div>
    </div>

    {{-- Health Checks --}}
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900 mb-4">System Health</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @foreach($healthChecks as $check)
            <div class="flex items-center gap-3 rounded-2xl border {{ $check['ok'] ? 'border-emerald-100 bg-emerald-50' : 'border-rose-100 bg-rose-50' }} p-4">
                <div class="h-8 w-8 rounded-xl {{ $check['ok'] ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center shrink-0">
                    @if($check['ok'])
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    @endif
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-800">{{ $check['label'] }}</p>
                    <p class="text-xs text-slate-500">{{ $check['detail'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <div class="grid grid-cols-1 gap-6">
        {{-- Backup Now + Schedule --}}
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">
            <div class="flex items-center gap-3 mb-1">
                <div class="h-10 w-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Database Backup</h2>
                    <p class="text-xs text-slate-500">Full SQL dump — includes all tables and data.</p>
                </div>
            </div>

            {{-- One-click backup --}}
            <form method="POST" action="{{ route('admin.maintenance.backup') }}">
                @csrf
                <button type="submit" class="w-full rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-700 shadow-sm transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Generate & Download Backup Now
                </button>
            </form>

            {{-- Schedule --}}
            <form method="POST" action="{{ route('admin.maintenance.schedule') }}" class="border-t border-slate-100 pt-5">
                @csrf
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Backup Schedule</label>
                <div class="flex gap-3">
                    <select name="backup_schedule" class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-400">
                        <option value="none" @selected($scheduledBackup === 'none')>None (Manual Only)</option>
                        <option value="daily" @selected($scheduledBackup === 'daily')>Daily</option>
                        <option value="weekly" @selected($scheduledBackup === 'weekly')>Weekly</option>
                    </select>
                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-700 transition">Save</button>
                </div>
                <p class="text-xs text-slate-400 mt-2">
                    @if($scheduledBackup === 'daily') ⏰ Run <code>php artisan backup:run</code> daily in your scheduler (Task Scheduler / cron).
                    @elseif($scheduledBackup === 'weekly') ⏰ Run <code>php artisan backup:run</code> weekly in your scheduler.
                    @else Schedule is currently off.
                    @endif
                </p>
            </form>
        </section>
    </div>

    {{-- Data Restore --}}
    <section class="rounded-3xl border border-rose-200 bg-rose-50/40 p-6 shadow-sm">
        <div class="flex flex-wrap items-start gap-4">
            <div class="h-10 w-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            </div>
            <div class="flex-1">
                <h2 class="text-lg font-bold text-slate-900">Database Restore</h2>
                <p class="text-xs text-rose-700 font-semibold mt-0.5 mb-4">⚠ <strong>Destructive operation</strong> — this will overwrite the current database with the backup. This action cannot be undone.</p>
                <div x-show="!confirmRestore">
                    <button type="button" @click="confirmRestore = true" class="rounded-xl border-2 border-rose-400 bg-white px-5 py-2.5 text-sm font-bold text-rose-600 hover:bg-rose-50 transition">
                        Restore from Backup File…
                    </button>
                </div>
                <form x-show="confirmRestore" method="POST" action="{{ route('admin.maintenance.restore') }}" enctype="multipart/form-data" x-cloak>
                    @csrf
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Upload .sql backup file</label>
                        <input type="file" name="backup_file" accept=".sql,.txt" required
                            class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm focus:border-rose-400 focus:outline-none">
                        <div class="flex gap-3">
                            <button type="submit" class="rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-rose-700 transition" onclick="return confirm('Are you absolutely sure? This will OVERWRITE the live database!')">
                                Confirm Restore
                            </button>
                            <button type="button" @click="confirmRestore = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    {{-- Backup Files List --}}
    @if(!empty($backupFiles))
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900 mb-4">Stored Backup Files</h2>
        <div class="space-y-2">
            @foreach($backupFiles as $file)
            <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 group hover:border-indigo-200 hover:bg-indigo-50/30 transition">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path></svg>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ $file['filename'] }}</p>
                        <p class="text-xs text-slate-400">{{ $file['modified'] }} &bull; {{ $file['size'] }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.maintenance.backup.download', ['filename' => $file['filename']]) }}" class="inline-flex items-center gap-1 rounded-xl bg-white border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download
                    </a>
                    <form method="POST" action="{{ route('admin.maintenance.backup.delete') }}" onsubmit="return confirm('Delete this backup file?')">
                        @csrf @method('DELETE')
                        <input type="hidden" name="filename" value="{{ $file['filename'] }}">
                        <button type="submit" class="inline-flex items-center rounded-xl bg-white border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-500 hover:bg-rose-50 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Backup History Table --}}
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900 mb-4">Backup History</h2>
        @if($backupHistory->isEmpty())
            <p class="text-sm text-slate-400 text-center py-6">No backup history yet. Click "Generate Backup" to start.</p>
        @else
        <div class="overflow-x-auto rounded-2xl border border-slate-100">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Filename</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Size</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Initiated By</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($backupHistory as $log)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 font-mono text-xs text-slate-700 max-w-[220px] truncate">{{ $log->filename }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ round($log->size_bytes / 1024, 1) }} KB</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase
                                {{ $log->type === 'manual' ? 'bg-indigo-100 text-indigo-700' : ($log->type === 'restore' ? 'bg-rose-100 text-rose-700' : 'bg-sky-100 text-sky-700') }}">
                                {{ $log->type }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $log->initiated_by }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $log->status === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ $log->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </section>
</div>
@endsection
