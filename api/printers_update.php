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

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) $data = $_POST;

$id = isset($data['id']) ? (int)$data['id'] : 0;
$name = array_key_exists('name', $data) ? trim((string)$data['name']) : null;
$price_key = array_key_exists('price_key', $data) ? trim((string)$data['price_key']) : null;
$status = array_key_exists('status', $data) ? (int)$data['status'] : null;

if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'id tələb olunur']);
    exit;
}

$updates = [];
if ($name !== null) {
    if ($name === '') {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'Printer adı boş ola bilməz']);
        exit;
    }
    $nameEsc = mysqli_real_escape_string($con, $name);
    $updates[] = "name='$nameEsc'";
}
if ($price_key !== null) {
    $price_key = preg_replace('/\s+/', '_', $price_key);
    if ($price_key === '') {
        $updates[] = "price_key=NULL";
    } else {
        $pkEsc = mysqli_real_escape_string($con, $price_key);
        $updates[] = "price_key='$pkEsc'";
    }
}
if ($status !== null) {
    $status = ($status === 1) ? 1 : 0;
    $updates[] = "status=$status";
}

if (empty($updates)) {
    echo json_encode(['ok' => true]);
    exit;
}

$sql = "UPDATE printers SET " . implode(',', $updates) . " WHERE id=$id";
$res = mysqli_query($con, $sql);
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Update failed', 'db_error' => mysqli_error($con)]);
    exit;
}

echo json_encode(['ok' => true]);
