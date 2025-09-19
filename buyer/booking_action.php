<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isBuyer()) {
    redirect('../index.php');
}

if (!isset($_GET['action']) || !isset($_GET['id'])) {
    redirect('../bookings.php');
}

$action = $_GET['action'];
$booking_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$database = new Database();
$db = $database->getConnection();

// Verify the booking belongs to the user
$query = "SELECT * FROM bookings WHERE id = :booking_id AND buyer_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':booking_id', $booking_id);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    $_SESSION['error'] = 'Booking not found or you do not have permission to modify it.';
    redirect('../bookings.php');
}

$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if ($action === 'cancel') {
    // Only allow cancelling pending bookings
    if ($booking['status'] === 'pending') {
        $query = "UPDATE bookings SET status = 'cancelled', updated_at = NOW() WHERE id = :booking_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':booking_id', $booking_id);
        
        if ($stmt->execute()) {
            // Log the cancellation action
            logAction($user_id, 'cancel_booking', 'bookings', $booking_id, 'pending', 'cancelled');
            
            $_SESSION['success'] = 'Booking cancelled successfully.';
        } else {
            $_SESSION['error'] = 'Error cancelling booking. Please try again.';
        }
    } else {
        $_SESSION['error'] = 'You can only cancel pending bookings.';
    }
} else {
    $_SESSION['error'] = 'Invalid action.';
}

redirect('../bookings.php');
?>