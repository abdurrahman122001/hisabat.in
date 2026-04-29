<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

echo "=== FORCE CLEAN ===\n";

// Read file
$content = file_get_contents($jsFile);

// Create completely clean version - ASCII only
$clean = '';
for ($i = 0; $i < strlen($content); $i++) {
    $byte = ord($content[$i]);
    
    // Only keep ASCII printable characters (32-126) plus common control chars
    if (($byte >= 32 && $byte <= 126) || $byte === 9 || $byte === 10 || $byte === 13) {
        $clean .= chr($byte);
    }
    // Skip all other bytes (including UTF-8 sequences)
}

echo "Original size: " . strlen($content) . " bytes\n";
echo "Clean size: " . strlen($clean) . " bytes\n";
echo "Removed: " . (strlen($content) - strlen($clean)) . " bytes\n";

// Save clean version
file_put_contents($jsFile, $clean);

echo "Saved clean ASCII-only version\n\n";

// Now add back ONLY the essential Azerbaijani replacements
$replacements = [
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
foreach ($replacements as $bad => $good) {
    $count = substr_count($clean, $bad);
    if ($count > 0) {
        $clean = str_replace($bad, $good, $clean);
        $fixed += $count;
        echo "Fixed: $good ($count)\n";
    }
}

// Final save
file_put_contents($jsFile, $clean);

echo "\nFinal size: " . strlen($clean) . " bytes\n";
echo "Total Azeri fixes: $fixed\n";
echo "\nFile should be syntax-error free now!\n";

// Verify first 500 bytes are clean
echo "\nFirst 500 bytes verification:\n";
$first500 = substr($clean, 0, 500);
$hasNonAscii = false;
for ($i = 0; $i < strlen($first500); $i++) {
    $byte = ord($first500[$i]);
    if ($byte < 32 || $byte > 126) {
        if (!$hasNonAscii) {
            echo "Non-ASCII found:\n";
            $hasNonAscii = true;
        }
        echo "  Position $i: " . sprintf('%02X', $byte) . "\n";
    }
}
if (!$hasNonAscii) {
    echo "All ASCII - clean!\n";
}
