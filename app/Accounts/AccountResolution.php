<?php

namespace App\Accounts;

final readonly class AccountResolution
{
    public function __construct(
        public string $accountId,
        public string $status,
        public bool $created,
        /**
         * The account's current contact address, or null when no linked
         * identity has asserted one.
         *
         * Metadata, not identity. Resolution stays keyed on
         * (provider, provider_subject), and equal emails still never merge.
         */
        public ?string $contactEmail = null,
    ) {}
}
