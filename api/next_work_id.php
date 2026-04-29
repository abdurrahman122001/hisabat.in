<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

@include(__DIR__ . '/../config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

function ensure_work_table(mysqli $con): void
{
    @mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `work` (
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
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function ensure_work_column(mysqli $con, string $column, string $definition): void
{
    $col = mysqli_real_escape_string($con, $column);
    $res = @mysqli_query($con, "SHOW COLUMNS FROM `work` LIKE '$col'");
    if ($res && mysqli_num_rows($res) > 0) {
        return;
    }
    @mysqli_query($con, "ALTER TABLE `work` ADD COLUMN $definition");
}

ensure_work_table($con);
// In case an older schema exists
ensure_work_column($con, 'op_id', "`op_id` VARCHAR(50) NULL");
ensure_work_column($con, 'total_amount', "`total_amount` DECIMAL(12,2) NULL");
ensure_work_column($con, 'price_per_m2', "`price_per_m2` DECIMAL(12,4) NULL");

function generate_next_work_id(mysqli $con): string
{
    $prefix = 'WRK ';
    $last = 'WRK 0000';

    $res = mysqli_query($con, "SELECT op_id FROM work WHERE op_id IS NOT NULL AND op_id != '' ORDER BY id DESC LIMIT 1");
    if ($res && ($row = mysqli_fetch_assoc($res)) && !empty($row['op_id'])) {
        $last = (string)$row['op_id'];
    }

    $num = 0;
    if (preg_match('/(\d+)/', $last, $m)) {
        $num = (int)$m[1];
    }
    $next = $num + 1;

    return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
}

echo json_encode(['ok' => true, 'next_work_id' => generate_next_work_id($con)]);
