<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

echo "=== REMOVE ALL QUESTION MARKS ===\n";

// Read file
$content = file_get_contents($jsFile);

// Create backup
copy($jsFile, $jsFile . '.remove_q_backup.' . time());

// Remove ALL question marks that are likely corrupted characters
// But keep legitimate question marks in URLs and ternary operators

// First, count all question marks
$totalQuestions = substr_count($content, '?');
echo "Total question marks: $totalQuestions\n";

// Remove question marks in typical Azerbaijani text contexts
$patterns = [
    // Remove ? in these contexts (they're corrupted characters)
    '/M\?st\?ri/' => 'Müştəri',
    '/\?d\?ni\?/' => 'Ödəniş',
    '/\?sl\?r/' => 'İşlər',
    '/Idar\?/' => 'İdarə',
    '/\?lav\?/' => 'əlavə',
    '/siyah\?s\?/' => 'siyahısı',
    '/tarix\?si/' => 'tarixləri',
    '/tarixl\?ri/' => 'tarixləri',
    '/Aktiv M\?st\?ril\?r/' => 'Aktiv Müştərilər',
    '/Ayl\?q/' => 'Aylıq',
    '/Silinm\?/' => 'Silinmə',
    '/M\?st\?ri ID/' => 'Müştəri ID',
    '/M\?st\?ri ad\?/' => 'Müştəri adı',
    '/tap\?lmad\?/' => 'tapılmadı',
    '/Server\?/' => 'Serverə',
    '/qo\?ulmaq/' => 'qoşulmaq',
    '/p\?nc\?r\?/' => 'pəncərə',
    '/a\?\?la/' => 'açıla',
    '/bilm\?di/' => 'bilmədi',
    '/icaz\?sini/' => 'icazəsini',
    '/yoxlay\?n/' => 'yoxlayın',
    '/\?mumi/' => 'ümumi',
    '/\?sl\?rin/' => 'İşlərin',
    '/Say\?/' => 'Sayı',
    '/\?Mhsul/' => 'Məhsul',
    '/aral\?\?\?/' => 'aralığı',
    '/Qal\?q/' => 'Qalıq',
    '/B\?t\?n/' => 'Bütün',
    '/h\?quqlar/' => 'hüquqlar',
    '/qorunur/' => 'qorunur',
    '/\?ix\?/' => 'Çıxış',
    '/g\?n/' => 'gün',
    '/ba\?/' => 'baş',
    '/ver\?nl\?r/' => 'verənlər',
    '/m\?/' => 'm²',
    '/n m/' => 'n m²',
];

$fixed = 0;
foreach ($patterns as $pattern => $replacement) {
    $count = preg_match_all($pattern, $content);
    if ($count > 0) {
        $content = preg_replace($pattern, $replacement, $content);
        $fixed += $count;
        echo "Fixed pattern: $replacement ($count)\n";
    }
}

// Now remove any remaining isolated ? that are likely corrupted
// But keep ? in URLs (contains /) and ternary operators (surrounded by spaces)
$content = preg_replace('/\?(?![\/\s])/', '', $content);

$remainingQuestions = substr_count($content, '?');
echo "\nFixed patterns: $fixed\n";
echo "Remaining ?: $remainingQuestions\n";

// Save
file_put_contents($jsFile, $content);

echo "\nFile saved without question marks.\n";
echo "Clear browser cache and test.\n";

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
