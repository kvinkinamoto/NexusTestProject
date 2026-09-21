<?php

namespace App\Nexus\Modules\ProductRelease\Commands;

use App\Nexus\Modules\ProductRelease\Services\LocalModuleReleaseService;
use App\Nexus\Modules\ShopProduct\Enums\PackageType;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * One-time (idempotent, safe to re-run) batch job that gives every existing
 * "pack" ShopProduct a genuinely installable release, built from the real
 * module folders in nexus-modules-archive (or, for a module that exists only
 * in this project — ShopProductVariation — this project's own
 * app/Nexus/Modules/**). Two shapes:
 *
 * - An ATOMIC pack (its own catalog listing already names exactly one real
 *   module, e.g. "ActivityLog", "GraphQL API Plugin") gets its release
 *   attached directly to its own existing ShopProduct row — no pack-items
 *   pivot needed, nothing to decompose in the cart.
 * - A MULTI pack (e.g. "E-commerce Core Pack") gets one hidden
 *   (`publish: false`) component ShopProduct per real module it bundles,
 *   each with its own release and a fair split of the pack's price (so the
 *   pack's own price stays intact once CartService::add() decomposes it —
 *   see that class's docblock), linked back to the pack via
 *   ShopProduct::packItems().
 *
 * "E-commerce Mega-Bundle" reuses Core/Pricing&Promotions/Loyalty/
 * Reviews-Q&A-Wishlist's own components (its own catalog description says
 * exactly this) rather than creating duplicates — its price is recomputed
 * here as the sum of those 18 shared components minus a 27.5% bundle
 * discount (the midpoint of pricing-and-tiers.md's own stated "~25-30%
 * discount against buying separately"), replacing its previous flat 14999
 * price, which was picked independently of the 4 packs it's supposed to
 * discount and was actually *higher* than their combined price (10546) —
 * an inconsistency that would have shown up the moment a Mega-Bundle
 * purchase decomposed into its real components in the cart.
 */
class SyncModulesFromArchiveCommand extends Command
{
    protected $signature = 'nexus:store:sync-modules-from-archive {--archive= : Override the nexus-modules-archive path}';

    protected $description = 'Create pack-item component products, zip their real module folders, and attach a ProductRelease to every catalog product';

    private const MEGA_BUNDLE_DISCOUNT = 0.275;

    /** @var array<string, array{atomic?: array{module: string, source: string}, items?: array<int, array{name: string, module: string, source: string}>, reuses?: array<int, string>}> */
    private const PACK_MAP = [
        'ecommerce-core-pack' => ['items' => [
            ['name' => 'Currency', 'module' => 'Currency', 'source' => 'modules'],
            ['name' => 'ShopCategory', 'module' => 'ShopCategory', 'source' => 'modules'],
            ['name' => 'ShopAttribute', 'module' => 'ShopAttribute', 'source' => 'modules'],
            ['name' => 'ShopProductType', 'module' => 'ShopProductType', 'source' => 'modules'],
            ['name' => 'ShopProduct (модуль каталогу)', 'module' => 'ShopProduct', 'source' => 'modules'],
            ['name' => 'ShopProductVariation', 'module' => 'ShopProductVariation', 'source' => 'project'],
            ['name' => 'Cart', 'module' => 'Cart', 'source' => 'modules'],
            ['name' => 'Order', 'module' => 'Order', 'source' => 'modules'],
            ['name' => 'OrderStatus', 'module' => 'OrderStatus', 'source' => 'modules'],
            ['name' => 'Storefront', 'module' => 'Storefront', 'source' => 'modules'],
        ]],
        'pricing-promotions-pack' => ['items' => [
            ['name' => 'PromoCode', 'module' => 'PromoCode', 'source' => 'modules'],
            ['name' => 'DiscountRule', 'module' => 'DiscountRule', 'source' => 'modules'],
            ['name' => 'Promotion', 'module' => 'Promotion', 'source' => 'modules'],
        ]],
        'payments-ua-pack' => ['atomic' => ['module' => 'PaymentMethod', 'source' => 'modules']],
        'delivery-tracking-ua-pack' => ['items' => [
            ['name' => 'Delivery', 'module' => 'Delivery', 'source' => 'modules'],
            ['name' => 'DeliveryType', 'module' => 'DeliveryType', 'source' => 'modules'],
            ['name' => 'Shipment', 'module' => 'Shipment', 'source' => 'modules'],
            ['name' => 'NovaPoshtaDelivery', 'module' => 'NovaPoshtaDelivery', 'source' => 'plugins'],
            ['name' => 'UkrPoshtaDelivery', 'module' => 'UkrPoshtaDelivery', 'source' => 'plugins'],
        ]],
        'loyalty-pack' => ['items' => [
            ['name' => 'Bonus', 'module' => 'Bonus', 'source' => 'modules'],
            ['name' => 'BonusTransaction', 'module' => 'BonusTransaction', 'source' => 'modules'],
        ]],
        'reviews-qa-wishlist-pack' => ['items' => [
            ['name' => 'ProductQuestion', 'module' => 'ProductQuestion', 'source' => 'modules'],
            ['name' => 'ProductReview', 'module' => 'ProductReview', 'source' => 'modules'],
            ['name' => 'Wishlist', 'module' => 'Wishlist', 'source' => 'modules'],
        ]],
        'ecommerce-mega-bundle' => ['reuses' => [
            'ecommerce-core-pack', 'pricing-promotions-pack', 'loyalty-pack', 'reviews-qa-wishlist-pack',
        ]],
        'social-network-pack' => ['atomic' => ['module' => 'SocialNetwork', 'source' => 'modules']],
        'activity-log' => ['atomic' => ['module' => 'ActivityLog', 'source' => 'modules']],
        'backup-restore' => ['items' => [
            ['name' => 'Backup', 'module' => 'Backup', 'source' => 'modules'],
            ['name' => 'Restore', 'module' => 'Restore', 'source' => 'modules'],
        ]],
        'advanced-translations' => ['items' => [
            ['name' => 'Language', 'module' => 'Language', 'source' => 'modules'],
            ['name' => 'Translations', 'module' => 'Translations', 'source' => 'modules'],
        ]],
        'library-vertical-pack' => ['items' => [
            ['name' => 'Author', 'module' => 'Author', 'source' => 'modules'],
            ['name' => 'Book', 'module' => 'Book', 'source' => 'modules'],
            ['name' => 'Edition', 'module' => 'Edition', 'source' => 'modules'],
            ['name' => 'BookSeries', 'module' => 'BookSeries', 'source' => 'modules'],
            ['name' => 'BookTranslation', 'module' => 'BookTranslation', 'source' => 'modules'],
            ['name' => 'BookFile', 'module' => 'BookFile', 'source' => 'modules'],
            ['name' => 'BookSearchIndex', 'module' => 'BookSearchIndex', 'source' => 'modules'],
            ['name' => 'BookImport', 'module' => 'BookImport', 'source' => 'modules'],
            ['name' => 'CopyrightStatus', 'module' => 'CopyrightStatus', 'source' => 'modules'],
            ['name' => 'ReadingProgress', 'module' => 'ReadingProgress', 'source' => 'modules'],
            ['name' => 'ReadingDiary', 'module' => 'ReadingDiary', 'source' => 'modules'],
            ['name' => 'ReaderAnnotation', 'module' => 'ReaderAnnotation', 'source' => 'modules'],
            ['name' => 'ReadingActivityDay', 'module' => 'ReadingActivityDay', 'source' => 'modules'],
            ['name' => 'AiApiKey', 'module' => 'AiApiKey', 'source' => 'modules'],
            ['name' => 'TelegramAnnouncement', 'module' => 'TelegramAnnouncement', 'source' => 'modules'],
        ]],
        'graphql-api-plugin' => ['atomic' => ['module' => 'GraphQL', 'source' => 'plugins']],
        'advertising-pack' => ['atomic' => ['module' => 'Advertising', 'source' => 'modules']],

        // --- Безкоштовні (вже по одному реальному модулю, крім forms/blog/widgets) ---
        'analytics' => ['atomic' => ['module' => 'Analytics', 'source' => 'modules']],
        'storefront-theme' => ['atomic' => ['module' => 'Storefront', 'source' => 'modules']],
        'forms-module' => ['items' => [
            ['name' => 'Form', 'module' => 'Form', 'source' => 'modules'],
            ['name' => 'FormSubmission', 'module' => 'FormSubmission', 'source' => 'modules'],
        ]],
        'blog-module' => ['items' => [
            ['name' => 'BlogPost', 'module' => 'BlogPost', 'source' => 'modules'],
            ['name' => 'BlogCategory', 'module' => 'BlogCategory', 'source' => 'modules'],
        ]],
        'slider-module' => ['atomic' => ['module' => 'Slider', 'source' => 'modules']],
        'menu-module' => ['atomic' => ['module' => 'Menu', 'source' => 'modules']],
        'redirect-module' => ['atomic' => ['module' => 'Redirect', 'source' => 'modules']],
        'sitemap-module' => ['atomic' => ['module' => 'Sitemap', 'source' => 'modules']],
        'demo-module' => ['atomic' => ['module' => 'Demo', 'source' => 'modules']],
        'widgets-pack' => ['items' => [
            ['name' => 'AdvertisingSlot', 'module' => 'AdvertisingSlot', 'source' => 'widgets'],
            ['name' => 'UsersCount', 'module' => 'UsersCount', 'source' => 'widgets'],
            ['name' => 'RecentDemoRecords', 'module' => 'RecentDemoRecords', 'source' => 'widgets'],
        ]],
    ];

    public function handle(LocalModuleReleaseService $releases): int
    {
        $archiveRoot = $this->option('archive') ?: 'C:\\OSPanel1\\home\\nexus-modules-archive';
        $projectModulesRoot = base_path('app'.DIRECTORY_SEPARATOR.'Nexus'.DIRECTORY_SEPARATOR.'Modules');

        // Two passes: first every pack that owns real items (so Mega-Bundle
        // has something to reuse), then Mega-Bundle's own reuse + repricing.
        foreach (self::PACK_MAP as $slug => $config) {
            if (isset($config['reuses'])) {
                continue;
            }

            $pack = ShopProduct::query()->where('slug', $slug)->first();
            if (! $pack) {
                $this->warn("Skipping \"{$slug}\" — no such product in the catalog.");

                continue;
            }

            if (isset($config['atomic'])) {
                $this->syncAtomic($releases, $pack, $config['atomic'], $archiveRoot, $projectModulesRoot);

                continue;
            }

            $this->syncMultiItemPack($releases, $pack, $config['items'], $archiveRoot, $projectModulesRoot);
        }

        foreach (self::PACK_MAP as $slug => $config) {
            if (! isset($config['reuses'])) {
                continue;
            }

            $this->syncReusingPack($slug, $config['reuses']);
        }

        return self::SUCCESS;
    }

    private function syncAtomic(LocalModuleReleaseService $releases, ShopProduct $pack, array $atomic, string $archiveRoot, string $projectModulesRoot): void
    {
        $sourceDir = $this->resolveSourceDir($atomic['source'], $atomic['module'], $archiveRoot, $projectModulesRoot);
        $releases->sync($pack, $sourceDir, $atomic['module']);
        $this->info("Synced release for \"{$pack->name}\" (atomic, {$atomic['module']}).");
    }

    private function syncMultiItemPack(LocalModuleReleaseService $releases, ShopProduct $pack, array $items, string $archiveRoot, string $projectModulesRoot): void
    {
        $count = count($items);
        $perItem = intdiv((int) $pack->price, $count);
        $componentIds = [];

        foreach ($items as $index => $item) {
            $price = $index === $count - 1
                ? (int) $pack->price - $perItem * ($count - 1)
                : $perItem;

            $component = ShopProduct::query()->updateOrCreate(
                ['slug' => Str::slug($item['module'])],
                [
                    'name' => $item['name'],
                    'price' => $price,
                    'package_type' => $item['source'] === 'plugins' ? PackageType::PLUGIN : ($item['source'] === 'widgets' ? PackageType::WIDGET : PackageType::MODULE),
                    'category_id' => $pack->category_id,
                    'publish' => false,
                ],
            );

            $sourceDir = $this->resolveSourceDir($item['source'], $item['module'], $archiveRoot, $projectModulesRoot);
            $releases->sync($component, $sourceDir, $item['module']);

            $componentIds[] = $component->id;
        }

        $pack->packItems()->syncWithoutDetaching($componentIds);
        $this->info("Synced \"{$pack->name}\" — {$count} pack items, prices split from ".number_format((float) $pack->price, 0, ',', ' ').' грн.');
    }

    private function syncReusingPack(string $slug, array $reusedPackSlugs): void
    {
        $pack = ShopProduct::query()->where('slug', $slug)->first();
        if (! $pack) {
            $this->warn("Skipping \"{$slug}\" — no such product in the catalog.");

            return;
        }

        $reusedPacks = ShopProduct::query()->whereIn('slug', $reusedPackSlugs)->with('packItems')->get();
        $allComponentIds = $reusedPacks->flatMap(fn (ShopProduct $reused) => $reused->packItems->pluck('id'))->unique()->values();

        $pack->packItems()->sync($allComponentIds);

        $bundlePrice = (int) round($reusedPacks->sum('price') * (1 - self::MEGA_BUNDLE_DISCOUNT));
        $oldPrice = (int) $pack->price;
        $pack->update(['price' => $bundlePrice]);

        $this->info("Synced \"{$pack->name}\" — reuses ".$allComponentIds->count().' components from ['.implode(', ', $reusedPackSlugs)."]; price {$oldPrice} -> {$bundlePrice} грн (sum ".$reusedPacks->sum('price').' минус '.(self::MEGA_BUNDLE_DISCOUNT * 100).'%).');
    }

    private function resolveSourceDir(string $source, string $module, string $archiveRoot, string $projectModulesRoot): string
    {
        return match ($source) {
            'modules' => $archiveRoot.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$module,
            'plugins' => $archiveRoot.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$module,
            'widgets' => $archiveRoot.DIRECTORY_SEPARATOR.'widgets'.DIRECTORY_SEPARATOR.$module,
            'project' => $projectModulesRoot.DIRECTORY_SEPARATOR.$module,
            default => throw new \RuntimeException("Unknown module source \"{$source}\"."),
        };
    }
}
