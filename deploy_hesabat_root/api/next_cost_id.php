<?php
error_reporting(0);
header('Content-Type: application/json');

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

echo json_encode(['ok' => true, 'next_cost_id' => generate_next_cost_id($con)]);
