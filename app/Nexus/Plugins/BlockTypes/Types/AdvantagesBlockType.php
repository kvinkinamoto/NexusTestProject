<?php

namespace App\Nexus\Plugins\BlockTypes\Types;

use Nodex\Nexus\Services\Blocks\BlockFieldDefinition;
use Nodex\Nexus\Services\Blocks\BlockTypeDefinition;

/**
 * A fixed 4-item advantages/benefits grid (ТЗ's "Переваги магазину" cards).
 * BlockFieldDefinition has no repeater support (see its own docblock — a
 * block's field set is a plain fields() list, not a variable-length
 * collection), so this is 4 explicit item slots rather than N repeatable
 * ones — matches the ТЗ's own "наприклад" 4-example wording.
 */
class AdvantagesBlockType implements BlockTypeDefinition
{
    public function key(): string
    {
        return 'advantages';
    }

    public function label(): string
    {
        return 'Advantages grid';
    }

    public function icon(): ?string
    {
        return 'solar:medal-star-bold';
    }

    public function fields(): array
    {
        return [
            new BlockFieldDefinition('heading', 'string', 'Heading'),
            new BlockFieldDefinition('item1_title', 'string', 'Item 1 — title', required: true),
            new BlockFieldDefinition('item1_text', 'string', 'Item 1 — text'),
            new BlockFieldDefinition('item2_title', 'string', 'Item 2 — title'),
            new BlockFieldDefinition('item2_text', 'string', 'Item 2 — text'),
            new BlockFieldDefinition('item3_title', 'string', 'Item 3 — title'),
            new BlockFieldDefinition('item3_text', 'string', 'Item 3 — text'),
            new BlockFieldDefinition('item4_title', 'string', 'Item 4 — title'),
            new BlockFieldDefinition('item4_text', 'string', 'Item 4 — text'),
        ];
    }
}
