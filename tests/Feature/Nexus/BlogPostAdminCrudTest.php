<?php

use App\Models\User;
use App\Nexus\Modules\BlogCategory\Models\BlogCategory;
use App\Nexus\Modules\BlogPost\Models\BlogPost;
use App\Nexus\Modules\Tag\Models\Tag;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'BlogCategory'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Tag'])->run();
    $this->artisan('nexus:module:install', ['name' => 'BlogPost'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/BlogPost/action/create')->assertOk();
});

it('creates a blog post with a category and tags through the generic admin CRUD', function () {
    $category = BlogCategory::create(['name' => 'News', 'slug' => 'news']);
    $tag = Tag::create(['name' => 'Laravel', 'slug' => 'laravel']);

    Livewire::test(ModuleForm::class, ['moduleName' => 'BlogPost'])
        ->set('data.title', 'Hello World')
        ->set('data.slug', 'hello-world')
        ->set('data.content', 'Body copy')
        ->set('data.is_published', true)
        ->set('data.category', $category->id)
        ->set('data.tags', [$tag->id])
        ->call('save')
        ->assertHasNoErrors();

    $post = BlogPost::where('slug', 'hello-world')->firstOrFail();
    expect($post->blog_category_id)->toBe($category->id);
    expect($post->tags()->pluck('tags.id')->all())->toBe([$tag->id]);
});

it('auto-generates the slug from the title when left blank, instead of crashing on a NOT NULL column', function () {
    // Regression: same class of bug reported against Menu — see
    // App\Nexus\Concerns\HasAutoSlug.
    Livewire::test(ModuleForm::class, ['moduleName' => 'BlogPost'])
        ->set('data.title', 'A Brand New Post')
        ->call('save')
        ->assertHasNoErrors();

    $post = BlogPost::where('title', 'A Brand New Post')->firstOrFail();
    expect($post->slug)->toBe('a-brand-new-post');
});

it('rejects a duplicate slug', function () {
    BlogPost::create(['title' => 'First', 'slug' => 'first', 'is_published' => true]);

    Livewire::test(ModuleForm::class, ['moduleName' => 'BlogPost'])
        ->set('data.title', 'Second')
        ->set('data.slug', 'first')
        ->call('save')
        ->assertHasErrors(['data.slug' => 'unique']);
});
