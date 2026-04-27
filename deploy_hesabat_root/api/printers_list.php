<?php
error_reporting(0);
header('Content-Type: application/json');

@include(__DIR__ . '/../config.php');
@include(__DIR__ . '/_auth.php');

require_role(['superadmin','admin','user']);

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

@mysqli_query(
    $con,
    "CREATE TABLE IF NOT EXISTS `printers` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `price_key` VARCHAR(50) NULL,
        `status` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uniq_printer_name` (`name`),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

// Seed defaults if empty
$cntRes = mysqli_query($con, "SELECT COUNT(*) AS c FROM printers");
$cntRow = $cntRes ? mysqli_fetch_assoc($cntRes) : null;
$cnt = $cntRow ? (int)$cntRow['c'] : 0;
if ($cnt === 0) {
    @mysqli_query($con, "INSERT IGNORE INTO printers (name, price_key, status) VALUES
        ('Konica','konica',1),
        ('Roland','roland',1),
        ('Laser','laser',1)");
}

$res = mysqli_query($con, "SELECT id,name,price_key,status FROM printers ORDER BY id ASC");
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'db_error' => mysqli_error($con)]);
    exit;
}

$rows = [];
$seen = [];
while ($r = mysqli_fetch_assoc($res)) {
    $normalized = strtolower(trim((string)($r['price_key'] ?? '')));
    if ($normalized === '') {
        $normalized = strtolower(trim((string)($r['name'] ?? '')));
    }
    if ($normalized !== '' && isset($seen[$normalized])) {
        continue;
    }
    if ($normalized !== '') {
        $seen[$normalized] = true;
    }
    $rows[] = [
        'id' => (int)$r['id'],
        'name' => $r['name'],
        'price_key' => $r['price_key'],
        'status' => (int)$r['status'],
    ];
}

echo json_encode(['ok' => true, 'printers' => $rows]);
