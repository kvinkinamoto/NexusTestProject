{{--
    #[Field(type: 'view', view: 'demo::admin.components.price-preview')] slot
    demo — a virtual field ($model, $field, $module passed in by
    dispatch.blade.php's 'view' branch), free-form Blade, no input or
    validation of its own.
--}}
<div class="mb-4 rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300">
    <p class="mb-1 font-medium text-gray-800 dark:text-white/90">{{ $field->label }}</p>
    <p>
        This block is injected via
        <code class="rounded bg-gray-100 px-1 py-0.5 text-xs dark:bg-white/10">#[Field(type: 'view', view: 'demo::admin.components.price-preview')]</code>
        — arbitrary Blade, no form input of its own.
    </p>
    @if($model?->exists)
        <p class="mt-2 text-xs text-gray-400">Editing record #{{ $model->getKey() }}, created {{ $model->created_at?->diffForHumans() }}.</p>
    @else
        <p class="mt-2 text-xs text-gray-400">New, unsaved record.</p>
    @endif
</div>
