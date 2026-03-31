@extends('layouts.app')

@section('title', 'Create Lead - Base CRM')
@section('page-title', 'Create Lead')

@php
    $formFields = collect($dynamicForm?->fields ?? $fallbackFields ?? [])->sortBy('order')->values();
    $fieldsBySection = $formFields->groupBy(function ($field) {
        return is_array($field) ? ($field['section'] ?? 'Details') : ($field->section ?? 'Details');
    });

    $getFieldValue = function ($field, string $key, $default = null) {
        if (is_array($field)) {
            return $field[$key] ?? $default;
        }

        return $field->{$key} ?? $default;
    };

    $runtimeOptions = [
        'preferred_location' => ['Shaheed Path', 'Sultanpur Road', 'Kanpur Road', 'Bijnore Road', 'IIM Road', 'Faizabad Road', 'Outer Ring Road', 'Sushant Golf City', 'Other'],
        'budget' => ['Under ₹1 Cr', '₹1.1 Cr – ₹2 Cr', 'Above ₹2 Cr'],
        'source' => array_map('strval', array_values(\App\Models\Lead::sourceOptions())),
        'use_end_use' => ['End User', '2nd Investments'],
        'property_type' => ['Apartment', 'Villa', 'Plot', 'Commercial', 'Other'],
        'possession_status' => ['Ready to Move', 'Under Construction'],
        'preferred_projects' => $projects->pluck('name', 'id')->mapWithKeys(fn ($label, $id) => [(string) $id => $label])->all(),
        'assigned_to' => $users->mapWithKeys(fn ($item) => [(string) $item->id => $item->name . ' (' . ($item->role->name ?? 'User') . ')'])->all(),
    ];

    $runtimeValues = [
        'property_type' => [
            'Apartment' => 'apartment',
            'Villa' => 'villa',
            'Plot' => 'plot',
            'Commercial' => 'commercial',
            'Other' => 'other',
        ],
        'source' => array_flip(\App\Models\Lead::sourceOptions()),
    ];

    $multipleFields = ['preferred_projects'];
@endphp

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8">
        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('leads.store') }}" class="space-y-8">
            @csrf

            @foreach($fieldsBySection as $sectionName => $sectionFields)
                @continue(!$showLocationDetails && $sectionName === 'Location Details')

                <section>
                    <div class="mb-5 pb-2 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">{{ $sectionName }}</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($sectionFields as $field)
                            @php
                                $fieldKey = (string) $getFieldValue($field, 'field_key');
                                $fieldType = (string) $getFieldValue($field, 'field_type', 'text');
                                $label = (string) $getFieldValue($field, 'label', ucfirst(str_replace('_', ' ', $fieldKey)));
                                $placeholder = (string) $getFieldValue($field, 'placeholder', '');
                                $required = (bool) $getFieldValue($field, 'required', false);
                                $helpText = $getFieldValue($field, 'help_text');
                                $options = $getFieldValue($field, 'options', []);
                                $options = is_array($options) && !empty($options) ? $options : ($runtimeOptions[$fieldKey] ?? []);
                                $isMultiple = in_array($fieldKey, $multipleFields, true);
                                $selectedValues = $isMultiple
                                    ? array_map('strval', old($fieldKey, []))
                                    : [(string) old($fieldKey, (string) $getFieldValue($field, 'default_value', ''))];
                                $columnClass = in_array($fieldType, ['textarea'], true) ? 'md:col-span-2' : '';
                            @endphp

                            <div class="{{ $columnClass }}">
                                <label for="{{ $fieldKey }}" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ $label }}
                                    @if($required)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>

                                @if($helpText)
                                    <p class="text-xs text-gray-500 mb-2">{{ $helpText }}</p>
                                @endif

                                @if($fieldType === 'textarea')
                                    <textarea
                                        name="{{ $fieldKey }}"
                                        id="{{ $fieldKey }}"
                                        rows="3"
                                        placeholder="{{ $placeholder }}"
                                        class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]"
                                        @if($required) required @endif
                                    >{{ old($fieldKey, (string) $getFieldValue($field, 'default_value', '')) }}</textarea>
                                @elseif($fieldType === 'select' || $fieldKey === 'assigned_to')
                                    <select
                                        name="{{ $fieldKey }}{{ $isMultiple ? '[]' : '' }}"
                                        id="{{ $fieldKey }}"
                                        class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]"
                                        @if($required || ($fieldKey === 'source' && auth()->user()->isCrm())) required @endif
                                        @if($isMultiple) multiple size="5" @endif
                                    >
                                        @unless($isMultiple)
                                            <option value="">
                                                {{ $fieldKey === 'assigned_to' ? "-- Don't Assign Now --" : '-- Select --' }}
                                            </option>
                                        @endunless

                                        @foreach($options as $optionValue => $optionLabel)
                                            @php
                                                $normalizedLabel = is_string($optionLabel) ? $optionLabel : (string) $optionValue;
                                                $optionValue = is_int($optionValue)
                                                    ? (string) ($runtimeValues[$fieldKey][$normalizedLabel] ?? $normalizedLabel)
                                                    : (string) ($runtimeValues[$fieldKey][$normalizedLabel] ?? $optionValue);
                                                $optionLabel = $normalizedLabel;
                                                $isSelected = in_array($optionValue, $selectedValues, true);
                                            @endphp
                                            <option value="{{ $optionValue }}" @selected($isSelected)>{{ $optionLabel }}</option>
                                        @endforeach
                                    </select>

                                    @if($fieldKey === 'preferred_projects')
                                        <p class="text-sm text-gray-500 mt-2">Ctrl/Cmd ke bina bhi multiple projects choose kiye ja sakte hain.</p>
                                    @elseif($fieldKey === 'assigned_to')
                                        <p class="text-sm text-gray-500 mt-2">You can assign this lead now or later.</p>
                                    @endif
                                @else
                                    <input
                                        type="{{ in_array($fieldType, ['email', 'number', 'date'], true) ? $fieldType : 'text' }}"
                                        name="{{ $fieldKey }}"
                                        id="{{ $fieldKey }}"
                                        value="{{ old($fieldKey, (string) $getFieldValue($field, 'default_value', '')) }}"
                                        placeholder="{{ $placeholder }}"
                                        class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]"
                                        @if($required) required @endif
                                    >
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <div class="flex justify-end gap-4">
                <a href="{{ route('leads.index') }}"
                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200 font-medium">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 font-medium">
                    Create Lead
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const projectSelect = document.getElementById('preferred_projects');
        if (!projectSelect) {
            return;
        }

        projectSelect.addEventListener('mousedown', function (event) {
            if (event.ctrlKey || event.metaKey) {
                return;
            }

            const option = event.target;
            if (option.tagName !== 'OPTION') {
                return;
            }

            event.preventDefault();
            option.selected = !option.selected;
        });
    });
</script>
@endsection
