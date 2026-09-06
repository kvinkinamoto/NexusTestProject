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
 * Coverage for task #8's security-hardening pass: uploading to a
 * #[Field(type: 'gallery')] field previously accepted any file type — only
 * a client-side accept="image/*" hint guarded it, trivially bypassed, and
 * nothing downstream validated mime/extension either
 * (Livewire\Concerns\ManagesGalleryFields::updatedGalleryUpload() now
 * validates image|max:10240 before attach()).
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo(AdminPanelPermissionEnum::ALL->value);
    $this->actingAs($this->admin);
});

it('rejects a non-image file uploaded to a gallery field', function () {
    $demo = Demo::create(['title' => 'Gallery validation', 'slug' => 'gallery-validation']);
    $file = UploadedFile::fake()->create('not-an-image.exe', 10, 'application/x-msdownload');

    Livewire::test(ModuleForm::class, ['moduleName' => 'Demo', 'id' => $demo->id])
        ->set('galleryUpload.mediaGallery', $file)
        ->assertHasErrors('galleryUpload.mediaGallery');

    expect(app(MediaLibraryInterface::class)->list($demo, 'mediaGallery'))->toHaveCount(0);
});

it('still accepts a real image upload to a gallery field', function () {
    $demo = Demo::create(['title' => 'Gallery validation ok', 'slug' => 'gallery-validation-ok']);
    $file = UploadedFile::fake()->image('photo.jpg', 200, 200);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Demo', 'id' => $demo->id])
        ->set('galleryUpload.mediaGallery', $file)
        ->assertHasNoErrors('galleryUpload.mediaGallery');

    expect(app(MediaLibraryInterface::class)->list($demo, 'mediaGallery'))->toHaveCount(1);
});
