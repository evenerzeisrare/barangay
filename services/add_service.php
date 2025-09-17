<?php
session_start();
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';

// Check if user is logged in and is a seller
checkRole(['seller']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_name = trim($_POST['service_name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $schedule = trim($_POST['schedule']);
    
    // Validate inputs
    if (empty($service_name) || empty($description) || empty($price) || empty($schedule)) {
        $error = 'All fields are required.';
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = 'Price must be a valid number greater than 0.';
    } else {
        try {
            // Handle image upload
            $service_image = null;
            if (isset($_FILES['service_image']) && $_FILES['service_image']['error'] === UPLOAD_ERR_OK) {
                // Check if file is an image
                $file_info = getimagesize($_FILES['service_image']['tmp_name']);
                if ($file_info !== false) {
                    $service_image = file_get_contents($_FILES['service_image']['tmp_name']);
                } else {
                    $error = 'Uploaded file is not a valid image.';
                }
            }
            
            if (!$error) {
                // Insert service into database
                $stmt = $pdo->prepare("INSERT INTO services (seller_id, service_name, description, price, schedule, service_image) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $service_name, $description, $price, $schedule, $service_image]);
                
                $success = 'Service added successfully!';
                header('Location: ../dashboard_seller.php?success=Service added successfully');
                exit();
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Service - Service Booking System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h1>Service Booking System</h1>
            </div>
            <ul class="nav-links">
                <li><a href="../dashboard_seller.php">Dashboard</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="dashboard">
        <div class="dashboard-header">
            <h2>Add New Service</h2>
            <p>Create a new service offering for buyers to book.</p>
        </div>

        <div class="dashboard-content">
            <div class="dashboard-section">
                <?php if ($error): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="service_name">Service Name</label>
                        <input type="text" id="service_name" name="service_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price ($)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="schedule">Available Schedule</label>
                        <textarea id="schedule" name="schedule" rows="3" required placeholder="e.g., Monday-Friday: 9am-5pm, Saturday: 10am-2pm"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="service_image">Service Image</label>
                        <input type="file" id="service_image" name="service_image" accept="image/*">
                    </div>
                    
                    <button type="submit" class="btn-primary">Add Service</button>
                    <a href="../dashboard_seller.php" class="btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
