<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

@include(__DIR__ . '/../config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

function ensure_delete_stock_log_table(mysqli $con): void
{
    @mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `delete_stock_log` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `stock_id` VARCHAR(50) NULL,
            `product` VARCHAR(255) NULL,
            `create_date` VARCHAR(50) NULL,
            `sgrm` DECIMAL(12,2) NULL,
            `delete_date` VARCHAR(50) NULL,
            `note` VARCHAR(255) NULL,
            `h_cm` DECIMAL(12,2) NULL,
            `w_cm` DECIMAL(12,2) NULL,
            `qty` INT NULL,
            `deduct_sgrm` DECIMAL(12,2) NULL,
            `before_sgrm` DECIMAL(12,2) NULL,
            `after_sgrm` DECIMAL(12,2) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function ensure_column(mysqli $con, string $table, string $column, string $definition): void
{
    $t = mysqli_real_escape_string($con, $table);
    $c = mysqli_real_escape_string($con, $column);
    $res = @mysqli_query($con, "SHOW COLUMNS FROM `$t` LIKE '$c'");
    if ($res && mysqli_num_rows($res) > 0) return;
    @mysqli_query($con, "ALTER TABLE `$t` ADD COLUMN $definition");
}

function table_exists(mysqli $con, string $table): bool
{
    $t = mysqli_real_escape_string($con, $table);
    $res = mysqli_query($con, "SHOW TABLES LIKE '$t'");
    return $res && mysqli_num_rows($res) > 0;
}

function stock_product_name_from_material(array $row): string
{
    $key = trim((string)($row['mat_key'] ?? ''));
    $label = trim((string)($row['label'] ?? ''));

    $map = [
        'banner_black_mate' => 'Banner Black Matt',
        'backlead' => 'Backleed',
        'banner_440_white' => 'Banner 440 GR White',
        'banner_440_black' => 'Banner 440 GR Black',
    ];

    if ($key !== '' && isset($map[$key])) {
        return $map[$key];
    }

    return $label;
}

function resolve_stock_padding(string $stockProduct): ?float
{
    static $groupA = [
        'Banner Matt',
        'Banner Glossy',
        'Banner Black Matt',
        'Banner Black Glossy',
        'White Banner',
        'White Vinily',
        'Backleed',
        'Backlead',
        'Flex',
        'Banner 440 GR White',
        'Banner 440 GR Black',
        'Banner 440 White',
        'Banner 440 Black'
    ];

    static $groupB = [
        'Vinily CH',
        'Vinily EU'
    ];

    if (in_array($stockProduct, $groupA, true)) {
        return 0.1;
    }
    if (in_array($stockProduct, $groupB, true)) {
        return 0.05;
    }

    return null;
}

function resolve_stock_margin_for_product(mysqli $con, string $product): float
{
    if ($product === '' || !table_exists($con, 'materials')) {
        $fallback = resolve_stock_padding($product);
        return $fallback === null ? 0.0 : $fallback;
    }

    $columnRes = @mysqli_query($con, "SHOW COLUMNS FROM `materials` LIKE 'stock_margin'");
    if (!$columnRes || mysqli_num_rows($columnRes) === 0) {
        $fallback = resolve_stock_padding($product);
        return $fallback === null ? 0.0 : $fallback;
    }

    $res = mysqli_query($con, "SELECT mat_key, label, category, stock_margin FROM materials WHERE status = 1 ORDER BY category <> '', id ASC");
    $matchedProduct = false;
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            if (strcasecmp(stock_product_name_from_material($row), $product) !== 0) {
                continue;
            }
            $matchedProduct = true;
            $margin = (float)($row['stock_margin'] ?? 0);
            if ($margin > 0) {
                return $margin;
            }
        }
    }

    if ($matchedProduct) {
        return 0.0;
    }

    $fallback = resolve_stock_padding($product);
    return $fallback === null ? 0.0 : $fallback;
}

function material_status_for_stock_product(mysqli $con, string $product): ?int
{
    if ($product === '' || !table_exists($con, 'materials')) {
        return null;
    }

    $res = mysqli_query($con, "SELECT mat_key, label, status FROM materials ORDER BY id ASC");
    if (!$res) {
        return null;
    }

    while ($row = mysqli_fetch_assoc($res)) {
        if (strcasecmp(stock_product_name_from_material($row), $product) === 0) {
            return (int)($row['status'] ?? 1);
        }
    }

    return null;
}

function extract_num(string $id): int
{
    if (preg_match('/(\d{1,})/', $id, $m)) {
        return (int)$m[1];
    }
    return 0;
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

ensure_delete_stock_log_table($con);
// Backwards-compatible: add columns if table existed from legacy
ensure_column($con, 'delete_stock_log', 'delete_date', "`delete_date` VARCHAR(50) NULL");
ensure_column($con, 'delete_stock_log', 'note', "`note` VARCHAR(255) NULL");
ensure_column($con, 'delete_stock_log', 'h_cm', "`h_cm` DECIMAL(12,2) NULL");
ensure_column($con, 'delete_stock_log', 'w_cm', "`w_cm` DECIMAL(12,2) NULL");
ensure_column($con, 'delete_stock_log', 'qty', "`qty` INT NULL");
ensure_column($con, 'delete_stock_log', 'deduct_sgrm', "`deduct_sgrm` DECIMAL(12,2) NULL");
ensure_column($con, 'delete_stock_log', 'before_sgrm', "`before_sgrm` DECIMAL(12,2) NULL");
ensure_column($con, 'delete_stock_log', 'after_sgrm', "`after_sgrm` DECIMAL(12,2) NULL");

if (!table_exists($con, 'in_stock')) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'in_stock table not found']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$product = isset($data['product']) ? trim((string)$data['product']) : '';
$hRaw = isset($data['h_cm']) ? (string)$data['h_cm'] : '';
$wRaw = isset($data['w_cm']) ? (string)$data['w_cm'] : '';
$qtyRaw = isset($data['qty']) ? (string)$data['qty'] : '';
$note = isset($data['note']) ? trim((string)$data['note']) : '';
$date = isset($data['date']) ? trim((string)$data['date']) : '';

if ($date === '') {
    $date = date('Y-m-d');
}

$today = date('Y-m-d');
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && $date < $today) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => ['date' => 'Keçmiş tarix seçmək olmaz']]);
    exit;
}

$h = (float)str_replace(',', '.', $hRaw);
$w = (float)str_replace(',', '.', $wRaw);
$qty = (int)preg_replace('/[^0-9]/', '', $qtyRaw);

$errors = [];
if ($product === '') $errors['product'] = 'Məhsul tələb olunur';
if ($note === '') $errors['note'] = 'Qeyd mütləqdir';
if (!is_numeric(str_replace(',', '.', $hRaw)) || $h <= 0) $errors['h_cm'] = 'H düzgün deyil';
if (!is_numeric(str_replace(',', '.', $wRaw)) || $w <= 0) $errors['w_cm'] = 'W düzgün deyil';
if ($qty <= 0) $errors['qty'] = 'Say düzgün deyil';

$materialStatus = material_status_for_stock_product($con, $product);
if ($materialStatus === 0) $errors['product'] = 'Deaktiv məhsuldan stok silmək olmaz';

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

$stockMargin = resolve_stock_margin_for_product($con, $product);
$deduct = (($h / 100.0) + $stockMargin) * (($w / 100.0) + $stockMargin) * (float)$qty;
$deduct = round($deduct, 2);

$productEsc = mysqli_real_escape_string($con, $product);
$dateEsc = mysqli_real_escape_string($con, $date);
$noteEsc = mysqli_real_escape_string($con, $note);

mysqli_begin_transaction($con);
try {
    $curRes = mysqli_query($con, "SELECT id, op_id, sgrm, date FROM in_stock WHERE product='$productEsc' LIMIT 1 FOR UPDATE");
    if ($curRes === false) {
        throw new Exception('Select failed: ' . mysqli_error($con));
    }

    $row = mysqli_fetch_assoc($curRes);
    if (!$row) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Bu məhsul stokda tapılmadı']);
        mysqli_rollback($con);
        exit;
    }

    $before = (float)$row['sgrm'];
    $after = round($before - $deduct, 2);

    if ($after < 0) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'errors' => ['sgrm' => 'Stok kifayət deyil']]);
        mysqli_rollback($con);
        exit;
    }

    $afterEsc = mysqli_real_escape_string($con, (string)$after);
    if ((int)$row['id'] > 0) {
        $upd = mysqli_query($con, "UPDATE in_stock SET sgrm='$afterEsc', date='$dateEsc', note='$noteEsc' WHERE id=" . (int)$row['id']);
    } else {
        $upd = mysqli_query($con, "UPDATE in_stock SET sgrm='$afterEsc', date='$dateEsc', note='$noteEsc' WHERE product='$productEsc'");
    }
    if ($upd === false) {
        throw new Exception('Update failed: ' . mysqli_error($con));
    }

    $dskId = next_delete_stock_id($con);
    $dskEsc = mysqli_real_escape_string($con, $dskId);

    $createDate = (string)($row['date'] ?? '');
    $createDateEsc = mysqli_real_escape_string($con, $createDate);

    $hEsc = mysqli_real_escape_string($con, (string)$h);
    $wEsc = mysqli_real_escape_string($con, (string)$w);
    $qtyEsc = mysqli_real_escape_string($con, (string)$qty);
    $deductEsc = mysqli_real_escape_string($con, (string)$deduct);
    $beforeEsc = mysqli_real_escape_string($con, (string)$before);

    $ins = mysqli_query(
        $con,
        "INSERT INTO delete_stock_log (stock_id, product, create_date, sgrm, delete_date, note, h_cm, w_cm, qty, deduct_sgrm, before_sgrm, after_sgrm)
         VALUES ('$dskEsc','$productEsc','$createDateEsc','$deductEsc','$dateEsc','$noteEsc','$hEsc','$wEsc','$qtyEsc','$deductEsc','$beforeEsc','$afterEsc')"
    );
    if ($ins === false) {
        throw new Exception('Insert log failed: ' . mysqli_error($con));
    }

    if ((int)$row['id'] > 0) {
        $cur = mysqli_query($con, "SELECT id, op_id, product, sgrm, date, note FROM in_stock WHERE id=" . (int)$row['id']);
    } else {
        $cur = mysqli_query($con, "SELECT id, op_id, product, sgrm, date, note FROM in_stock WHERE product='$productEsc' LIMIT 1");
    }
    $currentRow = $cur ? mysqli_fetch_assoc($cur) : null;

    mysqli_commit($con);
    echo json_encode([
        'ok' => true,
        'delete_id' => $dskId,
        'deduct_sgrm' => $deduct,
        'before_sgrm' => $before,
        'after_sgrm' => $after,
        'stock_item' => $currentRow
    ]);
} catch (Exception $e) {
    mysqli_rollback($con);
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Delete failed', 'db_error' => $e->getMessage()]);
}
