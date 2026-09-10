<?php

namespace App\Nexus\Modules\SocialNetwork\Commands;

use App\Nexus\Modules\SocialNetwork\Services\Auth\InstagramAuthService;
use Illuminate\Console\Command;

class InstagramAuthorizeCommand extends Command
{
    protected $signature = 'instagram:authorize {--code=}';

    protected $description = 'Authorize Instagram and store token in cache';

    public function handle(InstagramAuthService $auth): void
    {
        $code = $this->option('code');

        if (! $code) {
            $this->info('1. Open the link and IMMEDIATELY paste the code:');
            $this->line($auth->getAuthUrl());
            $this->newLine();
            $code = $this->ask('Paste the code from the URL');
        }

        try {
            $auth->storeTokensFromCode($code);
            $this->info('Instagram tokens saved.');
        } catch (\Throwable $e) {
            $this->error('Error: '.$e->getMessage());
        }
    }
}
