<?php

namespace Database\Seeders;

use App\Nexus\Modules\Page\Models\Page;
use App\Nexus\Modules\Page\Models\PageBlock;
use Illuminate\Database\Seeder;

/**
 * The storefront header/footer link to /about, /contacts, /delivery and
 * /nodex unconditionally (Storefront::layouts.storefront) — those routes
 * just redirect to /pages/{slug} (see Page's routes/web.php), so without a
 * matching Page row every one of those links 404s. Nothing seeded them.
 *
 * Page::title stays a single plain string (not translatable — a deliberate
 * scope decision, see [[no-archive-mirroring]]'s sibling project memory on
 * the Nexus Store build). Page::meta_title/meta_description and every
 * translatable block field (BlockFieldDefinition::$translatable, e.g.
 * text/hero/advantages) are Spatie-translatable json columns — their value
 * here is a locale-keyed array (['uk' => ..., 'en' => ...]), matching
 * exactly what the admin block editor itself saves, not a plain string.
 * updateOrCreate()/syncBlocks() are idempotent, so re-running this seeder
 * never regresses an already-translated row back down to a single locale.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $about = Page::query()->updateOrCreate(
            ['slug' => 'about'],
            [
                'title' => 'Про нас',
                'is_active' => true,
                // page.blade.php's @section('title', ...) already appends ' — Nexus Store'.
                'meta_title' => [
                    'uk' => 'Про нас',
                    'en' => 'About us',
                ],
                'meta_description' => [
                    'uk' => 'Nexus Store — маркетплейс готових модулів, плагінів і віджетів для адмін-панелей на платформі Nexus.',
                    'en' => 'Nexus Store is a marketplace for ready-made modules, plugins and widgets for admin panels on the Nexus platform.',
                ],
            ],
        );
        $this->syncBlocks($about, [
            [
                'type' => 'text',
                'data' => [
                    'heading' => ['uk' => 'Nexus Store', 'en' => 'Nexus Store'],
                    'body' => [
                        'uk' => "Nexus Store — маркетплейс готових модулів, плагінів і віджетів для адмін-панелей на платформі Nexus. Кожен модуль описує себе через PHP-атрибути прямо в моделі — без конфіг-файлів і шаблонного коду — а система плагінів дозволяє розширювати чужі модулі, не змінюючи їхній код.\n\nМи збираємо готові рішення для типових задач — каталог, оплати, доставка, лояльність, відгуки — щоб не писати їх з нуля під кожен проєкт на Nexus.",
                        'en' => "Nexus Store is a marketplace for ready-made modules, plugins, and widgets designed for admin panels on the Nexus platform. Each module defines itself using PHP attributes directly within the model—eliminating the need for configuration files or boilerplate code—while the plugin system allows you to extend existing modules without modifying their source code.\n\nWe curate ready-made solutions for common tasks—such as catalogs, payments, shipping, loyalty programs, and reviews—so we don't have to build them from scratch for every Nexus project.",
                    ],
                ],
            ],
        ]);

        $contacts = Page::query()->updateOrCreate(
            ['slug' => 'contacts'],
            [
                'title' => 'Контакти',
                'is_active' => true,
                'meta_title' => [
                    'uk' => 'Контакти',
                    'en' => 'Contacts',
                ],
                'meta_description' => [
                    'uk' => 'Зв\'яжіться з командою Nexus Store: підтримка з питань встановлення, оновлень і ліцензій.',
                    'en' => 'Get in touch with the Nexus Store team: support for installation, updates and license questions.',
                ],
            ],
        );
        $this->syncBlocks($contacts, [
            [
                'type' => 'contact_info',
                'data' => [
                    'address' => ['uk' => 'Україна (віддалена команда)', 'en' => 'Ukraine (remote team)'],
                    'phone' => '',
                    'email' => 'support@nexus-cms.shop',
                    'hours' => ['uk' => 'Пн–Пт, 10:00–18:00', 'en' => 'Mon–Fri, 10:00–18:00'],
                    'map_label' => '',
                ],
            ],
        ]);

        $delivery = Page::query()->updateOrCreate(
            ['slug' => 'delivery'],
            [
                'title' => 'Ліцензування та оновлення',
                'is_active' => true,
                'meta_title' => [
                    'uk' => 'Ліцензування та оновлення',
                    'en' => 'Licensing and updates',
                ],
                'meta_description' => [
                    'uk' => 'Як працює ліцензія на модулі Nexus, встановлення, оновлення та підтримка після покупки.',
                    'en' => 'How Nexus module licensing works — installation, updates and support after purchase.',
                ],
            ],
        );
        $this->syncBlocks($delivery, [
            [
                'type' => 'text',
                'data' => [
                    'heading' => ['uk' => 'Як працює ліцензія', 'en' => 'How the license works'],
                    'body' => [
                        'uk' => "Кожна покупка — це довічна ліцензія на 1 проєкт для конкретного модуля чи паку. Одразу після оплати архів модуля стає доступним для завантаження у вашому кабінеті (\"Мої ліцензії\") — встановлення не відрізняється від звичайного модуля Nexus.\n\nПак у кошику та в замовленні розкладається на реальні модулі, з яких він складається, — кожен встановлюється й оновлюється окремо.",
                        'en' => "Every purchase is a lifetime license for 1 project for that specific module or pack. Right after payment the module's archive becomes available for download in your account (\"My licenses\") — installation is no different from any regular Nexus module.\n\nA pack in the cart and order is broken down into the real modules it consists of — each one is installed and updated separately.",
                    ],
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'heading' => ['uk' => 'Оновлення та підтримка', 'en' => 'Updates and support'],
                    'body' => [
                        'uk' => "Усі майбутні оновлення ядра й функціоналу вже куплених модулів безкоштовні та доступні у вашому кабінеті без повторної оплати. Питання з встановленням чи роботою модуля — на support@nexus-cms.shop.\n\nПри купівлі модуля під певну версію всі його подальші оновлення й виправлення багів залишаються з вами назавжди — ви можете використовувати їх і в наступних своїх проєктах, без додаткової оплати.",
                        'en' => "All future core and feature updates for modules you already own are free and available in your account with no repeat payment. Questions about installing or running a module — reach us at support@nexus-cms.shop.\n\nWhen you buy a module for a specific version, all its future updates and bug fixes stay yours for good — you can reuse them in your next projects too, at no extra cost.",
                    ],
                ],
            ],
        ]);

        $nodex = Page::query()->updateOrCreate(
            ['slug' => 'nodex'],
            [
                'title' => 'Про Nodex',
                'is_active' => true,
                'meta_title' => [
                    'uk' => 'Що таке Nodex',
                    'en' => 'What is Nodex',
                ],
                'meta_description' => [
                    'uk' => 'Nodex — Laravel-фреймворк, де один модуль одразу дає адмінку, фронтенд і стандартизоване API, з атрибутами замість конфігів і плагінами без форку.',
                    'en' => 'Nodex is a Laravel framework where one module gives you the admin, the frontend and a standardized API at once, with attributes instead of config files and forkless plugins.',
                ],
            ],
        );
        $this->syncBlocks($nodex, [
            [
                'type' => 'hero',
                'data' => [
                    'headline' => [
                        'uk' => 'Nodex — адмінка, фронтенд і API з одного модуля на Laravel',
                        'en' => 'Nodex — admin, frontend and API from one Laravel module',
                    ],
                    'subheadline' => [
                        'uk' => 'Модуль — це не лише екран в адмінці. Він одразу отримує CRUD-форму й таблицю, реальний публічний URL для фронтенду і стандартизоване REST/GraphQL API — і кожну з цих трьох частин можна перевизначити чи доповнити окремо. Цей магазин, який ви зараз бачите, повністю побудований на Nodex.',
                        'en' => "A module isn't just an admin screen. It immediately gets a CRUD form and table, a real public URL for the frontend, and a standardized REST/GraphQL API — and each of these three pieces can be overridden or extended independently. This very store you're browsing is built entirely on Nodex.",
                    ],
                    'cta_label' => ['uk' => 'Переглянути каталог модулів', 'en' => 'Browse the module catalog'],
                    'cta_url' => '/catalog',
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'heading' => ['uk' => 'Архітектура', 'en' => 'Architecture'],
                    'body' => [
                        'uk' => 'Кожен модуль Nodex — це звичайна Eloquent-модель з PHP 8 атрибутами #[Module], #[Field], #[Section], які описують форму, таблицю й валідацію без окремих конфіг-файлів. Але модуль — це не лише адмінка: поле з apiExpose: true одразу потрапляє у стандартизоване API, а власний URL-резолвер модуля (#[Module(menuResolver:)]) дозволяє запису одразу зʼявитися в меню сайту й посилатися на реальну публічну сторінку. Livewire забезпечує реактивний адмін-інтерфейс без окремого фронтенд-стеку.',
                        'en' => "Every Nodex module is a plain Eloquent model with PHP 8 attributes — #[Module], #[Field], #[Section] — that describe its form, table and validation without separate config files. But a module isn't just an admin screen: a field marked apiExpose: true is immediately exposed through a standardized API, and a module's own URL resolver (#[Module(menuResolver:)]) lets a record show up in the site's menu right away and link to a real public page. Livewire powers a reactive admin interface with no separate frontend stack.",
                    ],
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'heading' => ['uk' => 'Адмінка, фронтенд і API — з одного оголошення', 'en' => 'Admin, frontend and API — from one declaration'],
                    'body' => [
                        'uk' => 'Оголосили модуль — і одразу маєте три речі, не одну: CRUD-екран в адмінці, робочий публічний URL для сайту (через резолвер модуля записи одразу можна лінкувати в меню, related-полях і сайтмапі) і стандартизоване API (REST і GraphQL) без жодного рядка Resource-класу. Кожен з цих трьох шарів незалежний і легко перевизначається чи доповнюється: власний Resource-клас підмінить згенероване API, власний Blade-шаблон підмінить публічну сторінку, а плагін додасть поле чи фільтр у чужий модуль, не змінюючи жодного його файлу.',
                        'en' => "Declare a module and you get three things at once, not one: a CRUD screen in the admin, a working public URL for the site (through the module's resolver, records can immediately be linked from menus, related-entity fields and the sitemap), and a standardized API (REST and GraphQL) without writing a single Resource class. Each of these three layers is independent and easy to override or extend: a custom Resource class replaces the generated API, a custom Blade view replaces the public page, and a plugin adds a field or filter to someone else's module without changing a single file there.",
                    ],
                ],
            ],
            [
                'type' => 'advantages',
                'data' => [
                    'heading' => ['uk' => 'Ключові переваги', 'en' => 'Key advantages'],
                    'item1_title' => ['uk' => 'Атрибути замість конфігів', 'en' => 'Attributes, not config files'],
                    'item1_text' => ['uk' => 'Модуль описує поля, форми й таблиці прямо в моделі', 'en' => 'The module describes its fields, forms and tables right in the model'],
                    'item2_title' => ['uk' => 'Модульна архітектура', 'en' => 'Modular architecture'],
                    'item2_text' => ['uk' => 'Встановлюйте лише те, що потрібно проєкту', 'en' => 'Install only what the project actually needs'],
                    'item3_title' => ['uk' => 'Livewire під капотом', 'en' => 'Livewire under the hood'],
                    'item3_text' => ['uk' => 'Реактивний інтерфейс без окремого фронтенд-стеку', 'en' => 'A reactive interface without a separate frontend stack'],
                    'item4_title' => ['uk' => 'Плагіни без форку', 'en' => 'Plugins without forking'],
                    'item4_text' => ['uk' => 'Розширюйте чужі модулі, не змінюючи їхній код', 'en' => "Extend someone else's module without touching its code"],
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'heading' => ['uk' => 'Мультимовність', 'en' => 'Multi-language'],
                    'body' => [
                        'uk' => 'Nodex підтримує кілька мов з коробки: модуль Language визначає доступні мови, а перекладені поля — текст сторінок, назви категорій і товарів — автоматично показують по одному полю на кожну активну мову прямо в адмінці.',
                        'en' => 'Nodex supports multiple languages out of the box: the Language module defines the available languages, and translatable fields — page content, category and product names — automatically show one input per active language right in the admin.',
                    ],
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'heading' => ['uk' => 'Плагіни та хуки', 'en' => 'Plugins & hooks'],
                    'body' => [
                        'uk' => "Плагін — це клас, який розширює чужий модуль, не торкаючись його коду: додає поле чи колонку через #[AttachField]/#[AttachColumn], фільтр списку через #[AttachFilter], або підключається до життєвого циклу запису (створення, оновлення, видалення, валідація, імпорт/експорт, права доступу) через events-каталог і відповідні nexus_filter()/nexus_action() хуки. Наприклад, спеціалізований модуль Review може додати рейтинг і відгуки до модуля Product, який про це навіть не знає.\n\nТой самий принцип працює і для власного модуля: згенероване API, URL-резолвер чи тип поля можна будь-коли підмінити власною реалізацією, не переписуючи решту модуля.",
                        'en' => "A plugin is a class that extends someone else's module without touching its source: it adds a field or column via #[AttachField]/#[AttachColumn], a list filter via #[AttachFilter], or hooks into a record's lifecycle (create, update, delete, validation, import/export, permissions) through the events catalog and the matching nexus_filter()/nexus_action() hooks. A specialized Review module, for example, can add ratings and reviews onto a Product module that has no idea it exists.\n\nThe same principle applies to your own module too: the generated API, the URL resolver, or a field type can be swapped for your own implementation at any time, without rewriting the rest of the module.",
                    ],
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'heading' => ['uk' => 'Понад 40 типів полів', 'en' => '40+ field types'],
                    'body' => [
                        'uk' => "Від простих (рядок, число, перемикач, дата) до спеціалізованих: медіа (зображення/відео/галерея через Media Library), зв'язки (belongsTo/belongsToMany з пошуком чи попереднім завантаженням), повторювач рядків (repeater), редактор блоків сторінки, кольори, рейтинг, слаг з автогенерацією, JSON-перегляд, вбудований Blade-вью для повністю кастомного поля — і власний тип поля можна зареєструвати одним артизан-генератором, якщо готового бракує.",
                        'en' => 'From simple types (string, number, toggle, date) to specialized ones: media (image/video/gallery via Media Library), relations (belongsTo/belongsToMany with search or preload), row repeaters, the page block editor, color pickers, star ratings, auto-generated slugs, a read-only JSON viewer, and an escape hatch that injects an arbitrary Blade view for a fully custom field — and you can register your own field type with a single artisan generator if nothing built-in fits.',
                    ],
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'heading' => ['uk' => 'Візуальний редактор сторінок', 'en' => 'Visual page builder'],
                    'body' => [
                        'uk' => 'Модуль Page (як і будь-який інший модуль з полем типу blockEditor) редагується блоками — Hero, Текст, Зображення, CTA, Текст+зображення, Переваги, Галерея, Контакти, Вбудована форма — кожен зі своїм набором полів, перекладених мовами сайту. Ця сторінка, яку ви зараз читаєте, зібрана саме так, без жодного рядка HTML вручну.',
                        'en' => "The Page module (like any module with a blockEditor field) is edited in blocks — Hero, Text, Image, CTA, Text+Image, Advantages, Gallery, Contact info, Embedded form — each with its own field set, translated into the site's languages. This very page you're reading was assembled exactly this way, with no hand-written HTML.",
                    ],
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'heading' => ['uk' => 'Таблиці, фільтри й імпорт', 'en' => 'Tables, filters & import'],
                    'body' => [
                        'uk' => 'Список кожного модуля — реактивна Livewire-таблиця: пошук, сортування, фільтри (в тому числі власні, з окремим обробником), групові дії, збережені "лінзи" — попередньо відфільтровані вигляди списку, — а також експорт і опційний CSV-імпорт. Усе це декларується атрибутами на моделі, без окремого контролера чи Blade-шаблону таблиці.',
                        'en' => "Every module's list is a reactive Livewire table: search, sorting, filters (including custom ones with their own handler), bulk actions, saved \"lenses\" — pre-filtered alternate views of the list — plus export and optional CSV import. All of it is declared with attributes on the model, no separate controller or table Blade template needed.",
                    ],
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'heading' => ['uk' => 'Стандартизоване API з коробки', 'en' => 'A standardized API out of the box'],
                    'body' => [
                        'uk' => 'Щойно ви позначили поле apiExpose: true, воно автоматично зʼявляється у REST-ресурсі (NexusResource) і в GraphQL-схемі — однаковим, передбачуваним способом для будь-якого модуля, одразу після його встановлення, без ручного написання Resource-класу чи GraphQL-типу. Якщо стандартної форми відповіді недостатньо — досить створити власний клас {Module}Resource у своєму модулі, і Nexus підхопить саме його замість згенерованого.',
                        'en' => "The moment you mark a field apiExpose: true, it automatically appears in the REST resource (NexusResource) and in the GraphQL schema — the same predictable way for every module, right after it's installed, with no Resource class or GraphQL type to hand-write. If the default response shape isn't enough, just add your own {Module}Resource class in the module, and Nexus will use it instead of the generated one.",
                    ],
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'heading' => ['uk' => 'Дашборд-віджети', 'en' => 'Dashboard widgets'],
                    'body' => [
                        'uk' => 'Будь-який модуль може додати картку метрики на дашборд адмінки через атрибут #[Widget] — число, графік чи довільний рендер.',
                        'en' => 'Any module can add a metric card to the admin dashboard via the #[Widget] attribute — a number, a chart, or a fully custom render.',
                    ],
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'heading' => ['uk' => 'Інструменти розробника', 'en' => 'Developer tooling'],
                    'body' => [
                        'uk' => 'nexus:make:module створює модуль з моделлю, формами валідації, міграцією і мовними файлами за секунди; nexus:make:field і nexus:make:filter генерують власний тип поля чи обробник фільтра; nexus:module:install/clear/cache керують встановленням і кешем маніфесту модулів. Кожен модуль, плагін чи віджет — це просто PHP-клас у app/Nexus/{Modules,Plugins,Widgets} — переносити чи ділитися ним так само просто, як скопіювати папку.',
                        'en' => 'nexus:make:module scaffolds a module with its model, validation requests, migration and language files in seconds; nexus:make:field and nexus:make:filter generate a custom field type or filter handler; nexus:module:install/clear/cache manage installation and the module-manifest cache. Every module, plugin or widget is just a PHP class under app/Nexus/{Modules,Plugins,Widgets} — moving or sharing one is as simple as copying a folder.',
                    ],
                ],
            ],
            [
                'type' => 'cta',
                'data' => [
                    'heading' => ['uk' => 'Порівняйте Nexus з Nova, Filament та іншими', 'en' => 'See how Nexus compares to Nova, Filament and others'],
                    'button_label' => ['uk' => 'Порівняння адмін-панелей', 'en' => 'Compare admin panels'],
                    'button_url' => '/comparison',
                ],
            ],
        ]);

        $comparison = Page::query()->updateOrCreate(
            ['slug' => 'comparison'],
            [
                'title' => 'Порівняння з Nova, Filament та іншими',
                'is_active' => true,
                'meta_title' => [
                    'uk' => 'Nexus vs Laravel Nova, Filament та інші CMS',
                    'en' => 'Nexus vs Laravel Nova, Filament and other CMS',
                ],
                'meta_description' => [
                    'uk' => 'Чим Nexus відрізняється від Laravel Nova, Filament, October CMS, Statamic та WinterCMS — адмінка, фронтенд, API, ціна, розширюваність.',
                    'en' => 'How Nexus differs from Laravel Nova, Filament, October CMS, Statamic and WinterCMS — admin, frontend, API, pricing, extensibility.',
                ],
            ],
        );
        $this->syncBlocks($comparison, [
            [
                'type' => 'hero',
                'data' => [
                    'headline' => [
                        'uk' => 'Nexus проти Nova, Filament та інших Laravel CMS',
                        'en' => 'Nexus vs Nova, Filament and other Laravel CMS',
                    ],
                    'subheadline' => [
                        'uk' => 'Чесне порівняння підходів до побудови на Laravel адмінки, фронтенду й API для довільних сутностей — щоб ви обрали правильний інструмент під свій проєкт, а не лише той, про який найбільше чули.',
                        'en' => "An honest comparison of approaches to building the admin, the frontend and the API for arbitrary entities on Laravel — so you pick the right tool for your project, not just the one you've heard of most.",
                    ],
                    'cta_label' => ['uk' => 'Переглянути каталог модулів Nexus', 'en' => "Browse Nexus's module catalog"],
                    'cta_url' => '/catalog',
                ],
            ],
            [
                'type' => 'comparison_table',
                'data' => [
                    'heading' => ['uk' => 'Коротко: усе поруч', 'en' => 'At a glance'],
                    'own_column_label' => ['uk' => 'Nexus', 'en' => 'Nexus'],
                    'column2_label' => ['uk' => 'Laravel Nova', 'en' => 'Laravel Nova'],
                    'column3_label' => ['uk' => 'Filament', 'en' => 'Filament'],
                    'column4_label' => ['uk' => 'October/Winter/Statamic', 'en' => 'October/Winter/Statamic'],

                    'row1_feature' => ['uk' => 'Ціна', 'en' => 'Pricing'],
                    'row1_own' => ['uk' => 'Модуль окремо, довічно', 'en' => 'Per module, for life'],
                    'row1_col2' => ['uk' => 'Ліцензія за проєкт', 'en' => 'Per-project license'],
                    'row1_col3' => ['uk' => 'Ядро безкоштовне, плагіни платні', 'en' => 'Free core, paid plugins'],
                    'row1_col4' => ['uk' => 'Безкоштовні (open source)', 'en' => 'Free (open source)'],

                    'row2_feature' => ['uk' => 'Конфігурація', 'en' => 'Configuration'],
                    'row2_own' => ['uk' => 'PHP-атрибути на моделі', 'en' => 'PHP attributes on the model'],
                    'row2_col2' => ['uk' => 'Клас-ресурс (fluent-білдер)', 'en' => 'Resource class (fluent builder)'],
                    'row2_col3' => ['uk' => 'Клас-ресурс (fluent-білдер)', 'en' => 'Resource class (fluent builder)'],
                    'row2_col4' => ['uk' => 'YAML + PHP / плоскі файли', 'en' => 'YAML + PHP / flat files'],

                    'row3_feature' => ['uk' => 'Фронтенд', 'en' => 'Frontend'],
                    'row3_own' => ['uk' => 'Вбудований, автоматичний URL', 'en' => 'Built in, automatic URL'],
                    'row3_col2' => ['uk' => 'Не надається', 'en' => 'Not provided'],
                    'row3_col3' => ['uk' => 'Не надається', 'en' => 'Not provided'],
                    'row3_col4' => ['uk' => 'Вбудований (це CMS)', 'en' => "Built in (it's a CMS)"],

                    'row4_feature' => ['uk' => 'Стандартизоване API', 'en' => 'Standardized API'],
                    'row4_own' => ['uk' => 'Автоматично (REST+GraphQL)', 'en' => 'Automatic (REST+GraphQL)'],
                    'row4_col2' => ['uk' => 'Вручну', 'en' => 'Manual'],
                    'row4_col3' => ['uk' => 'Вручну', 'en' => 'Manual'],
                    'row4_col4' => ['uk' => 'Вручну', 'en' => 'Manual'],

                    'row5_feature' => ['uk' => 'Мультимовність', 'en' => 'Multi-language'],
                    'row5_own' => ['uk' => 'Вбудована', 'en' => 'Built in'],
                    'row5_col2' => ['uk' => 'Через плагіни', 'en' => 'Via plugins'],
                    'row5_col3' => ['uk' => 'Через плагіни', 'en' => 'Via plugins'],
                    'row5_col4' => ['uk' => 'Частково вбудована', 'en' => 'Partially built in'],

                    'row6_feature' => ['uk' => 'Розширення чужого модуля', 'en' => "Extending someone else's module"],
                    'row6_own' => ['uk' => 'Без форку (плагіни)', 'en' => 'Without forking (plugins)'],
                    'row6_col2' => ['uk' => 'Форк або композиція', 'en' => 'Fork or composition'],
                    'row6_col3' => ['uk' => 'Форк або композиція', 'en' => 'Fork or composition'],
                    'row6_col4' => ['uk' => 'Форк', 'en' => 'Fork'],
                ],
            ],
            [
                'type' => 'image',
                'data' => [
                    'image' => 'images/screenshots/nodex-admin-block-editor.png',
                    'caption' => [
                        'uk' => 'Реальний скріншот адмінки Nexus: редактор блоків сторінки з окремим полем на кожну активну мову (UK/EN) для Hero-блоку.',
                        'en' => 'A real screenshot of the Nexus admin: the page block editor with a separate field per active language (UK/EN) for the Hero block.',
                    ],
                ],
            ],
            [
                'type' => 'image',
                'data' => [
                    'image' => 'images/screenshots/nodex-admin-seo-fields.png',
                    'caption' => [
                        'uk' => 'Той самий принцип для SEO-полів: Meta title і Meta description автоматично отримують по одному полю на кожну активну мову.',
                        'en' => 'The same principle for SEO fields: Meta title and Meta description automatically get one field per active language.',
                    ],
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'heading' => ['uk' => 'Nexus проти Laravel Nova', 'en' => 'Nexus vs Laravel Nova'],
                    'body' => [
                        'uk' => "Nova — офіційна перша сторона Laravel: платна ліцензія за проєкт, кожен ресурс — окремий PHP-клас у стилі fluent-білдера, фронтенд на Vue/Inertia. Це надійний, добре задокументований вибір, якщо команда вже інвестувала в екосистему Laravel/Vue.\n\nNexus іде іншим шляхом: модуль описує себе прямо в моделі через PHP-атрибути (#[Module], #[Field], #[Section]) — менше файлів на кожен тип контенту, немає окремого класу-ресурсу. Замість єдиної річної ліцензії на весь пакет — маркетплейс, де кожен модуль купується окремо, довічно, і встановлюється лише те, що реально потрібно проєкту. Мультимовні поля та візуальний редактор сторінок вбудовані одразу, без додаткових пакетів.\n\nІ ще одна суттєва відмінність: Nova — це виключно інструмент для адмін-екрана. Усе, що стосується публічного фронтенду й API, ви пишете самі, з нуля, окремо від Nova. Модуль Nexus одразу — це адмінка, реальний публічний URL для сайту і стандартизоване REST/GraphQL API одночасно, і кожну з цих частин можна перевизначити чи доповнити.",
                        'en' => "Nova is Laravel's official first-party option: a paid per-project license, each resource is its own PHP class in a fluent-builder style, Vue/Inertia frontend. It's a solid, well-documented choice if the team has already invested in the Laravel/Vue ecosystem.\n\nNexus takes a different path: a module describes itself right in the model through PHP attributes (#[Module], #[Field], #[Section]) — fewer files per content type, no separate resource class. Instead of one yearly license for the whole package, it's a marketplace where each module is bought separately, for life, and a project installs only what it actually needs. Translatable fields and a visual page builder are built in from the start, no extra packages required.\n\nAnd one more real difference: Nova is purely an admin-screen tool. Everything about the public frontend and API is something you build yourself, from scratch, entirely separate from Nova. A Nexus module is the admin, a real public URL for the site, and a standardized REST/GraphQL API — all at once — and any of those pieces can be overridden or extended.",
                    ],
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'heading' => ['uk' => 'Nexus проти Filament', 'en' => 'Nexus vs Filament'],
                    'body' => [
                        'uk' => "Filament теж працює на Livewire (як і Nexus) і має відкрите ядро з великою спільнотою плагінів — форми, таблиці й ресурси там конфігуруються через fluent PHP-білдер (Forms\\Components, Tables\\Columns) у методах класу-ресурсу.\n\nNexus замінює цей fluent-білдер атрибутами прямо на властивостях моделі — менше шаблонного коду для типової CRUD-сутності. Ключова відмінність не в UI-полірованні (в обох гарний адмін-інтерфейс), а в моделі поширення: Filament-плагіни здебільшого безкоштовні або платні окремо в екосистемі спільноти, тоді як Nexus — це цілісний маркетплейс готових модулів з довічним оновленням, а система плагінів дозволяє розширювати чужий модуль без форку так само, як атрибути описують свій.\n\nІ так само з фронтендом та API: Filament не бере їх на себе — це окремі пакети чи ручна робота поверх нього. У Nexus вони йдуть разом з модулем з першого дня, і кожен шар (адмінка, URL-резолвер, API-ресурс) можна замінити власною реалізацією.",
                        'en' => "Filament also runs on Livewire (like Nexus) and has an open core with a large plugin community — forms, tables and resources are configured through a fluent PHP builder (Forms\\Components, Tables\\Columns) inside resource-class methods.\n\nNexus replaces that fluent builder with attributes directly on model properties — less boilerplate for a typical CRUD entity. The real difference isn't UI polish (both have a solid admin interface) but the distribution model: Filament plugins are mostly free or separately paid across a community ecosystem, while Nexus is one cohesive marketplace of ready-made modules with lifetime updates, and its plugin system lets you extend someone else's module without forking, the same way attributes describe your own.\n\nSame story with the frontend and the API: Filament doesn't take those on either — they're separate packages or manual work on top of it. In Nexus they come with the module from day one, and every layer (admin, URL resolver, API resource) can be swapped for your own implementation.",
                    ],
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'heading' => ['uk' => 'Nexus проти October CMS, Statamic і WinterCMS', 'en' => 'Nexus vs October CMS, Statamic & WinterCMS'],
                    'body' => [
                        'uk' => "October CMS, WinterCMS (його форк-спадкоємець) і Statamic — це передусім CMS для контентних сайтів: сторінки, блоги, шаблонизація для маркетингових команд. October/Winter конфігурують компоненти через YAML поряд із PHP; Statamic робить ставку на плоскі файли й особливий фокус на контент-редакторах, не розробниках.\n\nNexus — не контентна CMS, а фреймворк для довільних сутностей: замовлення, ліцензія, товар чи сторінка — усе це модуль з однаковим набором можливостей — адмінка, публічний URL і API одночасно. Цей магазин, який ви зараз бачите — каталог, кошик, оплата, ліцензії — цілком побудований на модулях Nexus, а не на окремій e-commerce-надбудові. І на відміну від Nexus, жодна з цих CMS не генерує стандартизоване REST/GraphQL API автоматично для довільної сутності — це доводиться писати вручну поверх них.",
                        'en' => "October CMS, WinterCMS (its fork successor) and Statamic are primarily content-site CMSs: pages, blogs, templating for marketing teams. October/Winter configure components through YAML alongside PHP; Statamic bets on flat files with a strong focus on content editors rather than developers.\n\nNexus isn't a content CMS — it's a framework for arbitrary entities: an order, a license, a product or a page are all a module with the same set of capabilities — admin, a public URL and an API, all at once. This very store you're browsing — catalog, cart, checkout, licenses — is built entirely on Nexus modules, not a bolted-on e-commerce layer. And unlike Nexus, none of these CMSs automatically generates a standardized REST/GraphQL API for an arbitrary entity — that has to be hand-written on top of them.",
                    ],
                ],
            ],
            [
                'type' => 'advantages',
                'data' => [
                    'heading' => ['uk' => 'Коротко: чому Nexus', 'en' => 'In short: why Nexus'],
                    'item1_title' => ['uk' => 'Купив один раз — назавжди твій', 'en' => 'Buy once, own it forever'],
                    'item1_text' => ['uk' => 'Ліцензія на модуль, а не річна підписка на весь пакет', 'en' => 'A license per module, not a yearly subscription for the whole package'],
                    'item2_title' => ['uk' => 'Атрибути, а не Resource-класи', 'en' => 'Attributes, not resource classes'],
                    'item2_text' => ['uk' => 'Менше файлів і шаблонного коду на кожен тип контенту', 'en' => 'Fewer files and less boilerplate per content type'],
                    'item3_title' => ['uk' => 'Плагіни без форку', 'en' => 'Plugins without forking'],
                    'item3_text' => ['uk' => 'Розширюйте чужий модуль, не змінюючи й не форкаючи його', 'en' => "Extend someone else's module without changing or forking it"],
                    'item4_title' => ['uk' => 'Мультимовність і блоковий редактор — вбудовані', 'en' => 'Multi-language and a block editor — built in'],
                    'item4_text' => ['uk' => 'Без додаткових пакетів чи налаштувань', 'en' => 'No extra packages or setup required'],
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'heading' => ['uk' => 'Коли обрати не Nexus', 'en' => 'When Nexus is not the right choice'],
                    'body' => [
                        'uk' => 'Чесно: якщо команда вже глибоко в екосистемі Filament чи Nova й задоволена нею, міграція заради самої міграції рідко виправдана. Якщо потрібен саме контент-сайт із шаблонизацією для нетехнічних редакторів — придивіться до Statamic чи October/Winter. Nexus найкраще підходить, коли ви будуєте адмін-панель чи внутрішній інструмент із нуля й хочете купувати готові модулі поштучно, а не платити за весь набір одразу.',
                        'en' => "To be fair: if a team is already deep in the Filament or Nova ecosystem and happy with it, migrating just to migrate is rarely worth it. If you specifically need a content site with templating for non-technical editors, look at Statamic or October/Winter instead. Nexus fits best when you're building an admin panel or internal tool from scratch and want to buy ready-made modules one at a time rather than paying for the whole set upfront.",
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param  list<array{type: string, data: array<string, mixed>}>  $blocks
     */
    private function syncBlocks(Page $page, array $blocks): void
    {
        $page->blocks()->delete();

        foreach ($blocks as $position => $block) {
            PageBlock::create([
                'page_id' => $page->id,
                'type' => $block['type'],
                'data' => $block['data'],
                'position' => $position,
            ]);
        }
    }
}
