<?php
error_reporting(0);
header('Content-Type: application/json');

@include(__DIR__ . '/../config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

function ensure_cost_table(mysqli $con): void
{
    @mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `cost` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `client_name` VARCHAR(255) NULL,
            `client_id` VARCHAR(50) NOT NULL,
            `date` VARCHAR(50) NULL,
            `amount` DECIMAL(12,2) NULL,
            `note` TEXT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

ensure_cost_table($con);

$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$from = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
$to = isset($_GET['to']) ? trim((string)$_GET['to']) : '';

$where = [];
if ($search !== '') {
    $s = mysqli_real_escape_string($con, $search);
    $where[] = "(client_name LIKE '%$s%' OR client_id LIKE '%$s%' OR note LIKE '%$s%')";
}

if ($from !== '' && $to !== '') {
    $f = mysqli_real_escape_string($con, $from);
    $t = mysqli_real_escape_string($con, $to);
    $where[] = "date BETWEEN '$f' AND '$t'";
} elseif ($from !== '') {
    $f = mysqli_real_escape_string($con, $from);
    $where[] = "date >= '$f'";
} elseif ($to !== '') {
    $t = mysqli_real_escape_string($con, $to);
    $where[] = "date <= '$t'";
}

if (empty($where)) {
    $where[] = "DATE(date) = CURDATE()";
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

$sql = "SELECT id, client_name, client_id, date, amount, note FROM cost $whereSql ORDER BY id DESC";
$res = mysqli_query($con, $sql);
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'db_error' => mysqli_error($con)]);
    exit;
}

$rows = [];
while ($r = mysqli_fetch_assoc($res)) {
    $rows[] = [
        'id' => $r['id'],
        'cost_name' => $r['client_name'],
        'cost_id' => $r['client_id'],
        'date' => $r['date'],
        'amount' => $r['amount'],
        'note' => $r['note'],
    ];
}

echo json_encode(['ok' => true, 'costs' => $rows]);
