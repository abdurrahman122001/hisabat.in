<?php
/**
 * Master Migration Script
 * This script ensures all required tables are created in the database.
 */

require_once __DIR__ . '/config.php';

if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.\n");
}

if (!$con || $con->connect_errno) {
    die("Database connection failed: " . ($con ? $con->connect_error : 'Unknown error') . "\n");
}

echo "Starting master migration...\n";

// 1. Admin table (required by login.php)
echo "- Creating 'admin' table...\n";
mysqli_query($con, "CREATE TABLE IF NOT EXISTS `admin` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 2. Users table
echo "- Creating 'users' table...\n";
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

// 3. Clients table
echo "- Creating 'clients' table...\n";
mysqli_query($con, "CREATE TABLE IF NOT EXISTS `clients` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `client_id` VARCHAR(50) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(50) NULL,
    `date` DATE NULL,
    `banner_matt` DECIMAL(12,4) DEFAULT 0,
    `banner_glossy` DECIMAL(12,4) DEFAULT 0,
    `vinily_ch` DECIMAL(12,4) DEFAULT 0,
    `vinily_eu` DECIMAL(12,4) DEFAULT 0,
    `banner_black_mate` DECIMAL(12,4) DEFAULT 0,
    `banner_black_glossy` DECIMAL(12,4) DEFAULT 0,
    `white_banner` DECIMAL(12,4) DEFAULT 0,
    `white_vinily` DECIMAL(12,4) DEFAULT 0,
    `backlead` DECIMAL(12,4) DEFAULT 0,
    `flex` DECIMAL(12,4) DEFAULT 0,
    `banner_440_white` DECIMAL(12,4) DEFAULT 0,
    `banner_440_black` DECIMAL(12,4) DEFAULT 0,
    `roland_banner_matt` DECIMAL(12,4) DEFAULT 0,
    `roland_banner_glossy` DECIMAL(12,4) DEFAULT 0,
    `roland_vinily_ch` DECIMAL(12,4) DEFAULT 0,
    `roland_vinily_eu` DECIMAL(12,4) DEFAULT 0,
    `roland_black_matt` DECIMAL(12,4) DEFAULT 0,
    `roland_black_glossy` DECIMAL(12,4) DEFAULT 0,
    `cut_wood` DECIMAL(12,4) DEFAULT 0,
    `cut_forex` DECIMAL(12,4) DEFAULT 0,
    `cut_orch` DECIMAL(12,4) DEFAULT 0,
    `graw_wood` DECIMAL(12,4) DEFAULT 0,
    `graw_forex` DECIMAL(12,4) DEFAULT 0,
    `graw_orch` DECIMAL(12,4) DEFAULT 0,
    `advanced` DECIMAL(12,2) DEFAULT 0,
    `outstanding_debit` DECIMAL(12,2) DEFAULT 0,
    `total_amount` DECIMAL(12,2) DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_client_id` (`client_id`),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 4. Client Price Profiles
echo "- Creating 'client_price_profiles' table...\n";
mysqli_query($con, "CREATE TABLE IF NOT EXISTS `client_price_profiles` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `client_id` VARCHAR(50) NOT NULL,
    `printer_key` VARCHAR(50) NOT NULL,
    `material_key` VARCHAR(100) NOT NULL,
    `price` DECIMAL(12,4) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_client_price_profile` (`client_id`,`printer_key`,`material_key`),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 5. Work table
echo "- Creating 'work' table...\n";
mysqli_query($con, "CREATE TABLE IF NOT EXISTS `work` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `client_id` VARCHAR(50) NOT NULL,
    `work` VARCHAR(255) NULL,
    `size_h` DECIMAL(10,2) NULL,
    `size_w` DECIMAL(10,2) NULL,
    `piece` INT NULL,
    `material` VARCHAR(100) NULL,
    `printer` VARCHAR(50) NULL,
    `date` VARCHAR(50) NULL,
    `op_id` VARCHAR(50) NULL,
    `total_amount` DECIMAL(12,2) NULL,
    `price_per_m2` DECIMAL(12,4) NULL,
    `created_by` INT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 6. In Stock table
echo "- Creating 'in_stock' table...\n";
mysqli_query($con, "CREATE TABLE IF NOT EXISTS `in_stock` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `product` VARCHAR(255) NULL,
    `sgrm` DECIMAL(12,2) NULL,
    `date` VARCHAR(50) NULL,
    `note` VARCHAR(255) NULL,
    `op_id` VARCHAR(50) NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 7. Payment table
echo "- Creating 'payment' table...\n";
mysqli_query($con, "CREATE TABLE IF NOT EXISTS `payment` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `client_id` VARCHAR(50) NOT NULL,
    `name` VARCHAR(255) NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(50) NULL,
    `total_amount` DECIMAL(12,2) NULL,
    `paid` DECIMAL(12,2) NULL,
    `outstanding_debet` DECIMAL(12,2) NULL,
    `advanced` DECIMAL(12,2) NULL,
    `date` VARCHAR(50) NULL,
    `operation_id` VARCHAR(50) NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 8. Cost table
echo "- Creating 'cost' table...\n";
mysqli_query($con, "CREATE TABLE IF NOT EXISTS `cost` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `client_name` VARCHAR(255) NULL,
    `client_id` VARCHAR(50) NOT NULL,
    `date` VARCHAR(50) NULL,
    `amount` DECIMAL(12,2) NULL,
    `note` TEXT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 9. Materials table
echo "- Creating 'materials' table...\n";
mysqli_query($con, "CREATE TABLE IF NOT EXISTS `materials` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `mat_key` VARCHAR(100) NOT NULL,
    `label` VARCHAR(255) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `stock_margin` DECIMAL(10,4) NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_material_key` (`mat_key`,`category`),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 10. Printers table
echo "- Creating 'printers' table...\n";
mysqli_query($con, "CREATE TABLE IF NOT EXISTS `printers` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `price_key` VARCHAR(50) NULL,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_printer_name` (`name`),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 11. Log tables
echo "- Creating log tables...\n";
mysqli_query($con, "CREATE TABLE IF NOT EXISTS `delete_client_log` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `client_id` VARCHAR(50) NOT NULL,
    `name` VARCHAR(255) NULL,
    `date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($con, "CREATE TABLE IF NOT EXISTS `delete_work_log` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `work_id` INT NOT NULL,
    `op_id` VARCHAR(50) NULL,
    `date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($con, "CREATE TABLE IF NOT EXISTS `delete_payment_log` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `payment_id` INT NOT NULL,
    `client_id` VARCHAR(50) NULL,
    `date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($con, "CREATE TABLE IF NOT EXISTS `delete_stock_log` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `stock_id` INT NOT NULL,
    `product` VARCHAR(255) NULL,
    `date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo "Migration completed successfully.\n";
