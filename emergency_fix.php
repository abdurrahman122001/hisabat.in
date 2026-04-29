<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

// Read raw bytes
$raw = file_get_contents($jsFile);

// Strip any BOM at start
if (substr($raw, 0, 3) === "\xEF\xBB\xBF") {
    $raw = substr($raw, 3);
}

// Check what's at position ~237
$badByte = "\xEF\xBF\xBD"; // UTF-8 replacement char

// Replace ALL occurrences of replacement character
$clean = str_replace($badByte, '', $raw);

// Save clean file
file_put_contents($jsFile, $clean);

// Report
$removed = strlen($raw) - strlen($clean);
echo "Removed $removed replacement character bytes\n";
echo "File saved clean.\n";
