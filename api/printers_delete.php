<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

@include(__DIR__ . '/../config.php');
@include(__DIR__ . '/_auth.php');

require_role(['superadmin']);

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

@mysqli_query(
    $con,
    "CREATE TABLE IF NOT EXISTS `printers` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `price_key` VARCHAR(50) NULL,
        `status` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uniq_printer_name` (`name`),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

function table_exists(mysqli $con, string $table): bool
{
    $t = mysqli_real_escape_string($con, $table);
    $res = mysqli_query($con, "SHOW TABLES LIKE '$t'");
    return $res && mysqli_num_rows($res) > 0;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) $data = $_POST;

$id = isset($data['id']) ? (int)$data['id'] : 0;
if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'id tələb olunur']);
    exit;
}

$res = mysqli_query($con, "SELECT id, name, price_key FROM printers WHERE id=$id LIMIT 1");
$row = $res ? mysqli_fetch_assoc($res) : null;
if (!$row) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Printer tapılmadı']);
    exit;
}

$nameEsc = mysqli_real_escape_string($con, (string)$row['name']);
$priceKey = trim((string)($row['price_key'] ?? ''));
$priceKeyEsc = mysqli_real_escape_string($con, $priceKey);

if (table_exists($con, 'materials')) {
    $matRes = mysqli_query($con, "SELECT COUNT(*) AS c FROM materials WHERE category IN ('$nameEsc', '$priceKeyEsc')");
    $matRow = $matRes ? mysqli_fetch_assoc($matRes) : null;
    $matCount = $matRow ? (int)$matRow['c'] : 0;
    if ($matCount > 0) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'Bu printerə bağlı materiallar var. Əvvəl materialları silin.']);
        exit;
    }
}

mysqli_begin_transaction($con);
try {
    if (table_exists($con, 'client_price_profiles')) {
        $delProfiles = mysqli_query($con, "DELETE FROM client_price_profiles WHERE printer_key IN ('$nameEsc', '$priceKeyEsc')");
        if ($delProfiles === false) {
            throw new Exception('Delete client price profiles failed: ' . mysqli_error($con));
        }
    }

    $del = mysqli_query($con, "DELETE FROM printers WHERE id=$id LIMIT 1");
    if ($del === false) {
        throw new Exception('Delete printer failed: ' . mysqli_error($con));
    }

    mysqli_commit($con);
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    mysqli_rollback($con);
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Delete failed', 'db_error' => $e->getMessage()]);
}
