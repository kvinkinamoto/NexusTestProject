<?php

namespace App\Nexus\Modules\Sitemap\Commands;

use App\Nexus\Modules\Sitemap\Services\SitemapAutoService;
use Illuminate\Console\Command;
use Nodex\Nexus\Services\SettingsBuilder;

class GenerateSitemaps extends Command
{
    protected $signature = 'nexus:sitemap:generate';

    protected $description = 'Generate XML sitemaps (mode/base URL/split size managed in Admin → Site → Sitemap → Settings).';

    public function handle(SitemapAutoService $service): int
    {
        $mode = SettingsBuilder::get('sitemap', 'mode', 'multi');
        $baseUrl = SettingsBuilder::get('sitemap', 'base_url') ?: null;

        $this->line('  Mode       : <comment>'.($mode === 'single' ? 'single (all URLs → sitemap.xml)' : 'multi (separate files + index)').'</comment>');
        $this->line('  Base URL   : <comment>'.($baseUrl ?: config('app.url').' (APP_URL)').'</comment>');
        $this->line('  Split size : <comment>'.((int) SettingsBuilder::get('sitemap', 'split_size', 0) ?: 'off').'</comment>');
        $this->newLine();

        $this->info('Discovering generators and generating sitemaps...');
        $service->generateAuto($baseUrl, $mode === 'single');
        $this->info('Done.');

        return self::SUCCESS;
    }
}
