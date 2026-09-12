<?php

return [
    'module_name' => 'Demo',
    'index_title' => 'Demo',
    'group_label' => 'Demo',

    // wizard tabs (keyed by the raw tab name, see AttributeSchemaReader::processSectionAttrs())
    'basic' => 'Basic',
    'media' => 'Media',
    'choices' => 'Choices',
    'relations' => 'Relations',
    'repeater' => 'Repeater',
    'advanced' => 'Advanced',
    'extra' => 'Extra',

    // sections (keyed by the raw section name — same convention as tabs above)
    'text_inputs' => 'Text inputs',
    'toggles_dates' => 'Toggles & dates',
    'repeater_sect' => 'Repeater',
    'extra_fields' => 'Extra field types',

    // field labels (keyed lowercase — _label.blade.php looks up Str::lower($field->label))
    'id' => 'ID',
    'title' => 'Title',
    'translated title' => 'Translated title',
    'description' => 'Description',
    'count' => 'Count',
    'email' => 'Email',
    'secret' => 'Secret',
    'is active' => 'Is active',
    'active' => 'Active',
    'event date' => 'Event date',
    'event at' => 'Event at',
    'image' => 'Image',
    'video' => 'Video',
    'gallery images' => 'Gallery images',
    'gallery (media library)' => 'Gallery (media library)',
    'gallery videos' => 'Gallery videos',
    'status' => 'Status',
    'priority' => 'Priority',
    'permissions (preload)' => 'Permissions (preload)',
    'permissions (ajax search only)' => 'Permissions (ajax search only)',
    'permissions (ajax preload + search)' => 'Permissions (ajax preload + search)',
    'primary permission' => 'Primary permission',
    'permissions (preload) — list' => 'Permissions (preload) — list',
    'items' => 'Items',
    'rating' => 'Rating',
    'phone (overridden view)' => 'Phone (overridden view)',
    'price preview' => 'Price preview',
    'show extra field' => 'Show extra field',
    'extra field' => 'Extra field',
    'notes (read-only json)' => 'Notes (read-only json)',
    'api only note' => 'Api only note',
    'locked after create' => 'Locked after create',

    'event time' => 'Event time',
    'office location' => 'Office location',
    'tags' => 'Tags',
    'priority (radio)' => 'Priority (radio)',
    'slug' => 'Slug',
    'theme color' => 'Theme color',
    'nav icon' => 'Nav icon',
    'attachment' => 'Attachment',
    'notes (markdown)' => 'Notes (markdown)',
    'volume' => 'Volume',
    'price' => 'Price',
    'stars' => 'Stars',
    'website' => 'Website',

    // #[TableFilter] placeholder (module-table.blade.php looks up
    // Str::lower($filterCfg->label))
    'search by title' => 'Search by title',

    // module-level #[Setting(...)] labels (module-settings-form.blade.php,
    // same lowercase-label convention as field labels above)
    'items per page' => 'Items per page',
    'show archived by default' => 'Show archived by default',

    // enum values (DemoStatus — enum.blade.php falls back to {module}::translate.{value})
    'draft' => 'Draft',
    'published' => 'Published',
    'archived' => 'Archived',
];
