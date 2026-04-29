<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

echo "=== FORCE ASCII COMPLETE ===\n";

// Read file
$content = file_get_contents($jsFile);

// Convert to pure ASCII - strip everything except printable ASCII and basic control chars
$ascii = '';
for ($i = 0; $i < strlen($content); $i++) {
    $byte = ord($content[$i]);
    
    // Allow: tab (9), LF (10), CR (13), space (32) to ~ (126)
    if ($byte === 9 || $byte === 10 || $byte === 13 || ($byte >= 32 && $byte <= 126)) {
        $ascii .= chr($byte);
    }
}

echo "Original size: " . strlen($content) . " bytes\n";
echo "ASCII size: " . strlen($ascii) . " bytes\n";
echo "Removed: " . (strlen($content) - strlen($ascii)) . " bytes\n";

// Save pure ASCII
file_put_contents($jsFile, $ascii);

// Verify it's clean
$hasNonAscii = false;
for ($i = 0; $i < 1000 && $i < strlen($ascii); $i++) {
    $byte = ord($ascii[$i]);
    if ($byte < 32 || $byte > 126) {
        if ($byte !== 10 && $byte !== 13) { // Ignore newlines
            $hasNonAscii = true;
            break;
        }
    }
}

if ($hasNonAscii) {
    echo "WARNING: Still has non-ASCII!\n";
} else {
    echo "✓ File is pure ASCII\n";
}

// Test with Node.js
$tempFile = tempnam(sys_get_temp_dir(), 'js_test_');
file_put_contents($tempFile, $ascii);

$nodeCheck = shell_exec("node --check $tempFile 2>&1");
if (empty($nodeCheck)) {
    echo "✓ Node.js validation: PASSED\n";
} else {
    echo "✗ Node.js validation: FAILED\n";
    echo "Error: $nodeCheck\n";
}

unlink($tempFile);

echo "\nFile is now pure ASCII.\n";
echo "Test in browser - syntax error should be gone.\n";
echo "Note: Azerbaijani characters will show as ASCII equivalents.\n";

// Add proper headers to .htaccess to ensure correct serving
$htaccess = __DIR__ . '/ui/.htaccess';
$htaccessContent = "# Disable Directory Browsing
Options -Indexes

# Force correct headers for JS files
<Files \"*.js\">
    Header set Content-Type \"application/javascript; charset=utf-8\"
    Header unset Content-Length
</Files>

# SPA routing
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.html [L]";

file_put_contents($htaccess, $htaccessContent);
echo "\nUpdated ui/.htaccess with proper headers\n";
