<?php
// Encoding diagnostic test
header('Content-Type: application/json; charset=utf-8');

@include(__DIR__ . '/../config.php');

$tests = [
    'php_default_charset' => ini_get('default_charset'),
    'php_internal_encoding' => ini_get('internal_encoding'),
    'connection_charset' => isset($con) && $con instanceof mysqli ? $con->character_set_name() : 'N/A',
    'test_unicode' => 'Xoş gəldiniz - Müştəri Tələbat - Əyləncə',
    'test_json_encode' => json_encode(['message' => 'Xoş gəldiniz - Müştəri']),
];

// Also check a database value if possible
if (isset($con) && $con instanceof mysqli && !$con->connect_errno) {
    $res = $con->query("SHOW VARIABLES LIKE 'character_set%'");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $tests['db_' . $row['Variable_name']] = $row['Value'];
        }
    }
    
    // Check client names from database
    $res = $con->query("SELECT name FROM clients WHERE name LIKE '%ə%' OR name LIKE '%ş%' OR name LIKE '%ı%' LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        $tests['db_sample_name'] = $row['name'];
        $tests['db_sample_name_bytes'] = bin2hex($row['name']);
    }
}

echo json_encode($tests, JSON_UNESCAPED_UNICODE);
