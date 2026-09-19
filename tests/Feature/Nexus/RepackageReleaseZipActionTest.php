<?php

use App\Nexus\Modules\ProductRelease\Services\Actions\RepackageReleaseZipAction;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

function buildZip(array $entries): string
{
    $path = storage_path('app'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'test-'.Str::random(12).'.zip');
    File::ensureDirectoryExists(dirname($path));

    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    foreach ($entries as $name => $content) {
        $zip->addFromString($name, $content);
    }
    $zip->close();

    return $path;
}

function topLevelEntries(string $path): array
{
    $zip = new ZipArchive;
    $zip->open($path);

    $tops = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $tops[explode('/', $zip->getNameIndex($i))[0]] = true;
    }
    $zip->close();

    return array_keys($tops);
}

afterEach(function () {
    File::deleteDirectory(storage_path('app/tmp'));
});

it('keeps the already-conforming inner folder name when the repo nests its module one level down', function () {
    $path = buildZip(['owner-repo-abc123/SeoBoosterPro/Models/SeoBoosterPro.php' => '<?php']);

    RepackageReleaseZipAction::handle($path, 'SeoBoosterPro');

    expect(topLevelEntries($path))->toBe(['SeoBoosterPro']);

    $zip = new ZipArchive;
    $zip->open($path);
    expect($zip->locateName('SeoBoosterPro/Models/SeoBoosterPro.php'))->not->toBeFalse();
    $zip->close();
});

it('wraps the repo root in the module folder name when the repo root itself is the module', function () {
    $path = buildZip(['owner-repo-abc123/Models/SeoBoosterPro.php' => '<?php']);

    RepackageReleaseZipAction::handle($path, 'SeoBoosterPro');

    expect(topLevelEntries($path))->toBe(['SeoBoosterPro']);

    $zip = new ZipArchive;
    $zip->open($path);
    expect($zip->locateName('SeoBoosterPro/Models/SeoBoosterPro.php'))->not->toBeFalse();
    $zip->close();
});

it('wraps the repo root when its single inner folder is not a valid module name', function () {
    $path = buildZip(['owner-repo-abc123/some-folder/file.php' => '<?php']);

    RepackageReleaseZipAction::handle($path, 'SeoBoosterPro');

    expect(topLevelEntries($path))->toBe(['SeoBoosterPro']);

    $zip = new ZipArchive;
    $zip->open($path);
    expect($zip->locateName('SeoBoosterPro/some-folder/file.php'))->not->toBeFalse();
    $zip->close();
});

it('throws when the zip has no single top-level folder', function () {
    $path = buildZip(['file-a.php' => '<?php', 'file-b.php' => '<?php']);

    expect(fn () => RepackageReleaseZipAction::handle($path, 'SeoBoosterPro'))->toThrow(RuntimeException::class);
});

it('throws when given an invalid module folder name', function () {
    $path = buildZip(['owner-repo-abc123/Models/X.php' => '<?php']);

    expect(fn () => RepackageReleaseZipAction::handle($path, 'seo-booster-pro'))->toThrow(RuntimeException::class);
});

it('throws when the file is not a valid zip', function () {
    $path = storage_path('app'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'not-a-zip-'.Str::random(8).'.zip');
    File::ensureDirectoryExists(dirname($path));
    File::put($path, 'not actually a zip');

    expect(fn () => RepackageReleaseZipAction::handle($path, 'SeoBoosterPro'))->toThrow(RuntimeException::class);
});
