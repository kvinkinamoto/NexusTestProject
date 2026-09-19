<?php

namespace App\Nexus\Modules\ProductRelease\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\ProductRelease\Models\ProductRelease;
use App\Nexus\Modules\ProductRelease\Services\GithubReleaseSyncService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Nodex\Nexus\Models\Module;

/**
 * ModuleManager::getModuleAdminController() auto-discovers this and
 * NexusController::action() dispatches the model's #[TableAction(name:
 * 'syncFromGithub', ...)] row button here by matching method name — same
 * convention documented on Backup's AdminController.
 */
class AdminController extends Controller
{
    public function syncFromGithub(FormRequest $request, Module $module, string $id, GithubReleaseSyncService $service): RedirectResponse
    {
        $release = ProductRelease::query()->with('product')->findOrFail($id);

        $redirect = redirect()->route('nexus.module.action', ['module' => $module->name, 'action' => 'index']);

        try {
            $service->sync($release->product, $release->github_tag);
        } catch (\RuntimeException $e) {
            return $redirect->with('alert_message', $e->getMessage())->with('alert_type', 'danger');
        }

        return $redirect
            ->with('alert_message', __('productRelease::translate.release_synced'))
            ->with('alert_type', 'success');
    }
}
