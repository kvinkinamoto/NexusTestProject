<?php

return [
    'module_name' => 'Налаштування сайту',
    'index_title' => 'Налаштування сайту',
    'group_label' => 'Налаштування сайту',
    'settings' => 'Налаштування сайту',

    // setting labels (keyed lowercase — same convention as field labels,
    // see module-settings-form.blade.php / _label.blade.php)
    'site name' => 'Назва сайту',
    'site description' => 'Опис сайту',
    'maintenance mode' => 'Режим обслуговування',
    'logo' => 'Логотип',
    'favicon' => 'Фавікон',

    // сторінка "Усі налаштування" (Http/Controllers/AdminController::index())
    'manage' => 'Керувати',
    'settings count suffix' => 'налаштувань',
    'no settings registered' => 'Жоден модуль ще не має налаштувань.',

    'docs' => [
        'title' => 'Налаштування сайту',
        'subtitle' => 'Глобальні налаштування сайту (назва, опис, режим обслуговування, логотип, фавікон), доступні з шапки адмінки й збережені через загальний реєстр налаштувань модулів — без власної таблиці.',
    ],
];
