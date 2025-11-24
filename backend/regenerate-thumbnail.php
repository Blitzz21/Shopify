<?php
/**
 * Regenerate missing thumbnails
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'utils/helpers.php';

$database = new Database();
$db = $database->getConnection();

$designId = 2; // Your design ID

echo "<h3>Regenerating Thumbnail for Design ID: $designId</h3>";

// Get design info
$stmt = $db->prepare("SELECT * FROM designs WHERE id = ?");
$stmt->execute([$designId]);
$design = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$design) {
    die("❌ Design not found");
}

echo "Design: {$design['original_filename']}<br>";

// Convert relative path to absolute path for source file
$sourceFile = $design['file_path'];
if (strpos($sourceFile, '/') === 0) {
    $sourceFile = ROOT_PATH . ltrim($sourceFile, '/');
}
$sourceFile = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $sourceFile);

echo "Source file: $sourceFile<br>";
echo "Source exists: " . (file_exists($sourceFile) ? "✅ YES" : "❌ NO") . "<br>";

if (!file_exists($sourceFile)) {
    die("❌ Source design file not found");
}

// Ensure thumbnails directory exists
if (!file_exists(PREVIEW_PATH)) {
    mkdir(PREVIEW_PATH, 0755, true);
    echo "✅ Created thumbnails directory<br>";
}

// Generate thumbnail filename
$thumbFilename = 'thumb_' . $design['stored_filename'];
$thumbPath = PREVIEW_PATH . $thumbFilename;

echo "Thumbnail path: $thumbPath<br>";

// Generate thumbnail using your existing function
if (generateThumbnail($sourceFile, $thumbPath)) {
    echo "✅ Thumbnail generated successfully!<br>";
    echo "File size: " . filesize($thumbPath) . " bytes<br>";
    
    // Update database with the correct absolute path
    $updateStmt = $db->prepare("UPDATE designs SET preview_path = ? WHERE id = ?");
    $updateStmt->execute([$thumbPath, $designId]);
    echo "✅ Database updated with new preview_path<br>";
    
    // Test the web URL
    $webUrl = BASE_URL . 'uploads/thumbnails/' . $thumbFilename;
    echo "Web URL: <a href='$webUrl' target='_blank'>$webUrl</a><br>";
    
    echo "<br>🎉 <strong>Thumbnail regeneration complete!</strong><br>";
    echo "You can now test the preview link in your Print Jobs API.<br>";
    
} else {
    echo "❌ Thumbnail generation failed<br>";
    
    // Debug: Check what generateThumbnail expects
    echo "<h4>Debug Info:</h4>";
    echo "PREVIEW_PATH: " . PREVIEW_PATH . "<br>";
    echo "THUMBNAIL_WIDTH: " . (defined('THUMBNAIL_WIDTH') ? THUMBNAIL_WIDTH : 'NOT DEFINED') . "<br>";
    echo "THUMBNAIL_HEIGHT: " . (defined('THUMBNAIL_HEIGHT') ? THUMBNAIL_HEIGHT : 'NOT DEFINED') . "<br>";
}
?>