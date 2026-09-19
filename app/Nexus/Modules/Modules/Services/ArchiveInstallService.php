<?php

namespace App\Nexus\Modules\Modules\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nodex\Nexus\Models\Module;
use RuntimeException;
use ZipArchive;

/**
 * Extracts an uploaded .zip onto disk under app/Nexus/{Modules,Plugins,Widgets}
 * and, for a module, runs the existing `nexus:module:install` pipeline
 * (migrations/permissions/runtime registration — see InstallModuleCommand)
 * against it. Plugins and widgets have no install command of their own
 * (PluginManager self-registers a plugin's `nexus_plugins` row lazily on next
 * boot; WidgetRegistry is a live directory scan) — for those, copying the
 * files onto disk is the entire "install" step, matching how a developer
 * manually dropping a folder there already behaves.
 */
class ArchiveInstallService
{
    private const TYPE_DIRS = [
        'module' => 'Modules',
        'plugin' => 'Plugins',
        'widget' => 'Widgets',
    ];

    public function install(string $type, UploadedFile $archive): string
    {
        if (! isset(self::TYPE_DIRS[$type])) {
            throw new RuntimeException("Unknown archive type [{$type}].");
        }

        $extractedRoot = $this->extract($archive);

        try {
            [$name, $sourceDir] = $this->resolvePackage($extractedRoot);

            $destination = app_path('Nexus'.DIRECTORY_SEPARATOR.self::TYPE_DIRS[$type].DIRECTORY_SEPARATOR.$name);

            if (File::isDirectory($destination)) {
                throw new RuntimeException("\"{$name}\" is already installed — remove the existing folder before re-uploading its archive.");
            }

            File::ensureDirectoryExists(dirname($destination));
            File::copyDirectory($sourceDir, $destination);

            if ($type === 'module') {
                $this->runModuleInstall($name, $destination);
            }

            return $name;
        } finally {
            File::deleteDirectory($extractedRoot);
        }
    }

    private function runModuleInstall(string $name, string $destination): void
    {
        Artisan::call('nexus:module:install', ['name' => $name]);

        if (! Module::findByName($name)) {
            File::deleteDirectory($destination);

            throw new RuntimeException("\"{$name}\" was copied but could not be installed as a module — check that it declares a valid #[Module] attribute.");
        }
    }

    /**
     * @return array{0: string, 1: string} the package name and its extracted source directory
     */
    private function resolvePackage(string $extractedRoot): array
    {
        $entries = array_values(array_diff(scandir($extractedRoot), ['.', '..', '__MACOSX']));

        if (count($entries) !== 1 || ! File::isDirectory($extractedRoot.DIRECTORY_SEPARATOR.$entries[0])) {
            throw new RuntimeException('The archive must contain exactly one top-level folder, named after the module, plugin, or widget.');
        }

        $name = $entries[0];

        if (! preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $name)) {
            throw new RuntimeException("\"{$name}\" isn't a valid module/plugin/widget name (letters and digits only, starting with a letter).");
        }

        return [$name, $extractedRoot.DIRECTORY_SEPARATOR.$name];
    }

    private function extract(UploadedFile $archive): string
    {
        $zip = new ZipArchive;

        if ($zip->open($archive->getRealPath()) !== true) {
            throw new RuntimeException('The uploaded file is not a valid zip archive.');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);

            if ($entryName === false || str_contains($entryName, '..')) {
                $zip->close();

                throw new RuntimeException('The archive contains an unsafe path and was rejected.');
            }
        }

        $extractTo = storage_path('app'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'archive-install-'.Str::random(12));
        File::ensureDirectoryExists($extractTo);

        $zip->extractTo($extractTo);
        $zip->close();

        return $extractTo;
    }
}
