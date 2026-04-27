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
    // Create the delete log table if it does not exist (matches delete_log.php expectations)
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

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$client_id = isset($data['client_id']) ? trim((string)$data['client_id']) : '';
if ($client_id === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => ['client_id' => 'client_id tələb olunur']]);
    exit;
}

$client_id_esc = mysqli_real_escape_string($con, $client_id);

$res = mysqli_query($con, "SELECT * FROM clients WHERE client_id = '$client_id_esc' LIMIT 1");
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'db_error' => mysqli_error($con)]);
    exit;
}

$row = mysqli_fetch_assoc($res);
if (!$row) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Client not found']);
    exit;
}

$logClientId = mysqli_real_escape_string($con, (string)$row['client_id']);
$logName = mysqli_real_escape_string($con, (string)$row['name']);
$logEmail = mysqli_real_escape_string($con, (string)$row['email']);
$logPhone = mysqli_real_escape_string($con, (string)$row['phone']);
$logDebit = mysqli_real_escape_string($con, (string)$row['outstanding_debit']);
$logCreateDate = mysqli_real_escape_string($con, (string)$row['date']);

$logInsertError = null;
if (table_has_column($con, 'delete_client_log', 'delete_date')) {
    $logDeleteDate = date('Y-m-d H:i:s');
    $logDeleteDateEsc = mysqli_real_escape_string($con, $logDeleteDate);
    $insertLog = mysqli_query(
        $con,
        "INSERT INTO delete_client_log (client_id, name, email, phone, debit, create_date, delete_date) VALUES ('$logClientId','$logName','$logEmail','$logPhone','$logDebit','$logCreateDate','$logDeleteDateEsc')"
    );
    if ($insertLog === false) {
        $logInsertError = mysqli_error($con);
    }
} else {
    $insertLog = mysqli_query(
        $con,
        "INSERT INTO delete_client_log (client_id, name, email, phone, debit, create_date) VALUES ('$logClientId','$logName','$logEmail','$logPhone','$logDebit','$logCreateDate')"
    );
    if ($insertLog === false) {
        $logInsertError = mysqli_error($con);
    }
}

$del = mysqli_query($con, "DELETE FROM clients WHERE client_id = '$client_id_esc'");
if ($del === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Delete failed', 'db_error' => mysqli_error($con)]);
    exit;
}

// Best-effort cleanup (do not fail delete if this fails)
@mysqli_query($con, "DELETE FROM payment WHERE client_id = '$client_id_esc'");

echo json_encode([
    'ok' => true,
    'log_error' => $logInsertError
]);
