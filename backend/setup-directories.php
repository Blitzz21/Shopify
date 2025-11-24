<?php
// backend/setup-directories.php
require_once 'config/config.php';

echo "Setting up application directories...<br><br>";

$directories = [
    UPLOAD_PATH => 'Main uploads',
    DESIGN_PATH => 'Design files', 
    PREVIEW_PATH => 'Thumbnails',
    TEMP_PATH => 'Temporary files',
    BACKUP_PATH => 'Backups',
    PRINT_READY_PATH => 'Print-ready files', 
    LOG_PATH => 'Logs',
    CACHE_PATH => 'Cache'
];

foreach ($directories as $path => $purpose) {
    if (!file_exists($path)) {
        if (mkdir($path, 0755, true)) {
            echo "✅ Created: $purpose<br>";
            // Add security to upload directories
            if (strpos($path, 'upload') !== false) {
                file_put_contents($path . '.htaccess', "Order deny,allow\nDeny from all\n");
            }
        } else {
            echo "❌ Failed: $purpose<br>";
        }
    } else {
        echo "✓ Exists: $purpose<br>";
    }
}

echo "<br>Setup complete!";
?>