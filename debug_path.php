<?php
header('Content-Type: text/plain');
echo "PHP_SELF: " . $_SERVER['PHP_SELF'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "dirname(PHP_SELF): " . dirname($_SERVER['PHP_SELF']) . "\n";
echo "COOKIE_PATH (calculated): " . (dirname($_SERVER['PHP_SELF']) . '/') . "\n";

session_start();
echo "\n--- SESSION DATA ---\n";
print_r($_SESSION);
echo "\n--- COOKIE DATA ---\n";
print_r($_COOKIE);
?>
