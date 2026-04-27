<?php
error_reporting(0);
header('Content-Type: application/json');

@include(__DIR__ . '/../config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

function ensure_in_stock_table(mysqli $con): void
{
    @mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `in_stock` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `product` VARCHAR(255) NULL,
            `sgrm` DECIMAL(12,2) NULL,
            `date` VARCHAR(50) NULL,
            `note` VARCHAR(255) NULL,
            `op_id` VARCHAR(50) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function ensure_add_stock_table(mysqli $con): void
{
    @mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `add_stock` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `product` VARCHAR(255) NULL,
            `sgrm` DECIMAL(12,2) NULL,
            `note` VARCHAR(255) NULL,
            `op_id` VARCHAR(50) NULL,
            `date` VARCHAR(50) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
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

function next_stock_id(mysqli $con): string
{
    $prefix = 'ASK-';
    $max = 0;

    if (table_exists($con, 'add_stock')) {
        $res = mysqli_query($con, "SELECT op_id FROM add_stock");
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $id = (string)($row['op_id'] ?? '');
                if (stripos($id, $prefix) === 0) {
                    $n = extract_num($id);
                    if ($n > $max) $max = $n;
                }
            }
        }
    }

    if (table_exists($con, 'delete_stock_log')) {
        $res2 = mysqli_query($con, "SELECT stock_id FROM delete_stock_log");
        if ($res2) {
            while ($row2 = mysqli_fetch_assoc($res2)) {
                $id2 = (string)($row2['stock_id'] ?? '');
                if (stripos($id2, $prefix) === 0) {
                    $n2 = extract_num($id2);
                    if ($n2 > $max) $max = $n2;
                }
            }
        }
    }

    $next = $max + 1;
    return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
}

ensure_in_stock_table($con);
ensure_add_stock_table($con);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$product = isset($data['product']) ? trim((string)$data['product']) : '';
$sgrmRaw = isset($data['sgrm']) ? (string)$data['sgrm'] : '';
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

$sgrm = (float)str_replace(',', '.', $sgrmRaw);

$errors = [];
if ($product === '') $errors['product'] = 'Məhsul tələb olunur';
if ($note === '') $errors['note'] = 'Qeyd mütləqdir';
if (!is_numeric(str_replace(',', '.', $sgrmRaw)) || $sgrm <= 0) $errors['sgrm'] = 'KV/M düzgün deyil';

$materialStatus = material_status_for_stock_product($con, $product);
if ($materialStatus === 0) $errors['product'] = 'Deaktiv məhsula stok əlavə etmək olmaz';

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

$op_id = next_stock_id($con);

$productEsc = mysqli_real_escape_string($con, $product);
$sgrmEsc = mysqli_real_escape_string($con, (string)$sgrm);
$noteEsc = mysqli_real_escape_string($con, $note);
$dateEsc = mysqli_real_escape_string($con, $date);
$opEsc = mysqli_real_escape_string($con, $op_id);

$productCandidates = [$product];
if (table_exists($con, 'materials')) {
    $matRes = mysqli_query($con, "SELECT mat_key, label FROM materials ORDER BY id ASC");
    if ($matRes) {
        while ($matRow = mysqli_fetch_assoc($matRes)) {
            $matKey = trim((string)($matRow['mat_key'] ?? ''));
            $matLabel = stock_product_name_from_material($matRow);
            if ($matKey === '' || $matLabel === '') {
                continue;
            }
            if (strcasecmp($product, $matLabel) === 0 && !in_array($matKey, $productCandidates, true)) {
                $productCandidates[] = $matKey;
            }
            if (strcasecmp($product, $matKey) === 0 && !in_array($matLabel, $productCandidates, true)) {
                $productCandidates[] = $matLabel;
            }
        }
    }
}

mysqli_begin_transaction($con);
try {
    $row = null;
    $matchedProduct = $product;
    foreach ($productCandidates as $candidateProduct) {
        $candidateEsc = mysqli_real_escape_string($con, $candidateProduct);
        $existRes = mysqli_query($con, "SELECT id, sgrm, product FROM in_stock WHERE product='$candidateEsc' ORDER BY id DESC LIMIT 1");
        if ($existRes === false) {
            throw new Exception('Select failed: ' . mysqli_error($con));
        }
        $candidateRow = mysqli_fetch_assoc($existRes);
        if ($candidateRow) {
            $row = $candidateRow;
            $matchedProduct = (string)($candidateRow['product'] ?? $candidateProduct);
            break;
        }
    }

    if ($row) {
        $id = (int)$row['id'];
        $current = (float)$row['sgrm'];
        $new = $current + $sgrm;
        $newEsc = mysqli_real_escape_string($con, (string)$new);
        $matchedProductEsc = mysqli_real_escape_string($con, $matchedProduct);

        if ($id > 0) {
            $upd = mysqli_query($con, "UPDATE in_stock SET sgrm='$newEsc', date='$dateEsc', note='$noteEsc', op_id='$opEsc' WHERE id=$id");
        } else {
            $upd = mysqli_query($con, "UPDATE in_stock SET sgrm='$newEsc', date='$dateEsc', note='$noteEsc', op_id='$opEsc' WHERE product='$matchedProductEsc'");
        }
        if ($upd === false) {
            throw new Exception('Update failed: ' . mysqli_error($con));
        }
    } else {
        $ins = mysqli_query($con, "INSERT INTO in_stock (product, sgrm, date, note, op_id) VALUES ('$productEsc','$sgrmEsc','$dateEsc','$noteEsc','$opEsc')");
        if ($ins === false) {
            throw new Exception('Insert failed: ' . mysqli_error($con));
        }
    }

    $hist = mysqli_query($con, "INSERT INTO add_stock (product, sgrm, note, op_id, date) VALUES ('$productEsc','$sgrmEsc','$noteEsc','$opEsc','$dateEsc')");
    if ($hist === false) {
        throw new Exception('History insert failed: ' . mysqli_error($con));
    }

    $currentProductEsc = mysqli_real_escape_string($con, isset($matchedProduct) ? $matchedProduct : $product);
    $cur = mysqli_query($con, "SELECT id, op_id, product, sgrm, date, note FROM in_stock WHERE product='$currentProductEsc' ORDER BY id DESC LIMIT 1");
    $currentRow = $cur ? mysqli_fetch_assoc($cur) : null;

    mysqli_commit($con);
    echo json_encode(['ok' => true, 'op_id' => $op_id, 'stock_item' => $currentRow]);
} catch (Exception $e) {
    mysqli_rollback($con);
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Create failed', 'db_error' => $e->getMessage()]);
}
