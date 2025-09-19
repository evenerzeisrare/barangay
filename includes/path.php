<?php
// Function to get the correct path for includes
function getIncludePath($filename) {
    return INCLUDE_PATH . $filename;
}

// Function to get the correct URL for assets
function getAssetUrl($path) {
    return SITE_URL . $path;
}

// Function to require files with correct path
function requireInclude($filename) {
    require_once getIncludePath($filename);
}
?>