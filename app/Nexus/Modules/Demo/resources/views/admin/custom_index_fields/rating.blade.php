{{--
    #[Column(customField: 'rating')] demo — shows the custom ratingStars
    field's value as static stars in the list table (no interactivity
    needed here, unlike the form field's own livewire/field_types override).
--}}
@php($value = (int) ($item->{$fieldName} ?? 0))
<span class="text-warning-500">
    @for($i = 1; $i <= 5; $i++)
        {{ $i <= $value ? '★' : '' }}
    @endfor
</span>
@if(! $value)
    <span class="text-gray-400">—</span>
@endif
