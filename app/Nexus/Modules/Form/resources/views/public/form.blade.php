{{--
    Deliberately unstyled beyond a `nexus-form` BEM prefix — this partial is
    embedded into an arbitrary host site's own front-end (via @nexusForm()),
    not the Tailwind-based tailadmin admin theme, so it must not assume any
    particular CSS framework is present on the public side.
--}}
<div class="nexus-form" id="nexus-form-{{ $form->slug }}">
    @if (session('nexus_form_success'))
        <div class="nexus-form__success">{{ session('nexus_form_success') }}</div>
    @endif

    <form class="nexus-form__form" method="POST" action="{{ route('nexus.form.submit', $form->slug) }}">
        @csrf

        {{-- Honeypot: left empty by humans, filled by most naive bots. --}}
        <div class="nexus-form__honeypot" aria-hidden="true" style="position:absolute;left:-9999px;">
            <label for="nexus-form-hp-{{ $form->slug }}">Leave this field empty</label>
            <input type="text" id="nexus-form-hp-{{ $form->slug }}" name="_hp" tabindex="-1" autocomplete="off">
        </div>

        @foreach ($form->fields as $field)
            <div class="nexus-form__field nexus-form__field--{{ $field->type }}">
                <label class="nexus-form__label" for="nexus-form-{{ $form->slug }}-{{ $field->key }}">
                    {{ $field->label }}@if ($field->required) <span class="nexus-form__required">*</span> @endif
                </label>

                @switch($field->type)
                    @case('textarea')
                        <textarea
                            class="nexus-form__input"
                            id="nexus-form-{{ $form->slug }}-{{ $field->key }}"
                            name="{{ $field->key }}"
                            @if ($field->required) required @endif
                        >{{ old($field->key) }}</textarea>
                        @break

                    @case('select')
                        <select
                            class="nexus-form__input"
                            id="nexus-form-{{ $form->slug }}-{{ $field->key }}"
                            name="{{ $field->key }}"
                            @if ($field->required) required @endif
                        >
                            <option value="">—</option>
                            @foreach ($field->optionsList() as $option)
                                <option value="{{ $option }}" @selected(old($field->key) === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        @break

                    @case('radio')
                        @foreach ($field->optionsList() as $i => $option)
                            <label class="nexus-form__choice">
                                <input
                                    type="radio"
                                    name="{{ $field->key }}"
                                    value="{{ $option }}"
                                    @checked(old($field->key) === $option)
                                    @if ($field->required && $i === 0) required @endif
                                >
                                {{ $option }}
                            </label>
                        @endforeach
                        @break

                    @case('checkbox')
                        @foreach ($field->optionsList() as $option)
                            <label class="nexus-form__choice">
                                <input
                                    type="checkbox"
                                    name="{{ $field->key }}[]"
                                    value="{{ $option }}"
                                    @checked(in_array($option, (array) old($field->key, []), true))
                                >
                                {{ $option }}
                            </label>
                        @endforeach
                        @break

                    @default
                        <input
                            class="nexus-form__input"
                            type="{{ $field->type === 'number' ? 'number' : ($field->type === 'date' ? 'date' : ($field->type === 'email' ? 'email' : 'text')) }}"
                            id="nexus-form-{{ $form->slug }}-{{ $field->key }}"
                            name="{{ $field->key }}"
                            value="{{ old($field->key) }}"
                            @if ($field->required) required @endif
                        >
                @endswitch

                {{--
                    Not @error() — that directive hard-requires $errors to be
                    in scope, which only holds inside a real HTTP request
                    (ShareErrorsFromSession middleware). This partial can also
                    render through Blade::render() (ad-hoc string templates,
                    e.g. in tests), where nothing shares $errors, so look it
                    up defensively instead.
                --}}
                @php $nexusFieldError = ($errors ?? null)?->first($field->key); @endphp
                @if ($nexusFieldError)
                    <div class="nexus-form__error">{{ $nexusFieldError }}</div>
                @endif
            </div>
        @endforeach

        <button class="nexus-form__submit" type="submit">{{ __('form::translate.submission.submit_label') }}</button>
    </form>
</div>
