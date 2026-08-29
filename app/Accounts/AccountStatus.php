<?php

namespace App\Accounts;

enum AccountStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
}
