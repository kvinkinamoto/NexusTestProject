<?php

namespace App\Nexus\Plugins\Example;

use Nodex\Nexus\Attributes\Action;
use Nodex\Nexus\Attributes\Filter;
use Nodex\Nexus\Attributes\TargetModule;
use Nodex\Nexus\Services\FieldTypeRegistry;

/**
 * Real, working example plugin — proves out PluginManager's full
 * discover → registerAll/bootAll → apply() cycle, and every hook actually
 * called anywhere in the package today, the same way the Demo module proves
 * out the field-type/section/relation system. See
 * tests/Feature/Nexus/PluginSystemTest.php for the assertions this backs.
 * Disable it without deleting this file via
 * config('nexus.plugins.disabled').
 */
#[TargetModule('Demo')]
class ExamplePlugin
{
    /**
     * #[TargetModule('Demo')] makes this run every time Demo's module
     * config is built (Services/ModuleManager.php's resolveModuleConfig(),
     * via PluginManager::apply()) — mutates the same DTO every field/
     * section/relation on the model itself produces. Appends a marker to
     * the menu label since that's checkable without touching anything
     * Demo-specific.
     */
    public function handle(object $configuration): void
    {
        $configuration->menu->setLabel($configuration->menu->label.' ⚡');
    }

    /**
     * nexus_filter('nexus.validation.rules', $rules, $moduleConfig, $action)
     * — fires for every module's validation, not just Demo's; scoped to Demo
     * here (via $moduleConfig->name) just to keep the example concrete and
     * side-effect-free for every other module. Two independent call sites
     * both reach this: NexusRuleCollector::collect() (modules with no
     * dedicated Request) and NexusServiceProvider::registerValidationRulesFilter()
     * (every Validator built anywhere, including Demo's own hand-written
     * AdminStoreRequest/AdminUpdateRequest — see that method's docblock for
     * why it's the guaranteed call site regardless of how a dedicated
     * Request builds its rules()).
     */
    #[Filter(hook: 'nexus.validation.rules', priority: 10)]
    public function extendValidationRules(array $rules, object $moduleConfig, string $action): array
    {
        if ($moduleConfig->name === 'demo' && isset($rules['title'])) {
            // A field's rules can legally be either a pipe-delimited string
            // (Demo's own hand-written AdminStoreRequest uses this form) or
            // an array — this filter now reaches both kinds of Request, so
            // normalize before appending rather than assuming array shape.
            $titleRules = is_string($rules['title']) ? explode('|', $rules['title']) : $rules['title'];
            $titleRules[] = 'max:100';
            $rules['title'] = $titleRules;
        }

        return $rules;
    }

    /**
     * nexus_action('nexus.field_types.register', $fieldTypeRegistry) — runs
     * once, at boot, with a live FieldTypeRegistry (the same one built-in
     * field types register through). Registering a plain alias is the
     * smallest possible proof this hook actually fires and reaches it.
     */
    #[Action(hook: 'nexus.field_types.register', priority: 10)]
    public function registerFieldTypes(FieldTypeRegistry $registry): void
    {
        $registry->alias('examplePluginAlias', 'string');
    }
}
