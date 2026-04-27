<?php
error_reporting(0);
header('Content-Type: application/json');

@include(__DIR__ . '/../config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

function ensure_payment_log_table(mysqli $con): void
{
    @mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `payment_log` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `payment_id` INT NULL,
            `client_id` VARCHAR(50) NOT NULL,
            `name` VARCHAR(255) NULL,
            `email` VARCHAR(255) NULL,
            `phone` VARCHAR(50) NULL,
            `paid` DECIMAL(12,2) NOT NULL,
            `date` VARCHAR(50) NULL,
            `total_work` DECIMAL(12,2) NULL,
            `total_paid` DECIMAL(12,2) NULL,
            `outstanding_debit` DECIMAL(12,2) NULL,
            `avans` DECIMAL(12,2) NULL,
            `created_at` VARCHAR(50) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function ensure_payment_log_column(mysqli $con, string $column, string $definition): void
{
    $col = mysqli_real_escape_string($con, $column);
    $res = @mysqli_query($con, "SHOW COLUMNS FROM `payment_log` LIKE '$col'");
    if ($res && mysqli_num_rows($res) > 0) {
        return;
    }
    @mysqli_query($con, "ALTER TABLE `payment_log` ADD COLUMN $definition");
}

ensure_payment_log_table($con);
ensure_payment_log_column($con, 'payment_id', "`payment_id` INT NULL");

$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$client_id = isset($_GET['client_id']) ? trim((string)$_GET['client_id']) : '';
$from = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
$to = isset($_GET['to']) ? trim((string)$_GET['to']) : '';

function normalize_input_date_to_ymd(string $value): string
{
    if ($value === '') {
        return '';
    }

    $dt = DateTime::createFromFormat('Y-m-d', $value);
    if ($dt instanceof DateTime) {
        return $dt->format('Y-m-d');
    }

    $dt = DateTime::createFromFormat('m/d/Y', $value);
    if ($dt instanceof DateTime) {
        return $dt->format('Y-m-d');
    }

    $dt = DateTime::createFromFormat('n/j/Y', $value);
    if ($dt instanceof DateTime) {
        return $dt->format('Y-m-d');
    }

    $dt = DateTime::createFromFormat('d.m.Y', $value);
    if ($dt instanceof DateTime) {
        return $dt->format('Y-m-d');
    }

    return $value;
}

$from = normalize_input_date_to_ymd($from);
$to = normalize_input_date_to_ymd($to);

$where = [];
if ($search !== '') {
    $s = mysqli_real_escape_string($con, $search);
    $where[] = "(client_id LIKE '%$s%' OR name LIKE '%$s%' OR phone LIKE '%$s%')";
}
if ($client_id !== '') {
    $cid = mysqli_real_escape_string($con, $client_id);
    $where[] = "client_id = '$cid'";
}

$payDateExpr = "COALESCE(STR_TO_DATE(date, '%Y-%m-%d'), STR_TO_DATE(date, '%d.%m.%Y'), STR_TO_DATE(date, '%m/%d/%Y'))";
if ($from !== '') {
    $f = mysqli_real_escape_string($con, $from);
    $where[] = "($payDateExpr IS NOT NULL AND DATE($payDateExpr) >= '$f')";
}
if ($to !== '') {
    $t = mysqli_real_escape_string($con, $to);
    $where[] = "($payDateExpr IS NOT NULL AND DATE($payDateExpr) <= '$t')";
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "SELECT id, payment_id, client_id, name, email, phone, paid, date, total_work, total_paid, outstanding_debit, avans, created_at FROM payment_log $whereSql ORDER BY id DESC";
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

echo json_encode(['ok' => true, 'logs' => $rows]);
