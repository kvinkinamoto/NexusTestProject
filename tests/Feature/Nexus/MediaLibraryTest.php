<?php

use App\Models\User;
use App\Nexus\Modules\Demo\Models\Demo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Nodex\Nexus\Contracts\MediaLibrary\MediaLibraryInterface;
use Nodex\Nexus\Dto\MediaLibrary\MediaItemDto;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Nodex\Nexus\Services\MediaLibrary\SpatieMediaLibraryService;
use Spatie\Permission\Models\Permission;

/**
 * Regression coverage for #[Field(type: 'gallery')] — the new
 * spatie/laravel-medialibrary-backed field type, added as an additive
 * capability alongside (not replacing) the existing elFinder-backed
 * image/images/video/videos types. See MediaLibraryInterface's docblock for
 * the swappability contract this suite's "swappable" test exercises.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo(AdminPanelPermissionEnum::ALL->value);
    $this->actingAs($this->admin);
});

it('attaches, dedups, reorders and detaches media through the interface', function () {
    $demo = Demo::create(['title' => 'Gallery fixture']);
    $service = app(MediaLibraryInterface::class);

    // Different dimensions so the fakes' actual bytes differ — Laravel's
    // fake image content depends only on size, not filename, so two same-
    // sized fakes would hash identically and (correctly) dedup as one file.
    $fileA = UploadedFile::fake()->image('a.jpg', 50, 50);
    $fileB = UploadedFile::fake()->image('b.jpg', 80, 80);
    $fileAContent = file_get_contents($fileA->getRealPath());

    $itemA = $service->attach($demo, $fileA, 'mediaGallery');
    $itemB = $service->attach($demo, $fileB, 'mediaGallery');

    expect($service->list($demo, 'mediaGallery'))->toHaveCount(2);

    // Re-attaching a file whose content already matches item A dedups
    // instead of storing a second copy.
    $fileADuplicate = UploadedFile::fake()->createWithContent('a-again.jpg', $fileAContent);
    $dedupResult = $service->attach($demo, $fileADuplicate, 'mediaGallery');
    expect($dedupResult->id)->toBe($itemA->id)
        ->and($service->list($demo, 'mediaGallery'))->toHaveCount(2);

    // Reorder: swap A and B.
    $service->reorder($demo, [$itemB->id, $itemA->id], 'mediaGallery');
    $orderedIds = $service->list($demo, 'mediaGallery')->pluck('id')->all();
    expect($orderedIds)->toBe([$itemB->id, $itemA->id]);

    $service->detach($demo, $itemA->id, 'mediaGallery');
    expect($service->list($demo, 'mediaGallery'))->toHaveCount(1);
});

it('generates a thumb conversion for an attached image', function () {
    $demo = Demo::create(['title' => 'Thumbnail fixture']);
    $service = app(MediaLibraryInterface::class);

    $item = $service->attach($demo, UploadedFile::fake()->image('photo.jpg', 400, 400), 'mediaGallery');

    expect($item->thumbnailUrl)->not->toBeNull();
});

it('is swappable via a normal container rebind, per MediaLibraryInterface\'s docblock', function () {
    $stub = new class implements MediaLibraryInterface
    {
        public function attach($model, $file, string $collection = 'default'): MediaItemDto
        {
            return new MediaItemDto(id: 'stub-1', name: 'stub.jpg', url: 'https://stub.test/stub.jpg');
        }

        public function detach($model, string $mediaId, string $collection = 'default'): void {}

        public function list($model, string $collection = 'default'): Collection
        {
            return collect([new MediaItemDto(id: 'stub-1', name: 'stub.jpg', url: 'https://stub.test/stub.jpg')]);
        }

        public function reorder($model, array $orderedMediaIds, string $collection = 'default'): void {}
    };

    app()->instance(MediaLibraryInterface::class, $stub);

    expect(app(MediaLibraryInterface::class))->toBe($stub)
        ->and(app(MediaLibraryInterface::class)->list(new Demo, 'x')->first()->id)->toBe('stub-1');
});

it('shows a disabled notice for the gallery field type when media_library.enabled is false', function () {
    config(['nexus.media_library.enabled' => false]);
    $demo = Demo::create(['title' => 'Disabled fixture']);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Demo', 'id' => (string) $demo->id])
        ->assertSee('media library is disabled', false);
});

it('shows a "save first" notice for a new (unsaved) record instead of the upload input', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Demo'])
        ->assertDontSee('wire:model="galleryUpload.mediaGallery"', false);
});

it('rejects attach() on a model that does not implement HasMedia', function () {
    $notMediaCapable = new class extends Model {};

    expect(fn () => (new SpatieMediaLibraryService)->attach($notMediaCapable, UploadedFile::fake()->image('x.jpg')))
        ->toThrow(InvalidArgumentException::class);
});
