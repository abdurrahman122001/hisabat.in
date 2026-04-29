<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';
$content = file_get_contents($jsFile);

// Remaining patterns from output
$map = [
    'Mstri lav et' => 'Müştəri əlavə et',
    'Mstri elav et' => 'Müştəri əlavə et',
    'Mstri ?lav? et' => 'Müştəri əlavə et',
    'Ödnilr' => 'Ödənişlər',
    'dnilr' => 'Ödənişlər',
    'Mstri siyahisi' => 'Müştəri siyahısı',
    'Mstri siyahısı' => 'Müştəri siyahısı',
    'Silinm tarixsi' => 'Silinmə tarixləri',
    'Silinmə tarixsi' => 'Silinmə tarixləri',
    'Silinm tarixlri' => 'Silinmə tarixləri',
    'ixis' => 'Çıxış',
    ' Eradesign' => '© Eradesign',
    'Eradesign. Btn hquqlar' => 'Eradesign. Bütün hüquqlar',
    'Btn hquqlar' => 'Bütün hüquqlar',
    'hquqlar' => 'hüquqlar',
    'qorunur' => 'qorunur',
    'Aktiv Mstrilr' => 'Aktiv Müştərilər',
    'Aktiv Mstrilr' => 'Aktiv Müştərilər',
    'Mstrilr' => 'Müştərilər',
    'Mstri' => 'Müştəri',
    'dni' => 'Ödəniş',
    'dnilr' => 'Ödənişlər',
    'Aylq' => 'Aylıq',
    'Qaliq' => 'Qalıq',
    'borc' => 'borc',
    'Avans' => 'Avans',
    'Tarix' => 'Tarix',
    'Telefon' => 'Telefon',
    'Qeyd' => 'Qeyd',
    'Ad' => 'Ad',
    'Email' => 'Email',
    'Sayi' => 'Sayı',
    'mumi' => 'ümumi',
    'Mhsul' => 'Məhsul',
    'tarixi' => 'tarixi',
    'araligi' => 'aralığı',
    'Qeydiyyat' => 'Qeydiyyat',
    'm' => 'm²',
    'm²²' => 'm²',
    'n m' => 'n m²',
    'nm' => 'n m²',
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

// Check remaining replacement chars
$bad = "\xEF\xBF\xBD";
$remaining = substr_count($content, $bad);
echo "\nNew fixes: $fixed\n";
echo "Remaining: $remaining\n";

// Show remaining contexts
if ($remaining > 0) {
    echo "\nRemaining contexts:\n";
    $pos = 0; $n = 0;
    while (($p = strpos($content, $bad, $pos)) !== false && $n < 15) {
        $ctx = substr($content, max(0, $p-20), 45);
        echo "  ...$ctx...\n";
        $pos = $p + 1; $n++;
    }
}

// Save
if ($fixed > 0) {
    file_put_contents($jsFile, "\xEF\xBB\xBF" . $content);
    echo "\nSaved! Clear browser cache.\n";
}
