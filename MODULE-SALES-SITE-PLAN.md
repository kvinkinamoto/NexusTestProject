# ТЗ: сайт продажу модулів Nexus ("Nexus Store")

> Самодостатнє технічне завдання для реалізації в новому чаті. Не описує гіпотетичний
> generic-сайт — описує конкретний форк **цього** проєкту (NexusTestProject), який
> "їсть свій же корм": каталог/замовлення/ліцензії реалізовані як звичайні **Nexus-модулі**
> (ті самі атрибути `#[Module]`/`#[Field]`/`#[Column]`, та сама адмінка `/admin`), а не як
> окрема, незалежно вигадана e-commerce-система. Синтаксис атрибутів нижче відповідає
> реальним конвенціям проєкту (див. skills `nexus-modules`/`nexus-plugins`, канонічний
> приклад — `app/Nexus/Modules/Demo`).

## 0. Ухвалені рішення (з попереднього обговорення)

| Питання | Рішення |
| --- | --- |
| База проєкту | **Копія NexusTestProject** — той самий `packages/nodex/nexus`, той самий `/admin`, ті самі Auth/User/Role/Permission. |
| Акаунти покупців | **Той самий `User`**, окрема роль `Customer` без жодних адмін-прав. Реєстрація на вітрині створює `User` з роллю `Customer`. |
| Checkout | **І buy-now, і повноцінний кошик** — обидва шляхи ведуть до одного Order/Checkout потоку. |
| Оплата (MVP) | **LiqPay**. |
| Ліцензія | **Довічний доступ і оновлення, але ціна — за один проєкт** (одна ліцензія = один проєкт/домен; додатковий проєкт = нова покупка). |
| Доставка модуля | Без Composer/Packagist. Приватний GitHub-репозиторій на модуль → вебхук → кеш `.zip` на диску сайту → підписане завантаження з кабінету → встановлюється клієнтом через уже готовий `app/Nexus/Modules/Modules/Services/ArchiveInstallService.php`. |

## 1. Що переноситься з поточного проєкту без змін

- `packages/nodex/nexus` — сам фреймворк, без модифікацій.
- Core: `Auth`, `User`, `Role`, `Permission`, `Media`, `Settings` (реєстр налаштувань).
- Tier 2 контентні модулі — готові, використовуються для маркетингових сторінок сайту:
  `Page` + blockEditor (головна, "Про нас", "Умови", "Політика повернення"),
  `BlogPost`/`BlogCategory` (блог/новини), `Menu` (навігація), `Slider` (банери на головній),
  `Redirect`, `Sitemap`.
- `ArchiveInstallService`/`install-from-archive.blade.php` — **не займаємо**, це вже готова
  клієнтська частина встановлення; сайт лише генерує сумісний з нею `.zip`.

**Видалити з копії** (не потрібні продакшн-сайту): `Demo`, `ConfirmFixtureNote` та інші
демо/тестові модулі-приклади з поточного репозиторію (залишити тільки як довідку в
документації, якщо знадобиться приклад атрибутів).

## 2. Нові Nexus-модулі

Усі — стандартні `php artisan nexus:make:module {Name}` + ручне редагування моделі,
без окремого фреймворку e-commerce.

### 2.1. `Product` — картка платного модуля в каталозі

```php
#[Module(name: 'product', label: 'Продукт', icon: 'solar:box-bold', group: 'Store', showInMenu: true, livewire: true)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableAction(name: 'boolToggle', fieldName: 'is_published')]
#[TableFilter(name: 'search', label: 'Пошук за назвою', type: 'search')]
#[TableFilter(name: 'published', label: 'Опубліковано', type: 'select')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'base')]
#[Section(name: 'media', column: 'right', type: 'base', tab: 'media')]
#[Section(name: 'github', column: 'right', type: 'base', tab: 'advanced')]
#[Section(name: 'relations', column: 'right', type: 'base', tab: 'relations')]
class Product extends Model
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'Назва', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', required: true)]
    protected $name;

    #[Field(type: 'slug', section: 'main', slugSource: 'name', required: true)]
    protected $slug; // унікальний, публічний URL /modules/{slug}

    #[Field(type: 'string', section: 'main')]
    protected $tagline;

    #[Field(type: 'markdown', section: 'main')]
    protected $description;

    #[Column(label: 'Ціна', sortable: true)]
    #[Field(type: 'currency', section: 'main', required: true)]
    protected $price; // одна ціна = одна ліцензія на один проєкт

    #[Field(type: 'gallery', section: 'media', label: 'Скріншоти')]
    protected $screenshots;

    #[Field(type: 'enum', section: 'github', enum: PackageTypeEnum::class, required: true)]
    protected $package_type; // module|plugin|widget — визначає, як покупець його ставитиме (ArchiveInstallService)

    #[Field(type: 'string', section: 'github', required: true)]
    protected $github_owner;

    #[Field(type: 'string', section: 'github', required: true)]
    protected $github_repo;

    #[Field(type: 'string', section: 'github')]
    protected $min_core_version;

    #[Column(label: 'Опубліковано')]
    #[Field(type: 'boolean', section: 'main')]
    protected $is_published;

    #[Field(type: 'relationManager', section: 'relations', label: 'Версії')]
    #[Relation(type: 'hasMany', relatedModule: 'product_release')]
    protected $releases;
}
```

### 2.2. `ProductRelease` — версії/релізи продукту (з GitHub-тегів)

```php
#[Module(name: 'product_release', label: 'Версія продукту', icon: 'solar:tag-bold', group: 'Store', showInMenu: true, requires: ['Product'])]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableAction(name: 'syncFromGithub', label: 'Синхронізувати з GitHub', icon: 'refresh', isConfirm: false)]
#[TableFilter(name: 'product', label: 'Продукт', type: 'select')]
class ProductRelease extends Model
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'Продукт')]
    #[Field(type: 'belongsTo', section: 'main', relatedModule: 'product', required: true)]
    protected $product_id;

    #[Column(label: 'Версія', sortable: true)]
    #[Field(type: 'string', section: 'main', required: true)] // SemVer, напр. 1.4.0
    protected $version;

    #[Field(type: 'string', section: 'main', required: true, disabled: 'edit')]
    protected $github_tag;

    #[Field(type: 'markdown', section: 'main')]
    protected $changelog;

    #[Field(type: 'string', section: 'main')]
    protected $min_core_version;

    #[Field(type: 'string', section: 'main')]
    protected $max_core_version;

    #[Column(label: 'Опубліковано')]
    #[Field(type: 'datetime', section: 'main')]
    protected $published_at;

    // zip_disk_path / zip_size / synced_at — звичайні DB-колонки БЕЗ #[Field]:
    // заповнюються виключно GithubReleaseSyncService, в адмінці не редагуються.
}
```

`syncFromGithub` викликає `GithubReleaseSyncService::sync(ProductRelease $release)` —
той самий сервіс, що й вебхук (розділ 4), тільки вручну з картки конкретного релізу.

### 2.3. `Order` / `OrderItem` — замовлення

```php
#[Module(name: 'order', label: 'Замовлення', icon: 'solar:cart-bold', group: 'Store', showInMenu: true, livewire: true)]
#[TableAction(name: 'edit', label: 'Переглянути', icon: 'eye', isConfirm: false)]
#[TableAction(name: 'markPaid', label: 'Позначити оплаченим', icon: 'check', isConfirm: true)]
#[TableAction(name: 'refund', label: 'Оформити повернення', icon: 'refresh', isConfirm: true)]
#[TableFilter(name: 'status', label: 'Статус', type: 'select')]
class Order extends Model
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'Клієнт')]
    #[Field(type: 'belongsTo', section: 'main', relatedModule: 'user', required: true)]
    protected $customer_id;

    #[Column(label: 'Статус', sortable: true)]
    #[Field(type: 'enum', section: 'main', enum: OrderStatusEnum::class, required: true)]
    protected $status; // pending | awaiting_payment | paid | cancelled | refunded

    #[Column(label: 'Сума')]
    #[Field(type: 'currency', section: 'main', disabled: 'edit')]
    protected $total;

    #[Field(type: 'enum', section: 'main', enum: PaymentProviderEnum::class)]
    protected $payment_provider; // liqpay

    #[Field(type: 'string', section: 'main', disabled: 'edit')]
    protected $payment_reference;

    #[Field(type: 'datetime', section: 'main', disabled: 'edit')]
    protected $paid_at;

    #[Field(type: 'relationManager', section: 'relations', label: 'Позиції')]
    #[Relation(type: 'hasMany', relatedModule: 'order_item')]
    protected $items;
}
```

`OrderItem` — `showInMenu: false` (керується лише через `relationManager` замовлення):
`order_id` (belongsTo), `product_id` (belongsTo), `quantity` (number, default 1 — кількість
куплених "проєктних" ліцензій цього продукту в межах замовлення), `unit_price` (currency,
знімок ціни на момент покупки).

**Бізнес-логіка оплати:** `OrderService::markAsPaid(Order $order)` виставляє
`status = paid`, `paid_at = now()`, диспатчить власну доменну подію
`App\Nexus\Modules\Order\Events\OrderPaid`. Обробляє її **не сам Order-модуль**, а
`Listeners/IssueLicensesOnOrderPaid.php` у модулі `License` (класичний приклад
"реагування на життєвий цикл іншого модуля через `Listeners/`", без плагіна — див.
`nexus-modules` skill) — для кожного `OrderItem` створює `quantity` штук `License`.

### 2.4. `License` / `LicenseActivation` — ліцензії "один проєкт на ліцензію"

```php
#[Module(name: 'license', label: 'Ліцензія', icon: 'solar:key-bold', group: 'Store', showInMenu: true, requires: ['Product', 'Order'])]
#[TableAction(name: 'edit', label: 'Переглянути', icon: 'eye', isConfirm: false)]
#[TableAction(name: 'revoke', label: 'Відкликати', icon: 'lock', isConfirm: true)]
#[TableFilter(name: 'search', label: 'Пошук за ключем', type: 'search')]
#[TableFilter(name: 'status', label: 'Статус', type: 'select')]
class License extends Model
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'Клієнт')]
    #[Field(type: 'belongsTo', section: 'main', relatedModule: 'user', required: true, disabled: 'edit')]
    protected $customer_id;

    #[Column(label: 'Продукт')]
    #[Field(type: 'belongsTo', section: 'main', relatedModule: 'product', required: true, disabled: 'edit')]
    protected $product_id;

    #[Field(type: 'belongsTo', section: 'main', relatedModule: 'order_item', disabled: 'edit')]
    protected $order_item_id;

    #[Column(label: 'Ключ', searchable: true)]
    #[Field(type: 'string', section: 'main', disabled: 'edit')] // генерується сервісом, не вводиться вручну
    protected $license_key;

    #[Column(label: 'Статус')]
    #[Field(type: 'enum', section: 'main', enum: LicenseStatusEnum::class)] // active | revoked
    protected $status;

    #[Field(type: 'relationManager', section: 'relations', label: 'Активації')]
    #[Relation(type: 'hasMany', relatedModule: 'license_activation')]
    protected $activations;
}
```

`LicenseActivation` (`showInMenu: false`, керується через relationManager і через
клієнтський кабінет): `license_id` (belongsTo), `project_label` (string, вільний текст —
домен/назва проєкту), `activated_at` (datetime), `released_at` (datetime, nullable).

**Правило "один проєкт":** одночасно допускається лише один запис із
`released_at IS NULL` на ліцензію. Перевіряється в `LicenseActivationService::activate()`
під транзакцією/блокуванням рядка (не через partial unique index — MySQL його не
підтримує напряму). "Змінити проєкт" у кабінеті = `released_at = now()` на старому +
створення нового запису.

## 3. Публічна частина (НЕ Nexus-модулі — це вітрина, не адмін-контент-тайп)

Каталог/кошик/чекаут/кабінет — окрема структура поза `app/Nexus/Modules`, за аналогією з
тим, як `packages/nodex/nexus` вже відділяє публічний рендер (наприклад
`<x-nexus::image>`) від адмінки:

```
app/Http/Controllers/Store/   — GithubWebhookController, LiqPayController, DownloadController
app/Livewire/Store/           — ProductCatalog, ProductDetail, CartPage, CheckoutPage
app/Livewire/Account/         — Dashboard, OrdersList, OrderDetail, LicensesList, LicenseDetail, Profile
resources/views/store/...
resources/views/account/...
```

Кошик — звичайні Eloquent-моделі, не Nexus-модуль (ефемерні дані покупця, адмінці не
потрібні): `carts` (customer_id nullable, session_id nullable), `cart_items` (cart_id,
product_id, quantity, unit_price-знімок). Гостьовий кошик у сесії, зливається з
акаунтом при вході — так само, як типова Laravel-корзина.

## 4. Синхронізація з GitHub

Реалізація — Nexus-плагін `app/Nexus/Plugins/GithubSync/GithubSyncPlugin.php`:

```php
#[TargetModule('User')] // anchor-only, за зразком GraphQLPlugin/BlockTypesPlugin — реальна робота в boot()
class GithubSyncPlugin
{
    public function boot(): void
    {
        Route::post('/webhooks/github', [GithubWebhookController::class, 'handle']);
    }
}
```

- `GithubWebhookController::handle()`: перевіряє `X-Hub-Signature-256` проти
  `config('services.github.webhook_secret')`, знаходить `Product` за `github_repo`,
  викликає `GithubReleaseSyncService::sync()`.
- `GithubReleaseSyncService::sync(Product $product, string $tag)`:
  `GET /repos/{owner}/{repo}/zipball/{tag}` через GitHub App/PAT-токен
  (`config('services.github.token')`, право `Contents: read-only`, лише на потрібні
  приватні репозиторії), стрімить у
  `storage/app/releases/{product_slug}/{version}.zip`, створює/оновлює `ProductRelease`.
- Ручний бекфіл/тригер — команда модуля (за конвенцією: команди модуля живуть у
  `{Module}/Commands/`, не в `app/Console/Commands`):
  `app/Nexus/Modules/ProductRelease/Commands/SyncProductReleaseCommand.php`
  → `php artisan nexus:store:sync-release {product} {tag}`.

GitHub API rate limit (5000/год) не є проблемою — синк раз на реліз, покупці качають
уже закешований файл із власного диска сайту, не з GitHub напряму.

## 5. Оплата (LiqPay)

Плагін `app/Nexus/Plugins/LiqPay/LiqPayPlugin.php` (той самий anchor-патерн):
- `POST /checkout/liqpay/redirect` — формує підписаний LiqPay-запит (`data`+`signature`
  за офіційною схемою LiqPay API — **звірити актуальну версію API/параметрів у
  документації LiqPay на етапі реалізації**, тут фіксується тільки місце інтеграції).
- `POST /checkout/liqpay/callback` — серверний callback, перевіряє підпис, за
  `order_id` у `payment_reference` знаходить `Order`, викликає
  `OrderService::markAsPaid()` (розділ 2.3).

## 6. Видача завантажень (ліцензійний доступ)

`DownloadController::download(License $license, ProductRelease $release)` за
підписаним, короткоживучим `URL::temporarySignedRoute()`:

1. `auth()->user()->id === $license->customer_id` і `$license->status === 'active'`.
2. `$release->product_id === $license->product_id` і `$release->published_at` заповнено.
3. Якщо активної (`released_at IS NULL`) `LicenseActivation` ще нема — спершу форма
   "введіть назву/домен проєкту" → створює активацію, тоді дозволяє скачування.
4. Віддає файл із `storage/app/releases/...` (`Storage::download()` /
   `Storage::disk('s3')->temporaryUrl()`, якщо диск буде S3).

Покупець качає `.zip` і встановлює його через уже готовий
`ArchiveInstallService`/`install-from-archive.blade.php` — з боку клієнтського
Nexus-застосунку жодних змін не потрібно.

## 7. Повний перелік сторінок/роутів

**Публічні:**
| URL | Джерело |
| --- | --- |
| `/` | `Page` (blockEditor: hero, підбірка продуктів, CTA) |
| `/modules` | `Store\ProductCatalog` (Livewire) — каталог, пошук/фільтр |
| `/modules/{slug}` | `Store\ProductDetail` — опис, скріншоти, changelog (з `ProductRelease`), "Купити зараз" / "Додати в кошик" |
| `/cart` | `Store\CartPage` |
| `/checkout` | `Store\CheckoutPage` — дані покупця + "Оплатити LiqPay" |
| `/checkout/liqpay/redirect`, `/checkout/liqpay/callback` | `LiqPayPlugin` |
| `/orders/{order}/thank-you` | сторінка підтвердження після оплати |
| `/blog`, `/blog/{category}`, `/blog/{slug}` | `BlogPost`/`BlogCategory` (готові) |
| `/about`, `/terms`, `/privacy`, `/refund-policy`, `/contact` | окремі записи `Page` |
| `/sitemap.xml` | `Sitemap` (готовий) |
| `/login`, `/register`, `/password/reset` | існуючий `Auth`, тема під вітрину; реєстрація → роль `Customer` |

**Кабінет покупця (`auth`):**
| URL | Призначення |
| --- | --- |
| `/account` | огляд: останні замовлення, ліцензії |
| `/account/orders`, `/account/orders/{order}` | історія замовлень / деталі-рахунок |
| `/account/licenses` | список ліцензій |
| `/account/licenses/{license}` | ключ, статус активації, доступні версії, changelog |
| `/account/licenses/{license}/activate` | форма прив'язки проєкту (розділ 6, п.3) |
| `/account/licenses/{license}/download/{release}` | підписане завантаження |
| `/account/profile` | редагування профілю |

**Адмінка (`/admin`, автоматично з нових модулів + вже готове):**
`product`, `product_release` (+ дія "Синхронізувати з GitHub"), `order` (+ "Позначити
оплаченим"/"Повернення"), `license` (+ "Відкликати"), а також уже наявні `page`,
`blog_post`, `blog_category`, `menu`, `slider`, `redirect`, `sitemap`, `user`, `role`,
`permission`, налаштування.

## 8. Ролі й доступ

Сідер ролі `Customer` — **без жодного адмін-права** (жоден пункт `/admin`-меню не
з'являється; існуючий `NexusAdminMiddleware`/permission-механізм уже це забезпечує —
достатньо не видавати customer-ролі жодних дозволів). Реєстрація на вітрині завжди
призначає саме цю роль.

## 9. Тести (Pest, за конвенцією `testing-best-practices`)

- `ProductCatalogTest` — публічний список показує лише `is_published`.
- `GithubReleaseSyncServiceTest` — сервіс тестується напряму (не через artisan — див.
  прецедент з Tier 3/Sitemap: команди модуля недоступні через `$this->artisan()` у Pest).
- `LiqPayCallbackTest` — валідний/невалідний підпис, ідемпотентність повторного callback.
- `OrderPaidIssuesLicenseTest` — оплата замовлення з `quantity=2` створює 2 `License`.
- `LicenseActivationTest` — друга активація без звільнення першої відхиляється.
- `LicenseDownloadTest` — чужа ліцензія / неопублікований реліз / протермінований
  підписаний лінк → 403/404.
- `CustomerRegistrationTest` — нова реєстрація не отримує жодного адмін-дозволу.

## 10. Поетапний план реалізації

1. **Фаза 0 — каркас.** Копія проєкту, видалення Demo/тестових модулів, сідер ролі
   `Customer`, базова тема вітрини (layout, шапка/підвал через `Menu`).
2. **Фаза 1 — каталог.** Модулі `Product`/`ProductRelease`, адмінка для них,
   `SyncProductReleaseCommand` (ручний синк, без вебхука), публічні `/modules`,
   `/modules/{slug}` без кнопки купівлі.
3. **Фаза 2 — GitHub-автоматизація.** `GithubSyncPlugin` + вебхук, кеш `.zip` на диску.
4. **Фаза 3 — кошик і замовлення.** Cart/CartItem, buy-now і кошик, модулі
   `Order`/`OrderItem`, чернетка чекауту без реальної оплати (статус одразу `paid` для
   ручного тестування потоку).
5. **Фаза 4 — LiqPay.** Реальна оплата, callback, `OrderService::markAsPaid()`.
6. **Фаза 5 — ліцензії.** Модулі `License`/`LicenseActivation`, лістенер видачі ліцензій,
   кабінет покупця (замовлення/ліцензії/активація/завантаження).
7. **Фаза 6 — полірування.** Email-сповіщення (підтвердження замовлення, лист із ключем),
   SEO-сторінки (`Sitemap`/`Redirect`), security-прохід за зразком уже зробленого в
   основному проєкті (rate limiting на `/checkout/*` і `/account/*/download`).

## 11. Уточнити під час реалізації (не блокує старт Фази 0)

- Конкретний пакет/спосіб інтеграції LiqPay (офіційний PHP SDK чи ручний HTTP-запит).
- Поштовий провайдер для листів (SMTP/Mailgun/інше) — потрібен із Фази 5.
- Чи потрібна багатомовність вітрини (uk/en) — Nexus-поля вже підтримують
  `translated: true`, просто питання обсягу перекладу контенту.
