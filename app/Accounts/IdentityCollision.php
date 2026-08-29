<?php

namespace App\Accounts;

use RuntimeException;

final class IdentityCollision extends RuntimeException
{
    public static function ambiguous(): self
    {
        return new self('External identity mapping is ambiguous.');
    }

    public static function linkedElsewhere(): self
    {
        return new self('External identity is already linked to another account.');
    }
}
