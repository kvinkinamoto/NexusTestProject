<?php

namespace App\Nexus\Modules\WidgetPlacement\Listeners;

use Nodex\Nexus\Contracts\Widgets\RendersHtml;
use Nodex\Nexus\Events\AdminFormBuilding;
use Nodex\Nexus\Services\Widgets\WidgetRegistry;

/**
 * Populates the 'widget_key' select with every registered widget that can
 * actually produce HTML here — WidgetInstance::render() returns '' for
 * anything that isn't RendersHtml (see that class), so listing a
 * ProvidesMetric-only widget (e.g. UsersCount) would silently place a
 * permanently-blank card. Same AdminFormBuilding + $field->customData
 * pattern as Demo's PopulateDemoSelectOptions.
 */
class PopulateWidgetKeyOptions
{
    public function handle(AdminFormBuilding $event): void
    {
        if ($event->moduleName !== 'widgetPlacement') {
            return;
        }

        foreach ($event->config->form->fields as $field) {
            if ($field->name !== 'widget_key') {
                continue;
            }

            $field->customData = collect(app(WidgetRegistry::class)->all())
                ->filter(fn (array $entry) => is_subclass_of($entry['class'], RendersHtml::class))
                ->map(fn (array $entry, string $key) => ['value' => $key, 'name' => $entry['meta']->label])
                ->values()
                ->all();

            break;
        }
    }
}
