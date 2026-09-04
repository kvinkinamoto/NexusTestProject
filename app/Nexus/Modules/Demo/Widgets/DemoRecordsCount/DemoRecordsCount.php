<?php

namespace App\Nexus\Modules\Demo\Widgets\DemoRecordsCount;

use App\Nexus\Modules\Demo\Enums\DemoStatus;
use App\Nexus\Modules\Demo\Models\Demo;
use Nodex\Nexus\Attributes\Widget;
use Nodex\Nexus\Contracts\Widgets\ProvidesMetric;
use Nodex\Nexus\Contracts\Widgets\WidgetInterface;
use Nodex\Nexus\Dto\Widgets\MetricDto;
use Nodex\Nexus\Dto\Widgets\WidgetContext;
use Nodex\Nexus\Enums\WidgetSurface;

/**
 * Demonstrates a module declaring its own dashboard widget under its own
 * Widgets/ folder (ModuleManifestCache::discoverWidgets(), scoped to this
 * module's namespace) rather than the app-global app/Nexus/Widgets/ — the
 * other half of the "dashboard widgets" system Demo hadn't shown yet,
 * alongside its existing coverage of the field-type system.
 */
#[Widget(
    name: 'demoRecordsCount',
    label: 'Published Demo Records',
    surfaces: [WidgetSurface::Admin],
    icon: 'bx bx-widget',
    group: 'stats',
    module: 'demo',
    defaultSize: '1x1',
)]
class DemoRecordsCount implements ProvidesMetric, WidgetInterface
{
    public function getData(array $config, WidgetContext $context): array
    {
        return [
            'published' => Demo::where('status', DemoStatus::PUBLISHED->value)->count(),
            'total' => Demo::count(),
        ];
    }

    public static function configFields(): array
    {
        return [];
    }

    public function toMetric(array $config, WidgetContext $context): MetricDto
    {
        $published = Demo::where('status', DemoStatus::PUBLISHED->value)->count();
        $total = Demo::count();

        return new MetricDto(
            label: 'Published Demo Records',
            value: $published,
            unit: " / {$total}",
            icon: 'bx bx-widget',
        );
    }
}
