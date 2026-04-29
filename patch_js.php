<?php
// Patch JS files to use relative API paths
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
    
    // Count occurrences before
    $count1 = substr_count($content, '"/api/');
    $count2 = substr_count($content, "'/api/");
    echo "  Found $count1 double-quoted and $count2 single-quoted /api/ references\n";
    
    // Replace
    $content = str_replace('"/api/', '"api/', $content);
    $content = str_replace("'/api/", "'api/", $content);
    
    file_put_contents($file, $content);
    echo "  Patched successfully!\n\n";
}

echo "Done! Clear browser cache and try again.\n";
