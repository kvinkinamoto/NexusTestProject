<?php

namespace Database\Seeders;

use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Generates a dark, "Nexus"-branded cover SVG for every catalog product
 * (accent color keyed off package_type, icon keyed off slug) and points
 * ShopProduct::$image at it. No product photography exists for these
 * items — they're admin-panel modules, not physical goods — so the cover
 * is abstract: glow + icon badge, no baked-in product name (kept out of
 * the pixels on purpose, since the name is already real, translatable DOM
 * text everywhere this image is shown; baking uk/en text into a static
 * image would only work for one locale). Written straight to public/ (not
 * the storage disk) because public/storage on this install isn't a
 * working symlink to storage/app/public. Idempotent — safe to re-run.
 */
class ProductThumbnailSeeder extends Seeder
{
    private const string OUTPUT_DIR = 'images/products';

    /** @var array<string, array{glow: string, label: string}> */
    private const array PACKAGE_TYPE_STYLE = [
        'module' => ['glow' => '#6366f1', 'label' => 'MODULE'],
        'plugin' => ['glow' => '#a855f7', 'label' => 'PLUGIN'],
        'widget' => ['glow' => '#f59e0b', 'label' => 'WIDGET'],
    ];

    public function run(): void
    {
        $outputPath = public_path(self::OUTPUT_DIR);
        File::ensureDirectoryExists($outputPath);

        ShopProduct::query()->each(function (ShopProduct $product) use ($outputPath): void {
            $style = self::PACKAGE_TYPE_STYLE[$product->package_type?->value] ?? self::PACKAGE_TYPE_STYLE['module'];

            $svg = $this->card($style['glow'], $style['label'], $this->icon($product->slug));

            File::put($outputPath.DIRECTORY_SEPARATOR.$product->slug.'.svg', $svg);

            $product->forceFill(['image' => '/'.self::OUTPUT_DIR.'/'.$product->slug.'.svg'])->save();
        });
    }

    private function card(string $glow, string $label, string $icon): string
    {
        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 1200">
            <defs>
                <linearGradient id="bg" x1="0" y1="0" x2="900" y2="1200" gradientUnits="userSpaceOnUse">
                    <stop offset="0" stop-color="#171717"/>
                    <stop offset="1" stop-color="#080808"/>
                </linearGradient>
                <radialGradient id="glow" cx="50%" cy="50%" r="50%">
                    <stop offset="0" stop-color="{$glow}" stop-opacity="0.55"/>
                    <stop offset="1" stop-color="{$glow}" stop-opacity="0"/>
                </radialGradient>
                <pattern id="dots" width="36" height="36" patternUnits="userSpaceOnUse">
                    <circle cx="2" cy="2" r="1.4" fill="#ffffff" fill-opacity="0.06"/>
                </pattern>
            </defs>

            <rect width="900" height="1200" fill="url(#bg)"/>
            <rect width="900" height="1200" fill="url(#dots)"/>
            <circle cx="710" cy="230" r="380" fill="url(#glow)"/>
            <circle cx="140" cy="1010" r="280" fill="url(#glow)" opacity="0.45"/>

            <rect x="24" y="24" width="852" height="1152" rx="28" fill="none" stroke="#ffffff" stroke-opacity="0.08" stroke-width="2"/>

            <text x="64" y="98" font-family="system-ui,-apple-system,Segoe UI,sans-serif" font-size="22" font-weight="700" letter-spacing="3" fill="{$glow}">{$label}</text>
            <text x="836" y="98" text-anchor="end" font-family="system-ui,-apple-system,Segoe UI,sans-serif" font-size="18" font-weight="600" letter-spacing="4" fill="#ffffff" fill-opacity="0.35">NEXUS</text>

            <circle cx="450" cy="560" r="160" fill="#ffffff" fill-opacity="0.07"/>
            <circle cx="450" cy="560" r="160" fill="none" stroke="#ffffff" stroke-opacity="0.14" stroke-width="2"/>
            <g transform="translate(450,560)">
                {$icon}
            </g>
        </svg>
        SVG;
    }

    private function icon(string $slug): string
    {
        return match ($slug) {
            'ecommerce-core-pack' => <<<'SVG'
                <rect x="-55" y="-55" width="110" height="130" rx="14" stroke="#fff" stroke-width="7" fill="none"/>
                <path d="M-28,-55 v-15 a28,28 0 0 1 56,0 v15" stroke="#fff" stroke-width="7" fill="none" stroke-linecap="round"/>
                SVG,
            'pricing-promotions-pack' => <<<'SVG'
                <circle cx="-35" cy="-35" r="18" stroke="#fff" stroke-width="7" fill="none"/>
                <circle cx="35" cy="35" r="18" stroke="#fff" stroke-width="7" fill="none"/>
                <line x1="-45" y1="45" x2="45" y2="-45" stroke="#fff" stroke-width="7" stroke-linecap="round"/>
                SVG,
            'payments-ua-pack' => <<<'SVG'
                <rect x="-70" y="-45" width="140" height="90" rx="12" stroke="#fff" stroke-width="7" fill="none"/>
                <line x1="-70" y1="-15" x2="70" y2="-15" stroke="#fff" stroke-width="7"/>
                <line x1="-45" y1="20" x2="-10" y2="20" stroke="#fff" stroke-width="7" stroke-linecap="round"/>
                SVG,
            'delivery-tracking-ua-pack' => <<<'SVG'
                <rect x="-70" y="-20" width="90" height="55" rx="6" stroke="#fff" stroke-width="7" fill="none"/>
                <path d="M20,-5 h35 l15,20 v15 h-50 Z" stroke="#fff" stroke-width="7" fill="none" stroke-linejoin="round"/>
                <circle cx="-40" cy="40" r="14" stroke="#fff" stroke-width="7" fill="none"/>
                <circle cx="40" cy="40" r="14" stroke="#fff" stroke-width="7" fill="none"/>
                SVG,
            'loyalty-pack' => <<<'SVG'
                <path d="M0,-65 L15.3,-20.1 L61.8,-20.1 L24.3,7.9 L38.8,52.9 L0,25.9 L-38.8,52.9 L-24.3,7.9 L-61.8,-20.1 L-15.3,-20.1 Z" fill="#fff"/>
                SVG,
            'reviews-qa-wishlist-pack' => <<<'SVG'
                <path d="M-65,-45 h130 a10,10 0 0 1 10,10 v60 a10,10 0 0 1 -10,10 h-90 l-25,25 v-25 h-15 a10,10 0 0 1 -10,-10 v-60 a10,10 0 0 1 10,-10 Z" stroke="#fff" stroke-width="6" fill="none" stroke-linejoin="round"/>
                <path d="M0,-30 5,-12 24,-12 9,0 14,18 0,7 -14,18 -9,0 -24,-12 -5,-12 Z" fill="#fff"/>
                SVG,
            'ecommerce-mega-bundle' => <<<'SVG'
                <rect x="-55" y="10" width="110" height="40" rx="8" fill="#fff" fill-opacity="0.35"/>
                <rect x="-55" y="-15" width="110" height="40" rx="8" fill="#fff" fill-opacity="0.65"/>
                <rect x="-55" y="-40" width="110" height="40" rx="8" fill="#fff"/>
                SVG,
            'social-network-pack' => <<<'SVG'
                <circle cx="-45" cy="0" r="16" stroke="#fff" stroke-width="7" fill="none"/>
                <circle cx="40" cy="-40" r="16" stroke="#fff" stroke-width="7" fill="none"/>
                <circle cx="40" cy="40" r="16" stroke="#fff" stroke-width="7" fill="none"/>
                <line x1="-31" y1="-8" x2="26" y2="-34" stroke="#fff" stroke-width="6"/>
                <line x1="-31" y1="8" x2="26" y2="34" stroke="#fff" stroke-width="6"/>
                SVG,
            'activity-log' => <<<'SVG'
                <circle cx="0" cy="0" r="65" stroke="#fff" stroke-width="7" fill="none"/>
                <line x1="0" y1="0" x2="0" y2="-38" stroke="#fff" stroke-width="7" stroke-linecap="round"/>
                <line x1="0" y1="0" x2="28" y2="14" stroke="#fff" stroke-width="7" stroke-linecap="round"/>
                SVG,
            'backup-restore' => <<<'SVG'
                <path d="M-50,10 a25,25 0 1,1 8,-40 a32,32 0 0,1 60,10 a22,22 0 1,1 -3,30 Z" fill="none" stroke="#fff" stroke-width="7" stroke-linejoin="round"/>
                <line x1="0" y1="-5" x2="0" y2="45" stroke="#fff" stroke-width="7" stroke-linecap="round"/>
                <path d="M-15,25 L0,45 L15,25" stroke="#fff" stroke-width="7" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                SVG,
            'advanced-translations' => <<<'SVG'
                <circle cx="0" cy="0" r="65" stroke="#fff" stroke-width="7" fill="none"/>
                <ellipse cx="0" cy="0" rx="28" ry="65" stroke="#fff" stroke-width="6" fill="none"/>
                <line x1="-65" y1="0" x2="65" y2="0" stroke="#fff" stroke-width="6"/>
                <line x1="-58" y1="-32" x2="58" y2="-32" stroke="#fff" stroke-width="5" opacity="0.7"/>
                <line x1="-58" y1="32" x2="58" y2="32" stroke="#fff" stroke-width="5" opacity="0.7"/>
                SVG,
            'library-vertical-pack' => <<<'SVG'
                <path d="M0,-50 C-20,-60 -55,-58 -65,-50 L-65,55 C-55,47 -20,49 0,58" stroke="#fff" stroke-width="7" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M0,-50 C20,-60 55,-58 65,-50 L65,55 C55,47 20,49 0,58" stroke="#fff" stroke-width="7" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                SVG,
            'graphql-api-plugin' => <<<'SVG'
                <path d="M-20,-55 L-60,0 L-20,55" stroke="#fff" stroke-width="9" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M20,-55 L60,0 L20,55" stroke="#fff" stroke-width="9" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                SVG,
            'advertising-pack' => <<<'SVG'
                <path d="M-60,-10 L0,-45 v90 L-60,20 Z" stroke="#fff" stroke-width="7" fill="none" stroke-linejoin="round"/>
                <path d="M0,-45 L45,-60 v110 L0,45" stroke="#fff" stroke-width="7" fill="none" stroke-linejoin="round"/>
                <line x1="-35" y1="22" x2="-25" y2="55" stroke="#fff" stroke-width="7" stroke-linecap="round"/>
                SVG,
            'analytics' => <<<'SVG'
                <rect x="-55" y="10" width="26" height="55" fill="#fff"/>
                <rect x="-13" y="-20" width="26" height="85" fill="#fff"/>
                <rect x="29" y="-45" width="26" height="110" fill="#fff"/>
                SVG,
            'storefront-theme' => <<<'SVG'
                <path d="M-65,-10 L-50,-55 L50,-55 L65,-10 Z" stroke="#fff" stroke-width="7" fill="none" stroke-linejoin="round"/>
                <rect x="-55" y="-10" width="110" height="75" stroke="#fff" stroke-width="7" fill="none"/>
                <rect x="-15" y="25" width="30" height="40" stroke="#fff" stroke-width="6" fill="none"/>
                SVG,
            'forms-module' => <<<'SVG'
                <rect x="-45" y="-55" width="90" height="115" rx="10" stroke="#fff" stroke-width="7" fill="none"/>
                <rect x="-18" y="-68" width="36" height="20" rx="6" fill="#fff"/>
                <line x1="-25" y1="-15" x2="25" y2="-15" stroke="#fff" stroke-width="6" stroke-linecap="round"/>
                <line x1="-25" y1="10" x2="25" y2="10" stroke="#fff" stroke-width="6" stroke-linecap="round"/>
                <line x1="-25" y1="35" x2="10" y2="35" stroke="#fff" stroke-width="6" stroke-linecap="round"/>
                SVG,
            'blog-module' => <<<'SVG'
                <rect x="-60" y="-50" width="120" height="100" rx="6" stroke="#fff" stroke-width="7" fill="none"/>
                <line x1="-40" y1="-25" x2="40" y2="-25" stroke="#fff" stroke-width="7" stroke-linecap="round"/>
                <line x1="-40" y1="-5" x2="40" y2="-5" stroke="#fff" stroke-width="6" stroke-linecap="round" opacity="0.8"/>
                <line x1="-40" y1="15" x2="10" y2="15" stroke="#fff" stroke-width="6" stroke-linecap="round" opacity="0.8"/>
                <line x1="-40" y1="35" x2="25" y2="35" stroke="#fff" stroke-width="6" stroke-linecap="round" opacity="0.6"/>
                SVG,
            'slider-module' => <<<'SVG'
                <rect x="-65" y="-45" width="130" height="90" rx="10" stroke="#fff" stroke-width="7" fill="none"/>
                <circle cx="-30" cy="-15" r="12" stroke="#fff" stroke-width="6" fill="none"/>
                <path d="M-55,30 L-15,-5 L15,20 L35,0 L55,25" stroke="#fff" stroke-width="7" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                SVG,
            'menu-module' => <<<'SVG'
                <line x1="-55" y1="-30" x2="55" y2="-30" stroke="#fff" stroke-width="9" stroke-linecap="round"/>
                <line x1="-55" y1="0" x2="55" y2="0" stroke="#fff" stroke-width="9" stroke-linecap="round"/>
                <line x1="-55" y1="30" x2="55" y2="30" stroke="#fff" stroke-width="9" stroke-linecap="round"/>
                SVG,
            'redirect-module' => <<<'SVG'
                <path d="M-50,-40 h60 a30,30 0 0 1 30,30 v50" stroke="#fff" stroke-width="9" fill="none" stroke-linecap="round"/>
                <path d="M-25,-60 L-50,-40 L-25,-20" stroke="#fff" stroke-width="9" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                SVG,
            'sitemap-module' => <<<'SVG'
                <circle cx="0" cy="-55" r="14" stroke="#fff" stroke-width="7" fill="none"/>
                <circle cx="-45" cy="20" r="14" stroke="#fff" stroke-width="7" fill="none"/>
                <circle cx="0" cy="20" r="14" stroke="#fff" stroke-width="7" fill="none"/>
                <circle cx="45" cy="20" r="14" stroke="#fff" stroke-width="7" fill="none"/>
                <path d="M0,-41 v20 M0,-21 h-45 v27 M0,-21 v27 M0,-21 h45 v27" stroke="#fff" stroke-width="6" fill="none"/>
                SVG,
            'demo-module' => <<<'SVG'
                <path d="M-18,-60 h36 M-12,-60 v35 L-45,55 a10,10 0 0 0 9,15 h72 a10,10 0 0 0 9,-15 L12,-25 v-35" stroke="#fff" stroke-width="7" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="-32" y1="15" x2="32" y2="15" stroke="#fff" stroke-width="6" stroke-linecap="round"/>
                SVG,
            'widgets-pack' => <<<'SVG'
                <rect x="-55" y="-55" width="45" height="45" rx="8" fill="#fff"/>
                <rect x="10" y="-55" width="45" height="45" rx="8" fill="#fff" fill-opacity="0.6"/>
                <rect x="-55" y="10" width="45" height="45" rx="8" fill="#fff" fill-opacity="0.6"/>
                <rect x="10" y="10" width="45" height="45" rx="8" fill="#fff" fill-opacity="0.35"/>
                SVG,
            default => <<<'SVG'
                <rect x="-55" y="-55" width="110" height="110" rx="16" stroke="#fff" stroke-width="7" fill="none"/>
                SVG,
        };
    }
}
