<?php

use App\Models\User;
use App\Nexus\Modules\Page\Models\Page;
use Laravel\Sanctum\Sanctum;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Spatie\Permission\Models\Permission;

/**
 * Coverage for the createPage/updatePage/deletePage GraphQL mutations
 * (Services\GraphQL\SchemaBuilder) — proves scalar-only writes go through
 * the same NexusRuleCollector validation REST/admin use, gated the same way
 * as the query side.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Page'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo(AdminPanelPermissionEnum::ALL->value);
    Sanctum::actingAs($this->admin, ['*']);
});

it('creates a page through the createPage mutation', function () {
    $response = $this->postJson('/graphql', [
        'query' => '
            mutation {
                createPage(input: { title: "Contact", slug: "contact-gql", isActive: true }) {
                    id title slug isActive
                }
            }
        ',
    ]);

    $response->assertOk();
    expect($response->json('errors'))->toBeNull();
    expect($response->json('data.createPage.title'))->toBe('Contact');

    $page = Page::where('slug', 'contact-gql')->firstOrFail();
    expect($page->title)->toBe('Contact');
});

it('rejects an invalid createPage mutation with a GraphQL error, not a 500', function () {
    $response = $this->postJson('/graphql', [
        'query' => 'mutation { createPage(input: { slug: "no-title" }) { id } }',
    ]);

    $response->assertOk();
    expect($response->json('errors'))->not->toBeNull();
    expect(Page::where('slug', 'no-title')->exists())->toBeFalse();
});

it('updates a page through the updatePage mutation', function () {
    $page = Page::create(['title' => 'Old', 'slug' => 'update-gql', 'is_active' => true]);

    $response = $this->postJson('/graphql', [
        'query' => '
            mutation($id: ID!) {
                updatePage(id: $id, input: { title: "New", slug: "update-gql" }) { title }
            }
        ',
        'variables' => ['id' => $page->id],
    ]);

    $response->assertOk();
    expect($response->json('errors'))->toBeNull();
    expect($page->fresh()->title)->toBe('New');
});

it('deletes a page through the deletePage mutation', function () {
    $page = Page::create(['title' => 'Gone', 'slug' => 'delete-gql', 'is_active' => true]);

    $response = $this->postJson('/graphql', [
        'query' => 'mutation($id: ID!) { deletePage(id: $id) }',
        'variables' => ['id' => $page->id],
    ]);

    $response->assertOk();
    expect($response->json('data.deletePage'))->toBeTrue();
    expect(Page::find($page->id))->toBeNull();
});

it('denies a mutation for a user without the create permission', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/graphql', [
        'query' => 'mutation { createPage(input: { title: "Nope", slug: "nope" }) { id } }',
    ]);

    $response->assertOk();
    expect($response->json('errors'))->not->toBeNull();
    expect(Page::where('slug', 'nope')->exists())->toBeFalse();
});
