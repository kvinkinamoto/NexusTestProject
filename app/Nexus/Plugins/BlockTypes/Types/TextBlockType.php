<?php

namespace App\Nexus\Plugins\BlockTypes\Types;

use Nodex\Nexus\Services\Blocks\BlockFieldDefinition;
use Nodex\Nexus\Services\Blocks\BlockTypeDefinition;

class TextBlockType implements BlockTypeDefinition
{
    public function key(): string
    {
        return 'text';
    }

    public function label(): string
    {
        return 'Text';
    }

    public function icon(): ?string
    {
        return 'solar:text-bold';
    }

    public function fields(): array
    {
        return [
            new BlockFieldDefinition('heading', 'string', 'Heading'),
            new BlockFieldDefinition('body', 'text', 'Body', required: true),
        ];
    }
}
