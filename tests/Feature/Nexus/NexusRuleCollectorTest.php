<?php

use Nodex\Nexus\Models\Module;
use Nodex\Nexus\Services\Validation\NexusRuleCollector;

/**
 * Regression coverage for the validation-fragility fix: a relation field
 * used to get zero rule coverage from collect() (silently dropped by
 * Laravel's validated()) unless a dedicated Request happened to declare it
 * by hand — see NexusRuleCollector's own class docblock for the full story.
 * Demo's relation fields (belongsToMany, belongsTo, a repeater, and a
 * read-only relationManager) exercise every cardinality collectRelationDefaultRules()
 * has to handle.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();

    $this->moduleConfig = Module::where('name', 'Demo')->firstOrFail()->config;
    $this->collector = app(NexusRuleCollector::class);
});

it('gives a multi (belongsToMany) relation field a nullable array default', function () {
    $rules = $this->collector->collect($this->moduleConfig, 'store');

    expect($rules)->toHaveKey('relation.permissionsPreload')
        ->and($rules['relation.permissionsPreload'])->toBe(['nullable', 'array']);
});

it('gives a single (belongsTo) relation field a nullable scalar default, no array rule', function () {
    $rules = $this->collector->collect($this->moduleConfig, 'store');

    expect($rules)->toHaveKey('relation.primaryPermission')
        ->and($rules['relation.primaryPermission'])->toBe(['nullable']);
});

it('gives a repeater relation both its own array default and its per-column rules', function () {
    $rules = $this->collector->collect($this->moduleConfig, 'store');

    expect($rules)->toHaveKey('relation.items')
        ->and($rules['relation.items'])->toBe(['nullable', 'array'])
        ->and($rules)->toHaveKey('relation.items.*.title')
        ->and($rules['relation.items.*.title'])->toBe(['required']);
});

it('never generates a rule for a read-only relationManager field, since it never posts data', function () {
    $rules = $this->collector->collect($this->moduleConfig, 'store');

    expect($rules)->not->toHaveKey('relation.permissionsList');
});

it('throws in non-production when a dedicated Request covers some fields but skips a relation field', function () {
    expect(fn () => $this->collector->assertRelationCoverage($this->moduleConfig, [
        'title' => 'required|string',
    ]))->toThrow(RuntimeException::class, 'permissionsPreload');
});

it('does not throw when every posting relation field is covered', function () {
    $this->collector->assertRelationCoverage($this->moduleConfig, [
        'relation.permissionsPreload' => 'nullable|array',
        'relation.permissionsSearchOnly' => 'nullable|array',
        'relation.permissionsLoadMode' => 'nullable|array',
        'relation.primaryPermission' => 'nullable|integer',
        'relation.items' => 'nullable|array',
    ]);
})->throwsNoExceptions();

it('does not throw for incomplete coverage in production — dev-only guard, not a runtime behavior change', function () {
    app()->instance('env', 'production');

    try {
        $this->collector->assertRelationCoverage($this->moduleConfig, ['title' => 'required']);
    } finally {
        app()->instance('env', 'testing');
    }
})->throwsNoExceptions();
