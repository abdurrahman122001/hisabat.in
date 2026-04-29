<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

echo "=== RESTORING mp VARIABLE ===\n";

// Read file
$content = file_get_contents($jsFile);

// Create backup
copy($jsFile, $jsFile . '.mp_backup.' . time());

// Add missing mp variable at the beginning of relevant scope
// Look for where mp should be defined (usually in React component context)
$patterns = [
    // Add mp definition before it's used
    '/(var mp\s*=\s*[^;]+;)/' => 'var mp=Math.pow;', // If exists but incomplete
    '/(,mp=[^,}]+)/' => ',mp=Math.pow', // In object destructuring
];

$found = false;
foreach ($patterns as $pattern => $replacement) {
    if (preg_match($pattern, $content)) {
        $content = preg_replace($pattern, $replacement, $content);
        $found = true;
        echo "Fixed existing mp definition\n";
        break;
    }
}

// If no mp found, add it at the beginning of the file after any existing vars
if (!$found) {
    // Look for the first var declaration and add mp there
    if (preg_match('/(var\s+[a-zA-Z_$][a-zA-Z0-9_$]*\s*=\s*[^;]+;)/', $content, $matches)) {
        $content = str_replace($matches[1], $matches[1] . "\nvar mp=Math.pow;", $content);
        echo "Added mp=Math.pow after first var declaration\n";
    } else {
        // Add at the very beginning
        $content = "var mp=Math.pow;" . $content;
        echo "Added mp=Math.pow at beginning\n";
    }
}

// Also check for other missing Math functions that might have been removed
$mathFunctions = [
    'mp' => 'Math.pow',
    'ms' => 'Math.sqrt',
    'ma' => 'Math.abs',
    'mr' => 'Math.round',
    'mf' => 'Math.floor',
    'mc' => 'Math.ceil',
];

foreach ($mathFunctions as $var => $func) {
    if (substr_count($content, $var) > 0 && substr_count($content, "$var=") === 0) {
        // Add definition if used but not defined
        if (preg_match("/(var\s+[a-zA-Z_$][a-zA-Z0-9_$]*\s*=\s*[^;]+;)/", $content, $matches)) {
            $content = str_replace($matches[1], $matches[1] . "\nvar $var=$func;", $content);
            echo "Added $var=$func\n";
        }
    }
}

// Save
file_put_contents($jsFile, $content);

echo "File saved with mp variable restored\n";

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
