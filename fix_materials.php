<?php
require 'config.php';

echo "Updating materials to match printer categories...\n";

// Clear and re-seed materials with correct categories
mysqli_query($con, "DELETE FROM materials");

$materials = [
    // Konica materials
    ['banner_matt', 'Banner Matt', 'konica', 0.5],
    ['banner_glossy', 'Banner Glossy', 'konica', 0.6],
    ['vinily_ch', 'Vinil China', 'konica', 0.4],
    ['vinily_eu', 'Vinil Europe', 'konica', 0.8],
    ['banner_black_mate', 'Banner Black Matt', 'konica', 0.7],
    ['banner_black_glossy', 'Banner Black Glossy', 'konica', 0.8],
    ['white_banner', 'White Banner', 'konica', 0.9],
    ['white_vinily', 'White Vinil', 'konica', 1.0],
    ['backlead', 'Backlead', 'konica', 0.3],
    ['flex', 'Flex', 'konica', 0.5],
    ['banner_440_white', 'Banner 440 White', 'konica', 0.6],
    ['banner_440_black', 'Banner 440 Black', 'konica', 0.65],
    
    // Roland materials
    ['banner_matt', 'Banner Matt', 'roland', 0.7],
    ['banner_glossy', 'Banner Glossy', 'roland', 0.75],
    ['vinily_ch', 'Vinil China', 'roland', 0.45],
    ['vinily_eu', 'Vinil Europe', 'roland', 0.85],
    ['black_matt', 'Black Matt', 'roland', 0.72],
    ['black_glossy', 'Black Glossy', 'roland', 0.78],
    
    // Laser materials
    ['cut_wood', 'Cut On Wood', 'laser', 2.0],
    ['cut_forex', 'Cut On Forex', 'laser', 3.0],
    ['cut_orch', 'Cut On Orch', 'laser', 2.5],
    ['graw_wood', 'Graw On Wood', 'laser', 4.0],
    ['graw_forex', 'Graw On Forex', 'laser', 5.0],
    ['graw_orch', 'Graw On Orch', 'laser', 4.5],
];

$count = 0;
foreach ($materials as $mat) {
    $key = mysqli_real_escape_string($con, $mat[0]);
    $label = mysqli_real_escape_string($con, $mat[1]);
    $cat = mysqli_real_escape_string($con, $mat[2]);
    $margin = $mat[3];
    
    $sql = "INSERT INTO materials (mat_key, label, category, stock_margin, status) 
            VALUES ('$key', '$label', '$cat', $margin, 1)";
    
    if (mysqli_query($con, $sql)) {
        $count++;
    } else {
        echo "Error: " . mysqli_error($con) . "\n";
    }
}

echo "Inserted $count materials\n";

// Also ensure printers are correct
mysqli_query($con, "DELETE FROM printers WHERE price_key IS NULL OR price_key = ''");

$printers = [
    ['Konica 1024', 'konica'],
    ['Konica 512', 'konica'],
    ['Roland RF-640', 'roland'],
    ['Roland XF-640', 'roland'],
    ['Laser Cutter', 'laser'],
];

foreach ($printers as $printer) {
    $name = mysqli_real_escape_string($con, $printer[0]);
    $key = mysqli_real_escape_string($con, $printer[1]);
    mysqli_query($con, "INSERT IGNORE INTO printers (name, price_key, status) VALUES ('$name', '$key', 1)");
}

echo "Printers updated\n";
echo "Done! Refresh the page to see materials.\n";
