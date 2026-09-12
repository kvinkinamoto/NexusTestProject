<?php

use App\Nexus\Modules\ShopCategory\Models\ShopCategory;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopCategory'])->run();
});

it('lists published root categories in the storefront header nav', function () {
    $visible = ShopCategory::create(['name' => 'Взуття', 'slug' => 'vzuttya', 'publish' => true]);
    ShopCategory::create(['name' => 'Прихована', 'slug' => 'prykhovana', 'publish' => false]);
    ShopCategory::create(['name' => 'Підкатегорія', 'slug' => 'pidkategoriya', 'publish' => true, 'parent_id' => $visible->id]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Взуття');
    $response->assertDontSee('Прихована');
    $response->assertDontSee('Підкатегорія');
});

it('renders no nav category links when none are published', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertDontSee('/catalog/', false);
});
