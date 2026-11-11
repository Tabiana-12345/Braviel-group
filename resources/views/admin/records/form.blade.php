@extends('layouts.admin')

@section('title', $record ? 'Edit '.$module['name'] : 'Add '.$module['name'])

@section('content')
    <section class="page-heading">
        <div>
            <!-- <span class="form-kicker">{{ $record ? 'UPDATE RECORD' : 'NEW RECORD' }}</span> -->
            <h1>{{ $record ? 'Edit record' : 'Add record' }}</h1>
            <p>{{ $module['name'] }} · {{ $module['description'] }}</p>
        </div>
        <a class="admin-secondary-button" href="{{ route('admin.modules.index', $moduleKey) }}">Back to records</a>
    </section>

    <form class="admin-form-card" method="POST" action="{{ $record ? route('admin.modules.update', [$moduleKey, $record->id]) : route('admin.modules.store', $moduleKey) }}">
        @csrf
        @if ($record)
            @method('PUT')
        @endif

        <div class="form-card-heading">
            <div>
                <h2>{{ $module['name'] }} details</h2>
                <p>Enter the information below to {{ $record ? 'update' : 'add' }} this {{ strtolower($module['name']) }} record.</p>
            </div>
            <span class="form-required-note"><span aria-hidden="true">*</span> Required</span>
        </div>
        <div class="admin-form-grid">
            <div class="admin-field {{ in_array($moduleKey, ['brands', 'units'], true) ? '' : 'admin-field-full' }}">
                <label for="title">Name <span aria-hidden="true">*</span></label>
                <input id="title" name="title" type="text" maxlength="255" required value="{{ old('title', $record?->title) }}" autocomplete="off" aria-invalid="{{ $errors->has('title') ? 'true' : 'false' }}">
                @error('title')<span class="admin-field-error">{{ $message }}</span>@enderror
            </div>
            @foreach ($module['fields'] as $field)
                <div class="admin-field {{ $field['type'] === 'textarea' ? 'admin-field-full' : '' }}">
                    <label for="data-{{ $field['name'] }}">{{ $field['label'] }} @if ($field['required'] ?? false)<span aria-hidden="true">*</span>@endif</label>
                    @if ($field['type'] === 'select')
                        <select id="data-{{ $field['name'] }}" name="data[{{ $field['name'] }}]" @required($field['required'] ?? false) aria-invalid="{{ $errors->has('data.'.$field['name']) ? 'true' : 'false' }}">
                            <option value="">Select {{ strtolower($field['label']) }}</option>
                            @foreach ($fieldOptions[$field['name']] ?? [] as $value => $label)
                                <option value="{{ $value }}" @selected(old('data.'.$field['name'], $record?->data[$field['name']] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @if (empty($fieldOptions[$field['name']]))
                            <small class="field-help">Add {{ strtolower($field['label']) }} records first to select one.</small>
                        @endif
                    @elseif ($field['type'] === 'textarea')
                        <textarea id="data-{{ $field['name'] }}" name="data[{{ $field['name'] }}]" rows="3" @required($field['required'] ?? false) aria-invalid="{{ $errors->has('data.'.$field['name']) ? 'true' : 'false' }}">{{ old('data.'.$field['name'], $record?->data[$field['name']] ?? '') }}</textarea>
                    @else
                        <input id="data-{{ $field['name'] }}" name="data[{{ $field['name'] }}]" type="{{ $field['type'] }}" @required($field['required'] ?? false) @if ($field['type'] === 'number') step="any" @endif value="{{ old('data.'.$field['name'], $record?->data[$field['name']] ?? '') }}" aria-invalid="{{ $errors->has('data.'.$field['name']) ? 'true' : 'false' }}">
                    @endif
                    @error('data.'.$field['name'])<span class="admin-field-error">{{ $message }}</span>@enderror
                </div>
            @endforeach
        </div>
        <div class="admin-form-actions">
            <a class="admin-secondary-button" href="{{ route('admin.modules.index', $moduleKey) }}">Cancel</a>
            <button class="admin-primary-button" type="submit">{{ $record ? 'Save changes' : 'Add record' }}</button>
        </div>
    </form>
@endsection
