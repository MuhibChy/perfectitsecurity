<?php

return [
    // Sandbox top-up gateway. Forced off in production by the gateway
    // itself; enable explicitly in local/staging test environments only.
    'test_gateway' => env('WALLET_TEST_GATEWAY', false),
];
