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
                <input type="text" name="search" value="{{ $search }}" placeholder="Search student…" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm w-44 focus:outline-none focus:ring-2 focus:ring-green-400" onkeydown="if(event.key === 'Enter') this.form.submit()">
                <select name="type" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option @selected($type === 'Transcript of Records')>Transcript of Records</option>
                    <option @selected($type === 'Certificate of Enrollment')>Certificate of Enrollment</option>
                    <option @selected($type === 'Certificate of Good Standing')>Certificate of Good Standing</option>
                    <option @selected($type === 'Diploma Copy')>Diploma Copy</option>
                    <option @selected($type === 'Form 137')>Form 137</option>
                </select>
                <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option @selected($status === 'Pending')>Pending</option>
                    <option @selected($status === 'Processing')>Processing</option>
                    <option @selected($status === 'Completed')>Completed</option>
                    <option @selected($status === 'Rejected')>Rejected</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200" data-live-key="admin.documents.requests">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Student</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Document</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Purpose</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Date</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Urgency</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide text-center">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($requests as $r)
                        @php
                            $badge = match($r['status']){'Completed'=>'bg-emerald-100 text-emerald-700','Processing'=>'bg-sky-100 text-sky-700','Pending'=>'bg-amber-100 text-amber-700',default=>'bg-rose-100 text-rose-700'};
                            $urgBadge = $r['urgency']==='Rush' ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-600';
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 flex-shrink-0 rounded-full bg-green-600 grid place-items-center text-white text-xs font-bold">{{ strtoupper(substr($r['student'],0,1)) }}</div>
                                    <span class="font-semibold text-slate-900">{{ $r['student'] }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700">{{ $r['doc'] }}</td>
                            <td class="px-5 py-3.5 text-slate-500">{{ $r['purpose'] }}</td>
                            <td class="px-5 py-3.5 text-slate-500 whitespace-nowrap">{{ $r['date'] }}</td>
                            <td class="px-5 py-3.5"><span class="inline-flex rounded-full {{ $urgBadge }} px-2.5 py-0.5 text-xs font-semibold">{{ $r['urgency'] }}</span></td>
                            <td class="px-5 py-3.5 text-center"><span class="inline-flex rounded-full {{ $badge }} px-3 py-1 text-xs font-bold">{{ $r['status'] }}</span></td>
                            <td class="px-5 py-3.5">
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-500">No document requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection