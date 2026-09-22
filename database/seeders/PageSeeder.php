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
                    'email' => 'support@nexus-store.dev',
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
                        'uk' => 'Усі майбутні оновлення ядра й функціоналу вже куплених модулів безкоштовні та доступні у вашому кабінеті без повторної оплати. Питання з встановленням чи роботою модуля — на support@nexus-store.dev.',
                        'en' => 'All future core and feature updates for modules you already own are free and available in your account with no repeat payment. Questions about installing or running a module — reach us at support@nexus-store.dev.',
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
                    'uk' => 'Nodex — фреймворк адмін-панелей на Laravel з атрибутами замість конфігів, плагінами без форку та Livewire під капотом.',
                    'en' => 'Nodex is a Laravel admin panel framework with attributes instead of config files, forkless plugins and Livewire under the hood.',
                ],
            ],
        );
        $this->syncBlocks($nodex, [
            [
                'type' => 'hero',
                'data' => [
                    'headline' => [
                        'uk' => 'Nodex — фреймворк адмін-панелей для Laravel',
                        'en' => 'Nodex — an admin panel framework for Laravel',
                    ],
                    'subheadline' => [
                        'uk' => 'Кожен модуль описує себе через PHP-атрибути прямо в моделі. Без конфіг-файлів, без шаблонного коду — з реактивним інтерфейсом на Livewire з коробки. Цей магазин, який ви зараз бачите, повністю побудований на Nodex.',
                        'en' => 'Every module describes itself with PHP attributes right in the model. No config files, no boilerplate — with a reactive Livewire interface out of the box. This very store is built entirely on Nodex.',
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
                        'uk' => "Кожен модуль Nodex — це звичайна Eloquent-модель з PHP 8 атрибутами #[Module], #[Field], #[Section], які описують форму, таблицю й валідацію без окремих конфіг-файлів. Система плагінів дозволяє розширювати чужий модуль — додавати поля, колонки, зв'язки чи дії — не торкаючись його коду. Livewire забезпечує реактивний адмін-інтерфейс без окремого фронтенд-стеку.",
                        'en' => "Every Nodex module is a plain Eloquent model with PHP 8 attributes — #[Module], #[Field], #[Section] — that describe its form, table and validation without separate config files. The plugin system lets you extend someone else's module — adding fields, columns, relations or actions — without touching its source. Livewire powers a reactive admin interface with no separate frontend stack.",
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
