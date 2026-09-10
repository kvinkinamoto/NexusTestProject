<?php

use App\Models\User;
use App\Nexus\Modules\SocialNetwork\Enums\SocialPlatformEnum;
use App\Nexus\Modules\SocialNetwork\Models\SocialNetwork;
use Illuminate\Support\Facades\Http;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'SocialNetwork'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
});

it('prints the raw code for the CLI flow when the browser has no session', function () {
    $response = $this->get('/social-network/instagram/callback?code=abc123');

    $response->assertOk();
    $response->assertSee('instagram:authorize --code=abc123', false);
});

it('shows the provider error message when the provider redirects back with an error', function () {
    $response = $this->get('/social-network/facebook/callback?error=access_denied&error_description=User+denied');

    $response->assertSee('User denied');
});

it('redirects home with a danger message when authorization fails for a signed-in admin', function () {
    Http::fake(['*oauth/access_token*' => Http::response([], 400)]);

    $response = $this->actingAs($this->admin)->get('/social-network/facebook/callback?code=bad-code');

    $response->assertRedirect();
    expect(session('alert_type'))->toBe('danger');
});

it('rejects triggering authorization for an entry with no platform', function () {
    $social = SocialNetwork::create(['url' => 'https://example.com', 'publish' => true]);

    $response = $this->actingAs($this->admin)->post("/admin/social-network/{$social->id}/authorize");

    $response->assertRedirect();
    expect(session('alert_type'))->toBe('danger');
});

it('rejects triggering authorization for a non-OAuth platform', function () {
    $social = SocialNetwork::create(['url' => 'https://t.me/example', 'platform' => SocialPlatformEnum::TELEGRAM, 'publish' => true]);

    $response = $this->actingAs($this->admin)->post("/admin/social-network/{$social->id}/authorize");

    $response->assertRedirect();
    expect(session('alert_type'))->toBe('danger');
});
