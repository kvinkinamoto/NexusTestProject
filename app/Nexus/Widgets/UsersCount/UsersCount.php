<?php

namespace App\Nexus\Widgets\UsersCount;

use App\Models\User;
use Nodex\Nexus\Attributes\Widget;
use Nodex\Nexus\Contracts\Widgets\ProvidesMetric;
use Nodex\Nexus\Contracts\Widgets\WidgetInterface;
use Nodex\Nexus\Dto\Widgets\MetricDto;
use Nodex\Nexus\Dto\Widgets\WidgetContext;
use Nodex\Nexus\Enums\WidgetSurface;

/**
 * First real widget on top of the #[Widget]/WidgetRegistry system — proves
 * out the declarative widget pipeline the same way the Demo module proved
 * out the field-type system. config('nexus.dashboard.default') previously
 * pointed at a 'helloWorld' key with no widget behind it, so a fresh admin
 * dashboard rendered empty; this fills that in with something real.
 */
#[Widget(
    name: 'usersCount',
    label: 'Total Users',
    surfaces: [WidgetSurface::Admin],
    icon: 'bx bx-group',
    group: 'stats',
    defaultSize: '1x1',
)]
class UsersCount implements ProvidesMetric, WidgetInterface
{
    public function getData(array $config, WidgetContext $context): array
    {
        return ['count' => User::count()];
    }

    public static function configFields(): array
    {
        return [];
    }

    public function toMetric(array $config, WidgetContext $context): MetricDto
    {
        return new MetricDto(
            label: 'Total Users',
            value: User::count(),
            icon: 'bx bx-group',
        );
    }
}
