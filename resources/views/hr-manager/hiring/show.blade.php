@extends('layouts.app')

@section('title', ($candidate->name ?? 'Candidate') . ' - Hiring Details')
@section('page-title', 'Candidate Details')
@section('page-subtitle', 'Update hiring stage and internal HR remarks only.')

@php
    $statusPalette = [
        'new' => 'bg-slate-100 text-slate-700 border-slate-200',
        'connected' => 'bg-blue-50 text-blue-700 border-blue-200',
        'interview_pending' => 'bg-amber-50 text-amber-700 border-amber-200',
        'interview_complete' => 'bg-violet-50 text-violet-700 border-violet-200',
        'selected' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
    ];
    $statusClass = $statusPalette[$candidate->hiring_status ?? 'new'] ?? $statusPalette['new'];
@endphp

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('hr-manager.hiring.index') }}" class="inline-flex items-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Hiring Leads
        </a>
        <span class="inline-flex rounded-full border px-3 py-1.5 text-sm font-semibold {{ $statusClass }}">
            {{ $candidate->hiring_status_label ?? 'New' }}
        </span>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-xl font-bold text-emerald-800">
                    {{ strtoupper(substr($candidate->name ?? 'C', 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.08em] text-slate-500">Candidate</p>
                    <h2 class="mt-1 text-2xl font-bold text-slate-900">{{ $candidate->name }}</h2>
                    <p class="mt-2 text-sm text-slate-500">Assigned through Facebook hiring automation.</p>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Name</p>
                    <p class="mt-2 text-base font-semibold text-slate-900">{{ $candidate->name }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Phone</p>
                    <p class="mt-2 text-base font-semibold text-slate-900">{{ $candidate->phone }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Lead Source</p>
                    <p class="mt-2 text-base font-semibold text-slate-900">{{ $candidate->source_label ?? \App\Models\Lead::displaySourceLabel($candidate->source) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Facebook Form</p>
                    <p class="mt-2 text-base font-semibold text-slate-900">{{ $candidate->latestFbLead?->form?->name ?: 'No form linked' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Assigned HR</p>
                    <p class="mt-2 text-base font-semibold text-slate-900">{{ $candidate->activeAssignments->first()?->assignedTo?->name ?: 'Unassigned' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Created</p>
                    <p class="mt-2 text-base font-semibold text-slate-900">{{ optional($candidate->created_at)->format('d M Y, h:i A') }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900">Update Hiring Progress</h3>
            <p class="mt-1 text-sm text-slate-500">Name aur phone locked rahenge. HR sirf stage aur remark update karega.</p>

            <form method="POST" action="{{ route('hr-manager.hiring.update', $candidate) }}" class="mt-6 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="hiring_status" class="mb-2 block text-sm font-semibold text-slate-700">Hiring Status</label>
                    <select id="hiring_status" name="hiring_status"
                            class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        @foreach($statusOptions as $key => $label)
                            <option value="{{ $key }}" @selected(old('hiring_status', $candidate->hiring_status ?? 'new') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('hiring_status')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="hr_remark" class="mb-2 block text-sm font-semibold text-slate-700">HR Remark</label>
                    <textarea id="hr_remark" name="hr_remark" rows="8"
                              placeholder="Add candidate progress note, interview update, or rejection reason..."
                              class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">{{ old('hr_remark', $candidate->hr_remark) }}</textarea>
                    @error('hr_remark')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">
                    Save Update
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
