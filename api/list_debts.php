<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

@include(__DIR__ . '/../config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

function ensure_clients_table(mysqli $con): void
{
    @mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `clients` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `client_id` VARCHAR(50) NOT NULL,
            `name` VARCHAR(255) NULL,
            `email` VARCHAR(255) NULL,
            `phone` VARCHAR(50) NULL,
            `date` VARCHAR(50) NULL,
            `advanced` DECIMAL(12,2) NULL,
            `outstanding_debit` DECIMAL(12,2) NULL,
            `total_amount` DECIMAL(12,2) NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `client_id` (`client_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function ensure_clients_column(mysqli $con, string $column, string $definition): void
{
    $columnEsc = mysqli_real_escape_string($con, $column);
    $res = @mysqli_query($con, "SHOW COLUMNS FROM `clients` LIKE '$columnEsc'");
    if ($res && mysqli_num_rows($res) > 0) {
        return;
    }
    @mysqli_query($con, "ALTER TABLE `clients` ADD COLUMN $definition");
}

ensure_clients_table($con);
ensure_clients_column($con, 'advanced', "`advanced` DECIMAL(12,2) NULL");
ensure_clients_column($con, 'outstanding_debit', "`outstanding_debit` DECIMAL(12,2) NULL");
ensure_clients_column($con, 'total_amount', "`total_amount` DECIMAL(12,2) NULL");

@mysqli_query(
    $con,
    "CREATE TABLE IF NOT EXISTS `work` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `client_id` VARCHAR(50) NOT NULL,
        `work` VARCHAR(255) NULL,
        `size_h` DECIMAL(10,2) NULL,
        `size_w` DECIMAL(10,2) NULL,
        `piece` INT NULL,
        `material` VARCHAR(100) NULL,
        `printer` VARCHAR(50) NULL,
        `date` VARCHAR(50) NULL,
        `op_id` VARCHAR(50) NULL,
        `total_amount` DECIMAL(12,2) NULL,
        `price_per_m2` DECIMAL(12,4) NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

@mysqli_query(
    $con,
    "CREATE TABLE IF NOT EXISTS `payment` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `client_id` VARCHAR(50) NOT NULL,
        `name` VARCHAR(255) NULL,
        `email` VARCHAR(255) NULL,
        `phone` VARCHAR(50) NULL,
        `total_amount` DECIMAL(12,2) NULL,
        `paid` DECIMAL(12,2) NULL,
        `outstanding_debet` DECIMAL(12,2) NULL,
        `advanced` DECIMAL(12,2) NULL,
        `date` VARCHAR(50) NULL,
        `operation_id` VARCHAR(50) NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$from = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
$to = isset($_GET['to']) ? trim((string)$_GET['to']) : '';

$as_of = '';
if ($to !== '') {
    $as_of = $to;
} elseif ($from !== '') {
    $as_of = $from;
}

$where = [];
if ($search !== '') {
    $s = mysqli_real_escape_string($con, $search);
    $where[] = "(c.client_id COLLATE utf8mb4_general_ci LIKE '%$s%' OR c.name COLLATE utf8mb4_general_ci LIKE '%$s%' OR c.phone COLLATE utf8mb4_general_ci LIKE '%$s%' OR c.email COLLATE utf8mb4_general_ci LIKE '%$s%')";
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$asOfEsc = mysqli_real_escape_string($con, $as_of);
$workDateExpr = "COALESCE(STR_TO_DATE(work.date, '%Y-%m-%d'), STR_TO_DATE(work.date, '%d.%m.%Y'))";
$payDateExpr = "COALESCE(STR_TO_DATE(payment.date, '%Y-%m-%d'), STR_TO_DATE(payment.date, '%d.%m.%Y'))";

$workAsOfWhere = '';
$payAsOfWhere = '';
if ($as_of !== '') {
    $workAsOfWhere = "WHERE $workDateExpr IS NOT NULL AND DATE($workDateExpr) <= '$asOfEsc'";
    $payAsOfWhere = "WHERE $payDateExpr IS NOT NULL AND DATE($payDateExpr) <= '$asOfEsc'";
}

$sql = "SELECT
    c.client_id,
    c.name,
    c.phone,
    c.email,
    CAST(GREATEST(COALESCE(p.total_paid,0) - COALESCE(w.total_work,0), 0) AS DECIMAL(12,2)) AS advanced,
    CAST(GREATEST(COALESCE(w.total_work,0) - COALESCE(p.total_paid,0), 0) AS DECIMAL(12,2)) AS outstanding_debit,
    CAST(COALESCE(w.total_work,0) AS DECIMAL(12,2)) AS total_amount,
    c.date
  FROM clients c
  LEFT JOIN (
    SELECT work_client_totals.client_id, COALESCE(SUM(CEIL(work_client_totals.op_total)),0) AS total_work
    FROM (
      SELECT client_id, op_id, SUM(total_amount) AS op_total
      FROM work
      $workAsOfWhere
      GROUP BY client_id, op_id
    ) work_client_totals
    GROUP BY work_client_totals.client_id
  ) w ON (w.client_id COLLATE utf8mb4_general_ci = c.client_id COLLATE utf8mb4_general_ci)
  LEFT JOIN (
    SELECT client_id, COALESCE(SUM(paid),0) AS total_paid
    FROM payment
    $payAsOfWhere
    GROUP BY client_id
  ) p ON (p.client_id COLLATE utf8mb4_general_ci = c.client_id COLLATE utf8mb4_general_ci)
  $whereSql
  ORDER BY outstanding_debit DESC, c.client_id DESC";
$res = mysqli_query($con, $sql);
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'db_error' => mysqli_error($con)]);
    exit;
}

$debtRows = [];
$advanceRows = [];
while ($r = mysqli_fetch_assoc($res)) {
    $outstanding = (float)($r['outstanding_debit'] ?? 0);
    $advanced = (float)($r['advanced'] ?? 0);

    if ($outstanding > 0) {
        $debtRows[] = $r;
    }

    if ($advanced > 0) {
        $advanceRows[] = $r;
    }
}

echo json_encode(['ok' => true, 'clients' => $debtRows, 'advances' => $advanceRows]);
