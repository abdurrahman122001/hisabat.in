<?php
error_reporting(0);
header('Content-Type: application/json');

@include(__DIR__ . '/../config.php');
@include(__DIR__ . '/_auth.php');

require_role(['superadmin','admin']);

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

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$client_id = isset($data['client_id']) ? trim((string)$data['client_id']) : '';
$name = isset($data['name']) ? trim((string)$data['name']) : '';
$email = isset($data['email']) ? trim((string)$data['email']) : '';
$phone = isset($data['phone']) ? trim((string)$data['phone']) : '';
$phone = preg_replace('/\s+/', '', $phone);
$date = isset($data['date']) ? trim((string)$data['date']) : '';

$float = static function ($v): float {
    if ($v === null || $v === '') {
        return 0;
    }
    if (is_string($v)) {
        $v = str_replace(',', '.', $v);
    }
    return (float)$v;
};

// Pricing fields (optional)
$banner_matt = $float($data['konica_banner_matt'] ?? ($data['banner_matt'] ?? 0));
$banner_glossy = $float($data['konica_banner_glossy'] ?? ($data['banner_glossy'] ?? 0));
$vinily_ch = $float($data['konica_vinily_ch'] ?? ($data['vinily_ch'] ?? 0));
$vinily_eu = $float($data['konica_vinily_eu'] ?? ($data['vinily_eu'] ?? 0));
$banner_black_mate = $float($data['konica_banner_black_matt'] ?? ($data['banner_black_mate'] ?? 0));
$banner_black_glossy = $float($data['konica_banner_black_glossy'] ?? ($data['banner_black_glossy'] ?? 0));
$white_banner = $float($data['konica_white_banner'] ?? ($data['white_banner'] ?? 0));
$white_vinily = $float($data['konica_white_vinily'] ?? ($data['white_vinily'] ?? 0));
$backlead = $float($data['konica_backleed'] ?? ($data['backlead'] ?? 0));
$flex = $float($data['konica_flax'] ?? ($data['flex'] ?? 0));
$banner_440_white = $float($data['konica_banner_404_white'] ?? ($data['banner_440_white'] ?? 0));
$banner_440_black = $float($data['konica_banner_440_black'] ?? ($data['banner_440_black'] ?? 0));

$roland_banner_matt = $float($data['roland_banner_matt'] ?? 0);
$roland_banner_glossy = $float($data['roland_banner_glossy'] ?? 0);
$roland_vinily_ch = $float($data['roland_vinily_ch'] ?? 0);
$roland_vinily_eu = $float($data['roland_vinily_eu'] ?? 0);
$roland_black_matt = $float($data['roland_black_matt'] ?? 0);
$roland_black_glossy = $float($data['roland_black_glossy'] ?? 0);

$cut_wood = $float($data['laser_cut_wood'] ?? ($data['cut_wood'] ?? 0));
$cut_forex = $float($data['laser_cut_forex'] ?? ($data['cut_forex'] ?? 0));
$cut_orch = $float($data['laser_cut_orch'] ?? ($data['cut_orch'] ?? 0));
$graw_wood = $float($data['laser_graw_wood'] ?? ($data['graw_wood'] ?? 0));
$graw_forex = $float($data['laser_graw_cut_forex'] ?? ($data['graw_forex'] ?? 0));
$graw_orch = $float($data['laser_graw_cut_orch'] ?? ($data['graw_orch'] ?? 0));

$dynamicPrices = [
    'konica' => [
        'banner_matt' => $banner_matt,
        'banner_glossy' => $banner_glossy,
        'vinily_ch' => $vinily_ch,
        'vinily_eu' => $vinily_eu,
        'banner_black_mate' => $banner_black_mate,
        'banner_black_glossy' => $banner_black_glossy,
        'white_banner' => $white_banner,
        'white_vinily' => $white_vinily,
        'backlead' => $backlead,
        'flex' => $flex,
        'banner_440_white' => $banner_440_white,
        'banner_440_black' => $banner_440_black,
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
        'cut_wood' => $cut_wood,
        'cut_forex' => $cut_forex,
        'cut_orch' => $cut_orch,
        'graw_wood' => $graw_wood,
        'graw_forex' => $graw_forex,
        'graw_orch' => $graw_orch,
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

$errors = [];
if ($client_id === '') $errors['client_id'] = 'client_id tələb olunur';
if ($name === '') $errors['name'] = 'Ad doldurulmalıdır';
if ($email === '') $errors['email'] = 'Email doldurulmalıdır';
if ($phone === '') $errors['phone'] = 'Telefon doldurulmalıdır';
if ($date === '') $errors['date'] = 'Tarix doldurulmalıdır';

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

$client_id_esc = mysqli_real_escape_string($con, $client_id);
$name_esc = mysqli_real_escape_string($con, $name);
$email_esc = mysqli_real_escape_string($con, $email);
$phone_esc = mysqli_real_escape_string($con, $phone);
$date_esc = mysqli_real_escape_string($con, $date);

$exists = mysqli_query($con, "SELECT 1 FROM clients WHERE client_id = '$client_id_esc' LIMIT 1");
if ($exists === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'db_error' => mysqli_error($con)]);
    exit;
}
if (mysqli_num_rows($exists) === 0) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Client not found']);
    exit;
}

$dup = mysqli_query($con, "SELECT 1 FROM clients WHERE phone = '$phone_esc' AND client_id != '$client_id_esc' LIMIT 1");
if ($dup && mysqli_num_rows($dup) > 0) {
    http_response_code(409);
    echo json_encode(['ok' => false, 'errors' => ['phone' => 'Bu telefon nömrəsi ilə müştəri artıq mövcuddur']]);
    exit;
}

$sql = "UPDATE clients SET name='$name_esc', email='$email_esc', phone='$phone_esc', date='$date_esc' WHERE client_id='$client_id_esc'";
$sql = "UPDATE clients SET 
name='$name_esc',
email='$email_esc',
phone='$phone_esc',
date='$date_esc',
banner_matt='$banner_matt',
banner_glossy='$banner_glossy',
vinily_ch='$vinily_ch',
vinily_eu='$vinily_eu',
banner_black_mate='$banner_black_mate',
banner_black_glossy='$banner_black_glossy',
white_banner='$white_banner',
white_vinily='$white_vinily',
backlead='$backlead',
flex='$flex',
banner_440_white='$banner_440_white',
banner_440_black='$banner_440_black',
roland_banner_matt='$roland_banner_matt',
roland_banner_glossy='$roland_banner_glossy',
roland_vinily_ch='$roland_vinily_ch',
roland_vinily_eu='$roland_vinily_eu',
roland_black_matt='$roland_black_matt',
roland_black_glossy='$roland_black_glossy',
cut_wood='$cut_wood',
cut_forex='$cut_forex',
cut_orch='$cut_orch',
graw_wood='$graw_wood',
graw_forex='$graw_forex',
graw_orch='$graw_orch'
WHERE client_id='$client_id_esc'";
$upd = mysqli_query($con, $sql);
if ($upd === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Update failed', 'db_error' => mysqli_error($con)]);
    exit;
}

mysqli_query($con, "UPDATE payment SET date='$date_esc' WHERE client_id='$client_id_esc'");

upsert_client_price_profiles($con, $client_id, $dynamicPrices);

echo json_encode(['ok' => true]);
