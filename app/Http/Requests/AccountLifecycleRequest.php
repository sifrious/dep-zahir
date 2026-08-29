<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AccountLifecycleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->attributes->get('zahir.can_manage_account_lifecycle', false);
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:512']];
    }
}
