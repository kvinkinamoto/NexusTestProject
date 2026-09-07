<?php

namespace App\Nexus\Plugins\ConfirmFixtureNote;

use Nodex\Nexus\Attributes\AttachColumn;
use Nodex\Nexus\Attributes\AttachField;
use Nodex\Nexus\Attributes\AttachFilter;
use Nodex\Nexus\Attributes\Relation;
use Nodex\Nexus\Attributes\TargetModule;

/**
 * Admin-layer half of the ConfirmFixtureNote → ConfirmFixture attachment
 * demo (see App\Nexus\Modules\ConfirmFixtureNote\Models\ConfirmFixtureNote
 * for the full story, and Relations/ConfirmFixtureRelations.php for the
 * data-layer half this depends on). Neither method body is ever called —
 * #[AttachField]/#[AttachColumn]/#[AttachFilter] carry all the config
 * PluginManager needs, the same way a real model's #[Field]/#[Column]
 * attributes work.
 *
 * Disabling this plugin (config('nexus.plugins.disabled') or the Plugins
 * admin screen) or removing the ConfirmFixtureNote module makes "notes"/
 * "notes_count"/"notes_search" disappear from ConfirmFixture's admin schema
 * on the next request — nothing crashes, see
 * tests/Feature/Nexus/AttachFieldColumnTest.php.
 *
 * "notes"/"notes_count" also carry a `permission` — deliberately, to
 * demonstrate/prove that a viewer without 'confirmfixturenote_view' never
 * sees them even though they can otherwise fully edit ConfirmFixture
 * (a paid/optional module attaching onto a free base module shouldn't leak
 * to every editor of that base module by default). "notes_search" is left
 * ungated to prove the no-`permission` (always-visible) path still works
 * unchanged.
 */
#[TargetModule('ConfirmFixture')]
class ConfirmFixtureNotePlugin
{
    // Labels use the 'namespace::translate.key' form (see _label.blade.php's
    // and module-table.blade.php's own str_contains($label, '::') bypass) so
    // they resolve from THIS module's own lang file rather than needing a
    // key added to ConfirmFixture's — see resources/lang/en/translate.php.
    #[AttachField(name: 'notes', type: 'relationManager', section: 'main', label: 'confirmFixtureNote::translate.notes', permission: 'confirmfixturenote_view')]
    #[Relation(type: 'hasMany', show: 'body', relatedModule: 'confirmfixturenote')]
    public function notesField(): void {}

    #[AttachColumn(name: 'notes_count', label: 'confirmFixtureNote::translate.notes', permission: 'confirmfixturenote_view')]
    public function notesCountColumn(): void {}

    #[AttachFilter(name: 'notes_search', label: 'confirmFixtureNote::translate.notes', type: 'search')]
    public function notesSearchFilter(): void {}
}
