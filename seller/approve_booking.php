<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isSeller()) {
    redirect('../index.php');
}

if (!isset($_GET['id'])) {
    redirect('bookings.php');
}

$booking_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$database = new Database();
$db = $database->getConnection();

// Verify the booking belongs to the seller
$query = "SELECT b.* FROM bookings b 
          JOIN services s ON b.service_id = s.id 
          WHERE b.id = :booking_id AND s.seller_id = :user_id AND b.status = 'pending'";
$stmt = $db->prepare($query);
$stmt->bindParam(':booking_id', $booking_id);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    $_SESSION['error'] = 'Booking not found or you cannot approve it.';
    redirect('bookings.php');
}

// Update booking status to confirmed
$query = "UPDATE bookings SET status = 'confirmed', updated_at = NOW() WHERE id = :booking_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':booking_id', $booking_id);

if ($stmt->execute()) {
    // Log the booking approval action
    logAction($user_id, 'approve_booking', 'bookings', $booking_id, 'pending', 'confirmed');
    
    $_SESSION['success'] = 'Booking approved successfully.';
} else {
    $_SESSION['error'] = 'Error approving booking. Please try again.';
}

redirect('bookings.php');
?>