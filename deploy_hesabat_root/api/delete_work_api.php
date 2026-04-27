<?php
error_reporting(0);
header('Content-Type: application/json');

@include(__DIR__ . '/../config.php');
@include(__DIR__ . '/_auth.php');

require_role(['superadmin','admin']);

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

function has_clients_table(mysqli $con): bool
{
    $res = @mysqli_query($con, "SHOW TABLES LIKE 'clients'");
    return $res && mysqli_num_rows($res) > 0;
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

function ensure_delete_work_log_table(mysqli $con): void
{
    @mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `delete_work_log` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `client_id` VARCHAR(50) NULL,
            `client_name` VARCHAR(255) NULL,
            `work_name` VARCHAR(255) NULL,
            `size_h` VARCHAR(50) NULL,
            `size_w` VARCHAR(50) NULL,
            `piece` VARCHAR(50) NULL,
            `material` VARCHAR(100) NULL,
            `printer` VARCHAR(50) NULL,
            `date` VARCHAR(50) NULL,
            `op_id` VARCHAR(50) NULL,
            `total_amount` VARCHAR(50) NULL,
            `deleted_at` VARCHAR(50) NULL,
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
    $advanced = $new_outstanding < 0 ? abs($new_outstanding) : 0;

    $outstandingEsc = mysqli_real_escape_string($con, (string)$outstanding);
    $advancedEsc = mysqli_real_escape_string($con, (string)$advanced);
    $totalWorkEsc = mysqli_real_escape_string($con, (string)$total_work);

    @mysqli_query(
        $con,
        "UPDATE clients SET total_amount='$totalWorkEsc', outstanding_debit='$outstandingEsc', advanced='$advancedEsc' WHERE client_id='$client_id_esc'"
    );

    @mysqli_query(
        $con,
        "UPDATE payment SET total_amount='$totalWorkEsc', outstanding_debet='$outstandingEsc', advanced='$advancedEsc' WHERE client_id='$client_id_esc'"
    );
}

ensure_work_table($con);
ensure_delete_work_log_table($con);
ensure_payment_table($con);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$op_id = isset($data['op_id']) ? trim((string)$data['op_id']) : '';
if ($op_id === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => ['op_id' => 'op_id tələb olunur']]);
    exit;
}

$op_id_esc = mysqli_real_escape_string($con, $op_id);

$sql = '';
if (has_clients_table($con)) {
    $sql = "SELECT work.*, clients.name AS client_name 
            FROM work 
            INNER JOIN clients ON (clients.client_id COLLATE utf8mb4_general_ci = work.client_id COLLATE utf8mb4_general_ci)
            WHERE work.op_id='$op_id_esc'";
} else {
    $sql = "SELECT work.*, NULL AS client_name FROM work WHERE work.op_id='$op_id_esc'";
}
$res = mysqli_query($con, $sql);
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'db_error' => mysqli_error($con)]);
    exit;
}

$rows = [];
$client_id = null;
while ($r = mysqli_fetch_assoc($res)) {
    $rows[] = $r;
    $client_id = $r['client_id'];
}

if (empty($rows)) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Work not found']);
    exit;
}

$deletedAt = date('Y-m-d H:i:s');
$deletedAtEsc = mysqli_real_escape_string($con, $deletedAt);

mysqli_begin_transaction($con);
try {
    foreach ($rows as $row) {
        $clientIdEsc = mysqli_real_escape_string($con, (string)$row['client_id']);
        $clientNameEsc = mysqli_real_escape_string($con, (string)$row['client_name']);
        $workNameEsc = mysqli_real_escape_string($con, (string)$row['work']);
        $sizeHEsc = mysqli_real_escape_string($con, (string)$row['size_h']);
        $sizeWEsc = mysqli_real_escape_string($con, (string)$row['size_w']);
        $pieceEsc = mysqli_real_escape_string($con, (string)$row['piece']);
        $materialEsc = mysqli_real_escape_string($con, (string)$row['material']);
        $printerEsc = mysqli_real_escape_string($con, (string)$row['printer']);
        $dateEsc = mysqli_real_escape_string($con, (string)$row['date']);
        $opIdEsc = mysqli_real_escape_string($con, (string)$row['op_id']);
        $totalAmountEsc = mysqli_real_escape_string($con, (string)$row['total_amount']);

        $ins = mysqli_query(
            $con,
            "INSERT INTO delete_work_log (client_id, client_name, work_name, size_h, size_w, piece, material, printer, date, op_id, total_amount, deleted_at)
             VALUES ('$clientIdEsc','$clientNameEsc','$workNameEsc','$sizeHEsc','$sizeWEsc','$pieceEsc','$materialEsc','$printerEsc','$dateEsc','$opIdEsc','$totalAmountEsc','$deletedAtEsc')"
        );
        if ($ins === false) {
            throw new Exception('Log insert failed: ' . mysqli_error($con));
        }
    }

    $del = mysqli_query($con, "DELETE FROM work WHERE op_id='$op_id_esc'");
    if ($del === false) {
        throw new Exception('Delete failed: ' . mysqli_error($con));
    }

    if ($client_id) {
        recalc_client_balance($con, (string)$client_id);
    }

    mysqli_commit($con);
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    mysqli_rollback($con);
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Delete failed', 'db_error' => $e->getMessage()]);
}
