<?php

namespace App\Nexus\Modules\ProductRelease\Services\Actions;

use RuntimeException;
use ZipArchive;

/**
 * A manually uploaded release has no GitHub wrapper to strip (unlike a
 * GithubReleaseSyncService download — see RepackageReleaseZipAction), so
 * instead of rewriting it this only checks it already has the shape
 * ArchiveInstallService::resolvePackage() requires: exactly one top-level
 * folder, named with letters/digits only starting with a letter. A
 * malformed upload is rejected with a clear message rather than guessed at.
 */
class ValidateReleaseZipStructureAction
{
    public static function handle(string $zipPath): void
    {
        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Завантажений файл не є коректним zip-архівом.');
        }

        $tops = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);

            if ($entry === false || str_contains($entry, '..')) {
                $zip->close();

                throw new RuntimeException('Архів містить небезпечний шлях і був відхилений.');
            }

            $tops[explode('/', $entry)[0]] = true;
        }

        $zip->close();

        $topNames = array_keys($tops);

        if (count($topNames) !== 1) {
            throw new RuntimeException('Архів має містити рівно одну папку верхнього рівня, названу як модуль (наприклад, SeoBoosterPro/...).');
        }

        if (! preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $topNames[0])) {
            throw new RuntimeException("\"{$topNames[0]}\" — недопустима назва папки модуля (лише латинські літери й цифри, починаючи з літери).");
        }
    }
}
