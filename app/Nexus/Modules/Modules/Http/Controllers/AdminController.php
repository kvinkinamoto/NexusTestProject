<?php

namespace App\Nexus\Modules\Modules\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Modules\Services\ArchiveInstallService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Nodex\Nexus\Models\Module;

/**
 * ModuleManager::getModuleAdminController() auto-discovers this — same
 * convention as Restore's/Translations's own AdminController. Only
 * `installFromArchive` is declared here; every other action (index, edit,
 * boolToggle, ...) still falls back to the framework's generic handlers.
 */
class AdminController extends Controller
{
    /**
     * `Route::any` dispatches both the GET (show the upload form, reached
     * from the #[TableAction] button) and the POST (process the upload) to
     * this same method — the same pattern Restore's `scan` action uses for
     * its own single-name action.
     */
    public function installFromArchive(FormRequest $request, Module $module): View|RedirectResponse
    {
        if ($request->isMethod('get')) {
            return view('modules::install-from-archive', ['module' => $module]);
        }

        $validated = $request->validate([
            'type' => ['required', 'in:module,plugin,widget'],
            'archive' => ['required', 'file', 'mimes:zip', 'max:51200'],
        ]);

        try {
            $name = app(ArchiveInstallService::class)->install($validated['type'], $validated['archive']);
        } catch (\Throwable $e) {
            return redirect()
                ->route('nexus.module.action', ['module' => $module->name, 'action' => 'installFromArchive'])
                ->withInput()
                ->with('alert_message', $e->getMessage())
                ->with('alert_type', 'error');
        }

        return redirect()
            ->route('nexus.module.action', ['module' => $module->name, 'action' => 'index'])
            ->with('alert_message', __('modules::translate.install_from_archive.success', ['name' => $name]))
            ->with('alert_type', 'success');
    }
}
