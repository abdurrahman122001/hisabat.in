<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

echo "=== SIMPLE mp FIX ===\n";

// Read file
$content = file_get_contents($jsFile);

// Create backup
copy($jsFile, $jsFile . '.mp_simple_backup.' . time());

// Add mp=Math.pow at the very beginning of the file
$content = "var mp=Math.pow;" . $content;

// Save
file_put_contents($jsFile, $content);

echo "Added mp=Math.pow at beginning of file\n";

// Test with Node.js
$tempFile = tempnam(sys_get_temp_dir(), 'js_test_');
file_put_contents($tempFile, $content);

$nodeCheck = shell_exec("node --check $tempFile 2>&1");
if (empty($nodeCheck)) {
    echo "✓ Node.js validation: PASSED\n";
} else {
    echo "✗ Node.js validation: FAILED\n";
    echo "Error: $nodeCheck\n";
}

unlink($tempFile);

echo "\nClear browser cache and test!\n";
