<?php

use App\Models\User;
use App\Nexus\Modules\Demo\Models\Demo;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Nodex\Nexus\Contracts\MediaLibrary\MediaLibraryInterface;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

/**
 * Coverage for the focal-point half of task #5 ("smart media processing") —
 * Concerns\HasNexusMedia's now-focal-aware 'thumb' conversion and
 * MediaLibraryInterface::setFocalPoint(). Demo::mediaGallery is the live
 * #[Field(type: 'gallery')] example (see Demo.php) — the only field type
 * that's actually backed by Spatie Media Library, not a plain path column.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo(AdminPanelPermissionEnum::ALL->value);
    $this->actingAs($this->admin);
});

it('defaults to a center focal point and generates the thumb conversion on attach', function () {
    $demo = Demo::create(['title' => 'Focal demo', 'slug' => 'focal-demo']);
    $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

    $item = app(MediaLibraryInterface::class)->attach($demo, $file, 'mediaGallery');

    expect($item->focalPoint)->toBeNull();
    expect($item->thumbnailUrl)->not->toBeNull();
});

it('updates the stored focal point and clamps out-of-range input', function () {
    $demo = Demo::create(['title' => 'Focal clamp', 'slug' => 'focal-clamp']);
    $file = UploadedFile::fake()->image('photo.jpg', 800, 600);
    $item = app(MediaLibraryInterface::class)->attach($demo, $file, 'mediaGallery');

    $updated = app(MediaLibraryInterface::class)->setFocalPoint($demo, $item->id, 1.5, -0.5, 'mediaGallery');

    // JSON round-tripping through Spatie's custom_properties column doesn't
    // preserve "this was a float" for a whole number (1.0 comes back as the
    // PHP int 1) — assert numeric value, not exact PHP type.
    expect((float) $updated->focalPoint['x'])->toBe(1.0);
    expect((float) $updated->focalPoint['y'])->toBe(0.0);
    expect($updated->thumbnailUrl)->not->toBeNull();
});

it('sets the focal point through the admin Livewire gallery field', function () {
    $demo = Demo::create(['title' => 'Focal livewire', 'slug' => 'focal-livewire']);
    $file = UploadedFile::fake()->image('photo.jpg', 800, 600);
    $item = app(MediaLibraryInterface::class)->attach($demo, $file, 'mediaGallery');

    Livewire::test(ModuleForm::class, ['moduleName' => 'Demo', 'id' => $demo->id])
        ->call('setGalleryFocalPoint', 'mediaGallery', $item->id, 0.2, 0.8)
        ->assertHasNoErrors();

    $items = app(MediaLibraryInterface::class)->list($demo, 'mediaGallery');
    expect($items->first()->focalPoint)->toBe(['x' => 0.2, 'y' => 0.8]);
});
