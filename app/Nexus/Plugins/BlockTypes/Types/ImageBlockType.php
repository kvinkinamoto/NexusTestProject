<?php

namespace App\Nexus\Plugins\BlockTypes\Types;

use Nodex\Nexus\Services\Blocks\BlockFieldDefinition;
use Nodex\Nexus\Services\Blocks\BlockTypeDefinition;

class ImageBlockType implements BlockTypeDefinition
{
    public function key(): string
    {
        return 'image';
    }

    public function label(): string
    {
        return 'Image';
    }

    public function icon(): ?string
    {
        return 'solar:gallery-bold';
    }

    public function fields(): array
    {
        return [
            new BlockFieldDefinition('image', 'image', 'Image', required: true),
            new BlockFieldDefinition('caption', 'string', 'Caption'),
        ];
    }
}
