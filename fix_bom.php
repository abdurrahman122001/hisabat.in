<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';
$content = file_get_contents($jsFile);

// Remove UTF-8 BOM if present
if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
    $content = substr($content, 3);
    echo "Removed BOM\n";
}

// Save without BOM
file_put_contents($jsFile, $content);
echo "Fixed JS file saved without BOM.\n";
