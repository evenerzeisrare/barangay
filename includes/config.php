<?php
// Database configuration
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'barangaylink');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// Site configuration
if (!defined('SITE_NAME')) define('SITE_NAME', 'BarangayLink');
if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost/BarangayLink/');

// Path configuration - define these first
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
if (!defined('INCLUDE_PATH')) define('INCLUDE_PATH', ROOT_PATH . 'includes' . DIRECTORY_SEPARATOR);

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>