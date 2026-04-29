<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

echo "=== FIXING m² CHARACTERS ===\n";

// Read file
$content = file_get_contents($jsFile);

// Create backup
copy($jsFile, $jsFile . '.m2_backup.' . time());

// Remove all m² characters (they're causing syntax errors)
$patterns = [
    'm²' => 'm2',
    'm²' => 'm2',
    'm²' => 'm2',
    'n m²' => 'n m2',
    'n m²' => 'n m2',
    'n m²' => 'n m2',
    'm²²' => 'm2',
    'm²²' => 'm2',
    'm²²' => 'm2',
];

$fixed = 0;
foreach ($patterns as $from => $to) {
    $count = substr_count($content, $from);
    if ($count > 0) {
        $content = str_replace($from, $to, $content);
        $fixed += $count;
        echo "Fixed: $from => $to ($count)\n";
    }
}

// Also remove any remaining non-ASCII characters
$clean = '';
for ($i = 0; $i < strlen($content); $i++) {
    $byte = ord($content[$i]);
    // Keep ASCII, tab, LF, CR
    if ($byte === 9 || $byte === 10 || $byte === 13 || ($byte >= 32 && $byte <= 126)) {
        $clean .= $content[$i];
    }
}

$removed = strlen($content) - strlen($clean);
echo "Removed $removed non-ASCII bytes\n";

// Save clean version
file_put_contents($jsFile, $clean);

echo "File saved as pure ASCII\n";

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

echo "\nClear browser cache and test!\n";
