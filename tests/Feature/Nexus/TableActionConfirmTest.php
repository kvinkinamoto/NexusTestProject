<?php

use App\Models\User;
use App\Nexus\Modules\ConfirmFixture\Models\ConfirmFixture;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleTable;
use Spatie\Permission\Models\Permission;

/**
 * Regression coverage for the isConfirm fix: ModuleTable::runAction() only
 * has a handler for delete/restore/deletePermanent/duplicate, so any other
 * #[TableAction] (a custom name, e.g. 'archive') renders as a plain <a href>
 * to NexusController::action() instead of a wire:click. That branch used to
 * ignore $rowAction->confirm entirely — see module-table.blade.php's comment
 * on the fix for the full story.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ConfirmFixture'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');

    $admin = User::factory()->create();
    $admin->givePermissionTo(AdminPanelPermissionEnum::ALL->value);
    $this->actingAs($admin);
});

it('adds a JS confirm guard to a non-whitelisted table action that is marked isConfirm', function () {
    ConfirmFixture::create(['name' => 'Row one']);

    Livewire::test(ModuleTable::class, ['moduleName' => 'ConfirmFixture'])
        ->assertSeeHtml("sendFormConfirm(true, 'archive'")
        ->assertDontSeeHtml('wire:click="runAction(\'archive\'');
});

it('does not add a confirm guard to the non-confirm edit action', function () {
    ConfirmFixture::create(['name' => 'Row two']);

    Livewire::test(ModuleTable::class, ['moduleName' => 'ConfirmFixture'])
        ->assertDontSeeHtml("sendFormConfirm(true, 'edit'");
});
