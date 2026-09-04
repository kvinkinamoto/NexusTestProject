<?php

namespace App\Nexus\Widgets\RecentDemoRecords;

use App\Nexus\Modules\Demo\Models\Demo;
use Nodex\Nexus\Attributes\Widget;
use Nodex\Nexus\Contracts\Widgets\RendersHtml;
use Nodex\Nexus\Contracts\Widgets\WidgetInterface;
use Nodex\Nexus\Dto\Widgets\WidgetContext;
use Nodex\Nexus\Enums\WidgetSurface;

/**
 * First real RendersHtml implementor in the codebase — every other widget
 * built this session (UsersCount, DemoRecordsCount) is ProvidesMetric-only,
 * falling back to the package's built-in metric_card.blade.php. This one has
 * genuine custom Blade output — recentDemoRecords.blade.php, co-located in
 * this same folder rather than resources/views/widgets/, resolved via
 * View::file() below — and declares both Admin and Front surfaces so the
 * same class backs a dashboard card AND a front-placed widget via
 * WidgetInstance::render()/@position() — see App\Nexus\Modules\WidgetPlacement
 * for how an admin actually places it on a page.
 */
#[Widget(
    name: 'recentDemoRecords',
    label: 'Recent Demo Records',
    surfaces: [WidgetSurface::Admin, WidgetSurface::Front],
    icon: 'solar:list-bold',
    group: 'demo',
    module: 'demo',
)]
class RecentDemoRecords implements RendersHtml, WidgetInterface
{
    public function getData(array $config, WidgetContext $context): array
    {
        $limit = max(1, (int) ($config['limit'] ?? 5));

        return [
            'records' => Demo::query()->latest('id')->limit($limit)->get(['id', 'title', 'status']),
        ];
    }

    public static function configFields(): array
    {
        return [];
    }

    /**
     * @return array<string, string> Blade file name (relative to this class's
     *                               own folder) => display name.
     */
    public function availableViews(): array
    {
        return ['recentDemoRecords.blade.php' => 'Default'];
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
