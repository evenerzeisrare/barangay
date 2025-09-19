<?php
$page_title = "Edit Service";
require_once '../includes/header.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isSeller()) {
    redirect('../index.php');
}

if (!isset($_GET['id'])) {
    redirect('manage_services.php');
}

$user_id = $_SESSION['user_id'];
$service_id = $_GET['id'];
$error = '';
$success = '';

$database = new Database();
$db = $database->getConnection();

// Get service details
$query = "SELECT * FROM services WHERE id = :service_id AND seller_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':service_id', $service_id);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    $_SESSION['error'] = 'Service not found or you do not have permission to edit it.';
    redirect('manage_services.php');
}

$service = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $category = sanitizeInput($_POST['category']);
    $location = sanitizeInput($_POST['location']);
    $price = sanitizeInput($_POST['price']);
    $description = sanitizeInput($_POST['description']);
    $status = sanitizeInput($_POST['status']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Handle image upload
    $image = $service['image']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
    }
    
    $query = "UPDATE services SET title = :title, category = :category, location = :location, 
              price = :price, description = :description, image = :image, status = :status, 
              is_featured = :is_featured, updated_at = NOW() 
              WHERE id = :service_id AND seller_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':image', $image, PDO::PARAM_LOB);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':is_featured', $is_featured);
    $stmt->bindParam(':service_id', $service_id);
    $stmt->bindParam(':user_id', $user_id);
    
    if ($stmt->execute()) {
        // Log the service update action
        logAction($user_id, 'update_service', 'services', $service_id);
        
        $_SESSION['success'] = 'Service updated successfully.';
        redirect('manage_services.php');
    } else {
        $error = 'Error updating service. Please try again.';
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Edit Service</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Service Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo $service['title']; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="Food & Beverage" <?php echo $service['category'] === 'Food & Beverage' ? 'selected' : ''; ?>>Food & Beverage</option>
                                <option value="Personal Services" <?php echo $service['category'] === 'Personal Services' ? 'selected' : ''; ?>>Personal Services</option>
                                <option value="Education & Learning" <?php echo $service['category'] === 'Education & Learning' ? 'selected' : ''; ?>>Education & Learning</option>
                                <option value="Health & Fitness" <?php echo $service['category'] === 'Health & Fitness' ? 'selected' : ''; ?>>Health & Fitness</option>
                                <option value="Home & Maintenance" <?php echo $service['category'] === 'Home & Maintenance' ? 'selected' : ''; ?>>Home & Maintenance</option>
                                <option value="Transport & Delivery" <?php echo $service['category'] === 'Transport & Delivery' ? 'selected' : ''; ?>>Transport & Delivery</option>
                                <option value="Technology & Digital Services" <?php echo $service['category'] === 'Technology & Digital Services' ? 'selected' : ''; ?>>Technology & Digital Services</option>
                                <option value="Miscellaneous" <?php echo $service['category'] === 'Miscellaneous' ? 'selected' : ''; ?>>Miscellaneous</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo $service['location']; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="price" class="form-label">Price (₱)</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo $service['price']; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required><?php echo $service['description']; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?php echo $service['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $service['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Service Image</label>
                            <?php if ($service['image']): ?>
                                <div class="mb-2">
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($service['image']); ?>" class="img-thumbnail" style="max-width: 200px;" alt="Current Image">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">Leave empty to keep current image.</div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" <?php echo $service['is_featured'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_featured">Feature this service</label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Update Service</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>