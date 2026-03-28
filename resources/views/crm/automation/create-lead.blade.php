@extends('layouts.app')

@section('title', 'Create Lead - Base CRM')
@section('page-title', 'Create Lead')
@section('page-subtitle', 'Add a lead manually and optionally assign it to a user immediately.')

@section('content')
<div class="page-shell">
    <section class="crm-hero">
        <div class="crm-hero-grid">
            <div>
                <span class="crm-kicker">
                    <i class="fas fa-user-plus"></i>
                    Manual Lead Intake
                </span>
                <h2 class="crm-hero-title">Create a new lead in the <strong>same premium CRM workspace</strong>.</h2>
                <p class="crm-hero-copy">
                    Sirf Name aur Phone required hain. Lead create hone ke baad detailed requirement fields centrally fill ki ja sakti hain.
                </p>
            </div>
            <div class="crm-note">
                <strong>Note:</strong> Assign now karoge to calling task turant create ho jayega. Blank chhodoge to lead unassigned rahegi.
            </div>
        </div>
    </section>

    @if($errors->any())
        <div class="crm-note crm-note-warning">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <section class="crm-surface">
        <form method="POST" action="{{ route('crm.automation.leads.store') }}">
            @csrf
            <div class="crm-surface-header">
                <div>
                    <div class="crm-pill">Lead Form</div>
                    <h3 class="crm-section-title">Basic Information</h3>
                    <p class="crm-section-copy">Current lead creation logic unchanged. Sirf UI ko aligned workspace form me convert kiya gaya hai.</p>
                </div>
            </div>

            <div class="crm-form-grid">
                <div class="crm-field">
                    <label for="name">Name <span class="text-danger">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required placeholder="Enter lead name" class="crm-form-control w-100">
                </div>
                <div class="crm-field">
                    <label for="phone">Phone / Number <span class="text-danger">*</span></label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required placeholder="Enter phone number" class="crm-form-control w-100">
                </div>
                <div class="crm-field">
                    <label for="source">Lead Source <span class="text-danger">*</span></label>
                    <select name="source" id="source" required class="crm-form-control w-100">
                        <option value="">Select source</option>
                        @foreach(\App\Models\Lead::sourceOptions() as $value => $label)
                            <option value="{{ $value }}" {{ old('source') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="crm-field">
                    <label for="assigned_to">Assign To User</label>
                    <select name="assigned_to" id="assigned_to" class="crm-form-control w-100">
                        <option value="">Do not assign</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->role->name ?? $user->role->slug ?? '—' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="crm-inline-stack mt-4">
                <button type="submit" class="btn btn-brand-primary">Create Lead</button>
                <a href="{{ route('crm.automation.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </section>
</div>
@endsection
