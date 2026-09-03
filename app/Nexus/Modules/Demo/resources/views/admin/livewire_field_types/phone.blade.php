{{--
    Overridden-field demo — this is a Demo-only replacement for the
    package's built-in field_types/phone.blade.php, picked up by the same
    module-scoped override hook that powers ratingStars.blade.php. Any
    other module's `type: 'phone'` field is untouched — this view only
    exists under app/Nexus/Modules/Demo, so View::exists() only finds it
    for Demo's own fields.
--}}
@php
    $errorKey = "data.{$field->name}";
    $inputClass = 'h-11 w-full rounded-lg border-2 border-dashed bg-transparent pl-9 pr-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:outline-hidden disabled:cursor-not-allowed disabled:opacity-50 dark:text-white/90 ' . ($errors->has($errorKey) ? 'border-error-500 focus:ring-3 focus:ring-error-500/10' : 'border-brand-300 focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 dark:border-brand-700');
@endphp
<div class="mb-4">
    @include('nexus::' . config('nexus.template') . '.livewire.field_types._label', ['field' => $field])

    <p class="mb-1.5 text-xs text-brand-500">Overridden view — demo::admin.livewire_field_types.phone</p>

    <div class="relative">
        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">&#9742;</span>
        <input type="tel" id="field-{{ $field->name }}" wire:model="data.{{ $field->name }}"
            @disabled($field->isDisabledForAction($action ?? null))
            class="{{ $inputClass }}">
    </div>

    @error($errorKey)
        <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
    @enderror
</div>
