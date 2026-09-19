<?php

use App\Nexus\Modules\Modules\Services\ArchiveInstallService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nodex\Nexus\Models\Module as NexusModule;

/**
 * Builds a real .zip (ArchiveInstallService opens it with ZipArchive, so a
 * fake-sized UploadedFile::fake() stub won't do) whose only top-level entry
 * is $folderName, containing $files (relative path => contents).
 */
function makeArchiveUpload(string $folderName, array $files): UploadedFile
{
    $stagingDir = storage_path('app/tmp/archive-test-src-'.Str::random(8));
    foreach ($files as $relativePath => $contents) {
        $path = $stagingDir.DIRECTORY_SEPARATOR.$folderName.DIRECTORY_SEPARATOR.$relativePath;
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $contents);
    }

    $zipPath = storage_path('app/tmp/archive-test-'.Str::random(8).'.zip');
    File::ensureDirectoryExists(dirname($zipPath));

    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);

    $addDir = function (string $dir, string $localBase) use (&$addDir, $zip): void {
        foreach (scandir($dir) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $fullPath = $dir.DIRECTORY_SEPARATOR.$entry;
            $localPath = $localBase === '' ? $entry : $localBase.'/'.$entry;

            if (is_dir($fullPath)) {
                $zip->addEmptyDir($localPath);
                $addDir($fullPath, $localPath);
            } else {
                $zip->addFile($fullPath, $localPath);
            }
        }
    };
    $addDir($stagingDir, '');
    $zip->close();

    File::deleteDirectory($stagingDir);

    return new UploadedFile($zipPath, $folderName.'.zip', 'application/zip', null, true);
}

afterEach(function () {
    File::deleteDirectory(app_path('Nexus/Modules/ZzTestModule'));
    File::deleteDirectory(app_path('Nexus/Plugins/ZzTestPlugin'));
    File::deleteDirectory(app_path('Nexus/Widgets/ZzTestWidget'));
});

it('installs a plugin by copying its files onto disk', function () {
    $upload = makeArchiveUpload('ZzTestPlugin', [
        'ZzTestPlugin.php' => "<?php\nnamespace App\\Nexus\\Plugins\\ZzTestPlugin;\nclass ZzTestPlugin {}\n",
    ]);

    $name = app(ArchiveInstallService::class)->install('plugin', $upload);

    expect($name)->toBe('ZzTestPlugin');
    expect(app_path('Nexus/Plugins/ZzTestPlugin/ZzTestPlugin.php'))->toBeFile();
});

it('installs a widget by copying its files onto disk', function () {
    $upload = makeArchiveUpload('ZzTestWidget', [
        'ZzTestWidget.php' => "<?php\nnamespace App\\Nexus\\Widgets\\ZzTestWidget;\nclass ZzTestWidget {}\n",
    ]);

    $name = app(ArchiveInstallService::class)->install('widget', $upload);

    expect($name)->toBe('ZzTestWidget');
    expect(app_path('Nexus/Widgets/ZzTestWidget/ZzTestWidget.php'))->toBeFile();
});

it('installs a module by copying its files and running nexus:module:install', function () {
    $modelPhp = <<<'PHP'
<?php

namespace App\Nexus\Modules\ZzTestModule\Models;

use Illuminate\Database\Eloquent\Model;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

#[Module(name: 'zzTestModule', label: 'Zz Test Module', group: 'Site', showInMenu: false)]
class ZzTestModule extends Model
{
    use HasAttributeSchemaProperties;

    protected $table = 'zz_test_modules';
}
PHP;

    $upload = makeArchiveUpload('ZzTestModule', ['Models/ZzTestModule.php' => $modelPhp]);

    $name = app(ArchiveInstallService::class)->install('module', $upload);

    expect($name)->toBe('ZzTestModule');
    expect(app_path('Nexus/Modules/ZzTestModule/Models/ZzTestModule.php'))->toBeFile();
    expect(NexusModule::findByName('zzTestModule'))->not->toBeNull();
});

it('rejects an archive with more than one top-level entry', function () {
    $upload = makeArchiveUpload('ZzTestPlugin', ['ZzTestPlugin.php' => '<?php']);
    // Add a second top-level file after the fact so the archive has two entries.
    $zip = new ZipArchive;
    $zip->open($upload->getRealPath());
    $zip->addFromString('stray.txt', 'oops');
    $zip->close();

    app(ArchiveInstallService::class)->install('plugin', $upload);
})->throws(RuntimeException::class);

it('refuses to overwrite an already-installed plugin', function () {
    File::ensureDirectoryExists(app_path('Nexus/Plugins/ZzTestPlugin'));
    File::put(app_path('Nexus/Plugins/ZzTestPlugin/marker.txt'), 'already here');

    $upload = makeArchiveUpload('ZzTestPlugin', ['ZzTestPlugin.php' => '<?php']);

    try {
        app(ArchiveInstallService::class)->install('plugin', $upload);
    } catch (RuntimeException $e) {
        expect(File::get(app_path('Nexus/Plugins/ZzTestPlugin/marker.txt')))->toBe('already here');

        throw $e;
    }
})->throws(RuntimeException::class);

it('rejects a file that is not a valid zip', function () {
    $path = storage_path('app/tmp/not-a-zip-'.Str::random(8).'.zip');
    File::ensureDirectoryExists(dirname($path));
    File::put($path, 'not a zip file');
    $upload = new UploadedFile($path, 'broken.zip', 'application/zip', null, true);

    app(ArchiveInstallService::class)->install('plugin', $upload);
})->throws(RuntimeException::class);
