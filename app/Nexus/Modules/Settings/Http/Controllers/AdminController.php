<?php

namespace App\Nexus\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Http\FormRequest;
use Nodex\Nexus\Models\Module;
use Nodex\Nexus\Services\ModuleManager;

/**
 * ModuleManager::getModuleAdminController() auto-discovers this by
 * convention (App\Nexus\Modules\{Name}\Http\Controllers\AdminController) and
 * NexusController::action() calls whichever of its methods matches the
 * requested action instead of its own — see App\Nexus\Modules\User's
 * (currently empty) AdminController for the same convention already wired
 * up elsewhere. Settings has no bound Eloquent model (see
 * ModuleConfiguration), so the framework's default index/edit/create/delete
 * — all written assuming $moduleConfig->model exists — never apply here.
 *
 * index() doubles as the "All Settings" overview the topbar link opens: one
 * place listing every installed module that declares #[Setting(...)] — this
 * one included, since it resolves through the exact same ModuleManager::
 * getModuleConfig() call as any other module — each linking to its own
 * NexusController::settings() screen. Deliberately not one giant form
 * merging every module's settings together: different modules' settings
 * carry different permissions and meanings, and a flat merge would only get
 * messier as more modules add their own (see project-nexus-settings-registry
 * memory's "how to apply" for the option-A-vs-B tradeoff this resolved).
 */
class AdminController extends Controller
{
    public function index(FormRequest $request, Module $module, ?string $id = null)
    {
        $modulesWithSettings = Module::query()
            ->where('is_enabled', true)
            ->get()
            ->map(fn (Module $row) => (object) [
                'name' => $row->name,
                'config' => ModuleManager::getModuleConfig($row->name),
            ])
            ->filter(fn ($entry) => ! empty($entry->config->settings))
            ->sortBy(fn ($entry) => $entry->config->menu->label ?? $entry->name)
            ->values();

        return view('settings::overview', [
            'modulesWithSettings' => $modulesWithSettings,
        ]);
    }
}
