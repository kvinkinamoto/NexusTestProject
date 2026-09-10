<?php

namespace App\Nexus\Modules\Language\Listeners;

use App\Nexus\Modules\Language\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Nodex\Nexus\Events\EntityUpdating;
use Nodex\Nexus\Services\ModuleRegistry;

/**
 * Faithful port of ZentaraStartProject's Language\Listeners\ModuleEventListener
 * — if an admin renames a Language row's `code` (e.g. 'ua' -> 'uk'), every
 * other module's `Spatie\Translatable` JSON column has its old-locale key
 * rekeyed to the new one, so existing translated content isn't silently
 * orphaned under a locale code nothing reads anymore.
 *
 * Listens on Nodex\Nexus\Events\EntityUpdating (fired inside
 * UpdateActionMethod's DB transaction, right before $model->save()) rather
 * than EntityUpdated — it carries $oldData (pre-save attributes) alongside
 * $data (about to be saved), so the old/new code comparison needs no
 * getOriginal()/getPrevious() workaround the way the old project's listener
 * did.
 *
 * Rewritten to read/rewrite each row through the query builder in PHP
 * instead of the old project's raw JSON_SET/JSON_REMOVE SQL: that SQL is
 * MySQL/MariaDB-only (this app also needs to run its test suite against
 * sqlite — see feedback_test_suite_runs_on_sqlite memory) and was
 * completely untested even in the source project. This version works on
 * any driver and is covered by a real test.
 */
class RekeyTranslationsOnLocaleRename
{
    public function __construct(private readonly ModuleRegistry $moduleRegistry) {}

    public function handle(EntityUpdating $event): void
    {
        if (! $event->model instanceof Language) {
            return;
        }

        $oldCode = $event->oldData['code'] ?? null;
        $newCode = $event->data['code'] ?? null;

        if (! $oldCode || ! $newCode || $oldCode === $newCode) {
            return;
        }

        foreach ($this->moduleRegistry->getAllModules() as $module) {
            $this->rekeyModuleModels($module, $oldCode, $newCode);
        }
    }

    /** @param array{name: string, path: string, namespace: string} $module */
    private function rekeyModuleModels(array $module, string $oldCode, string $newCode): void
    {
        $modelsDir = $module['path'].DIRECTORY_SEPARATOR.'Models';

        if (! File::isDirectory($modelsDir)) {
            return;
        }

        foreach (File::files($modelsDir) as $file) {
            $class = $module['namespace'].'\\Models\\'.$file->getBasename('.php');

            if (! class_exists($class) || ! is_subclass_of($class, Model::class)) {
                continue;
            }

            $instance = new $class;

            if (! property_exists($instance, 'translatable')) {
                continue;
            }

            $property = (new \ReflectionClass($class))->getProperty('translatable');
            $property->setAccessible(true);
            $fields = $property->getValue($instance);

            if (empty($fields)) {
                continue;
            }

            $this->rekeyTable($instance, $fields, $oldCode, $newCode);
        }
    }

    /** @param array<int, string> $fields */
    private function rekeyTable(Model $instance, array $fields, string $oldCode, string $newCode): void
    {
        $connection = $instance->getConnection();
        $table = $instance->getTable();

        $rows = $connection->table($table)->select(array_merge([$instance->getKeyName()], $fields))->get();

        foreach ($rows as $row) {
            $updates = [];

            foreach ($fields as $field) {
                $raw = $row->{$field} ?? null;

                if (! $raw) {
                    continue;
                }

                $decoded = json_decode($raw, true);

                if (! is_array($decoded) || ! array_key_exists($oldCode, $decoded)) {
                    continue;
                }

                $decoded[$newCode] = $decoded[$oldCode];
                unset($decoded[$oldCode]);
                $updates[$field] = json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }

            if ($updates !== []) {
                $connection->table($table)->where($instance->getKeyName(), $row->{$instance->getKeyName()})->update($updates);
            }
        }
    }
}
