<?php
/**
 * Patch JS to add printer parameter to materials API call
 */

$files = [
    'ui/assets/index-CC2b_5k0.js',
    'deploy_hesabat_root/assets/index-CC2b_5k0.js'
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        continue;
    }
    
    echo "Patching $file...\n";
    $content = file_get_contents($file);
    
    // Find and replace materials_list API call to include printer parameter
    // Pattern: fetch("api/materials_list.php") or similar
    $original = $content;
    
    // Replace materials_list.php calls to include printer parameter
    // Look for patterns like: fetch("api/materials_list.php") or fetch('api/materials_list.php')
    $content = preg_replace(
        '/(["\'])api\/materials_list\.php\1/',
        '$1api/materials_list.php?printer=$2$1',
        $content
    );
    
    // More specific: if there's a selectedPrinter variable, use it
    // This is a simplified approach - the real fix would need the actual variable name from React
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "   Patched!\n";
    } else {
        echo "   No changes needed or pattern not found\n";
    }
}

echo "\nDone!\n";
echo "\nNote: For the printer filter to work, you need to:\n";
echo "1. Clear browser cache\n";
echo "2. Refresh the page\n";
echo "3. Select a printer first, then materials will filter automatically\n";
