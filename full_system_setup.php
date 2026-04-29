<?php
/**
 * Complete System Setup - Seeds all data with proper relationships
 */

require_once __DIR__ . '/config.php';

if (!$con || $con->connect_errno) {
    die("Database connection failed\n");
}

echo "=== COMPLETE SYSTEM SETUP ===\n\n";

// 1. Clean slate - clear existing data
echo "1. Clearing existing data...\n";
mysqli_query($con, "SET FOREIGN_KEY_CHECKS = 0");
mysqli_query($con, "DELETE FROM client_price_profiles");
mysqli_query($con, "DELETE FROM work");
mysqli_query($con, "DELETE FROM payment");
mysqli_query($con, "DELETE FROM cost");
mysqli_query($con, "DELETE FROM in_stock");
mysqli_query($con, "DELETE FROM clients");
mysqli_query($con, "DELETE FROM materials");
mysqli_query($con, "DELETE FROM printers");
mysqli_query($con, "DELETE FROM users");
mysqli_query($con, "DELETE FROM admin");
mysqli_query($con, "SET FOREIGN_KEY_CHECKS = 1");
echo "   Done\n";

// 2. Create Admin
echo "\n2. Creating admin...\n";
mysqli_query($con, "INSERT INTO admin (email, password) VALUES ('admin@hisabat.az', 'admin123')");
echo "   admin@hisabat.az / admin123\n";

// 3. Create Users
echo "\n3. Creating users...\n";
$users = [
    ['user1@hisabat.az', password_hash('user123', PASSWORD_DEFAULT), 'user'],
    ['manager@hisabat.az', password_hash('manager123', PASSWORD_DEFAULT), 'admin'],
];
foreach ($users as $u) {
    mysqli_query($con, "INSERT INTO users (email, password_hash, role, status) VALUES ('{$u[0]}', '{$u[1]}', '{$u[2]}', 1)");
}
echo "   Created " . count($users) . " users\n";

// 4. Create Printers with proper price_key values
echo "\n4. Creating printers with categories...\n";
$printers = [
    ['Konica 1024', 'konica'],
    ['Konica 512', 'konica'],
    ['Konica 512i', 'konica'],
    ['Roland RF-640', 'roland'],
    ['Roland XF-640', 'roland'],
    ['Roland VS-640', 'roland'],
    ['Laser Cutter', 'laser'],
    ['Laser Cutting Machine', 'laser'],
    ['Grawer Machine', 'laser'],
];
$printerIds = [];
foreach ($printers as $p) {
    mysqli_query($con, "INSERT INTO printers (name, price_key, status) VALUES ('{$p[0]}', '{$p[1]}', 1)");
    $printerIds[$p[0]] = mysqli_insert_id($con);
}
echo "   Created " . count($printers) . " printers\n";

// 5. Create Materials linked to printer categories
echo "\n5. Creating materials by printer category...\n";
$materials = [
    // KONICA materials (banner printing)
    ['banner_matt', 'Banner Matt (China)', 'konica', 2.5],
    ['banner_glossy', 'Banner Glossy (China)', 'konica', 3.0],
    ['vinily_ch', 'Vinil China', 'konica', 2.0],
    ['vinily_eu', 'Vinil Europe', 'konica', 4.0],
    ['banner_black_mate', 'Banner Black Matt', 'konica', 3.5],
    ['banner_black_glossy', 'Banner Black Glossy', 'konica', 4.0],
    ['white_banner', 'White Banner', 'konica', 4.5],
    ['white_vinily', 'White Vinil', 'konica', 5.0],
    ['backlead', 'Backlead', 'konica', 1.5],
    ['flex', 'Flex', 'konica', 2.5],
    ['banner_440_white', 'Banner 440 White', 'konica', 3.0],
    ['banner_440_black', 'Banner 440 Black', 'konica', 3.2],
    
    // ROLAND materials (premium printing)
    ['banner_matt', 'Banner Matt Premium', 'roland', 3.0],
    ['banner_glossy', 'Banner Glossy Premium', 'roland', 3.5],
    ['vinily_ch', 'Vinil China Premium', 'roland', 2.5],
    ['vinily_eu', 'Vinil Europe Premium', 'roland', 4.5],
    ['black_matt', 'Black Matt Premium', 'roland', 4.0],
    ['black_glossy', 'Black Glossy Premium', 'roland', 4.5],
    ['white_banner_premium', 'White Banner Premium', 'roland', 5.5],
    
    // LASER materials (cutting/engraving)
    ['cut_wood', 'Cut On Wood', 'laser', 15.0],
    ['cut_forex', 'Cut On Forex', 'laser', 25.0],
    ['cut_orch', 'Cut On Orch', 'laser', 20.0],
    ['cut_acrylic', 'Cut Acrylic', 'laser', 30.0],
    ['graw_wood', 'Engrave On Wood', 'laser', 12.0],
    ['graw_forex', 'Engrave On Forex', 'laser', 18.0],
    ['graw_orch', 'Engrave On Orch', 'laser', 15.0],
    ['graw_acrylic', 'Engrave On Acrylic', 'laser', 22.0],
];

$materialIds = [];
foreach ($materials as $m) {
    $sql = "INSERT INTO materials (mat_key, label, category, stock_margin, status) 
            VALUES ('{$m[0]}', '{$m[1]}', '{$m[2]}', {$m[3]}, 1)";
    mysqli_query($con, $sql);
    $key = $m[0] . '_' . $m[2];
    $materialIds[$key] = ['id' => mysqli_insert_id($con), 'price' => $m[3]];
}
echo "   Created " . count($materials) . " materials\n";

// 6. Create Clients
echo "\n6. Creating clients...\n";
$clientNames = ['GRI Advertising', 'Baku Print Center', 'Nigar Design', 'Azeri Signs', 'City Lights', 
                'Metro Reklam', 'Elite Banners', 'Fast Print Baku', 'Creative Studio', 'Modern Ads',
                'Print Master', 'Baku Signs', 'Capital Ads', 'Star Printing'];
$clientIds = [];
foreach ($clientNames as $i => $name) {
    $cid = 'CLI' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
    $clientIds[] = $cid;
    $email = 'contact@' . strtolower(str_replace(' ', '', $name)) . '.com';
    $phone = '+99450' . rand(1000000, 9999999);
    $total = rand(1000, 10000);
    $paid = rand(500, $total);
    $outstanding = $total - $paid;
    
    $sql = "INSERT INTO clients (client_id, name, email, phone, total_amount, outstanding_debit, advanced, created_at) 
            VALUES ('$cid', '$name', '$email', '$phone', $total, $outstanding, 0, NOW())";
    mysqli_query($con, $sql);
}
echo "   Created " . count($clientNames) . " clients\n";

// 7. Create Price Profiles for each client-material combination
echo "\n7. Creating price profiles...\n";
$profileCount = 0;
foreach ($clientIds as $cid) {
    // Create a few price profiles per client (not all materials)
    $selectedMaterials = array_slice($materials, 0, 8); // First 8 materials
    foreach ($selectedMaterials as $m) {
        $basePrice = $m[3];
        // Random discount 0-20%
        $discount = rand(0, 20) / 100;
        $finalPrice = round($basePrice * (1 - $discount), 2);
        
        $sql = "INSERT INTO client_price_profiles (client_id, printer_key, material_key, price) 
                VALUES ('$cid', '{$m[2]}', '{$m[0]}', $finalPrice)";
        mysqli_query($con, $sql);
        $profileCount++;
    }
}
echo "   Created $profileCount price profiles\n";

// 8. Create Sample Works
echo "\n8. Creating sample works...\n";
$workTypes = ['Banner Print', 'Vinil Print', 'Laser Cut', 'Engraving', 'Poster Print'];
for ($i = 0; $i < 30; $i++) {
    $cid = $clientIds[array_rand($clientIds)];
    $work = $workTypes[array_rand($workTypes)];
    $width = rand(50, 300) / 100;
    $height = rand(30, 200) / 100;
    $qty = rand(1, 5);
    $price = rand(20, 60) / 10;
    $total = round($width * $height * $qty * $price, 2);
    $date = date('Y-m-d', strtotime('-' . rand(1, 90) . ' days'));
    $opId = 'WRK' . date('Ymd') . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
    
    $sql = "INSERT INTO work (client_id, work, size_w, size_h, piece, material, printer, date, op_id, total_amount, price_per_m2) 
            VALUES ('$cid', '$work', $width, $height, $qty, 'banner_matt', 'Konica 1024', '$date', '$opId', $total, $price)";
    mysqli_query($con, $sql);
}
echo "   Created 30 works\n";

// 9. Create Stock
echo "\n9. Creating stock entries...\n";
$stockProducts = ['Banner Matt Roll 320cm', 'Banner Glossy Roll 320cm', 'Vinil China Roll', 
                  'Vinil Europe Roll', 'White Banner Roll', 'Black Banner Roll', 'Flex Roll'];
for ($i = 0; $i < 20; $i++) {
    $product = $stockProducts[array_rand($stockProducts)] . ' #' . ($i + 1);
    $amount = rand(50, 500);
    $date = date('Y-m-d', strtotime('-' . rand(1, 60) . ' days'));
    $opId = 'STK' . date('Ymd') . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
    
    mysqli_query($con, "INSERT INTO in_stock (product, sgrm, date, op_id) VALUES ('$product', $amount, '$date', '$opId')");
}
echo "   Created 20 stock entries\n";

// 10. Create Payments
echo "\n10. Creating payments...\n";
for ($i = 0; $i < 25; $i++) {
    $cid = $clientIds[array_rand($clientIds)];
    $total = rand(200, 3000);
    $paid = rand(100, $total);
    $outstanding = $total - $paid;
    $date = date('Y-m-d', strtotime('-' . rand(1, 90) . ' days'));
    $opId = 'PAY' . date('Ymd') . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
    
    mysqli_query($con, "INSERT INTO payment (client_id, name, total_amount, paid, outstanding_debet, date, operation_id) 
                       VALUES ('$cid', 'Payment', $total, $paid, $outstanding, '$date', '$opId')");
}
echo "   Created 25 payments\n";

// 11. Create Costs
echo "\n11. Creating costs...\n";
$costTypes = ['Material Purchase', 'Printer Maintenance', 'Rent', 'Electricity', 'Office Supplies', 'Transportation'];
for ($i = 0; $i < 20; $i++) {
    $cid = $clientIds[array_rand($clientIds)];
    $cost = $costTypes[array_rand($costTypes)];
    $amount = rand(50, 1000);
    $date = date('Y-m-d', strtotime('-' . rand(1, 90) . ' days'));
    
    mysqli_query($con, "INSERT INTO cost (client_name, client_id, date, amount, note) 
                       VALUES ('$cost', '$cid', '$date', $amount, '$cost expense')");
}
echo "   Created 20 costs\n";

echo "\n===========================================\n";
echo "SETUP COMPLETE!\n";
echo "===========================================\n";
echo "\nLogin: admin@hisabat.az / admin123\n";
echo "\nPrinters: Konica 1024, Konica 512, Roland RF-640, Laser Cutter, etc.\n";
echo "Materials: Linked by printer type (banner, vinil, laser)\n";
echo "Clients: 14 clients with price profiles\n";
echo "\nNow when you select a printer, you'll see only compatible materials!\n";
