<?php
// This file should be included only once
if (!defined('BARANGAYLINK_BOOTSTRAPPED')) {
    define('BARANGAYLINK_BOOTSTRAPPED', true);
    
    // Set the root path
    $root_path = dirname(__DIR__) . DIRECTORY_SEPARATOR;
    
    // Define essential constants only if they're not already defined
    if (!defined('ROOT_PATH')) define('ROOT_PATH', $root_path);
    if (!defined('INCLUDE_PATH')) define('INCLUDE_PATH', $root_path . 'includes' . DIRECTORY_SEPARATOR);
    if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost/BarangayLink/');
    if (!defined('SITE_NAME')) define('SITE_NAME', 'BarangayLink');
    
    // Include the configuration
    require_once INCLUDE_PATH . 'config.php';
    
    // Include other essential files
    require_once INCLUDE_PATH . 'database.php';
    require_once INCLUDE_PATH . 'functions.php';

    
    // Start session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}
?>