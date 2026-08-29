<?php

namespace App\Http\Requests;

use App\Identity\VerifiedExternal;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

final class VerifiedExternalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'external.provider' => ['required', 'string', 'max:64'],
            'external.provider_subject' => ['required', 'string', 'max:512'],
            'external.claims' => ['present', 'array:email,email_verified,name'],
            'external.claims.email' => ['sometimes', 'email:rfc', 'max:320'],
            'external.claims.email_verified' => ['sometimes', 'boolean'],
            'external.claims.name' => ['sometimes', 'string', 'max:255'],
            'external.provenance' => ['required', 'array:issuer,audience,asserted_at,assertion_id'],
            'external.provenance.issuer' => ['required', 'string', 'max:2048'],
            'external.provenance.audience' => ['required', 'string', 'max:512'],
            'external.provenance.asserted_at' => ['required', 'date'],
            'external.provenance.assertion_id' => ['sometimes', 'string', 'max:512'],
            'external.authenticated_at' => ['required', 'date'],
        ];
    }

    public function verifiedExternal(): VerifiedExternal
    {
        $external = $this->validated('external');

        return new VerifiedExternal(
            provider: $external['provider'],
            providerSubject: $external['provider_subject'],
            claims: $external['claims'],
            provenance: $external['provenance'],
            authenticatedAt: CarbonImmutable::parse($external['authenticated_at']),
        );
    }
}
