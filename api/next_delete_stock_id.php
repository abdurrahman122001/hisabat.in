<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

@include(__DIR__ . '/../config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

function extract_num(string $id): int
{
    if (preg_match('/(\d{1,})/', $id, $m)) {
        return (int)$m[1];
    }
    return 0;
}

function table_exists(mysqli $con, string $table): bool
{
    $t = mysqli_real_escape_string($con, $table);
    $res = mysqli_query($con, "SHOW TABLES LIKE '$t'");
    return $res && mysqli_num_rows($res) > 0;
}

function next_delete_stock_id(mysqli $con): string
{
    $prefix = 'DSK-';
    $max = 0;

    if (table_exists($con, 'delete_stock_log')) {
        $res = mysqli_query($con, "SELECT stock_id FROM delete_stock_log");
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $id = (string)($row['stock_id'] ?? '');
                if (stripos($id, $prefix) === 0) {
                    $n = extract_num($id);
                    if ($n > $max) $max = $n;
                }
            }
        }
    }

    $next = $max + 1;
    return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
}

echo json_encode(['ok' => true, 'next_delete_stock_id' => next_delete_stock_id($con)]);
