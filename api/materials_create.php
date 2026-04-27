<?php
error_reporting(0);
header('Content-Type: application/json');

@include(__DIR__ . '/../config.php');
@include(__DIR__ . '/_auth.php');

function resolve_material_category(mysqli $con, string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    $normalized = strtolower($value);
    $escaped = mysqli_real_escape_string($con, $value);
    $normalizedEscaped = mysqli_real_escape_string($con, $normalized);
    $res = mysqli_query($con, "SELECT price_key, name FROM printers WHERE LOWER(price_key)='$normalizedEscaped' OR LOWER(name)='$normalizedEscaped' OR name='$escaped' LIMIT 1");
    $row = $res ? mysqli_fetch_assoc($res) : null;
    if ($row) {
        $priceKey = trim((string)($row['price_key'] ?? ''));
        $name = trim((string)($row['name'] ?? ''));
        return $priceKey !== '' ? strtolower($priceKey) : strtolower($name);
    }

    return $normalized;
}

function normalize_category_list($rawCategories, string $fallbackCategory): array
{
    $list = [];
    if (is_array($rawCategories)) {
        foreach ($rawCategories as $value) {
            $category = trim((string)$value);
            if ($category !== '') {
                $list[] = $category;
            }
        }
    }
    if ($fallbackCategory !== '') {
        $list[] = $fallbackCategory;
    }

    $unique = [];
    foreach ($list as $category) {
        $key = strtolower($category);
        if (!isset($unique[$key])) {
            $unique[$key] = $category;
        }
    }

    return array_values($unique);
}

function material_label_from_key(string $key): string
{
    $key = trim($key);
    if ($key === '') {
        return '';
    }

    $parts = preg_split('/[_\s-]+/', $key);
    $parts = array_values(array_filter($parts, static function ($part) {
        return $part !== null && $part !== '';
    }));

    if (empty($parts)) {
        return $key;
    }

    $parts = array_map(static function ($part) {
        $part = strtolower((string)$part);
        return ucfirst($part);
    }, $parts);

    return implode(' ', $parts);
}

function ensure_material_stock_margin_column(mysqli $con): void
{
    $res = @mysqli_query($con, "SHOW COLUMNS FROM `materials` LIKE 'stock_margin'");
    if ($res && mysqli_num_rows($res) > 0) {
        return;
    }
    @mysqli_query($con, "ALTER TABLE `materials` ADD COLUMN `stock_margin` DECIMAL(10,4) NOT NULL DEFAULT 0 AFTER `category`");
}

require_role(['superadmin']);

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

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) $data = $_POST;

$key = isset($data['key']) ? trim((string)$data['key']) : '';
$label = isset($data['label']) ? trim((string)$data['label']) : '';
if ($label === '' && $key !== '') {
    $label = material_label_from_key($key);
}
$category = isset($data['category']) ? trim((string)$data['category']) : '';
$categories = normalize_category_list($data['categories'] ?? null, $category);
foreach ($categories as $index => $categoryValue) {
    $categories[$index] = resolve_material_category($con, (string)$categoryValue);
}
if (!empty($categories)) {
    $normalizedUnique = [];
    foreach ($categories as $categoryValue) {
        $categoryValue = trim((string)$categoryValue);
        $categoryKey = strtolower($categoryValue);
        if (!isset($normalizedUnique[$categoryKey])) {
            $normalizedUnique[$categoryKey] = $categoryValue;
        }
    }
    $categories = array_values($normalizedUnique);
}
if (empty($categories)) {
    $categories = [''];
}
$stockMargin = isset($data['stock_margin']) ? (float)str_replace(',', '.', (string)$data['stock_margin']) : 0.0;
$stockMarginProvided = array_key_exists('stock_margin', $data);
if ($stockMargin < 0) {
    $stockMargin = 0.0;
}

$keyEsc = mysqli_real_escape_string($con, $key);
$labelEsc = mysqli_real_escape_string($con, $label);
if (!$stockMarginProvided) {
    $inheritRes = mysqli_query($con, "SELECT stock_margin FROM materials WHERE mat_key='$keyEsc' AND category='' ORDER BY id ASC LIMIT 1");
    $inheritRow = $inheritRes ? mysqli_fetch_assoc($inheritRes) : null;
    if ($inheritRow && isset($inheritRow['stock_margin'])) {
        $stockMargin = max(0.0, (float)$inheritRow['stock_margin']);
    }
}
$stockMarginEsc = mysqli_real_escape_string($con, (string)$stockMargin);

$status = isset($data['status']) ? (int)$data['status'] : 1;

$errors = [];
if ($key === '') $errors['key'] = 'Material key tələb olunur';
if (!preg_match('/^[a-z0-9_]+$/', $key)) $errors['key'] = 'Key yalnız a-z 0-9 _ ola bilər';
if ($label === '') $errors['label'] = 'Material adı tələb olunur';
$status = ($status === 1) ? 1 : 0;

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

$keyEsc = mysqli_real_escape_string($con, $key);
$labelEsc = mysqli_real_escape_string($con, $label);
$stockMarginEsc = mysqli_real_escape_string($con, (string)$stockMargin);

$createdIds = [];
foreach ($categories as $categoryValue) {
    $categoryValue = trim((string)$categoryValue);
    $catEsc = mysqli_real_escape_string($con, $categoryValue);
    if ($categoryValue !== '') {
        $normalizedCatEsc = mysqli_real_escape_string($con, strtolower($categoryValue));
        mysqli_query($con, "UPDATE materials SET category='$catEsc' WHERE mat_key='$keyEsc' AND LOWER(category)='$normalizedCatEsc'");
    }
    $ins = mysqli_query($con, "INSERT IGNORE INTO materials (mat_key,label,category,stock_margin,status) VALUES ('$keyEsc','$labelEsc','$catEsc','$stockMarginEsc',$status)");
    if ($ins === false) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Insert failed', 'db_error' => mysqli_error($con)]);
        exit;
    }

    if (mysqli_affected_rows($con) > 0) {
        $createdIds[] = (int)mysqli_insert_id($con);
        continue;
    }

    $existingRes = mysqli_query($con, "SELECT id FROM materials WHERE mat_key='$keyEsc' AND category='$catEsc' LIMIT 1");
    $existingRow = $existingRes ? mysqli_fetch_assoc($existingRes) : null;
    if ($existingRow && isset($existingRow['id'])) {
        $createdIds[] = (int)$existingRow['id'];
    }
}

echo json_encode([
    'ok' => true,
    'id' => isset($createdIds[0]) ? (int)$createdIds[0] : 0,
    'ids' => $createdIds,
    'categories' => $categories,
    'stock_margin' => $stockMargin
]);
