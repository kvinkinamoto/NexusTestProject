<?php

namespace App\Nexus\Plugins\BlockTypes\Types;

use Nodex\Nexus\Services\Blocks\BlockFieldDefinition;
use Nodex\Nexus\Services\Blocks\BlockTypeDefinition;

/** Contacts page info column: address/phone/email/hours + a map placeholder label. */
class ContactInfoBlockType implements BlockTypeDefinition
{
    public function key(): string
    {
        return 'contact_info';
    }

    public function label(): string
    {
        return 'Contact info';
    }

    public function icon(): ?string
    {
        return 'solar:map-point-bold';
    }

    public function fields(): array
    {
        return [
            new BlockFieldDefinition('address', 'string', 'Address', required: true),
            new BlockFieldDefinition('phone', 'string', 'Phone', required: true),
            new BlockFieldDefinition('email', 'string', 'Email'),
            new BlockFieldDefinition('hours', 'string', 'Working hours'),
            new BlockFieldDefinition('map_label', 'string', 'Map label'),
        ];
    }
}
