@php($isCrmView = auth()->user()?->isCrm())
@extends('layouts.app')

@section('title', ($isCrmView ? 'Lead Off Users' : 'Sales Executive Status') . ' - Base CRM')
@section('page-title', $isCrmView ? 'Lead Off Users' : 'Sales Executive Status')
@section('page-subtitle', $isCrmView ? 'Control which users should stop receiving new auto-assigned leads' : 'Manage sales executive availability and status')

@section('header-actions')
    <a href="{{ route('lead-assignment.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200 text-sm font-medium">
        Back
    </a>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-sm text-gray-500">{{ $isCrmView ? 'Eligible Users' : 'Visible Users' }}</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">{{ $summary['total_users'] }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-sm text-gray-500">Lead Off</div>
            <div class="text-3xl font-bold text-red-600 mt-2">{{ $summary['lead_off_users'] }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-sm text-gray-500">Lead On</div>
            <div class="text-3xl font-bold text-green-600 mt-2">{{ $summary['lead_on_users'] }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-sm text-gray-500">Returning Today</div>
            <div class="text-3xl font-bold text-amber-600 mt-2">{{ $summary['returning_today'] }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-sm text-gray-500">Scheduled Windows</div>
            <div class="text-3xl font-bold text-indigo-600 mt-2">{{ $summary['scheduled_windows'] ?? 0 }}</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <label for="status-filter" class="text-sm font-medium text-gray-700">Filter</label>
            <select id="status-filter" name="status" class="px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900">
                <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Users</option>
                <option value="off" {{ $statusFilter === 'off' ? 'selected' : '' }}>Lead Off</option>
                <option value="on" {{ $statusFilter === 'on' ? 'selected' : '' }}>Lead On</option>
                <option value="scheduled" {{ $statusFilter === 'scheduled' ? 'selected' : '' }}>Scheduled Window</option>
                <option value="returning_today" {{ $statusFilter === 'returning_today' ? 'selected' : '' }}>Returning Today</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg">Apply</button>
            @if($statusFilter !== 'all')
            <a href="{{ request()->routeIs('lead-assignment.lead-off-users') ? route('lead-assignment.lead-off-users') : route('lead-assignment.telecaller-status') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Allocation Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active Leads</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pending Leads</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lead Off From</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Off Until</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Set By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Can Receive</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $user['name'] }}</div>
                                <div class="text-sm text-gray-500">{{ $user['email'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $user['role'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $user['is_absent'] ? 'bg-red-100 text-red-800' : ($user['has_scheduled_lead_off'] ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800') }}">
                                    {{ $user['is_absent'] ? 'Lead Off' : ($user['has_scheduled_lead_off'] ? 'Scheduled' : 'Lead On') }}
                                </span>
                                @if($user['absent_reason'])
                                    <div class="text-xs text-gray-500 mt-1">{{ $user['absent_reason'] }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user['active_assigned_count'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($user['is_sales_executive'])
                                    {{ $user['pending_count'] }}
                                    @if($user['max_pending_leads'] !== null)
                                        <div class="text-xs text-gray-400 mt-1">Max pending: {{ $user['max_pending_leads'] }}</div>
                                    @endif
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($user['lead_off_start_at'])
                                    {{ \Illuminate\Support\Carbon::parse($user['lead_off_start_at'])->format('d M Y h:i A') }}
                                @elseif($user['is_absent'])
                                    <span class="text-gray-400">Immediate</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($user['lead_off_end_at'])
                                    {{ \Illuminate\Support\Carbon::parse($user['lead_off_end_at'])->format('d M Y h:i A') }}
                                @else
                                    <span class="text-gray-400">Until CRM enables</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($user['lead_off_source'] === 'self')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-sky-100 text-sky-800">Self</span>
                                @elseif($user['lead_off_source'])
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">CRM</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $user['can_receive'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $user['can_receive'] ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($user['is_absent'] || $user['has_scheduled_lead_off'])
                                    <button type="button" onclick="openStatusModal({{ $user['id'] }}, 'on')" class="text-green-600 hover:text-green-800">
                                        Turn On
                                    </button>
                                @else
                                    <div class="flex flex-col gap-2">
                                        <button type="button" onclick="submitDirectStatus({{ $user['id'] }}, true)" class="text-rose-700 hover:text-rose-900 text-left">Lead Off</button>
                                        <button type="button" onclick="openStatusModal({{ $user['id'] }}, 'now')" class="text-red-600 hover:text-red-800 text-left">Turn Off Now</button>
                                        <button type="button" onclick="openStatusModal({{ $user['id'] }}, 'schedule')" class="text-indigo-600 hover:text-indigo-900 text-left">Schedule Window</button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-gray-500">No users matched the selected filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="status-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $isCrmView ? 'Update Lead Allocation' : 'Update User Status' }}</h3>
            <form id="status-form">
                <input type="hidden" id="status-user-id">
                <input type="hidden" id="status-is-absent">
                <input type="hidden" id="status-mode">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ $isCrmView ? 'Allocation Block Reason' : 'Absent Reason' }} (Optional)</label>
                    <textarea id="status-reason" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="{{ $isCrmView ? 'Enter reason for lead off' : 'Enter reason for absence' }}"></textarea>
                </div>

                <div class="mb-4 hidden" id="status-start-wrap">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lead Off From</label>
                    <input type="datetime-local" id="status-start" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <div class="mb-4" id="status-end-wrap">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ $isCrmView ? 'Lead Off Until' : 'Absent Until' }} (Optional)</label>
                    <input type="datetime-local" id="status-until" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <div class="text-xs text-gray-500 mt-2">Blank end time keeps lead allocation off until it is turned on manually.</div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeStatusModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d]">Save</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function formatDateTimeLocal(date) {
            const pad = (value) => String(value).padStart(2, '0');
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
        }

        function openStatusModal(userId, mode) {
            document.getElementById('status-user-id').value = userId;
            document.getElementById('status-is-absent').value = mode === 'on' ? 'false' : 'true';
            document.getElementById('status-mode').value = mode;
            document.getElementById('status-reason').value = '';
            document.getElementById('status-start').value = '';
            document.getElementById('status-until').value = '';

            const startWrap = document.getElementById('status-start-wrap');
            const endWrap = document.getElementById('status-end-wrap');

            if (mode === 'schedule') {
                startWrap.classList.remove('hidden');
                endWrap.classList.remove('hidden');
                document.getElementById('status-start').value = formatDateTimeLocal(new Date());
            } else if (mode === 'now') {
                startWrap.classList.add('hidden');
                endWrap.classList.remove('hidden');
            } else {
                startWrap.classList.add('hidden');
                endWrap.classList.add('hidden');
            }

            document.getElementById('status-modal').classList.remove('hidden');
        }

        function closeStatusModal() {
            document.getElementById('status-modal').classList.add('hidden');
        }

        function extractErrorMessage(error) {
            const responseData = error?.response?.data || {};
            if (responseData.message) {
                return responseData.message;
            }

            const errors = responseData.errors || {};
            const firstField = Object.keys(errors)[0];
            if (firstField && Array.isArray(errors[firstField]) && errors[firstField].length) {
                return errors[firstField][0];
            }

            return 'Failed to update status';
        }

        function submitDirectStatus(userId, isAbsent) {
            axios.post('{{ route("lead-assignment.telecaller-status.update") }}', {
                user_id: userId,
                is_absent: isAbsent,
                mode: isAbsent ? 'normal' : 'on',
                absent_reason: null,
                lead_off_start_at: null,
                lead_off_end_at: null,
                absent_until: null
            })
            .then(response => {
                alert(response.data.message);
                window.location.reload();
            })
            .catch(error => {
                alert('Error: ' + extractErrorMessage(error));
            });
        }

        document.getElementById('status-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const userId = document.getElementById('status-user-id').value;
            const isAbsent = document.getElementById('status-is-absent').value === 'true';
            const mode = document.getElementById('status-mode').value;
            const reason = document.getElementById('status-reason').value;
            const start = document.getElementById('status-start').value;
            const until = document.getElementById('status-until').value;

            axios.post('{{ route("lead-assignment.telecaller-status.update") }}', {
                user_id: userId,
                is_absent: isAbsent,
                mode: mode,
                absent_reason: reason || null,
                lead_off_start_at: isAbsent && mode === 'schedule' ? (start || null) : null,
                lead_off_end_at: isAbsent && mode === 'schedule' ? (until || null) : null,
                absent_until: isAbsent && mode !== 'schedule' ? (until || null) : null
            })
            .then(response => {
                alert(response.data.message);
                window.location.reload();
            })
            .catch(error => {
                alert('Error: ' + extractErrorMessage(error));
            });
        });
    </script>
    @endpush
@endsection
