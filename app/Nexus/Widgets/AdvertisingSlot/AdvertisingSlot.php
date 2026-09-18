<?php

namespace App\Nexus\Widgets\AdvertisingSlot;

use App\Nexus\Modules\Advertising\Services\AdPlacementService;
use Nodex\Nexus\Attributes\Widget;
use Nodex\Nexus\Contracts\Widgets\RendersHtml;
use Nodex\Nexus\Contracts\Widgets\WidgetInterface;
use Nodex\Nexus\Dto\ModuleDtos\FieldConfigDto;
use Nodex\Nexus\Dto\Widgets\WidgetContext;
use Nodex\Nexus\Enums\WidgetSurface;

/**
 * Front-only widget that renders one Advertising row for a given `slot`
 * key (see Advertising\Models\Advertising's docblock) — an admin places one
 * or more instances of this widget via WidgetPlacement, each configured
 * with {"slot": "..."}, and assigns each instance to whichever
 *
 * @position()/target_template it should appear at. Declares no cacheTtl so
 * AdPlacementService::pick() (and the impression it records as a side
 * effect) runs on every real page view, not once per cache window.
 */
#[Widget(
    name: 'advertisingSlot',
    label: 'Advertising Slot',
    surfaces: [WidgetSurface::Front],
    icon: 'solar:tag-price-bold',
    group: 'advertising',
    module: 'advertising',
)]
class AdvertisingSlot implements RendersHtml, WidgetInterface
{
    public function getData(array $config, WidgetContext $context): array
    {
        $slot = trim((string) ($config['slot'] ?? ''));

        return [
            'ad' => $slot !== '' ? app(AdPlacementService::class)->pick($slot) : null,
        ];
    }

    /**
     * @return array<int, FieldConfigDto>
     */
    public static function configFields(): array
    {
        return [
            new FieldConfigDto(name: 'slot', type: 'string', label: 'Ad slot key', isRequired: true),
        ];
    }

    public function availableViews(): array
    {
        return ['advertisingSlot.blade.php' => 'Default'];
    }

    public function viewFor(?string $name): ?string
    {
        $views = $this->availableViews();

        return $name && isset($views[$name]) ? $name : array_key_first($views);
    }

    public function render(array $config, WidgetContext $context): string
    {
        $view = $this->viewFor($context->params['view'] ?? null);
        $path = $view ? __DIR__.DIRECTORY_SEPARATOR.$view : null;

        if (! $path || ! is_file($path)) {
            return '';
        }

        return view()->file($path, $this->getData($config, $context))->render();
    }
}
