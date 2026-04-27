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

function table_exists(mysqli $con, string $table): bool
{
    $t = mysqli_real_escape_string($con, $table);
    $res = mysqli_query($con, "SHOW TABLES LIKE '$t'");
    return $res && mysqli_num_rows($res) > 0;
}

function created_by_value(): string
{
    $role = auth_user_role();
    if ($role === 'superadmin') {
        return 'NULL';
    }
    $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if ($uid > 0) {
        return (string)$uid;
    }
    return 'NULL';
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

function ensure_work_column(mysqli $con, string $column, string $definition): void
{
    $col = mysqli_real_escape_string($con, $column);
    $res = @mysqli_query($con, "SHOW COLUMNS FROM `work` LIKE '$col'");
    if ($res && mysqli_num_rows($res) > 0) {
        return;
    }
    @mysqli_query($con, "ALTER TABLE `work` ADD COLUMN $definition");
}

function ensure_payment_table(mysqli $con): void
{
    // Best-effort: do not fail if table already exists with different schema
    @mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `payment` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `client_id` VARCHAR(50) NOT NULL,
            `name` VARCHAR(255) NULL,
            `email` VARCHAR(255) NULL,
            `phone` VARCHAR(50) NULL,
            `total_amount` DECIMAL(12,2) NULL,
            `paid` DECIMAL(12,2) NULL,
            `outstanding_debet` DECIMAL(12,2) NULL,
            `advanced` DECIMAL(12,2) NULL,
            `date` VARCHAR(50) NULL,
            `operation_id` VARCHAR(50) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function ensure_client_price_profiles_table(mysqli $con): void
{
    @mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `client_price_profiles` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `client_id` VARCHAR(50) NOT NULL,
            `printer_key` VARCHAR(50) NOT NULL,
            `material_key` VARCHAR(100) NOT NULL,
            `price` DECIMAL(12,4) NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uniq_client_price_profile` (`client_id`,`printer_key`,`material_key`),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function load_client_price_profiles(mysqli $con, string $clientId): array
{
    if ($clientId === '' || !table_exists($con, 'client_price_profiles')) {
        return [];
    }

    $clientIdEsc = mysqli_real_escape_string($con, $clientId);
    $profiles = [];
    $res = @mysqli_query($con, "SELECT printer_key, material_key, price FROM client_price_profiles WHERE client_id = '$clientIdEsc'");
    if (!$res) {
        return $profiles;
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $printerKey = strtolower(trim((string)($row['printer_key'] ?? '')));
        $materialKey = trim((string)($row['material_key'] ?? ''));
        if ($printerKey === '' || $materialKey === '') {
            continue;
        }
        if (!isset($profiles[$printerKey]) || !is_array($profiles[$printerKey])) {
            $profiles[$printerKey] = [];
        }
        $profiles[$printerKey][$materialKey] = $row['price'] === null ? null : (float)$row['price'];
    }

    return $profiles;
}

function load_legacy_client_prices(mysqli $con, string $clientId): array
{
    if ($clientId === '') {
        return [];
    }

    $clientIdEsc = mysqli_real_escape_string($con, $clientId);
    $res = @mysqli_query($con, "SELECT * FROM clients WHERE client_id = '$clientIdEsc' LIMIT 1");
    $row = $res ? mysqli_fetch_assoc($res) : null;
    if (!$row) {
        return [];
    }

    $toFloat = static function ($value): float {
        if ($value === null || $value === '') {
            return 0.0;
        }
        return (float)str_replace(',', '.', (string)$value);
    };

    return [
        'konica' => [
            'banner_matt' => $toFloat($row['banner_matt'] ?? 0),
            'banner_glossy' => $toFloat($row['banner_glossy'] ?? 0),
            'vinily_ch' => $toFloat($row['vinily_ch'] ?? 0),
            'vinily_eu' => $toFloat($row['vinily_eu'] ?? 0),
            'banner_black_mate' => $toFloat($row['banner_black_mate'] ?? 0),
            'banner_black_glossy' => $toFloat($row['banner_black_glossy'] ?? 0),
            'white_banner' => $toFloat($row['white_banner'] ?? 0),
            'white_vinily' => $toFloat($row['white_vinily'] ?? 0),
            'backlead' => $toFloat($row['backlead'] ?? 0),
            'flex' => $toFloat($row['flex'] ?? 0),
            'banner_440_white' => $toFloat($row['banner_440_white'] ?? 0),
            'banner_440_black' => $toFloat($row['banner_440_black'] ?? 0),
        ],
        'roland' => [
            'banner_matt' => $toFloat($row['roland_banner_matt'] ?? 0),
            'banner_glossy' => $toFloat($row['roland_banner_glossy'] ?? 0),
            'vinily_ch' => $toFloat($row['roland_vinily_ch'] ?? 0),
            'vinily_eu' => $toFloat($row['roland_vinily_eu'] ?? 0),
            'black_matt' => $toFloat($row['roland_black_matt'] ?? 0),
            'black_glossy' => $toFloat($row['roland_black_glossy'] ?? 0),
        ],
        'laser' => [
            'cut_wood' => $toFloat($row['cut_wood'] ?? 0),
            'cut_forex' => $toFloat($row['cut_forex'] ?? 0),
            'cut_orch' => $toFloat($row['cut_orch'] ?? 0),
            'graw_wood' => $toFloat($row['graw_wood'] ?? 0),
            'graw_forex' => $toFloat($row['graw_forex'] ?? 0),
            'graw_orch' => $toFloat($row['graw_orch'] ?? 0),
        ],
    ];
}

function merge_client_price_maps(array $base, array $overrides): array
{
    $merged = $base;
    foreach ($overrides as $printerKey => $materials) {
        if (!isset($merged[$printerKey]) || !is_array($merged[$printerKey])) {
            $merged[$printerKey] = [];
        }
        if (!is_array($materials)) {
            continue;
        }
        foreach ($materials as $materialKey => $price) {
            if ((float)$price > 0) {
                $merged[$printerKey][$materialKey] = (float)$price;
            }
        }
    }
    return $merged;
}

function load_printer_key_map(mysqli $con): array
{
    if (!table_exists($con, 'printers')) {
        return [];
    }

    $map = [];
    $res = @mysqli_query($con, "SELECT name, price_key FROM printers");
    if (!$res) {
        return $map;
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $name = strtolower(trim((string)($row['name'] ?? '')));
        $priceKey = strtolower(trim((string)($row['price_key'] ?? '')));
        if ($name !== '' && $priceKey !== '') {
            $map[$name] = $priceKey;
        }
        if ($priceKey !== '') {
            $map[$priceKey] = $priceKey;
        }
    }
    return $map;
}

function load_material_stock_map(mysqli $con): array
{
    if (!table_exists($con, 'materials')) {
        return [];
    }

    $map = [];
    $res = @mysqli_query($con, "SELECT mat_key, category, label FROM materials WHERE status = 1");
    if (!$res) {
        return $map;
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $matKey = trim((string)($row['mat_key'] ?? ''));
        $category = strtolower(trim((string)($row['category'] ?? '')));
        $label = trim((string)($row['label'] ?? ''));
        if ($matKey === '' || $label === '') {
            continue;
        }
        if (!isset($map[$matKey]) || !is_array($map[$matKey])) {
            $map[$matKey] = [];
        }
        $map[$matKey][$category] = $label;
    }

    return $map;
}

function load_material_stock_margin_map(mysqli $con): array
{
    if (!table_exists($con, 'materials')) {
        return [];
    }

    $columnRes = @mysqli_query($con, "SHOW COLUMNS FROM `materials` LIKE 'stock_margin'");
    if (!$columnRes || mysqli_num_rows($columnRes) === 0) {
        return [];
    }

    $map = [];
    $res = @mysqli_query($con, "SELECT mat_key, category, stock_margin FROM materials WHERE status = 1");
    if (!$res) {
        return $map;
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $matKey = trim((string)($row['mat_key'] ?? ''));
        $category = strtolower(trim((string)($row['category'] ?? '')));
        if ($matKey === '' || $category === '') {
            continue;
        }
        if (!isset($map[$matKey]) || !is_array($map[$matKey])) {
            $map[$matKey] = [];
        }
        $map[$matKey][$category] = (float)($row['stock_margin'] ?? 0);
    }

    return $map;
}

function load_material_key_map(mysqli $con): array
{
    if (!table_exists($con, 'materials')) {
        return [];
    }

    $map = [];
    $res = @mysqli_query($con, "SELECT mat_key, label, category FROM materials WHERE status = 1");
    if (!$res) {
        return $map;
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $matKey = trim((string)($row['mat_key'] ?? ''));
        $label = strtolower(trim((string)($row['label'] ?? '')));
        $category = strtolower(trim((string)($row['category'] ?? '')));
        if ($matKey === '') {
            continue;
        }
        $map[strtolower($matKey)] = $matKey;
        if ($label !== '') {
            $map[$label] = $matKey;
            if ($category !== '') {
                $map[$category . '::' . $label] = $matKey;
            }
        }
    }

    return $map;
}

function normalize_material_key(string $material, string $printer, array $materialKeyMap, array $printerKeyMap): string
{
    $materialTrimmed = trim($material);
    if ($materialTrimmed === '') {
        return '';
    }

    $materialLower = strtolower($materialTrimmed);
    if (isset($materialKeyMap[$materialLower]) && $materialKeyMap[$materialLower] !== '') {
        return $materialKeyMap[$materialLower];
    }

    $printerLower = strtolower(trim($printer));
    $printerCandidates = [];
    if ($printerLower !== '') {
        $printerCandidates[] = $printerLower;
    }
    if (isset($printerKeyMap[$printerLower]) && $printerKeyMap[$printerLower] !== '') {
        $printerCandidates[] = $printerKeyMap[$printerLower];
    }

    foreach ($printerCandidates as $printerCandidate) {
        $scoped = $printerCandidate . '::' . $materialLower;
        if (isset($materialKeyMap[$scoped]) && $materialKeyMap[$scoped] !== '') {
            return $materialKeyMap[$scoped];
        }
    }

    return $materialTrimmed;
}

function resolve_stock_product_name(string $materialKey, string $printer, array $materialToStockProduct, array $materialStockMap, array $printerKeyMap): string
{
    if (isset($materialToStockProduct[$materialKey]) && $materialToStockProduct[$materialKey] !== '') {
        return $materialToStockProduct[$materialKey];
    }

    $printerLower = strtolower(trim($printer));
    $printerCandidates = [];
    if ($printerLower !== '') {
        $printerCandidates[] = $printerLower;
    }
    if (isset($printerKeyMap[$printerLower]) && $printerKeyMap[$printerLower] !== '') {
        $printerCandidates[] = $printerKeyMap[$printerLower];
    }

    if (isset($materialStockMap[$materialKey]) && is_array($materialStockMap[$materialKey])) {
        foreach ($printerCandidates as $printerKey) {
            if (isset($materialStockMap[$materialKey][$printerKey]) && $materialStockMap[$materialKey][$printerKey] !== '') {
                return $materialStockMap[$materialKey][$printerKey];
            }
        }

        foreach ($materialStockMap[$materialKey] as $label) {
            if ((string)$label !== '') {
                return (string)$label;
            }
        }
    }

    return $materialKey;
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

function resolve_material_stock_margin(string $materialKey, string $printer, array $materialStockMarginMap, array $printerKeyMap, string $stockProduct): float
{
    $printerLower = strtolower(trim($printer));
    $printerCandidates = [];
    if ($printerLower !== '') {
        $printerCandidates[] = $printerLower;
    }
    if (isset($printerKeyMap[$printerLower]) && $printerKeyMap[$printerLower] !== '') {
        $printerCandidates[] = $printerKeyMap[$printerLower];
    }

    if (isset($materialStockMarginMap[$materialKey]) && is_array($materialStockMarginMap[$materialKey])) {
        $matchedPrinter = false;
        foreach ($printerCandidates as $printerKey) {
            if (isset($materialStockMarginMap[$materialKey][$printerKey])) {
                $matchedPrinter = true;
                $margin = (float)$materialStockMarginMap[$materialKey][$printerKey];
                if ($margin > 0) {
                    return max(0, $margin);
                }
            }
        }
        if ($matchedPrinter) {
            return 0.0;
        }
    }

    $fallback = resolve_stock_padding($stockProduct);
    return $fallback === null ? 0.0 : max(0, (float)$fallback);
}

function is_stock_tracked_material(string $materialKey, array $materialToStockProduct, array $materialStockMap): bool
{
    if (isset($materialToStockProduct[$materialKey]) && $materialToStockProduct[$materialKey] !== '') {
        return true;
    }
    return isset($materialStockMap[$materialKey]) && is_array($materialStockMap[$materialKey]) && !empty($materialStockMap[$materialKey]);
}

function log_create_work_debug(string $message, array $context = []): void
{
    $payload = [
        'message' => $message,
        'context' => $context,
    ];
    @error_log('create_work_debug ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function resolve_price_for_work_item(array $item, array $clientPrices, array $printerKeyMap): float
{
    $raw = str_replace(',', '.', (string)($item['price_per_m2'] ?? 0));
    $price = (float)$raw;
    if ($price > 0) {
        return $price;
    }

    $printer = strtolower(trim((string)($item['printer'] ?? '')));
    $material = trim((string)($item['material'] ?? ''));
    if ($printer === '' || $material === '') {
        return 0.0;
    }

    $printerCandidates = [];
    if ($printer !== '') {
        $printerCandidates[] = $printer;
    }
    if (isset($printerKeyMap[$printer]) && $printerKeyMap[$printer] !== '') {
        $printerCandidates[] = $printerKeyMap[$printer];
    }

    foreach ($printerCandidates as $printerKey) {
        if (isset($clientPrices[$printerKey]) && array_key_exists($material, $clientPrices[$printerKey])) {
            $resolved = (float)$clientPrices[$printerKey][$material];
            if ($resolved > 0) {
                return $resolved;
            }
        }
    }

    return 0.0;
}

function generate_next_work_id(mysqli $con): string
{
    $prefix = 'WRK ';
    $last = 'WRK 0000';
    $res = mysqli_query($con, "SELECT op_id FROM work WHERE op_id IS NOT NULL AND op_id != '' ORDER BY id DESC LIMIT 1");
    if ($res && ($row = mysqli_fetch_assoc($res)) && !empty($row['op_id'])) {
        $last = (string)$row['op_id'];
    }
    $num = 0;
    if (preg_match('/(\d+)/', $last, $m)) {
        $num = (int)$m[1];
    }
    $next = $num + 1;
    return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
}

function recalc_client_balance(mysqli $con, string $client_id, string $date, string $op_id): void
{
    $client_id_esc = mysqli_real_escape_string($con, $client_id);

    $resWork = mysqli_query($con, "SELECT COALESCE((SELECT SUM(CEIL(op_total)) FROM (SELECT op_id, SUM(total_amount) AS op_total FROM work WHERE client_id='$client_id_esc' GROUP BY op_id) work_ops),0) AS total_work FROM work WHERE client_id='$client_id_esc'");
    $workRow = $resWork ? mysqli_fetch_assoc($resWork) : null;
    $total_work = $workRow ? (float)$workRow['total_work'] : 0.0;

    $resPaid = mysqli_query($con, "SELECT COALESCE(SUM(paid),0) AS total_paid FROM payment WHERE client_id='$client_id_esc'");
    $paidRow = $resPaid ? mysqli_fetch_assoc($resPaid) : null;
    $total_paid = $paidRow ? (float)$paidRow['total_paid'] : 0.0;

    $new_outstanding = $total_work - $total_paid;
    $outstanding = $new_outstanding < 0 ? 0 : $new_outstanding;
    $advanced = $new_outstanding < 0 ? abs($new_outstanding) : 0;

    $outstandingEsc = mysqli_real_escape_string($con, (string)$outstanding);
    $advancedEsc = mysqli_real_escape_string($con, (string)$advanced);
    $totalWorkEsc = mysqli_real_escape_string($con, (string)$total_work);

    @mysqli_query(
        $con,
        "UPDATE clients SET 
            total_amount='$totalWorkEsc',
            outstanding_debit='$outstandingEsc',
            advanced='$advancedEsc'
        WHERE client_id='$client_id_esc'"
    );

    $clientRes = mysqli_query($con, "SELECT name,email,phone FROM clients WHERE client_id='$client_id_esc' LIMIT 1");
    $client = $clientRes ? mysqli_fetch_assoc($clientRes) : null;
    $nameEsc = mysqli_real_escape_string($con, (string)($client['name'] ?? ''));
    $emailEsc = mysqli_real_escape_string($con, (string)($client['email'] ?? ''));
    $phoneEsc = mysqli_real_escape_string($con, (string)($client['phone'] ?? ''));
    $dateEsc = mysqli_real_escape_string($con, $date);
    $opEsc = mysqli_real_escape_string($con, $op_id);

    $upd = @mysqli_query(
        $con,
        "UPDATE payment SET total_amount='$totalWorkEsc', outstanding_debet='$outstandingEsc', advanced='$advancedEsc', date='$dateEsc', operation_id='$opEsc' WHERE client_id='$client_id_esc' ORDER BY id DESC LIMIT 1"
    );

    if ($upd === false || mysqli_affected_rows($con) === 0) {
        @mysqli_query(
            $con,
            "INSERT INTO payment (client_id, name, email, phone, total_amount, paid, outstanding_debet, advanced, date, operation_id)
             VALUES ('$client_id_esc','$nameEsc','$emailEsc','$phoneEsc','$totalWorkEsc','0','$outstandingEsc','$advancedEsc','$dateEsc','$opEsc')"
        );
    }
}

ensure_work_table($con);
ensure_work_column($con, 'price_per_m2', "`price_per_m2` DECIMAL(12,4) NULL");
ensure_work_column($con, 'created_by', "`created_by` INT NULL");
ensure_payment_table($con);
ensure_in_stock_table($con);
ensure_client_price_profiles_table($con);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$client_id = isset($data['client_id']) ? trim((string)$data['client_id']) : '';
$work_name = isset($data['work_name']) ? trim((string)$data['work_name']) : '';
$date = isset($data['date']) ? trim((string)$data['date']) : '';
$op_id = isset($data['op_id']) ? trim((string)$data['op_id']) : '';
$items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];

if ($date === '') {
    $date = date('Y-m-d');
}

$today = date('Y-m-d');
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && $date < $today) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => ['date' => 'Keçmiş tarix seçmək olmaz']]);
    exit;
}

$errors = [];
if ($client_id === '') $errors['client_id'] = 'Müştəri ID tələb olunur';
if ($work_name === '') $errors['work_name'] = 'İşin adı tələb olunur';
if (empty($items)) $errors['items'] = 'Ən azı 1 material əlavə edin';

if ($client_id !== '') {
    $client_id_esc = mysqli_real_escape_string($con, $client_id);
    $exists = mysqli_query($con, "SELECT 1 FROM clients WHERE client_id='$client_id_esc' LIMIT 1");
    if (!$exists || mysqli_num_rows($exists) === 0) {
        $errors['client_id'] = 'Müştəri tapılmadı';
    }
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

if ($op_id === '') {
    $op_id = generate_next_work_id($con);
}

$client_id_esc = mysqli_real_escape_string($con, $client_id);
$work_name_esc = mysqli_real_escape_string($con, $work_name);
$date_esc = mysqli_real_escape_string($con, $date);
$op_id_esc = mysqli_real_escape_string($con, $op_id);
$clientPrices = merge_client_price_maps(
    load_legacy_client_prices($con, $client_id),
    load_client_price_profiles($con, $client_id)
);
$printerKeyMap = load_printer_key_map($con);
$materialKeyMap = load_material_key_map($con);
$materialStockMap = load_material_stock_map($con);
$materialStockMarginMap = load_material_stock_margin_map($con);

$total_raw = 0.0;
$rowsToInsert = [];

$materialToStockProduct = [
    'banner_matt' => 'Banner Matt',
    'banner_glossy' => 'Banner Glossy',
    'banner_black_mate' => 'Banner Black Matt',
    'banner_black_glossy' => 'Banner Black Glossy',
    'white_banner' => 'White Banner',
    'white_vinily' => 'White Vinily',
    'backlead' => 'Backleed',
    'flex' => 'Flex',
    'banner_440_white' => 'Banner 440 GR White',
    'banner_440_black' => 'Banner 440 GR Black',
    'vinily_ch' => 'Vinily CH',
    'vinily_eu' => 'Vinily EU'
];

foreach ($items as $idx => $it) {
    if (!is_array($it)) continue;

    $printer = trim((string)($it['printer'] ?? ''));
    $material = normalize_material_key((string)($it['material'] ?? ''), $printer, $materialKeyMap, $printerKeyMap);
    $w_cm = (float)str_replace(',', '.', (string)($it['width_cm'] ?? 0));
    $h_cm = (float)str_replace(',', '.', (string)($it['height_cm'] ?? 0));
    $qty = (int)($it['qty'] ?? 0);
    $it['material'] = $material;
    $price = resolve_price_for_work_item($it, $clientPrices, $printerKeyMap);

    if ($material === '' || $printer === '' || $w_cm <= 0 || $h_cm <= 0 || $qty <= 0 || $price <= 0) {
        log_create_work_debug('invalid_item', [
            'line' => $idx + 1,
            'client_id' => $client_id,
            'printer' => $printer,
            'material' => $material,
            'width_cm' => $w_cm,
            'height_cm' => $h_cm,
            'qty' => $qty,
            'submitted_price_per_m2' => $it['price_per_m2'] ?? null,
            'resolved_price_per_m2' => $price,
            'printer_price_key' => $printerKeyMap[strtolower($printer)] ?? null,
            'client_prices_for_printer' => $clientPrices[strtolower($printer)] ?? null,
            'client_prices_for_price_key' => isset($printerKeyMap[strtolower($printer)]) ? ($clientPrices[$printerKeyMap[strtolower($printer)]] ?? null) : null,
        ]);
        http_response_code(422);
        $lineNo = $idx + 1;
        echo json_encode([
            'ok' => false,
            'error' => "Material sətri natamamdır (sətir $lineNo, printer: $printer, material: $material, qiymət: " . round($price, 4) . ")",
            'errors' => [
                'items' => "Material sətri natamamdır (sətir $lineNo, printer: $printer, material: $material, qiymət: " . round($price, 4) . ")"
            ]
        ]);
        exit;
    }

    $m2 = ($w_cm / 100.0) * ($h_cm / 100.0);
    $line_total = $m2 * $qty * $price;
    $total_raw += $line_total;

    $rowsToInsert[] = [
        'material' => $material,
        'printer' => $printer,
        'w' => $w_cm,
        'h' => $h_cm,
        'qty' => $qty,
        'price' => $price,
        'line_total' => $line_total
    ];
}

$total_ceiled = (int)ceil($total_raw);
$createdBySql = created_by_value();

mysqli_begin_transaction($con);
try {
    foreach ($rowsToInsert as $r) {
        $materialKey = (string)$r['material'];
        $stockProduct = resolve_stock_product_name($materialKey, (string)$r['printer'], $materialToStockProduct, $materialStockMap, $printerKeyMap);
        $pad = resolve_material_stock_margin($materialKey, (string)$r['printer'], $materialStockMarginMap, $printerKeyMap, $stockProduct);
        $isTrackedMaterial = is_stock_tracked_material($materialKey, $materialToStockProduct, $materialStockMap);

        if ($pad <= 0 && !$isTrackedMaterial) {
            continue;
        }

        $wM = ((float)$r['w'] / 100.0) + $pad;
        $hM = ((float)$r['h'] / 100.0) + $pad;
        $qtyI = (int)$r['qty'];
        $deduct = $wM * $hM * $qtyI;

        $productEsc = mysqli_real_escape_string($con, $stockProduct);
        $resStock = mysqli_query($con, "SELECT id, sgrm FROM in_stock WHERE product='$productEsc' ORDER BY id DESC LIMIT 1");
        $stockRow = $resStock ? mysqli_fetch_assoc($resStock) : null;
        $before = $stockRow ? (float)$stockRow['sgrm'] : 0.0;
        $after = $before - $deduct;

        if ($after < -0.00001) {
            log_create_work_debug('insufficient_stock', [
                'client_id' => $client_id,
                'printer' => $r['printer'],
                'material' => $materialKey,
                'stock_product' => $stockProduct,
                'before' => $before,
                'deduct' => $deduct,
                'after' => $after,
            ]);
            mysqli_rollback($con);
            http_response_code(422);
            echo json_encode([
                'ok' => false,
                'error' => "Anbarda kifayət qədər stok yoxdur: $stockProduct (mövcud: " . round($before, 2) . " m², lazım: " . round($deduct, 2) . " m²)",
                'errors' => [
                    'stock' => "Anbarda kifayət qədər stok yoxdur: $stockProduct (mövcud: " . round($before, 2) . " m², lazım: " . round($deduct, 2) . " m²)"
                ]
            ]);
            exit;
        }

        $afterEsc = mysqli_real_escape_string($con, (string)$after);
        if ($stockRow && isset($stockRow['id']) && (int)$stockRow['id'] > 0) {
            $sid = (int)$stockRow['id'];
            $upd = mysqli_query($con, "UPDATE in_stock SET sgrm='$afterEsc' WHERE id=$sid");
            if ($upd === false) {
                throw new Exception('Stock update failed: ' . mysqli_error($con));
            }
        } elseif ($stockRow) {
            $upd = mysqli_query($con, "UPDATE in_stock SET sgrm='$afterEsc' WHERE product='$productEsc'");
            if ($upd === false) {
                throw new Exception('Stock product update failed: ' . mysqli_error($con));
            }
        } else {
            $dateEsc2 = mysqli_real_escape_string($con, $date);
            $opEsc2 = mysqli_real_escape_string($con, $op_id);
            $insStock = mysqli_query($con, "INSERT INTO in_stock (product, sgrm, date, op_id) VALUES ('$productEsc','$afterEsc','$dateEsc2','$opEsc2')");
            if ($insStock === false) {
                throw new Exception('Stock insert failed: ' . mysqli_error($con));
            }
        }
    }

    foreach ($rowsToInsert as $r) {
        $material_esc = mysqli_real_escape_string($con, $r['material']);
        $printer_esc = mysqli_real_escape_string($con, $r['printer']);
        $w = (float)$r['w'];
        $h = (float)$r['h'];
        $qty = (int)$r['qty'];
        $price = (float)$r['price'];
        $line_total = (float)$r['line_total'];

        $sql = "INSERT INTO work (client_id, work, size_h, size_w, piece, material, printer, date, op_id, total_amount, price_per_m2, created_by)
                VALUES ('$client_id_esc','$work_name_esc','$h','$w','$qty','$material_esc','$printer_esc','$date_esc','$op_id_esc','$line_total','$price', $createdBySql)";
        $ins = mysqli_query($con, $sql);
        if ($ins === false) {
            throw new Exception('Insert failed: ' . mysqli_error($con));
        }
    }

    recalc_client_balance($con, $client_id, $date, $op_id);

    mysqli_commit($con);
    echo json_encode([
        'ok' => true,
        'op_id' => $op_id,
        'total_raw' => $total_raw,
        'total_ceiled' => $total_ceiled
    ]);
} catch (Exception $e) {
    mysqli_rollback($con);
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Create work failed', 'db_error' => $e->getMessage()]);
}
