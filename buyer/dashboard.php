<?php
$page_title = "Buyer Dashboard";
require_once '../includes/header.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isBuyer()) {
    redirect('../index.php');
}

$user_id = $_SESSION['user_id'];

// Get user's recent bookings
$database = new Database();
$db = $database->getConnection();

$query = "SELECT b.*, s.title as service_title, s.price, u.full_name as seller_name 
          FROM bookings b 
          JOIN services s ON b.service_id = s.id 
          JOIN users u ON s.seller_id = u.id 
          WHERE b.buyer_id = :user_id 
          ORDER BY b.created_at DESC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recommended services
$query = "SELECT s.*, u.full_name as seller_name 
          FROM services s 
          JOIN users u ON s.seller_id = u.id 
          WHERE s.status = 'active' 
          ORDER BY RAND() 
          LIMIT 6";
$stmt = $db->prepare($query);
$stmt->execute();
$recommended_services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h1 class="mb-4">Buyer Dashboard</h1>
    
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
                                        <th>Seller</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_bookings as $booking): ?>
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
                                                <a href="../service_detail.php?id=<?php echo $booking['service_id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                <?php if ($booking['status'] === 'pending'): ?>
                                                    <a href="booking_action.php?action=cancel&id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-danger">Cancel</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="../bookings.php" class="btn btn-primary">View All Bookings</a>
                        </div>
                    <?php else: ?>
                        <p>You haven't made any bookings yet.</p>
                        <a href="../search.php" class="btn btn-primary">Explore Services</a>
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
    <a href="../search.php" class="btn btn-primary mb-2">Find Services</a>
    <a href="bookings.php" class="btn btn-outline-primary mb-2">My Bookings</a>
    <a href="../messaging.php" class="btn btn-outline-primary mb-2">Messages</a>
    <a href="../profile.php" class="btn btn-outline-primary">My Profile</a>
</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <h3 class="mb-4">Recommended Services</h3>
            <div class="row">
                <?php if (count($recommended_services) > 0): ?>
                    <?php foreach ($recommended_services as $service): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 service-card">
                                <?php if (!empty($service['image'])): ?>
                                    <img src="../<?php echo htmlspecialchars($service['image']); ?>" class="card-img-top" alt="<?php echo $service['title']; ?>">
                                <?php else: ?>
                                    <img src="../images/service-placeholder.jpg" class="card-img-top" alt="Service Image">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $service['title']; ?></h5>
                                    <p class="card-text">
                                        <span class="badge bg-primary"><?php echo $service['category']; ?></span>
                                        <span class="badge bg-secondary"><?php echo $service['location']; ?></span>
                                    </p>
                                    <p class="card-text">₱<?php echo number_format($service['price'], 2); ?></p>
                                    <p class="card-text">By: <?php echo $service['seller_name']; ?></p>
                                </div>
                                <div class="card-footer">
                                    <a href="../service_detail.php?id=<?php echo $service['id']; ?>" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            No services available at the moment.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>