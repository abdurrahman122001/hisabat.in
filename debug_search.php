<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

@include(__DIR__ . '/config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    die("DB connection failed");
}

$search = 'CWZ 0061';

echo "Testing search: '$search'\n\n";

// Test 1: Check what looks_like_client_id returns
function looks_like_client_id(string $value): bool
{
    $normalized = preg_replace('/\s+/', '', trim($value));
    if ($normalized === null || $normalized === '') {
        return false;
    }
    $result = preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z0-9_\s-]+$/', trim($value)) === 1;
    echo "  looks_like_client_id('$value'): normalized='$normalized', regex result=$result\n";
    return $result;
}

$isClientId = looks_like_client_id($search);
echo "Is client ID: " . ($isClientId ? 'YES' : 'NO') . "\n\n";

// Test 2: Check if client exists in DB
$escapedValue = mysqli_real_escape_string($con, $search);
$normalizedSearch = preg_replace('/\s+/', '', $search);
$normalizedEscaped = mysqli_real_escape_string($con, (string)$normalizedSearch);

echo "Original escaped: '$escapedValue'\n";
echo "Normalized: '$normalizedSearch'\n";
echo "Normalized escaped: '$normalizedEscaped'\n\n";

// Test 3: Run the actual query part
$sql = "SELECT DISTINCT client_id FROM work WHERE REPLACE(client_id, ' ', '') COLLATE utf8mb4_general_ci = '$normalizedEscaped' LIMIT 5";
echo "SQL: $sql\n\n";

$res = mysqli_query($con, $sql);
if ($res) {
    $count = mysqli_num_rows($res);
    echo "Found $count matching records:\n";
    while ($r = mysqli_fetch_assoc($res)) {
        echo "  - client_id: " . $r['client_id'] . "\n";
    }
} else {
    echo "Query failed: " . mysqli_error($con) . "\n";
}

echo "\n\n--- Testing exact match without REPLACE ---\n";
$sql2 = "SELECT DISTINCT client_id FROM work WHERE client_id COLLATE utf8mb4_general_ci = '$escapedValue' LIMIT 5";
echo "SQL: $sql2\n\n";

$res2 = mysqli_query($con, $sql2);
if ($res2) {
    $count2 = mysqli_num_rows($res2);
    echo "Found $count2 matching records:\n";
    while ($r = mysqli_fetch_assoc($res2)) {
        echo "  - client_id: " . $r['client_id'] . "\n";
    }
} else {
    echo "Query failed: " . mysqli_error($con) . "\n";
}
