@php
    use App\Nexus\Modules\User\Enums\Gender;

    $value = old($field->name, $model?->gender?->value ?? '');
    $options = Gender::all();
@endphp

<div class="mb-3">
    <label for="{{ $field->name }}" class="form-label">
        @if(isset($field->label) && !empty($field->label))
            @lang($module->name . '::translate.' . $field->label)
        @else
            @lang($module->name . '::translate.' . $field->name)
        @endif
    </label>

    <select class="form-control @error($field->name) is-invalid @enderror"
            data-choices
            data-choices-sorting-false
            name="{{ $field->name }}"
            id="{{ $field->name }}">
        <option value="">—</option>
        @foreach($options as $opt)
            <option value="{{ $opt['value'] }}" @if($opt['value'] === $value) selected @endif>
                {{ $opt['label'] }}
            </option>
        @endforeach
    </select>

    {!! $errors->first($field->name, '<small class="error invalid-feedback">:message</small>') !!}
</div>
