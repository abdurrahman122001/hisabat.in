<?php
error_reporting(0);
header('Content-Type: application/json');

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
    "CREATE TABLE IF NOT EXISTS `materials` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `mat_key` VARCHAR(100) NOT NULL,
        `label` VARCHAR(255) NOT NULL,
        `category` VARCHAR(50) NOT NULL,
        `status` TINYINT(1) NOT NULL DEFAULT 1,
        `stock_margin` DECIMAL(10, 2) NULL DEFAULT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uniq_material_key` (`mat_key`,`category`),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) $data = $_POST;

$id = isset($data['id']) ? (int)$data['id'] : 0;
$label = array_key_exists('label', $data) ? trim((string)$data['label']) : null;
$status = array_key_exists('status', $data) ? (int)$data['status'] : null;
$stockMargin = array_key_exists('stock_margin', $data) ? (float)$data['stock_margin'] : null;

if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'id tələb olunur']);
    exit;
}

$updates = [];
if ($label !== null) {
    if ($label === '') {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'label boş ola bilməz']);
        exit;
    }
    $labelEsc = mysqli_real_escape_string($con, $label);
    $updates[] = "label='$labelEsc'";
}
if ($stockMargin !== null) {
    if ($stockMargin < 0) {
        $stockMargin = 0.0;
    }
    $stockMarginEsc = mysqli_real_escape_string($con, (string)$stockMargin);
    $updates[] = "stock_margin='$stockMarginEsc'";
}
if ($status !== null) {
    $status = ($status === 1) ? 1 : 0;
    $updates[] = "status=$status";
}

if (empty($updates)) {
    echo json_encode(['ok' => true]);
    exit;
}

$sql = "UPDATE materials SET " . implode(',', $updates) . " WHERE id=$id";
$res = mysqli_query($con, $sql);
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Update failed', 'db_error' => mysqli_error($con)]);
    exit;
}

echo json_encode(['ok' => true]);
