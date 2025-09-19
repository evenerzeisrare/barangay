<?php
$page_title = "Messages";
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$user_id = $_SESSION['user_id'];
$other_user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

$database = new Database();
$db = $database->getConnection();

// Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = sanitizeInput($_POST['receiver_id']);
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    $parent_id = isset($_POST['parent_id']) ? sanitizeInput($_POST['parent_id']) : null;
    
    $query = "INSERT INTO messages (sender_id, receiver_id, subject, message, parent_id) 
              VALUES (:sender_id, :receiver_id, :subject, :message, :parent_id)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':sender_id', $user_id);
    $stmt->bindParam(':receiver_id', $receiver_id);
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':message', $message);
    $stmt->bindParam(':parent_id', $parent_id);
    
    if ($stmt->execute()) {
        $message_id = $db->lastInsertId();
        
        // Log the message action
        logAction($user_id, 'send_message', 'messages', $message_id);
        
        $_SESSION['success'] = 'Message sent successfully.';
    } else {
        $_SESSION['error'] = 'Error sending message. Please try again.';
    }
}

// Get list of users the current user has conversed with
$query = "SELECT u.id, u.username, u.full_name, MAX(m.created_at) as last_message_date
          FROM users u
          JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
          WHERE (m.sender_id = :user_id OR m.receiver_id = :user_id) AND u.id != :user_id
          GROUP BY u.id
          ORDER BY last_message_date DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get messages if a conversation is selected
$messages = [];
if ($other_user_id) {
    // Mark messages as read
    $query = "UPDATE messages SET is_read = 1 WHERE receiver_id = :user_id AND sender_id = :other_user_id AND is_read = 0";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':other_user_id', $other_user_id);
    $stmt->execute();
    
    // Get the conversation
    $query = "SELECT m.*, u.username as sender_username, u.full_name as sender_name 
              FROM messages m 
              JOIN users u ON m.sender_id = u.id 
              WHERE (m.sender_id = :user_id AND m.receiver_id = :other_user_id) 
              OR (m.sender_id = :other_user_id AND m.receiver_id = :user_id) 
              ORDER BY m.created_at ASC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':other_user_id', $other_user_id);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get other user's info
    $query = "SELECT id, username, full_name FROM users WHERE id = :other_user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':other_user_id', $other_user_id);
    $stmt->execute();
    $other_user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="container">
    <h1 class="mb-4">Messages</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Conversations</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($conversations) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($conversations as $conversation): ?>
                                <a href="messaging.php?user_id=<?php echo $conversation['id']; ?>" class="list-group-item list-group-item-action <?php echo $other_user_id == $conversation['id'] ? 'active' : ''; ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo $conversation['full_name']; ?></h6>
                                        <small><?php echo date('M j', strtotime($conversation['last_message_date'])); ?></small>
                                    </div>
                                    <p class="mb-1">@<?php echo $conversation['username']; ?></p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-3 text-center">
                            <p>No conversations yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <?php if ($other_user_id): ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Conversation with <?php echo $other_user['full_name']; ?></h5>
                        <span>@<?php echo $other_user['username']; ?></span>
                    </div>
                    <div class="card-body message-container" style="max-height: 400px; overflow-y: auto;">
                        <?php if (count($messages) > 0): ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="message <?php echo $message['sender_id'] == $user_id ? 'message-sent' : 'message-received'; ?> mb-3">
                                    <div class="card <?php echo $message['sender_id'] == $user_id ? 'bg-primary text-white' : 'bg-light'; ?>">
                                        <div class="card-body">
                                            <?php if ($message['subject']): ?>
                                                <h6 class="card-title"><?php echo $message['subject']; ?></h6>
                                            <?php endif; ?>
                                            <p class="card-text"><?php echo nl2br($message['message']); ?></p>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?>
                                                by <?php echo $message['sender_name']; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <p>No messages yet. Start a conversation!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <form method="POST" action="">
                            <input type="hidden" name="receiver_id" value="<?php echo $other_user_id; ?>">
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject (Optional)</label>
                                <input type="text" class="form-control" id="subject" name="subject">
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <h5>Select a conversation</h5>
                        <p class="text-muted">Choose a conversation from the list to start messaging.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Scroll to bottom of message container
document.addEventListener('DOMContentLoaded', function() {
    const messageContainer = document.querySelector('.message-container');
    if (messageContainer) {
        messageContainer.scrollTop = messageContainer.scrollHeight;
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>