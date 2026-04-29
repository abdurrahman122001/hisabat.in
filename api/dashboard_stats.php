<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

@include(__DIR__ . '/../config.php');
@include(__DIR__ . '/_auth.php');

require_login();

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

function table_exists(mysqli $con, string $table): bool
{
    $t = mysqli_real_escape_string($con, $table);
    $res = mysqli_query($con, "SHOW TABLES LIKE '$t'");
    return $res && mysqli_num_rows($res) > 0;
}

function has_column(mysqli $con, string $table, string $column): bool
{
    $t = mysqli_real_escape_string($con, $table);
    $c = mysqli_real_escape_string($con, $column);
    $res = @mysqli_query($con, "SHOW COLUMNS FROM `$t` LIKE '$c'");
    return $res && mysqli_num_rows($res) > 0;
}

// normalize date string for comparisons (supports 'Y-m-d', 'd.m.Y', 'Y-m-d H:i:s')
function date_expr(string $col): string
{
    return "COALESCE(" .
        "STR_TO_DATE($col, '%Y-%m-%d')," .
        "STR_TO_DATE($col, '%Y-%m-%d %H:%i:%s')," .
        "STR_TO_DATE($col, '%d.%m.%Y')" .
    ")";
}

$monthStart = date('Y-m-01');
$monthEnd = date('Y-m-t');
$ms = mysqli_real_escape_string($con, $monthStart);
$me = mysqli_real_escape_string($con, $monthEnd);

// 1) Active customers
$active_customers = 0;
$active_clients = [];
if (table_exists($con, 'clients')) {
    $resCount = mysqli_query($con, "SELECT COUNT(*) AS c FROM clients");
    $row = $resCount ? mysqli_fetch_assoc($resCount) : null;
    $active_customers = $row ? (int)$row['c'] : 0;

    $resList = mysqli_query($con, "SELECT client_id, name, email, phone FROM clients ORDER BY client_id DESC LIMIT 10");
    if ($resList) {
        while ($r = mysqli_fetch_assoc($resList)) {
            $active_clients[] = [
                'client_id' => $r['client_id'],
                'name' => $r['name'],
                'email' => $r['email'],
                'phone' => $r['phone']
            ];
        }
    }
}

// 2) Monthly works + m2 + laser distance
$monthly_works = 0;
$monthly_work_m2 = 0.0;
$monthly_laser_distance_m = 0.0;
if (table_exists($con, 'work')) {
    $workDateExpr = date_expr('work.date');
    $hasDist = has_column($con, 'work', 'distance_m');

    $role = function_exists('auth_user_role') ? auth_user_role() : '';
    $userId = function_exists('auth_user_id_int') ? auth_user_id_int() : 0;
    $hasCreatedBy = has_column($con, 'work', 'created_by');

    // If we cannot reliably filter by ownership, do not leak global stats to normal users.
    if ($role === 'user' && (!$hasCreatedBy || $userId <= 0)) {
        $monthly_works = 0;
        $monthly_work_m2 = 0.0;
        $monthly_laser_distance_m = 0.0;
    } else {
        $createdBySql = '';
        if ($role === 'user' && $hasCreatedBy && $userId > 0) {
            $createdBySql = " AND work.created_by = $userId";
        }

        $sql = "SELECT
                    COUNT(DISTINCT work.op_id) AS work_count,
                    COALESCE(SUM((work.size_w/100) * (work.size_h/100) * work.piece),0) AS total_m2,
                    " . ($hasDist ? "COALESCE(SUM(COALESCE(work.distance_m,0)),0)" : "0") . " AS total_distance_m
                FROM work
                WHERE DATE($workDateExpr) BETWEEN '$ms' AND '$me'$createdBySql";

        $res = mysqli_query($con, $sql);
        $r = $res ? mysqli_fetch_assoc($res) : null;
        if ($r) {
            $monthly_works = (int)$r['work_count'];
            $monthly_work_m2 = (float)$r['total_m2'];
            $monthly_laser_distance_m = (float)$r['total_distance_m'];
        }
    }
}

// 3) Total stock m2
$total_stock_m2 = 0.0;
if (table_exists($con, 'in_stock')) {
    $res = mysqli_query($con, "SELECT COALESCE(SUM(sgrm),0) AS total_sgrm FROM in_stock");
    $r = $res ? mysqli_fetch_assoc($res) : null;
    $total_stock_m2 = $r ? (float)$r['total_sgrm'] : 0.0;
}

// 4) Monthly payments total
$monthly_payments_total = 0.0;
if (table_exists($con, 'payment')) {
    $payDateExpr = date_expr('payment.date');
    $res = mysqli_query(
        $con,
        "SELECT COALESCE(SUM(paid),0) AS total_paid
         FROM payment
         WHERE DATE($payDateExpr) BETWEEN '$ms' AND '$me'"
    );
    $r = $res ? mysqli_fetch_assoc($res) : null;
    $monthly_payments_total = $r ? (float)$r['total_paid'] : 0.0;
}

echo json_encode([
    'ok' => true,
    'month' => [
        'from' => $monthStart,
        'to' => $monthEnd
    ],
    'stats' => [
        'active_customers' => $active_customers,
        'monthly_works' => $monthly_works,
        'monthly_work_m2' => round($monthly_work_m2, 2),
        'monthly_laser_distance_m' => round($monthly_laser_distance_m, 2),
        'total_stock_m2' => round($total_stock_m2, 2),
        'monthly_payments_total' => round($monthly_payments_total, 2)
    ],
    'active_clients' => $active_clients
]);
