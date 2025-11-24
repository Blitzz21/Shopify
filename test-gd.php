<?php
if (extension_loaded('gd') && function_exists('gd_info')) {
    echo "GD is installed!";
    print_r(gd_info());
} else {
    echo "GD is NOT installed.";
}
?>