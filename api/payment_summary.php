<?php
error_reporting(0);
header('Content-Type: application/json');

@include(__DIR__ . '/../config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

function ensure_work_table(mysqli $con): void
{
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
}

function ensure_payment_table(mysqli $con): void
{
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
}

ensure_work_table($con);
ensure_payment_table($con);

$client_id = isset($_GET['client_id']) ? trim((string)$_GET['client_id']) : '';
if ($client_id === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => ['client_id' => 'client_id tələb olunur']]);
    exit;
}

$client_id_esc = mysqli_real_escape_string($con, $client_id);

$clientRes = mysqli_query($con, "SELECT client_id, name, email, phone FROM clients WHERE client_id='$client_id_esc' LIMIT 1");
if ($clientRes === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'db_error' => mysqli_error($con)]);
    exit;
}
$client = mysqli_fetch_assoc($clientRes);
if (!$client) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Client not found']);
    exit;
}

$resWork = mysqli_query($con, "SELECT COALESCE((SELECT SUM(CEIL(op_total)) FROM (SELECT op_id, SUM(total_amount) AS op_total FROM work WHERE client_id='$client_id_esc' GROUP BY op_id) work_ops),0) AS total_work FROM work WHERE client_id='$client_id_esc'");
$workRow = $resWork ? mysqli_fetch_assoc($resWork) : null;
$total_work = $workRow ? (float)$workRow['total_work'] : 0.0;

$resPaid = mysqli_query($con, "SELECT COALESCE(SUM(paid),0) AS total_paid FROM payment WHERE client_id='$client_id_esc'");
$paidRow = $resPaid ? mysqli_fetch_assoc($resPaid) : null;
$total_paid = $paidRow ? (float)$paidRow['total_paid'] : 0.0;

$new_outstanding = $total_work - $total_paid;
$outstanding = $new_outstanding < 0 ? 0 : $new_outstanding;
$avans = $new_outstanding < 0 ? abs($new_outstanding) : 0;

echo json_encode([
    'ok' => true,
    'customer' => [
        'client_id' => $client['client_id'],
        'name' => $client['name'],
        'email' => $client['email'],
        'phone' => $client['phone']
    ],
    'summary' => [
        'total_work' => $total_work,
        'total_paid' => $total_paid,
        'outstanding_debit' => $outstanding,
        'avans' => $avans
    ]
]);
