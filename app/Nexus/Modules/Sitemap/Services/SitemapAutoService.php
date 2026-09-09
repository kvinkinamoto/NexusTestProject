<?php

namespace App\Nexus\Modules\Sitemap\Services;

use App\Nexus\Modules\Sitemap\Dto\SitemapUrl;
use App\Nexus\Modules\Sitemap\Generators\AbstractSitemapGenerator;
use App\Nexus\Modules\Sitemap\Generators\CustomUrlGenerator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Nodex\Nexus\Services\SettingsBuilder;
use Spatie\Sitemap\Sitemap as SpatieSitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url as SpatieUrl;

/**
 * Ported from ZentaraStartProject's SitemapAutoService — auto-discovers one
 * generator per module (any Models-sibling `Sitemap/SitemapGenerator.php`
 * extending AbstractSitemapGenerator) instead of requiring each module to
 * register itself anywhere. Mode/base_url/split_size come from the
 * 'sitemap' module's #[Setting]s (see Models/SitemapCustomUrl.php) via the
 * same SettingsBuilder every other module's settings already use, replacing
 * the old project's ModuleSettingService.
 */
class SitemapAutoService
{
    public function generateAuto(?string $baseUrl = null, ?bool $single = null): void
    {
        $resolvedBase = $baseUrl ?: (SettingsBuilder::get('sitemap', 'base_url') ?: config('app.url'));
        $single ??= SettingsBuilder::get('sitemap', 'mode', 'multi') === 'single';
        $generators = $this->discoverGenerators();

        if (empty($generators)) {
            Log::warning('[Sitemap] No generators found.');

            return;
        }

        if ($single) {
            $this->generateSingle($generators, $resolvedBase);
        } else {
            $this->generateMulti($generators, $resolvedBase);
        }
    }

    private function generateSingle(array $generators, string $baseUrl): void
    {
        $sitemap = SpatieSitemap::create();
        $total = 0;

        foreach ($generators as ['class' => $class, 'config' => $config]) {
            try {
                $instance = new $class($config);
                $entryBaseUrl = $config['base_url'] ?? $baseUrl;
                $count = 0;

                foreach ($instance->urls($entryBaseUrl) as $sitemapUrl) {
                    $this->addUrl($sitemap, $sitemapUrl);
                    $count++;
                }

                Log::info('[Sitemap] Merged '.$count.' URLs from '.class_basename($class));
                $total += $count;
            } catch (\Throwable $e) {
                Log::error('[Sitemap] '.class_basename($class).' failed: '.$e->getMessage(), ['exception' => $e]);
            }
        }

        $path = public_path('sitemap.xml');
        File::ensureDirectoryExists(dirname($path));
        $sitemap->writeToFile($path);

        Log::info("[Sitemap] Generated: sitemap.xml ({$total} URLs, single mode)");
    }

    private function generateMulti(array $generators, string $baseUrl): void
    {
        $masterUrls = [];
        $splitSize = (int) SettingsBuilder::get('sitemap', 'split_size', 0);

        foreach ($generators as ['class' => $class, 'config' => $config]) {
            $filename = $config['filename'] ?? null;

            if (! $filename) {
                Log::warning('[Sitemap] '.class_basename($class).' has no filename in sitemapConfig() — skipped.');

                continue;
            }

            if ($filename === 'sitemap.xml') {
                Log::warning('[Sitemap] '.class_basename($class).': filename "sitemap.xml" is reserved for the master index.');

                continue;
            }

            $entryBaseUrl = $config['base_url'] ?? $baseUrl;
            $maxUrls = $splitSize > 0 ? $splitSize : (int) ($config['split']['max_urls'] ?? 0);
            $path = public_path($filename);

            File::ensureDirectoryExists(dirname($path));

            try {
                $instance = new $class($config);

                if ($maxUrls > 0) {
                    $chunks = $this->writeSplit($instance->urls($entryBaseUrl), $path, $maxUrls);

                    if (count($chunks) === 1) {
                        rename($chunks[0], $path);
                        $masterUrls[] = rtrim($entryBaseUrl, '/').'/'.ltrim($filename, '/');
                        Log::info("[Sitemap] Generated: {$filename} (no split, all URLs fit in one file)");
                    } elseif (count($chunks) > 1) {
                        $subIndex = SitemapIndex::create();
                        foreach ($chunks as $chunk) {
                            $subIndex->add(rtrim($entryBaseUrl, '/').'/'.basename($chunk));
                        }
                        $subIndex->writeToFile($path);
                        $masterUrls[] = rtrim($entryBaseUrl, '/').'/'.ltrim($filename, '/');
                        Log::info('[Sitemap] Generated: '.$filename.' (sub-index, '.count($chunks).' chunks)');
                    }
                } else {
                    $sitemap = SpatieSitemap::create();
                    $count = 0;

                    foreach ($instance->urls($entryBaseUrl) as $sitemapUrl) {
                        $this->addUrl($sitemap, $sitemapUrl);
                        $count++;
                    }

                    $sitemap->writeToFile($path);
                    $masterUrls[] = rtrim($entryBaseUrl, '/').'/'.ltrim($filename, '/');

                    Log::info("[Sitemap] Generated: {$filename} ({$count} URLs)");
                }
            } catch (\Throwable $e) {
                Log::error('[Sitemap] '.class_basename($class)." ({$filename}) failed: ".$e->getMessage(), ['exception' => $e]);
            }
        }

        if (! empty($masterUrls)) {
            $index = SitemapIndex::create();
            foreach ($masterUrls as $url) {
                $index->add($url);
            }
            $index->writeToFile(public_path('sitemap.xml'));
            Log::info('[Sitemap] Generated: sitemap.xml (master index, '.count($masterUrls).' entries)');
        }
    }

    private function addUrl(SpatieSitemap $sitemap, SitemapUrl $dto): void
    {
        $tag = SpatieUrl::create($dto->url)
            ->setLastModificationDate($dto->lastmod ?? now())
            ->setChangeFrequency($dto->changefreq)
            ->setPriority($dto->priority);

        foreach ($dto->images as $image) {
            if (! empty($image['url'])) {
                $tag->addImage($image['url'], $image['title'] ?? '', $image['caption'] ?? '');
            }
        }

        $sitemap->add($tag);
    }

    private function writeSplit(iterable $urls, string $mainPath, int $maxUrls): array
    {
        $dir = dirname($mainPath);
        $base = pathinfo($mainPath, PATHINFO_FILENAME);
        $ext = pathinfo($mainPath, PATHINFO_EXTENSION);
        $chunks = [];

        $current = SpatieSitemap::create();
        $currentCount = 0;
        $chunkIndex = 1;

        foreach ($urls as $sitemapUrl) {
            if ($currentCount > 0 && $currentCount % $maxUrls === 0) {
                $chunkPath = "{$dir}/{$base}_chunk_{$chunkIndex}.{$ext}";
                $current->writeToFile($chunkPath);
                $chunks[] = $chunkPath;

                $current = SpatieSitemap::create();
                $chunkIndex++;
                $currentCount = 0;
            }

            $this->addUrl($current, $sitemapUrl);
            $currentCount++;
        }

        if ($currentCount > 0) {
            $chunkPath = "{$dir}/{$base}_chunk_{$chunkIndex}.{$ext}";
            $current->writeToFile($chunkPath);
            $chunks[] = $chunkPath;
        }

        return $chunks;
    }

    private function discoverGenerators(): array
    {
        $generators = [];

        $scan = function (string $basePath, string $namespace) use (&$generators): void {
            if (! File::isDirectory($basePath)) {
                return;
            }

            foreach (File::directories($basePath) as $modulePath) {
                $file = $modulePath.DIRECTORY_SEPARATOR.'Sitemap'.DIRECTORY_SEPARATOR.'SitemapGenerator.php';

                if (! File::exists($file)) {
                    continue;
                }

                $fqcn = $namespace.'\\'.basename($modulePath).'\\Sitemap\\SitemapGenerator';

                if (! class_exists($fqcn) || ! is_a($fqcn, AbstractSitemapGenerator::class, true)) {
                    continue;
                }

                $config = $fqcn::sitemapConfig();

                if (empty($config) || ! ($config['enabled'] ?? true)) {
                    continue;
                }

                $generators[] = ['class' => $fqcn, 'config' => $config];
            }
        };

        $scan(app_path('Nexus/Modules'), 'App\\Nexus\\Modules');
        $scan(app_path('Nexus/Plugins'), 'App\\Nexus\\Plugins');

        if (Schema::hasTable('sitemap_custom_urls')) {
            $config = CustomUrlGenerator::sitemapConfig();
            if ($config['enabled'] ?? true) {
                $generators[] = ['class' => CustomUrlGenerator::class, 'config' => $config];
            }
        }

        return $generators;
    }
}
