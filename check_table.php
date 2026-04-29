<?php
require_once __DIR__ . '/config.php';

echo "=== CHECKING client_price_profiles TABLE ===\n\n";

// Check if table exists
$res = mysqli_query($con, "SHOW TABLES LIKE 'client_price_profiles'");
if ($res && mysqli_num_rows($res) > 0) {
    echo "✓ Table exists\n\n";
} else {
    echo "✗ Table DOES NOT exist\n\n";
}

// Check structure
$res = mysqli_query($con, "DESCRIBE client_price_profiles");
echo "2. Table structure:\n";
while ($row = mysqli_fetch_assoc($res)) {
    echo "   - {$row['Field']} ({$row['Type']})\n";
}

// Check data count
$res = mysqli_query($con, "SELECT COUNT(*) as cnt FROM client_price_profiles");
$row = mysqli_fetch_assoc($res);
echo "\n3. Records: {$row['cnt']}\n";

// Check specific client CLI0010
$cid = 'CLI0010';
echo "\n4. Prices for client $cid:\n";
$res = mysqli_query($con, "SELECT printer_key, material_key, price FROM client_price_profiles WHERE client_id='$cid'");
$found = false;
while ($row = mysqli_fetch_assoc($res)) {
    echo "   {$row['printer_key']} / {$row['material_key']} = {$row['price']}\n";
    $found = true;
}
if (!$found) {
    echo "   (no records found)\n";
}

// Check for roland prices specifically
echo "\n5. Roland prices for $cid:\n";
$res = mysqli_query($con, "SELECT printer_key, material_key, price FROM client_price_profiles WHERE client_id='$cid' AND printer_key='roland'");
$found = false;
while ($row = mysqli_fetch_assoc($res)) {
    echo "   {$row['material_key']} = {$row['price']}\n";
    $found = true;
}
if (!$found) {
    echo "   (no roland records)\n";
}
