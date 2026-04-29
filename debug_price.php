<?php
require_once __DIR__ . '/config.php';

$client_id = 'CLI0010'; // The client from the screenshot

echo "=== DEBUGGING PRICE FOR CLIENT $client_id ===\n\n";

// Check legacy prices
$res = mysqli_query($con, "SELECT * FROM clients WHERE client_id='$client_id'");
$client = mysqli_fetch_assoc($res);

echo "1. Legacy prices in clients table:\n";
echo "   banner_matt: " . ($client['banner_matt'] ?? 'NULL') . "\n";
echo "   banner_glossy: " . ($client['banner_glossy'] ?? 'NULL') . "\n";
echo "   vinily_ch: " . ($client['vinily_ch'] ?? 'NULL') . "\n";
echo "   vinily_eu: " . ($client['vinily_eu'] ?? 'NULL') . "\n";
echo "   roland_banner_matt: " . ($client['roland_banner_matt'] ?? 'NULL') . "\n";
echo "   roland_vinily_ch: " . ($client['roland_vinily_ch'] ?? 'NULL') . "\n";

// Check price profiles
echo "\n2. Price profiles:\n";
$res = mysqli_query($con, "SELECT * FROM client_price_profiles WHERE client_id='$client_id' AND printer_key='roland'");
while ($row = mysqli_fetch_assoc($res)) {
    echo "   {$row['printer_key']} / {$row['material_key']} = {$row['price']}\n";
}

// Check materials
echo "\n3. Roland materials:\n";
$res = mysqli_query($con, "SELECT mat_key, label, category FROM materials WHERE category='roland'");
while ($row = mysqli_fetch_assoc($res)) {
    echo "   {$row['mat_key']} - {$row['label']}\n";
}

// Check what the API expects
echo "\n4. Stock product mapping:\n";
echo "   Looking for: vinily_ch\n";

// Test price resolution
echo "\n=== FIXING ===\n";

// Add missing roland prices
$roland_prices = [
    ['banner_matt', 2.8],
    ['banner_glossy', 3.3],
    ['vinily_ch', 2.3],
    ['vinily_eu', 4.3],
    ['black_matt', 3.8],
    ['black_glossy', 4.3],
];

foreach ($roland_prices as $p) {
    $mat = $p[0];
    $price = $p[1];
    
    // Update clients table (legacy)
    $col = "roland_$mat";
    mysqli_query($con, "UPDATE clients SET $col = $price WHERE client_id = '$client_id'");
    
    // Add to price profiles
    $res = mysqli_query($con, "SELECT id FROM client_price_profiles WHERE client_id='$client_id' AND printer_key='roland' AND material_key='$mat'");
    if (!$res || mysqli_num_rows($res) == 0) {
        mysqli_query($con, "INSERT INTO client_price_profiles (client_id, printer_key, material_key, price) VALUES ('$client_id', 'roland', '$mat', $price)");
    }
}

echo "Added roland prices for client $client_id\n";
echo "\nDone! Try adding work again with Roland printer.\n";
