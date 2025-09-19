<?php
$page_title = "Service Details";
require_once 'includes/header.php';
require_once 'includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('search.php');
}

$service_id = $_GET['id'];

$database = new Database();
$db = $database->getConnection();

// Get service details
$query = "SELECT s.*, u.full_name as seller_name, u.id as seller_id 
          FROM services s 
          JOIN users u ON s.seller_id = u.id 
          WHERE s.id = :service_id AND s.status = 'active'";
$stmt = $db->prepare($query);
$stmt->bindParam(':service_id', $service_id);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Service not found.</div></div>';
    require_once 'includes/footer.php';
    exit();
}

$service = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn() && isBuyer()) {
    $user_id = $_SESSION['user_id'];
    $message = sanitizeInput($_POST['message']);
    
    // Check if user already has a pending booking for this service
    $query = "SELECT id FROM bookings WHERE buyer_id = :user_id AND service_id = :service_id AND status = 'pending'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':service_id', $service_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $error = 'You already have a pending booking for this service.';
    } else {
        // Create new booking
        $query = "INSERT INTO bookings (buyer_id, service_id, message) VALUES (:buyer_id, :service_id, :message)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':buyer_id', $user_id);
        $stmt->bindParam(':service_id', $service_id);
        $stmt->bindParam(':message', $message);
        
        if ($stmt->execute()) {
            $booking_id = $db->lastInsertId();
            
            // Log the booking action
            logAction($user_id, 'create_booking', 'bookings', $booking_id);
            
            $success = 'Booking request sent successfully. The seller will review your request.';
        } else {
            $error = 'Error creating booking. Please try again.';
        }
    }
}
?>

<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="search.php">Services</a></li>
            <li class="breadcrumb-item active"><?php echo $service['title']; ?></li>
        </ol>
    </nav>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <?php if ($service['image']): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($service['image']); ?>" class="card-img-top" alt="<?php echo $service['title']; ?>">
                <?php else: ?>
                    <img src="images/service-placeholder.jpg" class="card-img-top" alt="Service Image">
                <?php endif; ?>
                <div class="card-body">
                    <h1 class="card-title"><?php echo $service['title']; ?></h1>
                    <p class="card-text">
                        <span class="badge bg-primary"><?php echo $service['category']; ?></span>
                        <span class="badge bg-secondary"><?php echo $service['location']; ?></span>
                    </p>
                    <h3 class="text-primary">₱<?php echo number_format($service['price'], 2); ?></h3>
                    <p class="card-text">By: <a href="profile.php?id=<?php echo $service['seller_id']; ?>"><?php echo $service['seller_name']; ?></a></p>
                    <hr>
                    <h4>Description</h4>
                    <p class="card-text"><?php echo nl2br($service['description']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Book This Service</h5>
                </div>
                <div class="card-body">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isBuyer()): ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message to Seller (Optional)</label>
                                    <textarea class="form-control" id="message" name="message" rows="3" placeholder="Any special requests or questions..."></textarea>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Request Booking</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <p>You need a buyer account to book services.</p>
                                <a href="logout.php" class="btn btn-outline-primary">Switch Account</a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p>You need to be logged in to book this service.</p>
                            <a href="login.php" class="btn btn-primary">Login</a>
                            <a href="register.php" class="btn btn-outline-primary">Register</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5>Seller Information</h5>
                </div>
                <div class="card-body">
                    <h6><?php echo $service['seller_name']; ?></h6>
                    <p class="card-text">Location: <?php echo $service['location']; ?></p>
                    
                    <?php if (isLoggedIn()): ?>
                        <div class="d-grid">
                            <a href="messaging.php?user_id=<?php echo $service['seller_id']; ?>" class="btn btn-outline-primary">Message Seller</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>