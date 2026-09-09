<?php

use App\Nexus\Modules\BlogPost\Models\BlogPost;
use App\Nexus\Modules\Menu\Models\Menu;
use App\Nexus\Modules\Menu\Models\MenuItem;
use App\Nexus\Modules\Page\Models\Page;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Menu'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Page'])->run();
    $this->artisan('nexus:module:install', ['name' => 'BlogCategory'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Tag'])->run();
    $this->artisan('nexus:module:install', ['name' => 'BlogPost'])->run();
});

it('renders nested items in order for the requested, active menu', function () {
    $menu = Menu::create(['name' => 'Header', 'key' => 'header', 'is_active' => true]);
    $about = $menu->items()->create(['title' => 'About', 'url' => '/about']);
    $team = MenuItem::create(['menu_id' => $menu->id, 'title' => 'Team', 'url' => '/about/team']);
    $team->appendToNode($about)->save();

    $html = view('menu::public.menu', ['key' => 'header'])->render();

    expect($html)->toContain('About')
        ->and($html)->toContain('Team')
        ->and($html)->toContain('href="/about"')
        ->and($html)->toContain('href="/about/team"')
        ->and(strpos($html, 'About'))->toBeLessThan(strpos($html, 'Team'));
});

it('excludes inactive items and their children', function () {
    $menu = Menu::create(['name' => 'Header', 'key' => 'header', 'is_active' => true]);
    $hidden = $menu->items()->create(['title' => 'Hidden', 'is_active' => false]);
    $child = MenuItem::create(['title' => 'Hidden Child', 'menu_id' => $menu->id]);
    $child->appendToNode($hidden)->save();

    $html = view('menu::public.menu', ['key' => 'header'])->render();

    expect($html)->not->toContain('Hidden')
        ->and($html)->not->toContain('Hidden Child');
});

it('renders nothing for an inactive menu or an unknown key', function () {
    Menu::create(['name' => 'Footer', 'key' => 'footer', 'is_active' => false])
        ->items()->create(['title' => 'Should not show']);

    expect(trim(view('menu::public.menu', ['key' => 'footer'])->render()))->toBe('');
    expect(trim(view('menu::public.menu', ['key' => 'does-not-exist'])->render()))->toBe('');
});

it('resolves a link to a Page or BlogPost by slug when no manual url is set', function () {
    $menu = Menu::create(['name' => 'Header', 'key' => 'header', 'is_active' => true]);
    $page = Page::create(['title' => 'Contact', 'slug' => 'contact', 'is_active' => true]);
    $post = BlogPost::create(['title' => 'Hello', 'slug' => 'hello', 'is_published' => true]);

    $menu->items()->create(['title' => 'Contact Us', 'linkable_type' => Page::class, 'linkable_id' => $page->id]);
    $menu->items()->create(['title' => 'Hello Post', 'linkable_type' => BlogPost::class, 'linkable_id' => $post->id]);

    $html = view('menu::public.menu', ['key' => 'header'])->render();

    expect($html)->toContain('href="'.route('nexus.page.show', 'contact').'"')
        ->and($html)->toContain('href="'.route('nexus.blogPost.show', 'hello').'"');
});
