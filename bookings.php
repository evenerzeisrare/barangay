<?php
$page_title = "My Bookings";
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$user_id = $_SESSION['user_id'];

$database = new Database();
$db = $database->getConnection();

if (isBuyer()) {
    // Get buyer's bookings
    $query = "SELECT b.*, s.title as service_title, s.price, u.full_name as seller_name 
              FROM bookings b 
              JOIN services s ON b.service_id = s.id 
              JOIN users u ON s.seller_id = u.id 
              WHERE b.buyer_id = :user_id 
              ORDER BY b.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif (isSeller()) {
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
}
?>

<div class="container">
    <h1 class="mb-4">My Bookings</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <?php if (count($bookings) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <?php if (isBuyer()): ?>
                                    <th>Seller</th>
                                <?php else: ?>
                                    <th>Buyer</th>
                                <?php endif; ?>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo $booking['service_title']; ?></td>
                                    <td>
                                        <?php if (isBuyer()): ?>
                                            <?php echo $booking['seller_name']; ?>
                                        <?php else: ?>
                                            <?php echo $booking['buyer_name']; ?>
                                        <?php endif; ?>
                                    </td>
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
                                        <a href="service_detail.php?id=<?php echo $booking['service_id']; ?>" class="btn btn-sm btn-outline-primary">View Service</a>
                                        
                                        <?php if (isBuyer() && $booking['status'] === 'pending'): ?>
                                            <a href="buyer/booking_action.php?action=cancel&id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-danger">Cancel</a>
                                        <?php endif; ?>
                                        
                                        <?php if (isSeller() && $booking['status'] === 'pending'): ?>
                                            <a href="seller/approve_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-success">Approve</a>
                                            <a href="seller/cancel_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-danger">Reject</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <p>You don't have any bookings yet.</p>
                    <?php if (isBuyer()): ?>
                        <a href="search.php" class="btn btn-primary">Explore Services</a>
                    <?php else: ?>
                        <p>When buyers book your services, they will appear here.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>