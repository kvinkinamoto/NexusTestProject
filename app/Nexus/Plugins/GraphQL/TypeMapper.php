<?php

namespace App\Nexus\Plugins\GraphQL;

use GraphQL\Type\Definition\Type;

/**
 * Maps a #[Field]'s widget-type string (the same vocabulary the admin form
 * dispatcher uses — 'string', 'repeater', 'gallery', ...) to a GraphQL
 * scalar. Deliberately narrow: only types with an honest 1:1 scalar mapping
 * are listed here. Everything else (repeater/blockEditor/gallery/image(s)/
 * video(s)/file/json/view/password/relationManager/location/multiple_string)
 * has no entry and is treated as unsupported by SchemaBuilder, which drops
 * the field from the schema entirely rather than guessing at a mapping.
 */
class TypeMapper
{
    private const STRING_TYPES = [
        'string', 'text', 'email', 'url', 'slug', 'color', 'icon', 'markdown', 'phone', 'enum', 'date', 'datetime',
    ];

    private const FLOAT_TYPES = ['number', 'range', 'currency', 'rating'];

    private const BOOLEAN_TYPES = ['boolean'];

    public static function scalarFor(string $widgetType): ?Type
    {
        return match (true) {
            in_array($widgetType, self::STRING_TYPES, true) => Type::string(),
            in_array($widgetType, self::FLOAT_TYPES, true) => Type::float(),
            in_array($widgetType, self::BOOLEAN_TYPES, true) => Type::boolean(),
            default => null,
        };
    }

    public static function isSupported(string $widgetType): bool
    {
        return self::scalarFor($widgetType) !== null;
    }
}
