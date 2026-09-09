<?php

namespace App\Nexus\Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Kalnoy\Nestedset\NodeTrait;
use Nodex\Nexus\Services\RelatedEntityFieldService;
use Spatie\Translatable\HasTranslations;

class MenuItem extends Model
{
    use HasTranslations;
    use NodeTrait;

    /**
     * Same mechanism Permission::display_name/Role::display_name/
     * Demo::title_i18n already use — a plain string assignment (as every
     * existing caller of MenuItem::create()/update() already does) still
     * works unchanged, since Spatie's HasTranslations stores it under the
     * current app locale automatically. Only actually varies by locale once
     * more than one is configured — see activeLocales() usage in
     * MenuItemsManager, which is what decides whether the admin form even
     * offers more than one input.
     */
    public $translatable = ['title'];

    protected $fillable = [
        'menu_id', 'parent_id', 'title', 'url', 'linkable_type', 'linkable_id',
        'icon', 'open_in_new_tab', 'is_active',
    ];

    protected $casts = [
        'open_in_new_tab' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Scopes every nestedset operation (siblings/move/up/down/...) to rows
     * sharing the same menu_id, so several menus' trees never interfere with
     * each other's lft/rgt bookkeeping — see Kalnoy\Nestedset\NodeTrait's own
     * getScopeAttributes()/scoped() docblocks.
     */
    protected function getScopeAttributes(): array
    {
        return ['menu_id'];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Used by the public menu partial's recursion instead of NodeTrait's own
     * children()/defaultOrder(), which don't filter by is_active — an
     * inactive item (and everything under it) must disappear from the
     * public-facing menu without needing to be deleted.
     */
    public function activeChildren()
    {
        return $this->children()->where('is_active', true)->defaultOrder()->with('linkable');
    }

    /**
     * A manually-entered `url` always wins; otherwise resolve the linked
     * model's own public URL through Nodex\Nexus\Services\
     * RelatedEntityFieldService::resolveUrl() — the package's existing
     * #[Module(menuResolver:)]/UrlResolverInterface mechanism, so a new
     * linkable content type only ever needs its own module wired up
     * (menuResolver: SomeResolver::class), never a change here. Falls back
     * to '#' when there's no url, no linkable, or the linkable's owning
     * module has no resolver (or was disabled/deleted) — a broken menu link
     * shouldn't 500 the page it's rendered on.
     */
    public function resolvedUrl(): string
    {
        if ($this->url) {
            return $this->url;
        }

        if ($this->linkable) {
            $url = app(RelatedEntityFieldService::class)->resolveUrl($this->linkable);
            if ($url) {
                return $url;
            }
        }

        return '#';
    }
}
