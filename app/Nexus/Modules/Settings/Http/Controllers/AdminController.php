<?php

namespace App\Nexus\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Http\FormRequest;
use Nodex\Nexus\Models\Module;

/**
 * ModuleManager::getModuleAdminController() auto-discovers this by
 * convention (App\Nexus\Modules\{Name}\Http\Controllers\AdminController) and
 * NexusController::action() calls whichever of its methods matches the
 * requested action instead of its own — see App\Nexus\Modules\User's
 * (currently empty) AdminController for the same convention already wired
 * up elsewhere. The Settings module has no bound Eloquent model (see
 * ModuleConfiguration), so the framework's default index/edit/create/delete
 * — all written assuming $moduleConfig->model exists — never apply; edit()
 * is the only action worth having, and index() just redirects there since
 * there is no list to show.
 */
class AdminController extends Controller
{
    public function index(FormRequest $request, Module $module, ?string $id = null)
    {
        return redirect()->route('nexus.module.action', ['module' => $module->name, 'action' => 'edit']);
    }

    public function edit(FormRequest $request, Module $module, ?string $id = null)
    {
        return view('nexus::'.config('nexus.template').'.pages.moduleSettingsLivewire', [
            'module' => $module,
        ]);
    }
}
