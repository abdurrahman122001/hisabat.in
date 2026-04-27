<?php
@include __DIR__ . '/config.php';
if (!isset($con) || !($con instanceof mysqli) || $con->connect_errno) {
    fwrite(STDERR, "db connection failed\n");
    exit(1);
}
$res = mysqli_query($con, "SELECT id, product, sgrm, date, op_id FROM in_stock ORDER BY id DESC LIMIT 50");
if (!$res) {
    fwrite(STDERR, mysqli_error($con) . "\n");
    exit(1);
}
while ($row = mysqli_fetch_assoc($res)) {
    echo json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
}
