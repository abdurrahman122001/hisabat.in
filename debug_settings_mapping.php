<?php
include __DIR__ . '/config.php';

$p = mysqli_query($con, 'SELECT id,name,price_key,status FROM printers ORDER BY id');
while ($r = mysqli_fetch_assoc($p)) {
    echo 'PRINTER|' . $r['id'] . '|' . $r['name'] . '|' . $r['price_key'] . '|' . $r['status'] . PHP_EOL;
}

$m = mysqli_query($con, 'SELECT id,mat_key,label,category,status FROM materials ORDER BY category,id');
while ($r = mysqli_fetch_assoc($m)) {
    echo 'MATERIAL|' . $r['id'] . '|' . $r['mat_key'] . '|' . $r['label'] . '|' . $r['category'] . '|' . $r['status'] . PHP_EOL;
}
