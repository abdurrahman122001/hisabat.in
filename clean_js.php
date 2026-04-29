<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';
$backupFile = $jsFile . '.original.backup';

// First restore from original backup if exists, or create backup
if (!file_exists($backupFile) && file_exists($jsFile)) {
    copy($jsFile, $backupFile);
    echo "Created backup\n";
}

// Read file
$content = file_get_contents($jsFile);

// Remove BOM if any
$content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

// The replacement character pattern - could be appearing as ? or the actual bytes
$replacementBytes = "\xEF\xBF\xBD";

// Count and remove all replacement characters
$count = substr_count($content, $replacementBytes);
$content = str_replace($replacementBytes, '', $content);

// Also remove any literal ? that might be standalone replacements in context
// But preserve legitimate ? in URLs and ternary operators by being careful

// Save without BOM
file_put_contents($jsFile, $content);

echo "Removed $count replacement characters\n";
echo "File size: " . strlen($content) . " bytes\n";

// Check first 500 chars for debugging
$first = substr($content, 0, 500);
echo "First 500 chars (ASCII only): " . preg_replace('/[^\x20-\x7E]/', '?', $first) . "\n";
