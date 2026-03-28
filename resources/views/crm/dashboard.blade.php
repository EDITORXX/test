@extends('layouts.app')

@section('title', 'CRM Workspace - Base CRM')
@section('page-title', 'CRM Workspace')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    .crm-dashboard-shell {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }
    .crm-table-shell .table,
    .crm-table-shell .table * {
        color: inherit;
    }
    .crm-table-shell .table {
        margin-bottom: 0;
        min-width: 640px;
    }
    .crm-table-shell .table thead th {
        background: #f3f7f4;
        color: #667085;
        border-color: #ebf1ed;
        white-space: nowrap;
    }
    .crm-table-shell .table td {
        border-color: #ebf1ed;
        vertical-align: middle;
    }
    .crm-select-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }
    .crm-select-row .form-select,
    .crm-select-row .form-control {
        min-height: 46px;
        border-radius: 16px;
        border: 1px solid #d7e0d9;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.65);
    }
    .crm-muted-panel {
        border-radius: 22px;
        border: 1px solid rgba(6, 58, 28, 0.09);
        background: linear-gradient(180deg, #ffffff, #f8fbf9);
        padding: 20px;
        min-height: 100%;
    }
    .crm-telecaller-grid .telecaller-card {
        margin-bottom: 0;
        border-radius: 24px;
        padding: 20px;
        min-height: 100%;
        background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
        box-shadow: 0 18px 30px rgba(6, 58, 28, 0.18);
    }
    .crm-telecaller-grid .telecaller-card table {
        width: 100%;
    }
    .crm-telecaller-grid .text-muted {
        color: rgba(255,255,255,0.72) !important;
    }
    .crm-danger-zone {
        border: 1px solid rgba(220, 38, 38, 0.18);
        background: linear-gradient(135deg, #fff7f7, #fffdfd);
    }
    @media (max-width: 960px) {
        .crm-table-shell .table {
            min-width: 560px;
        }
    }
</style>
@endpush

@section('content')
<div class="page-shell crm-dashboard-shell">
    <section class="crm-hero">
        <div class="crm-hero-grid">
            <div>
                <span class="crm-kicker">
                    <i class="fas fa-headset"></i>
                    CRM Command Center
                </span>
                <h2 class="crm-hero-title">CRM dashboard for <strong>live lead movement</strong> and response monitoring.</h2>
                <p class="crm-hero-copy">
                    Sales executive performance, pending-response pressure, average callback speed, and lead operations stay exactly as they are now.
                    Sirf presentation ko premium workspace style me upgrade kiya gaya hai.
                </p>
            </div>
            <div class="crm-mini-grid">
                <div class="crm-mini-card">
                    <div class="crm-mini-label">Workspace Mode</div>
                    <div class="crm-mini-value">CRM</div>
                    <div class="crm-mini-copy">Shared admin-style shell with existing CRM APIs.</div>
                </div>
                <div class="crm-mini-card">
                    <div class="crm-mini-label">Sections Live</div>
                    <div class="crm-mini-value">4</div>
                    <div class="crm-mini-copy">Performance, pending response, average response, and lead controls.</div>
                </div>
            </div>
        </div>
    </section>

    <section class="crm-grid-4">
        <article class="crm-stat-card">
            <div class="crm-stat-top">
                <span class="crm-stat-icon"><i class="fas fa-users"></i></span>
                <span class="crm-pill">Performance</span>
            </div>
            <div class="crm-stat-value">Live</div>
            <div class="crm-stat-label">Sales Executive Table</div>
        </article>
        <article class="crm-stat-card">
            <div class="crm-stat-top">
                <span class="crm-stat-icon"><i class="fas fa-phone-slash"></i></span>
                <span class="crm-pill">Queue</span>
            </div>
            <div class="crm-stat-value">Open</div>
            <div class="crm-stat-label">Pending Response</div>
        </article>
        <article class="crm-stat-card">
            <div class="crm-stat-top">
                <span class="crm-stat-icon"><i class="fas fa-stopwatch"></i></span>
                <span class="crm-pill">Speed</span>
            </div>
            <div class="crm-stat-value">Auto</div>
            <div class="crm-stat-label">Average Response</div>
        </article>
        <article class="crm-stat-card">
            <div class="crm-stat-top">
                <span class="crm-stat-icon"><i class="fas fa-shield-alt"></i></span>
                <span class="crm-pill">Ops</span>
            </div>
            <div class="crm-stat-value">Guarded</div>
            <div class="crm-stat-label">Lead Controls</div>
        </article>
    </section>

    <a href="{{ route('lead-assignment.lead-off-users') }}" class="crm-surface" style="text-decoration:none;color:inherit;">
        <div class="crm-surface-header">
            <div>
                <div class="crm-pill">Lead Allocation Control</div>
                <h3 class="crm-section-title">Lead Off Users</h3>
                <p class="crm-section-copy">CRM yahan se kisi user ki new auto lead allocation off ya on kar sakta hai without disturbing existing assigned leads.</p>
            </div>
            <div class="crm-select-row">
                <span class="crm-pill">Click to manage</span>
            </div>
        </div>
        <div class="crm-grid-4" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
            <article class="crm-stat-card">
                <div class="crm-stat-top">
                    <span class="crm-stat-icon"><i class="fas fa-user-slash"></i></span>
                    <span class="crm-pill">Off</span>
                </div>
                <div class="crm-stat-value" id="lead-off-users-count">0</div>
                <div class="crm-stat-label">Lead Off Users</div>
            </article>
            <article class="crm-stat-card">
                <div class="crm-stat-top">
                    <span class="crm-stat-icon"><i class="fas fa-calendar-check"></i></span>
                    <span class="crm-pill">Today</span>
                </div>
                <div class="crm-stat-value" id="lead-off-returning-today">0</div>
                <div class="crm-stat-label">Returning Today</div>
            </article>
            <article class="crm-stat-card">
                <div class="crm-stat-top">
                    <span class="crm-stat-icon"><i class="fas fa-clock"></i></span>
                    <span class="crm-pill">Scheduled</span>
                </div>
                <div class="crm-stat-value" id="lead-off-scheduled-count">0</div>
                <div class="crm-stat-label">Off Until Scheduled</div>
            </article>
        </div>
    </a>

    <div id="notification-alert" class="alert alert-success alert-dismissible fade d-none" role="alert">
        <span id="notification-message"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <section class="crm-surface">
        <div class="crm-surface-header">
            <div>
                <div class="crm-pill">Performance Board</div>
                <h3 class="crm-section-title">Sales Executive Performance</h3>
                <p class="crm-section-copy">Current CRM dashboard metrics remain unchanged. Filters still use the same API endpoints and logic.</p>
            </div>
            <div class="crm-select-row">
                <select id="perf-role-filter" class="form-select form-select-sm" style="width: 180px;" title="User type">
                    <option value="all">All</option>
                </select>
                <select id="perf-date-range" class="form-select form-select-sm" style="width: 160px;" title="Date range">
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month" selected>This Month</option>
                    <option value="this_year">This Year</option>
                    <option value="all_time">All Time</option>
                    <option value="custom">Custom</option>
                </select>
                <span id="perf-custom-date-wrap" class="d-none crm-select-row">
                    <input type="date" id="perf-date-start" class="form-control form-control-sm" style="width: 145px;" title="From">
                    <input type="date" id="perf-date-end" class="form-control form-control-sm" style="width: 145px;" title="To">
                </span>
            </div>
        </div>
        <div id="telecaller-stats-container" class="crm-telecaller-grid">
            <div class="crm-empty">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading sales executive performance...</p>
            </div>
        </div>
    </section>

    <section class="crm-grid-2">
        <div class="crm-surface">
            <div class="crm-surface-header">
                <div>
                    <div class="crm-pill">Lead Queue</div>
                    <h3 class="crm-section-title">Leads Allocated</h3>
                    <p class="crm-section-copy">Pending follow-up visibility with the same expandable row logic and timestamps.</p>
                </div>
                <div class="crm-select-row">
                    <select id="leads-allocated-date-range" class="form-select form-select-sm" style="width: 160px;" title="Date range">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month" selected>This Month</option>
                        <option value="this_year">This Year</option>
                        <option value="all_time">All Time</option>
                        <option value="custom">Custom</option>
                    </select>
                    <span id="leads-allocated-custom-date-wrap" class="d-none crm-select-row">
                        <input type="date" id="leads-allocated-date-start" class="form-control form-control-sm" style="width: 145px;" title="From">
                        <input type="date" id="leads-allocated-date-end" class="form-control form-control-sm" style="width: 145px;" title="To">
                    </span>
                </div>
            </div>
            <div class="crm-table-shell">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 44px;"></th>
                                <th>User Name</th>
                                <th class="text-center">Pending Count</th>
                                <th>Oldest Assign</th>
                            </tr>
                        </thead>
                        <tbody id="leads-pending-response-tbody">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="crm-surface">
            <div class="crm-surface-header">
                <div>
                    <div class="crm-pill">Response Watch</div>
                    <h3 class="crm-section-title">Average Response</h3>
                    <p class="crm-section-copy">Same backend response-time calculations, cleaner presentation.</p>
                </div>
            </div>
            <div class="crm-muted-panel">
                <div id="average-response-time-panel" style="min-height: 120px;">
                    <div class="crm-empty">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Loading average response time...</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="crm-surface crm-danger-zone">
        <div class="crm-surface-header">
            <div>
                <div class="crm-pill" style="background:#fff1f2;color:#b42318;">Danger Zone</div>
                <h3 class="crm-section-title">Delete All Leads</h3>
                <p class="crm-section-copy">System-wide permanent deletion. Existing permission logic and password confirmation remain unchanged.</p>
            </div>
            <div>
                <button type="button" class="btn btn-outline-danger btn-sm" id="btnDeleteAllLeads" data-bs-toggle="modal" data-bs-target="#modalDeleteAllLeads">
                    <i class="fas fa-trash-alt me-2"></i>Delete All Leads
                </button>
            </div>
        </div>
    </section>
</div>

@include('crm.modals.user-management')
@include('crm.modals.transfer-leads')

<div class="modal fade" id="modalDeleteAllLeads" tabindex="-1" aria-labelledby="modalDeleteAllLeadsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px; border:1px solid rgba(220,38,38,0.16);">
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="crm-pill" style="background:#fff1f2;color:#b42318;">Critical Action</div>
                    <h5 class="modal-title mt-3 text-danger" id="modalDeleteAllLeadsLabel">Delete All Leads</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">This will permanently delete all leads. This action cannot be undone.</p>
                <div class="mb-0">
                    <label for="deleteAllLeadsPassword" class="form-label fw-semibold">Password</label>
                    <input type="password" class="form-control crm-form-control" id="deleteAllLeadsPassword" placeholder="Enter password" autocomplete="off">
                    <div id="deleteAllLeadsError" class="invalid-feedback"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDeleteAllLeads">Delete All Leads</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/crm-dashboard.js') }}"></script>
<script>
(function() {
    var deleteAllLeadsUrl = '{{ route("crm.danger.delete-all-leads") }}';
    var csrfToken = document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').content;

    document.getElementById('btnConfirmDeleteAllLeads').addEventListener('click', function() {
        var input = document.getElementById('deleteAllLeadsPassword');
        var errorEl = document.getElementById('deleteAllLeadsError');
        var btn = this;
        var password = (input && input.value) ? input.value.trim() : '';
        if (!password) {
            input.classList.add('is-invalid');
            errorEl.textContent = 'Please enter the password.';
            return;
        }
        input.classList.remove('is-invalid');
        errorEl.textContent = '';
        btn.disabled = true;
        btn.textContent = 'Deleting...';

        fetch(deleteAllLeadsUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ password: password })
        }).then(function(res) {
            return res.json().then(function(data) {
                return { ok: res.ok, status: res.status, data: data };
            }).catch(function() {
                return { ok: res.ok, status: res.status, data: { message: 'Invalid response.' } };
            });
        }).then(function(result) {
            btn.disabled = false;
            btn.textContent = 'Delete All Leads';
            if (result.ok && result.data && result.data.success) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('modalDeleteAllLeads'));
                if (modal) modal.hide();
                input.value = '';
                var msg = result.data.message || 'All leads deleted.';
                if (result.data.deleted_count !== undefined) {
                    msg += ' (' + result.data.deleted_count + ' deleted)';
                }
                var alertEl = document.getElementById('notification-alert');
                if (alertEl) {
                    alertEl.classList.remove('d-none', 'alert-danger');
                    alertEl.classList.add('alert-success', 'show');
                    document.getElementById('notification-message').textContent = msg;
                }
                setTimeout(function() { window.location.reload(); }, 1500);
            } else {
                input.classList.add('is-invalid');
                errorEl.textContent = (result.data && result.data.message) ? result.data.message : 'Invalid password or action not allowed.';
            }
        }).catch(function() {
            btn.disabled = false;
            btn.textContent = 'Delete All Leads';
            input.classList.add('is-invalid');
            errorEl.textContent = 'Request failed. Try again.';
        });
    });

    document.getElementById('modalDeleteAllLeads').addEventListener('show.bs.modal', function() {
        document.getElementById('deleteAllLeadsPassword').value = '';
        document.getElementById('deleteAllLeadsPassword').classList.remove('is-invalid');
        document.getElementById('deleteAllLeadsError').textContent = '';
    });
})();
</script>
@endpush
