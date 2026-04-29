<?php
require 'config.php';

echo "Adding stock to warehouse...\n\n";

// Add stock for common banner materials
$stockItems = [
    ['Banner Matt Roll 320cm', 500],
    ['Banner Glossy Roll 320cm', 500],
    ['Banner Black Matt Roll', 300],
    ['Banner Black Glossy Roll', 300],
    ['Vinil China Roll', 400],
    ['Vinil Europe Roll', 400],
    ['White Banner Roll', 250],
    ['White Vinil Roll', 250],
    ['Flex Roll', 200],
    ['Backlead Roll', 150],
];

foreach ($stockItems as $item) {
    $product = $item[0];
    $amount = $item[1];
    $date = date('Y-m-d');
    $opId = 'STK' . date('Ymd') . rand(1000, 9999);
    
    // Check if product exists
    $res = mysqli_query($con, "SELECT id, sgrm FROM in_stock WHERE product = '$product' LIMIT 1");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        // Update existing
        $newAmount = $row['sgrm'] + $amount;
        mysqli_query($con, "UPDATE in_stock SET sgrm = $newAmount WHERE id = {$row['id']}");
        echo "Updated: $product - now $newAmount m²\n";
    } else {
        // Insert new
        mysqli_query($con, "INSERT INTO in_stock (product, sgrm, date, op_id) VALUES ('$product', $amount, '$date', '$opId')");
        echo "Added: $product - $amount m²\n";
    }
}

echo "\nDone! Stock added successfully.\n";
echo "Refresh the page and try adding the work again.\n";
