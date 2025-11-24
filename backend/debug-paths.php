<?php
echo "<h2>Path Debug Information</h2>";

echo "Current file: " . __FILE__ . "<br>";
echo "Directory: " . __DIR__ . "<br>";
echo "Config file location: " . __DIR__ . "/config/config.php<br>";
echo "Config exists: " . (file_exists(__DIR__ . "/config/config.php") ? "YES" : "NO") . "<br>";

// Test the ROOT_PATH calculation
$test_root = realpath(__DIR__ . '/../') . '/';
echo "Calculated ROOT_PATH: " . $test_root . "<br>";

// Test if we can require the config
try {
    require_once __DIR__ . '/config/config.php';
    echo "✅ Config loaded successfully<br>";
    echo "ROOT_PATH constant: " . (defined('ROOT_PATH') ? ROOT_PATH : 'NOT DEFINED') . "<br>";
} catch (Error $e) {
    echo "❌ Config error: " . $e->getMessage() . "<br>";
}
?>