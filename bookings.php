<?php
// Set the page title
$page_title = "My Bookings";

// Include the bootstrap file
require_once '../includes/bootstrap.php';

// Check if user is logged in and is a buyer
if (!isLoggedIn() || !isBuyer()) {
    redirect('../index.php');
}

$user_id = $_SESSION['user_id'];

$database = new Database();
$db = $database->getConnection();

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

// Include the header
require_once '../includes/header.php';
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
        <div class="card-header">
            <h5>Booking History</h5>
        </div>
        <div class="card-body">
            <?php if (count($bookings) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Seller</th>
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
                                    <td><?php echo $booking['seller_name']; ?></td>
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
                                        <a href="../service_detail.php?id=<?php echo $booking['service_id']; ?>" class="btn btn-sm btn-outline-primary">View Service</a>
                                        <?php if ($booking['status'] === 'pending'): ?>
                                            <a href="booking_action.php?action=cancel&id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-danger">Cancel</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <p>You haven't made any bookings yet.</p>
                    <a href="../search.php" class="btn btn-primary">Explore Services</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>