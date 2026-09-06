<?php

namespace App\Nexus\Plugins\BlockTypes\Types;

use Nodex\Nexus\Services\Blocks\BlockFieldDefinition;
use Nodex\Nexus\Services\Blocks\BlockTypeDefinition;

class CtaBlockType implements BlockTypeDefinition
{
    public function key(): string
    {
        return 'cta';
    }

    public function label(): string
    {
        return 'Call to Action';
    }

    public function icon(): ?string
    {
        return 'solar:cursor-bold';
    }

    public function fields(): array
    {
        return [
            new BlockFieldDefinition('heading', 'string', 'Heading', required: true),
            new BlockFieldDefinition('button_label', 'string', 'Button label', required: true),
            new BlockFieldDefinition('button_url', 'url', 'Button URL', required: true),
        ];
    }
}
