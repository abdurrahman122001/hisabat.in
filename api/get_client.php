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

$client_id = isset($_GET['client_id']) ? trim((string)$_GET['client_id']) : '';
if ($client_id === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => ['client_id' => 'client_id tələb olunur']]);
    exit;
}

$client_id_esc = mysqli_real_escape_string($con, $client_id);
$res = mysqli_query($con, "SELECT * FROM clients WHERE client_id = '$client_id_esc' LIMIT 1");
if ($res === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'db_error' => mysqli_error($con)]);
    exit;
}

$row = mysqli_fetch_assoc($res);
if (!$row) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Client not found']);
    exit;
}

$prices = build_available_price_map($con);
$dynamicPrices = load_client_price_profiles($con, $client_id);
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

$row['prices'] = $prices;

echo json_encode(['ok' => true, 'client' => $row]);
