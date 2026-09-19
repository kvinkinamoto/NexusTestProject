<?php

use App\Nexus\Modules\ShopCategory\Models\ShopCategory;

it('uses the category\'s own meta title/description when set', function () {
    $category = ShopCategory::create([
        'name' => 'SEO Tools',
        'slug' => 'seo-tools',
        'publish' => true,
        'meta_title' => 'Custom SEO Tools Title',
        'meta_description' => 'Custom SEO tools meta description.',
    ]);

    $response = $this->get(route('shop.catalog.category', $category));

    $response->assertOk();
    $response->assertSee('Custom SEO Tools Title — Nexus Store', false);
    $response->assertSee('Custom SEO tools meta description.', false);
});

it('falls back to the category name and the generic default description when meta fields are empty', function () {
    $category = ShopCategory::create(['name' => 'Widgets', 'slug' => 'widgets', 'publish' => true]);

    $response = $this->get(route('shop.catalog.category', $category));

    $response->assertOk();
    $response->assertSee('Widgets — Nexus Store', false);
});

it('uses the generic catalog title when there is no category', function () {
    $response = $this->get(route('shop.catalog'));

    $response->assertOk();
    $response->assertSee('Каталог модулів — Nexus Store', false);
});
