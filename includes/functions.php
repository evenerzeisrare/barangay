<?php
// Include database configuration first
require_once 'database.php';

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function isBuyer() {
    return isLoggedIn() && getUserRole() === 'buyer';
}

function isSeller() {
    return isLoggedIn() && getUserRole() === 'seller';
}

function redirect($url) {
    // Use JavaScript redirect as a fallback
    if (!headers_sent()) {
        header("Location: $url");
        exit();
    } else {
        echo '<script>window.location.href = "' . $url . '";</script>';
        exit();
    }
}

function getFeaturedServices($limit = 6) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT s.*, u.full_name as seller_name 
              FROM services s 
              JOIN users u ON s.seller_id = u.id 
              WHERE s.is_featured = 1 AND s.status = 'active' 
              ORDER BY s.created_at DESC 
              LIMIT :limit";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function logAction($user_id, $action, $table_name, $record_id, $old_value = null, $new_value = null) {
    $database = new Database();
    $db = $database->getConnection();
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $query = "INSERT INTO audit_log (user_id, action, table_name, record_id, old_value, new_value, ip_address, user_agent) 
              VALUES (:user_id, :action, :table_name, :record_id, :old_value, :new_value, :ip_address, :user_agent)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':action', $action);
    $stmt->bindParam(':table_name', $table_name);
    $stmt->bindParam(':record_id', $record_id);
    $stmt->bindParam(':old_value', $old_value);
    $stmt->bindParam(':new_value', $new_value);
    $stmt->bindParam(':ip_address', $ip_address);
    $stmt->bindParam(':user_agent', $user_agent);
    
    return $stmt->execute();
}

// Additional helper functions
function getServiceById($service_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT s.*, u.full_name as seller_name 
              FROM services s 
              JOIN users u ON s.seller_id = u.id 
              WHERE s.id = :service_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':service_id', $service_id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getUserById($user_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id, username, full_name, role FROM users WHERE id = :user_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>