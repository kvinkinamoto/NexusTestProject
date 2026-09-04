<?php

use App\Models\User;
use App\Nexus\Modules\Demo\Models\Demo;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleTable;
use Nodex\Nexus\Modules\BulkAction\Jobs\BulkActionJob;
use Spatie\Permission\Models\Permission;

/**
 * Regression coverage for ModuleTable::runGroupAction()'s size-based split:
 * a selection at or below ASYNC_BULK_ACTION_THRESHOLD still runs synchronously
 * (unchanged existing behavior), a selection above it dispatches
 * BulkActionJob instead — see that constant's docblock.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo(AdminPanelPermissionEnum::ALL->value);
    $this->actingAs($this->admin);
});

function createDemoRecords(int $count): array
{
    return collect(range(1, $count))
        ->map(fn ($i) => Demo::create(['title' => "Bulk fixture row {$i}"])->id)
        ->all();
}

it('still runs a small group action synchronously, unchanged', function () {
    $ids = createDemoRecords(3);

    Livewire::test(ModuleTable::class, ['moduleName' => 'Demo'])
        ->set('selected', $ids)
        ->call('runGroupAction', 'deleteGroup');

    expect(Demo::whereIn('id', $ids)->count())->toBe(0);
});

it('dispatches BulkActionJob instead of running the old inline loop above the threshold', function () {
    $ids = createDemoRecords(51);

    // QUEUE_CONNECTION=sync (this suite's default) runs a dispatched job
    // immediately rather than deferring it, so the practical difference
    // this test can observe isn't "nothing happened yet" — it's that the
    // job path (which reports progress and clears $selected up front) was
    // actually taken, not the old direct/synchronous branch.
    Livewire::test(ModuleTable::class, ['moduleName' => 'Demo'])
        ->set('selected', $ids)
        ->call('runGroupAction', 'deleteGroup')
        ->assertDispatched('nexus-bulk-action-started')
        ->assertSet('selected', []);

    expect(Demo::whereIn('id', $ids)->count())->toBe(0);
});

it('runs BulkActionJob end-to-end in chunks and reports progress to completion', function () {
    $ids = createDemoRecords(120);

    $cacheKey = 'bulk_test_key';
    (new BulkActionJob('Demo', 'deleteGroup', $ids, $cacheKey, $this->admin->id, chunkSize: 40))->handle();

    $progress = Cache::get($cacheKey);
    expect($progress['status'])->toBe('completed')
        ->and($progress['processed'])->toBe(120)
        ->and($progress['total'])->toBe(120);

    expect(Demo::whereIn('id', $ids)->count())->toBe(0);
});
