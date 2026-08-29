<?php

$tokens = [];
foreach (array_filter(explode(',', (string) env('ZAHIR_SERVICE_TOKENS', ''))) as $credential) {
    [$caller, $token] = array_pad(explode(':', $credential, 2), 2, null);
    if (filled($caller) && filled($token)) {
        $tokens[$caller] = $token;
    }
}

return ['service_tokens' => $tokens];
