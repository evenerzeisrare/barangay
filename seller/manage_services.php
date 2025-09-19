<?php
$page_title = "Manage Services";
require_once '../includes/header.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isSeller()) {
    redirect('../index.php');
}

$user_id = $_SESSION['user_id'];

$database = new Database();
$db = $database->getConnection();

// Get seller's services
$query = "SELECT * FROM services WHERE seller_id = :user_id ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Services</h1>
        <a href="add_service.php" class="btn btn-primary">Add New Service</a>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <?php if (count($services) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Featured</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($service['image'])): ?>
                                            <img src="../<?php echo htmlspecialchars($service['image']); ?>" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;" alt="<?php echo $service['title']; ?>">
                                        <?php else: ?>
                                            <img src="../images/service-placeholder.jpg" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;" alt="Service Image">
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $service['title']; ?></td>
                                    <td><?php echo $service['category']; ?></td>
                                    <td>₱<?php echo number_format($service['price'], 2); ?></td>
                                    <td>
                                        <span class="badge <?php echo $service['status'] === 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo ucfirst($service['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $service['is_featured'] ? 'bg-info' : 'bg-secondary'; ?>">
                                            <?php echo $service['is_featured'] ? 'Yes' : 'No'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../service_detail.php?id=<?php echo $service['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        <a href="edit_service.php?id=<?php echo $service['id']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        <a href="delete_service.php?id=<?php echo $service['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this service?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <p>You haven't added any services yet.</p>
                    <a href="add_service.php" class="btn btn-primary">Add Your First Service</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>