<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ExternalIdentityLifecycleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->attributes->get('zahir.can_manage_account_lifecycle', false);
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', 'max:64'],
            'provider_subject' => ['required', 'string', 'max:512'],
            'reason_code' => ['required', 'string', 'max:128', 'regex:/^[a-z0-9]+(?:_[a-z0-9]+)*$/'],
            'accepted_recovery_reference' => $this->isMethod('DELETE')
                ? ['required', 'string', 'max:512']
                : ['prohibited'],
        ];
    }
}
