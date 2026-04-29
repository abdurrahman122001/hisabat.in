<?php
/**
 * Fix encoding issues in the minified JS file - Version 2
 * This finds and replaces ALL corrupted Unicode characters
 */

$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

if (!file_exists($jsFile)) {
    die("JS file not found: $jsFile\n");
}

// Read as binary to preserve exact bytes
$content = file_get_contents($jsFile);
if ($content === false) {
    die("Failed to read JS file\n");
}

// The replacement character is UTF-8 bytes: 0xEF 0xBF 0xBD
$replacementChar = "\xEF\xBF\xBD";

// First, let's find all occurrences and their context
$positions = [];
$offset = 0;
while (($pos = strpos($content, $replacementChar, $offset)) !== false) {
    $context = substr($content, max(0, $pos - 20), 50);
    $positions[] = ['pos' => $pos, 'context' => $context];
    $offset = $pos + 1;
}

echo "Found " . count($positions) . " replacement characters ()\n";
echo "Showing first 20 contexts:\n";
foreach (array_slice($positions, 0, 20) as $i => $p) {
    echo sprintf("  %3d: ...%s...\n", $i + 1, $p['context']);
}

// Now let's do comprehensive replacements
// The pattern is: the replacement char appears where Azerbaijani chars should be

// Build comprehensive replacement map based on common patterns
$replacements = [
    // Menu items - exact patterns from the file
    'Idar? paneli' => 'İdarə paneli',
    'Mst?rilav? et' => 'Müştəri əlavə et',
    'Mst?ri elav? et' => 'Müştəri əlavə et',
    'Mst?rilav? et' => 'Müştəri əlavə et',
    'Islav? et' => 'İş əlavə et',
    'Is elav? et' => 'İş əlavə et',
    'd?nisl?r' => 'Ödənişlər',
    'dnisl?r' => 'Ödənişlər',
    'Isl?r' => 'İşlər',
    'X?RCL?R' => 'XƏRCLƏR',
    'Anbar' => 'Anbar',  // Already correct
    'Mst?ri siyahisi' => 'Müştəri siyahısı',
    'Mst?ri siyahisi' => 'Müştəri siyahısı',
    'Silinm? tarix?si' => 'Silinmə tarixləri',
    'Silinm? tarix?si' => 'Silinmə tarixləri',
    'Settings' => 'Settings',  // Keep English
    
    // Dashboard cards
    'Aktiv Mst?ril?r' => 'Aktiv Müştərilər',
    'Aktiv Mst?ril?r' => 'Aktiv Müştərilər',
    'Ayl?qsl?r' => 'Aylıq İşlər',
    'Ayl?q Isl?r' => 'Aylıq İşlər',
    'Ayl?q ?d?ni?l?r' => 'Aylıq Ödənişlər',
    'Ayl?qd?ni?l?r' => 'Aylıq Ödənişlər',
    
    // Table headers
    'Mst?ri ID' => 'Müştəri ID',
    'Mst?ri ID' => 'Müştəri ID',
    'Mst?ri adi' => 'Müştəri adı',
    'Mst?ri adi' => 'Müştəri adı',
    'd?nis' => 'Ödəniş',
    'd?nis' => 'Ödəniş',
    
    // Other UI strings
    'Mst?ri tapilmadi' => 'Müştəri tapılmadı',
    'Mst?ri tapilmadi' => 'Müştəri tapılmadı',
    'Server? qo?ulmaq olmadi' => 'Serverə qoşulmaq olmadı',
    'Server? qosulmaq olmadi' => 'Serverə qoşulmaq olmadı',
    'PDF n p?nc?r? aila bilm?di' => 'PDF üçün pəncərə açıla bilmədi',
    'PDF n p?nc?r? acila bilm?di' => 'PDF üçün pəncərə açıla bilmədi',
    'Popup icaz?sini yoxlay?n' => 'Popup icazəsini yoxlayın',
    
    // PDF/Print strings
    'mumid?nis' => 'Ümumi Ödəniş',
    'umumi d?nis' => 'ümumi ödəniş',
    'Isl?rin Sayi' => 'İşlərin Sayı',
    'mumi' => 'Ümumi',
    'umumi' => 'ümumi',
    'mumi x?rc' => 'Ümumi xərc',
    'umumi x?rc' => 'ümumi xərc',
    'M?hsul' => 'Məhsul',
    'Silinmis stok' => 'Silinmiş stok',
    'Silinmis x?rcl?r' => 'Silinmiş xərclər',
    'Silinmis Isl?r' => 'Silinmiş İşlər',
    'Silinmisd?nisl?r' => 'Silinmiş Ödənişlər',
    'Silinmis Mst?ril?r' => 'Silinmiş Müştərilər',
    'Silinmis Mst?ril?r' => 'Silinmiş Müştərilər',
    
    // Table columns
    'Qeyd' => 'Qeyd',
    'Mst?ri ID' => 'Müştəri ID',
    'Telefon' => 'Telefon',
    'Email' => 'Email',
    'Qeydiyyat tarixi' => 'Qeydiyyat tarixi',
    'Ad' => 'Ad',
    
    // Units
    'm' => 'm²',
    
    // Common words
    'tarix?si' => 'tarixləri',
    'tarix?si' => 'tarixləri',
    'Sayi' => 'Sayı',
    'Sayi' => 'Sayı',
    'tarixi' => 'tarixi',
    'araligi' => 'aralığı',
];

$fixedCount = 0;
foreach ($replacements as $bad => $good) {
    // Replace with actual replacement char
    $badPattern = str_replace('?', $replacementChar, $bad);
    $count = substr_count($content, $badPattern);
    if ($count > 0) {
        $content = str_replace($badPattern, $good, $content);
        $fixedCount += $count;
        echo "Fixed: '$bad' => '$good' ($count occurrences)\n";
    }
}

// Also try with literal ? for any remaining
$fixedCount2 = 0;
foreach ($replacements as $bad => $good) {
    if (strpos($bad, '?') !== false) {
        $count = substr_count($content, $bad);
        if ($count > 0) {
            $content = str_replace($bad, $good, $content);
            $fixedCount2 += $count;
            echo "Fixed (literal ?): '$bad' => '$good' ($count occurrences)\n";
        }
    }
}

// Check remaining bad chars
$remaining = substr_count($content, $replacementChar);
echo "\n";
echo "Total fixes: " . ($fixedCount + $fixedCount2) . "\n";
echo "Remaining  characters: $remaining\n";

if ($remaining > 0) {
    echo "\nRemaining contexts:\n";
    $offset = 0;
    $shown = 0;
    while (($pos = strpos($content, $replacementChar, $offset)) !== false && $shown < 10) {
        $context = substr($content, max(0, $pos - 20), 50);
        echo "  ...$context...\n";
        $offset = $pos + 1;
        $shown++;
    }
}

// Save the fixed content
if ($fixedCount > 0 || $fixedCount2 > 0 || $remaining > 0) {
    // Backup original if not already backed up
    $backupFiles = glob($jsFile . '.backup.*');
    if (empty($backupFiles)) {
        $backupFile = $jsFile . '.backup.' . date('YmdHis');
        copy($jsFile, $backupFile);
        echo "\nBackup created: $backupFile\n";
    }
    
    // Write with UTF-8 BOM
    $result = file_put_contents($jsFile, "\xEF\xBB\xBF" . $content);
    if ($result !== false) {
        echo "Fixed JS file saved successfully.\n";
    } else {
        echo "ERROR: Failed to write fixed JS file\n";
    }
}

echo "\nDone! Clear browser cache and test.\n";
