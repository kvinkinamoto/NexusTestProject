<?php

namespace App\Nexus\Modules\SocialNetwork\Commands;

use App\Nexus\Modules\SocialNetwork\Services\Auth\FacebookAuthService;
use Illuminate\Console\Command;

class FacebookAuthorizeCommand extends Command
{
    protected $signature = 'facebook:authorize {--code=}';

    protected $description = 'Authorize Facebook and store Page token in cache';

    public function handle(FacebookAuthService $auth): void
    {
        $code = $this->option('code');

        if (! $code) {
            $this->info('1. Open the link:');
            $this->line($auth->getAuthUrl());
            $this->newLine();
            $code = $this->ask('Paste the code from the URL');
        }

        try {
            $auth->storeTokensFromCode($code);
            $this->info('Facebook tokens saved.');
        } catch (\Throwable $e) {
            $this->error('Error: '.$e->getMessage());
        }
    }
}
