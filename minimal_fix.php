<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

echo "=== MINIMAL FIX ===\n";

// Read file
$content = file_get_contents($jsFile);

// Remove ALL Azerbaijani characters that are causing issues
// Replace with simple ASCII equivalents for now
$replacements = [
    'İ' => 'I',
    'ı' => 'i',
    'ə' => 'e',
    'ə' => 'e',
    'ö' => 'o',
    'ö' => 'o',
    'ü' => 'u',
    'ü' => 'u',
    'ç' => 'c',
    'ç' => 'c',
    'ş' => 's',
    'ş' => 's',
    'ğ' => 'g',
    'ğ' => 'g',
    'Ä' => 'A',
    'ä' => 'a',
    'Ö' => 'O',
    'ö' => 'o',
    'Ü' => 'U',
    'ü' => 'u',
    'ß' => 'ss',
    '©' => '(c)',
    '²' => '2',
];

// Apply replacements
$fixed = 0;
foreach ($replacements as $from => $to) {
    $count = substr_count($content, $from);
    if ($count > 0) {
        $content = str_replace($from, $to, $content);
        $fixed += $count;
        echo "Replaced: $from -> $to ($count)\n";
    }
}

// Also remove any remaining non-ASCII bytes
$clean = '';
for ($i = 0; $i < strlen($content); $i++) {
    $byte = ord($content[$i]);
    if ($byte >= 32 && $byte <= 126) {
        $clean .= $content[$i];
    } elseif ($byte === 10 || $byte === 13) {
        $clean .= $content[$i]; // Keep line endings
    }
}

echo "Removed " . (strlen($content) - strlen($clean)) . " non-ASCII bytes\n";

// Save clean version
file_put_contents($jsFile, $clean);

echo "File saved with ASCII-only characters\n";
echo "Size: " . strlen($clean) . " bytes\n";
echo "Total fixes: $fixed\n";

// Test with Node.js
$tempFile = tempnam(sys_get_temp_dir(), 'js_test_');
file_put_contents($tempFile, $clean);

$nodeCheck = shell_exec("node --check $tempFile 2>&1");
if (empty($nodeCheck)) {
    echo "\n✓ Node.js validation: PASSED\n";
} else {
    echo "\n✗ Node.js validation: FAILED\n";
    echo "Error: $nodeCheck\n";
}

unlink($tempFile);

echo "\nClear browser cache and test.\n";
