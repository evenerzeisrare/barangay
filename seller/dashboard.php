<?php
// Set the page title
$page_title = "Seller Dashboard";

// Include the bootstrap file
require_once '../includes/bootstrap.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isSeller()) {
    redirect('../index.php');
}

$user_id = $_SESSION['user_id'];

// Get seller's stats
$database = new Database();
$db = $database->getConnection();

// Total services
$query = "SELECT COUNT(*) as total_services FROM services WHERE seller_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$total_services = $stmt->fetch(PDO::FETCH_ASSOC)['total_services'];

// Active services
$query = "SELECT COUNT(*) as active_services FROM services WHERE seller_id = :user_id AND status = 'active'";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$active_services = $stmt->fetch(PDO::FETCH_ASSOC)['active_services'];

// Total bookings
$query = "SELECT COUNT(*) as total_bookings 
          FROM bookings b 
          JOIN services s ON b.service_id = s.id 
          WHERE s.seller_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$total_bookings = $stmt->fetch(PDO::FETCH_ASSOC)['total_bookings'];

// Pending bookings
$query = "SELECT COUNT(*) as pending_bookings 
          FROM bookings b 
          JOIN services s ON b.service_id = s.id 
          WHERE s.seller_id = :user_id AND b.status = 'pending'";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$pending_bookings = $stmt->fetch(PDO::FETCH_ASSOC)['pending_bookings'];

// Recent bookings
$query = "SELECT b.*, s.title as service_title, u.full_name as buyer_name 
          FROM bookings b 
          JOIN services s ON b.service_id = s.id 
          JOIN users u ON b.buyer_id = u.id 
          WHERE s.seller_id = :user_id 
          ORDER BY b.created_at DESC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include the header
require_once '../includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Seller Dashboard</h1>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $total_services; ?></h5>
                    <p class="card-text">Total Services</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $active_services; ?></h5>
                    <p class="card-text">Active Services</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $total_bookings; ?></h5>
                    <p class="card-text">Total Bookings</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $pending_bookings; ?></h5>
                    <p class="card-text">Pending Bookings</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Recent Bookings</h5>
                </div>
                <div class="card-body">
                    <?php if (count($recent_bookings) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th>Buyer</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                        <tr>
                                            <td><?php echo $booking['service_title']; ?></td>
                                            <td><?php echo $booking['buyer_name']; ?></td>
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
                                                <?php if ($booking['status'] === 'pending'): ?>
                                                    <a href="approve_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-success">Approve</a>
                                                    <a href="cancel_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-danger">Reject</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="bookings.php" class="btn btn-primary">View All Bookings</a>
                        </div>
                    <?php else: ?>
                        <p>You don't have any bookings yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                   <div class="d-grid gap-2">
    <a href="add_service.php" class="btn btn-primary mb-2">Add New Service</a>
    <a href="manage_services.php" class="btn btn-outline-primary mb-2">Manage Services</a>
    <a href="bookings.php" class="btn btn-outline-primary mb-2">Manage Bookings</a>
    <a href="../messaging.php" class="btn btn-outline-primary mb-2">Messages</a>
    <a href="../profile.php" class="btn btn-outline-primary">My Profile</a>
</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>