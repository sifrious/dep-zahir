<?php

declare(strict_types=1);

namespace Sifrious\Zahir\Authentication\V1;

enum AuthenticationNextAction: string
{
    case None = 'none';
    case SignIn = 'sign_in';
    case Retry = 'retry';
    case RecoverExternalIdentity = 'recover_external_identity';
    case ContactSupport = 'contact_support';
}
