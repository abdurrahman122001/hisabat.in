<?php
// Direct API test for list_works
$_GET = ['search' => 'CWZ 0061'];

// Capture output
ob_start();
include __DIR__ . '/api/list_works.php';
$output = ob_get_clean();

$data = json_decode($output, true);

echo "Total works returned: " . count($data['works'] ?? []) . "\n";
echo "\nFirst 5 results:\n";
foreach (array_slice($data['works'] ?? [], 0, 5) as $w) {
    echo "- Client: " . ($w['client_id'] ?? 'N/A') . " | Date: " . ($w['date'] ?? 'N/A') . " | Work: " . ($w['work_name'] ?? 'N/A') . "\n";
}

if (empty($data['works'])) {
    echo "\nNo works found for 'CWZ 0061'\n";
} else {
    $clients = array_unique(array_column($data['works'], 'client_id'));
    echo "\nUnique clients in results: " . implode(', ', $clients) . "\n";
}
