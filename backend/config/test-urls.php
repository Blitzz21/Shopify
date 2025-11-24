<?php
echo "Server Name: " . $_SERVER['SERVER_NAME'] . "<br>";
echo "Server Port: " . $_SERVER['SERVER_PORT'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";

// This will show you the correct base path
$base_path = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
echo "Suggested BASE_URL: " . $base_path . "/<br>";
?>