<?php
// Set the page title
$page_title = "Manage Bookings";

// Include the bootstrap file
require_once '../includes/bootstrap.php';

// Check if user is logged in and is a seller
if (!isLoggedIn() || !isSeller()) {
    redirect('../index.php');
}

$user_id = $_SESSION['user_id'];

$database = new Database();
$db = $database->getConnection();

// Get seller's bookings
$query = "SELECT b.*, s.title as service_title, s.price, u.full_name as buyer_name 
          FROM bookings b 
          JOIN services s ON b.service_id = s.id 
          JOIN users u ON b.buyer_id = u.id 
          WHERE s.seller_id = :user_id 
          ORDER BY b.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include the header
require_once '../includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Manage Bookings</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h5>Booking Requests</h5>
        </div>
        <div class="card-body">
            <?php if (count($bookings) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Buyer</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Message</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo $booking['service_title']; ?></td>
                                    <td><?php echo $booking['buyer_name']; ?></td>
                                    <td>₱<?php echo number_format($booking['price'], 2); ?></td>
                                    <td>
                                        <span class="badge 
                                            <?php 
                                            switch ($booking['status']) {
                                                case 'pending': echo 'bg-warning'; break;
                                                case 'confirmed': echo 'bg-success'; break;
                                                case 'cancelled': echo 'bg-danger'; break;
                                                case 'completed': echo 'bg-info'; break;
                                                default: echo 'bg-secondary';
                                            }
                                            ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($booking['created_at'])); ?></td>
                                    <td>
                                        <?php if (!empty($booking['message'])): ?>
                                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="popover" title="Buyer Message" data-bs-content="<?php echo htmlspecialchars($booking['message']); ?>">
                                                View Message
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">No message</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($booking['status'] === 'pending'): ?>
                                            <a href="approve_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-success">Approve</a>
                                            <a href="cancel_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-danger">Reject</a>
                                        <?php elseif ($booking['status'] === 'confirmed'): ?>
                                            <a href="complete_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-info">Mark Complete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <p>You don't have any booking requests yet.</p>
                    <p>When buyers book your services, they will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Initialize popovers
document.addEventListener('DOMContentLoaded', function() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>