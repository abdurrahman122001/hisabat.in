<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';
$content = file_get_contents($jsFile);

// The actual replacement char bytes in the file
$replacementChar = "\xEF\xBF\xBD";

// Build patterns with actual replacement char
$map = [
    "M{$replacementChar}st{$replacementChar}ri {$replacementChar}lav{$replacementChar} et" => "Müştəri əlavə et",
    "M{$replacementChar}st{$replacementChar}ri elav{$replacementChar} et" => "Müştəri əlavə et",
    "{$replacementChar}d{$replacementChar}ni{$replacementChar}sl{$replacementChar}r" => "Ödənişlər",
    "{$replacementChar}d{$replacementChar}ni{$replacementChar}sl{$replacementChar}r" => "Ödənişlər",
    "{$replacementChar}d{$replacementChar}ni{$replacementChar}sl{$replacementChar}r" => "Ödənişlər",
    "M{$replacementChar}st{$replacementChar}ri siyah{$replacementChar}si" => "Müştəri siyahısı",
    "Silinm{$replacementChar} tarix{$replacementChar}l{$replacementChar}ri" => "Silinmə tarixləri",
    "Silinm{$replacementChar} tarix{$replacementChar}si" => "Silinmə tarixləri",
    "{$replacementChar}tarix{$replacementChar}si" => "tarixləri",
    "{$replacementChar}{$replacementChar}ixis" => "Çıxış",
    "{$replacementChar}Eradesign" => "© Eradesign",
    "{$replacementChar}{$replacementChar} Eradesign" => "© Eradesign",
    "B{$replacementChar}t{$replacementChar}n h{$replacementChar}quqlar" => "Bütün hüquqlar",
    "h{$replacementChar}quqlar" => "hüquqlar",
    "Aktiv M{$replacementChar}st{$replacementChar}ril{$replacementChar}r" => "Aktiv Müştərilər",
    "M{$replacementChar}st{$replacementChar}ril{$replacementChar}r" => "Müştərilər",
    "Ayliq {$replacementChar}d{$replacementChar}ni{$replacementChar}sl{$replacementChar}r" => "Aylıq Ödənişlər",
    "Ayliq {$replacementChar}{$replacementChar}d{$replacementChar}ni{$replacementChar}sl{$replacementChar}r" => "Aylıq Ödənişlər",
    "Bu g{$replacementChar}n bas ver{$replacementChar}nl{$replacementChar}r" => "Bu gün baş verənlər",
    "{$replacementChar}mumi" => "ümumi",
    "{$replacementChar}Mhsul" => "Məhsul",
    "{$replacementChar}Qeydiyyat" => "Qeydiyyat",
    "{$replacementChar}Qaliq" => "Qalıq",
    "{$replacementChar}Ayl{$replacementChar}q" => "Aylıq",
    "{$replacementChar}Say{$replacementChar}" => "Sayı",
    "tarix{$replacementChar}si" => "tarixləri",
    "arali{$replacementChar}i" => "aralığı",
    "m{$replacementChar}{$replacementChar}2" => "m²",
    "m{$replacementChar}2" => "m²",
];

$fixed = 0;
foreach ($map as $from => $to) {
    $count = substr_count($content, $from);
    if ($count) {
        $content = str_replace($from, $to, $content);
        $fixed += $count;
        echo "Fixed: ($count) => $to\n";
    }
}

// Also replace any single replacement chars with best guesses based on context
// Common single char replacements in Azerbaijani
$singleReplacements = [
    // In context of words
    "g{$replacementChar}n" => "gün",
    "{$replacementChar}n" => "ün", // common suffix
    "{$replacementChar}r" => "ər",
    "{$replacementChar}ri" => "əri",
    "{$replacementChar}li" => "əli",
    "{$replacementChar}si" => "ısı",
    "{$replacementChar}lar" => "ələr",
    "{$replacementChar}lar" => "ilər",
    "{$replacementChar}q" => "ıq",
    "{$replacementChar}k" => "ik",
    "{$replacementChar}r" => "ər",
    "{$replacementChar}s" => "ş",
    "{$replacementChar}i" => "ı",
    "{$replacementChar}u" => "ü",
    "{$replacementChar}e" => "ə",
    "{$replacementChar}a" => "ə",
    "{$replacementChar}o" => "ö",
    "{$replacementChar}c" => "ç",
    "{$replacementChar}g" => "ğ",
];

foreach ($singleReplacements as $from => $to) {
    $count = substr_count($content, $from);
    if ($count) {
        $content = str_replace($from, $to, $content);
        $fixed += $count;
        echo "Fixed single: ($count) => $to\n";
    }
}

$remaining = substr_count($content, $replacementChar);
echo "\nTotal fixed: $fixed\n";
echo "Remaining : $remaining\n";

if ($remaining > 0 && $remaining < 50) {
    echo "\nFinal remaining contexts:\n";
    $pos = 0;
    for ($i = 0; $i < min(20, $remaining); $i++) {
        $p = strpos($content, $replacementChar, $pos);
        if ($p === false) break;
        $ctx = substr($content, max(0, $p-25), 50);
        echo sprintf("  %2d: ...%s...\n", $i+1, $ctx);
        $pos = $p + 1;
    }
}

if ($fixed > 0 || $remaining > 0) {
    file_put_contents($jsFile, "\xEF\xBB\xBF" . $content);
    echo "\nSaved! Clear browser cache and test.\n";
}
