<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

@include(__DIR__ . '/../config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

function ensure_in_stock_table(mysqli $con): void
{
    @mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `in_stock` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `product` VARCHAR(255) NULL,
            `sgrm` DECIMAL(12,2) NULL,
            `date` VARCHAR(50) NULL,
            `note` VARCHAR(255) NULL,
            `op_id` VARCHAR(50) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function ensure_in_stock_column(mysqli $con, string $column, string $definition): void
{
    $col = mysqli_real_escape_string($con, $column);
    $res = @mysqli_query($con, "SHOW COLUMNS FROM `in_stock` LIKE '$col'");
    if ($res && mysqli_num_rows($res) > 0) return;
    @mysqli_query($con, "ALTER TABLE `in_stock` ADD COLUMN $definition");
}

ensure_in_stock_table($con);
ensure_in_stock_column($con, 'note', "`note` VARCHAR(255) NULL");
ensure_in_stock_column($con, 'op_id', "`op_id` VARCHAR(50) NULL");

$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$from = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
$to = isset($_GET['to']) ? trim((string)$_GET['to']) : '';

$where = [];
if ($search !== '') {
    $s = mysqli_real_escape_string($con, $search);
    $where[] = "(product LIKE '%$s%' OR op_id LIKE '%$s%' OR note LIKE '%$s%')";
}
if ($from !== '') {
    $f = mysqli_real_escape_string($con, $from);
    $where[] = "DATE(date) >= '$f'";
}
if ($to !== '') {
    $t = mysqli_real_escape_string($con, $to);
    $where[] = "DATE(date) <= '$t'";
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "SELECT id, op_id, product, sgrm, date, note FROM in_stock $whereSql ORDER BY id DESC";
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

echo json_encode(['ok' => true, 'stock' => $rows]);
