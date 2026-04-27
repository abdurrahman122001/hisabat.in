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

$products = [];
$inactiveProducts = [];

$defaults = [
    'Banner Matt',
    'Banner Glossy',
    'Vinily CH',
    'Vinily EU',
    'Banner Black Matt',
    'Banner Black Glossy',
    'White Banner',
    'White Vinily',
    'Backleed',
    'Flex',
    'Banner 440 GR White',
    'Banner 440 GR Black'
];

foreach ($defaults as $d) {
    $products[$d] = true;
}

if (table_exists($con, 'materials')) {
    $materialsRes = mysqli_query($con, "SELECT mat_key, label, status FROM materials ORDER BY id ASC");
    if ($materialsRes) {
        while ($material = mysqli_fetch_assoc($materialsRes)) {
            $product = trim(stock_product_name_from_material($material));
            if ($product === '') {
                continue;
            }

            if ((int)($material['status'] ?? 1) === 1) {
                $products[$product] = true;
                unset($inactiveProducts[$product]);
            } else {
                $inactiveProducts[$product] = true;
                unset($products[$product]);
            }
        }
    }
}

if (table_exists($con, 'in_stock')) {
    $res = mysqli_query($con, "SELECT DISTINCT product FROM in_stock WHERE product IS NOT NULL AND product != '' ORDER BY product ASC");
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $p = trim((string)($r['product'] ?? ''));
            if ($p !== '' && !isset($inactiveProducts[$p])) $products[$p] = true;
        }
    }
}

if (table_exists($con, 'add_stock')) {
    $res2 = mysqli_query($con, "SELECT DISTINCT product FROM add_stock WHERE product IS NOT NULL AND product != '' ORDER BY product ASC");
    if ($res2) {
        while ($r2 = mysqli_fetch_assoc($res2)) {
            $p2 = trim((string)($r2['product'] ?? ''));
            if ($p2 !== '' && !isset($inactiveProducts[$p2])) $products[$p2] = true;
        }
    }
}

$list = array_keys($products);
sort($list, SORT_NATURAL | SORT_FLAG_CASE);

echo json_encode(['ok' => true, 'products' => $list]);
