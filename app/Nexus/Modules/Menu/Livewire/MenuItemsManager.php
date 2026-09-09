<?php

namespace App\Nexus\Modules\Menu\Livewire;

use App\Nexus\Modules\Menu\Models\MenuItem;
use Livewire\Component;
use Nodex\Nexus\Models\Module;
use Nodex\Nexus\Services\FormBuilder;
use Nodex\Nexus\Services\ModuleManager;
use Nodex\Nexus\Services\RelatedEntityFieldService;

/**
 * Embedded on Menu's own edit screen via #[Field(type: 'view')] (see
 * Menu::$items_manager) — manages MenuItem's nested tree (Kalnoy\Nestedset)
 * scoped to one menu_id. No drag-and-drop: v1 admin UX is explicit
 * up/down/indent/outdent row actions, since nothing in the Nexus attribute
 * system models lft/rgt yet (see the Tier 2 build plan).
 *
 * Every mutating method re-checks the 'menu' module's edit permission itself
 * — this component sits outside ModuleForm's own #[TableAction] pipeline
 * (which auto-guards CRUD via ModuleManager::checkPermission()), so nothing
 * upstream already guarantees the caller is allowed to edit menus.
 */
class MenuItemsManager extends Component
{
    public int $menuId;

    /** @var string|array<string, string> Plain string with 1 configured language, locale-keyed array with more — see activeLocales(). */
    public string|array $newTitle = '';

    public string $newUrl = '';

    /** Selected module name (RelatedEntityOptionDto::$name) for the "link to content" picker's first step. */
    public string $newLinkModule = '';

    public ?int $newLinkItemId = null;

    public function mount(int $menuId): void
    {
        $this->menuId = $menuId;
        $this->resetNewTitle();
    }

    public function render()
    {
        $tree = MenuItem::where('menu_id', $this->menuId)
            ->whereIsRoot()
            ->with('children')
            ->defaultOrder()
            ->get();

        return view('menu::admin.components.items-tree', [
            'tree' => $tree,
            'linkModuleOptions' => $this->linkModuleOptions(),
            'linkItemOptions' => $this->newLinkModule ? $this->linkItemOptions($this->newLinkModule) : [],
        ]);
    }

    /**
     * FormBuilder::getLanguages() is the same "how many languages are
     * configured right now" source every #[Field(translated: true)] field
     * already asks (see ModuleForm::activeLocales()) — falls back to a
     * single-element array (just config('app.locale')) since no Language
     * module exists in this install yet, which is why the title input below
     * is a single plain field today and will start showing one row per
     * language automatically the moment a real Language module is added,
     * with no further change needed here.
     */
    public function activeLocales(): array
    {
        return collect(FormBuilder::getLanguages())->map(fn ($locale) => (string) $locale)->values()->all();
    }

    /** @return array<string, string> module name => label, restricted to modules that declared #[Module(menuResolver:)] */
    public function linkModuleOptions(): array
    {
        $options = app(RelatedEntityFieldService::class)->getLinkOptions(
            allowedModules: [],
            excludedModules: [],
            morphId: 'linkable',
            customFieldName: 'linkable',
        );

        $linkable = [];
        foreach ($options as $option) {
            $config = ModuleManager::getModuleConfig($option->name());
            if (! empty($config->resolvers['menu'] ?? null)) {
                $linkable[$option->name()] = $option->label() ?? $option->name();
            }
        }

        return $linkable;
    }

    /** @return array<int, string> id => label, for the module selected in step one */
    public function linkItemOptions(string $moduleName): array
    {
        $config = ModuleManager::getModuleConfig($moduleName);
        if (! $config->model) {
            return [];
        }

        return app(RelatedEntityFieldService::class)->getEntities($config->model);
    }

    public function updatedNewLinkModule(): void
    {
        $this->newLinkItemId = null;
    }

    public function addItem(): void
    {
        $this->authorizeMenuEdit();

        $titleRule = count($this->activeLocales()) > 1
            ? ['newTitle' => 'required|array', 'newTitle.*' => 'nullable|string|max:255']
            : ['newTitle' => 'required|string|max:255'];

        $this->validate(array_merge($titleRule, [
            'newUrl' => 'nullable|string|max:2048',
            'newLinkModule' => 'nullable|string',
            'newLinkItemId' => 'nullable|integer',
        ]));

        $linkableType = null;
        $linkableId = null;

        if (! $this->newUrl && $this->newLinkModule && $this->newLinkItemId) {
            $config = ModuleManager::getModuleConfig($this->newLinkModule);
            $linkableType = $config->model;
            $linkableId = $this->newLinkItemId;
        }

        MenuItem::create([
            'menu_id' => $this->menuId,
            'title' => $this->newTitle,
            'url' => $this->newUrl ?: null,
            'linkable_type' => $this->newUrl ? null : $linkableType,
            'linkable_id' => $this->newUrl ? null : $linkableId,
            'parent_id' => null,
        ]);

        $this->resetNewTitle();
        $this->reset(['newUrl', 'newLinkModule', 'newLinkItemId']);
    }

    public function removeItem(int $itemId): void
    {
        $this->authorizeMenuEdit();

        $this->findScopedItem($itemId)?->delete();
    }

    public function moveUp(int $itemId): void
    {
        $this->authorizeMenuEdit();

        $this->findScopedItem($itemId)?->up();
    }

    public function moveDown(int $itemId): void
    {
        $this->authorizeMenuEdit();

        $this->findScopedItem($itemId)?->down();
    }

    /**
     * Makes the item a child of its immediately preceding sibling — a no-op
     * for the first sibling under a given parent, since there's nothing to
     * indent under.
     */
    public function indent(int $itemId): void
    {
        $this->authorizeMenuEdit();

        $item = $this->findScopedItem($itemId);
        if (! $item) {
            return;
        }

        $previousSibling = $item->getPrevSibling();
        if ($previousSibling) {
            $item->appendToNode($previousSibling)->save();
        }
    }

    /**
     * Promotes the item to be a sibling of its own parent, inserted right
     * after it — a no-op for an already-root item.
     */
    public function outdent(int $itemId): void
    {
        $this->authorizeMenuEdit();

        $item = $this->findScopedItem($itemId);
        if (! $item || ! $item->parent_id) {
            return;
        }

        $parent = $item->parent()->first();
        $item->insertAfterNode($parent);
    }

    private function resetNewTitle(): void
    {
        $this->newTitle = count($this->activeLocales()) > 1
            ? array_fill_keys($this->activeLocales(), '')
            : '';
    }

    private function findScopedItem(int $itemId): ?MenuItem
    {
        return MenuItem::where('menu_id', $this->menuId)->find($itemId);
    }

    private function authorizeMenuEdit(): void
    {
        $module = Module::findByName('menu');
        abort_unless($module && ModuleManager::checkPermission('edit', $module), 403);
    }
}
