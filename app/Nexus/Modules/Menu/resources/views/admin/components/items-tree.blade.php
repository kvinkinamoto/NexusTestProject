<div class="nexus-menu-items-manager mb-4 rounded-lg border border-dashed border-gray-300 p-3 dark:border-gray-700">
    <div class="mb-2 flex flex-wrap items-end gap-2">
        @if(is_array($newTitle))
            {{-- More than one language is configured — one title input per locale, same convention as any #[Field(translated: true)] input. --}}
            @foreach($this->activeLocales() as $locale)
                <div class="flex items-center gap-1">
                    <span class="flex h-9 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs font-semibold uppercase text-gray-500 dark:bg-white/5 dark:text-gray-400">{{ $locale }}</span>
                    <input type="text" wire:model="newTitle.{{ $locale }}" placeholder="Title ({{ $locale }})" class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-800 dark:bg-transparent">
                </div>
                @error('newTitle.'.$locale) <p class="w-full text-xs text-error-500">{{ $message }}</p> @enderror
            @endforeach
        @else
            <input type="text" wire:model="newTitle" placeholder="Title" class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-800 dark:bg-transparent">
        @endif

        <input type="text" wire:model="newUrl" placeholder="URL (optional)" class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-800 dark:bg-transparent">

        @if(!empty($linkModuleOptions))
            <span class="text-xs text-gray-400">or link to:</span>
            <select wire:model.live="newLinkModule" class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-800 dark:bg-transparent">
                <option value="">— choose content —</option>
                @foreach($linkModuleOptions as $moduleName => $label)
                    <option value="{{ $moduleName }}">{{ $label }}</option>
                @endforeach
            </select>

            @if($newLinkModule)
                <select wire:model="newLinkItemId" class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-800 dark:bg-transparent">
                    <option value="">— choose item —</option>
                    @foreach($linkItemOptions as $itemId => $label)
                        <option value="{{ $itemId }}">{{ $label }}</option>
                    @endforeach
                </select>
            @endif
        @endif

        <button type="button" wire:click="addItem" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-white/5">
            Add item
        </button>
    </div>
    @error('newTitle') <p class="mb-2 text-xs text-error-500">{{ $message }}</p> @enderror

    @if($tree->isEmpty())
        <p class="text-sm text-gray-400">No items yet.</p>
    @else
        @include('menu::admin.components.item-node', ['items' => $tree])
    @endif
</div>
