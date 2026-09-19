<?php

namespace App\Nexus\Modules\ProductRelease\Services\Actions;

use FilesystemIterator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ZipArchive;

/**
 * A raw GitHub zipball (what GithubReleaseSyncService downloads) always
 * wraps its contents in one extra `owner-repo-<sha>/` folder — GitHub adds
 * this itself, and its hyphens/digits-after-letters shape never matches
 * ArchiveInstallService::resolvePackage()'s `^[A-Za-z][A-Za-z0-9]*$` rule
 * for "the archive's one top-level folder, named after the module." A
 * license holder downloading straight off GitHub could never install what
 * they bought through the admin's own "install from archive" flow.
 *
 * Rewrites the zip in place so its single top-level folder is
 * $moduleFolderName instead: if the repo already nests its real content one
 * level down in a single folder (e.g. seller committed a `SeoBoosterPro/`
 * directory at repo root), that inner folder's *contents* are used as-is;
 * otherwise the whole repo root (already stripped of GitHub's wrapper) is
 * taken as the module's contents. Either way the result is exactly one
 * top-level folder named $moduleFolderName, which is what
 * ArchiveInstallService requires.
 *
 * Telling those two shapes apart isn't just "is there a single subfolder" —
 * a repo root that *is* the module can itself have only one top-level
 * subfolder (e.g. a module with nothing but Models/ so far), which would
 * look identical to a nested `SeoBoosterPro/` wrapper by shape alone. What
 * actually distinguishes them is whether that single subfolder is one of
 * Nexus's own recognized module-internal folder names (see MODULE_FOLDERS)
 * — if so it's part of the module's own structure, not a wrapper around it.
 */
class RepackageReleaseZipAction
{
    private const MODULE_FOLDERS = [
        'models', 'http', 'requests', 'resources', 'database', 'services',
        'enums', 'listeners', 'commands', 'jobs', 'events', 'filters',
        'relations', 'fields', 'traits', 'repositories', 'observers', 'actions',
    ];

    public static function handle(string $zipPath, string $moduleFolderName): void
    {
        if (! preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $moduleFolderName)) {
            throw new RuntimeException("\"{$moduleFolderName}\" isn't a valid module folder name (letters and digits only, starting with a letter) — check the product's github_repo.");
        }

        $extractTo = storage_path('app'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'release-repack-'.Str::random(12));
        self::extract($zipPath, $extractTo);

        try {
            $githubRoot = self::singleSubdirectory($extractTo);
            if (! $githubRoot) {
                throw new RuntimeException("Release zip at {$zipPath} has no single top-level folder — not a GitHub zipball.");
            }

            $innerRoot = self::singleSubdirectory($githubRoot);
            $sourceDir = ($innerRoot
                && preg_match('/^[A-Za-z][A-Za-z0-9]*$/', basename($innerRoot))
                && ! in_array(mb_strtolower(basename($innerRoot)), self::MODULE_FOLDERS, true)
            ) ? $innerRoot : $githubRoot;

            $rebuiltRoot = storage_path('app'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'release-rebuilt-'.Str::random(12));
            $moduleDir = $rebuiltRoot.DIRECTORY_SEPARATOR.$moduleFolderName;
            File::ensureDirectoryExists($moduleDir);
            File::copyDirectory($sourceDir, $moduleDir);

            try {
                File::delete($zipPath);
                self::zipDirectory($rebuiltRoot, $zipPath);
            } finally {
                File::deleteDirectory($rebuiltRoot);
            }
        } finally {
            File::deleteDirectory($extractTo);
        }
    }

    private static function extract(string $zipPath, string $extractTo): void
    {
        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException("Could not open release zip at {$zipPath} for repackaging.");
        }

        File::ensureDirectoryExists($extractTo);
        $zip->extractTo($extractTo);
        $zip->close();
    }

    private static function singleSubdirectory(string $dir): ?string
    {
        $entries = array_values(array_diff(scandir($dir), ['.', '..', '__MACOSX']));

        if (count($entries) !== 1 || ! File::isDirectory($dir.DIRECTORY_SEPARATOR.$entries[0])) {
            return null;
        }

        return $dir.DIRECTORY_SEPARATOR.$entries[0];
    }

    private static function zipDirectory(string $sourceDir, string $destinationZip): void
    {
        $zip = new ZipArchive;
        $zip->open($destinationZip, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY,
        );

        foreach ($files as $file) {
            $localName = substr($file->getPathname(), strlen($sourceDir) + 1);
            $zip->addFile($file->getPathname(), str_replace('\\', '/', $localName));
        }

        $zip->close();
    }
}
