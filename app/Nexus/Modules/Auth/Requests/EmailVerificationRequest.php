<?php

namespace App\Nexus\Modules\Auth\Requests;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Http\FormRequest;

class EmailVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!$this->user()) {
            return false;
        }

        if (!hash_equals((string) $this->route('id'), (string) $this->user()->getKey())) {
            return false;
        }

        if (!hash_equals(sha1($this->user()->getEmailForVerification()), (string) $this->route('hash'))) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function fulfill(): void
    {
        if (!$this->user()->hasVerifiedEmail()) {
            $this->user()->markEmailAsVerified();
            event(new Verified($this->user()));
        }
    }
}
