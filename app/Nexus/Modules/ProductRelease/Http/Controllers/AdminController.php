<?php

namespace App\Nexus\Modules\ProductRelease\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\ProductRelease\Models\ProductRelease;
use App\Nexus\Modules\ProductRelease\Services\Actions\ValidateReleaseZipStructureAction;
use App\Nexus\Modules\ProductRelease\Services\GithubReleaseSyncService;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Nodex\Nexus\Models\Module;
use RuntimeException;

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
        } catch (RuntimeException $e) {
            return $redirect->with('alert_message', $e->getMessage())->with('alert_type', 'danger');
        }

        return $redirect
            ->with('alert_message', __('productRelease::translate.release_synced'))
            ->with('alert_type', 'success');
    }

    /**
     * `Route::any` dispatches both the GET (show the upload form) and the
     * POST (process the upload) here — same pattern as Modules'
     * `installFromArchive`. For sellers who'd rather not set up a GitHub
     * token/webhook/repo-naming convention at all: pick a product, type the
     * version and the core-version range it's compatible with, and upload
     * an already-correctly-shaped .zip directly — no GitHub involved.
     * Re-uploading the same version for the same product replaces its file
     * (updateOrCreate, same idempotent-by-tag identity GithubReleaseSyncService
     * already uses for a re-sync).
     */
    public function uploadRelease(FormRequest $request, Module $module): View|RedirectResponse
    {
        if ($request->isMethod('get')) {
            return view('productRelease::upload-release', [
                'module' => $module,
                'products' => ShopProduct::query()->publish()->orderBy('name')->get(),
            ]);
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:shop_products,id'],
            'version' => ['required', 'string', 'max:50'],
            'min_core_version' => ['nullable', 'string', 'max:20'],
            'max_core_version' => ['nullable', 'string', 'max:20'],
            'changelog' => ['nullable', 'string'],
            'archive' => ['required', 'file', 'mimes:zip', 'max:51200'],
        ]);

        $redirectBack = redirect()
            ->route('nexus.module.action', ['module' => $module->name, 'action' => 'uploadRelease'])
            ->withInput();

        try {
            ValidateReleaseZipStructureAction::handle($validated['archive']->getRealPath());
        } catch (RuntimeException $e) {
            return $redirectBack->with('alert_message', $e->getMessage())->with('alert_type', 'danger');
        }

        $product = ShopProduct::query()->publish()->findOrFail($validated['product_id']);
        $githubTag = 'manual-'.Str::slug($validated['version']);

        $destinationDir = storage_path('app'.DIRECTORY_SEPARATOR.'releases'.DIRECTORY_SEPARATOR.$product->slug);
        File::ensureDirectoryExists($destinationDir);
        $destinationPath = $destinationDir.DIRECTORY_SEPARATOR.$githubTag.'.zip';
        $validated['archive']->move($destinationDir, $githubTag.'.zip');

        ProductRelease::query()->updateOrCreate(
            ['product_id' => $product->id, 'github_tag' => $githubTag],
            [
                'version' => $validated['version'],
                'min_core_version' => $validated['min_core_version'] ?? null,
                'max_core_version' => $validated['max_core_version'] ?? null,
                'changelog' => $validated['changelog'] ?? null,
                'published_at' => now(),
                'zip_disk_path' => $destinationPath,
                'zip_size' => File::size($destinationPath),
                'synced_at' => now(),
            ],
        );

        return redirect()
            ->route('nexus.module.action', ['module' => $module->name, 'action' => 'index'])
            ->with('alert_message', __('productRelease::translate.release_uploaded'))
            ->with('alert_type', 'success');
    }
}
