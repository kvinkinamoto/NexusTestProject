<?php

namespace App\Nexus\Modules\Advertising\Models;

use App\Nexus\Modules\Advertising\Enums\AdvertisingType;
use App\Nexus\Modules\Advertising\Requests\AdminStoreRequest;
use App\Nexus\Modules\Advertising\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\Setting;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableFilter;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

/**
 * Both a settings holder (conversion-tracking pixel IDs — unchanged from
 * before, see tracking-scripts.blade.php) AND, as of this ad-slots feature,
 * a real content-managed module: one row = one ad campaign, which can be a
 * Google AdSense unit or any other custom HTML/JS embed (`type: Html`, the
 * snippet goes verbatim in `content_html`) or an uploaded image + click-
 * through link (`type: Banner`). `slot` is a free-text pool key, not a fixed
 * position enum — several rows can share the same slot to compete for one
 * placement (AdPlacementService picks one per view, respecting the caps
 * below), and the same slot can be shown at more than one on-page position
 * by creating more than one WidgetPlacement (widget_key: advertisingSlot)
 * that all point at it. The actual "where on the page" targeting is the
 * WidgetPlacement/`@position()` system every other front widget already
 * uses — see App\Nexus\Widgets\AdvertisingSlot.
 */
#[Module(
    name: 'advertising',
    label: 'Advertising',
    icon: 'solar:megaphone-bold',
    group: 'Site',
    showInMenu: true,
    livewire: true,
)]
#[Setting(name: 'enabled', type: 'boolean', label: 'Enabled', default: true, comment: 'Turn off to stop injecting any ad-tracking script or serving any ad slot, without clearing the settings/rows below.')]
#[Setting(name: 'facebook_pixel_id', type: 'string', label: 'Meta (Facebook) Pixel ID', comment: 'Numeric Pixel ID from Meta Events Manager.')]
#[Setting(name: 'google_ads_conversion_id', type: 'string', label: 'Google Ads Conversion ID', comment: 'e.g. AW-XXXXXXXXX — from Google Ads > Conversions.')]
#[Setting(name: 'adsense_client_id', type: 'string', label: 'Google AdSense Client ID', comment: 'e.g. ca-pub-XXXXXXXXXXXXXXXX — injects the AdSense loader script site-wide so individual ad units below only need their <ins> block.')]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Search by title', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'columns_2', icon: 'solar:megaphone-bold')]
#[Section(name: 'content_section', column: 'left', type: 'base', icon: 'solar:code-bold')]
#[Section(name: 'limits_section', column: 'right', type: 'base', icon: 'solar:chart-bold')]
class Advertising extends Model
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'Title', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Title', required: true, rules: ['max:255'])]
    protected $title;

    #[Column(label: 'Slot', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Slot key', required: true, rules: ['max:255'], showInInfolist: true)]
    protected $slot;

    #[Column(label: 'Type')]
    #[Field(type: 'enum', section: 'main', label: 'Type', required: true, enum: AdvertisingType::class, default: 'html')]
    protected $type;

    #[Field(type: 'text', section: 'content_section', label: 'HTML / JS code (AdSense unit or custom embed)', required: false, showWhen: [['field' => 'type', 'op' => 'eq', 'value' => 'html']], clearWhenHidden: true)]
    protected $content_html;

    #[Column(label: 'Banner', customField: 'showImage')]
    #[Field(type: 'image', section: 'content_section', label: 'Banner image', required: false, showWhen: [['field' => 'type', 'op' => 'eq', 'value' => 'banner']], clearWhenHidden: true)]
    protected $image;

    #[Field(type: 'url', section: 'content_section', label: 'Click-through URL', required: false, showWhen: [['field' => 'type', 'op' => 'eq', 'value' => 'banner']], clearWhenHidden: true)]
    protected $target_url;

    #[Column(label: 'Active', action: 'boolToggle', fieldName: 'is_active')]
    #[Field(type: 'boolean', section: 'main', label: 'Active', required: false, default: true)]
    protected $is_active;

    #[Field(type: 'number', section: 'limits_section', label: 'Max total impressions', required: false, rules: ['nullable', 'integer', 'min:1'])]
    protected $max_impressions;

    #[Field(type: 'number', section: 'limits_section', label: 'Max impressions per visitor', required: false, rules: ['nullable', 'integer', 'min:1'])]
    protected $max_impressions_per_visitor;

    #[Column(label: 'Impressions')]
    #[Field(type: 'number', section: 'limits_section', label: 'Impressions', required: false, disabled: true)]
    protected $impressions_count;

    #[Column(label: 'Clicks')]
    #[Field(type: 'number', section: 'limits_section', label: 'Clicks', required: false, disabled: true)]
    protected $clicks_count;

    #[Field(type: 'datetime', section: 'limits_section', label: 'Starts at', required: false)]
    protected $starts_at;

    #[Field(type: 'datetime', section: 'limits_section', label: 'Ends at', required: false)]
    protected $ends_at;

    protected $fillable = [
        'title', 'slot', 'type', 'content_html', 'image', 'target_url', 'is_active',
        'max_impressions', 'max_impressions_per_visitor', 'impressions_count', 'clicks_count',
        'starts_at', 'ends_at',
    ];

    protected $casts = [
        'type' => AdvertisingType::class,
        'is_active' => 'boolean',
        'max_impressions' => 'integer',
        'max_impressions_per_visitor' => 'integer',
        'impressions_count' => 'integer',
        'clicks_count' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function impressions(): HasMany
    {
        return $this->hasMany(AdImpression::class, 'advertising_id');
    }

    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function scopeForSlot($query, string $slot)
    {
        return $query->where('slot', $slot);
    }

    public function hasReachedGlobalCap(): bool
    {
        return $this->max_impressions !== null && $this->impressions_count >= $this->max_impressions;
    }

    public function hasReachedVisitorCap(string $visitorKey): bool
    {
        if ($this->max_impressions_per_visitor === null) {
            return false;
        }

        $views = $this->impressions()->where('visitor_key', $visitorKey)->value('views_count') ?? 0;

        return $views >= $this->max_impressions_per_visitor;
    }

    public function recordImpression(string $visitorKey): void
    {
        $this->increment('impressions_count');

        $impression = $this->impressions()->firstOrNew(['visitor_key' => $visitorKey]);
        $impression->views_count = $impression->exists ? $impression->views_count + 1 : 1;
        $impression->save();
    }

    public function recordClick(): void
    {
        $this->increment('clicks_count');
    }
}
