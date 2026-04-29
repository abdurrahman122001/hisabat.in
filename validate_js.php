<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

echo "=== JS VALIDATION ===\n\n";

$content = file_get_contents($jsFile);

// Extract the first line where error occurs (around position 237)
$lines = explode("\n", $content);
$charCount = 0;
$errorLine = -1;
$errorCol = -1;

for ($i = 0; $i < count($lines); $i++) {
    $lineLength = strlen($lines[$i]) + 1; // +1 for newline
    if ($charCount + $lineLength > 237) {
        $errorLine = $i;
        $errorCol = 237 - $charCount;
        break;
    }
    $charCount += $lineLength;
}

echo "Error around line " . ($errorLine + 1) . ", column " . ($errorCol + 1) . "\n\n";

// Show the problematic line and surrounding lines
$start = max(0, $errorLine - 2);
$end = min(count($lines), $errorLine + 3);

for ($i = $start; $i < $end; $i++) {
    $prefix = ($i == $errorLine) ? ">>> " : "    ";
    echo $prefix . "Line " . ($i + 1) . ": " . $lines[$i] . "\n";
}

// Check for common minification issues
echo "\nChecking for minification issues:\n";

// Look for incomplete strings around that area
$contextStart = max(0, 237 - 100);
$contextEnd = min(strlen($content), 237 + 100);
$context = substr($content, $contextStart, $contextEnd - $contextStart);

// Count quotes in context
$sq = substr_count($context, "'");
$dq = substr_count($context, '"');
$bt = substr_count($context, '`');

echo "Quotes in context: Single=$sq, Double=$dq, Backtick=$bt\n";

// Look for any escaped characters that might be malformed
echo "\nChecking for malformed escapes:\n";
if (preg_match('/\\\\[^nrtbfv"\'`\\\\x0-7]/', $context, $matches)) {
    echo "Found bad escape: " . $matches[0] . "\n";
} else {
    echo "No bad escapes found\n";
}

// Check if there's a string that's not properly closed
echo "\nChecking for unclosed strings:\n";
$linesAround = array_slice($lines, max(0, $errorLine - 5), 10);
foreach ($linesAround as $idx => $line) {
    $sqCount = substr_count($line, "'");
    $dqCount = substr_count($line, '"');
    $btCount = substr_count($line, '`');
    
    if ($sqCount % 2 != 0 || $dqCount % 2 != 0 || $btCount % 2 != 0) {
        echo "Line " . ($idx + max(0, $errorLine - 5) + 1) . " has odd quote count: sq=$sqCount, dq=$dqCount, bt=$btCount\n";
        echo "  Content: " . substr($line, 0, 100) . "...\n";
    }
}

// Try to validate with Node.js if available
echo "\nTrying Node.js validation:\n";
$tempFile = tempnam(sys_get_temp_dir(), 'js_test_');
file_put_contents($tempFile, $content);

$nodeCheck = shell_exec("node --check $tempFile 2>&1");
if (empty($nodeCheck)) {
    echo "Node.js: Syntax OK\n";
} else {
    echo "Node.js error: $nodeCheck\n";
}

unlink($tempFile);

echo "\n=== END VALIDATION ===\n";
