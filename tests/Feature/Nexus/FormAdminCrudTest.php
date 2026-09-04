<?php

use App\Models\User;
use App\Nexus\Modules\Form\Models\Form;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

/**
 * Regression coverage for the generic admin CRUD path (not just the public
 * submission side already covered elsewhere) — this is what actually caught
 * the "Class ...\Form\Models\FormSubmission not found" bug: Form::submissions()
 * is resolved as soon as the create form mounts (ManagesRelationPicker), not
 * only on save, and none of the other Form tests ever mount ModuleForm.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Form'])->run();
    $this->artisan('nexus:module:install', ['name' => 'FormSubmission'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/Form/action/create')->assertOk();
});

it('renders an added repeater row, including the boolean "required" cell, without an unsupported-type error', function () {
    // A create page's repeater starts with zero rows, so loading it alone
    // (the previous test) never actually renders a row's cells — the "not
    // supported in Livewire repeater" bug for the boolean column only shows
    // up once a row exists, exactly as it would for a real admin clicking
    // "add row" in the browser.
    Livewire::test(ModuleForm::class, ['moduleName' => 'Form'])
        ->call('addRepeaterRow', 'fields')
        ->assertDontSee('не підтримується')
        ->assertSee('wire:model="relationRows.fields.0.required"', false);
});

it('creates a form with field rows through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Form'])
        ->set('data.name', 'Contact')
        ->set('data.title', 'Contact us')
        ->set('data.slug', 'contact-admin')
        ->set('data.is_active', true)
        ->set('relationRows.fields', [
            ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'options' => null],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $form = Form::where('slug', 'contact-admin')->firstOrFail();
    expect($form->fields()->where('key', 'email')->exists())->toBeTrue();
});
