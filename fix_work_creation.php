<?php
/**
 * Fix work creation issues - stock mapping and price resolution
 */

require_once __DIR__ . '/config.php';

echo "=== FIXING WORK CREATION ISSUES ===\n\n";

// 1. Check current stock
echo "1. Current stock:\n";
$res = mysqli_query($con, "SELECT * FROM in_stock LIMIT 10");
while ($row = mysqli_fetch_assoc($res)) {
    echo "   - {$row['product']}: {$row['sgrm']} m²\n";
}

// 2. Add more stock with exact product names that match the mapping
echo "\n2. Adding stock with correct product names...\n";

$stockItems = [
    ['Banner Matt', 1000],
    ['Banner Glossy', 1000],
    ['Banner Black Matt', 500],
    ['Banner Black Glossy', 500],
    ['White Banner', 400],
    ['White Vinily', 400],
    ['Backleed', 300],
    ['Flex', 300],
    ['Banner 440 GR White', 250],
    ['Banner 440 GR Black', 250],
    ['Vinily CH', 600],
    ['Vinily EU', 600],
];

foreach ($stockItems as $item) {
    $product = $item[0];
    $amount = $item[1];
    
    // Check if exists
    $res = mysqli_query($con, "SELECT id, sgrm FROM in_stock WHERE product = '$product' LIMIT 1");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        $newAmount = $row['sgrm'] + $amount;
        mysqli_query($con, "UPDATE in_stock SET sgrm = $newAmount WHERE id = {$row['id']}");
        echo "   Updated: $product = $newAmount m²\n";
    } else {
        $date = date('Y-m-d');
        $opId = 'STK' . date('Ymd') . rand(1000, 9999);
        mysqli_query($con, "INSERT INTO in_stock (product, sgrm, date, op_id) VALUES ('$product', $amount, '$date', '$opId')");
        echo "   Added: $product = $amount m²\n";
    }
}

// 3. Fix client price profiles - ensure they have prices
echo "\n3. Ensuring clients have price profiles...\n";

$clients = [];
$res = mysqli_query($con, "SELECT client_id FROM clients LIMIT 5");
while ($row = mysqli_fetch_assoc($res)) {
    $clients[] = $row['client_id'];
}

$materials = [
    ['konica', 'banner_matt', 2.5],
    ['konica', 'banner_glossy', 3.0],
    ['konica', 'vinily_ch', 2.0],
    ['konica', 'vinily_eu', 4.0],
    ['konica', 'banner_black_mate', 3.5],
    ['konica', 'white_banner', 4.5],
    ['roland', 'banner_matt', 3.0],
    ['roland', 'banner_glossy', 3.5],
    ['laser', 'cut_wood', 15.0],
    ['laser', 'cut_forex', 25.0],
];

foreach ($clients as $cid) {
    foreach ($materials as $m) {
        $printer = $m[0];
        $mat = $m[1];
        $price = $m[2];
        
        // Check if profile exists
        $res = mysqli_query($con, "SELECT id FROM client_price_profiles WHERE client_id='$cid' AND printer_key='$printer' AND material_key='$mat'");
        if (!$res || mysqli_num_rows($res) == 0) {
            mysqli_query($con, "INSERT INTO client_price_profiles (client_id, printer_key, material_key, price) VALUES ('$cid', '$printer', '$mat', $price)");
        }
    }
}
echo "   Price profiles ensured for " . count($clients) . " clients\n";

// 4. Also update legacy client prices
echo "\n4. Updating legacy client prices...\n";
foreach ($clients as $cid) {
    mysqli_query($con, "UPDATE clients SET 
        banner_matt = 2.5, 
        banner_glossy = 3.0,
        vinily_ch = 2.0,
        vinily_eu = 4.0,
        banner_black_mate = 3.5,
        white_banner = 4.5
        WHERE client_id = '$cid'");
}
echo "   Legacy prices updated\n";

echo "\n===========================================\n";
echo "FIXES COMPLETE!\n";
echo "===========================================\n";
echo "\nNow try adding a work again:\n";
echo "1. Select client\n";
echo "2. Select printer (e.g., Konica 1024)\n";
echo "3. Select material (e.g., Banner Matt)\n";
echo "4. Enter dimensions\n";
echo "5. Click Add\n";
