<?php

use App\Nexus\Modules\ProductRelease\Services\Actions\ValidateReleaseZipStructureAction;
use App\Nexus\Modules\ProductRelease\Services\Actions\ZipLocalModuleFolderAction;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

function buildSourceFolder(array $files): string
{
    $dir = storage_path('app'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'test-src-'.Str::random(12));

    foreach ($files as $relativePath => $content) {
        $full = $dir.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        File::ensureDirectoryExists(dirname($full));
        File::put($full, $content);
    }

    return $dir;
}

afterEach(function () {
    File::deleteDirectory(storage_path('app/tmp'));
});

it('zips a local folder under a single top-level folder named after the module', function () {
    $source = buildSourceFolder([
        'Models/Currency.php' => '<?php',
        'database/migrations/create_currencies_table.php' => '<?php',
    ]);
    $destination = storage_path('app'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'out-'.Str::random(8).'.zip');

    ZipLocalModuleFolderAction::handle($source, 'Currency', $destination);

    $zip = new ZipArchive;
    $zip->open($destination);
    expect($zip->locateName('Currency/Models/Currency.php'))->not->toBeFalse();
    expect($zip->locateName('Currency/database/migrations/create_currencies_table.php'))->not->toBeFalse();
    $zip->close();

    ValidateReleaseZipStructureAction::handle($destination);
})->throwsNoExceptions();

it('rejects a module folder name that is not letters/digits starting with a letter', function () {
    $source = buildSourceFolder(['Models/X.php' => '<?php']);
    $destination = storage_path('app'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'out-'.Str::random(8).'.zip');

    ZipLocalModuleFolderAction::handle($source, '../Evil', $destination);
})->throws(RuntimeException::class);

it('fails when the source folder does not exist', function () {
    $destination = storage_path('app'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'out-'.Str::random(8).'.zip');

    ZipLocalModuleFolderAction::handle(storage_path('app/tmp/does-not-exist-'.Str::random(8)), 'Missing', $destination);
})->throws(RuntimeException::class);
