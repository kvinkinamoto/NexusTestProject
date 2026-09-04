<?php

return [
    'form' => 'форма',
    'Form' => 'Форма',
    'index_title' => 'Форми',
    'module_name' => 'Форма',

    'name' => 'Внутрішня назва',
    'title' => 'Заголовок',
    'slug' => 'Slug',
    'success_message' => 'Повідомлення про успіх',
    'notify_email' => 'Email для сповіщень',
    'is_active' => 'Активна',
    'main' => 'Основне',
    'fields_section' => 'Поля',
    'submissions_section' => 'Заявки',

    'key' => 'Ключ',
    'label' => 'Назва поля',
    'type' => 'Тип',
    'required' => 'Обовʼязкове',
    'options' => 'Варіанти (по одному в рядку)',

    'submission' => [
        'mail_subject' => 'Нова заявка — :title',
        'mail_greeting' => 'Нова заявка з форми ":title"',
        'default_success' => 'Дякуємо — вашу заявку отримано.',
        'submit_label' => 'Надіслати',
    ],

    'docs' => [
        'title' => 'Форм-білдер',
        'subtitle' => 'Створюйте публічні форми, вставляйте їх через @nexusForm(), переглядайте заявки.',
        'usage_title' => 'Вставка форми',
        'usage_desc' => 'Створіть форму, додайте поля рядками в репітер "Поля", потім вставте її в будь-який Blade-вигляд через @nexusForm(\'your-slug\'). Заявки потрапляють у модуль "Заявки з форм", а якщо вказано email — і в поштову скриньку.',
        'tech_title' => 'Технічні деталі',
        'admin_part' => 'Адмін-частина',
        'api_part' => 'Публічна частина',
        'endpoints' => 'Ендпоінти',
        'fields' => 'Поля',
    ],
];
