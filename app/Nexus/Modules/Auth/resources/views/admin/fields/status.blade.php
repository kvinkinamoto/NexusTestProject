@php
    $value = '';
    if(isset($tab_lang)){
        if(isset($model)){
            $value = old($fieldName.'.'.$tab_lang, ($model->getTranslation($fieldName, $tab_lang,false)));
        } else {
            $value = old($fieldName.'.'.$tab_lang, $field->defaultValue);
        }
    } else {
        if(isset($model)){
            $value = old($fieldName,($model->{$fieldName}));
        } else {
            $value = old($fieldName, $field->defaultValue);
        }
    }
    if(!isset($value)){
        $value = $field->defaultValue;
    }
@endphp

<div class="mb-3">
    <label for="{{$fieldName}}" class="form-label">
        @if(isset($field->label) && !empty($field->label))
            @lang($moduleName.'::'.$moduleName.'.'.$field->label)
        @else
            @lang($moduleName.'::'.$moduleName.'.'.$fieldName) {{$tab_lang??''}}
        @endif
        @if(in_array($fieldName,$modelSchema['required']))
            *
        @endif
    </label>

    <select class="form-control @error($fieldName) is-invalid @enderror"
            data-choices
            data-choices-sorting-false
            @if(isset($tab_lang))
                name="{{$fieldName}}[{{$tab_lang}}]"
            id="{{$fieldName}}[{{$tab_lang}}]"
            @else
                name="{{$fieldName}}"
            id="{{$fieldName}}"
            @endif

            @if(in_array($fieldName,$modelSchema['required']))
                required
        @endif
    >
        @foreach($field->customData as $data)
            <option
                value="{{ $data['value'] }}"
                @if($data['value'] == $value) selected @endif
            >
                {{ __('Auth::Auth.status_' . $data['name']) }}
            </option>
        @endforeach
    </select>

    {!! $errors->first($fieldName, '<small class="error invalid-feedback">:message</small>') !!}
</div>
