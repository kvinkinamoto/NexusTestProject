<?php

namespace App\Nexus\Modules\Backup\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Backup\Jobs\RunBackupJob;
use App\Nexus\Modules\Backup\Models\Backup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Nodex\Nexus\Models\Module;

/**
 * ModuleManager::getModuleAdminController() auto-discovers this and
 * NexusController::action() dispatches the model's #[TableAction(name:
 * 'run', ...)] row button here by matching method name — same convention
 * documented on App\Nexus\Modules\Settings's AdminController.
 */
class AdminController extends Controller
{
    public function run(FormRequest $request, Module $module, string $id): RedirectResponse
    {
        $backup = Backup::query()->findOrFail($id);

        RunBackupJob::dispatch($backup->id);

        return redirect()
            ->route('nexus.module.action', ['module' => $module->name, 'action' => 'index'])
            ->with('alert_message', __('backup::translate.backup_queued'))
            ->with('alert_type', 'success');
    }
}
