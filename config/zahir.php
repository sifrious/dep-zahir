<?php

return [
    'maximum_request_bytes' => (int) env('ZAHIR_MAXIMUM_REQUEST_BYTES', 32768),
    'requests_per_minute' => (int) env('ZAHIR_REQUESTS_PER_MINUTE', 120),
];
