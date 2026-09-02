@php
    $raw = old($field->name, isset($model) ? $model->{$field->name} : '');
    $value = $raw instanceof \Carbon\CarbonInterface ? $raw->format('Y-m-d') : (string) $raw;
    if ($value !== '' && strlen($value) > 10) {
        $value = substr($value, 0, 10);
    }
@endphp

<div class="mb-3">
    <label for="{{ $field->name }}" class="form-label">
        @if(isset($field->label) && !empty($field->label))
            @lang($module->name . '::translate.' . $field->label)
        @else
            @lang($module->name . '::translate.' . $field->name)
        @endif
    </label>

    <input type="date"
           class="form-control @error($field->name) is-invalid @enderror"
           id="{{ $field->name }}"
           name="{{ $field->name }}"
           value="{{ $value }}">

    {!! $errors->first($field->name, '<small class="error invalid-feedback">:message</small>') !!}
</div>
