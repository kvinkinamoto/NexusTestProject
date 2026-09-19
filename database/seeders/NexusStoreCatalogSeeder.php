<?php

namespace Database\Seeders;

use App\Nexus\Modules\ShopCategory\Models\ShopCategory;
use App\Nexus\Modules\ShopProduct\Enums\PackageType;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Database\Seeder;

/**
 * Populates the catalog from the module/plugin inventory collected in
 * C:\OSPanel1\home\nexus-modules-archive (modules/, plugins/, widgets/).
 * Paid packs, tiering (free vs paid) and price ranges follow
 * nexus-modules-archive/pricing-and-tiers.md; each price below is that
 * document's recommended range floor minus $10, converted to UAH at the
 * ~44.5 rate current on 2026-09-19 (LiqPay settles in UAH, matching the
 * storefront's existing "грн" display) and rounded to a retail ".99"/"49"
 * ending. Idempotent via updateOrCreate on slug — safe to re-run.
 */
class NexusStoreCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'ecommerce-modules' => 'E-commerce модулі',
            'payments-delivery' => 'Платежі та доставка',
            'marketing-analytics' => 'Маркетинг та аналітика',
            'social' => 'Соцмережі',
            'admin-tools' => 'Адміністрування',
            'library-vertical' => 'Бібліотечна вертикаль',
            'api-integrations' => 'API та інтеграції',
            'free-modules' => 'Безкоштовні модулі',
        ];

        $categoryIds = [];
        foreach ($categories as $slug => $name) {
            $categoryIds[$slug] = ShopCategory::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'publish' => true],
            )->id;
        }

        $products = [
            // --- Платні паки (Tier 3, pricing-and-tiers.md) ---
            [
                'slug' => 'ecommerce-core-pack',
                'name' => 'E-commerce Core Pack',
                'category' => 'ecommerce-modules',
                'package_type' => PackageType::MODULE,
                'price' => 6199,
                'short_description' => 'Повний набір для запуску інтернет-магазину: каталог, кошик, замовлення, мультивалюта.',
                'description' => 'Currency, ShopCategory, ShopAttribute, ShopProductType, ShopProduct, ShopProductVariation, Cart, Order, OrderStatus — разом з демо-темою вітрини Storefront. Глибший функціонал (варіації товару, мультивалюта), ніж у типових e-commerce-аддонів на кшталт October CMS "Shopaholic".',
            ],
            [
                'slug' => 'pricing-promotions-pack',
                'name' => 'Pricing & Promotions Pack',
                'category' => 'ecommerce-modules',
                'package_type' => PackageType::MODULE,
                'price' => 1749,
                'short_description' => 'Промокоди, правила знижок та акції для магазину.',
                'description' => 'PromoCode (+Usage), DiscountRule, Promotion (+Usage) — гнучкі правила ціноутворення й маркетингові акції. Аналог: WooCommerce Dynamic Pricing / Smart Coupons продаються за річною підпискою — тут одноразова покупка.',
            ],
            [
                'slug' => 'payments-ua-pack',
                'name' => 'Payments (UA) Pack',
                'category' => 'payments-delivery',
                'package_type' => PackageType::MODULE,
                'price' => 1749,
                'short_description' => 'Прийом оплат для українського ринку.',
                'description' => 'PaymentMethod з драйверами LiqPay, розстрочка Monobank та PrivatBank. Нішевий пак під український ринок платежів.',
            ],
            [
                'slug' => 'delivery-tracking-ua-pack',
                'name' => 'Delivery & Tracking (UA) Pack',
                'category' => 'payments-delivery',
                'package_type' => PackageType::MODULE,
                'price' => 1749,
                'short_description' => 'Доставка та трекінг для Нової пошти й Укрпошти.',
                'description' => 'Delivery, DeliveryType, Shipment з трекінгом Нової Пошти та Укрпошти. Аналог: платні розширення доставки WooCommerce ($79–149/рік за підпискою).',
            ],
            [
                'slug' => 'loyalty-pack',
                'name' => 'Loyalty Pack',
                'category' => 'ecommerce-modules',
                'package_type' => PackageType::MODULE,
                'price' => 1299,
                'short_description' => 'Бонусна програма лояльності для покупців.',
                'description' => 'Bonus, BonusTransaction — нарахування та списання бонусів. Аналог: WooCommerce Points & Rewards ($129/рік за підпискою).',
            ],
            [
                'slug' => 'reviews-qa-wishlist-pack',
                'name' => 'Reviews, Q&A & Wishlist Pack',
                'category' => 'ecommerce-modules',
                'package_type' => PackageType::MODULE,
                'price' => 1299,
                'short_description' => 'Відгуки, питання про товар та список бажань.',
                'description' => 'ProductQuestion, ProductReview, Wishlist. Аналог: WooCommerce Product Reviews Pro / Wishlists ($49–79/рік за підпискою).',
            ],
            [
                'slug' => 'ecommerce-mega-bundle',
                'name' => 'E-commerce Mega-Bundle',
                'category' => 'ecommerce-modules',
                'package_type' => PackageType::MODULE,
                'price' => 14999,
                'short_description' => 'Всі e-commerce паки разом зі знижкою проти покупки окремо.',
                'description' => "Об'єднує E-commerce Core, Pricing & Promotions, Loyalty та Reviews/Q&A/Wishlist Pack в одній покупці зі знижкою ~25–30% проти купівлі окремо.",
            ],
            [
                'slug' => 'social-network-pack',
                'name' => 'SocialNetwork Pack',
                'category' => 'social',
                'package_type' => PackageType::MODULE,
                'price' => 3099,
                'short_description' => 'Синхронізація публікацій у соцмережі.',
                'description' => 'Синхронізація постів Facebook/Instagram/TikTok/YouTube/Telegram + OAuth. Аналог: SaaS-агрегатори постів $10–30/міс — тут одноразова оплата вигідніша.',
            ],
            [
                'slug' => 'activity-log',
                'name' => 'ActivityLog',
                'category' => 'admin-tools',
                'package_type' => PackageType::MODULE,
                'price' => 849,
                'short_description' => 'Аудит-лог дій в адмін-панелі.',
                'description' => 'Повний журнал дій адміністраторів. Аналог: WP Activity Log Premium ($49–99/рік за підпискою).',
            ],
            [
                'slug' => 'backup-restore',
                'name' => 'Backup + Restore',
                'category' => 'admin-tools',
                'package_type' => PackageType::MODULE,
                'price' => 1299,
                'short_description' => 'Резервне копіювання та відновлення сайту.',
                'description' => 'Бекап БД, коду та сховища з можливістю відновлення. Аналог: UpdraftPlus Premium (~$70/рік за підпискою).',
            ],
            [
                'slug' => 'advanced-translations',
                'name' => 'Advanced Translations',
                'category' => 'admin-tools',
                'package_type' => PackageType::MODULE,
                'price' => 1749,
                'short_description' => 'Редактор перекладів контенту та інтерфейсу.',
                'description' => 'Language + Translations — адмін-редактор перекладів з груповим списком і відсотком завершеності. Аналог: WPML ($99/рік за підпискою).',
            ],
            [
                'slug' => 'library-vertical-pack',
                'name' => 'Book/Library Vertical Pack',
                'category' => 'library-vertical',
                'package_type' => PackageType::MODULE,
                'price' => 8399,
                'short_description' => 'Повний набір модулів для бібліотек і видавництв.',
                'description' => 'Author, Book, BookFile, BookImport, BookSearchIndex, BookSeries, BookTranslation, CopyrightStatus, Edition, ReaderAnnotation, ReadingActivityDay, ReadingDiary, ReadingProgress, TelegramAnnouncement, AiApiKey. Глибокий вертикальний пак — читалка, прогрес читання, AI-інтеграція.',
            ],
            [
                'slug' => 'graphql-api-plugin',
                'name' => 'GraphQL API Plugin',
                'category' => 'api-integrations',
                'package_type' => PackageType::PLUGIN,
                'price' => 2199,
                'short_description' => 'GraphQL API поверх адмін-панелі Nexus.',
                'description' => 'Плагін GraphQL — окремий фіч-гейт, суттєво дешевший за повний Statamic Pro ($259), який ховає REST/GraphQL за ціною всього пакету.',
            ],
            [
                'slug' => 'advertising-pack',
                'name' => 'Advertising Pack',
                'category' => 'marketing-analytics',
                'package_type' => PackageType::MODULE,
                'price' => 1299,
                'short_description' => 'Керування рекламою та трекінг показів.',
                'description' => 'Advertising — керування рекламними блоками й трекінг показів. Аналог: Advanced Ads Pro ($49–190/рік за підпискою).',
            ],

            // --- Безкоштовні модулі (Tier 1/2 + невизначені-раніше, з рекомендаціями документа) ---
            [
                'slug' => 'analytics',
                'name' => 'Analytics',
                'category' => 'marketing-analytics',
                'package_type' => PackageType::MODULE,
                'price' => 0,
                'short_description' => 'Базова аналітика — безкоштовно, як "wow"-фіча.',
                'description' => 'Базовий дашборд аналітики включено безкоштовно для залучення користувачів (як у Statamic). Розширені звіти/експорт можуть у майбутньому стати платним доповненням.',
            ],
            [
                'slug' => 'storefront-theme',
                'name' => 'Storefront (демо-тема)',
                'category' => 'free-modules',
                'package_type' => PackageType::MODULE,
                'price' => 0,
                'short_description' => 'Демо-тема вітрини — безкоштовний бонус до E-commerce Core.',
                'description' => 'Storefront не продається окремо — йде безкоштовним бонусом при покупці E-commerce Core Pack як стартова тема (як у October/Twill).',
            ],
            [
                'slug' => 'forms-module',
                'name' => 'Forms',
                'category' => 'free-modules',
                'package_type' => PackageType::MODULE,
                'price' => 0,
                'short_description' => 'Конструктор форм та збір заявок.',
                'description' => 'Form + FormSubmission — офіційний безкоштовний модуль, драйвер адопції фреймворка.',
            ],
            [
                'slug' => 'blog-module',
                'name' => 'Blog',
                'category' => 'free-modules',
                'package_type' => PackageType::MODULE,
                'price' => 0,
                'short_description' => 'Блог з категоріями публікацій.',
                'description' => 'BlogCategory + BlogPost — офіційний безкоштовний блог-модуль.',
            ],
            [
                'slug' => 'slider-module',
                'name' => 'Slider',
                'category' => 'free-modules',
                'package_type' => PackageType::MODULE,
                'price' => 0,
                'short_description' => 'Слайдер для головної сторінки.',
                'description' => 'Slider — офіційний безкоштовний модуль для банерів/слайдів на головній.',
            ],
            [
                'slug' => 'menu-module',
                'name' => 'Menu',
                'category' => 'free-modules',
                'package_type' => PackageType::MODULE,
                'price' => 0,
                'short_description' => 'Керування меню сайту.',
                'description' => 'Menu — офіційний безкоштовний модуль керування навігацією.',
            ],
            [
                'slug' => 'redirect-module',
                'name' => 'Redirect',
                'category' => 'free-modules',
                'package_type' => PackageType::MODULE,
                'price' => 0,
                'short_description' => '301-редиректи для SEO.',
                'description' => 'Redirect — офіційний безкоштовний модуль керування редиректами.',
            ],
            [
                'slug' => 'sitemap-module',
                'name' => 'Sitemap',
                'category' => 'free-modules',
                'package_type' => PackageType::MODULE,
                'price' => 0,
                'short_description' => 'Автогенерація sitemap.xml.',
                'description' => 'Sitemap — офіційний безкоштовний модуль автогенерації карти сайту.',
            ],
            [
                'slug' => 'demo-module',
                'name' => 'Demo',
                'category' => 'free-modules',
                'package_type' => PackageType::MODULE,
                'price' => 0,
                'short_description' => 'Навчальний приклад модуля для розробників.',
                'description' => 'Demo — навмисно безкоштовний зразковий модуль: демонструє, як побудувати свій модуль на Nexus (маркетинговий SEO/довіра-хід для розробників).',
            ],
            [
                'slug' => 'widgets-pack',
                'name' => 'Dashboard Widgets Pack',
                'category' => 'free-modules',
                'package_type' => PackageType::WIDGET,
                'price' => 0,
                'short_description' => 'Базові дашборд-віджети.',
                'description' => 'AdvertisingSlot, UsersCount, RecentDemoRecords та інші базові віджети для адмін-дашборду — входять безкоштовно.',
            ],
        ];

        foreach ($products as $data) {
            ShopProduct::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'short_description' => $data['short_description'],
                    'description' => $data['description'],
                    'category_id' => $categoryIds[$data['category']],
                    'package_type' => $data['package_type'],
                    'min_core_version' => '1.0',
                    'price' => $data['price'],
                    'publish' => true,
                ],
            );
        }
    }
}
