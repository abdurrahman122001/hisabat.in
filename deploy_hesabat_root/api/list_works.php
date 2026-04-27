<?php
error_reporting(0);
header('Content-Type: application/json');

@include(__DIR__ . '/../config.php');
@include(__DIR__ . '/_auth.php');

require_login();

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Database connection failed',
        'db_error' => $con instanceof mysqli ? $con->connect_error : null
    ]);
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
ensure_work_column($con, 'price_per_m2', "`price_per_m2` DECIMAL(12,4) NULL");
ensure_work_column($con, 'created_by', "`created_by` INT NULL");

$hasClientsTable = false;
$clientsCheck = @mysqli_query($con, "SHOW TABLES LIKE 'clients'");
if ($clientsCheck && mysqli_num_rows($clientsCheck) > 0) {
    $hasClientsTable = true;
}

$hasDistanceColumn = false;
$distCol = @mysqli_query($con, "SHOW COLUMNS FROM `work` LIKE 'distance_m'");
if ($distCol && mysqli_num_rows($distCol) > 0) {
    $hasDistanceColumn = true;
}

$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$from = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
$to = isset($_GET['to']) ? trim((string)$_GET['to']) : '';

function normalize_input_date_to_ymd(string $value): string
{
    if ($value === '') {
        return '';
    }

    $dt = DateTime::createFromFormat('Y-m-d', $value);
    if ($dt instanceof DateTime) {
        return $dt->format('Y-m-d');
    }

    $dt = DateTime::createFromFormat('m/d/Y', $value);
    if ($dt instanceof DateTime) {
        return $dt->format('Y-m-d');
    }

    $dt = DateTime::createFromFormat('n/j/Y', $value);
    if ($dt instanceof DateTime) {
        return $dt->format('Y-m-d');
    }

    $dt = DateTime::createFromFormat('d.m.Y', $value);
    if ($dt instanceof DateTime) {
        return $dt->format('Y-m-d');
    }

    return $value;
}

$from = normalize_input_date_to_ymd($from);
$to = normalize_input_date_to_ymd($to);

$where = [];

// Role-based restriction: normal users only see their own works
$role = auth_user_role();
if ($role === 'user') {
    $uid = auth_user_id_int();
    if ($uid <= 0) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Invalid session']);
        exit;
    }
    $where[] = 'work.created_by = ' . (int)$uid;
}
if ($search !== '') {
    $s = mysqli_real_escape_string($con, $search);
    if ($hasClientsTable) {
        $where[] = "(clients.phone COLLATE utf8mb4_general_ci LIKE '%$s%' OR work.client_id COLLATE utf8mb4_general_ci LIKE '%$s%' OR work.work COLLATE utf8mb4_general_ci LIKE '%$s%' OR work.material COLLATE utf8mb4_general_ci LIKE '%$s%' OR work.printer COLLATE utf8mb4_general_ci LIKE '%$s%' OR work.op_id COLLATE utf8mb4_general_ci LIKE '%$s%' OR CAST(work.size_h AS CHAR) LIKE '%$s%' OR CAST(work.size_w AS CHAR) LIKE '%$s%' OR CAST(work.piece AS CHAR) LIKE '%$s%')";
    } else {
        $where[] = "(work.client_id COLLATE utf8mb4_general_ci LIKE '%$s%' OR work.work COLLATE utf8mb4_general_ci LIKE '%$s%' OR work.material COLLATE utf8mb4_general_ci LIKE '%$s%' OR work.printer COLLATE utf8mb4_general_ci LIKE '%$s%' OR work.op_id COLLATE utf8mb4_general_ci LIKE '%$s%' OR CAST(work.size_h AS CHAR) LIKE '%$s%' OR CAST(work.size_w AS CHAR) LIKE '%$s%' OR CAST(work.piece AS CHAR) LIKE '%$s%')";
    }
}

$workDateExpr = "COALESCE(STR_TO_DATE(work.date, '%Y-%m-%d'), STR_TO_DATE(work.date, '%d.%m.%Y'))";

if ($from !== '' && $to !== '') {
    $f = mysqli_real_escape_string($con, $from);
    $t = mysqli_real_escape_string($con, $to);
    $where[] = "($workDateExpr IS NOT NULL AND DATE($workDateExpr) BETWEEN '$f' AND '$t')";
} elseif ($from !== '') {
    $f = mysqli_real_escape_string($con, $from);
    $where[] = "($workDateExpr IS NOT NULL AND DATE($workDateExpr) >= '$f')";
} elseif ($to !== '') {
    $t = mysqli_real_escape_string($con, $to);
    $where[] = "($workDateExpr IS NOT NULL AND DATE($workDateExpr) <= '$t')";
}

if (empty($where)) {
    $today = date('Y-m-d');
    $todayEsc = mysqli_real_escape_string($con, $today);
    $where[] = "(work.date = '$todayEsc' OR work.date LIKE '$todayEsc%' OR DATE(STR_TO_DATE(work.date, '%d.%m.%Y')) = CURDATE() OR DATE(STR_TO_DATE(work.date, '%Y-%m-%d')) = CURDATE())";
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

$sql = '';
if ($hasClientsTable) {
    $sql = "SELECT 
                work.op_id,
                work.client_id,
                clients.name AS client_name,
                clients.phone AS phone,
                MAX(work.work) AS work_name,
                MAX(work.date) AS date,
                " . ($hasDistanceColumn ? "SUM(COALESCE(work.distance_m,0))" : "NULL") . " AS total_distance_m,
                SUM((work.size_w/100) * (work.size_h/100) * work.piece) AS total_m2,
                SUM(work.piece) AS sum_piece,
                SUM(work.total_amount) AS total_raw,
                CEIL(SUM(work.total_amount)) AS total_ceiled,
                GROUP_CONCAT(DISTINCT work.size_w ORDER BY work.id SEPARATOR ', ') AS widths_cm,
                GROUP_CONCAT(DISTINCT work.size_h ORDER BY work.id SEPARATOR ', ') AS heights_cm,
                GROUP_CONCAT(DISTINCT CONCAT(work.size_w, 'x', work.size_h) ORDER BY work.id SEPARATOR ', ') AS sizes_cm,
                GROUP_CONCAT(DISTINCT work.price_per_m2 ORDER BY work.price_per_m2 SEPARATOR ', ') AS prices_per_m2,
                GROUP_CONCAT(DISTINCT work.material ORDER BY work.material SEPARATOR ', ') AS materials,
                GROUP_CONCAT(DISTINCT work.printer ORDER BY work.printer SEPARATOR ', ') AS printers
            FROM work
            INNER JOIN clients ON (clients.client_id COLLATE utf8mb4_general_ci = work.client_id COLLATE utf8mb4_general_ci)
            $whereSql
            GROUP BY work.op_id, work.client_id
            ORDER BY MAX(work.id) DESC";
} else {
    $sql = "SELECT 
                work.op_id,
                work.client_id,
                NULL AS client_name,
                NULL AS phone,
                MAX(work.work) AS work_name,
                MAX(work.date) AS date,
                " . ($hasDistanceColumn ? "SUM(COALESCE(work.distance_m,0))" : "NULL") . " AS total_distance_m,
                SUM((work.size_w/100) * (work.size_h/100) * work.piece) AS total_m2,
                SUM(work.piece) AS sum_piece,
                SUM(work.total_amount) AS total_raw,
                CEIL(SUM(work.total_amount)) AS total_ceiled,
                GROUP_CONCAT(DISTINCT work.size_w ORDER BY work.id SEPARATOR ', ') AS widths_cm,
                GROUP_CONCAT(DISTINCT work.size_h ORDER BY work.id SEPARATOR ', ') AS heights_cm,
                GROUP_CONCAT(DISTINCT CONCAT(work.size_w, 'x', work.size_h) ORDER BY work.id SEPARATOR ', ') AS sizes_cm,
                GROUP_CONCAT(DISTINCT work.price_per_m2 ORDER BY work.price_per_m2 SEPARATOR ', ') AS prices_per_m2,
                GROUP_CONCAT(DISTINCT work.material ORDER BY work.material SEPARATOR ', ') AS materials,
                GROUP_CONCAT(DISTINCT work.printer ORDER BY work.printer SEPARATOR ', ') AS printers
            FROM work
            $whereSql
            GROUP BY work.op_id, work.client_id
            ORDER BY MAX(work.id) DESC";
}

$res = mysqli_query($con, $sql);
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'db_error' => mysqli_error($con)]);
    exit;
}

$rows = [];
while ($r = mysqli_fetch_assoc($res)) {
    $rows[] = [
        'op_id' => $r['op_id'],
        'client_id' => $r['client_id'],
        'client_name' => $r['client_name'],
        'phone' => $r['phone'],
        'work_name' => $r['work_name'],
        'date' => $r['date'],
        'total_distance_m' => $r['total_distance_m'] === null ? null : (float)$r['total_distance_m'],
        'total_m2' => (float)$r['total_m2'],
        'sum_piece' => (int)$r['sum_piece'],
        'total_raw' => (float)$r['total_raw'],
        'total_ceiled' => (int)$r['total_ceiled'],
        'widths_cm' => $r['widths_cm'],
        'heights_cm' => $r['heights_cm'],
        'sizes_cm' => $r['sizes_cm'],
        'prices_per_m2' => $r['prices_per_m2'],
        'materials' => $r['materials'],
        'printers' => $r['printers']
    ];
}

echo json_encode(['ok' => true, 'works' => $rows]);
