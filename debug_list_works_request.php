<?php
$_GET = [
    'search' => $argv[1] ?? '',
    'from' => $argv[2] ?? '',
    'to' => $argv[3] ?? '',
];

include __DIR__ . '/api/list_works.php';
