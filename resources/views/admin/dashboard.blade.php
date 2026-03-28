@extends('layouts.app')

@section('title', 'Admin Dashboard - Base CRM')
@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'Welcome, ' . (auth()->user()->name ?? 'Admin') . ' (Admin)')

@section('header-actions')
<div style="display:flex;gap:10px;align-items:center;">
    <a href="{{ route('admin.verifications') }}"
        style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:linear-gradient(135deg,#1d4ed8,#2563eb);color:#fff;border:none;border-radius:9px;font-size:13.5px;font-weight:600;cursor:pointer;text-decoration:none;">
        <i class="fas fa-check-circle"></i> Verifications
    </a>
    <div class="dropdown" style="position:relative;">
        <button onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='block'?'none':'block'"
            style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:linear-gradient(135deg,#063A1C,#205A44);color:#fff;border:none;border-radius:9px;font-size:13.5px;font-weight:600;cursor:pointer;">
            <i class="fas fa-download"></i> Export
            <i class="fas fa-chevron-down" style="font-size:10px;"></i>
        </button>
        <div style="display:none;position:absolute;right:0;top:calc(100% + 6px);background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);padding:6px;min-width:200px;z-index:1000;">
            <a href="{{ route('export.index') }}" style="display:flex;align-items:center;gap:9px;padding:9px 12px;color:#374151;text-decoration:none;border-radius:7px;font-size:13px;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                <i class="fas fa-sliders-h" style="color:#205A44;width:14px;"></i> Custom Export
            </a>
            <form action="{{ route('export.prospects') }}" method="POST" style="margin:0;">@csrf<input type="hidden" name="format" value="csv">
                <button type="submit" style="width:100%;display:flex;align-items:center;gap:9px;padding:9px 12px;background:none;border:none;color:#374151;cursor:pointer;border-radius:7px;font-size:13px;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                    <i class="fas fa-user-check" style="color:#205A44;width:14px;"></i> Export Prospects
                </button>
            </form>
            <form action="{{ route('export.meetings') }}" method="POST" style="margin:0;">@csrf<input type="hidden" name="format" value="csv">
                <button type="submit" style="width:100%;display:flex;align-items:center;gap:9px;padding:9px 12px;background:none;border:none;color:#374151;cursor:pointer;border-radius:7px;font-size:13px;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                    <i class="fas fa-calendar-check" style="color:#205A44;width:14px;"></i> Export Meetings
                </button>
            </form>
            <form action="{{ route('export.site-visits') }}" method="POST" style="margin:0;">@csrf<input type="hidden" name="format" value="csv">
                <button type="submit" style="width:100%;display:flex;align-items:center;gap:9px;padding:9px 12px;background:none;border:none;color:#374151;cursor:pointer;border-radius:7px;font-size:13px;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                    <i class="fas fa-map-marker-alt" style="color:#205A44;width:14px;"></i> Export Site Visits
                </button>
            </form>
            <form action="{{ route('export.closed-leads') }}" method="POST" style="margin:0;">@csrf<input type="hidden" name="format" value="csv">
                <button type="submit" style="width:100%;display:flex;align-items:center;gap:9px;padding:9px 12px;background:none;border:none;color:#374151;cursor:pointer;border-radius:7px;font-size:13px;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                    <i class="fas fa-check-circle" style="color:#205A44;width:14px;"></i> Export Closed
                </button>
            </form>
            <form action="{{ route('export.dead-leads') }}" method="POST" style="margin:0;">@csrf<input type="hidden" name="format" value="csv">
                <button type="submit" style="width:100%;display:flex;align-items:center;gap:9px;padding:9px 12px;background:none;border:none;color:#374151;cursor:pointer;border-radius:7px;font-size:13px;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                    <i class="fas fa-times-circle" style="color:#ef4444;width:14px;"></i> Export Dead
                </button>
            </form>
        </div>
    </div>
</div>
<script>document.addEventListener('click',function(e){document.querySelectorAll('.dropdown [style*="display:block"]').forEach(function(m){if(!m.parentElement.contains(e.target))m.style.display='none';});});</script>
@endsection

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap');
.admin-dashboard-root { font-family: 'Outfit', 'Inter', sans-serif; color: #16161A; }
.admin-dashboard-shell { display: flex; flex-direction: column; gap: 20px; }
.admin-hero { position: relative; overflow: hidden; border: 1px solid #e2e1dc; border-radius: 20px; background: radial-gradient(circle at top right, rgba(93, 202, 165, 0.18), transparent 34%), radial-gradient(circle at left bottom, rgba(23, 97, 168, 0.10), transparent 26%), linear-gradient(135deg, #ffffff, #fafaf8 55%, #f0efec); padding: 22px 24px; box-shadow: 0 10px 30px rgba(0,0,0,.04); }
.admin-hero-grid { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr); gap: 20px; align-items: center; }
.admin-kicker { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 999px; background: #e4f4ee; color: #0b6b4f; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
.admin-hero-title { margin-top: 14px; font-family: 'Fraunces', serif; font-size: 34px; line-height: 1.05; font-weight: 700; color: #16161A; }
.admin-hero-title span { color: #0b6b4f; }
.admin-hero-copy { margin-top: 10px; max-width: 720px; font-size: 14px; line-height: 1.65; color: #5e5e5a; }
.admin-hero-meta { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
.hero-meta-card { border: 1px solid #e2e1dc; border-radius: 14px; background: rgba(255,255,255,.8); padding: 14px 16px; }
.hero-meta-value { font-size: 26px; line-height: 1; font-weight: 800; color: #0b6b4f; }
.hero-meta-label { margin-top: 6px; font-size: 11px; color: #7a7a73; text-transform: uppercase; letter-spacing: .06em; font-weight: 700; }
.dashboard-mode-toggle { display: inline-flex; gap: 6px; padding: 6px; border-radius: 999px; border: 1px solid #e2e1dc; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.03); }
.dashboard-mode-btn { border: none; border-radius: 999px; background: transparent; color: #7a7a73; font-size: 13px; font-weight: 700; cursor: pointer; padding: 10px 18px; transition: all .2s ease; }
.dashboard-mode-btn.is-active.sale { background: linear-gradient(135deg, #0b6b4f, #084d3a); color: #fff; box-shadow: 0 8px 20px rgba(11,107,79,.18); }
.dashboard-mode-btn.is-active.marketing { background: linear-gradient(135deg, #5946c0, #7a67de); color: #fff; box-shadow: 0 8px 20px rgba(89,70,192,.18); }
.dashboard-panel { display: none; animation: fadeUp .24s ease; }
.dashboard-panel.is-active { display: block; }
.premium-filter-bar { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
.premium-filter-label { font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #7a7a73; }
.premium-grid-2, .premium-grid-3, .premium-grid-4 { display: grid; gap: 18px; }
.premium-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.premium-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.premium-grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
.marketing-stat { border: 1px solid #e2e1dc; border-radius: 16px; background: #fff; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,.03); }
.marketing-stat .value { font-size: 30px; font-weight: 800; line-height: 1; color: #16161A; }
.marketing-stat .label { margin-top: 6px; font-size: 11px; color: #7a7a73; text-transform: uppercase; letter-spacing: .08em; font-weight: 700; }
.marketing-stat .sub { margin-top: 8px; font-size: 12px; color: #5e5e5a; }
.marketing-list { display: flex; flex-direction: column; gap: 10px; }
.marketing-list-item { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 14px; border: 1px solid #edece7; border-radius: 12px; background: #fafaf8; }
.marketing-list-item strong { font-size: 13px; color: #16161A; }
.marketing-list-item span { font-size: 12px; color: #5e5e5a; }
.marketing-score-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.marketing-score-box { border: 1px solid #eceaf8; border-radius: 14px; background: linear-gradient(135deg, #ffffff, #f8f7ff); padding: 16px; }
.marketing-score-box .score { font-size: 28px; line-height: 1; font-weight: 800; color: #5946c0; }
.marketing-score-box .name { margin-top: 6px; font-size: 11px; letter-spacing: .08em; text-transform: uppercase; color: #7a7a73; font-weight: 700; }
.marketing-score-box .note { margin-top: 8px; font-size: 12px; color: #5e5e5a; }
.shortcut-grid { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:14px; }
.shortcut-card { display:flex; align-items:center; gap:12px; border:1px solid #e7e5df; border-radius:16px; background:#fff; padding:16px; text-decoration:none; color:#16161A; box-shadow:0 1px 3px rgba(0,0,0,.03); transition:transform .18s ease, box-shadow .18s ease; }
.shortcut-card:hover { transform:translateY(-2px); box-shadow:0 10px 24px rgba(0,0,0,.06); }
.shortcut-icon { width:38px; height:38px; border-radius:12px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#eef6f2,#dff3ea); color:#0b6b4f; }
.shortcut-label { font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:#7a7a73; font-weight:700; }
.shortcut-value { margin-top:3px; font-size:22px; font-weight:800; color:#16161A; line-height:1; }
.score-card-grid { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:12px; }
.score-card { text-align:center; padding:16px; border:1px solid #eceaf8; border-radius:14px; background:linear-gradient(135deg,#fff,#f9f8ff); }
.score-card .value { font-size:28px; line-height:1; font-weight:800; color:#5946c0; }
.score-card .label { margin-top:6px; font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:#7a7a73; font-weight:700; }
.score-card .note { margin-top:8px; font-size:12px; color:#5e5e5a; }
.funnel-stack { display:flex; flex-direction:column; gap:12px; }
.funnel-row { display:flex; align-items:center; gap:12px; width:100%; }
.funnel-row-link {
    display:flex;
    width:100%;
    flex:1 1 auto;
    align-items:center;
    gap:12px;
    text-decoration:none;
    color:inherit;
    border-radius:14px;
    padding:8px 10px;
    margin:0 -8px;
    transition:background .18s ease, transform .18s ease;
}
.funnel-row-link:hover {
    background:#f8faf8;
    transform:translateX(2px);
}
.funnel-label { width:92px; flex-shrink:0; text-align:right; font-size:12px; color:#5e5e5a; font-weight:600; }
.funnel-bar-wrap { flex:1 1 auto; min-width:180px; height:34px; display:flex; align-items:stretch; background:#f1f0ec; border-radius:999px; overflow:hidden; }
.funnel-bar { min-width:52px; height:34px; display:flex; align-items:center; padding:0 12px; border-radius:999px; color:#fff; font-size:12px; font-weight:700; white-space:nowrap; }
.funnel-meta { width:56px; flex-shrink:0; font-size:12px; color:#7a7a73; text-align:right; }
.progress-list { display:flex; flex-direction:column; gap:12px; }
.progress-item-head { display:flex; justify-content:space-between; gap:10px; margin-bottom:5px; font-size:12px; }
.progress-item-head strong { color:#16161A; }
.progress-item-head span { color:#5e5e5a; font-weight:600; }
.progress-track { height:7px; border-radius:999px; background:#f1f0ec; overflow:hidden; }
.progress-fill { height:100%; border-radius:999px; }
.metric-table td small { color:#7a7a73; font-size:11px; }
.table-avatar { width:26px; height:26px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; color:#fff; background:linear-gradient(135deg,#1761a8,#3b82f6); margin-right:8px; }
.metric-modal { position:fixed; inset:0; background:rgba(0,0,0,.48); display:none; align-items:center; justify-content:center; z-index:1200; padding:20px; }
.metric-modal.is-open { display:flex; }
.metric-modal-card { width:min(900px, 100%); max-height:86vh; overflow:auto; background:#fff; border-radius:20px; box-shadow:0 20px 60px rgba(0,0,0,.2); padding:22px 24px; }
.metric-modal-header { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:18px; }
.metric-modal-close { border:none; background:#f3f4f6; color:#374151; width:36px; height:36px; border-radius:10px; cursor:pointer; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
/* ── Stat Cards ─────────────────────────────── */
.stat-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    padding: 20px 22px;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
    transition: transform .2s, box-shadow .2s;
    display: flex;
    align-items: center;
    gap: 16px;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.1); }
.stat-icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.stat-icon i { font-size: 20px; color: #fff; }
.stat-value { font-size: 30px; font-weight: 700; color: #111827; line-height: 1; }
.stat-label { font-size: 12px; color: #6b7280; font-weight: 500; margin-top: 4px; }

/* ── Section Cards ──────────────────────────── */
.section-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    padding: 22px 24px;
    margin-bottom: 22px;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.section-title {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 18px;
    padding-bottom: 14px;
    border-bottom: 1.5px solid #f3f4f6;
    display: flex;
    align-items: center;
    gap: 9px;
}
.section-title-icon {
    width: 28px; height: 28px; border-radius: 7px;
    display: inline-flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg,#063A1C,#205A44);
    flex-shrink: 0;
}
.section-title-icon i { color: #fff; font-size: 12px; }

/* ── Mini stat box ──────────────────────────── */
.mini-stat {
    background: #f9fafb; border-radius: 10px; padding: 14px 16px;
    border: 1px solid #f3f4f6;
}
.mini-stat-val { font-size: 22px; font-weight: 700; color: #063A1C; line-height: 1; }
.mini-stat-lbl { font-size: 11px; color: #6b7280; margin-top: 4px; }

/* ── Date filter pills ──────────────────────── */
.date-filter-btn {
    padding: 7px 16px; border: 1.5px solid #e5e7eb; background: #fff;
    border-radius: 20px; font-size: 12.5px; font-weight: 600; color: #374151;
    cursor: pointer; transition: all .2s; white-space: nowrap;
}
.date-filter-btn:hover { border-color: #205A44; color: #205A44; }
.date-filter-btn.active { background: linear-gradient(135deg,#063A1C,#205A44); color: #fff; border-color: transparent; box-shadow: 0 2px 8px rgba(6,58,28,.25); }

/* ── VM filter pills ────────────────────────── */
.visits-meetings-filter-btn {
    padding: 6px 14px; border: 1.5px solid #e5e7eb; background: #fff;
    border-radius: 20px; font-size: 12px; font-weight: 600; color: #374151;
    cursor: pointer; transition: all .2s;
}
.visits-meetings-filter-btn:hover { border-color: #205A44; color: #205A44; }
.visits-meetings-filter-btn.active { background: linear-gradient(135deg,#063A1C,#205A44); color: #fff; border-color: transparent; }

/* ── Tables ─────────────────────────────────── */
table { width: 100%; border-collapse: collapse; }
th, td { padding: 11px 14px; text-align: left; border-bottom: 1px solid #f3f4f6; }
th { background: #f9fafb; font-weight: 600; color: #374151; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; }
td { color: #374151; font-size: 13.5px; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: #fafafa; }

/* ── Badges ─────────────────────────────────── */
.badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-new { background: #dbeafe; color: #1e40af; }
.badge-connected { background: #bfdbfe; color: #1e3a8a; }
.badge-verified-prospect { background: #e9d5ff; color: #6b21a8; }
.badge-meeting-scheduled { background: #ddd6fe; color: #5b21b6; }
.badge-meeting-completed { background: #cffafe; color: #155e75; }
.badge-visit-scheduled { background: #ede9fe; color: #5b21b6; }
.badge-visit-done { background: #fce7f3; color: #9f1239; }
.badge-revisited-scheduled { background: #fce7f3; color: #9f1239; }
.badge-revisited-completed { background: #fecdd3; color: #881337; }
.badge-closed { background: #d1fae5; color: #065f46; }
.badge-dead { background: #fee2e2; color: #991b1b; }
.badge-on-hold { background: #f3f4f6; color: #374151; }
.badge-contacted { background: #fef3c7; color: #92400e; }
.badge-default { background: #f3f4f6; color: #6b7280; }
.badge-qualified { background: #e9d5ff; color: #6b21a8; }

/* ── Chart container ────────────────────────── */
.chart-container { position: relative; height: 280px; }

/* ── Call stats filter ──────────────────────── */
.call-stats-filter-btn {
    padding: 5px 12px; border: 1.5px solid #e5e7eb; background: #fff;
    border-radius: 6px; font-size: 12px; font-weight: 600; color: #374151; cursor: pointer; transition: all .2s;
}
.call-stats-filter-btn.active { background: linear-gradient(135deg,#063A1C,#205A44); color: #fff; border-color: transparent; }

/* ── quick-action-btn kept for JS compat ──── */
.quick-action-btn { display: none; }
</style>
@endpush

@section('content')
<div id="dashboard-content" class="admin-dashboard-root">
    <div class="admin-dashboard-shell">

    {{-- ── Loading State ──────────────────────────────────── --}}
    <div id="loading" style="text-align:center;padding:60px 20px;">
        <div style="width:44px;height:44px;border:3px solid #e5e7eb;border-top-color:#205A44;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 14px;"></div>
        <p style="color:#9ca3af;font-size:14px;">Loading dashboard...</p>
    </div>
    <style>@keyframes spin{to{transform:rotate(360deg)}}</style>

    <section class="admin-hero">
        <div class="admin-hero-grid">
            <div>
                <span class="admin-kicker"><i class="fas fa-chart-pie"></i> Command Center</span>
                <div class="admin-hero-title">Admin dashboard built for <span>sales control</span> and marketing visibility.</div>
                <p class="admin-hero-copy">Live operational sections stay intact, but the layout is now organized for faster scanning across inflow, pipeline movement, team output, and recovery actions.</p>
                <div class="premium-filter-bar" style="margin-top:18px;">
                    <div>
                        <div class="premium-filter-label">Dashboard Mode</div>
                        <div class="dashboard-mode-toggle" style="margin-top:8px;">
                            <button type="button" class="dashboard-mode-btn sale is-active" id="dashboard-mode-sale" onclick="switchDashboardMode('sale')">Sale</button>
                            <button type="button" class="dashboard-mode-btn marketing" id="dashboard-mode-marketing" onclick="switchDashboardMode('marketing')">Marketing</button>
                        </div>
                    </div>
                    <div>
                        <div class="premium-filter-label">Reporting Window</div>
                        <div style="margin-top:8px;font-size:14px;font-weight:600;color:#16161A;" id="hero-date-range-label">This month</div>
                    </div>
                </div>
            </div>
            <div class="admin-hero-meta">
                <div class="hero-meta-card">
                    <div class="hero-meta-value" id="hero-total-leads">0</div>
                    <div class="hero-meta-label">Leads In Window</div>
                </div>
                <div class="hero-meta-card">
                    <div class="hero-meta-value" id="hero-connection-rate">0%</div>
                    <div class="hero-meta-label">Call Connection Rate</div>
                </div>
                <div class="hero-meta-card">
                    <div class="hero-meta-value" id="hero-imported-leads">0</div>
                    <div class="hero-meta-label">Imported Leads</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Date Filter ────────────────────────────────────── --}}
    <div class="section-card" style="margin-bottom:20px;">
        <div class="premium-filter-bar">
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <span class="premium-filter-label" style="white-space:nowrap;">Period</span>
                <button onclick="applyDateFilter('today')"   id="filter-today"  class="date-filter-btn">Today</button>
                <button onclick="applyDateFilter('week')"    id="filter-week"   class="date-filter-btn">This Week</button>
                <button onclick="applyDateFilter('month')"   id="filter-month"  class="date-filter-btn active">This Month</button>
                <button onclick="applyDateFilter('year')"    id="filter-year"   class="date-filter-btn">This Year</button>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-left:auto;">
                <span style="font-size:12px;color:#6b7280;font-weight:500;">Custom:</span>
                <input type="date" id="custom-start-date" onchange="applyCustomDateFilter()"
                    style="padding:6px 10px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:12.5px;color:#374151;outline:none;background:#f9fafb;">
                <span style="font-size:12px;color:#9ca3af;">→</span>
                <input type="date" id="custom-end-date" onchange="applyCustomDateFilter()"
                    style="padding:6px 10px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:12.5px;color:#374151;outline:none;background:#f9fafb;">
            </div>
        </div>
    </div>

    <div id="sale-dashboard-panel" class="dashboard-panel is-active">
    <div id="dashboard-shortcuts" class="shortcut-grid" style="margin-bottom:22px;">
        <a class="shortcut-card" href="#">
            <span class="shortcut-icon"><i class="fas fa-layer-group"></i></span>
            <span><div class="shortcut-label">Dashboard</div><div class="shortcut-value">0</div></span>
        </a>
    </div>

    {{-- ── KPI Stats Row ───────────────────────────────────── --}}
    <div id="main-stats" style="display:none;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:22px;" class="grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#063A1C,#205A44);"><i class="fas fa-users"></i></div>
            <div><div class="stat-value" id="total-leads">0</div><div class="stat-label">Total Leads</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#1d4ed8,#3b82f6);"><i class="fas fa-map-marker-alt"></i></div>
            <div><div class="stat-value" id="total-visits">0</div><div class="stat-label">Site Visits</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#7c3aed,#a78bfa);"><i class="fas fa-calendar-check"></i></div>
            <div><div class="stat-value" id="total-meetings">0</div><div class="stat-label">Meetings</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#065f46,#10b981);"><i class="fas fa-handshake"></i></div>
            <div><div class="stat-value" id="total-closers">0</div><div class="stat-label">Closed</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#991b1b,#ef4444);"><i class="fas fa-times-circle"></i></div>
            <div><div class="stat-value" id="total-dead">0</div><div class="stat-label">Dead</div></div>
        </div>
    </div>

    {{-- ── Lead Statistics + Property Segments ──────────────── --}}
    <div class="premium-grid-2" style="margin-bottom:22px;">
        <div class="section-card" style="margin-bottom:0;">
            <div class="section-title">
                <span class="section-title-icon"><i class="fas fa-gauge-high"></i></span>
                Performance Scores
            </div>
            <div id="performance-scores" class="score-card-grid">
                <div class="score-card"><div class="value">0%</div><div class="label">PS Score</div><div class="note">Loading...</div></div>
            </div>
        </div>
        <div class="section-card" style="margin-bottom:0;">
            <div class="section-title">
                <span class="section-title-icon"><i class="fas fa-filter"></i></span>
                Pipeline Funnel
            </div>
            <div id="pipeline-funnel" class="funnel-stack">
                <div class="funnel-row"><div class="funnel-label">Leads</div><div class="funnel-bar-wrap"><div class="funnel-bar" style="width:40px;background:#0b6b4f;">0</div></div><div class="funnel-meta">0%</div></div>
            </div>
        </div>
    </div>

    <div class="premium-grid-2" style="margin-bottom:22px;">
        <div class="section-card" style="margin-bottom:0;">
            <div class="section-title">
                <span class="section-title-icon"><i class="fas fa-bullseye"></i></span>
                Team Targets
            </div>
            <div id="team-targets-summary" class="progress-list">
                <div class="progress-item-head"><strong>Meetings</strong><span>0 / 0</span></div>
                <div class="progress-track"><div class="progress-fill" style="width:0%;background:#1761a8;"></div></div>
            </div>
            <button type="button" onclick="openTargetsModal()" class="date-filter-btn" style="margin-top:16px;">View Per User Breakdown</button>
        </div>
        <div class="section-card" id="incentives-summary-section" style="margin-bottom:0;">
            <div class="section-title">
                <span class="section-title-icon"><i class="fas fa-indian-rupee-sign"></i></span>
                Incentives
            </div>
            <div id="incentive-summary" class="score-card-grid">
                <div class="score-card"><div class="value">0</div><div class="label">Total</div><div class="note">Loading...</div></div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-title">
            <span class="section-title-icon"><i class="fas fa-ranking-star"></i></span>
            Sales Team - PS / PP / VP Scores
        </div>
        <div id="sales-score-table" style="overflow-x:auto;">
            <p style="color:#9ca3af;font-size:13px;">Loading...</p>
        </div>
    </div>

    <div class="section-card">
        <div class="section-title">
            <span class="section-title-icon"><i class="fas fa-users-viewfinder"></i></span>
            User View - Meetings, Visits & Closures
        </div>
        <div id="user-pipeline-table" style="overflow-x:auto;">
            <p style="color:#9ca3af;font-size:13px;">Loading...</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:22px;" class="grid-responsive-2">
        <div class="section-card" style="margin-bottom:0;">
            <div class="section-title">
                <span class="section-title-icon"><i class="fas fa-chart-bar"></i></span>
                Lead Statistics
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:18px;">
                <div class="mini-stat"><div class="mini-stat-val" id="leads-today">0</div><div class="mini-stat-lbl">Today</div></div>
                <div class="mini-stat"><div class="mini-stat-val" id="leads-week">0</div><div class="mini-stat-lbl">This Week</div></div>
                <div class="mini-stat"><div class="mini-stat-val" id="leads-month">0</div><div class="mini-stat-lbl">This Month</div></div>
            </div>
            <div class="chart-container"><canvas id="leadStatusChart"></canvas></div>
        </div>
        <div class="section-card" style="margin-bottom:0;">
            <div class="section-title">
                <span class="section-title-icon"><i class="fas fa-building"></i></span>
                Property Segments
            </div>
            <div class="chart-container"><canvas id="propertySegmentsChart"></canvas></div>
            <div id="property-segments-legend" style="margin-top:12px;display:flex;flex-wrap:wrap;gap:10px;justify-content:center;">
                <span style="color:#9ca3af;font-size:12px;">Loading...</span>
            </div>
        </div>
    </div>

    {{-- ── Agents Visits vs Meetings ────────────────────────── --}}
    <div class="section-card">
        <div class="section-title">
            <span class="section-title-icon"><i class="fas fa-chart-line"></i></span>
            Agents — Visits vs Meetings
        </div>
        <div class="chart-container"><canvas id="agentsVisitsMeetingsChart"></canvas></div>
        <div id="agents-visits-meetings-table" style="margin-top:16px;overflow-x:auto;">
            <p style="color:#9ca3af;font-size:13px;">Loading...</p>
        </div>
    </div>

    {{-- ── Sales Executive Performance ─────────────────────── --}}
    <div class="section-card">
        <div class="section-title">
            <span class="section-title-icon"><i class="fas fa-trophy"></i></span>
            Sales Executive Performance
        </div>
        <div id="telecaller-performance-cards" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;">
            <p style="color:#9ca3af;font-size:13px;">Loading...</p>
        </div>
    </div>

    {{-- ── Leads Allocated + Avg Response ──────────────────── --}}
    <div class="section-card">
        <div class="section-title">
            <span class="section-title-icon"><i class="fas fa-tasks"></i></span>
            Leads Allocated
        </div>
        <div style="display:grid;grid-template-columns:3fr 1fr;gap:24px;align-items:start;" class="grid-responsive-leads">
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th style="width:36px;"></th>
                            <th>Sales Executive</th>
                            <th style="text-align:center;">Pending</th>
                            <th>Oldest Assigned</th>
                        </tr>
                    </thead>
                    <tbody id="leads-pending-response-tbody">
                        <tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:20px;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
            <div>
                <div style="font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;margin-bottom:12px;">Avg Response Time</div>
                <div id="average-response-time-panel">
                    <p style="color:#9ca3af;font-size:12px;">Loading...</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── User Visits & Meetings ────────────────────────────── --}}
    <div class="section-card">
        <div class="section-title" style="justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:9px;">
                <span class="section-title-icon"><i class="fas fa-user-clock"></i></span>
                User Visits &amp; Meetings
            </div>
            <a href="{{ route('export.index') }}" style="font-size:12px;font-weight:600;color:#205A44;text-decoration:none;display:flex;align-items:center;gap:5px;">
                <i class="fas fa-download"></i> Export
            </a>
        </div>
        <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
            <button class="visits-meetings-filter-btn active" onclick="filterVisitsMeetings('today',this)">Today</button>
            <button class="visits-meetings-filter-btn" onclick="filterVisitsMeetings('tomorrow',this)">Tomorrow</button>
            <button class="visits-meetings-filter-btn" onclick="filterVisitsMeetings('this_weekend',this)">Weekend</button>
            <button class="visits-meetings-filter-btn" onclick="filterVisitsMeetings('this_month',this)">This Month</button>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:18px;">
            <div class="mini-stat"><div class="mini-stat-val" id="visits-meetings-total-users">0</div><div class="mini-stat-lbl">Users</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="visits-meetings-total-visits">0</div><div class="mini-stat-lbl">Site Visits</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="visits-meetings-total-meetings">0</div><div class="mini-stat-lbl">Meetings</div></div>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th style="cursor:pointer;" onclick="sortTable('user_name')">User <i class="fas fa-sort" style="opacity:.4;"></i></th>
                        <th style="cursor:pointer;" onclick="sortTable('role')">Role <i class="fas fa-sort" style="opacity:.4;"></i></th>
                        <th style="text-align:center;cursor:pointer;" onclick="sortTable('visits_count')">Visits <i class="fas fa-sort" style="opacity:.4;"></i></th>
                        <th style="text-align:center;cursor:pointer;" onclick="sortTable('meetings_count')">Meetings <i class="fas fa-sort" style="opacity:.4;"></i></th>
                        <th style="text-align:center;cursor:pointer;" onclick="sortTable('total')">Total <i class="fas fa-sort" style="opacity:.4;"></i></th>
                    </tr>
                </thead>
                <tbody id="visits-meetings-table-body">
                    <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:20px;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Call Statistics ──────────────────────────────────── --}}
    <div class="section-card" id="call-statistics-section">
        <div class="section-title" style="justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:9px;">
                <span class="section-title-icon"><i class="fas fa-phone"></i></span>
                Call Statistics
            </div>
            <div style="display:flex;gap:6px;align-items:center;">
                <button class="call-stats-filter-btn active" onclick="loadCallStatistics('today',this)">Today</button>
                <button class="call-stats-filter-btn" onclick="loadCallStatistics('this_week',this)">Week</button>
                <button class="call-stats-filter-btn" onclick="loadCallStatistics('this_month',this)">Month</button>
                <a href="{{ route('calls.index') }}" style="font-size:12px;font-weight:600;color:#205A44;text-decoration:none;padding:5px 10px;border:1.5px solid #bbf7d0;border-radius:6px;">View All</a>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
            <div class="mini-stat"><div class="mini-stat-val" id="call-stats-total">0</div><div class="mini-stat-lbl">Total Calls</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="call-stats-duration">0s</div><div class="mini-stat-lbl">Total Duration</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="call-stats-avg-duration">0s</div><div class="mini-stat-lbl">Avg Duration</div></div>
            <div class="mini-stat"><div class="mini-stat-val" id="call-stats-connection-rate">0%</div><div class="mini-stat-lbl">Connection Rate</div></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
            <div style="border:1px solid #f3f4f6;border-radius:10px;padding:16px;">
                <div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:12px;">Calls by Role</div>
                <div class="chart-container"><canvas id="callsByRoleChart"></canvas></div>
            </div>
            <div style="border:1px solid #f3f4f6;border-radius:10px;padding:16px;">
                <div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:12px;">Call Outcome Distribution</div>
                <div class="chart-container"><canvas id="outcomeDistributionChart"></canvas></div>
            </div>
        </div>
        <div id="top-users-section" style="display:none;">
            <div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:12px;">Top Users by Calls</div>
            <div style="overflow-x:auto;">
                <table>
                    <thead><tr><th>User</th><th style="text-align:center;">Calls</th><th style="text-align:center;">Duration</th><th style="text-align:center;">Avg</th></tr></thead>
                    <tbody id="top-users-table-body"></tbody>
                </table>
            </div>
        </div>
        <div id="recent-calls-section" style="display:none;margin-top:16px;">
            <div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:12px;">Recent Calls</div>
            <div id="recent-calls-list" style="max-height:280px;overflow-y:auto;"></div>
        </div>
    </div>

    {{-- ── User Stats + Recent Activities (2 col) ──────────── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:22px;" class="grid-responsive-2">
        <div class="section-card" style="margin-bottom:0;">
            <div class="section-title">
                <span class="section-title-icon"><i class="fas fa-user-cog"></i></span>
                Team Overview
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px;">
                <div class="mini-stat" style="text-align:center;"><div class="mini-stat-val" id="users-admin">0</div><div class="mini-stat-lbl">Admin</div></div>
                <div class="mini-stat" style="text-align:center;"><div class="mini-stat-val" id="users-crm">0</div><div class="mini-stat-lbl">CRM</div></div>
                <div class="mini-stat" style="text-align:center;"><div class="mini-stat-val" id="users-sales-manager">0</div><div class="mini-stat-lbl">Sr. Manager</div></div>
                <div class="mini-stat" style="text-align:center;"><div class="mini-stat-val" id="users-sales-executive">0</div><div class="mini-stat-lbl">Sales Exec</div></div>
                <div class="mini-stat" style="text-align:center;"><div class="mini-stat-val" id="users-telecaller">0</div><div class="mini-stat-lbl">Telecaller</div></div>
                <div class="mini-stat" style="text-align:center;"><div class="mini-stat-val" id="users-total">0</div><div class="mini-stat-lbl">Total Active</div></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px;">
                    <div style="font-size:20px;font-weight:700;color:#065f46;" id="users-new-month">0</div>
                    <div style="font-size:11px;color:#6b7280;margin-top:3px;">New This Month</div>
                </div>
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px;">
                    <div style="font-size:20px;font-weight:700;color:#1e40af;" id="users-active-24h">0</div>
                    <div style="font-size:11px;color:#6b7280;margin-top:3px;">Active (24h)</div>
                </div>
            </div>
        </div>
        <div class="section-card" style="margin-bottom:0;">
            <div class="section-title">
                <span class="section-title-icon"><i class="fas fa-history"></i></span>
                Recent Activities
            </div>
            <div id="recent-activities" style="max-height:280px;overflow-y:auto;">
                <p style="color:#9ca3af;font-size:13px;">Loading...</p>
            </div>
        </div>
    </div>

    {{-- ── Recent Leads ──────────────────────────────────────── --}}
    <div class="section-card">
        <div class="section-title" style="justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:9px;">
                <span class="section-title-icon"><i class="fas fa-user-plus"></i></span>
                Recent Leads
            </div>
            <a href="{{ route('leads.index') }}" style="font-size:12px;font-weight:600;color:#205A44;text-decoration:none;">View All →</a>
        </div>
        <div id="recent-leads" style="overflow-x:auto;">
            <p style="color:#9ca3af;font-size:13px;">Loading...</p>
        </div>
    </div>

    </div>

    <div id="marketing-dashboard-panel" class="dashboard-panel">
        <div class="premium-grid-4" style="margin-bottom:22px;">
            <div class="marketing-stat">
                <div class="value" id="marketing-total-batches">0</div>
                <div class="label">Import Batches</div>
                <div class="sub" id="marketing-completed-batches">0 completed batches</div>
            </div>
            <div class="marketing-stat">
                <div class="value" id="marketing-imported-leads">0</div>
                <div class="label">Imported Leads</div>
                <div class="sub" id="marketing-pending-batches">0 pending or processing</div>
            </div>
            <div class="marketing-stat">
                <div class="value" id="marketing-junk-leads">0</div>
                <div class="label">Junk Leads</div>
                <div class="sub">Current reporting window quality drop-offs</div>
            </div>
            <div class="marketing-stat">
                <div class="value" id="marketing-not-interested">0</div>
                <div class="label">Not Interested</div>
                <div class="sub">Use this to inspect source quality and recycle flow</div>
            </div>
        </div>

        <div class="premium-grid-2" style="margin-bottom:22px;">
            <div class="section-card" style="margin-bottom:0;">
                <div class="section-title">
                    <span class="section-title-icon"><i class="fas fa-bullseye"></i></span>
                    Lead Source Mix
                </div>
                <div class="chart-container"><canvas id="marketingSourceChart"></canvas></div>
                <div id="marketing-source-list" class="marketing-list" style="margin-top:14px;">
                    <div class="marketing-list-item"><strong>No data</strong><span>Source distribution will appear here.</span></div>
                </div>
            </div>
            <div class="section-card" style="margin-bottom:0;">
                <div class="section-title">
                    <span class="section-title-icon"><i class="fas fa-wave-square"></i></span>
                    Lead Inflow
                </div>
                <div class="chart-container"><canvas id="marketingInflowChart"></canvas></div>
                <div id="marketing-import-summary" class="marketing-score-grid" style="margin-top:14px;">
                    <div class="marketing-score-box">
                        <div class="score">0</div>
                        <div class="name">Completed Imports</div>
                        <div class="note">Import summary will render here.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="premium-grid-3" style="margin-bottom:22px;">
            <div class="section-card" style="margin-bottom:0;">
                <div class="section-title">
                    <span class="section-title-icon"><i class="fas fa-filter"></i></span>
                    Quality Snapshot
                </div>
                <div class="marketing-score-grid" id="marketing-quality-grid">
                    <div class="marketing-score-box">
                        <div class="score">0</div>
                        <div class="name">Connected</div>
                        <div class="note">Lead quality split will appear here.</div>
                    </div>
                </div>
            </div>
            <div class="section-card" style="margin-bottom:0;">
                <div class="section-title">
                    <span class="section-title-icon"><i class="fas fa-phone-volume"></i></span>
                    Call Outcomes
                </div>
                <div id="marketing-call-outcomes" class="marketing-list">
                    <div class="marketing-list-item"><strong>No outcomes</strong><span>Call outcome mix will appear here.</span></div>
                </div>
            </div>
            <div class="section-card" style="margin-bottom:0;">
                <div class="section-title">
                    <span class="section-title-icon"><i class="fas fa-user-clock"></i></span>
                    Fresh Leads
                </div>
                <div id="marketing-recent-leads" class="marketing-list">
                    <div class="marketing-list-item"><strong>No recent leads</strong><span>Recent additions will appear here.</span></div>
                </div>
            </div>
        </div>
    </div>

    <div id="targets-breakdown-modal" class="metric-modal" onclick="closeTargetsModal(event)">
        <div class="metric-modal-card" onclick="event.stopPropagation()">
            <div class="metric-modal-header">
                <div>
                    <div class="premium-filter-label">Team Targets</div>
                    <div style="font-size:24px;font-family:'Fraunces',serif;font-weight:700;color:#16161A;">Per User Breakdown</div>
                </div>
                <button type="button" class="metric-modal-close" onclick="closeTargetsModal()"><i class="fas fa-times"></i></button>
            </div>
            <div id="targets-breakdown-table" style="overflow-x:auto;">
                <p style="color:#9ca3af;font-size:13px;">Loading...</p>
            </div>
        </div>
    </div>

    {{-- hidden IDs kept for JS compat --}}
    <div style="display:none;">
        <span id="pending-verifications"></span>
        <span id="active-automations"></span>
        <span id="pending-imports"></span>
        <span id="failed-imports"></span>
    </div>
    <div id="health-stats" style="display:none;"></div>
    <div id="target-overview-section" style="display:none;"><div id="target-overview-content"></div></div>
</div>
</div>

<style>
@media(max-width:768px){
    .admin-hero-grid,
    .premium-grid-2,
    .premium-grid-3,
    .premium-grid-4,
    .marketing-score-grid,
    .shortcut-grid,
    .score-card-grid { grid-template-columns: 1fr !important; }
    .admin-hero-title { font-size: 28px; }
    .admin-hero-meta { grid-template-columns: 1fr; }
    .grid-responsive-2{grid-template-columns:1fr !important;}
    .grid-responsive-leads{grid-template-columns:1fr !important;}
    #main-stats{grid-template-columns:repeat(2,1fr) !important;}
    .funnel-row { flex-wrap: wrap; }
    .funnel-row-link { flex-wrap: wrap; margin:0; padding:8px 0; }
    .funnel-label, .funnel-meta { width: auto; text-align: left; }
    .funnel-bar-wrap { min-width: 100%; }
    .metric-modal-card { padding: 18px 16px; }
}
@media(max-width:480px){
    #main-stats{grid-template-columns:1fr !important;}
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@if(config('broadcasting.default') === 'pusher')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
@endif
<script>
    const API_BASE_URL = '/admin/dashboard/data';
    let leadStatusChart = null;
    let agentsVisitsMeetingsChart = null;
    let propertySegmentsChart = null;
    let marketingSourceChart = null;
    let marketingInflowChart = null;
    let currentFilter = localStorage.getItem('dashboardFilter') || 'month';
    let customStartDate = localStorage.getItem('dashboardStartDate') || '';
    let customEndDate = localStorage.getItem('dashboardEndDate') || '';
    let currentDashboardMode = localStorage.getItem('adminDashboardMode') || 'sale';

    // Initialize filter UI on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Set active filter button
        document.querySelectorAll('.date-filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        const activeBtn = document.getElementById('filter-' + currentFilter);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }

        // Set custom dates if available
        if (customStartDate && customEndDate) {
            document.getElementById('custom-start-date').value = customStartDate;
            document.getElementById('custom-end-date').value = customEndDate;
        }

        switchDashboardMode(currentDashboardMode);
        updateHeroDateRangeLabel();
    });

    function updateHeroDateRangeLabel() {
        const label = document.getElementById('hero-date-range-label');
        if (!label) {
            return;
        }

        if (currentFilter === 'custom' && customStartDate && customEndDate) {
            label.textContent = `${customStartDate} to ${customEndDate}`;
            return;
        }

        const labels = {
            today: 'Today',
            week: 'This week',
            month: 'This month',
            year: 'This year',
            custom: 'Custom range',
        };
        label.textContent = labels[currentFilter] || 'This month';
    }

    function switchDashboardMode(mode) {
        currentDashboardMode = mode === 'marketing' ? 'marketing' : 'sale';
        localStorage.setItem('adminDashboardMode', currentDashboardMode);

        document.querySelectorAll('.dashboard-mode-btn').forEach((button) => {
            button.classList.remove('is-active');
        });

        const saleBtn = document.getElementById('dashboard-mode-sale');
        const marketingBtn = document.getElementById('dashboard-mode-marketing');
        const salePanel = document.getElementById('sale-dashboard-panel');
        const marketingPanel = document.getElementById('marketing-dashboard-panel');

        if (saleBtn) saleBtn.classList.toggle('is-active', currentDashboardMode === 'sale');
        if (marketingBtn) marketingBtn.classList.toggle('is-active', currentDashboardMode === 'marketing');
        if (salePanel) salePanel.classList.toggle('is-active', currentDashboardMode === 'sale');
        if (marketingPanel) marketingPanel.classList.toggle('is-active', currentDashboardMode === 'marketing');

        window.requestAnimationFrame(() => {
            if (leadStatusChart) leadStatusChart.resize();
            if (agentsVisitsMeetingsChart) agentsVisitsMeetingsChart.resize();
            if (propertySegmentsChart) propertySegmentsChart.resize();
            if (marketingSourceChart) marketingSourceChart.resize();
            if (marketingInflowChart) marketingInflowChart.resize();
        });
    }

    function applyDateFilter(filter) {
        currentFilter = filter;
        customStartDate = '';
        customEndDate = '';
        
        // Update active button
        document.querySelectorAll('.date-filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.getElementById('filter-' + filter).classList.add('active');

        // Clear custom date inputs
        document.getElementById('custom-start-date').value = '';
        document.getElementById('custom-end-date').value = '';

        // Save to localStorage
        localStorage.setItem('dashboardFilter', filter);
        localStorage.removeItem('dashboardStartDate');
        localStorage.removeItem('dashboardEndDate');
        updateHeroDateRangeLabel();

        // Reload dashboard data
        loadDashboardData();
    }

    function applyCustomDateFilter() {
        const startDate = document.getElementById('custom-start-date').value;
        const endDate = document.getElementById('custom-end-date').value;

        if (!startDate || !endDate) {
            return;
        }

        customStartDate = startDate;
        customEndDate = endDate;
        currentFilter = 'custom';

        // Update active button
        document.querySelectorAll('.date-filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Save to localStorage
        localStorage.setItem('dashboardFilter', 'custom');
        localStorage.setItem('dashboardStartDate', startDate);
        localStorage.setItem('dashboardEndDate', endDate);
        updateHeroDateRangeLabel();

        // Reload dashboard data
        loadDashboardData();
    }

    async function loadDashboardData() {
        try {
            // Show loading state
            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('main-stats').classList.add('hidden');
            document.getElementById('health-stats').classList.add('hidden');

            // Build query parameters
            const params = new URLSearchParams();
            params.append('filter', currentFilter);
            if (customStartDate && customEndDate) {
                params.append('start_date', customStartDate);
                params.append('end_date', customEndDate);
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const response = await fetch(API_BASE_URL + '?' + params.toString(), {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                const errorText = await response.text();
                let errorData;
                try {
                    errorData = JSON.parse(errorText);
                } catch (e) {
                    errorData = { message: errorText || 'Failed to load dashboard data' };
                }
                throw new Error(errorData.message || errorData.error || 'Failed to load dashboard data');
            }

            const data = await response.json();
            
            // Debug: Log data to console
            console.log('Dashboard data received:', data);
            
            // Validate data structure
            if (!data || typeof data !== 'object') {
                throw new Error('Invalid data received from server');
            }
            
            renderDashboard(data);
        } catch (error) {
            console.error('Error loading dashboard:', error);
            const loadingEl = document.getElementById('loading');
            if (loadingEl) {
                loadingEl.innerHTML = `<p class="text-red-600">Error: ${error.message}. Please refresh the page.</p>`;
            }
        }
    }

    function renderDashboard(data) {
        // Hide loading, show content
        const loadingEl = document.getElementById('loading');
        const mainStatsEl = document.getElementById('main-stats');
        const healthStatsEl = document.getElementById('health-stats');
        
        if (loadingEl) loadingEl.classList.add('hidden');
        if (mainStatsEl) mainStatsEl.classList.remove('hidden');
        if (healthStatsEl) healthStatsEl.classList.remove('hidden');
        updateHeroDateRangeLabel();
        renderHeroMetrics(data);
        renderDashboardShortcuts(data.dashboard_shortcuts || []);
        renderPerformanceScores(data.performance_scores || {});
        renderPipelineFunnel(data.pipeline_funnel || []);
        renderTeamTargetsSummary(data.team_targets_summary || {});
        renderTargetsBreakdown(data.team_targets_breakdown || []);
        renderIncentiveSummary(data.incentive_summary || {});
        renderSalesScoreTable(data.sales_score_table || []);
        renderUserPipelineTable(data.user_pipeline_table || []);

        // System Stats - with null checks
        const systemStats = data.system_stats || {};
        
        const leadsEl = document.getElementById('total-leads');
        if (leadsEl) leadsEl.textContent = systemStats.total_leads || 0;
        
        const visitsEl = document.getElementById('total-visits');
        if (visitsEl) visitsEl.textContent = systemStats.total_visits || 0;
        
        const meetingsEl = document.getElementById('total-meetings');
        if (meetingsEl) meetingsEl.textContent = systemStats.total_meetings || 0;
        
        const closersEl = document.getElementById('total-closers');
        if (closersEl) closersEl.textContent = systemStats.total_closers || 0;
        
        const deadEl = document.getElementById('total-dead');
        if (deadEl) deadEl.textContent = systemStats.total_dead || 0;

        // System Health - with null checks
        const systemHealth = data.system_health || {};
        const pendingVerEl = document.getElementById('pending-verifications');
        if (pendingVerEl) pendingVerEl.textContent = systemHealth.pending_verifications || 0;
        
        const activeAutoEl = document.getElementById('active-automations');
        if (activeAutoEl) activeAutoEl.textContent = systemHealth.active_automations || 0;
        
        const pendingImpEl = document.getElementById('pending-imports');
        if (pendingImpEl) pendingImpEl.textContent = systemHealth.pending_imports || 0;
        
        const failedImpEl = document.getElementById('failed-imports');
        if (failedImpEl) failedImpEl.textContent = systemHealth.failed_imports || 0;

        // User Stats - with null checks
        const userStats = data.user_stats || {};
        const byRole = userStats.by_role || {};
        const adminEl = document.getElementById('users-admin');
        if (adminEl) adminEl.textContent = byRole.admin || 0;
        
        const crmEl = document.getElementById('users-crm');
        if (crmEl) crmEl.textContent = byRole.crm || 0;
        
        const smEl = document.getElementById('users-sales-manager');
        if (smEl) smEl.textContent = byRole.sales_manager || 0;
        
        const seEl = document.getElementById('users-sales-executive');
        if (seEl) seEl.textContent = byRole.sales_executive || 0;
        
        const telEl = document.getElementById('users-telecaller');
        if (telEl) telEl.textContent = byRole.telecaller || 0;
        
        const newMonthEl = document.getElementById('users-new-month');
        if (newMonthEl) newMonthEl.textContent = userStats.new_this_month || 0;
        
        const active24hEl = document.getElementById('users-active-24h');
        if (active24hEl) active24hEl.textContent = userStats.active_24h || 0;
        
        const totalEl = document.getElementById('users-total');
        if (totalEl) totalEl.textContent = userStats.total || 0;

        // Lead Stats - with null checks
        const leadStats = data.lead_stats || {};
        const todayEl = document.getElementById('leads-today');
        if (todayEl) todayEl.textContent = leadStats.new_today || 0;
        
        const weekEl = document.getElementById('leads-week');
        if (weekEl) weekEl.textContent = leadStats.new_this_week || 0;
        
        const monthEl = document.getElementById('leads-month');
        if (monthEl) monthEl.textContent = leadStats.new_this_month || 0;

        // Lead Status Chart
        if (leadStats.by_status) {
            renderLeadStatusChart(leadStats.by_status);
        }

        // Target Overview
        if (data.target_overview) {
            renderTargetOverview(data.target_overview);
        }

        // Recent Leads
        if (data.recent_leads && Array.isArray(data.recent_leads)) {
            renderRecentLeads(data.recent_leads);
        } else {
            renderRecentLeads([]);
        }

        // Recent Activities
        if (data.recent_activities && Array.isArray(data.recent_activities)) {
            renderRecentActivities(data.recent_activities);
        } else {
            renderRecentActivities([]);
        }

        // Agents Visits vs Meetings
        if (data.agents_visits_meetings && Array.isArray(data.agents_visits_meetings)) {
            renderAgentsVisitsVsMeetings(data.agents_visits_meetings);
        } else {
            renderAgentsVisitsVsMeetings([]);
        }

        // Property Segments
        if (data.property_segments) {
            renderPropertySegments(data.property_segments);
        } else {
            renderPropertySegments({});
        }

        // Telecaller Performance
        console.log('Telecaller Performance Data:', data.telecaller_performance);
        if (data.telecaller_performance && Array.isArray(data.telecaller_performance)) {
            renderTelecallerPerformance(data.telecaller_performance);
        } else {
            console.warn('No telecaller performance data or invalid format');
            renderTelecallerPerformance([]);
        }

        // Leads Pending Response
        if (data.leads_pending_response && Array.isArray(data.leads_pending_response)) {
            renderLeadsPendingResponse(data.leads_pending_response, data.server_now);
        } else {
            renderLeadsPendingResponse([], data.server_now);
        }

        // Average Response Time
        if (data.average_response_time_by_user && Array.isArray(data.average_response_time_by_user)) {
            renderAverageResponseTime(data.average_response_time_by_user);
        } else {
            renderAverageResponseTime([]);
        }

        // Call Statistics
        if (data.call_statistics) {
            updateCallStatistics(data.call_statistics);
        }

        renderMarketingSummary(
            data.marketing_summary || {},
            data.call_statistics || {},
            Array.isArray(data.recent_leads) ? data.recent_leads : []
        );

        // User Visits & Meetings - Load with default filter
        const visitsMeetingsFilter = localStorage.getItem('visitsMeetingsFilter') || 'this_month';
        loadUserVisitsMeetings(visitsMeetingsFilter);
    }

    function renderHeroMetrics(data) {
        const systemStats = data.system_stats || {};
        const marketingSummary = data.marketing_summary || {};
        const importSummary = marketingSummary.import_summary || {};
        const callStatistics = data.call_statistics || {};

        const totalLeads = document.getElementById('hero-total-leads');
        const connectionRate = document.getElementById('hero-connection-rate');
        const importedLeads = document.getElementById('hero-imported-leads');

        if (totalLeads) totalLeads.textContent = systemStats.total_leads || 0;
        if (connectionRate) connectionRate.textContent = `${Math.round(callStatistics.connection_rate || 0)}%`;
        if (importedLeads) importedLeads.textContent = importSummary.imported_leads || 0;
    }

    function renderDashboardShortcuts(shortcuts) {
        const container = document.getElementById('dashboard-shortcuts');
        if (!container) {
            return;
        }

        if (!Array.isArray(shortcuts) || shortcuts.length === 0) {
            container.innerHTML = '';
            return;
        }

        container.innerHTML = shortcuts.map((item) => `
            <a class="shortcut-card" href="${item.url || '#'}">
                <span class="shortcut-icon"><i class="fas ${item.icon || 'fa-layer-group'}"></i></span>
                <span>
                    <div class="shortcut-label">${item.label || 'Option'}</div>
                    <div class="shortcut-value">${item.count || 0}</div>
                </span>
            </a>
        `).join('');
    }

    function renderPerformanceScores(scores) {
        const container = document.getElementById('performance-scores');
        if (!container) {
            return;
        }

        const items = [
            ['PS Score', `${Number(scores.ps || 0).toFixed(1)}%`, '(Visits + Meetings) / Leads'],
            ['PP Score', `${Number(scores.pp || 0).toFixed(1)}%`, 'Closures / Leads'],
            ['VP Score', `${Number(scores.vp || 0).toFixed(1)}%`, 'Closures / Visits'],
        ];

        container.innerHTML = items.map(([label, value, note]) => `
            <div class="score-card">
                <div class="value">${value}</div>
                <div class="label">${label}</div>
                <div class="note">${note}</div>
            </div>
        `).join('');
    }

    function renderPipelineFunnel(items) {
        const container = document.getElementById('pipeline-funnel');
        if (!container) {
            return;
        }

        const colors = ['#0b6b4f', '#1f8f73', '#1761a8', '#5946c0', '#9a6510', '#9a3412', '#b42318'];
        const funnelLinks = {
            leads: '{{ route('leads.index') }}',
            prospects: '{{ route('prospects.index') }}',
            meetings: '{{ route('leads.index', ['lead_type_filter' => 'meeting']) }}',
            visits: '{{ route('leads.index', ['lead_type_filter' => 'visit']) }}',
            closures: '{{ route('leads.index', ['lead_type_filter' => 'closer']) }}',
            junk: '{{ route('admin.other-leads.index', ['type' => 'junk']) }}',
            'not interested': '{{ route('admin.other-leads.index', ['type' => 'not_interested']) }}',
        };
        container.innerHTML = (Array.isArray(items) ? items : []).map((item, index) => {
            const width = Math.max(12, Number(item.percentage || 0));
            const key = String(item.label || '').trim().toLowerCase();
            const target = funnelLinks[key] || funnelLinks.leads;
            return `
                <div class="funnel-row">
                    <a href="${target}" class="funnel-row-link">
                        <div class="funnel-label">${item.label || 'Stage'}</div>
                        <div class="funnel-bar-wrap">
                            <div class="funnel-bar" style="width:${width}%;background:${colors[index] || '#0b6b4f'};">${item.value || 0}</div>
                        </div>
                        <div class="funnel-meta">${Number(item.percentage || 0).toFixed(1)}%</div>
                    </a>
                </div>
            `;
        }).join('');
    }

    function renderTeamTargetsSummary(summary) {
        const container = document.getElementById('team-targets-summary');
        if (!container) {
            return;
        }

        const metrics = summary.metrics || {};
        const config = [
            ['Meetings', metrics.meetings || {}, '#1761a8'],
            ['Visits', metrics.visits || {}, '#5946c0'],
            ['Closers', metrics.closers || {}, '#9a6510'],
        ];

        container.innerHTML = config.map(([label, item, color]) => `
            <div>
                <div class="progress-item-head">
                    <strong>${label}</strong>
                    <span>${item.achieved || 0} / ${item.target || 0}</span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill" style="width:${Math.min(100, Number(item.percentage || 0))}%;background:${color};"></div>
                </div>
            </div>
        `).join('');
    }

    function renderTargetsBreakdown(rows) {
        const container = document.getElementById('targets-breakdown-table');
        if (!container) {
            return;
        }

        if (!Array.isArray(rows) || rows.length === 0) {
            container.innerHTML = '<p style="color:#9ca3af;font-size:13px;">No target breakdown found.</p>';
            return;
        }

        container.innerHTML = `
            <table class="metric-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Meetings</th>
                        <th>Visits</th>
                        <th>Closers</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows.map((row) => `
                        <tr>
                            <td><span class="table-avatar">${(row.user_name || 'U').charAt(0).toUpperCase()}</span>${row.user_name || 'Unknown'}</td>
                            <td>${row.role || 'Unknown'}</td>
                            <td>${row.meetings?.achieved || 0} / ${row.meetings?.target || 0}<br><small>${Number(row.meetings?.percentage || 0).toFixed(1)}%</small></td>
                            <td>${row.visits?.achieved || 0} / ${row.visits?.target || 0}<br><small>${Number(row.visits?.percentage || 0).toFixed(1)}%</small></td>
                            <td>${row.closers?.achieved || 0} / ${row.closers?.target || 0}<br><small>${Number(row.closers?.percentage || 0).toFixed(1)}%</small></td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }

    function renderIncentiveSummary(summary) {
        const container = document.getElementById('incentive-summary');
        if (!container) {
            return;
        }

        const amount = Number(summary.total_amount || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 });
        const items = [
            ['Total', summary.total || 0, `Rs ${amount} total value`],
            ['Verified', summary.verified || 0, 'Fully approved incentives'],
            ['Pending', summary.pending || 0, 'Awaiting approval'],
            ['Rejected', summary.rejected || 0, 'Rejected requests'],
        ];

        container.innerHTML = items.map(([label, value, note]) => `
            <div class="score-card">
                <div class="value">${value}</div>
                <div class="label">${label}</div>
                <div class="note">${note}</div>
            </div>
        `).join('');
    }

    function renderSalesScoreTable(rows) {
        const container = document.getElementById('sales-score-table');
        if (!container) {
            return;
        }

        if (!Array.isArray(rows) || rows.length === 0) {
            container.innerHTML = '<p style="color:#9ca3af;font-size:13px;">No score data available.</p>';
            return;
        }

        container.innerHTML = `
            <table class="metric-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Leads</th>
                        <th>Meet + Visit</th>
                        <th>Closers</th>
                        <th>PS %</th>
                        <th>PP %</th>
                        <th>VP %</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows.map((row) => `
                        <tr>
                            <td><span class="table-avatar">${(row.user_name || 'U').charAt(0).toUpperCase()}</span>${row.user_name || 'Unknown'}</td>
                            <td>${row.role || 'Unknown'}</td>
                            <td>${row.leads || 0}</td>
                            <td>${row.meet_visit || 0}</td>
                            <td>${row.closers || 0}</td>
                            <td><strong style="color:#0b6b4f;">${Number(row.ps || 0).toFixed(1)}%</strong></td>
                            <td><strong style="color:#1761a8;">${Number(row.pp || 0).toFixed(1)}%</strong></td>
                            <td><strong style="color:#5946c0;">${Number(row.vp || 0).toFixed(1)}%</strong></td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }

    function renderUserPipelineTable(rows) {
        const container = document.getElementById('user-pipeline-table');
        if (!container) {
            return;
        }

        if (!Array.isArray(rows) || rows.length === 0) {
            container.innerHTML = '<p style="color:#9ca3af;font-size:13px;">No user pipeline data available.</p>';
            return;
        }

        container.innerHTML = `
            <table class="metric-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Leads</th>
                        <th>Meetings</th>
                        <th>Visits</th>
                        <th>Closers</th>
                        <th>Avg Response</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows.map((row) => `
                        <tr>
                            <td><span class="table-avatar">${(row.user_name || 'U').charAt(0).toUpperCase()}</span>${row.user_name || 'Unknown'}</td>
                            <td>${row.role || 'Unknown'}</td>
                            <td>${row.leads || 0}</td>
                            <td>${row.meetings || 0}</td>
                            <td>${row.visits || 0}</td>
                            <td>${row.closers || 0}</td>
                            <td>${Number(row.avg_response_minutes || 0).toFixed(1)} min</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }

    function openTargetsModal() {
        const modal = document.getElementById('targets-breakdown-modal');
        if (modal) {
            modal.classList.add('is-open');
        }
    }

    function closeTargetsModal(event) {
        if (event && event.target && event.target !== event.currentTarget) {
            return;
        }
        const modal = document.getElementById('targets-breakdown-modal');
        if (modal) {
            modal.classList.remove('is-open');
        }
    }

    function renderLeadStatusChart(statusData) {
        const ctx = document.getElementById('leadStatusChart');
        if (leadStatusChart) {
            leadStatusChart.destroy();
        }

        const labels = Object.keys(statusData);
        const values = Object.values(statusData);

        leadStatusChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels.map(label => label.replace(/_/g, ' ').toUpperCase()),
                datasets: [{
                    label: 'Leads by Status',
                    data: values,
                    backgroundColor: 'var(--primary-color)',
                    borderColor: 'var(--secondary-color)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function renderTargetOverview(overview) {
        const section = document.getElementById('target-overview-section');
        const content = document.getElementById('target-overview-content');
        
        if (!overview || !overview.targets) {
            section.classList.add('hidden');
            return;
        }

        section.classList.remove('hidden');
        const ov = overview;
        content.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="p-4 bg-[#F7F6F3] rounded-lg">
                    <h4 class="font-semibold text-brand-primary mb-2">Total Prospects Extract</h4>
                    <div class="text-2xl font-bold text-gray-700">${ov.actuals?.prospects_extract || 0} / ${ov.targets?.prospects_extract || 0}</div>
                    <div class="text-sm text-gray-600">${Math.round(ov.percentages?.prospects_extract || 0)}% Complete</div>
                </div>
                <div class="p-4 bg-[#F7F6F3] rounded-lg">
                    <h4 class="font-semibold text-brand-primary mb-2">Total Prospects Verified</h4>
                    <div class="text-2xl font-bold text-gray-700">${ov.actuals?.prospects_verified || 0} / ${ov.targets?.prospects_verified || 0}</div>
                    <div class="text-sm text-gray-600">${Math.round(ov.percentages?.prospects_verified || 0)}% Complete</div>
                </div>
                <div class="p-4 bg-[#F7F6F3] rounded-lg">
                    <h4 class="font-semibold text-brand-primary mb-2">Total Calls</h4>
                    <div class="text-2xl font-bold text-gray-700">${ov.actuals?.calls || 0} / ${ov.targets?.calls || 0}</div>
                    <div class="text-sm text-gray-600">${Math.round(ov.percentages?.calls || 0)}% Complete</div>
                </div>
            </div>
            <p class="text-sm text-gray-600">Total Users: ${ov.total_users || 0} | Month: ${ov.month || 'N/A'}</p>
        `;
    }

    // Status mapping function for display
    function getStatusDisplay(status) {
        const statusMap = {
            // New statuses
            'new': { label: 'New', class: 'badge-new' },
            'connected': { label: 'Connected', class: 'badge-connected' },
            'verified_prospect': { label: 'Verified Prospect', class: 'badge-verified-prospect' },
            'meeting_scheduled': { label: 'Meeting Scheduled', class: 'badge-meeting-scheduled' },
            'meeting_completed': { label: 'Meeting Completed', class: 'badge-meeting-completed' },
            'visit_scheduled': { label: 'Visit Scheduled', class: 'badge-visit-scheduled' },
            'visit_done': { label: 'Visit Done', class: 'badge-visit-done' },
            'revisited_scheduled': { label: 'Revisit Scheduled', class: 'badge-revisited-scheduled' },
            'revisited_completed': { label: 'Revisit Completed', class: 'badge-revisited-completed' },
            'closed': { label: 'Closed', class: 'badge-closed' },
            'dead': { label: 'Dead', class: 'badge-dead' },
            'on_hold': { label: 'On Hold', class: 'badge-on-hold' },
            // Old statuses (for backward compatibility during migration)
            'contacted': { label: 'Contacted', class: 'badge-contacted' },
            'qualified': { label: 'Verified Prospect', class: 'badge-verified-prospect' },
            'site_visit_scheduled': { label: 'Visit Scheduled', class: 'badge-visit-scheduled' },
            'site_visit_completed': { label: 'Visit Done', class: 'badge-visit-done' },
            'closed_won': { label: 'Closed', class: 'badge-closed' },
            'closed_lost': { label: 'Dead', class: 'badge-dead' },
            'negotiation': { label: 'Negotiation', class: 'badge-negotiation' },
        };
        
        const statusInfo = statusMap[status] || { label: status || 'N/A', class: 'badge-default' };
        return statusInfo;
    }

    function renderRecentLeads(leads) {
        const container = document.getElementById('recent-leads');
        
        if (!leads || !Array.isArray(leads) || leads.length === 0) {
            container.innerHTML = '<p class="text-gray-600">No recent leads found</p>';
            return;
        }

        const tableHtml = `
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    ${leads.filter(lead => lead !== null && lead !== undefined).map(lead => {
                        const statusInfo = getStatusDisplay(lead?.status);
                        return `
                        <tr>
                            <td>${lead?.name || 'N/A'}</td>
                            <td>${lead?.phone || 'N/A'}</td>
                            <td><span class="badge ${statusInfo.class}">${statusInfo.label}</span></td>
                            <td>${lead?.created_by || 'System'}</td>
                            <td>${lead?.created_at ? new Date(lead.created_at).toLocaleDateString() : 'N/A'}</td>
                        </tr>
                    `;
                    }).join('')}
                </tbody>
            </table>
        `;
        container.innerHTML = tableHtml;
    }

    function renderMarketingSummary(summary, callStatistics, recentLeads) {
        const leadQuality = summary.lead_quality || {};
        const importSummary = summary.import_summary || {};
        const sourceDistribution = Array.isArray(summary.source_distribution) ? summary.source_distribution : [];
        const leadInflow = Array.isArray(summary.lead_inflow) ? summary.lead_inflow : [];
        const outcomeDistribution = callStatistics.outcome_distribution || {};

        const setText = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        };

        setText('marketing-total-batches', importSummary.total_batches || 0);
        setText('marketing-completed-batches', `${importSummary.completed_batches || 0} completed batches`);
        setText('marketing-imported-leads', importSummary.imported_leads || 0);
        setText('marketing-pending-batches', `${(importSummary.pending_batches || 0)} pending or processing`);
        setText('marketing-junk-leads', leadQuality.junk || 0);
        setText('marketing-not-interested', leadQuality.not_interested || 0);

        renderMarketingSourceChart(sourceDistribution);
        renderMarketingInflowChart(leadInflow);

        const sourceList = document.getElementById('marketing-source-list');
        if (sourceList) {
            sourceList.innerHTML = sourceDistribution.length
                ? sourceDistribution.map((item) => `
                    <div class="marketing-list-item">
                        <strong>${item.source || 'Unknown'}</strong>
                        <span>${item.value || 0} leads</span>
                    </div>
                `).join('')
                : '<div class="marketing-list-item"><strong>No data</strong><span>Source distribution will appear here.</span></div>';
        }

        const importBox = document.getElementById('marketing-import-summary');
        if (importBox) {
            importBox.innerHTML = `
                <div class="marketing-score-box">
                    <div class="score">${importSummary.completed_batches || 0}</div>
                    <div class="name">Completed Imports</div>
                    <div class="note">${importSummary.failed_batches || 0} failed batches in this window.</div>
                </div>
                <div class="marketing-score-box">
                    <div class="score">${importSummary.pending_batches || 0}</div>
                    <div class="name">Pending Imports</div>
                    <div class="note">${importSummary.imported_leads || 0} leads imported from all completed runs.</div>
                </div>
            `;
        }

        const qualityGrid = document.getElementById('marketing-quality-grid');
        if (qualityGrid) {
            const items = [
                ['Connected', leadQuality.connected || 0, 'Leads that moved past first contact.'],
                ['Verified Prospect', leadQuality.verified_prospect || 0, 'Leads validated as stronger pipeline candidates.'],
                ['Junk', leadQuality.junk || 0, 'Leads filtered out as invalid or unusable.'],
                ['Not Interested', leadQuality.not_interested || 0, 'Leads parked for later recycle or reassignment.'],
            ];
            qualityGrid.innerHTML = items.map(([name, score, note]) => `
                <div class="marketing-score-box">
                    <div class="score">${score}</div>
                    <div class="name">${name}</div>
                    <div class="note">${note}</div>
                </div>
            `).join('');
        }

        const outcomes = document.getElementById('marketing-call-outcomes');
        if (outcomes) {
            const entries = Object.entries(outcomeDistribution);
            outcomes.innerHTML = entries.length
                ? entries.slice(0, 6).map(([name, value]) => `
                    <div class="marketing-list-item">
                        <strong>${String(name).replace(/_/g, ' ')}</strong>
                        <span>${value || 0} calls</span>
                    </div>
                `).join('')
                : '<div class="marketing-list-item"><strong>No outcomes</strong><span>Call outcome mix will appear here.</span></div>';
        }

        const recentLeadsList = document.getElementById('marketing-recent-leads');
        if (recentLeadsList) {
            recentLeadsList.innerHTML = recentLeads.length
                ? recentLeads.slice(0, 6).map((lead) => `
                    <div class="marketing-list-item">
                        <strong>${lead?.name || 'N/A'}</strong>
                        <span>${lead?.phone || 'N/A'} · ${lead?.created_by || 'System'}</span>
                    </div>
                `).join('')
                : '<div class="marketing-list-item"><strong>No recent leads</strong><span>Recent additions will appear here.</span></div>';
        }
    }

    function renderMarketingSourceChart(items) {
        const ctx = document.getElementById('marketingSourceChart');
        if (!ctx) {
            return;
        }

        if (marketingSourceChart) {
            marketingSourceChart.destroy();
        }

        marketingSourceChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: items.map((item) => item.source || 'Unknown'),
                datasets: [{
                    data: items.map((item) => item.value || 0),
                    backgroundColor: ['#0b6b4f', '#3f7cff', '#f59e0b', '#7a67de', '#ef4444', '#14b8a6'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    function renderMarketingInflowChart(items) {
        const ctx = document.getElementById('marketingInflowChart');
        if (!ctx) {
            return;
        }

        if (marketingInflowChart) {
            marketingInflowChart.destroy();
        }

        marketingInflowChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: items.map((item) => item.label || ''),
                datasets: [{
                    label: 'Lead Inflow',
                    data: items.map((item) => item.value || 0),
                    borderColor: '#5946c0',
                    backgroundColor: 'rgba(89, 70, 192, 0.12)',
                    tension: 0.35,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function renderRecentActivities(activities) {
        const container = document.getElementById('recent-activities');
        
        if (!activities || !Array.isArray(activities) || activities.length === 0) {
            container.innerHTML = '<p class="text-gray-600">No recent activities found</p>';
            return;
        }

        const tableHtml = `
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    ${activities.filter(activity => activity !== null && activity !== undefined).map(activity => `
                        <tr>
                            <td>${activity?.user_name || 'System'}</td>
                            <td><span class="badge">${activity?.action || 'N/A'}</span></td>
                            <td>${activity?.description || 'N/A'}</td>
                            <td>${activity?.created_at ? new Date(activity.created_at).toLocaleString() : 'N/A'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
        container.innerHTML = tableHtml;
    }

    function renderAgentsVisitsVsMeetings(agentsData) {
        const chartContainer = document.getElementById('agentsVisitsMeetingsChart');
        const tableContainer = document.getElementById('agents-visits-meetings-table');
        
        if (!agentsData || !Array.isArray(agentsData) || agentsData.length === 0) {
            if (tableContainer) {
                tableContainer.innerHTML = '<p class="text-[#B3B5B4]">No agent data available</p>';
            }
            return;
        }

        // Destroy existing chart if it exists
        if (agentsVisitsMeetingsChart) {
            agentsVisitsMeetingsChart.destroy();
        }

        // Prepare data for chart
        const labels = agentsData.map(agent => agent.agent_name || 'Unknown');
        const meetingsData = agentsData.map(agent => agent.meetings || 0);
        const visitsData = agentsData.map(agent => agent.visits || 0);
        const closersData = agentsData.map(agent => agent.closers || 0);

        // Create chart
        if (chartContainer) {
            agentsVisitsMeetingsChart = new Chart(chartContainer, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Meetings',
                            data: meetingsData,
                            backgroundColor: '#3B82F6',
                            borderColor: '#2563EB',
                            borderWidth: 1
                        },
                        {
                            label: 'Visits',
                            data: visitsData,
                            backgroundColor: '#10B981',
                            borderColor: '#059669',
                            borderWidth: 1
                        },
                        {
                            label: 'Closers',
                            data: closersData,
                            backgroundColor: '#F59E0B',
                            borderColor: '#D97706',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    }
                },
                plugins: [{
                    id: 'showValues',
                    afterDatasetsDraw: (chart) => {
                        const ctx = chart.ctx;
                        ctx.save();
                        ctx.font = 'bold 12px Arial';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';
                        ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--text-color').trim() || '#063A1C';
                        
                        chart.data.datasets.forEach((dataset, i) => {
                            const meta = chart.getDatasetMeta(i);
                            meta.data.forEach((bar, index) => {
                                const value = dataset.data[index];
                                if (value > 0) {
                                    ctx.fillText(value, bar.x, bar.y - 5);
                                }
                            });
                        });
                        ctx.restore();
                    }
                }]
            });
        }

        // Render table
        if (tableContainer) {
            const tableHtml = `
                <table>
                    <thead>
                        <tr>
                            <th>Agent Name</th>
                            <th>Role</th>
                            <th>Meetings</th>
                            <th>Visits</th>
                            <th>Closers</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${agentsData.map(agent => {
                            const total = (agent.meetings || 0) + (agent.visits || 0) + (agent.closers || 0);
                            return `
                                <tr>
                                    <td>${agent.agent_name || 'Unknown'}</td>
                                    <td><span class="badge">${agent.role || 'N/A'}</span></td>
                                    <td>${agent.meetings || 0}</td>
                                    <td>${agent.visits || 0}</td>
                                    <td>${agent.closers || 0}</td>
                                    <td class="font-semibold">${total}</td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            `;
            tableContainer.innerHTML = tableHtml;
        }
    }

    function renderPropertySegments(segmentsData) {
        const chartContainer = document.getElementById('propertySegmentsChart');
        const legendContainer = document.getElementById('property-segments-legend');
        
        if (!segmentsData || Object.keys(segmentsData).length === 0) {
            if (legendContainer) {
                legendContainer.innerHTML = '<p class="text-[#B3B5B4]">No property segment data available</p>';
            }
            return;
        }

        // Destroy existing chart if it exists
        if (propertySegmentsChart) {
            propertySegmentsChart.destroy();
        }

        // Prepare data
        const labels = ['Plot', 'Commercial', 'Residential', 'Other'];
        const data = [
            segmentsData.plot || 0,
            segmentsData.commercial || 0,
            segmentsData.residential || 0,
            segmentsData.other || 0
        ];
        const colors = ['#F59E0B', '#3B82F6', '#10B981', '#6B7280'];
        const total = data.reduce((sum, val) => sum + val, 0);

        // Create donut chart
        if (chartContainer) {
            propertySegmentsChart = new Chart(chartContainer, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Render legend
        if (legendContainer) {
            const legendHtml = labels.map((label, index) => {
                const value = data[index];
                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                return `
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded" style="background-color: ${colors[index]}"></div>
                        <span class="text-sm text-brand-primary font-medium">${label}:</span>
                        <span class="text-sm font-bold text-brand-primary">${value}</span>
                        <span class="text-xs text-[#B3B5B4]">(${percentage}%)</span>
                    </div>
                `;
            }).join('');
            legendContainer.innerHTML = legendHtml;
        }
    }

    function renderTelecallerPerformance(telecallersData) {
        const container = document.getElementById('telecaller-performance-cards');
        
        if (!telecallersData || !Array.isArray(telecallersData) || telecallersData.length === 0) {
            if (container) {
                container.innerHTML = '<p class="text-[#B3B5B4]">No sales executive performance data available</p>';
            }
            return;
        }

        const cardsHtml = telecallersData.map(telecaller => {
            // Use same card style as CRM dashboard
            const allocated = (telecaller.allocated !== undefined && telecaller.allocated !== null) ? telecaller.allocated : 0;
            const called = (telecaller.called !== undefined && telecaller.called !== null) ? telecaller.called : 0;
            const remaining = (telecaller.remaining !== undefined && telecaller.remaining !== null) ? telecaller.remaining : 0;
            const interested = (telecaller.interested !== undefined && telecaller.interested !== null) ? telecaller.interested : 0;
            const notInterested = (telecaller.not_interested !== undefined && telecaller.not_interested !== null) ? telecaller.not_interested : 0;
            const cnp = (telecaller.cnp !== undefined && telecaller.cnp !== null) ? telecaller.cnp : 0;
            
            return `
                <div class="rounded-lg p-6 text-white shadow-md hover:shadow-lg transition-shadow" style="background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));">
                    <h3 class="text-xl font-bold mb-4 text-center text-white">${telecaller.telecaller_name || 'Unknown'}</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <span class="text-sm opacity-90 block">Allocated</span>
                            <span class="text-lg font-bold block">${allocated}</span>
                        </div>
                        <div>
                            <span class="text-sm opacity-90 block">Called</span>
                            <span class="text-lg font-bold block">${called}</span>
                        </div>
                        <div>
                            <span class="text-sm opacity-90 block">Remaining</span>
                            <span class="text-lg font-bold block">${remaining}</span>
                        </div>
                        <div>
                            <span class="text-sm opacity-90 block">Interested</span>
                            <span class="text-lg font-bold block" style="color: #90ee90;">${interested}</span>
                        </div>
                        <div>
                            <span class="text-sm opacity-90 block">Not Interested</span>
                            <span class="text-lg font-bold block" style="color: #ffb3b3;">${notInterested}</span>
                        </div>
                        <div>
                            <span class="text-sm opacity-90 block">CNP</span>
                            <span class="text-lg font-bold block text-white">${cnp}</span>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        if (container) {
            container.innerHTML = cardsHtml;
        }
    }

    function formatAssignedAt(isoString, nowIso) {
        if (!isoString) return '—';
        const d = new Date(isoString);
        if (isNaN(d.getTime())) return isoString;
        const now = nowIso ? new Date(nowIso) : new Date();
        const diffMs = now - d;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        if (diffMins < 60) return diffMins <= 1 ? '1m ago' : diffMins + 'm ago';
        if (diffHours < 24) return diffHours === 1 ? '1h ago' : diffHours + 'h ago';
        if (diffDays < 7) return diffDays === 1 ? '1d ago' : diffDays + 'd ago';
        return d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function formatAssignedAtFull(isoString) {
        if (!isoString) return '—';
        const d = new Date(isoString);
        if (isNaN(d.getTime())) return isoString;
        return d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function maskPhone(phone) {
        if (!phone || typeof phone !== 'string') return '—';
        const digits = phone.replace(/\D/g, '');
        if (digits.length < 4) return '****';
        return digits.slice(0, 2) + '****' + digits.slice(-4);
    }

    function formatAvgResponseTime(avgResponseMinutes) {
        if (avgResponseMinutes == null || avgResponseMinutes === 0 || isNaN(avgResponseMinutes)) return '0 min';
        const m = Math.round(Number(avgResponseMinutes));
        if (m < 60) return m + ' min';
        const h = Math.floor(m / 60);
        const min = m % 60;
        return min > 0 ? (h + 'h ' + min + 'm') : (h + 'h');
    }

    function renderAverageResponseTime(list) {
        const panel = document.getElementById('average-response-time-panel');
        if (!panel) return;
        let html = '<table style="width: 100%; border-collapse: collapse; font-size: 14px;"><thead><tr style="background: #F7F6F3; border-bottom: 2px solid #E5DED4;"><th style="padding: 8px 12px; text-align: left; font-weight: 600; color: var(--text-color);">User Name</th><th style="padding: 8px 12px; text-align: right; font-weight: 600; color: var(--text-color);">Avg Time</th></tr></thead><tbody>';
        if (!list || list.length === 0) {
            html += '<tr><td colspan="2" style="padding: 12px; text-align: center; color: #B3B5B4;">No users in this role.</td></tr>';
        } else {
            list.forEach(function(row) {
                const name = escapeHtml(row.user_name || '');
                const timeStr = formatAvgResponseTime(row.avg_response_minutes);
                html += '<tr style="border-bottom: 1px solid #eee;"><td style="padding: 8px 12px;">' + name + '</td><td style="padding: 8px 12px; text-align: right; font-weight: 600; color: var(--text-color);">' + timeStr + '</td></tr>';
            });
        }
        html += '</tbody></table>';
        panel.innerHTML = html;
    }

    function renderLeadsPendingResponse(data, serverNow) {
        const tbody = document.getElementById('leads-pending-response-tbody');
        if (!tbody) return;

        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="padding: 20px; text-align: center; color: #B3B5B4;">No leads pending response.</td></tr>';
            return;
        }

        const leadShowBase = "{{ url('/leads') }}";
        let html = '';
        data.forEach((row, index) => {
            const leads = row.leads || [];
            const oldestAssignedAt = leads.length > 0
                ? leads.reduce((min, l) => (!l.assigned_at ? min : (!min || l.assigned_at < min ? l.assigned_at : min)), null)
                : null;
            const oldestAssign = oldestAssignedAt ? formatAssignedAt(oldestAssignedAt, serverNow) : '—';
            const rowId = 'pending-row-' + row.user_id;
            const detailId = 'pending-detail-' + row.user_id;
            html += `
                <tr class="leads-pending-user-row" data-user-id="${row.user_id}" style="background: white; border-bottom: 1px solid #E5DED4; cursor: pointer;" onclick="toggleLeadsPendingDetail('${detailId}', '${rowId}')">
                    <td style="padding: 12px;"><i class="fas fa-chevron-right leads-pending-chevron" id="chevron-${rowId}" style="color: #B3B5B4;"></i></td>
                    <td style="padding: 12px; font-weight: 500;">${escapeHtml(row.user_name || '')}</td>
                    <td style="padding: 12px; text-align: center;">${row.pending_count || 0}</td>
                    <td style="padding: 12px; color: #666;">${oldestAssign}</td>
                </tr>
                <tr id="${detailId}" class="leads-pending-detail-row" style="display: none;">
                    <td colspan="4" style="padding: 0; border-bottom: 1px solid #E5DED4; background: #FAFAF9;">
                        <div style="padding: 12px 12px 12px 48px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom: 1px solid #E5DED4;">
                                        <th style="padding: 8px 12px; text-align: left; font-weight: 600; color: #666; font-size: 12px;">Lead Name</th>
                                        <th style="padding: 8px 12px; text-align: left; font-weight: 600; color: #666; font-size: 12px;">Phone</th>
                                        <th style="padding: 8px 12px; text-align: left; font-weight: 600; color: #666; font-size: 12px;">Assigned At</th>
                                        <th style="padding: 8px 12px; width: 80px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${(row.leads || []).length === 0 ? '<tr><td colspan="4" style="padding: 12px; text-align: center; color: #B3B5B4;">No pending leads.</td></tr>' : (row.leads || []).map(lead => `
                                        <tr style="border-bottom: 1px solid #eee;">
                                            <td style="padding: 8px 12px;">${escapeHtml(lead.name || '—')}</td>
                                            <td style="padding: 8px 12px;">${maskPhone(lead.phone)}</td>
                                            <td style="padding: 8px 12px;">${formatAssignedAtFull(lead.assigned_at)}</td>
                                            <td style="padding: 8px 12px;"><a href="${leadShowBase}/${lead.lead_id}" style="color: var(--primary-color); font-size: 12px;">View</a></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function toggleLeadsPendingDetail(detailId, rowId) {
        const detailRow = document.getElementById(detailId);
        const chevron = document.getElementById('chevron-' + rowId);
        if (!detailRow || !chevron) return;
        const isHidden = detailRow.style.display === 'none';
        detailRow.style.display = isHidden ? 'table-row' : 'none';
        chevron.className = isHidden ? 'fas fa-chevron-down leads-pending-chevron' : 'fas fa-chevron-right leads-pending-chevron';
        if (chevron.style) chevron.style.color = '#B3B5B4';
    }

    function escapeHtml(text) {
        if (text == null) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // User Visits & Meetings Functions
    let currentVisitsMeetingsFilter = localStorage.getItem('visitsMeetingsFilter') || 'this_month';
    let visitsMeetingsSortColumn = 'total';
    let visitsMeetingsSortDirection = 'desc';

    // Initialize filter button on page load
    document.addEventListener('DOMContentLoaded', function() {
        const activeBtn = document.querySelector(`.visits-meetings-filter-btn[data-filter="${currentVisitsMeetingsFilter}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }
    });

    async function loadUserVisitsMeetings(filter) {
        try {
            currentVisitsMeetingsFilter = filter;
            localStorage.setItem('visitsMeetingsFilter', filter);

            // Update active button
            document.querySelectorAll('.visits-meetings-filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            const activeBtn = document.querySelector(`.visits-meetings-filter-btn[data-filter="${filter}"]`);
            if (activeBtn) {
                activeBtn.classList.add('active');
            }

            // Show loading state
            const tableBody = document.getElementById('visits-meetings-table-body');
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="5" style="padding: 20px; text-align: center; color: #B3B5B4;">Loading...</td></tr>';
            }

            // Build query parameters
            const params = new URLSearchParams();
            params.append('visits_meetings_filter', filter);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const response = await fetch(API_BASE_URL + '?' + params.toString(), {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Failed to load visits/meetings data');
            }

            const data = await response.json();
            
            if (data.user_visits_meetings) {
                renderVisitsMeetingsSummary(data.user_visits_meetings.summary);
                renderVisitsMeetingsTable(data.user_visits_meetings.users);
            } else {
                renderVisitsMeetingsSummary({ total_users: 0, total_visits: 0, total_meetings: 0 });
                renderVisitsMeetingsTable([]);
            }
        } catch (error) {
            console.error('Error loading visits/meetings data:', error);
            const tableBody = document.getElementById('visits-meetings-table-body');
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="5" style="padding: 20px; text-align: center; color: #ef4444;">Error loading data. Please try again.</td></tr>';
            }
        }
    }

    function filterVisitsMeetings(filter, buttonElement) {
        loadUserVisitsMeetings(filter);
    }

    function renderVisitsMeetingsSummary(summary) {
        const totalUsersEl = document.getElementById('visits-meetings-total-users');
        const totalVisitsEl = document.getElementById('visits-meetings-total-visits');
        const totalMeetingsEl = document.getElementById('visits-meetings-total-meetings');

        if (totalUsersEl) totalUsersEl.textContent = summary.total_users || 0;
        if (totalVisitsEl) totalVisitsEl.textContent = summary.total_visits || 0;
        if (totalMeetingsEl) totalMeetingsEl.textContent = summary.total_meetings || 0;
    }

    function renderVisitsMeetingsTable(users) {
        const tableBody = document.getElementById('visits-meetings-table-body');
        
        if (!tableBody) return;

        if (!users || users.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" style="padding: 20px; text-align: center; color: #B3B5B4;">No data available</td></tr>';
            return;
        }

        // Sort data
        const sortedUsers = [...users].sort((a, b) => {
            let aVal, bVal;
            switch(visitsMeetingsSortColumn) {
                case 'user_name':
                    aVal = a.user_name || '';
                    bVal = b.user_name || '';
                    return visitsMeetingsSortDirection === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                case 'role':
                    aVal = a.role || '';
                    bVal = b.role || '';
                    return visitsMeetingsSortDirection === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                case 'visits_count':
                    aVal = a.visits_count || 0;
                    bVal = b.visits_count || 0;
                    return visitsMeetingsSortDirection === 'asc' ? aVal - bVal : bVal - aVal;
                case 'meetings_count':
                    aVal = a.meetings_count || 0;
                    bVal = b.meetings_count || 0;
                    return visitsMeetingsSortDirection === 'asc' ? aVal - bVal : bVal - aVal;
                case 'total':
                default:
                    aVal = a.total || 0;
                    bVal = b.total || 0;
                    return visitsMeetingsSortDirection === 'asc' ? aVal - bVal : bVal - aVal;
            }
        });

        const rowsHtml = sortedUsers.map(user => `
            <tr style="border-bottom: 1px solid #E5DED4;" onmouseover="this.style.background='#F7F6F3'" onmouseout="this.style.background='transparent'">
                <td style="padding: 12px; color: var(--text-color);">${user.user_name || 'N/A'}</td>
                <td style="padding: 12px; color: var(--text-color);">${user.role || 'N/A'}</td>
                <td style="padding: 12px; text-align: center; color: var(--text-color); font-weight: 600;">${user.visits_count || 0}</td>
                <td style="padding: 12px; text-align: center; color: var(--text-color); font-weight: 600;">${user.meetings_count || 0}</td>
                <td style="padding: 12px; text-align: center; color: var(--text-color); font-weight: 700;">${user.total || 0}</td>
            </tr>
        `).join('');

        tableBody.innerHTML = rowsHtml;
    }

    function sortTable(column) {
        if (visitsMeetingsSortColumn === column) {
            visitsMeetingsSortDirection = visitsMeetingsSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            visitsMeetingsSortColumn = column;
            visitsMeetingsSortDirection = 'desc';
        }

        // Reload table with new sort
        loadUserVisitsMeetings(currentVisitsMeetingsFilter);
    }

    // Initial load
    loadDashboardData();

    // Auto-refresh every 30 seconds
    setInterval(loadDashboardData, 30000);

    // Export dropdown toggle
    document.addEventListener('DOMContentLoaded', function() {
        const exportBtn = document.querySelector('.dropdown button');
        const dropdownMenu = document.querySelector('.dropdown-menu');
        
        if (exportBtn && dropdownMenu) {
            exportBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.style.display = dropdownMenu.style.display === 'none' ? 'block' : 'none';
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!exportBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.style.display = 'none';
                }
            });
        }
    });
    
    // Call Statistics Functions
    let callsByRoleChart = null;
    let outcomeDistributionChart = null;
    let currentCallStatsFilter = 'today';
    
    async function loadCallStatistics(filter = 'today', buttonElement = null) {
        currentCallStatsFilter = filter;
        localStorage.setItem('callStatsFilter', filter);
        
        // Update button states
        if (buttonElement) {
            document.querySelectorAll('.call-stats-filter-btn').forEach(btn => {
                btn.classList.remove('active');
                btn.style.background = 'white';
                btn.style.color = 'var(--text-color)';
            });
            buttonElement.classList.add('active');
            buttonElement.style.background = 'var(--primary-color)';
            buttonElement.style.color = 'white';
        }
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const params = new URLSearchParams();
            params.append('date_range', filter);
            
            const response = await fetch(API_BASE_URL + '?' + params.toString(), {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });
            
            if (!response.ok) {
                throw new Error('Failed to load call statistics');
            }
            
            const data = await response.json();
            
            if (data.call_statistics) {
                updateCallStatistics(data.call_statistics);
            }
        } catch (error) {
            console.error('Error loading call statistics:', error);
        }
    }
    
    function updateCallStatistics(stats) {
        // Update summary cards
        const totalEl = document.getElementById('call-stats-total');
        if (totalEl) totalEl.textContent = stats.total_calls || 0;
        
        const durationEl = document.getElementById('call-stats-duration');
        if (durationEl) durationEl.textContent = stats.formatted_duration || '0s';
        
        const avgDurationEl = document.getElementById('call-stats-avg-duration');
        if (avgDurationEl) avgDurationEl.textContent = stats.formatted_average_duration || '0s';
        
        const connectionRateEl = document.getElementById('call-stats-connection-rate');
        if (connectionRateEl) connectionRateEl.textContent = (stats.connection_rate || 0).toFixed(1) + '%';
        
        // Update Calls by Role Chart
        if (stats.calls_by_role && stats.calls_by_role.length > 0) {
            updateCallsByRoleChart(stats.calls_by_role);
        }
        
        // Update Outcome Distribution Chart
        if (stats.outcome_distribution && stats.outcome_distribution.length > 0) {
            updateOutcomeDistributionChart(stats.outcome_distribution);
        }
        
        // Update Top Users Table
        if (stats.top_users && stats.top_users.length > 0) {
            const tbody = document.getElementById('top-users-table-body');
            if (tbody) {
                tbody.innerHTML = stats.top_users.map(user => `
                    <tr style="border-bottom: 1px solid #E5DED4;">
                        <td style="padding: 12px; color: var(--text-color);">${user.user_name}</td>
                        <td style="padding: 12px; text-align: center; color: var(--text-color);">${user.total_calls}</td>
                        <td style="padding: 12px; text-align: center; color: var(--text-color);">${user.formatted_duration}</td>
                        <td style="padding: 12px; text-align: center; color: var(--text-color);">${user.formatted_average_duration}</td>
                    </tr>
                `).join('');
            }
            const topUsersSection = document.getElementById('top-users-section');
            if (topUsersSection) topUsersSection.style.display = 'block';
        }
        
        // Update Recent Calls
        if (stats.recent_calls && stats.recent_calls.length > 0) {
            const list = document.getElementById('recent-calls-list');
            if (list) {
                list.innerHTML = stats.recent_calls.map(call => `
                    <div style="padding: 12px; border-bottom: 1px solid #E5DED4; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 600; color: var(--text-color);">${call.lead_name}</div>
                            <div style="font-size: 12px; color: #B3B5B4;">${call.phone_number} • ${call.duration} • ${new Date(call.start_time).toLocaleString()}</div>
                        </div>
                        <a href="/calls/${call.id}" style="color: var(--link-color); text-decoration: none;">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                `).join('');
            }
            const recentCallsSection = document.getElementById('recent-calls-section');
            if (recentCallsSection) recentCallsSection.style.display = 'block';
        }
    }
    
    function updateCallsByRoleChart(data) {
        const ctx = document.getElementById('callsByRoleChart');
        if (!ctx) return;
        
        if (callsByRoleChart) {
            callsByRoleChart.destroy();
        }
        
        const labels = data.map(item => item.role_name);
        const callCounts = data.map(item => item.call_count);
        
        callsByRoleChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Calls',
                    data: callCounts,
                    backgroundColor: [
                        'rgba(32, 90, 68, 0.6)',
                        'rgba(6, 58, 28, 0.6)',
                        'rgba(21, 128, 61, 0.6)',
                        'rgba(179, 181, 180, 0.6)',
                    ],
                    borderColor: [
                        'rgba(32, 90, 68, 1)',
                        'rgba(6, 58, 28, 1)',
                        'rgba(21, 128, 61, 1)',
                        'rgba(179, 181, 180, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
    
    function updateOutcomeDistributionChart(data) {
        const ctx = document.getElementById('outcomeDistributionChart');
        if (!ctx) return;
        
        if (outcomeDistributionChart) {
            outcomeDistributionChart.destroy();
        }
        
        const labels = data.map(item => item.label);
        const counts = data.map(item => item.count);
        
        outcomeDistributionChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: [
                        'rgba(32, 90, 68, 0.6)',
                        'rgba(239, 68, 68, 0.6)',
                        'rgba(59, 130, 246, 0.6)',
                        'rgba(234, 179, 8, 0.6)',
                        'rgba(168, 85, 247, 0.6)',
                        'rgba(107, 114, 128, 0.6)',
                    ],
                    borderColor: [
                        'rgba(32, 90, 68, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(234, 179, 8, 1)',
                        'rgba(168, 85, 247, 1)',
                        'rgba(107, 114, 128, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    // Real-time call log updates via Pusher
    @if(config('broadcasting.default') === 'pusher')
    document.addEventListener('DOMContentLoaded', function() {
        const pusher = new Pusher('{{ config("broadcasting.connections.pusher.key") }}', {
            cluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}',
            encrypted: true
        });
        
        const callLogsChannel = pusher.subscribe('call-logs');
        callLogsChannel.bind('call-log.created', function(data) {
            // Reload call statistics
            if (typeof loadCallStatistics === 'function') {
                loadCallStatistics(currentCallStatsFilter);
            }
        });
        
        // Subscribe to admin channel
        const adminChannel = pusher.subscribe('private-admin');
        adminChannel.bind('call-log.created', function(data) {
            if (typeof loadCallStatistics === 'function') {
                loadCallStatistics(currentCallStatsFilter);
            }
        });
    });
    @endif
</script>
@endpush
