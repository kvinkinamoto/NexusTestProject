<?php

namespace App\Nexus\Modules\ConfirmFixtureNote\Relations;

use App\Nexus\Modules\ConfirmFixture\Models\ConfirmFixture;
use App\Nexus\Modules\ConfirmFixtureNote\Models\ConfirmFixtureNote;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nodex\Nexus\Attributes\AttachRelation;
use Nodex\Nexus\Attributes\AttachScope;

/**
 * Data-layer half of the ConfirmFixtureNote → ConfirmFixture attachment demo
 * (see Models/ConfirmFixtureNote.php for the full story). Attaches:
 *  - a `notes` HasMany relation onto ConfirmFixture, so
 *    $fixture->notes actually resolves despite ConfirmFixture's own file
 *    never declaring it.
 *  - a `notes_count` withCount() global scope, so a plain
 *    #[AttachColumn(name: 'notes_count')] (see the plugin) has a real value
 *    to render — computed-column support piggybacks on the same
 *    #[AttachScope] mechanism rather than needing a dedicated one.
 */
class ConfirmFixtureRelations
{
    #[AttachRelation(model: ConfirmFixture::class, name: 'notes')]
    public static function notes(ConfirmFixture $fixture): HasMany
    {
        return $fixture->hasMany(ConfirmFixtureNote::class);
    }

    #[AttachScope(model: ConfirmFixture::class, name: 'withNotesCount')]
    public static function withNotesCount(): \Closure
    {
        return fn ($query) => $query->withCount('notes');
    }
}
