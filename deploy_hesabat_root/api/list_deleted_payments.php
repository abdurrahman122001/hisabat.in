<?php
error_reporting(0);
header('Content-Type: application/json');

@include(__DIR__ . '/../config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

function ensure_delete_payment_log_table(mysqli $con): void
{
    @mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `delete_payment_log` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `payment_id` INT NULL,
            `client_id` VARCHAR(50) NULL,
            `name` VARCHAR(255) NULL,
            `phone` VARCHAR(50) NULL,
            `paid` DECIMAL(12,2) NULL,
            `date` VARCHAR(50) NULL,
            `deleted_at` VARCHAR(50) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

ensure_delete_payment_log_table($con);

$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$from = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
$to = isset($_GET['to']) ? trim((string)$_GET['to']) : '';

$where = [];
if ($search !== '') {
    $s = mysqli_real_escape_string($con, $search);
    $where[] = "(client_id LIKE '%$s%' OR name LIKE '%$s%' OR phone LIKE '%$s%')";
}
if ($from !== '') {
    $f = mysqli_real_escape_string($con, $from);
    $where[] = "DATE(deleted_at) >= '$f'";
}
if ($to !== '') {
    $t = mysqli_real_escape_string($con, $to);
    $where[] = "DATE(deleted_at) <= '$t'";
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "SELECT id, payment_id, client_id, name, phone, paid, date, deleted_at FROM delete_payment_log $whereSql ORDER BY id DESC";
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

echo json_encode(['ok' => true, 'payments' => $rows]);
