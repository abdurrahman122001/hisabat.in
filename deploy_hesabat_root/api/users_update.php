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
    "CREATE TABLE IF NOT EXISTS `users` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `email` VARCHAR(255) NOT NULL,
        `password_hash` VARCHAR(255) NOT NULL,
        `role` VARCHAR(50) NOT NULL DEFAULT 'user',
        `status` TINYINT(1) NOT NULL DEFAULT 1,
        `is_online` TINYINT(1) NOT NULL DEFAULT 0,
        `last_seen` DATETIME NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uniq_users_email` (`email`),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$hasHash = mysqli_query($con, "SHOW COLUMNS FROM `users` LIKE 'password_hash'");
$hasHashOk = ($hasHash && mysqli_num_rows($hasHash) > 0);
if (!$hasHashOk) {
    @mysqli_query($con, "ALTER TABLE `users` ADD COLUMN `password_hash` VARCHAR(255) NOT NULL DEFAULT ''");
    $hasLegacyPassword = mysqli_query($con, "SHOW COLUMNS FROM `users` LIKE 'password'");
    if ($hasLegacyPassword && mysqli_num_rows($hasLegacyPassword) > 0) {
        @mysqli_query($con, "UPDATE `users` SET `password_hash`=`password` WHERE (`password_hash` IS NULL OR `password_hash`='') AND `password` IS NOT NULL AND `password`<>''");
    }
}

$hasRole = mysqli_query($con, "SHOW COLUMNS FROM `users` LIKE 'role'");
if (!($hasRole && mysqli_num_rows($hasRole) > 0)) {
    @mysqli_query($con, "ALTER TABLE `users` ADD COLUMN `role` VARCHAR(50) NOT NULL DEFAULT 'user'");
}
$hasStatus = mysqli_query($con, "SHOW COLUMNS FROM `users` LIKE 'status'");
if (!($hasStatus && mysqli_num_rows($hasStatus) > 0)) {
    @mysqli_query($con, "ALTER TABLE `users` ADD COLUMN `status` TINYINT(1) NOT NULL DEFAULT 1");
}
$hasIsOnline = mysqli_query($con, "SHOW COLUMNS FROM `users` LIKE 'is_online'");
if (!($hasIsOnline && mysqli_num_rows($hasIsOnline) > 0)) {
    @mysqli_query($con, "ALTER TABLE `users` ADD COLUMN `is_online` TINYINT(1) NOT NULL DEFAULT 0");
}
$hasLastSeen = mysqli_query($con, "SHOW COLUMNS FROM `users` LIKE 'last_seen'");
if (!($hasLastSeen && mysqli_num_rows($hasLastSeen) > 0)) {
    @mysqli_query($con, "ALTER TABLE `users` ADD COLUMN `last_seen` DATETIME NULL");
}
$hasCreatedAt = mysqli_query($con, "SHOW COLUMNS FROM `users` LIKE 'created_at'");
if (!($hasCreatedAt && mysqli_num_rows($hasCreatedAt) > 0)) {
    @mysqli_query($con, "ALTER TABLE `users` ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$id = isset($data['id']) ? (int)$data['id'] : 0;
$role = isset($data['role']) ? trim((string)$data['role']) : null;
$status = array_key_exists('status', $data) ? (int)$data['status'] : null;
$is_online = array_key_exists('is_online', $data) ? (int)$data['is_online'] : null;
$password = array_key_exists('password', $data) ? (string)$data['password'] : null;

if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'id tələb olunur']);
    exit;
}

$updates = [];
if ($role !== null) {
    if ($role !== 'admin' && $role !== 'user' && $role !== 'superadmin') {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'role yalnız superadmin/admin/user ola bilər']);
        exit;
    }
    $roleEsc = mysqli_real_escape_string($con, $role);
    $updates[] = "role='$roleEsc'";
}
if ($status !== null) {
    $status = ($status === 1) ? 1 : 0;
    $updates[] = "status=$status";
}
if ($is_online !== null) {
    $is_online = ($is_online === 1) ? 1 : 0;
    $updates[] = "is_online=$is_online";
    $updates[] = "last_seen=NOW()";
}
if ($password !== null && $password !== '') {
    if (strlen($password) < 4) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'Şifrə minimum 4 simvol olmalıdır']);
        exit;
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $hashEsc = mysqli_real_escape_string($con, $hash);
    $updates[] = "password_hash='$hashEsc'";
}

if (empty($updates)) {
    echo json_encode(['ok' => true]);
    exit;
}

$sql = "UPDATE users SET " . implode(',', $updates) . " WHERE id=$id";
$res = mysqli_query($con, $sql);
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Update failed', 'db_error' => mysqli_error($con)]);
    exit;
}

echo json_encode(['ok' => true]);
