<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';
$content = file_get_contents($jsFile);

// Remove BOM if present
if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
    $content = substr($content, 3);
}

$bad = "\xEF\xBF\xBD";

// All known patterns with actual replacement char
$map = [
    "M{$bad}st{$bad}ri {$bad}lav{$bad} et" => "Müştəri əlavə et",
    "M{$bad}st{$bad}ri elav{$bad} et" => "Müştəri əlavə et",
    "{$bad}d{$bad}ni{$bad}sl{$bad}r" => "Ödənişlər",
    "M{$bad}st{$bad}ri siyah{$bad}si" => "Müştəri siyahısı",
    "Silinm{$bad} tarix{$bad}l{$bad}ri" => "Silinmə tarixləri",
    "Silinm{$bad} tarix{$bad}si" => "Silinmə tarixləri",
    "{$bad}tarix{$bad}si" => "tarixləri",
    "{$bad}{$bad}ixis" => "Çıxış",
    "{$bad}{$bad} Eradesign" => "© Eradesign",
    "{$bad}Eradesign" => "© Eradesign",
    "B{$bad}t{$bad}n h{$bad}quqlar" => "Bütün hüquqlar",
    "h{$bad}quqlar" => "hüquqlar",
    "Aktiv M{$bad}st{$bad}ril{$bad}r" => "Aktiv Müştərilər",
    "M{$bad}st{$bad}ril{$bad}r" => "Müştərilər",
    "Ayliq {$bad}d{$bad}ni{$bad}sl{$bad}r" => "Aylıq Ödənişlər",
    "Bu g{$bad}n bas ver{$bad}nl{$bad}r" => "Bu gün baş verənlər",
    "{$bad}mumi" => "ümumi",
    "{$bad}Mhsul" => "Məhsul",
    "{$bad}Qeydiyyat" => "Qeydiyyat",
    "{$bad}Qaliq" => "Qalıq",
    "{$bad}Ayl{$bad}q" => "Aylıq",
    "{$bad}Say{$bad}" => "Sayı",
    "tarix{$bad}si" => "tarixləri",
    "arali{$bad}i" => "aralığı",
    "m{$bad}{$bad}2" => "m²",
    "m{$bad}2" => "m²",
    "g{$bad}n" => "gün",
    "b{$bad}t{$bad}n" => "bütün",
    "bas ver{$bad}nl{$bad}r" => "baş verənlər",
    "ver{$bad}nl{$bad}r" => "verənlər",
    "bas ver" => "baş ver",
    "p{$bad}nc{$bad}r{$bad}" => "pəncərə",
    "aila bil{$bad}di" => "açıla bilmədi",
    "qo{$bad}ulmaq" => "qoşulmaq",
    "x{$bad}rc" => "xərc",
    "{$bad}sl{$bad}r" => "İşlər",
    "{$bad}d{$bad}ni{$bad}" => "Ödəniş",
    "{$bad}d{$bad}ni{$bad}sl{$bad}r" => "Ödənişlər",
    "{$bad}d{$bad}nis" => "Ödəniş",
    "{$bad}z{$bad}l" => "əzəl",
    "yoxla{$bad}n" => "yoxlayın",
    "icaz{$bad}sini" => "icazəsini",
    "{$bad}hsul" => "Məhsul",
    "{$bad}hsul" => "əhsul",
    "M{$bad}hsul" => "Məhsul",
    "qoru{$bad}ur" => "qorunur",
];

$fixed = 0;
foreach ($map as $from => $to) {
    $count = substr_count($content, $from);
    if ($count) {
        $content = str_replace($from, $to, $content);
        $fixed += $count;
    }
}

$remaining = substr_count($content, $bad);

// Save WITHOUT BOM
file_put_contents($jsFile, $content);

echo "Fixed: $fixed\n";
echo "Remaining : $remaining\n";

if ($remaining > 0) {
    echo "\nContexts:\n";
    $pos = 0;
    for ($i = 0; $i < min(10, $remaining); $i++) {
        $p = strpos($content, $bad, $pos);
        if ($p === false) break;
        $ctx = substr($content, max(0, $p-20), 45);
        echo "  ...$ctx...\n";
        $pos = $p + 1;
    }
}
echo "Done! Clear browser cache.\n";
