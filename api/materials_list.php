<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

@include(__DIR__ . '/../config.php');
@include(__DIR__ . '/_auth.php');

function normalize_material_list_category(array $printerCategoryMap, string $category): string
{
    $category = trim($category);
    if ($category === '') {
        return '';
    }

    $lookup = strtolower($category);
    return isset($printerCategoryMap[$lookup]) ? $printerCategoryMap[$lookup] : $lookup;
}

function ensure_material_stock_margin_column(mysqli $con): void
{
    $res = @mysqli_query($con, "SHOW COLUMNS FROM `materials` LIKE 'stock_margin'");
    if ($res && mysqli_num_rows($res) > 0) {
        return;
    }
    @mysqli_query($con, "ALTER TABLE `materials` ADD COLUMN `stock_margin` DECIMAL(10,4) NOT NULL DEFAULT 0 AFTER `category`");
}

require_role(['superadmin','admin','user']);

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

@mysqli_query(
    $con,
    "CREATE TABLE IF NOT EXISTS `materials` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `mat_key` VARCHAR(100) NOT NULL,
        `label` VARCHAR(255) NOT NULL,
        `category` VARCHAR(50) NOT NULL,
        `stock_margin` DECIMAL(10,4) NOT NULL DEFAULT 0,
        `status` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uniq_material_key` (`mat_key`,`category`),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);
ensure_material_stock_margin_column($con);

// Seed defaults if empty
$cntRes = mysqli_query($con, "SELECT COUNT(*) AS c FROM materials");
$cntRow = $cntRes ? mysqli_fetch_assoc($cntRes) : null;
$cnt = $cntRow ? (int)$cntRow['c'] : 0;
if ($cnt === 0) {
    @mysqli_query($con, "INSERT IGNORE INTO materials (mat_key,label,category,stock_margin,status) VALUES
        ('banner_matt','Banner Matt','konica',0,1),
        ('banner_glossy','Banner Glossy','konica',0,1),
        ('vinily_ch','Vinily CH','konica',0,1),
        ('vinily_eu','Vinily EU','konica',0,1),
        ('banner_black_mate','Banner Black Matt','konica',0,1),
        ('banner_black_glossy','Banner Black Glossy','konica',0,1),
        ('white_banner','White Banner','konica',0,1),
        ('white_vinily','White Vinily','konica',0,1),
        ('backlead','Backlead','konica',0,1),
        ('flex','Flex','konica',0,1),
        ('banner_440_white','Banner 440 White','konica',0,1),
        ('banner_440_black','Banner 440 Black','konica',0,1),

        ('banner_matt','Banner Matt','roland',0,1),
        ('banner_glossy','Banner Glossy','roland',0,1),
        ('vinily_ch','Vinily CH','roland',0,1),
        ('vinily_eu','Vinily EU','roland',0,1),
        ('black_matt','Black Matt','roland',0,1),
        ('black_glossy','Black Glossy','roland',0,1),

        ('cut_wood','Cut On Wood','laser',0,1),
        ('cut_forex','Cut On Forex','laser',0,1),
        ('cut_orch','Cut On Orch','laser',0,1),
        ('graw_wood','Graw On Wood','laser',0,1),
        ('graw_forex','Graw Cut On Forex','laser',0,1),
        ('graw_orch','Graw Cut On Orch','laser',0,1)");
}

// Get optional printer filter from query param
$printerFilter = isset($_GET['printer']) ? trim($_GET['printer']) : '';
$printerCategory = '';

if ($printerFilter !== '') {
    // Look up the printer's price_key/category
    $printerEsc = mysqli_real_escape_string($con, $printerFilter);
    $printerRes = mysqli_query($con, "SELECT price_key FROM printers WHERE name='$printerEsc' OR price_key='$printerEsc' LIMIT 1");
    if ($printerRes && $pRow = mysqli_fetch_assoc($printerRes)) {
        $printerCategory = strtolower(trim($pRow['price_key'] ?? ''));
    }
}

// Build query - filter by printer category if specified
$sql = "SELECT id,mat_key,label,category,stock_margin,status FROM materials";
if ($printerCategory !== '') {
    $sql .= " WHERE category='" . mysqli_real_escape_string($con, $printerCategory) . "'";
}
$sql .= " ORDER BY category ASC, id ASC";

$res = mysqli_query($con, $sql);
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'db_error' => mysqli_error($con)]);
    exit;
}

$printerCategoryMap = [];
$printerRes = mysqli_query($con, "SELECT name, price_key FROM printers");
if ($printerRes !== false) {
    while ($printer = mysqli_fetch_assoc($printerRes)) {
        $priceKey = strtolower(trim((string)($printer['price_key'] ?? '')));
        $name = strtolower(trim((string)($printer['name'] ?? '')));
        $normalized = $priceKey !== '' ? $priceKey : $name;
        if ($normalized === '') {
            continue;
        }
        if ($name !== '') {
            $printerCategoryMap[$name] = $normalized;
        }
        if ($priceKey !== '') {
            $printerCategoryMap[$priceKey] = $normalized;
        }
    }
}

$rows = [];
$seen = [];
$assignedByKey = [];
$allRows = [];
while ($r = mysqli_fetch_assoc($res)) {
    $allRows[] = $r;
    $matKey = strtolower(trim((string)($r['mat_key'] ?? '')));
    $rawCategory = trim((string)($r['category'] ?? ''));
    if ($matKey !== '' && $rawCategory !== '') {
        $assignedByKey[$matKey] = true;
    }
}

foreach ($allRows as $r) {
    $category = normalize_material_list_category($printerCategoryMap, (string)($r['category'] ?? ''));
    $matKey = strtolower(trim((string)($r['mat_key'] ?? '')));
    if ($category === '' && isset($assignedByKey[$matKey])) {
        continue;
    }
    $rowKey = strtolower((string)($r['mat_key'] ?? '')) . '|' . $category;
    if (isset($seen[$rowKey])) {
        continue;
    }
    $seen[$rowKey] = true;
    $rows[] = [
        'id' => (int)$r['id'],
        'key' => $r['mat_key'],
        'label' => $r['label'],
        'category' => $category,
        'stock_margin' => (float)($r['stock_margin'] ?? 0),
        'status' => (int)$r['status'],
    ];
}

echo json_encode(['ok' => true, 'materials' => $rows]);
