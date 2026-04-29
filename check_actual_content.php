<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

echo "=== CHECKING ACTUAL CONTENT ===\n\n";

$content = file_get_contents($jsFile);

// Check position 237 again
$byte237 = ord($content[237]);
echo "Byte at 237: " . sprintf('%02X', $byte237) . " (decimal: $byte237)\n";

// Check if it's part of a UTF-8 sequence
if ($byte237 >= 0xC0 && $byte237 <= 0xFD) {
    echo "This is start of UTF-8 multi-byte sequence\n";
    // Show next 2 bytes
    if (strlen($content) > 239) {
        $byte238 = ord($content[238]);
        $byte239 = ord($content[239]);
        echo "Next bytes: " . sprintf('%02X %02X', $byte238, $byte239) . "\n";
        
        // Try to decode
        $utf8Bytes = substr($content, 237, 3);
        $decoded = @mb_convert_encoding($utf8Bytes, 'UTF-8', 'UTF-8');
        if ($decoded !== false) {
            echo "Decoded as: '$decoded'\n";
        }
    }
}

// Look for any Azerbaijani characters
$azeriChars = ['ı', 'İ', 'ə', 'Ə', 'ö', 'Ö', 'ü', 'Ü', 'ç', 'Ç', 'ş', 'Ş', 'ğ', 'Ğ'];
echo "\nSearching for Azerbaijani characters:\n";
foreach ($azeriChars as $char) {
    $count = substr_count($content, $char);
    if ($count > 0) {
        echo "Found '$char': $count times\n";
    }
}

// Look for replacement characters
$replacementChar = "\xEF\xBF\xBD";
$repCount = substr_count($content, $replacementChar);
echo "\nUTF-8 replacement chars: $repCount\n";

// Look for literal ? that might be corrupted
$questionCount = substr_count($content, '?');
echo "Literal ? marks: $questionCount\n";

// Show context around position 237
echo "\nContext around position 237:\n";
$start = max(0, 237 - 50);
$context = substr($content, $start, 100);
echo "Position " . $start . " to " . ($start + 99) . ":\n";

// Show as hex and ASCII
for ($i = 0; $i < strlen($context); $i++) {
    $actualPos = $start + $i;
    $byte = ord($context[$i]);
    $hex = sprintf('%02X', $byte);
    $char = ($byte >= 32 && $byte <= 126) ? $context[$i] : '.';
    
    if ($actualPos == 237) {
        echo ">>> $actualPos: $hex '$char' <<<\n";
    } else {
        echo "    $actualPos: $hex '$char'\n";
    }
}

// Check if file is being served correctly
echo "\n=== RECOMMENDATION ===\n";
echo "The file might still have encoding issues.\n";
echo "Run: php force_clean.php to make it pure ASCII\n";
echo "Then clear browser cache and test.\n";
