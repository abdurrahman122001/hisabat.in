<?php
error_reporting(0);
header('Content-Type: application/json');

@include(__DIR__ . '/../config.php');
@include(__DIR__ . '/_auth.php');

function printer_price_key_from_name(string $name): string
{
    $name = strtolower(trim($name));
    if ($name === '') {
        return '';
    }

    $name = preg_replace('/[^a-z0-9]+/', '_', $name);
    return trim((string)$name, '_');
}

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

$name = isset($data['name']) ? trim((string)$data['name']) : '';
$price_key = isset($data['price_key']) ? trim((string)$data['price_key']) : '';
if ($price_key === '' && $name !== '') {
    $price_key = printer_price_key_from_name($name);
}
$status = isset($data['status']) ? (int)$data['status'] : 1;

$errors = [];
if ($name === '') $errors['name'] = 'Printer adı tələb olunur';
$price_key = preg_replace('/\s+/', '_', $price_key);
$status = ($status === 1) ? 1 : 0;

if ($name !== '') {
    $nameEscCheck = mysqli_real_escape_string($con, $name);
    $dupNameRes = mysqli_query($con, "SELECT id FROM printers WHERE name='$nameEscCheck' LIMIT 1");
    if ($dupNameRes && mysqli_num_rows($dupNameRes) > 0) {
        $errors['name'] = 'Bu printer artıq mövcuddur';
    }
}
if ($price_key !== '') {
    $pkEscCheck = mysqli_real_escape_string($con, $price_key);
    $dupPkRes = mysqli_query($con, "SELECT id FROM printers WHERE price_key='$pkEscCheck' LIMIT 1");
    if ($dupPkRes && mysqli_num_rows($dupPkRes) > 0) {
        $errors['price_key'] = 'Bu price_key artıq mövcuddur';
    }
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

$nameEsc = mysqli_real_escape_string($con, $name);
$pkEsc = $price_key === '' ? 'NULL' : ("'" . mysqli_real_escape_string($con, $price_key) . "'");

$ins = mysqli_query($con, "INSERT INTO printers (name, price_key, status) VALUES ('$nameEsc', $pkEsc, $status)");
if ($ins === false) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Insert failed', 'db_error' => mysqli_error($con)]);
    exit;
}

echo json_encode(['ok' => true, 'id' => (int)mysqli_insert_id($con)]);
