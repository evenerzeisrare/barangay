<?php
$page_title = "Add Service";
require_once '../includes/header.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isSeller()) {
    redirect('../index.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $category = sanitizeInput($_POST['category']);
    $location = sanitizeInput($_POST['location']);
    $price = sanitizeInput($_POST['price']);
    $description = sanitizeInput($_POST['description']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "INSERT INTO services (seller_id, title, category, location, price, description, image, is_featured) 
              VALUES (:seller_id, :title, :category, :location, :price, :description, :image, :is_featured)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':seller_id', $user_id);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':image', $image, PDO::PARAM_LOB);
    $stmt->bindParam(':is_featured', $is_featured);
    
    if ($stmt->execute()) {
        $service_id = $db->lastInsertId();
        
        // Log the service creation action
        logAction($user_id, 'create_service', 'services', $service_id);
        
        $_SESSION['success'] = 'Service added successfully.';
        redirect('manage_services.php');
    } else {
        $error = 'Error adding service. Please try again.';
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Add New Service</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Service Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Food & Beverage">Food & Beverage</option>
                                <option value="Personal Services">Personal Services</option>
                                <option value="Education & Learning">Education & Learning</option>
                                <option value="Health & Fitness">Health & Fitness</option>
                                <option value="Home & Maintenance">Home & Maintenance</option>
                                <option value="Transport & Delivery">Transport & Delivery</option>
                                <option value="Technology & Digital Services">Technology & Digital Services</option>
                                <option value="Miscellaneous">Miscellaneous</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="price" class="form-label">Price (₱)</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Service Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured">
                            <label class="form-check-label" for="is_featured">Feature this service</label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Add Service</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>