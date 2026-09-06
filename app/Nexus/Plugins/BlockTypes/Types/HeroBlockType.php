<?php

namespace App\Nexus\Plugins\BlockTypes\Types;

use Nodex\Nexus\Services\Blocks\BlockFieldDefinition;
use Nodex\Nexus\Services\Blocks\BlockTypeDefinition;

class HeroBlockType implements BlockTypeDefinition
{
    public function key(): string
    {
        return 'hero';
    }

    public function label(): string
    {
        return 'Hero';
    }

    public function icon(): ?string
    {
        return 'solar:gallery-wide-bold';
    }

    public function fields(): array
    {
        return [
            new BlockFieldDefinition('headline', 'string', 'Headline', required: true),
            new BlockFieldDefinition('subheadline', 'text', 'Subheadline'),
            new BlockFieldDefinition('image', 'image', 'Image'),
            new BlockFieldDefinition('cta_label', 'string', 'Button label'),
            new BlockFieldDefinition('cta_url', 'url', 'Button URL'),
        ];
    }
}
