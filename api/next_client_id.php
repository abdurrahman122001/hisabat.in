<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

@include(__DIR__ . '/../config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

function generate_next_client_id(mysqli $con): string
{
    $prefix = 'CWZ ';
    $maxNum = 0;

    $res = mysqli_query($con, "SELECT client_id FROM clients");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $cid = (string)($row['client_id'] ?? '');
            if (preg_match('/(\d+)/', $cid, $m)) {
                $n = (int)$m[1];
                if ($n > $maxNum) $maxNum = $n;
            }
        }
    }

    $hasDelLog = mysqli_query($con, "SHOW TABLES LIKE 'delete_client_log'");
    if ($hasDelLog && mysqli_num_rows($hasDelLog) > 0) {
        $res2 = mysqli_query($con, "SELECT client_id FROM delete_client_log");
        if ($res2) {
            while ($row2 = mysqli_fetch_assoc($res2)) {
                $cid2 = (string)($row2['client_id'] ?? '');
                if (preg_match('/(\d+)/', $cid2, $m2)) {
                    $n2 = (int)$m2[1];
                    if ($n2 > $maxNum) $maxNum = $n2;
                }
            }
        }
    }

    $next = $maxNum + 1;
    return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
}

echo json_encode(['ok' => true, 'next_client_id' => generate_next_client_id($con)]);
