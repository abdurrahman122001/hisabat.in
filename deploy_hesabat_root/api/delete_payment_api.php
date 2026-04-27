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

function ensure_delete_payment_log_table(mysqli $con): void
{
    @mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `delete_payment_log` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `payment_id` INT NULL,
            `client_id` VARCHAR(50) NULL,
            `name` VARCHAR(255) NULL,
            `phone` VARCHAR(50) NULL,
            `paid` DECIMAL(12,2) NULL,
            `date` VARCHAR(50) NULL,
            `deleted_at` VARCHAR(50) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
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

function recalc_client_balance(mysqli $con, string $client_id): void
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
}

ensure_payment_table($con);
ensure_payment_log_table($con);
ensure_delete_payment_log_table($con);
ensure_work_table($con);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}


$payment_id = isset($data['payment_id']) ? (int)$data['payment_id'] : 0;
$log_id = isset($data['log_id']) ? (int)$data['log_id'] : 0;

if ($payment_id <= 0 && $log_id <= 0) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => ['payment_id' => 'payment_id və ya log_id tələb olunur']]);
    exit;
}

$pidEsc = (int)$payment_id;
$logIdEsc = (int)$log_id;

$log = null;
$pay = null;

// If log_id given, resolve details from payment_log first
if ($logIdEsc > 0) {
    $logRes = mysqli_query($con, "SELECT id, payment_id, client_id, name, phone, paid, date FROM payment_log WHERE id=$logIdEsc LIMIT 1");
    $log = $logRes ? mysqli_fetch_assoc($logRes) : null;
    if ($log && isset($log['payment_id']) && (int)$log['payment_id'] > 0) {
        $pidEsc = (int)$log['payment_id'];
    }
}

// If payment_id given (or resolved), pull payment_log and payment row
if ($pidEsc > 0) {
    $logRes2 = mysqli_query($con, "SELECT id, payment_id, client_id, name, phone, paid, date FROM payment_log WHERE payment_id=$pidEsc ORDER BY id DESC LIMIT 1");
    $log2 = $logRes2 ? mysqli_fetch_assoc($logRes2) : null;
    if ($log2) {
        $log = $log2;
    }
    $payRes = mysqli_query($con, "SELECT id, client_id, name, phone, paid, date FROM payment WHERE id=$pidEsc LIMIT 1");
    $pay = $payRes ? mysqli_fetch_assoc($payRes) : null;
}

// Legacy fallback: if we still don't have a payment row, try to find it by matching fields from log
if (!$pay && $log && !empty($log['client_id'])) {
    $cid = mysqli_real_escape_string($con, (string)$log['client_id']);
    $paidVal = mysqli_real_escape_string($con, (string)$log['paid']);
    $dateVal = mysqli_real_escape_string($con, (string)$log['date']);
    $payRes2 = mysqli_query($con, "SELECT id, client_id, name, phone, paid, date FROM payment WHERE client_id='$cid' AND paid='$paidVal' AND date='$dateVal' ORDER BY id DESC LIMIT 1");
    $pay = $payRes2 ? mysqli_fetch_assoc($payRes2) : null;
    if ($pay && isset($pay['id'])) {
        $pidEsc = (int)$pay['id'];
    }
}

$client_id = (string)($log['client_id'] ?? ($pay['client_id'] ?? ''));
if ($client_id === '') {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Payment not found']);
    exit;
}

$deletedAt = date('Y-m-d H:i:s');
$cidEsc = mysqli_real_escape_string($con, $client_id);
$nameEsc = mysqli_real_escape_string($con, (string)($log['name'] ?? ($pay['name'] ?? '')));
$phoneEsc = mysqli_real_escape_string($con, (string)($log['phone'] ?? ($pay['phone'] ?? '')));
$paidEsc = mysqli_real_escape_string($con, (string)($log['paid'] ?? ($pay['paid'] ?? '0')));
$dateEsc = mysqli_real_escape_string($con, (string)($log['date'] ?? ($pay['date'] ?? '')));
$deletedAtEsc = mysqli_real_escape_string($con, $deletedAt);

mysqli_begin_transaction($con);
try {
    $ins = mysqli_query(
        $con,
        "INSERT INTO delete_payment_log (payment_id, client_id, name, phone, paid, date, deleted_at)
         VALUES ($pidEsc,'$cidEsc','$nameEsc','$phoneEsc','$paidEsc','$dateEsc','$deletedAtEsc')"
    );
    if ($ins === false) {
        throw new Exception('Log insert failed: ' . mysqli_error($con));
    }

    if ($pidEsc > 0) {
        $del1 = mysqli_query($con, "DELETE FROM payment WHERE id=$pidEsc");
        if ($del1 === false) {
            throw new Exception('Delete payment failed: ' . mysqli_error($con));
        }
        // best-effort: also remove history line(s)
        @mysqli_query($con, "DELETE FROM payment_log WHERE payment_id=$pidEsc");
    }

    // legacy: if only log_id was known, remove that one log row too
    if ($logIdEsc > 0) {
        @mysqli_query($con, "DELETE FROM payment_log WHERE id=$logIdEsc");
    }

    recalc_client_balance($con, $client_id);

    mysqli_commit($con);
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    mysqli_rollback($con);
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Delete failed', 'db_error' => $e->getMessage()]);
}
