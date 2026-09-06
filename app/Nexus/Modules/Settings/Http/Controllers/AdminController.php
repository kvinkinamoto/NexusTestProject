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
 * up elsewhere. Settings has no bound Eloquent model (see
 * ModuleConfiguration), so the framework's default index/edit/create/delete
 * — all written assuming $moduleConfig->model exists — never apply here.
 * NexusController::settings() (generic, works for any module) already
 * covers the real screen; this override only redirects the unused index
 * action there, since there's no list to show.
 */
class AdminController extends Controller
{
    public function index(FormRequest $request, Module $module, ?string $id = null)
    {
        return redirect()->route('nexus.module.action', ['module' => $module->name, 'action' => 'settings']);
    }
}
