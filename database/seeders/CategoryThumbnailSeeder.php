<?php

namespace Database\Seeders;

use App\Nexus\Modules\ShopCategory\Models\ShopCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Category counterpart to ProductThumbnailSeeder — same dark "Nexus" cover
 * system (glow + icon badge, no baked-in name), but landscape (4:3, matching
 * home.blade.php's category-tile aspect-[4/3]) and with a bottom darkening
 * gradient added, since that tile overlays a real white category name on
 * top of the image via `flex items-end`. Accent color and icon are keyed
 * off slug directly (categories don't share a type taxonomy like products'
 * package_type). Written to public/ (not the storage disk) for the same
 * reason as ProductThumbnailSeeder — public/storage isn't a working symlink
 * on this install. Idempotent — safe to re-run.
 */
class CategoryThumbnailSeeder extends Seeder
{
    private const string OUTPUT_DIR = 'images/categories';

    /** @var array<string, string> */
    private const array ACCENT = [
        'catalog-modules' => '#f97316',
        'ecommerce-modules' => '#10b981',
        'payments-delivery' => '#0ea5e9',
        'marketing-analytics' => '#f43f5e',
        'social' => '#d946ef',
        'admin-tools' => '#14b8a6',
        'library-vertical' => '#b45309',
        'api-integrations' => '#7c3aed',
        'free-modules' => '#84cc16',
    ];

    public function run(): void
    {
        $outputPath = public_path(self::OUTPUT_DIR);
        File::ensureDirectoryExists($outputPath);

        ShopCategory::query()->each(function (ShopCategory $category) use ($outputPath): void {
            $glow = self::ACCENT[$category->slug] ?? '#6366f1';

            $svg = $this->card($glow, $this->icon($category->slug));

            File::put($outputPath.DIRECTORY_SEPARATOR.$category->slug.'.svg', $svg);

            $category->forceFill(['image' => '/'.self::OUTPUT_DIR.'/'.$category->slug.'.svg'])->save();
        });
    }

    private function card(string $glow, string $icon): string
    {
        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 900">
            <defs>
                <linearGradient id="bg" x1="0" y1="0" x2="1200" y2="900" gradientUnits="userSpaceOnUse">
                    <stop offset="0" stop-color="#171717"/>
                    <stop offset="1" stop-color="#080808"/>
                </linearGradient>
                <radialGradient id="glow" cx="50%" cy="50%" r="50%">
                    <stop offset="0" stop-color="{$glow}" stop-opacity="0.55"/>
                    <stop offset="1" stop-color="{$glow}" stop-opacity="0"/>
                </radialGradient>
                <linearGradient id="fade" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#000000" stop-opacity="0"/>
                    <stop offset="1" stop-color="#000000" stop-opacity="0.75"/>
                </linearGradient>
                <pattern id="dots" width="36" height="36" patternUnits="userSpaceOnUse">
                    <circle cx="2" cy="2" r="1.4" fill="#ffffff" fill-opacity="0.06"/>
                </pattern>
            </defs>

            <rect width="1200" height="900" fill="url(#bg)"/>
            <rect width="1200" height="900" fill="url(#dots)"/>
            <circle cx="950" cy="180" r="380" fill="url(#glow)"/>
            <circle cx="200" cy="760" r="280" fill="url(#glow)" opacity="0.4"/>

            <rect x="24" y="24" width="1152" height="852" rx="28" fill="none" stroke="#ffffff" stroke-opacity="0.08" stroke-width="2"/>

            <text x="836" y="74" text-anchor="end" font-family="system-ui,-apple-system,Segoe UI,sans-serif" font-size="18" font-weight="600" letter-spacing="4" fill="#ffffff" fill-opacity="0.35">NEXUS</text>

            <circle cx="600" cy="370" r="150" fill="#ffffff" fill-opacity="0.07"/>
            <circle cx="600" cy="370" r="150" fill="none" stroke="#ffffff" stroke-opacity="0.14" stroke-width="2"/>
            <g transform="translate(600,370)">
                {$icon}
            </g>

            <rect x="0" y="560" width="1200" height="340" fill="url(#fade)"/>
        </svg>
        SVG;
    }

    private function icon(string $slug): string
    {
        return match ($slug) {
            'catalog-modules' => <<<'SVG'
                <path d="M-65,-35 h35 l15,20 h80 v75 h-130 Z" stroke="#fff" stroke-width="8" fill="none" stroke-linejoin="round"/>
                SVG,
            'ecommerce-modules' => <<<'SVG'
                <path d="M-65,-55 h20 l15,80 h65" stroke="#fff" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M-30,-25 h95 l-12,45 h-70 Z" stroke="#fff" stroke-width="8" fill="none" stroke-linejoin="round"/>
                <circle cx="-15" cy="55" r="12" fill="#fff"/>
                <circle cx="45" cy="55" r="12" fill="#fff"/>
                SVG,
            'payments-delivery' => <<<'SVG'
                <rect x="-60" y="-50" width="120" height="100" rx="10" stroke="#fff" stroke-width="8" fill="none"/>
                <line x1="-60" y1="-10" x2="60" y2="-10" stroke="#fff" stroke-width="7"/>
                <line x1="0" y1="-50" x2="0" y2="-10" stroke="#fff" stroke-width="7"/>
                <path d="M-25,25 L-8,42 L28,6" stroke="#fff" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                SVG,
            'marketing-analytics' => <<<'SVG'
                <path d="M-60,40 L-20,0 L10,25 L60,-45" stroke="#fff" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M20,-45 h40 v40" stroke="#fff" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                SVG,
            'social' => <<<'SVG'
                <circle cx="-45" cy="0" r="16" stroke="#fff" stroke-width="7" fill="none"/>
                <circle cx="40" cy="-40" r="16" stroke="#fff" stroke-width="7" fill="none"/>
                <circle cx="40" cy="40" r="16" stroke="#fff" stroke-width="7" fill="none"/>
                <line x1="-31" y1="-8" x2="26" y2="-34" stroke="#fff" stroke-width="6"/>
                <line x1="-31" y1="8" x2="26" y2="34" stroke="#fff" stroke-width="6"/>
                SVG,
            'admin-tools' => $this->gearIcon(),
            'library-vertical' => <<<'SVG'
                <path d="M0,-50 C-20,-60 -55,-58 -65,-50 L-65,55 C-55,47 -20,49 0,58" stroke="#fff" stroke-width="7" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M0,-50 C20,-60 55,-58 65,-50 L65,55 C55,47 20,49 0,58" stroke="#fff" stroke-width="7" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                SVG,
            'api-integrations' => <<<'SVG'
                <path d="M-20,-55 L-60,0 L-20,55" stroke="#fff" stroke-width="9" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M20,-55 L60,0 L20,55" stroke="#fff" stroke-width="9" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                SVG,
            'free-modules' => <<<'SVG'
                <rect x="-60" y="-10" width="120" height="70" stroke="#fff" stroke-width="8" fill="none"/>
                <rect x="-68" y="-35" width="136" height="28" rx="6" stroke="#fff" stroke-width="8" fill="none"/>
                <line x1="0" y1="-35" x2="0" y2="60" stroke="#fff" stroke-width="7"/>
                <ellipse cx="-22" cy="-42" rx="16" ry="12" stroke="#fff" stroke-width="7" fill="none"/>
                <ellipse cx="22" cy="-42" rx="16" ry="12" stroke="#fff" stroke-width="7" fill="none"/>
                SVG,
            default => <<<'SVG'
                <rect x="-55" y="-55" width="110" height="110" rx="16" stroke="#fff" stroke-width="7" fill="none"/>
                SVG,
        };
    }

    private function gearIcon(): string
    {
        $teeth = '';
        foreach (range(0, 7) as $i) {
            $teeth .= '<rect x="-9" y="-72" width="18" height="26" rx="4" fill="#fff" transform="rotate('.($i * 45).')"/>';
        }

        return $teeth.<<<'SVG'
            <circle cx="0" cy="0" r="52" stroke="#fff" stroke-width="8" fill="none"/>
            <circle cx="0" cy="0" r="18" fill="#fff"/>
            SVG;
    }
}
