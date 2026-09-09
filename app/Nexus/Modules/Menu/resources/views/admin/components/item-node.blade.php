{{-- Recursive: included by itself for every level of children. --}}
<ul class="nexus-menu-items-manager__list list-none pl-4">
    @foreach($items as $item)
        <li class="nexus-menu-items-manager__item mb-1" wire:key="menu-item-{{ $item->id }}">
            <div class="flex items-center gap-2 text-sm">
                <span>{{ $item->title }}</span>
                @if($item->url)
                    <span class="text-xs text-gray-400">({{ $item->url }})</span>
                @endif

                <button type="button" wire:click="moveUp({{ $item->id }})" title="Up" class="text-xs text-gray-500 hover:text-gray-800 dark:hover:text-white">&uarr;</button>
                <button type="button" wire:click="moveDown({{ $item->id }})" title="Down" class="text-xs text-gray-500 hover:text-gray-800 dark:hover:text-white">&darr;</button>
                <button type="button" wire:click="indent({{ $item->id }})" title="Indent" class="text-xs text-gray-500 hover:text-gray-800 dark:hover:text-white">&rarr;</button>
                <button type="button" wire:click="outdent({{ $item->id }})" title="Outdent" class="text-xs text-gray-500 hover:text-gray-800 dark:hover:text-white">&larr;</button>
                <button type="button" wire:click="removeItem({{ $item->id }})" wire:confirm="Remove this item?" title="Remove" class="text-xs text-error-500 hover:text-error-700">&times;</button>
            </div>

            @if($item->children->isNotEmpty())
                @include('menu::admin.components.item-node', ['items' => $item->children])
            @endif
        </li>
    @endforeach
</ul>
