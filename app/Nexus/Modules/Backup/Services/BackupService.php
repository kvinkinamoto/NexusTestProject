<?php

namespace App\Nexus\Modules\Backup\Services;

use App\Nexus\Modules\Backup\Events\BackupFailed;
use App\Nexus\Modules\Backup\Events\BackupFinished;
use App\Nexus\Modules\Backup\Models\Backup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Nodex\Nexus\Services\Interfaces\SettingsProviderInterface;
use RuntimeException;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;
use ZipArchive;

/**
 * Faithful port of ZentaraStartProject's Backup\Services\BackupService, with
 * two deliberate changes (both approved by the user before this phase):
 * - database dumping goes through spatie/db-dumper instead of a hand-rolled
 *   exec() + hardcoded OSPanel Windows binary path fallback;
 * - callers (RunBackupJob) queue this instead of running it inline in the
 *   admin HTTP request, since a large DB/code/storage backup can take a
 *   while — see the module docs.blade.php.
 *
 * Directory/file exclusions for the code/storage zips are unchanged from the
 * old project.
 */
class BackupService
{
    private const CODE_EXCLUDES = ['vendor', 'node_modules', 'storage', '.git', '.idea', 'scratch', 'bootstrap/cache'];

    public function __construct(
        private readonly SettingsProviderInterface $settings,
    ) {}

    public function run(Backup $backup): void
    {
        $backup->forceFill(['last_status' => 'running'])->save();

        $tempDir = storage_path('app/backups_temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $prefix = Str::slug($backup->name).'_'.now()->format('Y_m_d_His');
        $errors = [];

        if ($backup->backup_database) {
            try {
                $this->backupDatabase($backup, $tempDir, $prefix);
            } catch (\Throwable $e) {
                $errors[] = "database: {$e->getMessage()}";
                event(new BackupFailed($backup, 'db', $e->getMessage()));
            }
        }

        if ($backup->backup_code) {
            try {
                $this->backupCode($backup, $tempDir, $prefix);
            } catch (\Throwable $e) {
                $errors[] = "code: {$e->getMessage()}";
                event(new BackupFailed($backup, 'code', $e->getMessage()));
            }
        }

        if ($backup->backup_storage) {
            try {
                $this->backupStorage($backup, $tempDir, $prefix);
            } catch (\Throwable $e) {
                $errors[] = "storage: {$e->getMessage()}";
                event(new BackupFailed($backup, 'storage', $e->getMessage()));
            }
        }

        $this->cleanupOldBackups($backup);

        $backup->forceFill([
            'last_run_at' => now(),
            'last_status' => $errors === [] ? 'success' : 'failed',
            'last_error' => $errors === [] ? null : implode('; ', $errors),
        ])->save();

        if ($errors !== []) {
            throw new RuntimeException('Backup finished with errors: '.implode('; ', $errors));
        }
    }

    private function backupDatabase(Backup $backup, string $tempDir, string $prefix): void
    {
        $driver = config('database.default');
        $connection = config("database.connections.{$driver}");
        $file = "{$tempDir}/{$prefix}_db.sql";

        match ($driver) {
            'mysql', 'mariadb' => $this->dumpMysql($connection, $file),
            'pgsql' => $this->dumpPostgres($connection, $file),
            default => throw new RuntimeException("Unsupported database driver for backup: {$driver}"),
        };

        $this->uploadToTargetDisk($file, basename($file));
        event(new BackupFinished($backup, 'db', basename($file)));
    }

    /** @param array<string, mixed> $connection */
    private function dumpMysql(array $connection, string $file): void
    {
        $dumper = MySql::create()
            ->setDbName($connection['database'])
            ->setUserName($connection['username'])
            ->setPassword($connection['password'] ?? '')
            ->setHost($connection['host'])
            ->setPort((int) ($connection['port'] ?? 3306));

        if ($binPath = config('backup.mysql_bin_path')) {
            $dumper->setDumpBinaryPath($binPath);
        }

        $dumper->dumpToFile($file);
    }

    /** @param array<string, mixed> $connection */
    private function dumpPostgres(array $connection, string $file): void
    {
        $dumper = PostgreSql::create()
            ->setDbName($connection['database'])
            ->setUserName($connection['username'])
            ->setPassword($connection['password'] ?? '')
            ->setHost($connection['host'])
            ->setPort((int) ($connection['port'] ?? 5432));

        if ($binPath = config('backup.pgsql_bin_path')) {
            $dumper->setDumpBinaryPath($binPath);
        }

        $dumper->dumpToFile($file);
    }

    private function backupCode(Backup $backup, string $tempDir, string $prefix): void
    {
        $file = "{$tempDir}/{$prefix}_code.zip";
        $this->zipDirectory(base_path(), $file, self::CODE_EXCLUDES);
        $this->uploadToTargetDisk($file, basename($file));
        event(new BackupFinished($backup, 'code', basename($file)));
    }

    private function backupStorage(Backup $backup, string $tempDir, string $prefix): void
    {
        $file = "{$tempDir}/{$prefix}_storage.zip";
        $this->zipDirectory(storage_path('app/public'), $file, []);
        $this->uploadToTargetDisk($file, basename($file));
        event(new BackupFinished($backup, 'storage', basename($file)));
    }

    /** @param array<int, string> $excludes */
    private function zipDirectory(string $source, string $destination, array $excludes): void
    {
        if (! is_dir($source)) {
            throw new RuntimeException("Cannot zip missing directory: {$source}");
        }

        $zip = new ZipArchive;
        if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Could not create zip archive at {$destination}");
        }

        $sourceReal = realpath($source);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = ltrim(str_replace($sourceReal, '', $item->getPathname()), DIRECTORY_SEPARATOR);
            $relativePath = str_replace('\\', '/', $relativePath);

            foreach ($excludes as $exclude) {
                if ($relativePath === $exclude || str_starts_with($relativePath, $exclude.'/')) {
                    continue 2;
                }
            }

            if ($item->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($item->getPathname(), $relativePath);
            }
        }

        $zip->close();
    }

    private function uploadToTargetDisk(string $localFile, string $filename): void
    {
        $disk = $this->settings->get('backup', 'storage_disk', 'local');
        $path = $this->settings->get('backup', 'storage_path', 'backups');

        $stream = fopen($localFile, 'r');
        Storage::disk($disk)->put(trim($path, '/').'/'.$filename, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        unlink($localFile);
    }

    private function cleanupOldBackups(Backup $backup): void
    {
        $maxBackups = $backup->max_backups ?: 10;
        $disk = $this->settings->get('backup', 'storage_disk', 'local');
        $path = trim($this->settings->get('backup', 'storage_path', 'backups'), '/');
        $namePrefix = Str::slug($backup->name).'_';

        $files = collect(Storage::disk($disk)->files($path))
            ->filter(fn (string $file) => str_starts_with(basename($file), $namePrefix))
            ->sortByDesc(fn (string $file) => Storage::disk($disk)->lastModified($file))
            ->values();

        $files->slice($maxBackups)->each(fn (string $file) => Storage::disk($disk)->delete($file));
    }
}
