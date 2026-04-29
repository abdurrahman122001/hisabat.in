<?php
$jsFile = __DIR__ . '/ui/assets/index-CC2b_5k0.js';

echo "=== RESTORING ORIGINAL ===\n";

// Try to restore from git if available
if (file_exists(__DIR__ . '/.git')) {
    echo "Git repository found. Attempting to restore...\n";
    $gitCmd = 'cd ' . __DIR__ . ' && git checkout HEAD -- ui/assets/index-CC2b_5k0.js 2>&1';
    $output = shell_exec($gitCmd);
    echo "Git output: $output\n";
    
    if (file_exists($jsFile)) {
        echo "File restored from git\n";
    } else {
        echo "Git restore failed\n";
    }
}

// If no git or restore failed, check for any backup
if (!file_exists($jsFile) || filesize($jsFile) === 0) {
    echo "Looking for backup files...\n";
    $backups = glob(__DIR__ . '/ui/assets/index-CC2b_5k0.js.*');
    
    if (!empty($backups)) {
        // Use the most recent backup
        $latest = end($backups);
        copy($latest, $jsFile);
        echo "Restored from: " . basename($latest) . "\n";
    } else {
        echo "No backups found!\n";
        die("Cannot restore file\n");
    }
}

// Verify the restored file
$size = filesize($jsFile);
echo "Restored file size: $size bytes\n";

// Check if it's valid
$content = file_get_contents($jsFile);
if (strlen($content) < 1000) {
    echo "WARNING: File seems too small\n";
}

echo "File restored. Now run: php force_clean.php\n";
echo "Then clear browser cache and test.\n";
