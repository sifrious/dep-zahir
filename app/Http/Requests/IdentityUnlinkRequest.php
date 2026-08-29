<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class IdentityUnlinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', 'max:64'],
            'provider_subject' => ['required', 'string', 'max:512'],
            'accepted_recovery_reference' => ['sometimes', 'string', 'max:512'],
        ];
    }
}
