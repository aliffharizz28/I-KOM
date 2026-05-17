<?php
// One-time server setup script - DELETE AFTER USE
$dirs = [
    __DIR__ . '/lampiran_tugasan',
    __DIR__ . '/pic',
    __DIR__ . '/pic/logoSIG',
];

echo "<h2>Server Directory Setup</h2>";
foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0775, true)) {
            echo "<p style='color:green'>✅ Created: $dir</p>";
        } else {
            echo "<p style='color:red'>❌ Failed to create: $dir — you need SSH to fix permissions.</p>";
        }
    } else {
        $writable = is_writable($dir);
        echo "<p style='color:" . ($writable ? 'green' : 'orange') . "'>"
            . ($writable ? '✅' : '⚠️') . " Already exists"
            . ($writable ? ' (writable)' : ' (NOT writable - needs chmod)')
            . ": $dir</p>";
    }
}

echo "<hr><p style='color:gray'><strong>Delete this file after use:</strong> /var/www/ikom/ikom/public/serversetup.php</p>";
