<?php

namespace App\Nexus\Modules\ProductRelease\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\ProductRelease\Jobs\SyncGithubReleaseJob;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Receives GitHub's "release" webhook (repo settings → Webhooks → Payload
 * URL = this route, content type application/json, event = "Releases") and
 * queues the same GithubReleaseSyncService::sync() the admin's manual
 * "Синхронізувати з GitHub" button and `nexus:store:sync-release` command
 * already use. One shared endpoint for every product's repo — the product
 * is resolved per-request from the payload's owner/repo, not the URL.
 */
class GithubWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $secret = config('services.github.webhook_secret');

        if (! $secret) {
            Log::error('GitHub webhook received but GITHUB_WEBHOOK_SECRET is not configured.');

            return response()->json(['status' => 'webhook not configured'], 500);
        }

        if (! $this->hasValidSignature($request, $secret)) {
            Log::warning('GitHub webhook signature mismatch', ['ip' => $request->ip()]);

            return response()->json(['status' => 'invalid signature'], 401);
        }

        $event = $request->header('X-GitHub-Event');
        $payload = $request->json()->all();

        if ($event === 'ping') {
            return response()->json(['status' => 'pong']);
        }

        if ($event !== 'release' || ($payload['action'] ?? null) !== 'published') {
            return response()->json(['status' => 'ignored']);
        }

        $owner = $payload['repository']['owner']['login'] ?? null;
        $repo = $payload['repository']['name'] ?? null;
        $tag = $payload['release']['tag_name'] ?? null;

        if (! $owner || ! $repo || ! $tag) {
            return response()->json(['status' => 'malformed payload'], 422);
        }

        $product = ShopProduct::query()
            ->where('github_owner', $owner)
            ->where('github_repo', $repo)
            ->first();

        if (! $product) {
            Log::info('GitHub webhook for unmatched repo', ['owner' => $owner, 'repo' => $repo]);

            return response()->json(['status' => 'ignored']);
        }

        SyncGithubReleaseJob::dispatch($product->id, $tag);

        return response()->json(['status' => 'queued'], 202);
    }

    private function hasValidSignature(Request $request, string $secret): bool
    {
        $signatureReceived = $request->header('X-Hub-Signature-256');

        if (! $signatureReceived) {
            return false;
        }

        $signatureLocal = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($signatureLocal, $signatureReceived);
    }
}
