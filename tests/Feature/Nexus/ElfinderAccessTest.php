<?php

use App\Models\User;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Spatie\Permission\Models\Permission;

/**
 * Coverage for task #8's security-hardening pass: elFinder's published
 * config previously gated /elfinder with plain 'auth' only — ANY logged-in
 * account, including a front-end user with zero Nexus admin permissions,
 * could reach the file manager. config/elfinder.php now adds
 * NexusAdminMiddleware to the route group, the same admin_panel permission
 * gate every /admin/* route already requires.
 */
it('blocks a plain authenticated user with no admin_panel permission from elFinder', function () {
    $this->artisan('nexus:permission:init')->run();
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/elfinder');

    $response->assertRedirect(route('logout'));
});

it('allows an admin with admin_panel permission to reach elFinder', function () {
    $this->artisan('nexus:permission:init')->run();
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');
    $admin = User::factory()->create();
    $admin->givePermissionTo(AdminPanelPermissionEnum::ADMIN_PANEL->value);
    $this->actingAs($admin);

    $response = $this->get('/elfinder');

    $response->assertOk();
});

it('restricts elFinder uploads to an explicit type allow-list', function () {
    expect(config('elfinder.root_options.uploadOrder'))->toBe(['deny', 'allow'])
        ->and(config('elfinder.root_options.uploadDeny'))->toBe(['all'])
        ->and(config('elfinder.root_options.uploadAllow'))->toContain('image', 'video');
});
