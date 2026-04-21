@extends('layouts.admin')

@section('title', 'Grades')
@section('pageDescription', 'Review grade summaries across courses.')

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-slate-400">Grade Generator</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Generate Admin Grade Report</h2>
                    <p class="mt-2 text-sm text-slate-500">Download the latest grade records as a CSV whenever needed.</p>
                </div>

                <a href="{{ route('admin.grades.export') }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Generate and Download CSV
                </a>
            </div>
        </div>

        @if(count($grades) > 0)
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($grades as $grade)
                    <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                        <p class="text-sm uppercase tracking-[0.3em] text-slate-400">{{ $grade['course'] }}</p>
                        <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $grade['average'] }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $grade['status'] }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center">
                <p class="text-base font-semibold text-slate-700">No grade data available yet.</p>
                <p class="mt-2 text-sm text-slate-500">Grade summaries will appear here once students have module grade records.</p>
            </div>
        @endif
        </div>
    </div>
@endsection