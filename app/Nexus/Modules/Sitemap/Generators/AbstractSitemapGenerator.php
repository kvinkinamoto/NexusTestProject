<?php

namespace App\Nexus\Modules\Sitemap\Generators;

use App\Nexus\Modules\Sitemap\Contracts\SitemapGeneratorInterface;
use App\Nexus\Modules\Sitemap\Dto\SitemapUrl;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Ported from ZentaraStartProject's app/Zentara/Modules/Sitemap, dropping its
 * locale_strategy/alternates/x-default support — Nexus has no Language
 * module yet (see FormBuilder::getLanguages(), which always resolves to a
 * single configured locale today), so that machinery would be untestable
 * dead weight until one exists.
 */
abstract class AbstractSitemapGenerator implements SitemapGeneratorInterface
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->defaultConfig(), $config);
    }

    /**
     * Static sitemap config for auto-discovery. Override with at minimum
     * `filename`. Return [] (default) to exclude this generator.
     */
    public static function sitemapConfig(): array
    {
        return [];
    }

    /**
     * Default config — override in a subclass for module-specific defaults.
     *
     * Keys:
     *   enabled        bool   — false skips this generator entirely
     *   chunk          bool   — use a DB cursor for memory-efficient streaming
     *   changefreq     string — always|hourly|daily|weekly|monthly|yearly|never
     *   priority       float  — 0.0-1.0
     *   lastmod_field  string — model attribute used for <lastmod>
     */
    protected function defaultConfig(): array
    {
        return [
            'enabled' => true,
            'chunk' => false,
            'changefreq' => 'daily',
            'priority' => 0.8,
            'lastmod_field' => 'updated_at',
        ];
    }

    /**
     * Return the Eloquent query used to fetch records. Apply your own
     * publish/active scopes here.
     */
    abstract protected function query(): Builder;

    /**
     * Build the URL path (without base URL, without leading slash) for one
     * record. Return null to skip this record.
     */
    abstract protected function buildPath(mixed $record): ?string;

    /**
     * Optional hook — override to provide image sitemap entries for a record.
     * $baseUrl is the same resolved base (settings override or config('app.url'))
     * the record's own <loc> is built from — build any absolute image URL
     * from it too, not from the global url() helper, so both stay consistent
     * when `sitemap.base_url` differs from APP_URL (e.g. a CDN override).
     *
     * @return array<int, array{url: string, title?: string, caption?: string}>
     */
    protected function buildImages(mixed $record, string $baseUrl): array
    {
        return [];
    }

    /**
     * Yields SitemapUrl instances. Uses a DB cursor when chunk=true so
     * memory stays flat for large datasets.
     */
    public function urls(string $baseUrl): \Generator
    {
        if (! ($this->config['enabled'] ?? true)) {
            return;
        }

        $records = ($this->config['chunk'] ?? false)
            ? $this->query()->cursor()
            : $this->query()->get();

        foreach ($records as $record) {
            $path = $this->buildPath($record);

            if (! $path) {
                continue;
            }

            yield new SitemapUrl(
                url: $this->absolute($baseUrl, $path),
                lastmod: $this->resolveLastmod($record),
                changefreq: $this->config['changefreq'],
                priority: (float) $this->config['priority'],
                images: $this->buildImages($record, $baseUrl),
            );
        }
    }

    protected function resolveLastmod(mixed $record): ?Carbon
    {
        $field = $this->config['lastmod_field'] ?? 'updated_at';
        $value = $record->{$field} ?? null;

        if (! $value) {
            return null;
        }

        return $value instanceof Carbon ? $value : Carbon::parse($value);
    }

    private function absolute(string $baseUrl, string $path): string
    {
        return rtrim($baseUrl, '/').'/'.ltrim($path, '/');
    }
}
