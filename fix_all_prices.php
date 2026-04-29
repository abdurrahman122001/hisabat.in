<?php
require_once __DIR__ . '/config.php';

echo "=== FIXING ALL CLIENT PRICES ===\n\n";

// Get all clients
$res = mysqli_query($con, "SELECT client_id FROM clients");
$clients = [];
while ($row = mysqli_fetch_assoc($res)) {
    $clients[] = $row['client_id'];
}

echo "Found " . count($clients) . " clients\n\n";

// Konica prices
$konica_prices = [
    ['banner_matt', 2.5],
    ['banner_glossy', 3.0],
    ['vinily_ch', 2.0],
    ['vinily_eu', 4.0],
    ['banner_black_mate', 3.5],
    ['banner_black_glossy', 4.0],
    ['white_banner', 4.5],
    ['white_vinily', 5.0],
    ['backlead', 1.5],
    ['flex', 2.5],
    ['banner_440_white', 3.0],
    ['banner_440_black', 3.2],
];

// Roland prices  
$roland_prices = [
    ['banner_matt', 2.8],
    ['banner_glossy', 3.3],
    ['vinily_ch', 2.3],
    ['vinily_eu', 4.3],
    ['black_matt', 3.8],
    ['black_glossy', 4.3],
];

// Laser prices
$laser_prices = [
    ['cut_wood', 15.0],
    ['cut_forex', 25.0],
    ['cut_orch', 20.0],
    ['graw_wood', 12.0],
    ['graw_forex', 18.0],
    ['graw_orch', 15.0],
];

$totalProfiles = 0;

foreach ($clients as $cid) {
    echo "Processing $cid...\n";
    
    // 1. Update legacy columns in clients table
    $updates = [];
    foreach ($konica_prices as $p) {
        $col = $p[0];
        $price = $p[1];
        $updates[] = "$col = $price";
        
        // Add price profile
        $res = mysqli_query($con, "SELECT id FROM client_price_profiles WHERE client_id='$cid' AND printer_key='konica' AND material_key='$col'");
        if (!$res || mysqli_num_rows($res) == 0) {
            mysqli_query($con, "INSERT INTO client_price_profiles (client_id, printer_key, material_key, price) VALUES ('$cid', 'konica', '$col', $price)");
            $totalProfiles++;
        }
    }
    
    foreach ($roland_prices as $p) {
        $col = 'roland_' . $p[0];
        $price = $p[1];
        $updates[] = "$col = $price";
        
        $mat = $p[0];
        $res = mysqli_query($con, "SELECT id FROM client_price_profiles WHERE client_id='$cid' AND printer_key='roland' AND material_key='$mat'");
        if (!$res || mysqli_num_rows($res) == 0) {
            mysqli_query($con, "INSERT INTO client_price_profiles (client_id, printer_key, material_key, price) VALUES ('$cid', 'roland', '$mat', $price)");
            $totalProfiles++;
        }
    }
    
    foreach ($laser_prices as $p) {
        $col = $p[0];
        $price = $p[1];
        $updates[] = "$col = $price";
        
        $res = mysqli_query($con, "SELECT id FROM client_price_profiles WHERE client_id='$cid' AND printer_key='laser' AND material_key='$col'");
        if (!$res || mysqli_num_rows($res) == 0) {
            mysqli_query($con, "INSERT INTO client_price_profiles (client_id, printer_key, material_key, price) VALUES ('$cid', 'laser', '$col', $price)");
            $totalProfiles++;
        }
    }
    
    // Execute update
    $sql = "UPDATE clients SET " . implode(', ', $updates) . " WHERE client_id = '$cid'";
    mysqli_query($con, $sql);
}

echo "\n===========================================\n";
echo "FIXED ALL CLIENTS!\n";
echo "Added $totalProfiles price profiles\n";
echo "===========================================\n";
echo "\nNow try adding work again with any client.\n";
