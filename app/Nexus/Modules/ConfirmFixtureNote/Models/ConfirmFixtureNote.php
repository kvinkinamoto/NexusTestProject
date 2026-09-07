<?php

namespace App\Nexus\Modules\ConfirmFixtureNote\Models;

use App\Nexus\Modules\ConfirmFixture\Models\ConfirmFixture;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

/**
 * Permanent test fixture, not a real business module — the "specialized"
 * side of the #[AttachRelation]/#[AttachField]/#[AttachColumn] demo, proving
 * a module can attach a relation, admin field and admin column onto another
 * module (ConfirmFixture, the "base" side) without ConfirmFixture's own file
 * ever referencing this one. See Relations/ConfirmFixtureRelations.php and
 * app/Nexus/Plugins/ConfirmFixtureNote/ConfirmFixtureNotePlugin.php, and
 * tests/Feature/Nexus/AttachFieldColumnTest.php for the assertions this backs.
 */
#[Module(
    name: 'confirmfixturenote',
    label: 'ConfirmFixtureNote',
    icon: 'solar:chat-line-bold',
    group: 'Site',
    showInMenu: false,
    requires: ['ConfirmFixture'],
)]
#[Section(name: 'main', column: 'right', type: 'base', icon: 'solar:chat-line-bold')]
class ConfirmFixtureNote extends Model
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'Body')]
    #[Field(type: 'string', section: 'main', label: 'Body', required: true)]
    protected $body;

    protected $fillable = [
        'confirm_fixture_id',
        'body',
    ];

    public function confirmFixture(): BelongsTo
    {
        return $this->belongsTo(ConfirmFixture::class);
    }
}
