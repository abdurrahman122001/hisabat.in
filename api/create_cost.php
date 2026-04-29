<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

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

function generate_next_cost_id(mysqli $con): string
{
    $prefix = 'CST ';
    $last = 'CST 0000';

    $res = mysqli_query($con, "SELECT client_id FROM cost ORDER BY client_id DESC LIMIT 1");
    if ($res && ($row = mysqli_fetch_assoc($res)) && !empty($row['client_id'])) {
        $last = (string)$row['client_id'];
    }

    $number = 0;
    if (preg_match('/(\d+)/', $last, $m)) {
        $number = (int)$m[1];
    }
    $next = $number + 1;

    return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$cost_name = isset($data['cost_name']) ? trim((string)$data['cost_name']) : '';
$amount_raw = isset($data['amount']) ? (string)$data['amount'] : '';
$note = isset($data['note']) ? trim((string)$data['note']) : '';
$date = isset($data['date']) ? trim((string)$data['date']) : '';

if ($date === '') {
    $date = date('Y-m-d');
}

// Disallow past dates
$today = date('Y-m-d');
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && $date < $today) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => ['date' => 'Keçmiş tarix seçmək olmaz']]);
    exit;
}

$errors = [];
if ($cost_name === '') {
    $errors['cost_name'] = 'Xərc adı doldurulmalıdır';
}

$amount_raw = str_replace(' ', '', $amount_raw);
$amount_raw = str_replace(',', '.', $amount_raw);
$amount = (float)$amount_raw;
if ($amount <= 0) {
    $errors['amount'] = 'Məbləğ düzgün deyil';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

$cost_id = generate_next_cost_id($con);

$cost_id_esc = mysqli_real_escape_string($con, $cost_id);
$cost_name_esc = mysqli_real_escape_string($con, $cost_name);
$note_esc = mysqli_real_escape_string($con, $note);
$date_esc = mysqli_real_escape_string($con, $date);

$sql = "INSERT INTO cost (client_name, client_id, date, amount, note) VALUES ('$cost_name_esc','$cost_id_esc','$date_esc','$amount','$note_esc')";
$res = mysqli_query($con, $sql);
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Insert failed', 'db_error' => mysqli_error($con)]);
    exit;
}

echo json_encode(['ok' => true, 'cost_id' => $cost_id]);
