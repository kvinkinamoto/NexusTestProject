<?php

namespace App\Nexus\Plugins\ConfirmFixtureNote;

use Nodex\Nexus\Attributes\AttachColumn;
use Nodex\Nexus\Attributes\AttachField;
use Nodex\Nexus\Attributes\Relation;
use Nodex\Nexus\Attributes\TargetModule;

/**
 * Admin-layer half of the ConfirmFixtureNote → ConfirmFixture attachment
 * demo (see App\Nexus\Modules\ConfirmFixtureNote\Models\ConfirmFixtureNote
 * for the full story, and Relations/ConfirmFixtureRelations.php for the
 * data-layer half this depends on). Neither method body is ever called —
 * #[AttachField]/#[AttachColumn] carry all the config PluginManager needs,
 * the same way a real model's #[Field]/#[Column] attributes work.
 *
 * Disabling this plugin (config('nexus.plugins.disabled') or the Plugins
 * admin screen) or removing the ConfirmFixtureNote module makes "notes"/
 * "notes_count" disappear from ConfirmFixture's admin schema on the next
 * request — nothing crashes, see tests/Feature/Nexus/AttachFieldColumnTest.php.
 */
#[TargetModule('ConfirmFixture')]
class ConfirmFixtureNotePlugin
{
    // Labels use the 'namespace::translate.key' form (see _label.blade.php's
    // and module-table.blade.php's own str_contains($label, '::') bypass) so
    // they resolve from THIS module's own lang file rather than needing a
    // key added to ConfirmFixture's — see resources/lang/en/translate.php.
    #[AttachField(name: 'notes', type: 'relationManager', section: 'main', label: 'confirmFixtureNote::translate.notes')]
    #[Relation(type: 'hasMany', show: 'body', relatedModule: 'confirmfixturenote')]
    public function notesField(): void {}

    #[AttachColumn(name: 'notes_count', label: 'confirmFixtureNote::translate.notes')]
    public function notesCountColumn(): void {}
}
