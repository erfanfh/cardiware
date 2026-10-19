<?php

return [
    'composer_binary' => env('SYSTEM_REPORT_COMPOSER_BINARY', 'composer'),
    'npm_binary' => env('SYSTEM_REPORT_NPM_BINARY', PHP_OS_FAMILY === 'Windows' ? 'npm.cmd' : 'npm'),
    'audit_timeout' => 120,
];
