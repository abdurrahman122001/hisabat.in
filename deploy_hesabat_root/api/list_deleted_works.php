<?php
error_reporting(0);
header('Content-Type: application/json');

@include(__DIR__ . '/../config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

function ensure_delete_work_log_table(mysqli $con): void
{
    @mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `delete_work_log` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `client_id` VARCHAR(50) NULL,
            `client_name` VARCHAR(255) NULL,
            `work_name` VARCHAR(255) NULL,
            `size_h` VARCHAR(50) NULL,
            `size_w` VARCHAR(50) NULL,
            `piece` VARCHAR(50) NULL,
            `material` VARCHAR(100) NULL,
            `printer` VARCHAR(50) NULL,
            `date` VARCHAR(50) NULL,
            `op_id` VARCHAR(50) NULL,
            `total_amount` VARCHAR(50) NULL,
            `deleted_at` VARCHAR(50) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

ensure_delete_work_log_table($con);

$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$from = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
$to = isset($_GET['to']) ? trim((string)$_GET['to']) : '';

$where = [];
if ($search !== '') {
    $s = mysqli_real_escape_string($con, $search);
    $where[] = "(client_id LIKE '%$s%' OR client_name LIKE '%$s%' OR work_name LIKE '%$s%' OR material LIKE '%$s%' OR printer LIKE '%$s%' OR op_id LIKE '%$s%' OR size_h LIKE '%$s%' OR size_w LIKE '%$s%' OR piece LIKE '%$s%')";
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

$res = mysqli_query($con, "SELECT * FROM delete_work_log $whereSql ORDER BY id DESC");
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'db_error' => mysqli_error($con)]);
    exit;
}

$rows = [];
while ($r = mysqli_fetch_assoc($res)) {
    $rows[] = $r;
}

echo json_encode(['ok' => true, 'works' => $rows]);
