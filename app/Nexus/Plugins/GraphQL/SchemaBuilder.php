<?php

namespace App\Nexus\Plugins\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use Illuminate\Support\Str;
use Nodex\Nexus\Dto\ModuleDtos\DefaultModuleConfigurationDto;
use Nodex\Nexus\Dto\ModuleDtos\FieldConfigDto;
use Nodex\Nexus\Models\Module;
use Nodex\Nexus\Services\ModuleManager;
use Nodex\Nexus\Services\Validation\NexusRuleCollector;

/**
 * Builds one GraphQL\Type\Schema per request from every enabled module's
 * already-attribute-derived DefaultModuleConfigurationDto — no separate
 * schema-definition language, no per-module boilerplate to hand-write. A
 * module is only visible here at all once it has at least one
 * #[Field(apiExpose: true)] scalar field (see TypeMapper for what counts as
 * scalar); everything else about it stays admin-only, same opt-in posture
 * REST's apiExpose already declared but never actually wired through
 * FieldConfigDto until this feature (see AttributeSchemaReader::processFieldAttr()).
 *
 * Deliberately NOT cached across requests — matches AttributeSchemaReader's
 * own per-request-only precedent (see ModuleManager::instanceGetModuleConfig()'s
 * docblock); cross-request schema caching is a documented follow-up, not
 * part of this feature.
 *
 * Authorization reuses ModuleManager::checkPermission() exactly as REST does
 * (same 'adminPanel' permission place) — this endpoint is protected by
 * auth:sanctum (see GraphQLPlugin::boot()), and Illuminate\Auth\Middleware\Authenticate
 * calls Auth::shouldUse() for whichever guard authenticated the request, so
 * auth()->user() here transparently resolves to the Sanctum token's user
 * with no change needed to the permission-checking code itself.
 *
 * Premium plugin (app/Nexus/Plugins/GraphQL) — extracted out of the
 * open-core nodex/nexus package; every dependency imported above is a
 * stable, public core class.
 */
class SchemaBuilder
{
    /** @var array<string, ObjectType> */
    private array $objectTypes = [];

    /** @var array<string, InputObjectType> */
    private array $inputTypes = [];

    public function __construct(private NexusRuleCollector $ruleCollector) {}

    public function build(): Schema
    {
        $queryFields = [
            'ping' => [
                'type' => Type::boolean(),
                'resolve' => fn () => true,
            ],
        ];
        $mutationFields = [];

        foreach (ModuleManager::getEnabledModules() as $moduleRow) {
            $moduleConfig = ModuleManager::getModuleConfig($moduleRow->name);
            $apiFields = $this->apiExposedScalarFields($moduleConfig);

            if (empty($apiFields)) {
                continue;
            }

            $camel = Str::camel($moduleConfig->name);
            $studly = Str::studly($moduleConfig->name);
            $objectType = $this->objectTypeFor($moduleConfig, $apiFields);
            $inputType = $this->inputTypeFor($moduleConfig, $apiFields);

            $queryFields[$camel] = [
                'type' => $objectType,
                'args' => ['id' => Type::nonNull(Type::id())],
                'resolve' => fn ($root, array $args) => $this->resolveShow($moduleConfig, (string) $args['id']),
            ];

            $queryFields[Str::plural($camel)] = [
                'type' => Type::listOf($objectType),
                'args' => ['limit' => Type::int(), 'offset' => Type::int()],
                'resolve' => fn ($root, array $args) => $this->resolveIndex($moduleConfig, $args),
            ];

            $mutationFields['create'.$studly] = [
                'type' => Type::nonNull($objectType),
                'args' => ['input' => Type::nonNull($inputType)],
                'resolve' => fn ($root, array $args) => $this->resolveCreate($moduleConfig, $apiFields, $args['input']),
            ];

            $mutationFields['update'.$studly] = [
                'type' => Type::nonNull($objectType),
                'args' => ['id' => Type::nonNull(Type::id()), 'input' => Type::nonNull($inputType)],
                'resolve' => fn ($root, array $args) => $this->resolveUpdate($moduleConfig, $apiFields, (string) $args['id'], $args['input']),
            ];

            $mutationFields['delete'.$studly] = [
                'type' => Type::nonNull(Type::boolean()),
                'args' => ['id' => Type::nonNull(Type::id())],
                'resolve' => fn ($root, array $args) => $this->resolveDelete($moduleConfig, (string) $args['id']),
            ];
        }

        $config = SchemaConfig::create()
            ->setQuery(new ObjectType(['name' => 'Query', 'fields' => $queryFields]));

        if (! empty($mutationFields)) {
            $config->setMutation(new ObjectType(['name' => 'Mutation', 'fields' => $mutationFields]));
        }

        return new Schema($config);
    }

    /** @return FieldConfigDto[] */
    private function apiExposedScalarFields(DefaultModuleConfigurationDto $moduleConfig): array
    {
        return array_values(array_filter(
            $moduleConfig->form->fields,
            fn (FieldConfigDto $field) => $field->apiExpose && TypeMapper::isSupported($field->type),
        ));
    }

    /** @param FieldConfigDto[] $apiFields */
    private function objectTypeFor(DefaultModuleConfigurationDto $moduleConfig, array $apiFields): ObjectType
    {
        $studly = Str::studly($moduleConfig->name);

        if (isset($this->objectTypes[$studly])) {
            return $this->objectTypes[$studly];
        }

        $fields = [
            'id' => ['type' => Type::nonNull(Type::id()), 'resolve' => fn ($model) => (string) $model->id],
        ];

        foreach ($apiFields as $field) {
            $fields[Str::camel($field->name)] = [
                'type' => TypeMapper::scalarFor($field->type),
                'resolve' => fn ($model) => $this->formatScalar($model->{$field->name} ?? null, $field->type),
            ];
        }

        return $this->objectTypes[$studly] = new ObjectType(['name' => $studly, 'fields' => $fields]);
    }

    /** @param FieldConfigDto[] $apiFields */
    private function inputTypeFor(DefaultModuleConfigurationDto $moduleConfig, array $apiFields): InputObjectType
    {
        $studly = Str::studly($moduleConfig->name);

        if (isset($this->inputTypes[$studly])) {
            return $this->inputTypes[$studly];
        }

        $fields = [];
        foreach ($apiFields as $field) {
            $fields[Str::camel($field->name)] = ['type' => TypeMapper::scalarFor($field->type)];
        }

        return $this->inputTypes[$studly] = new InputObjectType(['name' => $studly.'Input', 'fields' => $fields]);
    }

    private function formatScalar(mixed $value, string $widgetType): mixed
    {
        if ($value === null) {
            return null;
        }

        if (in_array($widgetType, ['date', 'datetime'], true) && $value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        return $value instanceof \UnitEnum ? ($value->value ?? $value->name) : $value;
    }

    /** @param FieldConfigDto[] $apiFields */
    private function inputToAttributes(array $apiFields, array $input): array
    {
        $byCamelName = [];
        foreach ($apiFields as $field) {
            $byCamelName[Str::camel($field->name)] = $field->name;
        }

        $attributes = [];
        foreach ($input as $key => $value) {
            if (isset($byCamelName[$key])) {
                $attributes[$byCamelName[$key]] = $value;
            }
        }

        return $attributes;
    }

    private function module(DefaultModuleConfigurationDto $moduleConfig): Module
    {
        return Module::findByName($moduleConfig->name) ?? throw new Error("Module [{$moduleConfig->name}] is not registered.");
    }

    private function authorize(string $action, DefaultModuleConfigurationDto $moduleConfig): void
    {
        if (! ModuleManager::checkPermission($action, $this->module($moduleConfig))) {
            throw new Error('Forbidden.');
        }
    }

    private function resolveShow(DefaultModuleConfigurationDto $moduleConfig, string $id)
    {
        $this->authorize('view', $moduleConfig);

        return $moduleConfig->model::query()->find($id);
    }

    private function resolveIndex(DefaultModuleConfigurationDto $moduleConfig, array $args)
    {
        $this->authorize('index', $moduleConfig);

        $query = $moduleConfig->model::query();
        if (isset($args['offset'])) {
            $query->skip((int) $args['offset']);
        }
        $query->take((int) ($args['limit'] ?? config('nexus.table.pagination.default_per_page', 15)));

        return $query->get();
    }

    /** @param FieldConfigDto[] $apiFields */
    private function resolveCreate(DefaultModuleConfigurationDto $moduleConfig, array $apiFields, array $input)
    {
        $this->authorize('create', $moduleConfig);

        $attributes = $this->inputToAttributes($apiFields, $input);
        $rules = $this->ruleCollector->collect($moduleConfig, 'store', $attributes);
        $validated = $this->validate($attributes, $rules);

        $modelClass = $moduleConfig->model;
        $model = new $modelClass;
        $model->fill($validated);
        $model->save();

        return $model;
    }

    /** @param FieldConfigDto[] $apiFields */
    private function resolveUpdate(DefaultModuleConfigurationDto $moduleConfig, array $apiFields, string $id, array $input)
    {
        $this->authorize('edit', $moduleConfig);

        $model = $moduleConfig->model::query()->findOrFail($id);
        $attributes = $this->inputToAttributes($apiFields, $input);
        $rules = $this->ruleCollector->collect($moduleConfig, 'update', $attributes);
        $validated = $this->validate($attributes, $rules);

        $model->fill($validated);
        $model->save();

        return $model;
    }

    private function resolveDelete(DefaultModuleConfigurationDto $moduleConfig, string $id): bool
    {
        $this->authorize('delete', $moduleConfig);

        $model = $moduleConfig->model::query()->find($id);
        if (! $model) {
            return false;
        }

        $model->delete();

        return true;
    }

    /**
     * Only the rule keys the GraphQL input actually covers are checked —
     * collect() also returns rules for relation/non-apiExpose fields (e.g.
     * Page's own 'relation.blocks'), which is fine here since those keys are
     * simply absent from $attributes and their rules are 'nullable', not
     * 'required' (see NexusRuleCollector::collectRelationDefaultRules()).
     */
    private function validate(array $attributes, array $rules): array
    {
        $validator = validator($attributes, $rules);
        if ($validator->fails()) {
            throw new Error(implode(' ', $validator->errors()->all()));
        }

        return $validator->validated();
    }
}
