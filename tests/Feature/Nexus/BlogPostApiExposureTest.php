<?php

use App\Models\User;
use App\Nexus\Modules\BlogPost\Models\BlogPost;
use Laravel\Sanctum\Sanctum;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Spatie\Permission\Models\Permission;

/**
 * Confirms the apiExpose: true fields declared on BlogPost (per the
 * permissions-and-API-compatibility requirement in the Tier 2 build plan)
 * actually surface through the generic REST wildcard route, the same
 * mechanism RestApiAuthTest.php proves for Page — not just that the
 * attribute was written, but that it does something.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'BlogCategory'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Tag'])->run();
    $this->artisan('nexus:module:install', ['name' => 'BlogPost'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
});

it('rejects an unauthenticated REST request for blog posts', function () {
    $this->getJson('/api/blogPost/action/getAllByFilter')->assertUnauthorized();
});

it('exposes apiExpose fields through the REST wildcard route', function () {
    BlogPost::create(['title' => 'Hello World', 'slug' => 'hello-world', 'is_published' => true]);

    $admin = User::factory()->create();
    $admin->givePermissionTo(AdminPanelPermissionEnum::ALL->value);
    Sanctum::actingAs($admin, ['*']);

    $response = $this->getJson('/api/blogPost/action/getAllByFilter');

    $response->assertOk();
    $response->assertJsonFragment(['title' => 'Hello World']);
});
