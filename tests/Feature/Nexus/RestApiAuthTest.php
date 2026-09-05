<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Spatie\Permission\Models\Permission;

/**
 * Coverage for api_middleware now requiring auth:sanctum (config/nexus.php),
 * the same token auth GraphQLSchemaTest.php already covers for /graphql —
 * proves the REST wildcard route (routes/api.php's {module}/action/{action})
 * rejects an unauthenticated request the same way, and lets an authenticated
 * one through to the controller's own permission check.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Page'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
});

it('rejects an unauthenticated request to the REST wildcard route', function () {
    $response = $this->getJson('/api/page/action/getAllByFilter');

    $response->assertUnauthorized();
});

it('lets a Sanctum-authenticated request reach the controller', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo(AdminPanelPermissionEnum::ALL->value);
    Sanctum::actingAs($admin, ['*']);

    $response = $this->getJson('/api/page/action/getAllByFilter');

    expect($response->status())->not->toBe(401);
});
