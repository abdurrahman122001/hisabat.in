<?php
require_once __DIR__ . '/config.php';

echo "=== CHECKING PRINTERS ===\n\n";

$res = mysqli_query($con, "SELECT name, price_key, status FROM printers");
echo "Printers in database:\n";
while ($row = mysqli_fetch_assoc($res)) {
    echo "   '{$row['name']}' => price_key: '{$row['price_key']}'\n";
}

// Now simulate what the API does
$printerName = 'Roland RF-640'; // from error message
$printerLower = strtolower(trim($printerName));

echo "\n\n=== SIMULATING API LOOKUP ===\n";
echo "Input printer: '$printerName'\n";
echo "Lowercase: '$printerLower'\n";

// Load printer key map
$map = [];
$res = mysqli_query($con, "SELECT name, price_key FROM printers");
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

echo "\nPrinter key map:\n";
foreach ($map as $k => $v) {
    echo "   '$k' => '$v'\n";
}

echo "\nLookup for '$printerLower': ";
if (isset($map[$printerLower])) {
    echo "'{$map[$printerLower]}' ✓\n";
} else {
    echo "NOT FOUND ✗\n";
}

// Check if the exact name exists
$res = mysqli_query($con, "SELECT * FROM printers WHERE LOWER(name) = '$printerLower'");
if ($res && mysqli_num_rows($res) > 0) {
    echo "\nPrinter found in database:\n";
    while ($row = mysqli_fetch_assoc($res)) {
        echo "   name: '{$row['name']}', price_key: '{$row['price_key']}'\n";
    }
} else {
    echo "\n✗ Printer '$printerName' NOT found in database!\n";
}
