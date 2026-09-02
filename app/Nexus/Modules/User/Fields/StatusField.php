<?php

namespace App\Nexus\Modules\User\Fields;


use App\Nexus\Modules\User\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;
use Nodex\Nexus\Services\Interfaces\CustomFieldTypeInterface;

class StatusField implements CustomFieldTypeInterface
{

    public function getData()
    {
        return Status::cases();
    }

    public function getDefaultValue()
    {
        return Status::DEFAULT->value;
    }

    public function renderField($model, $field, $modelSchema, $lang): string|View
    {
//        dd($model, $field, $modelSchema, $lang);
//        dd( $this->getCustomData($model));
        return view('user::admin.field_types.status', [
            'model' => $model,
            'field' => $field,
            'customData' => $this->getCustomData($model),
            'defaultValue' => $this->getDefaultValue(),
            'moduleName' => 'user',
        ])->render();
    }

    public function renderFieldInList($model): string|View
    {
        // TODO: Implement renderFieldInList() method.
    }

    public function getCustomData(Model $model): mixed
    {
        return Status::cases();
    }

    public function getModel(): ?Model
    {
        return null;
        // TODO: Implement getModel() method.
    }
}
