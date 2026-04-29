<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

echo "=== JS FILE DIAGNOSTIC ===\n\n";

// Check file exists and size
if (!file_exists($jsFile)) {
    die("File not found: $jsFile\n");
}

$size = filesize($jsFile);
echo "File size: $size bytes\n";

// Read first 500 bytes
$handle = fopen($jsFile, 'rb');
$first500 = fread($handle, 500);
fclose($handle);

echo "\nFirst 500 bytes (hex):\n";
echo bin2hex($first500) . "\n\n";

echo "First 500 bytes (ASCII representation):\n";
$ascii = '';
for ($i = 0; $i < strlen($first500); $i++) {
    $byte = ord($first500[$i]);
    if ($byte >= 32 && $byte <= 126) {
        $ascii .= chr($byte);
    } else {
        $ascii .= '[' . sprintf('%02X', $byte) . ']';
    }
}
echo $ascii . "\n\n";

// Check for BOM
if (substr($first500, 0, 3) === "\xEF\xBB\xBF") {
    echo "FOUND UTF-8 BOM at start\n";
} else {
    echo "No UTF-8 BOM found\n";
}

// Check for replacement character at position 237 (0-indexed)
if (strlen($first500) > 237) {
    $byte237 = ord($first500[237]);
    echo "Byte at position 237: " . sprintf('%02X', $byte237) . " (decimal: $byte237)\n";
    
    // Check if it's part of UTF-8 replacement char
    if ($byte237 === 0xEF && strlen($first500) > 239) {
        $byte238 = ord($first500[238]);
        $byte239 = ord($first500[239]);
        if ($byte238 === 0xBF && $byte239 === 0xBD) {
            echo "FOUND UTF-8 REPLACEMENT CHAR at position 237!\n";
        }
    }
}

// Count all problematic bytes
$badPatterns = [
    "\xEF\xBF\xBD" => 'UTF-8 replacement char',
    "\xFF\xFD" => 'Invalid UTF-8',
    "\x00" => 'Null byte',
];

foreach ($badPatterns as $pattern => $desc) {
    $count = substr_count($first500, $pattern);
    if ($count > 0) {
        echo "Found $count x $desc\n";
    }
}

echo "\n=== END DIAGNOSTIC ===\n";
