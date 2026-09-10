<?php

namespace App\Nexus\Modules\Translations\Http\Controllers;

use App\Nexus\Modules\Language\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Nodex\Nexus\Models\Module;
use Nodex\Nexus\Services\DirectTranslationService;

/**
 * Auto-discovered by ModuleManager::getModuleAdminController() (same
 * convention as every other module's AdminController override this port
 * uses). Every action here is fully custom — this module has no bound
 * model, so none of the framework's own index/edit/create/delete handlers
 * would even apply.
 *
 * DirectTranslationService lives in the core Nodex\Nexus package, not this
 * module — it was already there, singleton-bound in NexusServiceProvider,
 * and already reads App\Nexus\Modules\Language\Models\Language directly
 * (down to the exact query it expects — see that model's isPublished()
 * scope) — dormant infrastructure nothing had wired a UI onto yet, the same
 * shape of discovery as the #[Setting] registry before it (see
 * project_nexus_settings_registry memory). This controller is that UI.
 */
class AdminController
{
    public function __construct(
        protected DirectTranslationService $translationService,
    ) {}

    /** Page 1: every translation group (file), with a completion percentage. */
    public function index(FormRequest $request, Module $module): View
    {
        $groups = $this->translationService->getGroups();

        return view('translations::index', compact('groups', 'module'));
    }

    /** Page 2: every key in one group, across the selected locales. */
    public function phrases(FormRequest $request, Module $module, string $id): View
    {
        $keys = $this->translationService->getKeys($id);
        $allLocales = Language::query()->publish()->get();

        $selectedCodes = $request->input('locales', [config('app.locale', 'en')]);
        $locales = $allLocales->whereIn('code', $selectedCodes);

        if ($locales->isEmpty()) {
            $locales = $allLocales->where('code', config('app.locale', 'en'));
        }

        return view('translations::phrases', [
            'keys' => $keys,
            'id' => $id,
            'locales' => $locales,
            'allLocales' => $allLocales,
            'module' => $module,
            'selectedCodes' => $selectedCodes,
        ]);
    }

    /** Page 3: edit one key for every published locale at once. */
    public function edit(FormRequest $request, Module $module, string $id): View
    {
        $key = $request->query('key', '');
        $allKeys = $this->translationService->getKeys($id);
        $translations = $allKeys[$key] ?? [];
        $locales = Language::query()->publish()->get();

        return view('translations::edit', compact('id', 'key', 'translations', 'locales', 'module'));
    }

    /** Writes the key to disk for every locale submitted. */
    public function update(FormRequest $request, Module $module, string $id): RedirectResponse
    {
        $key = (string) $request->input('key');
        $translations = (array) $request->input('translations', []);

        $this->translationService->saveKey($id, $key, $translations);

        return redirect()
            ->route('nexus.module.action', ['module' => $module->name, 'action' => 'edit', 'id' => $id, 'key' => $key])
            ->with('alert_message', __('translations::translate.saved_success'))
            ->with('alert_type', 'success');
    }
}
