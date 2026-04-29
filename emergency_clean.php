<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

echo "=== EMERGENCY CLEAN ===\n";

// Read file
$content = file_get_contents($jsFile);

// Create backup
copy($jsFile, $jsFile . '.emergency_backup.' . time());

// Remove ALL non-ASCII characters completely
$clean = '';
for ($i = 0; $i < strlen($content); $i++) {
    $byte = ord($content[$i]);
    
    // Only keep ASCII printable, tab, LF, CR
    if ($byte === 9 || $byte === 10 || $byte === 13 || ($byte >= 32 && $byte <= 126)) {
        $clean .= chr($byte);
    }
}

echo "Original size: " . strlen($content) . " bytes\n";
echo "Clean size: " . strlen($clean) . " bytes\n";
echo "Removed: " . (strlen($content) - strlen($clean)) . " bytes\n";

// Save clean version
file_put_contents($jsFile, $clean);

// Verify position 237 is now clean
if (strlen($clean) > 237) {
    $byte237 = ord($clean[237]);
    echo "New byte at 237: " . sprintf('%02X', $byte237) . " (decimal: $byte237)\n";
    if ($byte237 >= 32 && $byte237 <= 126) {
        echo "✓ Position 237 is now clean ASCII\n";
    } else {
        echo "✗ Position 237 is still problematic\n";
    }
}

// Test with Node.js
$tempFile = tempnam(sys_get_temp_dir(), 'js_test_');
file_put_contents($tempFile, $clean);

$nodeCheck = shell_exec("node --check $tempFile 2>&1");
if (empty($nodeCheck)) {
    echo "✓ Node.js validation: PASSED\n";
} else {
    echo "✗ Node.js validation: FAILED\n";
    echo "Error: $nodeCheck\n";
}

unlink($tempFile);

echo "\nFile is now pure ASCII.\n";
echo "1. Clear browser cache (Ctrl+F5)\n";
echo "2. Test the site\n";
echo "3. If it works, we can add back Azerbaijani characters properly\n";
