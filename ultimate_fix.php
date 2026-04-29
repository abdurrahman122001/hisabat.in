<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

echo "=== ULTIMATE FIX ===\n";

// Read entire file as binary
$content = file_get_contents($jsFile);

// Remove BOM if present
if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
    $content = substr($content, 3);
    echo "Removed BOM\n";
}

// Find and remove ALL UTF-8 replacement characters
$replacementChar = "\xEF\xBF\xBD";
$removed = substr_count($content, $replacementChar);
$content = str_replace($replacementChar, '', $content);
echo "Removed $removed replacement characters\n";

// Also remove any other problematic UTF-8 sequences
$problematic = [
    "\xFF\xFD", // Invalid UTF-8
    "\x00",     // Null bytes
    "\xEF\xBF", // Incomplete UTF-8
];

foreach ($problematic as $seq) {
    $count = substr_count($content, $seq);
    if ($count > 0) {
        $content = str_replace($seq, '', $content);
        echo "Removed $count problematic sequences\n";
    }
}

// Now add back the Azerbaijani characters properly
$azeri = [
    'M?st?ri ?lav? et' => 'Müştəri əlavə et',
    'M?st?ri siyah?s?' => 'Müştəri siyahısı',
    '?d?ni?l?r' => 'Ödənişlər',
    '?d?ni?' => 'Ödəniş',
    '?sl?r' => 'İşlər',
    'Idar? paneli' => 'İdarə paneli',
    'Aktiv M?st?ril?r' => 'Aktiv Müştərilər',
    'Ayl?q ?sl?r' => 'Aylıq İşlər',
    'Ayl?q ?d?ni?l?r' => 'Aylıq Ödənişlər',
    'Silinm? tarixl?ri' => 'Silinmə tarixləri',
    'M?st?ri ID' => 'Müştəri ID',
    'M?st?ri ad?' => 'Müştəri adı',
    'M?st?ri tap?lmad?' => 'Müştəri tapılmadı',
    'Server? qo?ulmaq olmad?' => 'Serverə qoşulmaq olmadı',
    'PDF ?ç?n p?nc?r? a??la bilm?di' => 'PDF üçün pəncərə açıla bilmədi',
    'Popup icaz?sini yoxlay?n' => 'Popup icazəsini yoxlayın',
    'ümumi ?d?ni?' => 'ümumi Ödəniş',
    '?mumi' => 'ümumi',
    '?sl?rin Say?' => 'İşlərin Sayı',
    'ümumi x?rc' => 'ümumi xərc',
    'M?hsul' => 'Məhsul',
    'Silinmi? x?rcl?r' => 'Silinmiş xərclər',
    'Silinmi? ?sl?r' => 'Silinmiş İşlər',
    'Silinmi? M?st?ril?r' => 'Silinmiş Müştərilər',
    'm?' => 'm²',
    'tarixl?ri' => 'tarixləri',
    'Say?' => 'Sayı',
    'aral???' => 'aralığı',
    'Qal?q borc' => 'Qalıq borc',
    'Ayl?q' => 'Aylıq',
    '© Eradesign' => '© Eradesign',
    'Bütün hüquqlar' => 'Bütün hüquqlar',
    'hüquqlar' => 'hüquqlar',
    'qorunur' => 'qorunur',
    'Ç?x??' => 'Çıxış',
    'Bu gün ba? ver?nl?r' => 'Bu gün baş verənlər',
];

$fixed = 0;
foreach ($azeri as $bad => $good) {
    $count = substr_count($content, $bad);
    if ($count > 0) {
        $content = str_replace($bad, $good, $content);
        $fixed += $count;
        echo "Fixed: $good ($count)\n";
    }
}

// Save WITHOUT any BOM
$result = file_put_contents($jsFile, $content);

if ($result !== false) {
    echo "\nSUCCESS: File saved clean\n";
    echo "Final size: " . strlen($content) . " bytes\n";
    echo "Total Azeri fixes: $fixed\n";
    echo "\nClear browser cache and reload!\n";
} else {
    echo "\nERROR: Failed to save file\n";
}

// Verify first 100 bytes are clean
echo "\nFirst 100 bytes check:\n";
$first100 = substr($content, 0, 100);
for ($i = 0; $i < strlen($first100); $i++) {
    $byte = ord($first100[$i]);
    if ($byte < 32 || $byte > 126) {
        echo "Non-ASCII at pos $i: " . sprintf('%02X', $byte) . "\n";
    }
}
