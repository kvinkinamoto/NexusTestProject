<?php

namespace App\Nexus\Modules\Demo\Listeners;

use Nodex\Nexus\Events\AdminFormBuilding;

/**
 * First real, working example of AdminFormBuilding populating a 'select'
 * field's options — select.blade.php's own docblock references a
 * "Widget's PopulateWidgetSelectOptions" example that doesn't actually
 * exist anywhere in the codebase. Auto-discovered by
 * ModuleManifestCache::discoverListeners() from this Listeners/ folder —
 * no manual registration needed.
 */
class PopulateDemoSelectOptions
{
    public function handle(AdminFormBuilding $event): void
    {
        if ($event->moduleName !== 'demo') {
            return;
        }

        $options = [
            ['value' => 'low', 'name' => 'Low'],
            ['value' => 'medium', 'name' => 'Medium'],
            ['value' => 'high', 'name' => 'High'],
        ];

        foreach ($event->config->form->fields as $field) {
            // Same options, two renderings — 'priority' (select dropdown)
            // and 'priority_radio' (#[Field(type: 'radio')]) both read
            // customData the same way, see select.blade.php/radio.blade.php.
            if ($field->name === 'priority' || $field->name === 'priority_radio') {
                $field->customData = $options;
            }
        }
    }
}
