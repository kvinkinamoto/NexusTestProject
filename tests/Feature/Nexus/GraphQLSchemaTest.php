<?php

use App\Models\User;
use App\Nexus\Modules\Page\Models\Page;
use Laravel\Sanctum\Sanctum;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Spatie\Permission\Models\Permission;

/**
 * Coverage for the /graphql endpoint (GraphQLController + Services\GraphQL\SchemaBuilder)
 * — proves a module's apiExpose fields (Page's title/slug/metaTitle/metaDescription/isActive,
 * see Page.php) surface as a real GraphQL Query type, gated by Sanctum auth
 * and the same ModuleManager::checkPermission() REST already uses.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Page'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
});

it('queries a single apiExpose-enabled record by id', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo(AdminPanelPermissionEnum::ALL->value);
    Sanctum::actingAs($admin, ['*']);

    $page = Page::create(['title' => 'Home', 'slug' => 'home', 'meta_title' => 'Home | Site', 'is_active' => true]);

    $response = $this->postJson('/graphql', [
        'query' => '{ page(id: '.$page->id.') { id title slug metaTitle isActive } }',
    ]);

    $response->assertOk();
    expect($response->json('errors'))->toBeNull();
    expect($response->json('data.page'))->toMatchArray([
        'id' => (string) $page->id,
        'title' => 'Home',
        'slug' => 'home',
        'metaTitle' => 'Home | Site',
        'isActive' => true,
    ]);
});

it('queries a list of records', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo(AdminPanelPermissionEnum::ALL->value);
    Sanctum::actingAs($admin, ['*']);

    Page::create(['title' => 'Home', 'slug' => 'home-list', 'is_active' => true]);
    Page::create(['title' => 'About', 'slug' => 'about-list', 'is_active' => true]);

    $response = $this->postJson('/graphql', ['query' => '{ pages { title } }']);

    $response->assertOk();
    expect(collect($response->json('data.pages'))->pluck('title'))->toContain('Home', 'About');
});

it('does not expose the blockEditor blocks field, which has no scalar GraphQL mapping', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo(AdminPanelPermissionEnum::ALL->value);
    Sanctum::actingAs($admin, ['*']);

    $page = Page::create(['title' => 'Home', 'slug' => 'home-blocks', 'is_active' => true]);

    $response = $this->postJson('/graphql', ['query' => '{ page(id: '.$page->id.') { blocks } }']);

    $response->assertOk();
    expect($response->json('errors'))->not->toBeNull();
});

it('rejects an unauthenticated request before it reaches the resolver', function () {
    $response = $this->postJson('/graphql', ['query' => '{ pages { title } }']);

    $response->assertUnauthorized();
});

it('returns a GraphQL error, not the data, for a user without permission', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    Page::create(['title' => 'Secret', 'slug' => 'secret', 'is_active' => true]);

    $response = $this->postJson('/graphql', ['query' => '{ pages { title } }']);

    $response->assertOk();
    expect($response->json('errors'))->not->toBeNull();
    expect($response->json('data.pages'))->toBeNull();
});
