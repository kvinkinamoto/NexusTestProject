<?php

namespace App\Nexus\Modules\Restore\Services;

use App\Nexus\Modules\Restore\Models\Restore;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Nodex\Nexus\Services\Interfaces\SettingsProviderInterface;
use RuntimeException;
use ZipArchive;

/**
 * Faithful port of ZentaraStartProject's Restore\Services\RestoreService,
 * with the same two deliberate changes as BackupService: reads
 * storage_disk/storage_path from Backup's own #[Setting]s (see Restore
 * model docblock — old project duplicated these settings on both modules
 * and kept them in sync by hand) and is meant to be called from a queued
 * job rather than inline in the admin request.
 *
 * One security fix versus the old project: the MySQL restore shells out
 * using a temporary `--defaults-extra-file` for credentials (same approach
 * spatie/db-dumper uses for the backup side) instead of putting the
 * password directly on the command line, where it would be visible to any
 * other process on the box via `ps`/Task Manager for the duration of the
 * import.
 *
 * PostgreSQL restore remains an intentionally unimplemented stub, matching
 * the old project exactly — it never had a working implementation either.
 */
class RestoreService
{
    public function __construct(
        private readonly SettingsProviderInterface $settings,
    ) {}

    public function scanBackups(): void
    {
        $disk = $this->settings->get('backup', 'storage_disk', 'local');
        $path = trim((string) $this->settings->get('backup', 'storage_path', 'backups'), '/');

        $found = [];

        foreach (Storage::disk($disk)->files($path) as $file) {
            $filename = basename($file);
            $found[] = $filename;

            Restore::query()->updateOrCreate(
                ['filename' => $filename],
                [
                    'disk' => $disk,
                    'path' => $file,
                    'type' => $this->inferType($filename),
                    'size' => $this->formatBytes(Storage::disk($disk)->size($file)),
                    'last_modified' => Carbon::createFromTimestamp(Storage::disk($disk)->lastModified($file)),
                    'status' => 'available',
                ]
            );
        }

        Restore::query()->whereNotIn('filename', $found)->delete();
    }

    public function restore(Restore $restore): void
    {
        $restore->forceFill(['status' => 'restoring'])->save();

        try {
            $localFile = $this->ensureLocalCopy($restore);

            match ($restore->type) {
                'db' => $this->restoreDatabase($localFile),
                'code' => $this->restoreCode($localFile),
                'storage' => $this->restoreStorage($localFile),
                default => throw new RuntimeException("Cannot restore unknown backup type: {$restore->type}"),
            };

            @unlink($localFile);

            try {
                $restore->forceFill(['status' => 'restored'])->save();
            } catch (\Throwable $e) {
                // A DB restore that just replaced the schema may have taken
                // the restores table itself down with it — not fatal, the
                // restore already succeeded.
                Log::warning('Could not flag restore as restored after a successful run', [
                    'restore_id' => $restore->id,
                    'message' => $e->getMessage(),
                ]);
            }
        } catch (\Throwable $e) {
            try {
                $restore->markFailed($e->getMessage());
            } catch (\Throwable $inner) {
                Log::warning('Could not flag restore as failed', [
                    'restore_id' => $restore->id,
                    'message' => $inner->getMessage(),
                ]);
            }

            throw $e;
        }
    }

    private function ensureLocalCopy(Restore $restore): string
    {
        $tempDir = storage_path('app/restores_temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $localFile = $tempDir.'/'.$restore->filename;
        file_put_contents($localFile, Storage::disk($restore->disk)->readStream($restore->path));

        return $localFile;
    }

    private function restoreDatabase(string $file): void
    {
        $driver = config('database.default');

        if (! in_array($driver, ['mysql', 'mariadb', 'pgsql'], true)) {
            throw new RuntimeException("Unsupported database driver for restore: {$driver}");
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->dropAllMysqlTables();
            $this->restoreMysql($file);

            return;
        }

        throw new RuntimeException('PostgreSQL restore not fully implemented yet.');
    }

    private function dropAllMysqlTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach (DB::select('SHOW TABLES') as $row) {
            $table = array_values((array) $row)[0];
            DB::statement("DROP TABLE IF EXISTS `{$table}`");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function restoreMysql(string $file): void
    {
        $connection = config('database.connections.'.config('database.default'));
        $binPath = config('backup.mysql_bin_path');
        $binary = $binPath ? rtrim($binPath, '/').'/mysql' : 'mysql';

        $credentialsFile = tempnam(sys_get_temp_dir(), 'mysql_cred_');
        file_put_contents($credentialsFile, implode(PHP_EOL, [
            '[client]',
            "user = '{$connection['username']}'",
            "password = '{$connection['password']}'",
            "host = '{$connection['host']}'",
            "port = '{$connection['port']}'",
        ]));

        $command = sprintf(
            '%s --defaults-extra-file=%s %s < %s 2>&1',
            escapeshellarg($binary),
            escapeshellarg($credentialsFile),
            escapeshellarg($connection['database']),
            escapeshellarg($file)
        );

        exec($command, $output, $returnVar);
        @unlink($credentialsFile);

        if ($returnVar !== 0) {
            throw new RuntimeException('mysql import failed: '.implode("\n", $output));
        }
    }

    private function restoreCode(string $zipFile): void
    {
        $this->swapDirectory(base_path(), $zipFile);
    }

    private function restoreStorage(string $zipFile): void
    {
        $this->swapDirectory(storage_path('app/public'), $zipFile);
    }

    private function swapDirectory(string $liveDir, string $zipFile): void
    {
        $timestamp = now()->format('YmdHis');
        $newDir = rtrim($liveDir, '/\\')."_new_{$timestamp}";
        $oldDir = rtrim($liveDir, '/\\')."_old_{$timestamp}";

        File::copyDirectory($liveDir, $newDir);

        $zip = new ZipArchive;
        if ($zip->open($zipFile) !== true) {
            throw new RuntimeException("Could not open zip archive: {$zipFile}");
        }
        $zip->extractTo($newDir);
        $zip->close();

        rename($liveDir, $oldDir);

        try {
            rename($newDir, $liveDir);
        } catch (\Throwable $e) {
            rename($oldDir, $liveDir);

            throw $e;
        }
    }

    private function inferType(string $filename): string
    {
        return match (true) {
            str_ends_with($filename, '_db.sql') => 'db',
            str_ends_with($filename, '_code.zip') => 'code',
            str_ends_with($filename, '_storage.zip') => 'storage',
            default => 'unknown',
        };
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        return round($bytes / (1024 ** $power), 2).' '.$units[$power];
    }
}
