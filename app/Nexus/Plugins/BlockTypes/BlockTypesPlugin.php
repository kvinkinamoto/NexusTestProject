<?php

namespace App\Nexus\Plugins\BlockTypes;

use App\Nexus\Plugins\BlockTypes\Types\CtaBlockType;
use App\Nexus\Plugins\BlockTypes\Types\HeroBlockType;
use App\Nexus\Plugins\BlockTypes\Types\ImageBlockType;
use App\Nexus\Plugins\BlockTypes\Types\TextBlockType;
use Illuminate\Support\Facades\View;
use Nodex\Nexus\Attributes\TargetModule;
use Nodex\Nexus\Services\Blocks\BlockTypeRegistry;

/**
 * Premium plugin: the built-in block-type catalog (Hero/Text/Image/CTA) for
 * #[Field(type: 'blockEditor')] fields, extracted out of the open-core
 * nodex/nexus package. The block-editing engine itself (ManagesBlockFields,
 * the blockEditor field-type view, BlockTypeRegistry) stays in core and is
 * fully generic — this plugin only supplies the ready-made catalog. See
 * BlockTypeRegistry's own docblock: "a plugin/module can register its own
 * the same way, from its own service provider's boot() — no discovery
 * mechanism needed for this registry."
 *
 * #[TargetModule('User')] is just a required, always-installed anchor (see
 * GraphQLPlugin for the same pattern) — handle() is a no-op.
 *
 * View::addNamespace() APPENDS a hint path rather than replacing one, so
 * this adds a second search path under the same 'nexus::' namespace the
 * core package registers — @nexusBlocks resolves
 * 'nexus::public.block_types.{type}' the same way regardless of which
 * registered path actually holds the file.
 */
#[TargetModule('User')]
class BlockTypesPlugin
{
    public function handle(object $configuration): void {}

    public function boot(): void
    {
        $registry = app(BlockTypeRegistry::class);

        $registry->register(new HeroBlockType);
        $registry->register(new TextBlockType);
        $registry->register(new ImageBlockType);
        $registry->register(new CtaBlockType);

        View::addNamespace('nexus', __DIR__.'/resources/views');
    }
}
