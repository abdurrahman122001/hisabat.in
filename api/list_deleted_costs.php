<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

@include(__DIR__ . '/../config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

function ensure_delete_cost_log_table(mysqli $con): void
{
    @mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `delete_cost_log` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `cost_id` VARCHAR(50) NOT NULL,
            `amount` VARCHAR(50) NULL,
            `date` VARCHAR(50) NULL,
            `note` TEXT NULL,
            `cost_name` VARCHAR(255) NULL,
            `delete_date` VARCHAR(50) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function table_has_column(mysqli $con, string $table, string $column): bool
{
    $tableEsc = mysqli_real_escape_string($con, $table);
    $colEsc = mysqli_real_escape_string($con, $column);
    $res = mysqli_query($con, "SHOW COLUMNS FROM `$tableEsc` LIKE '$colEsc'");
    if ($res === false) {
        return false;
    }
    return mysqli_num_rows($res) > 0;
}

ensure_delete_cost_log_table($con);

$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$from = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
$to = isset($_GET['to']) ? trim((string)$_GET['to']) : '';

$where = [];
if ($search !== '') {
    $s = mysqli_real_escape_string($con, $search);
    $where[] = "(cost_id LIKE '%$s%' OR cost_name LIKE '%$s%' OR note LIKE '%$s%')";
}

$dateColumn = table_has_column($con, 'delete_cost_log', 'delete_date') ? 'delete_date' : 'date';
if ($from !== '') {
    $f = mysqli_real_escape_string($con, $from);
    $where[] = "DATE($dateColumn) >= '$f'";
}
if ($to !== '') {
    $t = mysqli_real_escape_string($con, $to);
    $where[] = "DATE($dateColumn) <= '$t'";
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$res = mysqli_query($con, "SELECT * FROM delete_cost_log $whereSql ORDER BY id DESC");
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'db_error' => mysqli_error($con)]);
    exit;
}

$rows = [];
while ($r = mysqli_fetch_assoc($res)) {
    $rows[] = $r;
}

echo json_encode(['ok' => true, 'costs' => $rows]);
