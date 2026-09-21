<?php

namespace Database\Seeders;

use App\Nexus\Modules\Page\Models\Page;
use App\Nexus\Modules\Page\Models\PageBlock;
use Illuminate\Database\Seeder;

/**
 * The storefront header/footer link to /about, /contacts and /delivery
 * unconditionally (Storefront::layouts.storefront) — those routes just
 * redirect to /pages/{slug} (see Page's routes/web.php), so without a
 * matching Page row every one of those links 404s. Nothing seeded them.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $about = Page::query()->updateOrCreate(
            ['slug' => 'about'],
            ['title' => 'Про нас', 'is_active' => true],
        );
        $this->syncBlocks($about, [
            [
                'type' => 'text',
                'data' => [
                    'heading' => 'Nexus Store',
                    'body' => "Nexus Store — маркетплейс готових модулів, плагінів і віджетів для адмін-панелей на платформі Nexus. Кожен модуль описує себе через PHP-атрибути прямо в моделі — без конфіг-файлів і шаблонного коду — а система плагінів дозволяє розширювати чужі модулі, не змінюючи їхній код.\n\nМи збираємо готові рішення для типових задач — каталог, оплати, доставка, лояльність, відгуки — щоб не писати їх з нуля під кожен проєкт на Nexus.",
                ],
            ],
        ]);

        $contacts = Page::query()->updateOrCreate(
            ['slug' => 'contacts'],
            ['title' => 'Контакти', 'is_active' => true],
        );
        $this->syncBlocks($contacts, [
            [
                'type' => 'contact_info',
                'data' => [
                    'address' => 'Україна (віддалена команда)',
                    'phone' => '',
                    'email' => 'support@nexus-store.dev',
                    'hours' => 'Пн–Пт, 10:00–18:00',
                    'map_label' => '',
                ],
            ],
        ]);

        $delivery = Page::query()->updateOrCreate(
            ['slug' => 'delivery'],
            ['title' => 'Ліцензування та оновлення', 'is_active' => true],
        );
        $this->syncBlocks($delivery, [
            [
                'type' => 'text',
                'data' => [
                    'heading' => 'Як працює ліцензія',
                    'body' => "Кожна покупка — це довічна ліцензія на 1 проєкт для конкретного модуля чи паку. Одразу після оплати архів модуля стає доступним для завантаження у вашому кабінеті (\"Мої ліцензії\") — встановлення не відрізняється від звичайного модуля Nexus.\n\nПак у кошику та в замовленні розкладається на реальні модулі, з яких він складається, — кожен встановлюється й оновлюється окремо.",
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'heading' => 'Оновлення та підтримка',
                    'body' => 'Усі майбутні оновлення ядра й функціоналу вже куплених модулів безкоштовні та доступні у вашому кабінеті без повторної оплати. Питання з встановленням чи роботою модуля — на support@nexus-store.dev.',
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
