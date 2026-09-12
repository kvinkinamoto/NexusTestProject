<?php

namespace App\Nexus\Plugins\BlockTypes\Types;

use Nodex\Nexus\Services\Blocks\BlockFieldDefinition;
use Nodex\Nexus\Services\Blocks\BlockTypeDefinition;

/**
 * Side-by-side text + image section — covers "Наша історія"/"Наша місія"
 * style content the built-in Text/Image block types can't (they're always
 * full-width and stacked). `image_position` is a free-text 'left'/'right'
 * rather than a select — BlockFieldDefinition has no options/enum support.
 */
class TextImageBlockType implements BlockTypeDefinition
{
    public function key(): string
    {
        return 'text_image';
    }

    public function label(): string
    {
        return 'Text + Image';
    }

    public function icon(): ?string
    {
        return 'solar:gallery-minimalistic-bold';
    }

    public function fields(): array
    {
        return [
            new BlockFieldDefinition('heading', 'string', 'Heading', required: true),
            new BlockFieldDefinition('body', 'text', 'Body', required: true),
            new BlockFieldDefinition('image', 'image', 'Image'),
            new BlockFieldDefinition('image_position', 'string', "Image position ('left' or 'right')"),
        ];
    }
}
