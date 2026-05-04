<?php
include "config.php";
if ($con->connect_error) die("Connection failed: " . $con->connect_error);

echo "Database: " . $DB_NAME . "\n\n";

$tables = ["clients", "work", "payment", "users", "admin", "in_stock"];
foreach ($tables as $table) {
    $res = $con->query("SELECT COUNT(*) as c FROM `$table` ");
    if ($res) {
        $row = $res->fetch_assoc();
        echo "Table $table: " . $row['c'] . " rows\n";
    } else {
        echo "Table $table: DOES NOT EXIST or error (" . $con->error . ")\n";
    }
}
?>
