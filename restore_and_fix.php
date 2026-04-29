<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';
$backup = $jsFile . '.original.backup';

// Restore original if backup exists
if (file_exists($backup)) {
    copy($backup, $jsFile);
    echo "Restored from backup\n";
}

$content = file_get_contents($jsFile);

// Remove any BOM
$content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

// Replacement char bytes
$r = "\xEF\xBF\xBD";

// Azerbaijani character replacements based on common patterns
$patterns = [
    // Menu items
    "M{$r}st{$r}ri {$r}lav{$r} et" => "Müştəri əlavə et",
    "M{$r}st{$r}ri elav{$r} et" => "Müştəri əlavə et",
    "M{$r}st{$r}ri siyah{$r}si" => "Müştəri siyahısı",
    "{$r}d{$r}ni{$r}sl{$r}r" => "Ödənişlər",
    "{$r}d{$r}ni{$r}s" => "Ödəniş",
    "{$r}sl{$r}r" => "İşlər",
    "Idar{$r} paneli" => "İdarə paneli",
    
    // Dashboard
    "Aktiv M{$r}st{$r}ril{$r}r" => "Aktiv Müştərilər",
    "Ayl{$r}q {$r}sl{$r}r" => "Aylıq İşlər",
    "Ayl{$r}q {$r}d{$r}ni{$r}sl{$r}r" => "Aylıq Ödənişlər",
    
    // Other UI
    "Silinm{$r} tarix{$r}si" => "Silinmə tarixləri",
    "Silinm{$r} tarix{$r}l{$r}ri" => "Silinmə tarixləri",
    "Silinm{$r} {$r}d{$r}ni{$r}sl{$r}r" => "Silinmiş Ödənişlər",
    "Silinm{$r} {$r}sl{$r}r" => "Silinmiş İşlər",
    "Silinm{$r} M{$r}st{$r}ril{$r}r" => "Silinmiş Müştərilər",
    
    // Table headers
    "M{$r}st{$r}ri ID" => "Müştəri ID",
    "M{$r}st{$r}ri ad{$r}" => "Müştəri adı",
    "{$r}d{$r}ni{$r}" => "Ödəniş",
    
    // Messages
    "M{$r}st{$r}ri tapilmadi" => "Müştəri tapılmadı",
    "Server{$r} qo{$r}ulmaq olmadi" => "Serverə qoşulmaq olmadı",
    "PDF {$r}{$r}n p{$r}nc{$r}r{$r} a{$r}ila bilm{$r}di" => "PDF üçün pəncərə açıla bilmədi",
    "Popup icaz{$r}sini yoxla{$r}n" => "Popup icazəsini yoxlayın",
    
    // PDF strings
    "{$r}mumi {$r}d{$r}ni{$r}s" => "ümumi Ödəniş",
    "{$r}mumi" => "ümumi",
    "{$r}sl{$r}rin Say{$r}" => "İşlərin Sayı",
    "{$r}mumi x{$r}rc" => "ümumi xərc",
    "{$r}Mhsul" => "Məhsul",
    "Silinmi{$r} stok" => "Silinmiş stok",
    "Silinmi{$r} x{$r}rcl{$r}r" => "Silinmiş xərclər",
    
    // Units
    "m{$r}{$r}2" => "m²",
    "m{$r}2" => "m²",
    "m{$r}" => "m²",
    
    // Other words
    "tarix{$r}si" => "tarixləri",
    "Say{$r}" => "Sayı",
    "aral{$r}i{$r}i" => "aralığı",
    "{$r}z{$r}l" => "əzəl",
    "Q{$r}liq borc" => "Qalıq borc",
    "{$r}yl{$r}q" => "Aylıq",
    "Qeydiyyat" => "Qeydiyyat",
    
    // Footer and misc
    "{$r}{$r} Eradesign" => "© Eradesign",
    "B{$r}t{$r}n h{$r}quqlar" => "Bütün hüquqlar",
    "h{$r}quqlar" => "hüquqlar",
    "{$r}orunur" => "qorunur",
    "qoru{$r}ur" => "qorunur",
    "{$r}ixis" => "Çıxış",
    "Bu g{$r}n bas ver{$r}nl{$r}r" => "Bu gün baş verənlər",
    "{$r}tt{$r}n h{$r}quqlar" => "Bütün hüquqlar",
];

$total = 0;
foreach ($patterns as $bad => $good) {
    $count = substr_count($content, $bad);
    if ($count > 0) {
        $content = str_replace($bad, $good, $content);
        $total += $count;
        echo "Fixed: $good ($count)\n";
    }
}

// Count remaining
$remaining = substr_count($content, $r);

// Save clean
file_put_contents($jsFile, $content);

echo "\nTotal fixed: $total\n";
echo "Remaining : $remaining\n";

if ($remaining > 0) {
    echo "\nSample remaining:\n";
    $pos = 0;
    for ($i = 0; $i < 5 && ($p = strpos($content, $r, $pos)) !== false; $i++) {
        echo "  ..." . substr($content, max(0, $p-15), 35) . "...\n";
        $pos = $p + 1;
    }
}

echo "\nClear browser cache and test.\n";
