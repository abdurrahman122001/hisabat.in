<?php
/**
 * Fix encoding issues in the minified JS file
 * Run this once to fix corrupted Turkish/Azerbaijani characters
 */

$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

if (!file_exists($jsFile)) {
    die("JS file not found: $jsFile\n");
}

$content = file_get_contents($jsFile);
if ($content === false) {
    die("Failed to read JS file\n");
}

// Common corrupted patterns and their fixes
$replacements = [
    // Welcome message
    'Xos g?ldiniz' => 'Xoş gəldiniz',
    'Xo? g?ldiniz' => 'Xoş gəldiniz',

    // Customer related
    'M?st?ri' => 'Müştəri',
    'Mst?ri' => 'Müştəri',
    'Müst?ri' => 'Müştəri',

    // Other common words
    'd?nis' => 'Ödəniş',
    '?d?nis' => 'Ödəniş',
    'Telefon' => 'Telefon', // Usually OK
    'Tarix' => 'Tarix', // Usually OK
    'Qaliq borc' => 'Qalıq borc',
    'Qaliq b?rc' => 'Qalıq borc',
    'Avans' => 'Avans', // Usually OK

    // Menu items from screenshot
    '?s?rl?ri t?l?b?t' => 'İşləri tələbat',
    '?s ?lav? et' => 'İş əlavə et',
    'M?d?rl?r' => 'Mədənilər',
    'BORCLAR' => 'BORCLAR', // OK
    '?d?ri' => 'Əməliyyatlar',
    'X?RCL?R' => 'XƏRCLƏR',
    'Anbar' => 'Anbar', // OK
    'M?d?n siyahisi' => 'Məhsul siyahısı',
    'Silinm? tar?x?l?ri' => 'Silinmə tarixləri',
    'Settings' => 'Settings', // Keep English

    // Dashboard labels
    'Aktiv M?st?ril?r' => 'Aktiv Müştərilər',
    'Ayl?q ??l?r' => 'Aylıq İşlər',
    'Anbar' => 'Anbar',
    'Ayl?q ?d?ni?l?r' => 'Aylıq Ödənişlər',

    // Other UI strings
    'M?st?ri tapilmadi' => 'Müştəri tapılmadı',
    'Server? qo?ulmaq olmadi' => 'Serverə qoşulmaq olmadı',
    'PDF ??n p?nc?r? a??la bilm?di' => 'PDF üçün pəncərə açıla bilmədi',
    'Popup icaz?sini yoxlay?n' => 'Popup icazəsini yoxlayın',
];

$fixedCount = 0;
foreach ($replacements as $bad => $good) {
    $count = substr_count($content, $bad);
    if ($count > 0) {
        $content = str_replace($bad, $good, $content);
        $fixedCount += $count;
        echo "Fixed: '$bad' => '$good' ($count occurrences)\n";
    }
}

// Also check for any remaining characters
$replacementChar = "";
$badCount = substr_count($content, $replacementChar);
if ($badCount > 0) {
    echo "\nWARNING: Still found $badCount replacement characters () in file.\n";
    echo "Some characters couldn't be automatically fixed.\n";

    // Show context around remaining bad characters
    $pos = 0;
    for ($i = 0; $i < min(5, $badCount); $i++) {
        $pos = strpos($content, $replacementChar, $pos);
        if ($pos === false)
            break;
        $context = substr($content, max(0, $pos - 30), 60);
        echo "  Context: ...$context...\n";
        $pos++;
    }
}

// Save the fixed content
if ($fixedCount > 0 || $badCount > 0) {
    // Backup original
    $backupFile = $jsFile . '.backup.' . date('YmdHis');
    copy($jsFile, $backupFile);
    echo "\nBackup created: $backupFile\n";

    // Write fixed content with UTF-8 BOM to ensure proper encoding
    $result = file_put_contents($jsFile, "\xEF\xBB\xBF" . $content);
    if ($result !== false) {
        echo "Fixed JS file saved successfully.\n";
        echo "Total fixes: $fixedCount\n";
    } else {
        echo "ERROR: Failed to write fixed JS file\n";
    }
} else {
    echo "No encoding issues found in JS file.\n";
}
