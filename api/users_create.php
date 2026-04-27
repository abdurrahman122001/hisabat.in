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

$email = isset($data['email']) ? trim((string)$data['email']) : '';
$password = isset($data['password']) ? (string)$data['password'] : '';
$role = isset($data['role']) ? trim((string)$data['role']) : 'user';
$status = isset($data['status']) ? (int)$data['status'] : 1;

$errors = [];
if ($email === '' || strpos($email, '@') === false) $errors['email'] = 'Email düzgün deyil';
if ($password === '' || strlen($password) < 4) $errors['password'] = 'Şifrə minimum 4 simvol olmalıdır';
if ($role !== 'admin' && $role !== 'user' && $role !== 'superadmin') $errors['role'] = 'Role yalnız superadmin/admin/user ola bilər';
if ($status !== 0 && $status !== 1) $status = 1;

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

$emailEsc = mysqli_real_escape_string($con, $email);
$exists = mysqli_query($con, "SELECT 1 FROM users WHERE email='$emailEsc' LIMIT 1");
if ($exists && mysqli_num_rows($exists) > 0) {
    http_response_code(409);
    echo json_encode(['ok' => false, 'error' => 'User artıq mövcuddur']);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$hashEsc = mysqli_real_escape_string($con, $hash);
$roleEsc = mysqli_real_escape_string($con, $role);

$ins = mysqli_query($con, "INSERT INTO users (email,password_hash,role,status,is_online,last_seen) VALUES ('$emailEsc','$hashEsc','$roleEsc',$status,0,NOW())");
if ($ins === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Insert failed', 'db_error' => mysqli_error($con)]);
    exit;
}

$id = mysqli_insert_id($con);

echo json_encode(['ok' => true, 'id' => (int)$id]);
