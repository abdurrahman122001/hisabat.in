<?php
/**
 * Seed Data Script
 * This script populates all tables with sample data for testing.
 */

require_once __DIR__ . '/config.php';

// Allow both CLI and web access for convenience
// if (php_sapi_name() !== 'cli') {
//     header('Content-Type: text/plain; charset=utf-8');
// }

if (!$con || $con->connect_errno) {
    die("Database connection failed: " . ($con ? $con->connect_error : 'Unknown error') . "\n");
}

echo "Starting data seeding...\n\n";

// 1. Seed Admin User
echo "1. Seeding admin user...\n";
$admin_email = 'admin@hisabat.az';
$admin_password = 'admin123';
mysqli_query($con, "INSERT IGNORE INTO `admin` (`email`, `password`) VALUES ('$admin_email', '$admin_password')");
echo "   - Admin: $admin_email / $admin_password\n";

// 2. Seed Regular Users
echo "\n2. Seeding users...\n";
$users = [
    ['user1@hisabat.az', password_hash('user123', PASSWORD_DEFAULT), 'user'],
    ['user2@hisabat.az', password_hash('user123', PASSWORD_DEFAULT), 'user'],
    ['manager@hisabat.az', password_hash('manager123', PASSWORD_DEFAULT), 'admin'],
];
foreach ($users as $user) {
    $email = $user[0];
    $hash = $user[1];
    $role = $user[2];
    mysqli_query($con, "INSERT IGNORE INTO `users` (`email`, `password_hash`, `role`, `status`) VALUES ('$email', '$hash', '$role', 1)");
    echo "   - User: $email ($role)\n";
}

// 3. Seed Materials
echo "\n3. Seeding materials...\n";
$materials = [
    ['banner_matt', 'Banner Matt (China)', 'banner', 0.5],
    ['banner_glossy', 'Banner Glossy (China)', 'banner', 0.6],
    ['vinily_ch', 'Vinil China', 'vinil', 0.4],
    ['vinily_eu', 'Vinil Europe', 'vinil', 0.8],
    ['banner_black_mate', 'Banner Black Matt', 'banner', 0.7],
    ['banner_black_glossy', 'Banner Black Glossy', 'banner', 0.8],
    ['white_banner', 'White Banner', 'banner', 0.9],
    ['white_vinily', 'White Vinil', 'vinil', 1.0],
    ['backlead', 'Backlead', 'material', 0.3],
    ['flex', 'Flex', 'material', 0.5],
    ['banner_440_white', 'Banner 440 White', 'banner', 0.6],
    ['banner_440_black', 'Banner 440 Black', 'banner', 0.65],
    ['roland_banner_matt', 'Roland Banner Matt', 'banner', 0.7],
    ['roland_banner_glossy', 'Roland Banner Glossy', 'banner', 0.75],
    ['roland_vinily_ch', 'Roland Vinil China', 'vinil', 0.45],
    ['roland_vinily_eu', 'Roland Vinil Europe', 'vinil', 0.85],
    ['roland_black_matt', 'Roland Black Matt', 'banner', 0.72],
    ['roland_black_glossy', 'Roland Black Glossy', 'banner', 0.78],
    ['cut_wood', 'Cut Wood', 'cutting', 2.0],
    ['cut_forex', 'Cut Forex', 'cutting', 3.0],
    ['cut_orch', 'Cut Orch', 'cutting', 2.5],
    ['graw_wood', 'Grawer Wood', 'grawer', 4.0],
    ['graw_forex', 'Grawer Forex', 'grawer', 5.0],
    ['graw_orch', 'Grawer Orch', 'grawer', 4.5],
];
foreach ($materials as $mat) {
    $key = mysqli_real_escape_string($con, $mat[0]);
    $label = mysqli_real_escape_string($con, $mat[1]);
    $cat = mysqli_real_escape_string($con, $mat[2]);
    $margin = $mat[3];
    mysqli_query($con, "INSERT IGNORE INTO `materials` (`mat_key`, `label`, `category`, `stock_margin`) VALUES ('$key', '$label', '$cat', $margin)");
}
echo "   - Seeded " . count($materials) . " materials\n";

// 4. Seed Printers
echo "\n4. Seeding printers...\n";
$printers = [
    ['Konica 1024', 'konica_1024'],
    ['Konica 512', 'konica_512'],
    ['Roland RF-640', 'roland_rf640'],
    ['Roland XF-640', 'roland_xf640'],
    ['Laser Cutter 1', 'laser_cut_1'],
    ['Laser Cutter 2', 'laser_cut_2'],
    ['Grawer Machine', 'grawer_machine'],
    ['Manual Work Station', 'manual_work'],
];
foreach ($printers as $printer) {
    $name = mysqli_real_escape_string($con, $printer[0]);
    $key = mysqli_real_escape_string($con, $printer[1]);
    mysqli_query($con, "INSERT IGNORE INTO `printers` (`name`, `price_key`, `status`) VALUES ('$name', '$key', 1)");
}
echo "   - Seeded " . count($printers) . " printers\n";

// 5. Seed Clients
echo "\n5. Seeding clients...\n";
$client_names = ['GRI Advertising', 'Baku Print Center', 'Nigar Design', 'Azeri Signs', 'City Lights', 'Metro Reklam', 'Elite Banners', 'Fast Print Baku', 'Creative Studio', 'Modern Ads'];
$client_ids = [];
for ($i = 0; $i < count($client_names); $i++) {
    $client_id = 'CLI' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
    $client_ids[] = $client_id;
    $name = mysqli_real_escape_string($con, $client_names[$i]);
    $email = 'contact@' . strtolower(str_replace(' ', '', $client_names[$i])) . '.com';
    $phone = '+99450' . rand(1000000, 9999999);
    $date = date('Y-m-d', strtotime('-' . rand(1, 365) . ' days'));
    
    mysqli_query($con, "INSERT IGNORE INTO `clients` (
        `client_id`, `name`, `email`, `phone`, `date`,
        `banner_matt`, `banner_glossy`, `vinily_ch`, `vinily_eu`,
        `advanced`, `outstanding_debit`, `total_amount`, `created_at`
    ) VALUES (
        '$client_id', '$name', '$email', '$phone', '$date',
        2.5, 3.0, 2.0, 4.0,
        " . rand(100, 1000) . ", " . rand(50, 500) . ", " . rand(1000, 5000) . ", NOW()
    )");
}
echo "   - Seeded " . count($client_names) . " clients\n";

// 6. Seed Client Price Profiles
echo "\n6. Seeding client price profiles...\n";
$price_profiles = [
    ['CLI0001', 'konica_1024', 'banner_matt', 2.3],
    ['CLI0001', 'konica_1024', 'banner_glossy', 2.8],
    ['CLI0001', 'roland_rf640', 'banner_matt', 2.5],
    ['CLI0002', 'konica_512', 'banner_matt', 2.2],
    ['CLI0002', 'konica_512', 'vinily_ch', 1.9],
    ['CLI0003', 'roland_rf640', 'vinily_eu', 3.8],
    ['CLI0003', 'laser_cut_1', 'cut_wood', 1.9],
    ['CLI0004', 'grawer_machine', 'graw_wood', 3.8],
    ['CLI0005', 'konica_1024', 'banner_black_matt', 6.5],
];
foreach ($price_profiles as $profile) {
    $cid = $profile[0];
    $printer = mysqli_real_escape_string($con, $profile[1]);
    $material = mysqli_real_escape_string($con, $profile[2]);
    $price = $profile[3];
    mysqli_query($con, "INSERT IGNORE INTO `client_price_profiles` (`client_id`, `printer_key`, `material_key`, `price`) VALUES ('$cid', '$printer', '$material', $price)");
}
echo "   - Seeded " . count($price_profiles) . " price profiles\n";

// 7. Seed Works
echo "\n7. Seeding works...\n";
$work_types = ['Banner Print', 'Vinil Cutting', 'Laser Cut Sign', 'Grawer Plate', 'Poster Print', 'Sticker Print', 'Business Card', 'Flyer Print'];
$materials_work = ['banner_matt', 'banner_glossy', 'vinily_ch', 'vinily_eu', 'cut_wood', 'graw_forex', 'white_banner', 'banner_black_matt'];
$printers_work = ['Konica 1024', 'Konica 512', 'Roland RF-640', 'Laser Cutter 1', 'Grawer Machine', 'Konica 1024', 'Roland XF-640', 'Konica 1024'];

for ($i = 0; $i < 25; $i++) {
    $client_id = $client_ids[array_rand($client_ids)];
    $work = $work_types[array_rand($work_types)];
    $material = $materials_work[array_rand($materials_work)];
    $printer = $printers_work[array_rand($printers_work)];
    $size_h = rand(50, 500) / 100;
    $size_w = rand(50, 300) / 100;
    $piece = rand(1, 10);
    $price_per_m2 = rand(15, 60) / 10;
    $total = round($size_h * $size_w * $piece * $price_per_m2, 2);
    $date = date('Y-m-d', strtotime('-' . rand(1, 90) . ' days'));
    $op_id = 'OP' . date('Ymd') . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
    
    mysqli_query($con, "INSERT IGNORE INTO `work` (
        `client_id`, `work`, `size_h`, `size_w`, `piece`, `material`, `printer`, `date`, `op_id`, `total_amount`, `price_per_m2`
    ) VALUES (
        '$client_id', '$work', $size_h, $size_w, $piece, '$material', '$printer', '$date', '$op_id', $total, $price_per_m2
    )");
}
echo "   - Seeded 25 works\n";

// 8. Seed In Stock
echo "\n8. Seeding in_stock...\n";
$stock_products = ['Banner Matt Roll', 'Banner Glossy Roll', 'Vinil China Roll', 'Vinil Europe Roll', 'White Banner Roll', 'Black Banner Roll', 'Flex Material', 'Backlead Material'];
for ($i = 0; $i < 15; $i++) {
    $product = $stock_products[array_rand($stock_products)] . ' ' . ($i + 1);
    $sgrm = rand(50, 500);
    $date = date('Y-m-d', strtotime('-' . rand(1, 60) . ' days'));
    $op_id = 'STK' . date('Ymd') . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
    
    mysqli_query($con, "INSERT IGNORE INTO `in_stock` (`product`, `sgrm`, `date`, `op_id`) VALUES ('$product', $sgrm, '$date', '$op_id')");
}
echo "   - Seeded 15 stock entries\n";

// 9. Seed Payments
echo "\n9. Seeding payments...\n";
for ($i = 0; $i < 20; $i++) {
    $client_id = $client_ids[array_rand($client_ids)];
    $client_name = mysqli_real_escape_string($con, $client_names[array_search($client_id, $client_ids)]);
    $total_amount = rand(100, 2000);
    $paid = rand(50, $total_amount);
    $outstanding = $total_amount - $paid;
    $advanced = rand(0, $paid / 2);
    $date = date('Y-m-d', strtotime('-' . rand(1, 90) . ' days'));
    $op_id = 'PAY' . date('Ymd') . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
    
    mysqli_query($con, "INSERT IGNORE INTO `payment` (
        `client_id`, `name`, `total_amount`, `paid`, `outstanding_debet`, `advanced`, `date`, `operation_id`
    ) VALUES (
        '$client_id', '$client_name', $total_amount, $paid, $outstanding, $advanced, '$date', '$op_id'
    )");
}
echo "   - Seeded 20 payments\n";

// 10. Seed Costs
echo "\n10. Seeding costs...\n";
$cost_notes = ['Material purchase', 'Printer maintenance', 'Rent payment', 'Electricity bill', 'Office supplies', 'Transportation', 'Advertising', 'Employee salary'];
for ($i = 0; $i < 15; $i++) {
    $client_id = $client_ids[array_rand($client_ids)];
    $client_name = mysqli_real_escape_string($con, $client_names[array_search($client_id, $client_ids)]);
    $amount = rand(50, 500);
    $note = mysqli_real_escape_string($con, $cost_notes[array_rand($cost_notes)]);
    $date = date('Y-m-d', strtotime('-' . rand(1, 90) . ' days'));
    
    mysqli_query($con, "INSERT IGNORE INTO `cost` (`client_name`, `client_id`, `date`, `amount`, `note`) VALUES ('$client_name', '$client_id', '$date', $amount, '$note')");
}
echo "   - Seeded 15 costs\n";

echo "\n===========================================\n";
echo "Data seeding completed successfully!\n";
echo "===========================================\n";
echo "\nLogin credentials:\n";
echo "  Admin: admin@hisabat.az / admin123\n";
echo "  Users: user1@hisabat.az / user123\n";
echo "         manager@hisabat.az / manager123\n";
echo "\nYou can now view the data in the application.\n";
