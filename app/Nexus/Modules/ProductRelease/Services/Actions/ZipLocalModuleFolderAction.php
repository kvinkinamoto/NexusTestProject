<?php

namespace App\Nexus\Modules\ProductRelease\Services\Actions;

use FilesystemIterator;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ZipArchive;

/**
 * Zips a real module folder already on disk (nexus-modules-archive, or this
 * project's own app/Nexus/Modules/**) straight into the single-top-level-
 * folder shape ArchiveInstallService::resolvePackage() requires. The
 * counterpart to RepackageReleaseZipAction for a source that's a plain
 * folder rather than a downloaded GitHub zipball needing its wrapper
 * stripped first — no extraction step, just add every file under
 * $moduleFolderName/.
 */
class ZipLocalModuleFolderAction
{
    public static function handle(string $sourceDir, string $moduleFolderName, string $destinationZip): void
    {
        if (! preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $moduleFolderName)) {
            throw new RuntimeException("\"{$moduleFolderName}\" isn't a valid module folder name (letters and digits only, starting with a letter).");
        }

        if (! File::isDirectory($sourceDir)) {
            throw new RuntimeException("Module source folder not found: {$sourceDir}");
        }

        File::ensureDirectoryExists(dirname($destinationZip));
        File::delete($destinationZip);

        $zip = new ZipArchive;

        if ($zip->open($destinationZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Could not create zip at {$destinationZip}.");
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY,
        );

        foreach ($files as $file) {
            $relative = substr($file->getPathname(), strlen($sourceDir) + 1);
            $localName = $moduleFolderName.'/'.str_replace('\\', '/', $relative);
            $zip->addFile($file->getPathname(), $localName);
        }

        $zip->close();
    }
}
