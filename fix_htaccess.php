<?php
$htaccessFile = __DIR__ . '/.htaccess';

echo "=== FIXING .HTACCESS ===\n";

// Read current .htaccess
$content = file_get_contents($htaccessFile);

// Fix the problematic line - remove JS from forced charset
$oldPattern = '/<FilesMatch "\.(js|css|json|html)\$">\s*\n\s*ForceType \'text\/plain; charset=UTF-8\'\s*\n<\/FilesMatch>/m';
$newContent = preg_replace($oldPattern, '', $content);

// Add proper JS handling
$newContent = preg_replace(
    '/(AddDefaultCharset UTF-8\n)/',
    "$1\n# Proper MIME types\n<FilesMatch \"\\.js\$\">\n    Header set Content-Type \"application/javascript; charset=utf-8\"\n</FilesMatch>\n<FilesMatch \"\\.(css|json|html)\$\">\n    Header set Content-Type \"text/html; charset=utf-8\"\n</FilesMatch>\n",
    $newContent
);

// Save updated .htaccess
file_put_contents($htaccessFile, $newContent);

echo "Updated .htaccess:\n";
echo "- Removed forced text/plain for JS files\n";
echo "- Added proper application/javascript MIME type\n";
echo "- Kept UTF-8 charset\n\n";

echo "New .htaccess content:\n";
echo $newContent . "\n";

echo "Clear browser cache and test!\n";
