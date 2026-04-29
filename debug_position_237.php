<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

echo "=== DEBUGGING POSITION 237 ===\n\n";

// Read file
$content = file_get_contents($jsFile);

// Show bytes around position 237
$start = max(0, 237 - 20);
$end = min(strlen($content), 237 + 20);
$segment = substr($content, $start, $end - $start);

echo "Bytes around position 237:\n";
echo "Position: " . $start . " to " . ($end - 1) . "\n";

for ($i = 0; $i < strlen($segment); $i++) {
    $actualPos = $start + $i;
    $byte = ord($segment[$i]);
    $char = ($byte >= 32 && $byte <= 126) ? chr($byte) : '[' . sprintf('%02X', $byte) . ']';
    
    if ($actualPos == 237) {
        echo ">>> POS $actualPos: $byte ($char) <<<\n";
    } else {
        echo "    POS $actualPos: $byte ($char)\n";
    }
}

// Also check line endings around that area
echo "\nContext (ASCII):\n";
$context = substr($content, max(0, 237 - 50), 100);
$ascii = '';
for ($i = 0; $i < strlen($context); $i++) {
    $byte = ord($context[$i]);
    if ($byte >= 32 && $byte <= 126) {
        $ascii .= chr($byte);
    } else {
        $ascii .= '[' . sprintf('%02X', $byte) . ']';
    }
}
echo $ascii . "\n";

// Check for any non-ASCII in first 1000 bytes
echo "\nNon-ASCII bytes in first 1000:\n";
$first1000 = substr($content, 0, 1000);
$nonAscii = [];
for ($i = 0; $i < strlen($first1000); $i++) {
    $byte = ord($first1000[$i]);
    if ($byte < 32 || $byte > 126) {
        $nonAscii[] = "Position $i: " . sprintf('%02X', $byte);
    }
}

if (empty($nonAscii)) {
    echo "None found - first 1000 bytes are clean ASCII\n";
} else {
    foreach (array_slice($nonAscii, 0, 10) as $item) {
        echo "  $item\n";
    }
    if (count($nonAscii) > 10) {
        echo "  ... and " . (count($nonAscii) - 10) . " more\n";
    }
}

echo "\n=== END DEBUG ===\n";
