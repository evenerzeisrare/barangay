<?php
$page_title = "Dashboard";
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Redirect based on user role
if (isBuyer()) {
    redirect('buyer/dashboard.php');
} elseif (isSeller()) {
    redirect('seller/dashboard.php');
} else {
    // If user has no role or invalid role, log them out
    session_destroy();
    redirect('login.php');
}
?>