<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

@include(__DIR__ . '/../config.php');
@include(__DIR__ . '/_auth.php');

require_role(['superadmin','admin','user']);

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

function ensure_work_column(mysqli $con, string $column, string $definition): void
{
    $col = mysqli_real_escape_string($con, $column);
    $res = @mysqli_query($con, "SHOW COLUMNS FROM `work` LIKE '$col'");
    if ($res && mysqli_num_rows($res) > 0) {
        return;
    }
    @mysqli_query($con, "ALTER TABLE `work` ADD COLUMN $definition");
}

function created_by_value(): string
{
    $role = auth_user_role();
    if ($role === 'superadmin') {
        return 'NULL';
    }
    $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if ($uid > 0) {
        return (string)$uid;
    }
    return 'NULL';
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

function recalc_client_balance(mysqli $con, string $client_id, string $date, string $op_id): void
{
    $client_id_esc = mysqli_real_escape_string($con, $client_id);

    $resWork = mysqli_query($con, "SELECT COALESCE(CEIL(SUM(total_amount)),0) AS total_work FROM work WHERE client_id='$client_id_esc'");
    $workRow = $resWork ? mysqli_fetch_assoc($resWork) : null;
    $total_work = $workRow ? (float)$workRow['total_work'] : 0.0;

    $resPaid = mysqli_query($con, "SELECT COALESCE(SUM(paid),0) AS total_paid FROM payment WHERE client_id='$client_id_esc'");
    $paidRow = $resPaid ? mysqli_fetch_assoc($resPaid) : null;
    $total_paid = $paidRow ? (float)$paidRow['total_paid'] : 0.0;

    $new_outstanding = $total_work - $total_paid;
    $outstanding = $new_outstanding < 0 ? 0 : $new_outstanding;
    $advanced = $new_outstanding < 0 ? abs($new_outstanding) : 0;

    $outstandingEsc = mysqli_real_escape_string($con, (string)$outstanding);
    $advancedEsc = mysqli_real_escape_string($con, (string)$advanced);
    $totalWorkEsc = mysqli_real_escape_string($con, (string)$total_work);

    @mysqli_query(
        $con,
        "UPDATE clients SET 
            total_amount='$totalWorkEsc',
            outstanding_debit='$outstandingEsc',
            advanced='$advancedEsc'
        WHERE client_id='$client_id_esc'"
    );

    $clientRes = mysqli_query($con, "SELECT name,email,phone FROM clients WHERE client_id='$client_id_esc' LIMIT 1");
    $client = $clientRes ? mysqli_fetch_assoc($clientRes) : null;
    $nameEsc = mysqli_real_escape_string($con, (string)($client['name'] ?? ''));
    $emailEsc = mysqli_real_escape_string($con, (string)($client['email'] ?? ''));
    $phoneEsc = mysqli_real_escape_string($con, (string)($client['phone'] ?? ''));
    $dateEsc = mysqli_real_escape_string($con, $date);
    $opEsc = mysqli_real_escape_string($con, $op_id);

    $latestRes = mysqli_query($con, "SELECT id FROM payment WHERE client_id='$client_id_esc' ORDER BY id DESC LIMIT 1");
    $latest = $latestRes ? mysqli_fetch_assoc($latestRes) : null;
    if ($latest && isset($latest['id'])) {
        $pid = (int)$latest['id'];
        @mysqli_query(
            $con,
            "UPDATE payment SET 
                total_amount='$totalWorkEsc',
                outstanding_debet='$outstandingEsc',
                advanced='$advancedEsc'
            WHERE id=$pid"
        );
    } else {
        @mysqli_query(
            $con,
            "INSERT INTO payment (client_id,name,email,phone,total_amount,paid,outstanding_debet,advanced,date,operation_id)
            VALUES ('$client_id_esc','$nameEsc','$emailEsc','$phoneEsc','$totalWorkEsc','0','$outstandingEsc','$advancedEsc','$dateEsc','$opEsc')"
        );
    }
}

ensure_work_table($con);
ensure_work_column($con, 'created_by', "`created_by` INT NULL");
ensure_payment_table($con);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

$client_id = trim((string)($data['client_id'] ?? ''));
$work_name = trim((string)($data['work_name'] ?? ''));
$date = trim((string)($data['date'] ?? ''));
$op_id = trim((string)($data['op_id'] ?? ''));
$printer = trim((string)($data['printer'] ?? ''));
$total_amount = (float)str_replace(',', '.', (string)($data['total_amount'] ?? '0'));

$errors = [];
if ($client_id === '') $errors['client_id'] = 'client_id required';
if ($work_name === '') $errors['work_name'] = 'work_name required';
if ($date === '') $errors['date'] = 'date required';
if ($op_id === '') $errors['op_id'] = 'op_id required';
if ($total_amount <= 0) $errors['total_amount'] = 'total_amount must be > 0';

$today = date('Y-m-d');
if ($date !== '' && $date < $today) {
    $errors['date'] = 'Past dates are not allowed';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

$clientEsc = mysqli_real_escape_string($con, $client_id);
$workEsc = mysqli_real_escape_string($con, $work_name);
$dateEsc = mysqli_real_escape_string($con, $date);
$opEsc = mysqli_real_escape_string($con, $op_id);
$printerEsc = mysqli_real_escape_string($con, $printer);
$totalEsc = mysqli_real_escape_string($con, (string)$total_amount);

$createdBySql = created_by_value();

$res = mysqli_query(
    $con,
    "INSERT INTO work (client_id, work, size_h, size_w, piece, material, printer, date, op_id, total_amount, price_per_m2, created_by)
     VALUES ('$clientEsc', '$workEsc', NULL, NULL, 0, NULL, '$printerEsc', '$dateEsc', '$opEsc', '$totalEsc', NULL, $createdBySql)"
);

if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Insert failed', 'db_error' => mysqli_error($con)]);
    exit;
}

recalc_client_balance($con, $client_id, $date, $op_id);

echo json_encode([
    'ok' => true,
    'op_id' => $op_id,
    'total_raw' => $total_amount,
    'total_ceiled' => (int)ceil($total_amount)
]);
