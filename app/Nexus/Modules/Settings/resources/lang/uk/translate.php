<?php

return [
    'module_name' => 'Налаштування сайту',
    'index_title' => 'Налаштування сайту',
    'group_label' => 'Налаштування сайту',

    // sections (keyed by the raw section name — same convention as Demo's translate.php)
    'main' => 'Загальні',
    'media' => 'Медіа',

    // field labels (keyed lowercase — _label.blade.php looks up Str::lower($field->label))
    'id' => 'ID',
    'site name' => 'Назва сайту',
    'site description' => 'Опис сайту',
    'maintenance mode' => 'Режим обслуговування',
    'logo' => 'Логотип',
    'favicon' => 'Фавікон',

    'docs' => [
        'title' => 'Налаштування сайту',
        'subtitle' => 'Глобальні налаштування сайту (назва, опис, режим обслуговування, логотип, фавікон) — єдиний запис, доступний з випадаючого меню в шапці адмінки.',
    ],
];
