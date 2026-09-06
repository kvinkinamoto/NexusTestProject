<?php

use Nodex\Nexus\Services\Blocks\BlockTypeRegistry;

/**
 * Coverage for task #7's open-core split: the built-in block-type catalog
 * (Hero/Text/Image/CTA) is now a premium plugin (app/Nexus/Plugins/BlockTypes)
 * rather than core-registered — this proves BlockTypesPlugin::boot() (fired
 * automatically via PluginManager::registerAll()/bootAll(), same
 * unconditional boot-cycle PluginSystemTest.php relies on) still populates
 * BlockTypeRegistry with all four types, and that its added 'nexus::' view
 * namespace resolves the public block partials PageRenderTest.php exercises
 * end-to-end.
 */
it('registers the built-in block-type catalog via the premium plugin', function () {
    $registry = app(BlockTypeRegistry::class);

    expect($registry->find('hero'))->not->toBeNull()
        ->and($registry->find('text'))->not->toBeNull()
        ->and($registry->find('image'))->not->toBeNull()
        ->and($registry->find('cta'))->not->toBeNull();

    expect($registry->find('hero')->label())->toBe('Hero');
});

it('adds a second nexus:: view search path resolving the plugin\'s own public block partials', function () {
    expect(view()->exists('nexus::public.block_types.hero'))->toBeTrue()
        ->and(view()->exists('nexus::public.block_types.cta'))->toBeTrue();
});
