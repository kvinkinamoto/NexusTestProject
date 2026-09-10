<?php

namespace App\Nexus\Modules\Language\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Language\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Nodex\Nexus\Models\Module;

/**
 * ModuleManager::getModuleAdminController() auto-discovers this and
 * NexusController::action() dispatches the model's #[TableAction(name:
 * 'switchAdminLocale', ...)] row button here by matching method name — same
 * convention as Backup/Restore's own AdminControllers (Phase 12).
 */
class AdminController extends Controller
{
    public function switchAdminLocale(FormRequest $request, Module $module, string $id): RedirectResponse
    {
        $language = Language::query()->publish()->findOrFail($id);

        session(['admin_locale' => $language->code]);

        return redirect()
            ->route('nexus.module.action', ['module' => $module->name, 'action' => 'index'])
            ->with('alert_message', __('language::translate.admin_locale_switched'))
            ->with('alert_type', 'success');
    }
}
