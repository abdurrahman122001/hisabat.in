<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

@include(__DIR__ . '/../config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

function table_exists(mysqli $con, string $table): bool
{
    $t = mysqli_real_escape_string($con, $table);
    $res = mysqli_query($con, "SHOW TABLES LIKE '$t'");
    return $res && mysqli_num_rows($res) > 0;
}

if (!table_exists($con, 'delete_stock_log')) {
    echo json_encode(['ok' => true, 'deleted' => []]);
    exit;
}

$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$from = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
$to = isset($_GET['to']) ? trim((string)$_GET['to']) : '';

$where = [];
if ($search !== '') {
    $s = mysqli_real_escape_string($con, $search);
    $where[] = "(product LIKE '%$s%' OR stock_id LIKE '%$s%' OR note LIKE '%$s%')";
}
if ($from !== '') {
    $f = mysqli_real_escape_string($con, $from);
    $where[] = "DATE(COALESCE(delete_date, create_date)) >= '$f'";
}
if ($to !== '') {
    $t = mysqli_real_escape_string($con, $to);
    $where[] = "DATE(COALESCE(delete_date, create_date)) <= '$t'";
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "SELECT * FROM delete_stock_log $whereSql ORDER BY COALESCE(delete_date, create_date) DESC";
$res = mysqli_query($con, $sql);
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'db_error' => mysqli_error($con)]);
    exit;
}

$rows = [];
while ($r = mysqli_fetch_assoc($res)) {
    $rows[] = $r;
}

echo json_encode(['ok' => true, 'deleted' => $rows]);
