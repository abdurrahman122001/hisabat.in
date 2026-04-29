<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

echo "=== FIXING QUESTION MARKS ===\n";

// Read file
$content = file_get_contents($jsFile);

// Count question marks
$questionCount = substr_count($content, '?');
echo "Found $questionCount question marks\n";

// Create backup
copy($jsFile, $jsFile . '.qm_backup.' . time());

// Common patterns where ? replaces Azerbaijani characters
$patterns = [
    // Menu items
    'M?st?ri ?lav? et' => 'Müştəri əlavə et',
    'M?st?ri elav? et' => 'Müştəri əlavə et',
    'M?st?ri siyah?s?' => 'Müştəri siyahısı',
    '?d?ni?l?r' => 'Ödənişlər',
    '?d?ni?' => 'Ödəniş',
    '?sl?r' => 'İşlər',
    'Idar? paneli' => 'İdarə paneli',
    
    // Dashboard
    'Aktiv M?st?ril?r' => 'Aktiv Müştərilər',
    'Ayl?q ?sl?r' => 'Aylıq İşlər',
    'Ayl?q ?d?ni?l?r' => 'Aylıq Ödənişlər',
    
    // Other UI
    'Silinm? tarixl?ri' => 'Silinmə tarixləri',
    'Silinm? ?d?ni?l?r' => 'Silinmiş Ödənişlər',
    'Silinm? ?sl?r' => 'Silinmiş İşlər',
    'Silinm? M?st?ril?r' => 'Silinmiş Müştərilər',
    
    // Table headers
    'M?st?ri ID' => 'Müştəri ID',
    'M?st?ri ad?' => 'Müştəri adı',
    
    // Messages
    'M?st?ri tap?lmad?' => 'Müştəri tapılmadı',
    'Server? qo?ulmaq olmad?' => 'Serverə qoşulmaq olmadı',
    'PDF ?ç?n p?nc?r? a??la bilm?di' => 'PDF üçün pəncərə açıla bilmədi',
    'Popup icaz?sini yoxlay?n' => 'Popup icazəsini yoxlayın',
    
    // PDF strings
    '?mumi ?d?ni?' => 'ümumi Ödəniş',
    '?mumi' => 'ümumi',
    '?sl?rin Say?' => 'İşlərin Sayı',
    '?mumi x?rc' => 'ümumi xərc',
    '?Mhsul' => 'Məhsul',
    'm?' => 'm²',
    
    // Other words
    'tarixl?ri' => 'tarixləri',
    'Say?' => 'Sayı',
    'aral???' => 'aralığı',
    'Qal?q borc' => 'Qalıq borc',
    'Ayl?q' => 'Aylıq',
    'Qeydiyyat' => 'Qeydiyyat',
    
    // Footer
    'B?t?n h?quqlar' => 'Bütün hüquqlar',
    'h?quqlar' => 'hüquqlar',
    'qorunur' => 'qorunur',
    '?ix?' => 'Çıxış',
    'Bu g?n ba? ver?nl?r' => 'Bu gün baş verənlər',
    
    // Units
    'n m' => 'n m²',
];

// Apply replacements
$fixed = 0;
foreach ($patterns as $bad => $good) {
    $count = substr_count($content, $bad);
    if ($count > 0) {
        $content = str_replace($bad, $good, $content);
        $fixed += $count;
        echo "Fixed: $good ($count)\n";
    }
}

// Count remaining question marks
$remainingQuestions = substr_count($content, '?');
echo "\nFixed patterns: $fixed\n";
echo "Remaining ?: $remainingQuestions\n";

// Save
file_put_contents($jsFile, $content);

echo "\nFile saved. Clear browser cache and test.\n";

// Test with Node.js
$tempFile = tempnam(sys_get_temp_dir(), 'js_test_');
file_put_contents($tempFile, $content);

$nodeCheck = shell_exec("node --check $tempFile 2>&1");
if (empty($nodeCheck)) {
    echo "✓ Node.js validation: PASSED\n";
} else {
    echo "✗ Node.js validation: FAILED\n";
    echo "Error: $nodeCheck\n";
}

unlink($tempFile);
