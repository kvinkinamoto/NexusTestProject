<?php

namespace App\Nexus\Modules\User\Requests;

use App\Nexus\Modules\User\Enums\Gender;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^[+\d\s\-()]+$/'],
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'birthday' => ['nullable', 'date', 'before:today'],
            'avatar' => ['nullable', 'string', 'max:2048'],
            'password' => ['required', Password::defaults()],
            'relation' => ['nullable', 'array'],
            'relation.roles' => ['nullable', 'array'],
            'relation.roles.*' => ['nullable', 'int'],
            'relation.permissions' => ['nullable', 'array'],
            'relation.permissions.*' => ['nullable', 'int'],
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
            'avatar' => __('user::translate.avatar'),
            'password' => __('user::translate.password'),
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->request->has('password') && request()->get('password') == null) {
            $this->request->remove('password');
        } else {
            $this->request->set('password', Hash::make(strval(request()->get('password'))));
        }
    }
}
