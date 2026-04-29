<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

@include(__DIR__ . '/../config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
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

function ensure_delete_client_log_table(mysqli $con): void
{
    @mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `delete_client_log` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `client_id` VARCHAR(50) NOT NULL,
            `name` VARCHAR(255) NULL,
            `email` VARCHAR(255) NULL,
            `phone` VARCHAR(50) NULL,
            `debit` VARCHAR(50) NULL,
            `create_date` VARCHAR(50) NULL,
            `delete_date` VARCHAR(50) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

ensure_delete_client_log_table($con);

$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$from = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
$to = isset($_GET['to']) ? trim((string)$_GET['to']) : '';

$where = [];
if ($search !== '') {
    $s = mysqli_real_escape_string($con, $search);
    $where[] = "(client_id LIKE '%$s%' OR name LIKE '%$s%' OR phone LIKE '%$s%' OR email LIKE '%$s%')";
}
$dateColumn = table_has_column($con, 'delete_client_log', 'delete_date') ? 'delete_date' : 'create_date';
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

$res = mysqli_query($con, "SELECT * FROM delete_client_log $whereSql ORDER BY id DESC");
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'db_error' => mysqli_error($con)]);
    exit;
}

$rows = [];
while ($r = mysqli_fetch_assoc($res)) {
    $rows[] = $r;
}

echo json_encode(['ok' => true, 'clients' => $rows]);
