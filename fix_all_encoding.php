<?php
/**
 * Comprehensive encoding fix for minified JS
 * Run on server: php fix_all_encoding.php
 */

$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

if (!file_exists($jsFile)) {
    die("JS file not found\n");
}

$content = file_get_contents($jsFile);
if (!$content) die("Failed to read file\n");

// UTF-8 Replacement Character
$bad = "\xEF\xBF\xBD";

// Comprehensive map of all corrupted patterns
$map = [
    // Single char fixes (where one = one Azeri char)
    'Idar paneli' => 'İdarə paneli',
    'Mstri' => 'Müştəri',
    'lav et' => 'əlavə et',
    'dni' => 'Ödəniş',
    'dnilr' => 'Ödənişlər',
    'dnilr' => 'Ödənişlər',
    'dnilr' => 'Ödənişlər',
    'dni' => 'Ödəniş',
    'slr' => 'İşlər',
    'slrin' => 'İşlərin',
    'XRCLR' => 'XƏRCLƏR',
    'Mdn' => 'Mədən',
    'siyahisi' => 'siyahısı',
    'Silinm' => 'Silinmə',
    'tarixsi' => 'tarixləri',
    'Aktiv Mstrilr' => 'Aktiv Müştərilər',
    'Aylqslr' => 'Aylıq İşlər',
    'Aylqdnilr' => 'Aylıq Ödənişlər',
    'Mstri ID' => 'Müştəri ID',
    'Mstri adi' => 'Müştəri adı',
    'Qaliq borc' => 'Qalıq borc',
    'Mstri tapilmadi' => 'Müştəri tapılmadı',
    'Server qoulmaq olmadi' => 'Serverə qoşulmaq olmadı',
    'PDF n pncr aila bilmdi' => 'PDF üçün pəncərə açıla bilmədi',
    'Popup icazsini yoxlayn' => 'Popup icazəsini yoxlayın',
    'm' => 'm²',
    'mumi' => 'Ümumi',
    'Mhsul' => 'Məhsul',
    'Silinmis' => 'Silinmiş',
    'xrclr' => 'xərclər',
    'Silinmis Mstrilr' => 'Silinmiş Müştərilər',
    'Silinmisdnilr' => 'Silinmiş Ödənişlər',
    'Islrin Sayi' => 'İşlərin Sayı',
    'mumi xrc' => 'ümumi xərc',
    'Sayi' => 'Sayı',
    'araligi' => 'aralığı',
    'Qeydiyyat' => 'Qeydiyyat',
    'tarixi' => 'tarixi',
    'Telefon' => 'Telefon',
    'Email' => 'Email',
    'Ad' => 'Ad',
    'Qeyd' => 'Qeyd',
    'Tarix' => 'Tarix',
    'Avans' => 'Avans',
    'BORCLAR' => 'BORCLAR',
    'Anbar' => 'Anbar',
    'Settings' => 'Settings',
    
    // Multiple patterns
    'Mstrilav et' => 'Müştəri əlavə et',
    'Mstri siyahisi' => 'Müştəri siyahısı',
    'Islav et' => 'İş əlavə et',
    'Islav et' => 'İş əlavə et',
    'Silinm tarixsi' => 'Silinmə tarixləri',
    'Aylqdnilr' => 'Aylıq Ödənişlər',
    'Server qoulmaq olmadi' => 'Serverə qoşulmaq olmadı',
    'Popup icazsini yoxlayn' => 'Popup icazəsini yoxlayın',
];

$fixed = 0;
foreach ($map as $from => $to) {
    $count = substr_count($content, $from);
    if ($count) {
        $content = str_replace($from, $to, $content);
        $fixed += $count;
        echo "Fixed: $from => $to ($count)\n";
    }
}

// Check remaining
$remaining = substr_count($content, $bad);
echo "\nTotal fixed: $fixed\n";
echo "Remaining: $remaining\n";

if ($remaining > 0) {
    echo "\nRemaining contexts:\n";
    $pos = 0; $n = 0;
    while (($p = strpos($content, $bad, $pos)) !== false && $n < 10) {
        $ctx = substr($content, max(0, $p-15), 35);
        echo "  ...$ctx...\n";
        $pos = $p + 1; $n++;
    }
}

// Save with BOM
if ($fixed > 0 || $remaining > 0) {
    copy($jsFile, $jsFile . '.bak.' . time());
    file_put_contents($jsFile, "\xEF\xBB\xBF" . $content);
    echo "\nSaved. Clear browser cache!\n";
}
