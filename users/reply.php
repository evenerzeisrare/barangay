<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    header("Location: " . SITE_URL . "/login.php");
    exit();
}

$message_id = $_GET['message_id'] ?? 0;
$user_id = $_SESSION['user_id'];
$errors = [];

$conn = getDBConnection();

// Get original message
$sql = "SELECT m.*, u.username as sender_name 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE m.id = ? AND m.receiver_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $message_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$original_message = $result->fetch_assoc();
$stmt->close();

if (!$original_message) {
    header("Location: messages.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply_message = trim($_POST['message'] ?? '');
    
    if (empty($reply_message)) {
        $errors['message'] = "Message cannot be empty.";
    }

    if (empty($errors)) {
        $sql = "INSERT INTO messages (parent_id, sender_id, receiver_id, service_id, message) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iiiis",
            $message_id,
            $user_id,
            $original_message['sender_id'],
            $original_message['service_id'],
            $reply_message
        );

        if ($stmt->execute()) {
            header("Location: messages.php?action=sent");
            exit();
        } else {
            $errors['general'] = "Failed to send reply.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<section class="contact">
    <div class="container">
        <div class="section-title">
            <h2>Reply to Message</h2>
            <a href="messages.php" class="btn-outline">Back to Messages</a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?= $errors['general'] ?? 'Please correct the errors below.' ?>
            </div>
        <?php endif; ?>

        <div class="original-message mb-2">
            <h4>Original Message from <?= htmlspecialchars($original_message['sender_name']) ?></h4>
            <p><?= nl2br(htmlspecialchars($original_message['message'])) ?></p>
            <small>
                <i class="fas fa-clock"></i> 
                <?= date('M j, Y g:i A', strtotime($original_message['created_at'])) ?>
            </small>
        </div>

        <form method="POST" class="contact-form">
            <div class="form-group">
                <label>Your Reply</label>
                <textarea name="message" required></textarea>
                <?php if (isset($errors['message'])): ?>
                    <small class="text-danger"><?= $errors['message'] ?></small>
                <?php endif; ?>
            </div>
            <button type="submit" class="submit-btn">Send Reply</button>
        </form>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
