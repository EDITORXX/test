@extends('layouts.app')

@section('title', 'HR Hiring Leads')
@section('page-title', 'HR Hiring Leads')
@section('page-subtitle', 'Manage Facebook hiring candidates assigned to you.')

@php
    $statusPalette = [
        'new' => 'bg-slate-100 text-slate-700 border-slate-200',
        'connected' => 'bg-blue-50 text-blue-700 border-blue-200',
        'interview_pending' => 'bg-amber-50 text-amber-700 border-amber-200',
        'interview_complete' => 'bg-violet-50 text-violet-700 border-violet-200',
        'selected' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
    ];
@endphp

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-2 xl:grid-cols-6 gap-4">
        @foreach($statusOptions as $key => $label)
            <a href="{{ route('hr-manager.hiring.index', array_filter(['status' => $key, 'search' => $search])) }}"
               class="rounded-2xl border bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $selectedStatus === $key ? 'border-emerald-500 ring-2 ring-emerald-100' : 'border-slate-200' }}">
                <div class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">{{ $label }}</div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ $counts[$key] ?? 0 }}</div>
            </a>
        @endforeach
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_220px_auto]">
            <div>
                <label for="search" class="mb-2 block text-sm font-semibold text-slate-700">Search candidate</label>
                <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Name or phone"
                       class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            </div>
            <div>
                <label for="status" class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                <select id="status" name="status"
                        class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">All statuses</option>
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" @selected($selectedStatus === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">
                    Filter
                </button>
                <a href="{{ route('hr-manager.hiring.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-lg font-bold text-slate-900">Assigned Candidates</h2>
            <p class="mt-1 text-sm text-slate-500">Only hiring candidates assigned to your HR account are shown here.</p>
        </div>

        @if($candidates->count())
            <div class="hidden lg:block overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Candidate</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Phone</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Source / Form</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Status</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Latest Remark</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Updated</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($candidates as $candidate)
                            @php
                                $statusClass = $statusPalette[$candidate->hiring_status ?? 'new'] ?? $statusPalette['new'];
                                $sourceType = $candidate->source_label ?? \App\Models\Lead::displaySourceLabel($candidate->source);
                                $formName = $candidate->latestFbLead?->form?->name;
                            @endphp
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-900">{{ $candidate->name }}</div>
                                    <div class="mt-1 text-xs text-slate-500">Created {{ optional($candidate->created_at)->format('d M Y, h:i A') }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm font-medium text-slate-800">{{ $candidate->phone }}</td>
                                <td class="px-5 py-4 text-sm text-slate-700">
                                    <div>{{ $sourceType }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $formName ?: 'No form linked' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ $candidate->hiring_status_label ?? 'New' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-700 max-w-xs">
                                    <div class="line-clamp-2">{{ $candidate->hr_remark ?: 'No remark yet' }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-500">{{ optional($candidate->updated_at)->format('d M Y, h:i A') }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('hr-manager.hiring.show', $candidate) }}" class="inline-flex items-center rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800">
                                        Open
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="space-y-4 p-4 lg:hidden">
                @foreach($candidates as $candidate)
                    @php
                        $statusClass = $statusPalette[$candidate->hiring_status ?? 'new'] ?? $statusPalette['new'];
                        $sourceType = $candidate->source_label ?? \App\Models\Lead::displaySourceLabel($candidate->source);
                        $formName = $candidate->latestFbLead?->form?->name;
                    @endphp
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-base font-bold text-slate-900">{{ $candidate->name }}</h3>
                                <p class="mt-1 text-sm text-slate-600">{{ $candidate->phone }}</p>
                            </div>
                            <span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-semibold {{ $statusClass }}">
                                {{ $candidate->hiring_status_label ?? 'New' }}
                            </span>
                        </div>
                        <div class="mt-4 space-y-2 text-sm text-slate-600">
                            <p><span class="font-semibold text-slate-700">Source:</span> {{ $sourceType }}</p>
                            <p><span class="font-semibold text-slate-700">Form:</span> {{ $formName ?: 'No form linked' }}</p>
                            <p><span class="font-semibold text-slate-700">Remark:</span> {{ $candidate->hr_remark ?: 'No remark yet' }}</p>
                        </div>
                        <a href="{{ route('hr-manager.hiring.show', $candidate) }}" class="mt-4 inline-flex items-center rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800">
                            Open Candidate
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-slate-200 px-5 py-4">
                {{ $candidates->links() }}
            </div>
        @else
            <div class="px-5 py-16 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="fas fa-user-clock text-xl"></i>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-slate-900">No hiring candidates found</h3>
                <p class="mt-2 text-sm text-slate-500">Jab hiring Facebook form se lead assign hogi, wo yahan dikh jayegi.</p>
            </div>
        @endif
    </div>
</div>
@endsection
