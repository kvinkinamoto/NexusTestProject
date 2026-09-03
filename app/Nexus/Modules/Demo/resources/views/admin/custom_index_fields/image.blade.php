{{--
    #[Column(customField: 'image')] demo — module-table.blade.php includes
    this with $item (the row's model) already in scope, plus the passed
    $fieldName. Mirrors livewire/field_types/image.blade.php's own
    public-root-relative path + no-image fallback convention.
--}}
<div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-lg bg-gray-100 dark:bg-white/5">
    <img src="/{{ ltrim($item->{$fieldName} ?? 'nexus/images/no-image.jpg', '/') }}" class="h-10 w-10 object-cover">
</div>
