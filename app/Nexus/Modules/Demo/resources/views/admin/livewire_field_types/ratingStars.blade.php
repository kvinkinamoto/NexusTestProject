{{--
    Custom field type demo — 'ratingStars' does not exist anywhere in
    dispatch.blade.php's built-in chain, so this view is its only
    implementation. Proves the module-scoped override hook added there
    (see dispatch.blade.php's top @if) can introduce a brand new type, not
    just override an existing one.
--}}
@php
    $errorKey = "data.{$field->name}";
    $current = (int) ($this->data[$field->name] ?? 0);
@endphp
<div class="mb-4">
    @include('nexus::' . config('nexus.template') . '.livewire.field_types._label', ['field' => $field])

    <div class="flex items-center gap-1">
        @for($i = 1; $i <= 5; $i++)
            <button type="button" wire:click="$set('data.{{ $field->name }}', {{ $i }})"
                @disabled($field->isDisabledForAction($action ?? null))
                class="text-2xl leading-none disabled:cursor-not-allowed disabled:opacity-50 {{ $i <= $current ? 'text-warning-500' : 'text-gray-300 dark:text-gray-700' }}">
                &#9733;
            </button>
        @endfor
        @if($current > 0)
            <button type="button" wire:click="$set('data.{{ $field->name }}', null)"
                class="ml-2 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                @lang('nexus::translate.cancel')
            </button>
        @endif
    </div>

    @error($errorKey)
        <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
    @enderror
</div>
