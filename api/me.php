<?php
error_reporting(0);
header('Content-Type: application/json');

@include(__DIR__ . '/../config.php');
@include(__DIR__ . '/_auth.php');

require_login();

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

function ensure_users_table(mysqli $con): void
{
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
}

ensure_users_table($con);

$role = auth_user_role();
$userId = auth_user_id();
$email = isset($_SESSION['email']) ? (string)$_SESSION['email'] : '';

if ($role === 'superadmin') {
    echo json_encode([
        'ok' => true,
        'user' => [
            'id' => $userId,
            'email' => $email,
            'role' => 'superadmin',
            'status' => 1,
            'is_online' => 1
        ]
    ]);
    exit;
}

$idNum = (int)$userId;
if ($idNum <= 0) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Invalid session']);
    exit;
}

$res = mysqli_query($con, "SELECT id,email,role,status,is_online FROM users WHERE id=$idNum LIMIT 1");
$row = $res ? mysqli_fetch_assoc($res) : null;
if (!$row) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'User not found']);
    exit;
}

$statusRaw = $row['status'] ?? 0;
$statusStr = strtolower(trim((string)$statusRaw));
$statusOut = ((string)$statusRaw === '1' || (int)$statusRaw === 1 || $statusStr === 'active' || $statusStr === 'aktiv') ? 1 : 0;

echo json_encode([
    'ok' => true,
    'user' => [
        'id' => (int)$row['id'],
        'email' => $row['email'],
        'role' => $row['role'],
        'status' => (int)$statusOut,
        'is_online' => (int)$row['is_online']
    ]
]);
