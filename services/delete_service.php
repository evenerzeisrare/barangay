<?php
session_start();
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';

// Check if user is logged in and is a seller
checkRole(['seller']);

if (!isset($_GET['id'])) {
    header('Location: ../dashboard_seller.php?error=Invalid service ID');
    exit();
}

$service_id = $_GET['id'];

try {
    // Check if service belongs to the logged-in seller
    $stmt = $pdo->prepare("SELECT id FROM services WHERE id = ? AND seller_id = ?");
    $stmt->execute([$service_id, $_SESSION['user_id']]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$service) {
        header('Location: ../dashboard_seller.php?error=Service not found');
        exit();
    }
    
    // Check if there are any active bookings for this service
    $stmt = $pdo->prepare("SELECT id FROM bookings WHERE service_id = ? AND status IN ('pending', 'approved')");
    $stmt->execute([$service_id]);
    
    if ($stmt->rowCount() > 0) {
        header('Location: ../dashboard_seller.php?error=Cannot delete service with active bookings');
        exit();
    }
    
    // Delete the service
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    
    header('Location: ../dashboard_seller.php?success=Service deleted successfully');
    exit();
    
} catch (PDOException $e) {
    header('Location: ../dashboard_seller.php?error=Database error: ' . $e->getMessage());
    exit();
}
?>
