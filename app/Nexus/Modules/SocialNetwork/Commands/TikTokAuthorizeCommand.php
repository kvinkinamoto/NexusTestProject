<?php

namespace App\Nexus\Modules\SocialNetwork\Commands;

use App\Nexus\Modules\SocialNetwork\Services\Auth\TikTokAuthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class TikTokAuthorizeCommand extends Command
{
    protected $signature = 'tiktok:authorize {--code= : Authorization code from OAuth callback}';

    protected $description = 'Authorize TikTok and store tokens in cache';

    public function handle(TikTokAuthService $auth): void
    {
        $code = $this->option('code');

        if (! $code) {
            $url = $auth->getAuthUrl();

            $this->info('Open the link and IMMEDIATELY paste the code (it lives for ~10 minutes):');
            $this->line($url);
            $this->newLine();

            $code = $this->ask('Paste the code from the URL');
        }

        $verifier = $auth->getCachedCodeVerifier();

        if (! $verifier) {
            $this->error('code_verifier not found.');

            return;
        }

        try {
            $auth->storeTokensFromCode($code, $verifier);
            Cache::forget('tiktok:code_verifier');
            $this->info('Tokens saved.');
        } catch (\Throwable $e) {
            $this->error('Error: '.$e->getMessage());
        }
    }
}
