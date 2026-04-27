<?php
error_reporting(0);
header('Content-Type: application/json');

@include(__DIR__ . '/../config.php');

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

function load_printer_alias_map(mysqli $con): array
{
    if (!table_exists($con, 'printers')) {
        return [];
    }

    $aliases = [];
    $res = @mysqli_query($con, "SELECT name, price_key FROM printers");
    if (!$res) {
        return $aliases;
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $name = strtolower(trim((string)($row['name'] ?? '')));
        $priceKey = strtolower(trim((string)($row['price_key'] ?? '')));
        if ($name !== '' && $priceKey !== '') {
            $aliases[$name] = $priceKey;
        }
        if ($priceKey !== '') {
            $aliases[$priceKey] = $priceKey;
        }
    }

    return $aliases;
}

function build_available_price_map(mysqli $con): array
{
    if (!table_exists($con, 'materials')) {
        return [];
    }

    $priceMap = [];
    $res = @mysqli_query($con, "SELECT mat_key, category, status FROM materials ORDER BY id ASC");
    if (!$res) {
        return $priceMap;
    }

    while ($row = mysqli_fetch_assoc($res)) {
        if ((int)($row['status'] ?? 1) !== 1) {
            continue;
        }
        $printerKey = strtolower(trim((string)($row['category'] ?? '')));
        $materialKey = trim((string)($row['mat_key'] ?? ''));
        if ($printerKey === '' || $materialKey === '') {
            continue;
        }
        if (!isset($priceMap[$printerKey]) || !is_array($priceMap[$printerKey])) {
            $priceMap[$printerKey] = [];
        }
        if (!array_key_exists($materialKey, $priceMap[$printerKey])) {
            $priceMap[$printerKey][$materialKey] = null;
        }
    }

    return $priceMap;
}

ensure_client_price_profiles_table($con);

$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
if ($q === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => ['q' => 'Axtarış tələb olunur']]);
    exit;
}

$qPhone = preg_replace('/\s+/', '', $q);
$qEsc = mysqli_real_escape_string($con, $q);
$qPhoneEsc = mysqli_real_escape_string($con, $qPhone);

$sql = "SELECT * FROM clients WHERE client_id LIKE '%$qEsc%' OR phone LIKE '%$qPhoneEsc%' LIMIT 1";
$res = mysqli_query($con, $sql);
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'db_error' => mysqli_error($con)]);
    exit;
}

$row = mysqli_fetch_assoc($res);
if (!$row) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Customer not found']);
    exit;
}

// Map prices from client profile to material keys used in UI
$prices = build_available_price_map($con);
$legacyPrices = [
    'konica' => [
        'banner_matt' => $row['banner_matt'] ?? null,
        'banner_glossy' => $row['banner_glossy'] ?? null,
        'vinily_ch' => $row['vinily_ch'] ?? null,
        'vinily_eu' => $row['vinily_eu'] ?? null,
        'banner_black_mate' => $row['banner_black_mate'] ?? null,
        'banner_black_glossy' => $row['banner_black_glossy'] ?? null,
        'white_banner' => $row['white_banner'] ?? null,
        'white_vinily' => $row['white_vinily'] ?? null,
        'backlead' => $row['backlead'] ?? null,
        'flex' => $row['flex'] ?? null,
        'banner_440_white' => $row['banner_440_white'] ?? null,
        'banner_440_black' => $row['banner_440_black'] ?? null,
    ],
    'roland' => [
        'banner_matt' => $row['roland_banner_matt'] ?? null,
        'banner_glossy' => $row['roland_banner_glossy'] ?? null,
        'vinily_ch' => $row['roland_vinily_ch'] ?? null,
        'vinily_eu' => $row['roland_vinily_eu'] ?? null,
        'black_matt' => $row['roland_black_matt'] ?? null,
        'black_glossy' => $row['roland_black_glossy'] ?? null,
    ],
    'laser' => [
        'cut_wood' => $row['cut_wood'] ?? null,
        'cut_forex' => $row['cut_forex'] ?? null,
        'cut_orch' => $row['cut_orch'] ?? null,
        'graw_wood' => $row['graw_wood'] ?? null,
        'graw_forex' => $row['graw_forex'] ?? null,
        'graw_orch' => $row['graw_orch'] ?? null,
    ]
];

foreach ($legacyPrices as $printerKey => $materials) {
    if (!isset($prices[$printerKey]) || !is_array($prices[$printerKey])) {
        $prices[$printerKey] = [];
    }
    foreach ($materials as $materialKey => $price) {
        $prices[$printerKey][$materialKey] = $price;
    }
}

$dynamicPrices = load_client_price_profiles($con, (string)($row['client_id'] ?? ''));
foreach ($dynamicPrices as $printerKey => $materials) {
    if (!isset($prices[$printerKey]) || !is_array($prices[$printerKey])) {
        $prices[$printerKey] = [];
    }
    foreach ($materials as $materialKey => $price) {
        $prices[$printerKey][$materialKey] = $price;
    }
}

$printerAliases = load_printer_alias_map($con);
foreach ($printerAliases as $printerName => $priceKey) {
    if (!isset($prices[$priceKey]) || !is_array($prices[$priceKey])) {
        continue;
    }
    if (!isset($prices[$printerName]) || !is_array($prices[$printerName])) {
        $prices[$printerName] = [];
    }
    foreach ($prices[$priceKey] as $materialKey => $price) {
        $prices[$printerName][$materialKey] = $price;
    }
}

echo json_encode([
    'ok' => true,
    'customer' => [
        'client_id' => $row['client_id'] ?? '',
        'name' => $row['name'] ?? '',
        'phone' => $row['phone'] ?? '',
        'email' => $row['email'] ?? ''
    ],
    'prices' => $prices
]);
