<?php
// Set the page title
$page_title = "Home";

// Include the bootstrap file
require_once 'includes/bootstrap.php';

// Include functions
require_once 'includes/functions.php';

$featured_services = getFeaturedServices();

// Include the header
require_once 'includes/header.php';
?>

<div class="hero-section bg-primary text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold">Connect with Local Services</h1>
                <p class="lead">BarangayLink helps you discover and book services from trusted sellers in your community.</p>
                <a href="search.php" class="btn btn-light btn-lg mt-3">Explore Services</a>
                <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-outline-light btn-lg mt-3 ms-2">Join Now</a>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <img src="images/community.svg" alt="Community Connection" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<div class="container">
    <h2 class="text-center mb-5">Featured Services</h2>
    
    <div class="row">
        <?php if (count($featured_services) > 0): ?>
            <?php foreach ($featured_services as $service): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 service-card">
                        <?php if (!empty($service['image'])): ?>
                            <img src="<?php echo htmlspecialchars($service['image']); ?>" class="card-img-top" alt="<?php echo $service['title']; ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <img src="images/service-placeholder.jpg" class="card-img-top" alt="Service Image" style="height: 200px; object-fit: cover;">
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
                            <a href="service_detail.php?id=<?php echo $service['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    No featured services available at the moment.
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="text-center mt-5">
        <a href="search.php" class="btn btn-outline-primary btn-lg">View All Services</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>