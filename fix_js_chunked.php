<?php
// Fix JS files - process in chunks to avoid memory issues
set_time_limit(300);

$files = [
    'ui/assets/index-CC2b_5k0.js',
    'deploy_hesabat_root/assets/index-CC2b_5k0.js'
];

$chunkSize = 500000; // 500KB chunks

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        continue;
    }
    
    echo "\nProcessing $file...\n";
    
    $size = filesize($file);
    echo "  File size: " . round($size/1024/1024, 2) . " MB\n";
    
    $tempFile = $file . '.tmp';
    $out = fopen($tempFile, 'w');
    $in = fopen($file, 'r');
    
    $totalReplaced = 0;
    $chunkNum = 0;
    $carryOver = ''; // Handle patterns split across chunks
    
    while (!feof($in)) {
        $chunk = fread($in, $chunkSize);
        $chunkNum++;
        
        // Prepend carry-over from previous chunk
        $chunk = $carryOver . $chunk;
        
        // Save last 20 chars for next chunk (in case pattern is split)
        $carryOver = substr($chunk, -20);
        $chunk = substr($chunk, 0, -20);
        
        // Count and replace
        $count1 = substr_count($chunk, '"/api/');
        $count2 = substr_count($chunk, "'/api/");
        $count3 = substr_count($chunk, '`/api/');
        $count4 = substr_count($chunk, '(/api/');
        $total = $count1 + $count2 + $count3 + $count4;
        $totalReplaced += $total;
        
        // Replace patterns
        $chunk = str_replace('"/api/', '"api/', $chunk);
        $chunk = str_replace("'/api/", "'api/", $chunk);
        $chunk = str_replace('`/api/', '`api/', $chunk);
        $chunk = str_replace('(/api/', '(api/', $chunk);
        
        fwrite($out, $chunk);
        
        if ($total > 0) {
            echo "  Chunk $chunkNum: replaced $total occurrences\n";
        }
    }
    
    // Process carry-over at end
    if ($carryOver) {
        $carryOver = str_replace('"/api/', '"api/', $carryOver);
        $carryOver = str_replace("'/api/", "'api/", $carryOver);
        $carryOver = str_replace('`/api/', '`api/', $carryOver);
        $carryOver = str_replace('(/api/', '(api/', $carryOver);
        fwrite($out, $carryOver);
    }
    
    fclose($in);
    fclose($out);
    
    // Replace original with temp
    rename($tempFile, $file);
    
    echo "  Total replaced: $totalReplaced\n";
    echo "  Done!\n";
}

echo "\n========================================\n";
echo "All files patched successfully!\n";
echo "Clear browser cache and try again.\n";
echo "========================================\n";
