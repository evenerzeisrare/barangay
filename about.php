<?php
// Set the page title
$page_title = "About";

// Include the bootstrap file
require_once 'includes/bootstrap.php';

// Include the header
require_once 'includes/header.php';
?>

<div class="container">
    <h1 class="text-center mb-5">About BarangayLink</h1>
    
    <div class="row mb-5">
        <div class="col-md-6">
            <h2>Our Mission</h2>
            <p>BarangayLink aims to strengthen local communities by connecting buyers with trusted service providers in their area. We believe in supporting local businesses and making it easier for people to find the services they need.</p>
        </div>
        <div class="col-md-6">
            <img src="images/community-support.svg" alt="Community Support" class="img-fluid">
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-md-6 order-md-2">
            <h2>How It Works</h2>
            <p>For buyers, BarangayLink provides an easy way to discover and book services from local providers. For sellers, it offers a platform to showcase their services and grow their business within the community.</p>
        </div>
        <div class="col-md-6 order-md-1">
            <img src="images/how-it-works.svg" alt="How It Works" class="img-fluid">
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <h2 class="text-center mb-4">Our Values</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Trust</h5>
                            <p class="card-text">We verify all sellers to ensure a trustworthy community.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-users fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Community</h5>
                            <p class="card-text">We believe in strengthening local communities through connections.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Security</h5>
                            <p class="card-text">We prioritize the security and privacy of our users' data.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>