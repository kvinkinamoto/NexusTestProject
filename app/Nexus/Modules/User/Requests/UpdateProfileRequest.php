<?php

namespace App\Nexus\Modules\User\Requests;

use App\Nexus\Modules\User\Enums\Gender;
use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class UpdateProfileRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^[+\d\s\-()]+$/'],
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'birthday' => ['nullable', 'date', 'before:today'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('user::translate.name'),
            'last_name' => __('user::translate.last_name'),
            'middle_name' => __('user::translate.middle_name'),
            'email' => __('user::translate.email'),
            'phone' => __('user::translate.phone'),
            'gender' => __('user::translate.gender'),
            'birthday' => __('user::translate.birthday'),
        ];
    }
}
