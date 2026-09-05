<?php

use App\Models\User;
use App\Nexus\Modules\Demo\Models\Demo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Nodex\Nexus\Contracts\MediaLibrary\MediaLibraryInterface;
use Nodex\Nexus\Dto\MediaLibrary\MediaItemDto;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Spatie\Permission\Models\Permission;

/**
 * Coverage for the responsive-variants half of task #5 — the sm/md/lg
 * conversions Concerns\HasNexusMedia now registers alongside 'thumb', and
 * the new <x-nexus::image> public component that turns them into a srcset.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo(AdminPanelPermissionEnum::ALL->value);
    $this->actingAs($this->admin);
});

it('generates sm/md/lg conversions on attach, exposed as MediaItemDto variants', function () {
    $demo = Demo::create(['title' => 'Variants demo', 'slug' => 'variants-demo']);
    $file = UploadedFile::fake()->image('photo.jpg', 2000, 1500);

    $item = app(MediaLibraryInterface::class)->attach($demo, $file, 'mediaGallery');

    expect($item->variants)->toHaveKeys(['sm', 'md', 'lg']);
    foreach ($item->variants as $url) {
        expect($url)->toBeString()->not->toBeEmpty();
    }
});

it('renders a srcset from the variants through <x-nexus::image>', function () {
    $item = new MediaItemDto(
        id: '1',
        name: 'photo.jpg',
        url: '/storage/1/photo.jpg',
        variants: [
            'sm' => '/storage/1/conversions/photo-sm.jpg',
            'md' => '/storage/1/conversions/photo-md.jpg',
            'lg' => '/storage/1/conversions/photo-lg.jpg',
        ],
    );

    $html = Blade::render('<x-nexus::image :item="$item" sizes="(max-width: 600px) 100vw, 800px" />', ['item' => $item]);

    expect($html)->toContain('src="/storage/1/photo.jpg"')
        ->and($html)->toContain('/storage/1/conversions/photo-sm.jpg 400w')
        ->and($html)->toContain('/storage/1/conversions/photo-md.jpg 800w')
        ->and($html)->toContain('/storage/1/conversions/photo-lg.jpg 1200w')
        ->and($html)->toContain('sizes="(max-width: 600px) 100vw, 800px"');
});

it('omits srcset/sizes when there are no variants', function () {
    $item = new MediaItemDto(id: '1', name: 'photo.jpg', url: '/storage/1/photo.jpg');

    $html = Blade::render('<x-nexus::image :item="$item" />', ['item' => $item]);

    expect($html)->toContain('src="/storage/1/photo.jpg"')
        ->and($html)->not->toContain('srcset');
});
