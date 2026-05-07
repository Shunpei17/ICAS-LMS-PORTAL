@extends('layouts.student')
@section('title', 'Document Requests')
@section('pageDescription', 'Submit and track your official document requests.')
@section('content')
<div class="space-y-6" x-data="{ showModal: false }">

    {{-- Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition>
        <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl p-8" @click.outside="showModal = false">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-slate-900">New Document Request</h3>
                <button @click="showModal = false" class="rounded-xl p-1.5 hover:bg-slate-100 transition text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form action="{{ route('student.documents.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Document Type <span class="text-rose-500">*</span></label>
                    <select name="document_type" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                        <option value="">Select document type…</option>
                        @foreach(['Gradeslip', 'Tor', 'Scholastic', 'Diploma Copy', 'Good moral', 'Honorable dismissal', 'Documents letter Request', 'OJT Recommendation Letter', 'Certificate of Enrollment', 'Certificate of Graduation'] as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Purpose <span class="text-rose-500">*</span></label>
                    <input type="text" name="purpose" required placeholder="e.g. Scholarship Application, College Admission…" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Urgency</label>
                    <div class="flex gap-3">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="urgency" value="Standard" class="sr-only peer" checked>
                            <div class="rounded-2xl border-2 border-slate-200 peer-checked:border-green-500 peer-checked:bg-green-50 p-3 text-center text-sm font-semibold text-slate-600 peer-checked:text-green-700 transition">
                                Standard<br><span class="text-xs font-normal">3–5 business days</span>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="urgency" value="Rush" class="sr-only peer">
                            <div class="rounded-2xl border-2 border-slate-200 peer-checked:border-amber-500 peer-checked:bg-amber-50 p-3 text-center text-sm font-semibold text-slate-600 peer-checked:text-amber-700 transition">
                                Rush<br><span class="text-xs font-normal">1–2 business days</span>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 rounded-2xl bg-green-600 py-3 text-sm font-bold text-white hover:bg-green-700 transition">Submit Request</button>
                    <button type="button" @click="showModal = false" class="rounded-2xl border border-slate-200 px-6 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Header --}}
    <section class="rounded-3xl bg-gradient-to-r from-green-500 to-emerald-600 p-6 shadow-md text-white">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold">Document Requests</h2>
                <p class="mt-1 text-green-100 text-sm">Submit requests for official school documents and track their status.</p>
            </div>
            <button @click="showModal = true" class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-2.5 text-sm font-bold text-green-700 hover:bg-green-50 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Request
            </button>
        </div>
    </section>

    @if(session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="grid gap-4 sm:grid-cols-4">
        @foreach($summary as $s)
            <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-5 text-center">
                <p class="text-xs uppercase tracking-widest font-semibold text-slate-500">{{ $s['label'] }}</p>
                <p class="mt-3 text-4xl font-black text-slate-900">{{ $s['value'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Request History --}}
    <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-5">Request History</h3>
        <div class="space-y-4">
            @forelse($requests as $req)
                @php
                    $st = $req['status'];
                    $badge = match($st) {'Completed'=>'bg-emerald-100 text-emerald-700','Processing'=>'bg-sky-100 text-sky-700','Pending'=>'bg-amber-100 text-amber-700', 'Rejected' => 'bg-rose-100 text-rose-700', default=>'bg-slate-100 text-slate-600'};
                    $steps = ['Pending','Processing','Completed'];
                    $stepIdx = array_search($st, $steps) !== false ? array_search($st,$steps) : ($st === 'Rejected' ? -1 : 0);
                @endphp
                <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h4 class="font-bold text-slate-900">{{ $req['title'] }}</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Purpose: {{ $req['purpose'] }} &nbsp;·&nbsp; {{ $req['urgency'] }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">Requested: {{ $req['requested'] }}</p>
                        </div>
                        <span class="inline-flex rounded-full {{ $badge }} px-3 py-1 text-xs font-bold flex-shrink-0">{{ $st }}</span>
                    </div>

                    @if($st !== 'Rejected')
                        {{-- Progress steps --}}
                        <div class="mt-4 flex items-center gap-0">
                            @foreach($steps as $i => $step)
                                <div class="flex items-center {{ $i < count($steps)-1 ? 'flex-1' : '' }}">
                                    <div class="h-6 w-6 rounded-full flex-shrink-0 grid place-items-center text-xs font-bold {{ $i <= $stepIdx ? 'bg-green-600 text-white' : 'bg-slate-200 text-slate-400' }}">{{ $i+1 }}</div>
                                    <span class="ml-1.5 text-xs {{ $i <= $stepIdx ? 'text-slate-800 font-semibold' : 'text-slate-400' }}">{{ $step }}</span>
                                    @if($i < count($steps)-1)
                                        <div class="flex-1 mx-2 h-0.5 {{ $i < $stepIdx ? 'bg-green-500' : 'bg-slate-200' }}"></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($req['note'])
                        <div class="mt-3 rounded-2xl bg-white border border-slate-200 px-4 py-3 text-xs text-slate-600 flex items-center gap-2">
                            @if($st === 'Completed')
                                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            @endif
                            {{ $req['note'] }}
                        </div>
                    @endif
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center text-slate-500">
                    You haven't made any document requests yet.
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection