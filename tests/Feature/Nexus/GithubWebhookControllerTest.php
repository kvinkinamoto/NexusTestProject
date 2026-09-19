<?php

use App\Nexus\Modules\ProductRelease\Jobs\SyncGithubReleaseJob;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Support\Facades\Bus;

function githubReleasePayload(string $action = 'published'): array
{
    return [
        'action' => $action,
        'release' => ['tag_name' => 'v2.0.0'],
        'repository' => [
            'name' => 'seo-booster-pro',
            'owner' => ['login' => 'example-org'],
        ],
    ];
}

function signedGithubHeaders(array $payload, string $secret, string $event = 'release'): array
{
    $body = json_encode($payload);

    return [
        'X-GitHub-Event' => $event,
        'X-Hub-Signature-256' => 'sha256='.hash_hmac('sha256', $body, $secret),
    ];
}

beforeEach(function () {
    config(['services.github.webhook_secret' => 'test-webhook-secret']);

    $this->product = ShopProduct::create([
        'name' => 'SEO Booster Pro',
        'slug' => 'seo-booster-pro',
        'price' => 1499,
        'publish' => true,
        'package_type' => 'module',
        'github_owner' => 'example-org',
        'github_repo' => 'seo-booster-pro',
    ]);
});

it('queues a sync job for a validly signed published release event', function () {
    Bus::fake();

    $payload = githubReleasePayload();
    $headers = signedGithubHeaders($payload, 'test-webhook-secret');

    $this->postJson('/webhooks/github', $payload, $headers)
        ->assertStatus(202)
        ->assertJson(['status' => 'queued']);

    Bus::assertDispatched(SyncGithubReleaseJob::class, function (SyncGithubReleaseJob $job) {
        return $job->productId === $this->product->id && $job->tag === 'v2.0.0';
    });
});

it('rejects a request with an invalid signature', function () {
    Bus::fake();

    $payload = githubReleasePayload();

    $this->postJson('/webhooks/github', $payload, [
        'X-GitHub-Event' => 'release',
        'X-Hub-Signature-256' => 'sha256=not-the-right-signature',
    ])->assertStatus(401);

    Bus::assertNotDispatched(SyncGithubReleaseJob::class);
});

it('rejects a request with no signature header at all', function () {
    Bus::fake();

    $this->postJson('/webhooks/github', githubReleasePayload(), [
        'X-GitHub-Event' => 'release',
    ])->assertStatus(401);

    Bus::assertNotDispatched(SyncGithubReleaseJob::class);
});

it('ignores a release event for a repo that matches no product', function () {
    Bus::fake();

    $payload = githubReleasePayload();
    $payload['repository']['name'] = 'some-other-repo';
    $headers = signedGithubHeaders($payload, 'test-webhook-secret');

    $this->postJson('/webhooks/github', $payload, $headers)
        ->assertStatus(200)
        ->assertJson(['status' => 'ignored']);

    Bus::assertNotDispatched(SyncGithubReleaseJob::class);
});

it('ignores a non-release github event, responding to ping without dispatching', function () {
    Bus::fake();

    $payload = ['zen' => 'Responsive is better than fast.'];
    $headers = signedGithubHeaders($payload, 'test-webhook-secret', 'ping');

    $this->postJson('/webhooks/github', $payload, $headers)
        ->assertStatus(200)
        ->assertJson(['status' => 'pong']);

    Bus::assertNotDispatched(SyncGithubReleaseJob::class);
});

it('ignores a release event whose action is not published', function () {
    Bus::fake();

    $payload = githubReleasePayload(action: 'created');
    $headers = signedGithubHeaders($payload, 'test-webhook-secret');

    $this->postJson('/webhooks/github', $payload, $headers)
        ->assertStatus(200)
        ->assertJson(['status' => 'ignored']);

    Bus::assertNotDispatched(SyncGithubReleaseJob::class);
});

it('refuses every request when no webhook secret is configured', function () {
    config(['services.github.webhook_secret' => null]);
    Bus::fake();

    $payload = githubReleasePayload();
    $headers = signedGithubHeaders($payload, 'test-webhook-secret');

    $this->postJson('/webhooks/github', $payload, $headers)
        ->assertStatus(500);

    Bus::assertNotDispatched(SyncGithubReleaseJob::class);
});
