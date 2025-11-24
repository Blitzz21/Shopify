<?php
require_once 'config/config.php';

echo "<h3>Thumbnail File Check</h3>";

$thumbnailFile = "thumb_design_test_123.png";
$thumbnailsDir = PREVIEW_PATH;
$fullPath = $thumbnailsDir . $thumbnailFile;

echo "Looking for: $thumbnailFile<br>";
echo "Full path: $fullPath<br>";
echo "File exists: " . (file_exists($fullPath) ? "✅ YES" : "❌ NO") . "<br>";

if (file_exists($fullPath)) {
    echo "File size: " . filesize($fullPath) . " bytes<br>";
    $testUrl = BASE_URL . 'uploads/thumbnails/' . $thumbnailFile;
    echo "Test URL: <a href='$testUrl' target='_blank'>$testUrl</a><br>";
} else {
    echo "<h4>Files that DO exist in thumbnails directory:</h4>";
    $files = glob($thumbnailsDir . "*");
    if ($files) {
        foreach ($files as $file) {
            $filename = basename($file);
            echo "- $filename<br>";
        }
    } else {
        echo "No files found in thumbnails directory<br>";
    }
    
    echo "<h4>Checking designs directory for source file:</h4>";
    $designsDir = DESIGN_PATH;
    $designFiles = glob($designsDir . "*");
    if ($designFiles) {
        foreach ($designFiles as $file) {
            $filename = basename($file);
            echo "- $filename<br>";
        }
    }
}
?>