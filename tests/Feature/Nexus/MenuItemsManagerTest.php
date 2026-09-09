<?php

use App\Models\User;
use App\Nexus\Modules\BlogPost\Models\BlogPost;
use App\Nexus\Modules\Menu\Livewire\MenuItemsManager;
use App\Nexus\Modules\Menu\Models\Menu;
use App\Nexus\Modules\Menu\Models\MenuItem;
use App\Nexus\Modules\Page\Models\Page;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Services\ModuleRegistry;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Menu'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Page'])->run();
    $this->artisan('nexus:module:install', ['name' => 'BlogCategory'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Tag'])->run();
    $this->artisan('nexus:module:install', ['name' => 'BlogPost'])->run();

    // ModuleRegistry::getEnabledModules()/getAllModules() memoize on the
    // singleton — the first nexus:module:install call above already cached
    // a stale enabled-modules list before Page/BlogPost were installed.
    // linkModuleOptions() (unlike this file's other tests) actually walks
    // that list, so it needs a fresh scan — same fix already established in
    // ExtensionPointsGapCoverageTest.php for the same root cause.
    app(ModuleRegistry::class)->refresh();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->menu = Menu::create(['name' => 'Header', 'key' => 'header', 'is_active' => true]);

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
});

it('adds a root item', function () {
    $this->actingAs($this->admin);

    Livewire::test(MenuItemsManager::class, ['menuId' => $this->menu->id])
        ->set('newTitle', 'Home')
        ->set('newUrl', '/')
        ->call('addItem')
        ->assertHasNoErrors();

    $item = MenuItem::where('menu_id', $this->menu->id)->firstOrFail();
    expect($item->title)->toBe('Home');
    expect($item->url)->toBe('/');
    expect($item->isRoot())->toBeTrue();
});

it('requires a title to add an item', function () {
    $this->actingAs($this->admin);

    Livewire::test(MenuItemsManager::class, ['menuId' => $this->menu->id])
        ->call('addItem')
        ->assertHasErrors(['newTitle' => 'required']);
});

it('reorders siblings with moveUp/moveDown', function () {
    $this->actingAs($this->admin);

    $first = $this->menu->items()->create(['title' => 'First', 'menu_id' => $this->menu->id]);
    $second = $this->menu->items()->create(['title' => 'Second', 'menu_id' => $this->menu->id]);

    Livewire::test(MenuItemsManager::class, ['menuId' => $this->menu->id])
        ->call('moveUp', $second->id);

    $ordered = MenuItem::where('menu_id', $this->menu->id)->defaultOrder()->pluck('title')->all();
    expect($ordered)->toBe(['Second', 'First']);
});

it('indents an item under its previous sibling, then outdents it back to root', function () {
    $this->actingAs($this->admin);

    $first = $this->menu->items()->create(['title' => 'Parent', 'menu_id' => $this->menu->id]);
    $second = $this->menu->items()->create(['title' => 'Child', 'menu_id' => $this->menu->id]);

    $component = Livewire::test(MenuItemsManager::class, ['menuId' => $this->menu->id])
        ->call('indent', $second->id);

    $second->refresh();
    expect($second->parent_id)->toBe($first->id);
    expect($second->isRoot())->toBeFalse();

    $component->call('outdent', $second->id);

    $second->refresh();
    expect($second->isRoot())->toBeTrue();
});

it('removes an item', function () {
    $this->actingAs($this->admin);

    $item = $this->menu->items()->create(['title' => 'Home', 'menu_id' => $this->menu->id]);

    Livewire::test(MenuItemsManager::class, ['menuId' => $this->menu->id])
        ->call('removeItem', $item->id);

    expect(MenuItem::find($item->id))->toBeNull();
});

it('only offers modules that declared a menu resolver in the link picker', function () {
    $this->actingAs($this->admin);

    $options = Livewire::test(MenuItemsManager::class, ['menuId' => $this->menu->id])
        ->instance()
        ->linkModuleOptions();

    // Keyed by Module::name exactly as installed (nexus:module:install's
    // given name — "Page"/"BlogPost" here, not the lowercase #[Module(name:)]
    // attribute value), the same casing ModuleManager::getModuleConfig()
    // already resolves case-appropriately regardless.
    expect($options)->toHaveKeys(['Page', 'BlogPost'])
        ->and($options)->not->toHaveKey('Tag');
});

it('adds an item linked to a chosen Page through the two-step picker', function () {
    $this->actingAs($this->admin);

    $page = Page::create(['title' => 'Contact', 'slug' => 'contact', 'is_active' => true]);

    Livewire::test(MenuItemsManager::class, ['menuId' => $this->menu->id])
        ->set('newTitle', 'Contact Us')
        ->set('newLinkModule', 'Page')
        ->set('newLinkItemId', $page->id)
        ->call('addItem')
        ->assertHasNoErrors();

    $item = MenuItem::where('menu_id', $this->menu->id)->firstOrFail();
    expect($item->linkable_type)->toBe(Page::class);
    expect($item->linkable_id)->toBe($page->id);
    expect($item->url)->toBeNull();
    expect($item->resolvedUrl())->toBe(route('nexus.page.show', 'contact'));
});

it('prefers a manual URL over a selected linkable when both are given', function () {
    $this->actingAs($this->admin);

    $post = BlogPost::create(['title' => 'Hello', 'slug' => 'hello', 'is_published' => true]);

    Livewire::test(MenuItemsManager::class, ['menuId' => $this->menu->id])
        ->set('newTitle', 'Custom')
        ->set('newUrl', '/custom-page')
        ->set('newLinkModule', 'BlogPost')
        ->set('newLinkItemId', $post->id)
        ->call('addItem')
        ->assertHasNoErrors();

    $item = MenuItem::where('menu_id', $this->menu->id)->firstOrFail();
    expect($item->url)->toBe('/custom-page');
    expect($item->linkable_type)->toBeNull();
    expect($item->resolvedUrl())->toBe('/custom-page');
});

it('returns just the configured app locale today, since no Language module exists', function () {
    $this->actingAs($this->admin);

    $locales = Livewire::test(MenuItemsManager::class, ['menuId' => $this->menu->id])
        ->instance()
        ->activeLocales();

    expect($locales)->toBe([config('app.locale')]);
});

it('denies mutations from a user without the menu edit permission', function () {
    $viewer = User::factory()->create();
    $this->actingAs($viewer);

    Livewire::test(MenuItemsManager::class, ['menuId' => $this->menu->id])
        ->call('addItem')
        ->assertStatus(403);
});
