<?php
error_reporting(0);
header('Content-Type: application/json');

@include(__DIR__ . '/../config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
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

function recalc_client_balance(mysqli $con, string $client_id): array
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
    $avans = $new_outstanding < 0 ? abs($new_outstanding) : 0;

    $outstandingEsc = mysqli_real_escape_string($con, (string)$outstanding);
    $avansEsc = mysqli_real_escape_string($con, (string)$avans);
    $totalWorkEsc = mysqli_real_escape_string($con, (string)$total_work);

    @mysqli_query(
        $con,
        "UPDATE clients SET total_amount='$totalWorkEsc', outstanding_debit='$outstandingEsc', advanced='$avansEsc' WHERE client_id='$client_id_esc'"
    );

    @mysqli_query(
        $con,
        "UPDATE payment SET total_amount='$totalWorkEsc', outstanding_debet='$outstandingEsc', advanced='$avansEsc' WHERE client_id='$client_id_esc'"
    );

    return [
        'total_work' => $total_work,
        'total_paid' => $total_paid,
        'outstanding_debit' => $outstanding,
        'avans' => $avans
    ];
}

ensure_payment_table($con);
ensure_payment_log_table($con);
ensure_payment_log_column($con, 'payment_id', "`payment_id` INT NULL");
ensure_work_table($con);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$client_id = isset($data['client_id']) ? trim((string)$data['client_id']) : '';
$paidRaw = isset($data['paid']) ? (string)$data['paid'] : '';
$date = isset($data['date']) ? trim((string)$data['date']) : '';

if ($date === '') {
    $date = date('Y-m-d');
}

$today = date('Y-m-d');
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && $date < $today) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => ['date' => 'Keçmiş tarix seçmək olmaz']]);
    exit;
}

$paid = (float)str_replace(',', '.', $paidRaw);

$errors = [];
if ($client_id === '') $errors['client_id'] = 'Müştəri ID tələb olunur';
if (!is_numeric(str_replace(',', '.', $paidRaw)) || $paid <= 0) $errors['paid'] = 'Ödəniş məbləği düzgün deyil';

if ($client_id !== '') {
    $client_id_esc = mysqli_real_escape_string($con, $client_id);
    $exists = mysqli_query($con, "SELECT client_id, name, email, phone FROM clients WHERE client_id='$client_id_esc' LIMIT 1");
    if (!$exists || mysqli_num_rows($exists) === 0) {
        $errors['client_id'] = 'Müştəri tapılmadı';
    }
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

$client_id_esc = mysqli_real_escape_string($con, $client_id);
$clientRes = mysqli_query($con, "SELECT client_id, name, email, phone FROM clients WHERE client_id='$client_id_esc' LIMIT 1");
$client = $clientRes ? mysqli_fetch_assoc($clientRes) : null;

$nameEsc = mysqli_real_escape_string($con, (string)($client['name'] ?? ''));
$emailEsc = mysqli_real_escape_string($con, (string)($client['email'] ?? ''));
$phoneEsc = mysqli_real_escape_string($con, (string)($client['phone'] ?? ''));
$dateEsc = mysqli_real_escape_string($con, $date);
$paidEsc = mysqli_real_escape_string($con, (string)$paid);

mysqli_begin_transaction($con);

try {
    $ins = mysqli_query(
        $con,
        "INSERT INTO payment (client_id, name, email, phone, paid, date) VALUES ('$client_id_esc','$nameEsc','$emailEsc','$phoneEsc','$paidEsc','$dateEsc')"
    );
    if ($ins === false) {
        throw new Exception('Insert failed: ' . mysqli_error($con));
    }

    $paymentId = (int)mysqli_insert_id($con);

    $summary = recalc_client_balance($con, $client_id);

    $createdAtEsc = mysqli_real_escape_string($con, date('Y-m-d H:i:s'));
    $twEsc = mysqli_real_escape_string($con, (string)$summary['total_work']);
    $tpEsc = mysqli_real_escape_string($con, (string)$summary['total_paid']);
    $odEsc = mysqli_real_escape_string($con, (string)$summary['outstanding_debit']);
    $avEsc = mysqli_real_escape_string($con, (string)$summary['avans']);

    $log = mysqli_query(
        $con,
        "INSERT INTO payment_log (payment_id, client_id, name, email, phone, paid, date, total_work, total_paid, outstanding_debit, avans, created_at)
         VALUES ($paymentId,'$client_id_esc','$nameEsc','$emailEsc','$phoneEsc','$paidEsc','$dateEsc','$twEsc','$tpEsc','$odEsc','$avEsc','$createdAtEsc')"
    );
    if ($log === false) {
        throw new Exception('Log insert failed: ' . mysqli_error($con));
    }

    mysqli_commit($con);

    echo json_encode([
        'ok' => true,
        'client_id' => $client_id,
        'summary' => $summary
    ]);
} catch (Exception $e) {
    mysqli_rollback($con);
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Payment create failed', 'db_error' => $e->getMessage()]);
}
