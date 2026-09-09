<?php

use App\Nexus\Modules\Redirect\Models\Redirect;
use Nodex\Nexus\Models\Module;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Redirect'])->run();
});

it('redirects a matching active path with the configured status code', function () {
    Redirect::create([
        'from_path' => 'old-page',
        'to_path' => '/new-page',
        'status_code' => 301,
        'is_active' => true,
    ]);

    $response = $this->get('/old-page');

    $response->assertRedirect('/new-page');
    expect($response->getStatusCode())->toBe(301);
});

it('redirects to an absolute external URL untouched', function () {
    Redirect::create([
        'from_path' => 'old-external',
        'to_path' => 'https://example.com/moved',
        'status_code' => 302,
        'is_active' => true,
    ]);

    $response = $this->get('/old-external');

    $response->assertRedirect('https://example.com/moved');
    expect($response->getStatusCode())->toBe(302);
});

it('does not redirect an inactive rule', function () {
    Redirect::create([
        'from_path' => 'disabled-page',
        'to_path' => '/new-page',
        'is_active' => false,
    ]);

    $response = $this->get('/disabled-page');

    $response->assertNotFound();
});

it('passes through a path with no matching rule', function () {
    $response = $this->get('/nothing-configured-here');

    $response->assertNotFound();
});

it('never intercepts admin-prefixed paths even when a matching rule exists', function () {
    Redirect::create([
        'from_path' => 'admin',
        'to_path' => 'https://example.com/should-not-be-used',
        'is_active' => true,
    ]);

    $response = $this->get('/admin');

    expect($response->headers->get('Location'))->not->toBe('https://example.com/should-not-be-used');
});

it('never intercepts api-prefixed paths even when a matching rule exists', function () {
    Redirect::create([
        'from_path' => 'api/redirect',
        'to_path' => 'https://example.com/should-not-be-used',
        'is_active' => true,
    ]);

    $response = $this->get('/api/redirect');

    expect($response->headers->get('Location'))->not->toBe('https://example.com/should-not-be-used');
});

it('never intercepts graphql-prefixed paths even when a matching rule exists', function () {
    Redirect::create([
        'from_path' => 'graphql',
        'to_path' => 'https://example.com/should-not-be-used',
        'is_active' => true,
    ]);

    $response = $this->get('/graphql');

    expect($response->headers->get('Location'))->not->toBe('https://example.com/should-not-be-used');
});

it('stops applying rules once the Redirect module is disabled', function () {
    Redirect::create([
        'from_path' => 'old-page',
        'to_path' => '/new-page',
        'is_active' => true,
    ]);

    Module::findByName('redirect')->update(['is_enabled' => false]);

    $response = $this->get('/old-page');

    $response->assertNotFound();
});
