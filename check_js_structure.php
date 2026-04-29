<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

echo "=== JS STRUCTURE CHECK ===\n\n";

$content = file_get_contents($jsFile);

// Check if it's a valid JS module
echo "File starts with: " . substr($content, 0, 50) . "...\n";
echo "File ends with: ..." . substr($content, -50) . "\n\n";

// Check for balanced parentheses, brackets, braces
$checks = [
    '(' => ')',
    '[' => ']',
    '{' => '}'
];

foreach ($checks as $open => $close) {
    $openCount = substr_count($content, $open);
    $closeCount = substr_count($content, $close);
    echo "$open$open: $openCount, $close: $closeCount";
    if ($openCount != $closeCount) {
        echo " - MISMATCH!";
    }
    echo "\n";
}

// Check for common JS syntax issues
echo "\nChecking for syntax issues:\n";

// Check for incomplete strings
$singleQuotes = substr_count($content, "'");
$doubleQuotes = substr_count($content, '"');
echo "Single quotes: $singleQuotes (should be even)\n";
echo "Double quotes: $doubleQuotes (should be even)\n";

// Check for backticks (template literals)
$backticks = substr_count($content, '`');
echo "Backticks: $backticks (should be even)\n";

// Look for any null bytes or control chars
$nullBytes = substr_count($content, "\0");
if ($nullBytes > 0) {
    echo "WARNING: $nullBytes null bytes found\n";
}

// Check line endings
$crlf = substr_count($content, "\r\n");
$lf = substr_count($content, "\n") - $crlf;
$cr = substr_count($content, "\r") - $crlf;
echo "\nLine endings: CRLF=$crlf, LF=$lf, CR=$cr\n";

// Check if it's minified properly (no excessive whitespace)
$whitespace = substr_count($content, "  ") + substr_count($content, "\t") + substr_count($content, "\n\n");
echo "Whitespace issues: $whitespace\n";

// Try to validate as JS (basic check)
echo "\nBasic JS validation:\n";
if (strpos($content, 'function') !== false || strpos($content, 'var ') !== false || strpos($content, 'let ') !== false || strpos($content, 'const ') !== false) {
    echo "✓ Contains JS keywords\n";
} else {
    echo "✗ Missing JS keywords\n";
}

if (strpos($content, 'export') !== false || strpos($content, 'import') !== false) {
    echo "✓ ES6 module detected\n";
}

// Check for any obvious corruption at the very start
echo "\nFirst 100 chars (detailed):\n";
$first100 = substr($content, 0, 100);
for ($i = 0; $i < strlen($first100); $i++) {
    $byte = ord($first100[$i]);
    $char = ($byte >= 32 && $byte <= 126) ? $first100[$i] : '[' . sprintf('%02X', $byte) . ']';
    echo sprintf("%3d: %s\n", $i, $char);
}

echo "\n=== END CHECK ===\n";
