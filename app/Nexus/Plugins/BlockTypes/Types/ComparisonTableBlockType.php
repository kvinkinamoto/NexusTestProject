<?php

namespace App\Nexus\Plugins\BlockTypes\Types;

use Nodex\Nexus\Services\Blocks\BlockFieldDefinition;
use Nodex\Nexus\Services\Blocks\BlockTypeDefinition;

/**
 * A 4-column feature-comparison grid (Nexus vs. 3 competitors), the "modern
 * SaaS landing page" table format — one row per dimension, one column per
 * product, own-product column visually highlighted by the public view.
 * Same fixed-slot reasoning as AdvantagesBlockType/GalleryBlockType
 * (BlockFieldDefinition has no repeater support): 6 explicit row slots
 * rather than N repeatable ones. Column 1 is always "your product" and its
 * header isn't a field — only the 3 competitor column headers are editable,
 * so the block stays reusable for a different set of competitors without
 * code changes.
 */
class ComparisonTableBlockType implements BlockTypeDefinition
{
    public function key(): string
    {
        return 'comparison_table';
    }

    public function label(): string
    {
        return 'Comparison table';
    }

    public function icon(): ?string
    {
        return 'solar:widget-4-bold';
    }

    public function fields(): array
    {
        $fields = [
            new BlockFieldDefinition('heading', 'string', 'Heading', translatable: true),
            new BlockFieldDefinition('own_column_label', 'string', 'Column 1 label (your product)', required: true, translatable: true),
            new BlockFieldDefinition('column2_label', 'string', 'Column 2 label', required: true, translatable: true),
            new BlockFieldDefinition('column3_label', 'string', 'Column 3 label', required: true, translatable: true),
            new BlockFieldDefinition('column4_label', 'string', 'Column 4 label', required: true, translatable: true),
        ];

        for ($row = 1; $row <= 6; $row++) {
            $fields[] = new BlockFieldDefinition("row{$row}_feature", 'string', "Row {$row} — feature", translatable: true);
            $fields[] = new BlockFieldDefinition("row{$row}_own", 'string', "Row {$row} — your product's value", translatable: true);
            $fields[] = new BlockFieldDefinition("row{$row}_col2", 'string', "Row {$row} — column 2 value", translatable: true);
            $fields[] = new BlockFieldDefinition("row{$row}_col3", 'string', "Row {$row} — column 3 value", translatable: true);
            $fields[] = new BlockFieldDefinition("row{$row}_col4", 'string', "Row {$row} — column 4 value", translatable: true);
        }

        return $fields;
    }
}
