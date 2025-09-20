<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isSeller()) {
    redirect('../index.php');
}

if (!isset($_GET['id'])) {
    redirect('manage_services.php');
}

$user_id = $_SESSION['user_id'];
$service_id = $_GET['id'];

$database = new Database();
$db = $database->getConnection();

// Verify the service belongs to the user
$query = "SELECT * FROM services WHERE id = :service_id AND seller_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':service_id', $service_id);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    $_SESSION['error'] = 'Service not found or you do not have permission to delete it.';
    redirect('manage_services.php');
}

// Delete the service
$query = "DELETE FROM services WHERE id = :service_id AND seller_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':service_id', $service_id);
$stmt->bindParam(':user_id', $user_id);

if ($stmt->execute()) {
    // Log the service deletion action
    logAction($user_id, 'delete_service', 'services', $service_id);
    
    $_SESSION['success'] = 'Service deleted successfully.';
} else {
    $_SESSION['error'] = 'Error deleting service. Please try again.';
}

redirect('manage_services.php');
?>