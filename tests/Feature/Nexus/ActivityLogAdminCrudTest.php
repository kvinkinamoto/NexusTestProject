<?php

use App\Models\User;
use App\Nexus\Modules\ActivityLog\Models\ActivityLog;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'ActivityLog'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('lists activity log rows on the bare index route', function () {
    ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100]);

    $this->get('/admin/ActivityLog/action/index')->assertOk();
});

it('opens the view (edit) screen for a real log row', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100]);
    $activity = ActivityLog::query()->forSubject($product)->firstOrFail();

    $this->get("/admin/ActivityLog/action/edit/{$activity->id}")->assertOk();
});
