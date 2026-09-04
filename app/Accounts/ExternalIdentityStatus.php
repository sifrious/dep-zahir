<?php

namespace App\Accounts;

enum ExternalIdentityStatus: string
{
    case Active = 'active';
    case Revoked = 'revoked';
}
