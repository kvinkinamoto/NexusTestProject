<?php

namespace App\Nexus\Plugins\BlockTypes\Types;

use Nodex\Nexus\Services\Blocks\BlockFieldDefinition;
use Nodex\Nexus\Services\Blocks\BlockTypeDefinition;

/** Embeds an existing Form module record (by slug) via the @nexusForm directive. */
class FormEmbedBlockType implements BlockTypeDefinition
{
    public function key(): string
    {
        return 'form_embed';
    }

    public function label(): string
    {
        return 'Embedded form';
    }

    public function icon(): ?string
    {
        return 'solar:letter-bold';
    }

    public function fields(): array
    {
        return [
            new BlockFieldDefinition('heading', 'string', 'Heading', translatable: true),
            new BlockFieldDefinition('form_slug', 'string', 'Form slug', required: true),
        ];
    }
}
