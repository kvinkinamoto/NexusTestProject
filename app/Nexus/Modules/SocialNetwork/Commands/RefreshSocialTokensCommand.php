<?php

namespace App\Nexus\Modules\SocialNetwork\Commands;

use App\Nexus\Modules\SocialNetwork\Services\Auth\FacebookAuthService;
use App\Nexus\Modules\SocialNetwork\Services\Auth\InstagramAuthService;
use Illuminate\Console\Command;

class RefreshSocialTokensCommand extends Command
{
    protected $signature = 'social-tokens:refresh';

    protected $description = 'Refresh social network tokens';

    public function handle(
        InstagramAuthService $instagram,
        FacebookAuthService $facebook,
    ): void {
        foreach (
            [
                'Instagram' => $instagram,
                'Facebook' => $facebook,
            ] as $name => $service
        ) {
            try {
                $service->refreshIfNeeded();
                $this->info("{$name} token OK");
            } catch (\Throwable $e) {
                $this->error("{$name}: ".$e->getMessage());
            }
        }
    }
}
