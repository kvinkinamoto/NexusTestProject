<?php

namespace App\Nexus\Modules\Restore\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Restore\Jobs\RunRestoreJob;
use App\Nexus\Modules\Restore\Models\Restore;
use App\Nexus\Modules\Restore\Services\RestoreService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Nodex\Nexus\Models\Module;

/**
 * ModuleManager::getModuleAdminController() auto-discovers this and
 * NexusController::action() dispatches matching #[TableAction] names here
 * instead of the framework default — same convention as Backup's own
 * AdminController and App\Nexus\Modules\Settings's.
 */
class AdminController extends Controller
{
    /**
     * Overrides the default index to auto-scan before rendering, matching
     * the old project's behavior — the list is always kept in sync with
     * whatever is actually on disk, without a separate manual step.
     * A scan failure (disk unreachable, etc.) is logged rather than
     * breaking the page — the listing still renders with whatever was
     * scanned last time.
     */
    public function index(FormRequest $request, Module $module, ?string $id = null)
    {
        try {
            app(RestoreService::class)->scanBackups();
        } catch (\Throwable $e) {
            Log::warning('Restore auto-scan failed', ['message' => $e->getMessage()]);
        }

        return view('nexus::'.config('nexus.template').'.pages.indexLivewire', [
            'module' => $module,
        ]);
    }

    public function scan(FormRequest $request, Module $module): RedirectResponse
    {
        app(RestoreService::class)->scanBackups();

        return redirect()
            ->route('nexus.module.action', ['module' => $module->name, 'action' => 'index'])
            ->with('alert_message', __('restore::translate.scan_complete'))
            ->with('alert_type', 'success');
    }

    public function runRestore(FormRequest $request, Module $module, string $id): RedirectResponse
    {
        $restore = Restore::query()->findOrFail($id);

        RunRestoreJob::dispatch($restore->id);

        return redirect()
            ->route('nexus.module.action', ['module' => $module->name, 'action' => 'index'])
            ->with('alert_message', __('restore::translate.restore_queued'))
            ->with('alert_type', 'success');
    }
}
