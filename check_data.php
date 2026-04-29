<?php
require 'config.php';

echo "=== PRINTERS ===\n";
$res = mysqli_query($con, 'SELECT * FROM printers');
while ($r = mysqli_fetch_assoc($res)) {
    print_r($r);
}

echo "\n=== MATERIALS (first 10) ===\n";
$res2 = mysqli_query($con, 'SELECT id, mat_key, label, category FROM materials LIMIT 10');
while ($r2 = mysqli_fetch_assoc($res2)) {
    print_r($r2);
}

echo "\n=== TOTAL MATERIALS ===\n";
$cntRes = mysqli_query($con, "SELECT COUNT(*) as c FROM materials");
$cnt = mysqli_fetch_assoc($cntRes);
echo "Total: " . $cnt['c'] . "\n";
