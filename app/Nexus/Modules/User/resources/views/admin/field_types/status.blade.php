@php
    $value = '';
    if(isset($tab_lang)){
        if(isset($model)){
            $value = old($field->name.'.'.$tab_lang, ($model->getTranslation($field->name, $tab_lang,false)));
        } else {
            $value = old($field->name.'.'.$tab_lang, $defaultValue);
        }
    } else {
        if(isset($model)){
            $value = old($field->name,($model->{$field->name}));
        } else {
            $value = old($field->name, $defaultValue);
        }
    }
    if(!isset($value)){
        $value = $defaultValue;
    }
@endphp

<div class="mb-3">
    <label for="{{$field->name}}" class="form-label">
        @if(isset($field->label) && !empty($field->label))
            @lang($moduleName.'::translate.'.$field->label)
        @else
            @lang($moduleName.'::translate.'.$field->name) {{$tab_lang??''}}
        @endif
        @if(in_array($field->name,$modelSchema['required']??[]))
            *
        @endif
    </label>

    <select class="form-control @error($field->name) is-invalid @enderror"
            data-choices
            data-choices-sorting-false
            @if(isset($tab_lang))
                name="{{$field->name}}[{{$tab_lang}}]"
            id="{{$field->name}}[{{$tab_lang}}]"
            @else
                name="{{$field->name}}"
            id="{{$field->name}}"
            @endif

            @if(in_array($field->name,$modelSchema['required']??[]))
                required
        @endif
    >
        @foreach($customData as $data)
            <option
                value="{{ $data->value }}"
                @if($data->value == $value) selected @endif
            >
                @lang($moduleName.'::translate.'.$data->name)
            </option>
        @endforeach
    </select>

    {!! $errors->first($field->name, '<small class="error invalid-feedback">:message</small>') !!}
</div>
