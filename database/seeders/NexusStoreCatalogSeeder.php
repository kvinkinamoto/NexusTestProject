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
                'description' => 'Це той момент, коли одного каталогу товарів вже не вистачає, а писати кошик, оформлення замовлення і мультивалютність з нуля — тижні роботи. Пак ставить повноцінний e-commerce-двигун поверх Nexus: категорії з довільною глибиною вкладеності, атрибути й варіації товару (розмір, колір, комплектація — що завгодно), кошик, який пам\'ятає гостя за сесією, і замовлення з власною воронкою статусів. Мультивалютність вбудована одразу, а не докручена збоку — ціни перераховуються автоматично за курсом, який ви задаєте в адмінці. У комплекті йде демо-тема вітрини Storefront, тож після встановлення у вас вже є робочий магазин, а не порожня адмінка. Порівняно з готовими збірками на кшталт October CMS «Shopaholic» тут суттєво глибша варіативність товару — різниця, яку відчуваєш, коли продаєш не футболки одного кольору, а щось із реальними комбінаціями опцій.',
            ],
            [
                'slug' => 'pricing-promotions-pack',
                'name' => 'Pricing & Promotions Pack',
                'category' => 'ecommerce-modules',
                'package_type' => PackageType::MODULE,
                'price' => 1749,
                'short_description' => 'Промокоди, правила знижок та акції для магазину.',
                'description' => 'Знижка «-15% до кінця тижня», промокод на перше замовлення, «3 за ціною 2» — рано чи пізно це просить будь-який магазин, і щоразу доводиться городити костиль прямо в коді продукту. Цей пак виносить усю логіку ціноутворення в окремий шар: правила знижок, що спрацьовують за умовами (сума кошика, категорія, кількість), промокоди з лімітом використань і акції з власною історією застосувань. Усе це рахується автоматично при оформленні замовлення, без ручного перерахунку в голові. Аналогічний функціонал у WooCommerce (Dynamic Pricing, Smart Coupons) продають за річною підпискою — тут це одноразова покупка, яка працює, поки працює ваш сайт.',
            ],
            [
                'slug' => 'payments-ua-pack',
                'name' => 'Payments (UA) Pack',
                'category' => 'payments-delivery',
                'package_type' => PackageType::MODULE,
                'price' => 1749,
                'short_description' => 'Прийом оплат для українського ринку.',
                'description' => 'Український покупець хоче платити карткою через LiqPay або в розстрочку від Monobank чи PrivatBank — і якщо цього немає на чекауті, він просто закриває вкладку. Пак підключає готові драйвери під ці способи оплати до вже наявного модуля PaymentMethod: жодних окремих інтеграцій писати не треба, достатньо вписати ключі API в адмінці. Вузько заточений під український ринок пак — саме тому й компактніший за ціною порівняно з e-commerce-паками ширшого призначення.',
            ],
            [
                'slug' => 'delivery-tracking-ua-pack',
                'name' => 'Delivery & Tracking (UA) Pack',
                'category' => 'payments-delivery',
                'package_type' => PackageType::MODULE,
                'price' => 1749,
                'short_description' => 'Доставка та трекінг для Нової пошти й Укрпошти.',
                'description' => 'Доставка — це не тільки поле «вписати адресу», а ще й статуси відправлення і трекінг у реальному часі. Пак додає модулі Delivery, DeliveryType і Shipment з готовою інтеграцією трекінгу Нової Пошти та Укрпошти: покупець бачить статус свого замовлення сам, не питаючи в підтримки «а де моя посилка». Для порівняння — подібні розширення доставки для WooCommerce коштують $79–149 на рік за підпискою; тут це одна покупка назавжди.',
            ],
            [
                'slug' => 'loyalty-pack',
                'name' => 'Loyalty Pack',
                'category' => 'ecommerce-modules',
                'package_type' => PackageType::MODULE,
                'price' => 1299,
                'short_description' => 'Бонусна програма лояльності для покупців.',
                'description' => 'Постійний покупець, який повертається за бонусами, обходиться дешевше за нового, залученого рекламою — але реалізувати бонусну систему самостійно означає вручну займатися нарахуваннями, списаннями й історією транзакцій. Bonus і BonusTransaction роблять це за вас: бали нараховуються після оплати замовлення, списуються при наступній покупці, і кожна операція лишає слід в історії, яку бачить і покупець, і адміністратор. WooCommerce Points & Rewards просить за подібне $129 щороку — тут це разова інвестиція.',
            ],
            [
                'slug' => 'reviews-qa-wishlist-pack',
                'name' => 'Reviews, Q&A & Wishlist Pack',
                'category' => 'ecommerce-modules',
                'package_type' => PackageType::MODULE,
                'price' => 1299,
                'short_description' => 'Відгуки, питання про товар та список бажань.',
                'description' => 'Три речі, які найбільше підвищують довіру до картки товару — це відгуки з реальними оцінками, можливість запитати щось перед покупкою і список бажань, куди можна відкласти товар «на потім». Пак додає всі три: ProductReview для відгуків з рейтингом, ProductQuestion для запитань-відповідей прямо на сторінці товару, і Wishlist, який працює і для зареєстрованих покупців, і для гостей за сесією. У WooCommerce ці три речі часто продають окремими платними розширеннями — тут вони в одному паці за ціною одного з них.',
            ],
            [
                'slug' => 'ecommerce-mega-bundle',
                'name' => 'E-commerce Mega-Bundle',
                'category' => 'ecommerce-modules',
                'package_type' => PackageType::MODULE,
                'price' => 14999,
                'short_description' => 'Всі e-commerce паки разом зі знижкою проти покупки окремо.',
                'description' => 'Якщо ви вже знаєте, що вам потрібен повний e-commerce — каталог, ціноутворення, бонуси, відгуки — і не хочете рахувати, скільки виходить купувати паки поштучно, ось відповідь: цей бандл об\'єднує E-commerce Core, Pricing & Promotions, Loyalty та Reviews/Q&A/Wishlist Pack в одній покупці зі знижкою приблизно 25–30% проти суми окремих цін. Це стандартна економіка маркетплейсів на кшталт October чи CodeCanyon — і найпростіший спосіб нічого не пропустити, докуповуючи паки по одному місяцями.',
            ],
            [
                'slug' => 'social-network-pack',
                'name' => 'SocialNetwork Pack',
                'category' => 'social',
                'package_type' => PackageType::MODULE,
                'price' => 3099,
                'short_description' => 'Синхронізація публікацій у соцмережі.',
                'description' => 'Публікувати кожен пост вручну на Facebook, Instagram, TikTok, YouTube і Telegram — та рутина, яку хочеться делегувати комусь іншому. Модуль SocialNetwork підключається через OAuth до кожної платформи і синхронізує публікації прямо з адмінки, без перемикання між десятком вкладок. SaaS-агрегатори на кшталт Buffer чи Later коштують $10–30 на місяць — виходить $120–360 щороку, знову й знову; тут разова оплата, яка окупається вже на другому-третьому місяці використання.',
            ],
            [
                'slug' => 'activity-log',
                'name' => 'ActivityLog',
                'category' => 'admin-tools',
                'package_type' => PackageType::MODULE,
                'price' => 849,
                'short_description' => 'Аудит-лог дій в адмін-панелі.',
                'description' => 'Хтось видалив товар з каталогу, і ніхто не пам\'ятає хто — знайома ситуація для будь-якої команди з кількома адміністраторами. ActivityLog веде повний журнал дій в адмін-панелі: хто, що і коли змінив, з можливістю подивитись деталі кожної дії. Просто, недорого, і рятує години розбирательств. WP Activity Log Premium бере за подібне $49–99 щороку — тут ціна одноразова й суттєво нижча.',
            ],
            [
                'slug' => 'backup-restore',
                'name' => 'Backup + Restore',
                'category' => 'admin-tools',
                'package_type' => PackageType::MODULE,
                'price' => 1299,
                'short_description' => 'Резервне копіювання та відновлення сайту.',
                'description' => 'Резервна копія, зроблена за день до того, як щось зламалось, коштує дорожче за будь-яку підписку на бекап-сервіс. Модуль архівує базу даних, код і файлове сховище за розкладом або вручну, і дозволяє відновити все одним кліком з адмінки, без доступу до сервера через SSH. UpdraftPlus Premium, популярний вибір для WordPress, коштує близько $70 на рік; тут це одна покупка.',
            ],
            [
                'slug' => 'advanced-translations',
                'name' => 'Advanced Translations',
                'category' => 'admin-tools',
                'package_type' => PackageType::MODULE,
                'price' => 1749,
                'short_description' => 'Редактор перекладів контенту та інтерфейсу.',
                'description' => 'Мультимовний сайт зазвичай означає купу рядків перекладу, розкиданих по коду й базі — і жодного зручного способу побачити, що саме ще не перекладено. Language + Translations дають єдиний адмін-екран з груповим списком усіх рядків, фільтром за мовою і відсотком завершеності перекладу для кожної секції сайту. WPML, найпопулярніший вибір для WordPress, коштує $99 на рік; тут функціонал вужчий (тільки контент і UI-рядки, без мультисайтовості), зате ціна одноразова й суттєво нижча.',
            ],
            [
                'slug' => 'library-vertical-pack',
                'name' => 'Book/Library Vertical Pack',
                'category' => 'library-vertical',
                'package_type' => PackageType::MODULE,
                'price' => 8399,
                'short_description' => 'Повний набір модулів для бібліотек і видавництв.',
                'description' => 'Це не e-commerce в класичному розумінні — цей пак для видавництв, бібліотек і сервісів електронного читання. Author, Book, Edition, BookSeries і BookTranslation описують книжку з усіма її версіями та перекладами; BookFile зберігає самі файли; BookSearchIndex робить пошук по книжках швидким навіть на великих каталогах. ReadingProgress, ReadingDiary і ReaderAnnotation дають читачу власний прогрес читання, щоденник і нотатки прямо в тексті — те, чого немає в жодному звичайному e-commerce-модулі. AiApiKey підключає AI-функції, а TelegramAnnouncement автоматично анонсує нові надходження у Telegram-каналі. Вузька, але платоспроможна аудиторія — і відповідно вища ціна, яка все одно суттєво нижча за вартість розробки такого функціоналу з нуля.',
            ],
            [
                'slug' => 'graphql-api-plugin',
                'name' => 'GraphQL API Plugin',
                'category' => 'api-integrations',
                'package_type' => PackageType::PLUGIN,
                'price' => 2199,
                'short_description' => 'GraphQL API поверх адмін-панелі Nexus.',
                'description' => 'REST API в Nexus є з коробки, але якщо ваш фронтенд чи мобільний застосунок хоче отримувати саме ті поля, які йому потрібні, без зайвих запитів — це GraphQL. Плагін додає повноцінну GraphQL-схему поверх існуючих модулів, без переписування бекенду. Statamic ховає GraphQL за повним Pro-тарифом у $259; тут це окремий, набагато дешевший фіч-гейт саме для тих, кому потрібен лише він.',
            ],
            [
                'slug' => 'advertising-pack',
                'name' => 'Advertising Pack',
                'category' => 'marketing-analytics',
                'package_type' => PackageType::MODULE,
                'price' => 1299,
                'short_description' => 'Керування рекламою та трекінг показів.',
                'description' => 'Рекламний банер на сайті, який реально показує, скільки разів його побачили і скільки разів на нього клікнули, — а не просто картинка в сайдбарі. Модуль Advertising керує рекламними блоками по всьому сайту й рахує покази та кліки в реальному часі, тож видно, яка реклама справді працює. Advanced Ads Pro для WordPress коштує $49–190 на рік залежно від тарифу — тут це одна покупка з тим самим базовим трекінгом.',
            ],

            // --- Безкоштовні модулі (Tier 1/2 + невизначені-раніше, з рекомендаціями документа) ---
            [
                'slug' => 'analytics',
                'name' => 'Analytics',
                'category' => 'marketing-analytics',
                'package_type' => PackageType::MODULE,
                'price' => 0,
                'short_description' => 'Базова аналітика — безкоштовно, як "wow"-фіча.',
                'description' => 'Базова аналітика — це та річ, яку не хочеться ставити окремим платним модулем, коли достатньо просто бачити, звідки приходять відвідувачі і які сторінки найпопулярніші. Тому дашборд Analytics безкоштовний за замовчуванням — та сама логіка, за якою Statamic дає базову статистику без додаткової оплати. Якщо згодом вам знадобляться глибші звіти й експорт у CSV, це може стати окремим платним доповненням, але базовий дашборд лишається безкоштовним завжди.',
            ],
            [
                'slug' => 'storefront-theme',
                'name' => 'Storefront (демо-тема)',
                'category' => 'free-modules',
                'package_type' => PackageType::MODULE,
                'price' => 0,
                'short_description' => 'Демо-тема вітрини — безкоштовний бонус до E-commerce Core.',
                'description' => 'Встановити e-commerce-двигун і отримати порожню сторінку без жодного дизайну — не найкращий досвід першого запуску. Тому Storefront, готова тема вітрини з каталогом, кошиком і чекаутом, йде безкоштовним бонусом до E-commerce Core Pack: купуєте один пак — отримуєте робочий магазин одразу, а не тільки бекенд. Так само роблять October CMS і Twill зі своїми демо-темами — окремо цю тему не продають.',
            ],
            [
                'slug' => 'forms-module',
                'name' => 'Forms',
                'category' => 'free-modules',
                'package_type' => PackageType::MODULE,
                'price' => 0,
                'short_description' => 'Конструктор форм та збір заявок.',
                'description' => 'Форма зворотного зв\'язку, заявка на консультацію, підписка на розсилку — базова потреба майже будь-якого сайту, тому Forms безкоштовний завжди. Конструктор дозволяє зібрати форму з довільним набором полів прямо в адмінці, без правки коду, а всі заявки складаються в окремий розділ із можливістю позначити оброблені.',
            ],
            [
                'slug' => 'blog-module',
                'name' => 'Blog',
                'category' => 'free-modules',
                'package_type' => PackageType::MODULE,
                'price' => 0,
                'short_description' => 'Блог з категоріями публікацій.',
                'description' => 'Блог із категоріями і повноцінним редактором постів — стандартна річ, яка мала б бути в кожній CMS без додаткової оплати, і тут вона така. Категорії впорядковують пости по темах, а сам редактор постів підтримує обкладинку, SEO-поля і публікацію за розкладом.',
            ],
            [
                'slug' => 'slider-module',
                'name' => 'Slider',
                'category' => 'free-modules',
                'package_type' => PackageType::MODULE,
                'price' => 0,
                'short_description' => 'Слайдер для головної сторінки.',
                'description' => 'Банер на головній сторінці, який покупці бачать першим, — і прибивати його намертво в коді теми не варіант, якщо контент має мінятись частіше за реліз коду. Slider дозволяє керувати слайдами прямо з адмінки: картинка, текст, посилання, порядок показу — усе редагується без розробника.',
            ],
            [
                'slug' => 'menu-module',
                'name' => 'Menu',
                'category' => 'free-modules',
                'package_type' => PackageType::MODULE,
                'price' => 0,
                'short_description' => 'Керування меню сайту.',
                'description' => 'Пункти навігації змінюються частіше, ніж хотілося б переносити структуру меню в код теми щоразу. Menu дає адмінці повний контроль над навігацією сайту — довільна вкладеність пунктів, посилання на будь-яку сторінку чи зовнішній URL, перетягування для зміни порядку.',
            ],
            [
                'slug' => 'redirect-module',
                'name' => 'Redirect',
                'category' => 'free-modules',
                'package_type' => PackageType::MODULE,
                'price' => 0,
                'short_description' => '301-редиректи для SEO.',
                'description' => 'Сторінка переїхала, URL змінився, а старе посилання все ще індексоване в Google — без 301-редиректу це втрачений трафік і зіпсоване SEO. Redirect дозволяє додати правило переспрямування за секунди прямо з адмінки, без правки .htaccess чи конфігів сервера.',
            ],
            [
                'slug' => 'sitemap-module',
                'name' => 'Sitemap',
                'category' => 'free-modules',
                'package_type' => PackageType::MODULE,
                'price' => 0,
                'short_description' => 'Автогенерація sitemap.xml.',
                'description' => 'Пошуковики індексують сайт швидше й повніше, коли мають актуальну карту сайту, — а оновлювати sitemap.xml вручну після кожної нової сторінки чи товару ніхто не хоче. Sitemap генерує карту сайту автоматично на основі опублікованого контенту, оновлюючи її щоразу, коли щось змінюється в каталозі чи блозі.',
            ],
            [
                'slug' => 'demo-module',
                'name' => 'Demo',
                'category' => 'free-modules',
                'package_type' => PackageType::MODULE,
                'price' => 0,
                'short_description' => 'Навчальний приклад модуля для розробників.',
                'description' => 'Найкращий спосіб зрозуміти, як влаштований модуль у Nexus, — це подивитись на реальний, робочий приклад, а не тільки читати документацію. Demo саме для цього: навчальний модуль, що охоплює майже всі типи полів, секції форми і варіанти relation-зв\'язків, які підтримує фреймворк. Лишається безкоштовним навмисно — це водночас підручник для розробників і привід для них знайти Nexus через пошук.',
            ],
            [
                'slug' => 'widgets-pack',
                'name' => 'Dashboard Widgets Pack',
                'category' => 'free-modules',
                'package_type' => PackageType::WIDGET,
                'price' => 0,
                'short_description' => 'Базові дашборд-віджети.',
                'description' => 'Дашборд адмінки виглядає живим, коли на ньому є хоч якісь цифри в реальному часі, а не тільки список пунктів меню зліва. Пак додає базовий набір віджетів — лічильник користувачів, рекламний слот прямо на дашборді, стрічку останніх записів — які можна перетягувати й вмикати за потреби. Базовий набір, входить безкоштовно.',
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
