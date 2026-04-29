<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

echo "=== HTTP HEADERS CHECK ===\n\n";

// Check .htaccess settings
$htaccess = __DIR__ . '/.htaccess';
if (file_exists($htaccess)) {
    echo "Main .htaccess content:\n";
    echo file_get_contents($htaccess) . "\n\n";
}

$uiHtaccess = __DIR__ . '/ui/.htaccess';
if (file_exists($uiHtaccess)) {
    echo "UI .htaccess content:\n";
    echo file_get_contents($uiHtaccess) . "\n\n";
}

// Check if server is adding headers
echo "Simulating HTTP headers for JS file:\n";

// Read file
$content = file_get_contents($jsFile);

// Check content type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $jsFile);
finfo_close($finfo);

echo "Detected MIME type: $mimeType\n";

// Check if there's a BOM
if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
    echo "WARNING: UTF-8 BOM detected at start\n";
} else {
    echo "No BOM detected\n";
}

// Check for any non-ASCII in first 100 bytes
$first100 = substr($content, 0, 100);
$hasNonAscii = false;
for ($i = 0; $i < strlen($first100); $i++) {
    $byte = ord($first100[$i]);
    if ($byte < 32 || $byte > 126) {
        if (!$hasNonAscii) {
            echo "Non-ASCII bytes in first 100:\n";
            $hasNonAscii = true;
        }
        echo "  Position $i: " . sprintf('%02X', $byte) . "\n";
    }
}
if (!$hasNonAscii) {
    echo "First 100 bytes are clean ASCII\n";
}

// Create a test to see what headers would be sent
echo "\nCreating test endpoint to check actual headers...\n";

$testFile = __DIR__ . '/test_js_headers.php';
$testContent = '<?php
$file = __DIR__ . "/ui/assets/index-CC2b_5k0.js";
header("Content-Type: application/javascript; charset=utf-8");
header("Content-Length: " . filesize($file));
readfile($file);
?>';

file_put_contents($testFile, $testContent);
echo "Created test endpoint: /test_js_headers.php\n";
echo "Access this in browser to see if error persists\n";

// Also check if nginx/apache might be compressing or modifying
echo "\nRecommendations:\n";
echo "1. Access /test_js_headers.php in browser\n";
echo "2. Check browser Network tab for actual headers\n";
echo "3. Look for Content-Encoding (gzip/deflate)\n";
echo "4. Check if charset is being set correctly\n";

echo "\n=== END CHECK ===\n";
