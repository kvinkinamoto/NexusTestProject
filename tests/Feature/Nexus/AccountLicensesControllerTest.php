<?php

use App\Models\User;
use App\Nexus\Modules\License\Enums\LicenseStatus;
use App\Nexus\Modules\License\Http\Controllers\Public\AccountLicensesController;
use App\Nexus\Modules\License\Models\License;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\ProductRelease\Models\ProductRelease;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Isolate this file's throttle bucket from anything a previous test in
    // the same run may have hit — Laravel's default throttle key is
    // sha1(route domain + IP), not scoped to this route alone.
    Cache::flush();

    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();

    $this->product = ShopProduct::create(['name' => 'Module A', 'slug' => 'module-a', 'price' => 500, 'publish' => true]);

    $this->order = Order::create(['user_id' => $this->user->id, 'user_name' => 'Bob', 'user_phone' => '+380000000', 'payment_method' => 'liqpay', 'total_price' => 500]);

    $this->license = License::create([
        'user_id' => $this->user->id,
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'license_key' => License::generateKey(),
        'status' => LicenseStatus::ACTIVE,
        'max_activations' => 1,
    ]);
});

it('only lists the authenticated user\'s own licenses', function () {
    $otherLicense = License::create([
        'user_id' => $this->otherUser->id,
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'license_key' => License::generateKey(),
        'status' => LicenseStatus::ACTIVE,
        'max_activations' => 1,
    ]);

    $response = $this->actingAs($this->user)->get(route('account.licenses'));

    $response->assertOk();
    $response->assertSee($this->license->license_key);
    $response->assertDontSee($otherLicense->license_key);
});

it('redirects a guest to login instead of showing licenses', function () {
    $this->get(route('account.licenses'))->assertRedirect(route('login'));
});

it('activates a license for a project identifier', function () {
    $response = $this->actingAs($this->user)
        ->post(route('account.licenses.activate', $this->license->id), ['identifier' => 'my-project.dev']);

    $response->assertRedirect();
    $this->assertDatabaseHas('license_activations', ['license_id' => $this->license->id, 'identifier' => 'my-project.dev']);
});

it('refuses to activate a license belonging to another user', function () {
    $this->actingAs($this->otherUser)
        ->post(route('account.licenses.activate', $this->license->id), ['identifier' => 'my-project.dev'])
        ->assertForbidden();

    $this->assertDatabaseMissing('license_activations', ['license_id' => $this->license->id]);
});

it('refuses to activate beyond max_activations', function () {
    $this->license->activations()->create(['identifier' => 'first-project.dev', 'activated_at' => now()]);

    $this->actingAs($this->user)
        ->post(route('account.licenses.activate', $this->license->id), ['identifier' => 'second-project.dev'])
        ->assertSessionHasErrors('identifier');

    $this->assertDatabaseMissing('license_activations', ['identifier' => 'second-project.dev']);
});

it('refuses to activate the same project twice', function () {
    $this->license->activations()->create(['identifier' => 'my-project.dev', 'activated_at' => now()]);
    $this->license->update(['max_activations' => 5]);

    $this->actingAs($this->user)
        ->post(route('account.licenses.activate', $this->license->id), ['identifier' => 'my-project.dev'])
        ->assertSessionHasErrors('identifier');

    expect($this->license->activations()->count())->toBe(1);
});

it('refuses to activate a revoked license', function () {
    $this->license->update(['status' => LicenseStatus::REVOKED]);

    $this->actingAs($this->user)
        ->post(route('account.licenses.activate', $this->license->id), ['identifier' => 'my-project.dev'])
        ->assertSessionHasErrors('identifier');
});

it('deactivates an activation, freeing the slot', function () {
    $activation = $this->license->activations()->create(['identifier' => 'my-project.dev', 'activated_at' => now()]);

    $this->actingAs($this->user)
        ->delete(route('account.licenses.deactivate', ['license' => $this->license->id, 'activation' => $activation->id]))
        ->assertRedirect();

    $this->assertDatabaseMissing('license_activations', ['id' => $activation->id]);
});

it('refuses to deactivate another user\'s activation', function () {
    $activation = $this->license->activations()->create(['identifier' => 'my-project.dev', 'activated_at' => now()]);

    $this->actingAs($this->otherUser)
        ->delete(route('account.licenses.deactivate', ['license' => $this->license->id, 'activation' => $activation->id]))
        ->assertForbidden();

    $this->assertDatabaseHas('license_activations', ['id' => $activation->id]);
});

it('downloads the latest published release zip via a valid signed url', function () {
    $zipPath = storage_path('app/tmp/account-licenses-test.zip');
    File::ensureDirectoryExists(dirname($zipPath));
    File::put($zipPath, 'zip bytes');

    ProductRelease::create([
        'product_id' => $this->product->id,
        'version' => '1.0.0',
        'github_tag' => 'v1.0.0',
        'zip_disk_path' => $zipPath,
        'zip_size' => File::size($zipPath),
        'published_at' => now()->subDay(),
    ]);

    $url = AccountLicensesController::signedDownloadUrl($this->license);

    $this->actingAs($this->user)->get($url)->assertOk();

    File::delete($zipPath);
});

it('rejects a download request with a tampered or missing signature', function () {
    $this->actingAs($this->user)
        ->get(route('account.licenses.download', $this->license->id))
        ->assertForbidden();
});

it('refuses to download another user\'s license even with a validly signed url', function () {
    $url = AccountLicensesController::signedDownloadUrl($this->license);

    $this->actingAs($this->otherUser)->get($url)->assertForbidden();
});

it('returns 404 when the product has no published release yet', function () {
    $url = AccountLicensesController::signedDownloadUrl($this->license);

    $this->actingAs($this->user)->get($url)->assertNotFound();
});

it('throttles downloads past 20 per minute even with a validly signed url', function () {
    $zipPath = storage_path('app/tmp/account-licenses-throttle-test.zip');
    File::ensureDirectoryExists(dirname($zipPath));
    File::put($zipPath, 'zip bytes');

    ProductRelease::create([
        'product_id' => $this->product->id,
        'version' => '1.0.0',
        'github_tag' => 'v1.0.0',
        'zip_disk_path' => $zipPath,
        'zip_size' => File::size($zipPath),
        'published_at' => now()->subDay(),
    ]);

    $url = AccountLicensesController::signedDownloadUrl($this->license);

    $this->actingAs($this->user);
    for ($i = 0; $i < 20; $i++) {
        $this->get($url)->assertOk();
    }

    $this->get($url)->assertStatus(429);

    File::delete($zipPath);
});
