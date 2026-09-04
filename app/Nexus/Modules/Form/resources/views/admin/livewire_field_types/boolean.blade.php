{{--
    {module}::admin.livewire_field_types.{type} is ONE override slot shared
    by two different dispatchers (see dispatch.blade.php and repeater.blade.php's
    own docblocks): a top-level #[Field(type: 'boolean')] on the model itself
    (Form's own "is_active") AND a #[RepeaterField(type: 'boolean')] cell
    (Fields repeater's "required" column) both resolve to this exact same
    file, since there's no built-in repeater_cells/boolean.blade.php (only
    string, number, image, video — see repeater_cells/unsupported.blade.php).
    Distinguish the two call sites by $field->type itself rather than
    isset($column)/isset($index): Blade's compiled @include() forwards
    get_defined_vars() of the ENCLOSING template at the include site, and
    module-form.blade.php's per-field loop @includes dispatch.blade.php for
    every field from that one shared scope — so $column/$index, once set by
    an earlier repeater field's own thead loop in the same request, are
    still "isset" by the time a later sibling field (is_active) is
    dispatched, even though nothing repeater-related is happening for it.
    $field passed into a repeater CELL is always the parent repeater field
    itself (type 'repeater'), never the cell's own type, so checking that
    is unambiguous regardless of what's left over in scope.
--}}
@if($field->type === 'repeater')
    <input type="checkbox" wire:model="relationRows.{{ $field->name }}.{{ $index }}.{{ $column->name }}"
        class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
@else
    @include('nexus::' . config('nexus.template') . '.livewire.field_types.boolean', ['field' => $field])
@endif
