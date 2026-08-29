<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class EntitlementDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => ['required', 'string', 'max:30'],
            'product' => ['required', 'string', 'max:128'],
            'entitlement' => ['required', 'string', 'max:128'],
        ];
    }
}
