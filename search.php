<?php
$page_title = "Search Services";
// Start session first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/header.php';
require_once 'includes/functions.php';

$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$location = isset($_GET['location']) ? sanitizeInput($_GET['location']) : '';

$database = new Database();
$db = $database->getConnection();

// Build query based on filters
$query = "SELECT s.*, u.full_name as seller_name 
          FROM services s 
          JOIN users u ON s.seller_id = u.id 
          WHERE s.status = 'active'";
$params = [];

if (!empty($search)) {
    $query .= " AND (s.title LIKE :search OR s.description LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($category)) {
    $query .= " AND s.category = :category";
    $params[':category'] = $category;
}

if (!empty($location)) {
    $query .= " AND s.location LIKE :location";
    $params[':location'] = "%$location%";
}

$query .= " ORDER BY s.created_at DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all categories for filter dropdown
$query = "SELECT DISTINCT category FROM services WHERE status = 'active' ORDER BY category";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="container">
    <h1 class="mb-4">Search Services</h1>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" value="<?php echo $search; ?>" placeholder="Search by title or description">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>" <?php echo $category === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo $location; ?>" placeholder="Enter location">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="row">
        <?php if (count($services) > 0): ?>
            <?php foreach ($services as $service): ?>
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
                            <p class="card-text"><?php echo substr($service['description'], 0, 100); ?>...</p>
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
                    No services found matching your criteria.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>