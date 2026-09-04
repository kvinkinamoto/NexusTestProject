<?php

use Illuminate\Database\Eloquent\Model;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;
use Nodex\Nexus\Exceptions\ModuleMisconfiguredException;
use Nodex\Nexus\Services\AttributeSchemaReader;

#[Module(name: 'misconfig-fixture-relation')]
class MisconfigFixtureDanglingRelation extends Model
{
    use HasAttributeSchemaProperties;

    #[Field(type: 'relation')]
    protected $owner;
}

#[Module(name: 'misconfig-fixture-request')]
#[Requests(store: 'App\\Nexus\\Modules\\DoesNotExist\\Requests\\AdminStoreRequest')]
class MisconfigFixtureBadRequestClass extends Model
{
    use HasAttributeSchemaProperties;
}

it('throws when a #[Field(type: relation)] has no matching #[Relation] attribute', function () {
    expect(fn () => app(AttributeSchemaReader::class)->read(MisconfigFixtureDanglingRelation::class))
        ->toThrow(ModuleMisconfiguredException::class, 'owner');
});

it('throws when #[Requests] points at a class that does not exist', function () {
    expect(fn () => app(AttributeSchemaReader::class)->read(MisconfigFixtureBadRequestClass::class))
        ->toThrow(ModuleMisconfiguredException::class, 'DoesNotExist');
});
