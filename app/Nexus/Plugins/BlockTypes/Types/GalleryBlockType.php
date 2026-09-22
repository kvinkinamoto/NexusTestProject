<?php

namespace App\Nexus\Plugins\BlockTypes\Types;

use Nodex\Nexus\Services\Blocks\BlockFieldDefinition;
use Nodex\Nexus\Services\Blocks\BlockTypeDefinition;

/**
 * A fixed 4-photo grid (About page's "Наша команда та шоурум" gallery).
 * Same fixed-slot reasoning as AdvantagesBlockType — no repeater support in
 * BlockFieldDefinition.
 */
class GalleryBlockType implements BlockTypeDefinition
{
    public function key(): string
    {
        return 'gallery';
    }

    public function label(): string
    {
        return 'Photo gallery';
    }

    public function icon(): ?string
    {
        return 'solar:gallery-wide-bold';
    }

    public function fields(): array
    {
        return [
            new BlockFieldDefinition('heading', 'string', 'Heading', translatable: true),
            new BlockFieldDefinition('image_1', 'image', 'Image 1', required: true),
            new BlockFieldDefinition('image_2', 'image', 'Image 2'),
            new BlockFieldDefinition('image_3', 'image', 'Image 3'),
            new BlockFieldDefinition('image_4', 'image', 'Image 4'),
        ];
    }
}
