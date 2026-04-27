<?php
/**
 * Admin User Seeder Script
 * Usage: php create_admin.php <email> <password>
 */

require_once __DIR__ . '/config.php';

if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.\n");
}

if ($argc < 3) {
    echo "Usage: php create_admin.php <email> <password>\n";
    exit(1);
}

$email = $argv[1];
$password = $argv[2];
$role = 'superadmin';

if (!$con || $con->connect_errno) {
    echo "Database connection failed: " . ($con ? $con->connect_error : 'Unknown error') . "\n";
    exit(1);
}

// Ensure table and columns exist (same logic as api/users_create.php)
mysqli_query($con, "CREATE TABLE IF NOT EXISTS `users` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Check for password_hash column
$hasHash = mysqli_query($con, "SHOW COLUMNS FROM `users` LIKE 'password_hash'");
if (!($hasHash && mysqli_num_rows($hasHash) > 0)) {
    mysqli_query($con, "ALTER TABLE `users` ADD COLUMN `password_hash` VARCHAR(255) NOT NULL DEFAULT ''");
}

// Check for role column
$hasRole = mysqli_query($con, "SHOW COLUMNS FROM `users` LIKE 'role'");
if (!($hasRole && mysqli_num_rows($hasRole) > 0)) {
    mysqli_query($con, "ALTER TABLE `users` ADD COLUMN `role` VARCHAR(50) NOT NULL DEFAULT 'user'");
}

$emailEsc = mysqli_real_escape_string($con, $email);
$hash = password_hash($password, PASSWORD_DEFAULT);
$hashEsc = mysqli_real_escape_string($con, $hash);
$roleEsc = mysqli_real_escape_string($con, $role);

$check = mysqli_query($con, "SELECT id FROM users WHERE email = '$emailEsc'");
if ($check && mysqli_num_rows($check) > 0) {
    echo "User with email '$email' already exists. Updating password and setting role to 'superadmin'...\n";
    $update = mysqli_query($con, "UPDATE users SET password_hash = '$hashEsc', role = '$roleEsc', status = 1 WHERE email = '$emailEsc'");
    if ($update) {
        echo "Admin user updated successfully.\n";
    } else {
        echo "Update failed: " . mysqli_error($con) . "\n";
    }
} else {
    echo "Creating new admin user with email '$email'...\n";
    $insert = mysqli_query($con, "INSERT INTO users (email, password_hash, role, status, is_online, last_seen) VALUES ('$emailEsc', '$hashEsc', '$roleEsc', 1, 0, NOW())");
    if ($insert) {
        echo "Admin user created successfully.\n";
    } else {
        echo "Insert failed: " . mysqli_error($con) . "\n";
    }
}
?>
