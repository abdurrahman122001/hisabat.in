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

function ensure_delete_cost_log_table(mysqli $con): void
{
    @mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `delete_cost_log` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `cost_id` VARCHAR(50) NOT NULL,
            `amount` VARCHAR(50) NULL,
            `date` VARCHAR(50) NULL,
            `note` TEXT NULL,
            `cost_name` VARCHAR(255) NULL,
            `delete_date` VARCHAR(50) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
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

ensure_delete_cost_log_table($con);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$cost_id = isset($data['cost_id']) ? trim((string)$data['cost_id']) : '';
if ($cost_id === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => ['cost_id' => 'cost_id tələb olunur']]);
    exit;
}

$cost_id_esc = mysqli_real_escape_string($con, $cost_id);
$res = mysqli_query($con, "SELECT * FROM cost WHERE client_id = '$cost_id_esc' LIMIT 1");
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'db_error' => mysqli_error($con)]);
    exit;
}

$row = mysqli_fetch_assoc($res);
if (!$row) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Cost not found']);
    exit;
}

$logCostId = mysqli_real_escape_string($con, (string)$row['client_id']);
$logAmount = mysqli_real_escape_string($con, (string)$row['amount']);
$logDate = mysqli_real_escape_string($con, (string)$row['date']);
$logNote = mysqli_real_escape_string($con, (string)$row['note']);
$logName = mysqli_real_escape_string($con, (string)$row['client_name']);

$logError = null;
if (table_has_column($con, 'delete_cost_log', 'delete_date')) {
    $deleteDate = mysqli_real_escape_string($con, date('Y-m-d H:i:s'));
    $ins = mysqli_query($con, "INSERT INTO delete_cost_log (cost_id, amount, date, note, cost_name, delete_date) VALUES ('$logCostId','$logAmount','$logDate','$logNote','$logName','$deleteDate')");
    if ($ins === false) $logError = mysqli_error($con);
} else {
    $ins = mysqli_query($con, "INSERT INTO delete_cost_log (cost_id, amount, date, note, cost_name) VALUES ('$logCostId','$logAmount','$logDate','$logNote','$logName')");
    if ($ins === false) $logError = mysqli_error($con);
}

$del = mysqli_query($con, "DELETE FROM cost WHERE client_id = '$cost_id_esc'");
if ($del === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Delete failed', 'db_error' => mysqli_error($con)]);
    exit;
}

echo json_encode(['ok' => true, 'log_error' => $logError]);
