{{--
    #[Field(type: 'view', view: 'menu::admin.components.items-manager')] slot
    on Menu — embeds the MenuItemsManager Livewire component (registered in
    App\Providers\AppServiceProvider::boot(), since it lives outside
    Livewire's default component namespace). Needs a real Menu id, so it only
    renders once the menu has been saved at least once.
--}}
<div class="mb-4">
    @if($model?->exists)
        @livewire('nexus-menu-items-manager', ['menuId' => $model->getKey()], key('menu-items-manager-'.$model->getKey()))
    @else
        <div class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
            {{ $field->label }} — save the menu first to add items.
        </div>
    @endif
</div>
