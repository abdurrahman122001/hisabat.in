<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

@include(__DIR__ . '/../config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$date = isset($_GET['date']) ? trim((string)$_GET['date']) : '';

$where = [];
if ($search !== '') {
    $search_esc = mysqli_real_escape_string($con, $search);
    $where[] = "(name LIKE '%$search_esc%' OR client_id LIKE '%$search_esc%' OR phone LIKE '%$search_esc%' OR email LIKE '%$search_esc%')";
}
if ($date !== '') {
    $date_esc = mysqli_real_escape_string($con, $date);
    $where[] = "DATE(date) = '$date_esc'";
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "SELECT client_id, name, phone, email, date FROM clients $whereSql ORDER BY client_id DESC";
$res = mysqli_query($con, $sql);
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'db_error' => mysqli_error($con)]);
    exit;
}

$clients = [];
while ($row = mysqli_fetch_assoc($res)) {
    $clients[] = [
        'client_id' => $row['client_id'],
        'name' => $row['name'],
        'phone' => $row['phone'],
        'email' => $row['email'],
        'date' => $row['date'],
    ];
}

echo json_encode(['ok' => true, 'clients' => $clients]);
