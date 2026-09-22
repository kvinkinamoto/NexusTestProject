<?php

use App\Models\User;
use App\Nexus\Modules\Language\Models\Language;
use App\Nexus\Modules\Page\Models\Page;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

/**
 * Regression coverage for the #[Field(type: 'blockEditor')] admin CRUD path
 * — mirrors FormAdminCrudTest.php's structure. Proves addBlock() renders
 * that block type's own fields (not a fixed repeaterColumns list), and that
 * save() persists heterogeneous PageBlock rows through the same
 * buildLegacyInput()/saveMultipleRelation() pipeline #[RepeaterField] uses.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Page'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/Page/action/create')->assertOk();
});

it('renders an added block\'s own fields, not a fixed column list', function () {
    // No Language module installed in this test's DB, so activeLocales() falls
    // back to just [config('app.locale')] — see ModuleForm::activeLocales() and
    // MenuItemsManagerTest's identical "no Language module" precedent. A
    // translatable block field still renders one locale-suffixed input per
    // active locale (here, just the app locale).
    Livewire::test(ModuleForm::class, ['moduleName' => 'Page'])
        ->call('addBlock', 'blocks', 'hero')
        ->assertDontSee('не підтримується')
        ->assertSee('wire:model="relationRows.blocks.0.data.headline.'.config('app.locale').'"', false)
        ->assertSee('wire:model="relationRows.blocks.0.data.cta_url"', false)
        ->call('addBlock', 'blocks', 'cta')
        ->assertSee('wire:model="relationRows.blocks.1.data.button_label.'.config('app.locale').'"', false);
});

it('creates a page with heterogeneous blocks through the generic admin CRUD', function () {
    $locale = config('app.locale');

    Livewire::test(ModuleForm::class, ['moduleName' => 'Page'])
        ->set('data.title', 'Home')
        ->set('data.slug', 'home-admin')
        ->set('data.is_active', true)
        ->call('addBlock', 'blocks', 'hero')
        ->set("relationRows.blocks.0.data.headline.{$locale}", 'Welcome')
        ->call('addBlock', 'blocks', 'text')
        ->set("relationRows.blocks.1.data.body.{$locale}", 'Some body copy')
        ->call('save')
        ->assertHasNoErrors();

    $page = Page::where('slug', 'home-admin')->firstOrFail();
    $blocks = $page->blocks()->orderBy('position')->get();

    expect($blocks)->toHaveCount(2);
    expect($blocks[0]->type)->toBe('hero');
    expect($blocks[0]->data['headline'])->toBe([$locale => 'Welcome']);
    expect($blocks[1]->type)->toBe('text');
    expect($blocks[1]->data['body'])->toBe([$locale => 'Some body copy']);
});

it('renders one input per active language once the Language module defines several', function () {
    $this->artisan('nexus:module:install', ['name' => 'Language'])->run();

    Language::query()->create(['language' => 'Українська', 'code' => 'uk', 'is_default' => true, 'publish' => true]);
    Language::query()->create(['language' => 'English', 'code' => 'en', 'publish' => true]);
    Language::query()->create(['language' => 'Draft', 'code' => 'xx', 'publish' => false]);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Page'])
        ->call('addBlock', 'blocks', 'hero')
        ->assertSee('wire:model="relationRows.blocks.0.data.headline.uk"', false)
        ->assertSee('wire:model="relationRows.blocks.0.data.headline.en"', false)
        ->assertDontSee('wire:model="relationRows.blocks.0.data.headline.xx"', false)
        ->set('relationRows.blocks.0.data.headline.uk', 'Ласкаво просимо')
        ->set('relationRows.blocks.0.data.headline.en', 'Welcome')
        ->set('data.title', 'Home')
        ->set('data.slug', 'home-multilang')
        ->call('save')
        ->assertHasNoErrors();

    $block = Page::where('slug', 'home-multilang')->firstOrFail()->blocks()->firstOrFail();

    expect($block->data['headline'])->toBe(['uk' => 'Ласкаво просимо', 'en' => 'Welcome']);
});

it('removes a block and reorders the remaining ones on save', function () {
    $page = Page::create(['title' => 'Home', 'slug' => 'home-reorder', 'is_active' => true]);
    $page->blocks()->create(['type' => 'hero', 'data' => ['headline' => 'First'], 'position' => 0]);
    $page->blocks()->create(['type' => 'text', 'data' => ['body' => 'Second'], 'position' => 1]);
    $page->blocks()->create(['type' => 'cta', 'data' => ['heading' => 'Third'], 'position' => 2]);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Page', 'id' => $page->id])
        ->call('removeBlock', 'blocks', 0)
        ->call('moveBlockUp', 'blocks', 1)
        ->call('save')
        ->assertHasNoErrors();

    $remaining = $page->fresh()->blocks()->orderBy('position')->get();

    expect($remaining)->toHaveCount(2);
    expect($remaining[0]->type)->toBe('cta');
    expect($remaining[1]->type)->toBe('text');
});
