<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

@include(__DIR__ . '/../config.php');

if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
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

function upsert_client_price_profiles(mysqli $con, string $clientId, array $prices): void
{
    $clientIdEsc = mysqli_real_escape_string($con, $clientId);
    @mysqli_query($con, "DELETE FROM client_price_profiles WHERE client_id = '$clientIdEsc'");

    foreach ($prices as $printerKey => $materials) {
        if (!is_array($materials)) {
            continue;
        }

        $printerKeyEsc = mysqli_real_escape_string($con, (string)$printerKey);
        foreach ($materials as $materialKey => $price) {
            $priceNum = is_numeric($price) ? (float)$price : 0.0;
            if ($priceNum <= 0) {
                continue;
            }

            $materialKeyEsc = mysqli_real_escape_string($con, (string)$materialKey);
            $priceEsc = mysqli_real_escape_string($con, (string)$priceNum);
            @mysqli_query(
                $con,
                "INSERT INTO client_price_profiles (client_id, printer_key, material_key, price)
                 VALUES ('$clientIdEsc', '$printerKeyEsc', '$materialKeyEsc', '$priceEsc')"
            );
        }
    }
}

ensure_client_price_profiles_table($con);

function generate_next_client_id(mysqli $con): string
{
    $prefix = 'CWZ ';
    $maxNum = 0;

    $res = mysqli_query($con, "SELECT client_id FROM clients");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $cid = (string)($row['client_id'] ?? '');
            if (preg_match('/(\d+)/', $cid, $m)) {
                $n = (int)$m[1];
                if ($n > $maxNum) $maxNum = $n;
            }
        }
    }

    $hasDelLog = mysqli_query($con, "SHOW TABLES LIKE 'delete_client_log'");
    if ($hasDelLog && mysqli_num_rows($hasDelLog) > 0) {
        $res2 = mysqli_query($con, "SELECT client_id FROM delete_client_log");
        if ($res2) {
            while ($row2 = mysqli_fetch_assoc($res2)) {
                $cid2 = (string)($row2['client_id'] ?? '');
                if (preg_match('/(\d+)/', $cid2, $m2)) {
                    $n2 = (int)$m2[1];
                    if ($n2 > $maxNum) $maxNum = $n2;
                }
            }
        }
    }

    $next = $maxNum + 1;
    return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$name = isset($data['name']) ? trim((string)$data['name']) : '';
$email = isset($data['email']) ? trim((string)$data['email']) : '';
$phone = isset($data['phone']) ? trim((string)$data['phone']) : '';
$phone = preg_replace('/\s+/', '', $phone);
$date = isset($data['date']) ? trim((string)$data['date']) : '';
if ($date === '') {
    $date = date('Y-m-d');
}

$errors = [];
if ($name === '') {
    $errors['name'] = 'Ad doldurulmalıdır';
}
if ($email === '') {
    $errors['email'] = 'Email doldurulmalıdır';
}
if ($phone === '') {
    $errors['phone'] = 'Telefon doldurulmalıdır';
}

if ($phone !== '' && !preg_match('/^\d+$/', $phone)) {
    $errors['phone'] = 'Telefon yalnız rəqəmlərdən ibarət olmalıdır';
}

if ($email !== '' && strpos($email, '@') === false) {
    $errors['email'] = 'Email düzgün deyil';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

$name_esc = mysqli_real_escape_string($con, $name);
$email_esc = mysqli_real_escape_string($con, $email);
$phone_esc = mysqli_real_escape_string($con, $phone);
$date_esc = mysqli_real_escape_string($con, $date);

$check_sql = "SELECT 1 FROM clients WHERE phone = '$phone_esc' LIMIT 1";
$check_res = mysqli_query($con, $check_sql);
if ($check_res && mysqli_num_rows($check_res) > 0) {
    http_response_code(409);
    echo json_encode(['ok' => false, 'errors' => ['phone' => 'Bu telefon nömrəsi ilə müştəri artıq mövcuddur']]);
    exit;
}

$client_id = generate_next_client_id($con);
$client_id_esc = mysqli_real_escape_string($con, $client_id);

$float = static function ($v): float {
    if ($v === null || $v === '') {
        return 0;
    }
    if (is_string($v)) {
        $v = str_replace(',', '.', $v);
    }
    return (float)$v;
};

$konica_banner_matt = $float($data['konica_banner_matt'] ?? 0);
$konica_banner_glossy = $float($data['konica_banner_glossy'] ?? 0);
$konica_vinily_ch = $float($data['konica_vinily_ch'] ?? 0);
$konica_vinily_eu = $float($data['konica_vinily_eu'] ?? 0);
$konica_banner_black_matt = $float($data['konica_banner_black_matt'] ?? 0);
$konica_banner_black_glossy = $float($data['konica_banner_black_glossy'] ?? 0);
$konica_white_banner = $float($data['konica_white_banner'] ?? 0);
$konica_white_vinily = $float($data['konica_white_vinily'] ?? 0);
$konica_backleed = $float($data['konica_backleed'] ?? 0);
$konica_flax = $float($data['konica_flax'] ?? 0);
$konica_banner_404_white = $float($data['konica_banner_404_white'] ?? 0);
$konica_banner_440_black = $float($data['konica_banner_440_black'] ?? 0);

$roland_banner_matt = $float($data['roland_banner_matt'] ?? 0);
$roland_banner_glossy = $float($data['roland_banner_glossy'] ?? 0);
$roland_vinily_ch = $float($data['roland_vinily_ch'] ?? 0);
$roland_vinily_eu = $float($data['roland_vinily_eu'] ?? 0);
$roland_black_matt = $float($data['roland_black_matt'] ?? 0);
$roland_black_glossy = $float($data['roland_black_glossy'] ?? 0);

$laser_cut_wood = $float($data['laser_cut_wood'] ?? 0);
$laser_cut_forex = $float($data['laser_cut_forex'] ?? 0);
$laser_cut_orch = $float($data['laser_cut_orch'] ?? 0);
$laser_graw_wood = $float($data['laser_graw_wood'] ?? 0);
$laser_graw_cut_forex = $float($data['laser_graw_cut_forex'] ?? 0);
$laser_graw_cut_orch = $float($data['laser_graw_cut_orch'] ?? 0);

$dynamicPrices = [
    'konica' => [
        'banner_matt' => $konica_banner_matt,
        'banner_glossy' => $konica_banner_glossy,
        'vinily_ch' => $konica_vinily_ch,
        'vinily_eu' => $konica_vinily_eu,
        'banner_black_mate' => $konica_banner_black_matt,
        'banner_black_glossy' => $konica_banner_black_glossy,
        'white_banner' => $konica_white_banner,
        'white_vinily' => $konica_white_vinily,
        'backlead' => $konica_backleed,
        'flex' => $konica_flax,
        'banner_440_white' => $konica_banner_404_white,
        'banner_440_black' => $konica_banner_440_black,
    ],
    'roland' => [
        'banner_matt' => $roland_banner_matt,
        'banner_glossy' => $roland_banner_glossy,
        'vinily_ch' => $roland_vinily_ch,
        'vinily_eu' => $roland_vinily_eu,
        'black_matt' => $roland_black_matt,
        'black_glossy' => $roland_black_glossy,
    ],
    'laser' => [
        'cut_wood' => $laser_cut_wood,
        'cut_forex' => $laser_cut_forex,
        'cut_orch' => $laser_cut_orch,
        'graw_wood' => $laser_graw_wood,
        'graw_forex' => $laser_graw_cut_forex,
        'graw_orch' => $laser_graw_cut_orch,
    ],
];

if (isset($data['prices']) && is_array($data['prices'])) {
    foreach ($data['prices'] as $printerKey => $materials) {
        if (!is_array($materials)) {
            continue;
        }

        if (!isset($dynamicPrices[$printerKey]) || !is_array($dynamicPrices[$printerKey])) {
            $dynamicPrices[$printerKey] = [];
        }

        foreach ($materials as $materialKey => $price) {
            $dynamicPrices[$printerKey][$materialKey] = $float($price);
        }
    }
}

// Require at least 3 product prices
$priceValues = [];
foreach ($dynamicPrices as $printerMaterials) {
    if (!is_array($printerMaterials)) {
        continue;
    }
    foreach ($printerMaterials as $priceValue) {
        $priceValues[] = $priceValue;
    }
}

$filledCount = 0;
foreach ($priceValues as $v) {
    if (is_numeric($v) && (float)$v > 0) {
        $filledCount++;
    }
}

if ($filledCount < 3) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'errors' => [
            'prices' => 'Ən azı 3 məhsula qiymət yazılmalıdır'
        ]
    ]);
    exit;
}

$sql = "INSERT INTO clients 
(`client_id`, `name`, `email`, `phone`, `date`,
`banner_matt`, `banner_glossy`, `vinily_ch`, `vinily_eu`, 
`banner_black_mate`, `banner_black_glossy`, `white_banner`, `white_vinily`, 
`backlead`, `flex`, `banner_440_white`, `banner_440_black`, 
`roland_banner_matt`, `roland_banner_glossy`, `roland_vinily_ch`, `roland_vinily_eu`, 
`roland_black_matt`, `roland_black_glossy`, `cut_wood`, `cut_forex`, 
`cut_orch`, `graw_wood`, `graw_forex`, `graw_orch`,`advanced`,`outstanding_debit`,`total_amount`) 
VALUES 
('$client_id_esc', '$name_esc', '$email_esc', '$phone_esc', '$date_esc',
'$konica_banner_matt', '$konica_banner_glossy', '$konica_vinily_ch', '$konica_vinily_eu',
'$konica_banner_black_matt', '$konica_banner_black_glossy', '$konica_white_banner', '$konica_white_vinily',
'$konica_backleed', '$konica_flax', '$konica_banner_404_white', '$konica_banner_440_black',
'$roland_banner_matt', '$roland_banner_glossy', '$roland_vinily_ch', '$roland_vinily_eu',
'$roland_black_matt', '$roland_black_glossy', '$laser_cut_wood', '$laser_cut_forex',
'$laser_cut_orch', '$laser_graw_wood', '$laser_graw_cut_forex', '$laser_graw_cut_orch', '0', '0', '0')";

$insert = mysqli_query($con, $sql);
if ($insert === false) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Insert failed',
        'db_error' => mysqli_error($con)
    ]);
    exit;
}

mysqli_query($con, "INSERT INTO payment(`client_id`, `name`, `email`, `phone`, `date`) VALUES ('$client_id_esc','$name_esc','$email_esc','$phone_esc','$date_esc')");

upsert_client_price_profiles($con, $client_id, $dynamicPrices);

echo json_encode(['ok' => true, 'client_id' => $client_id]);
