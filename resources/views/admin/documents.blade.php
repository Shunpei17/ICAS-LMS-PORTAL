@extends('layouts.admin')
@section('title', 'Document Requests')
@section('pageDescription', 'Review and process student document requests.')
@section('content')
<div class="space-y-6">
    {{-- Summary --}}
    <div class="grid gap-4 sm:grid-cols-4">
        @foreach($summary as $s)
            @php $c = match($s['color'] ?? 'slate'){
                'amber'=>['bg-amber-50','border-amber-200','text-amber-700'],'sky'=>['bg-sky-50','border-sky-200','text-sky-700'],'emerald'=>['bg-emerald-50','border-emerald-200','text-emerald-700'],default=>['bg-white','border-slate-200','text-slate-900']
            }; @endphp
            <div class="rounded-3xl {{ $c[0] }} border {{ $c[1] }} shadow-sm p-6">
                <p class="text-xs uppercase tracking-widest font-semibold text-slate-500">{{ $s['label'] }}</p>
                <p class="mt-3 text-4xl font-black {{ $c[2] }}">{{ $s['value'] }}</p>
            </div>
        @endforeach
    </div>

    <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">All Document Requests</h2>
                <p class="text-sm text-slate-500 mt-1">Review requests and update their status. Students will be notified.</p>
            </div>
            <form action="{{ route('admin.documents') }}" method="GET" class="flex flex-wrap gap-3">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search student…" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm w-44 focus:outline-none focus:ring-2 focus:ring-green-400">
                <select name="type" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    <option value="">All Types</option>
                    @foreach(['Gradeslip', 'Tor', 'Scholastic', 'Diploma Copy', 'Good moral', 'Honorable dismissal', 'Documents letter Request', 'OJT Recommendation Letter', 'Certificate of Enrollment', 'Certificate of Graduation'] as $t)
                        <option value="{{ $t }}" @selected($type === $t)>{{ $t }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    <option value="">All Statuses</option>
                    <option @selected($status === 'Pending')>Pending</option>
                    <option @selected($status === 'Processing')>Processing</option>
                    <option @selected($status === 'Completed')>Completed</option>
                    <option @selected($status === 'Rejected')>Rejected</option>
                </select>
                <button type="submit" class="rounded-xl bg-slate-900 px-6 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition">Filter</button>
                @if($search || $type || $status)
                    <a href="{{ route('admin.documents') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200" data-live-key="admin.documents.requests">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Student</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Document</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Purpose</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide text-center">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($requests as $r)
                        @php
                            $badge = match($r['status']){'Completed'=>'bg-emerald-100 text-emerald-700','Processing'=>'bg-sky-100 text-sky-700','Pending'=>'bg-amber-100 text-amber-700',default=>'bg-rose-100 text-rose-700'};
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 flex-shrink-0 rounded-full bg-green-600 grid place-items-center text-white text-xs font-bold">{{ strtoupper(substr($r['student'],0,1)) }}</div>
                                    <span class="font-semibold text-slate-900">{{ $r['student'] }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-slate-700">{{ $r['doc'] }}</td>
                            <td class="px-5 py-4">
                                <p class="text-slate-900 font-medium">{{ $r['purpose'] }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-wider">{{ $r['date'] }} · {{ $r['urgency'] }}</p>
                            </td>
                            <td class="px-5 py-4 text-center"><span class="inline-flex rounded-full {{ $badge }} px-3 py-1 text-xs font-bold">{{ $r['status'] }}</span></td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <form action="{{ route('admin.documents.update', $r['id']) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-200 bg-white px-2 py-1.5 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-green-400">
                                            <option value="Pending" @selected($r['status']==='Pending')>Pending</option>
                                            <option value="Processing" @selected($r['status']==='Processing')>Processing</option>
                                            <option value="Completed" @selected($r['status']==='Completed')>Completed</option>
                                            <option value="Rejected" @selected($r['status']==='Rejected')>Rejected</option>
                                        </select>
                                    </form>

                                    @if($r['status'] === 'Rejected')
                                        <form action="{{ route('admin.documents.delete', $r['id']) }}" method="POST" class="inline" onsubmit="return confirm('Permanently delete this rejected request?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Delete record">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-500">No document requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection