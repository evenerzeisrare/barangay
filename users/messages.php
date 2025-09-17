<?php
require_once '../includes/config.php';
require_once '../includes/header.php';
require_once '../includes/db_functions.php';


if (!isLoggedIn()) {
    header("Location: " . SITE_URL . "/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type']; // 'seller' or 'buyer'
$action = $_GET['action'] ?? 'inbox';
$message_id = $_GET['id'] ?? 0;
$errors = [];
$success = false;

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = intval($_POST['receiver_id'] ?? 0);
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : null;
    $message = trim($_POST['message'] ?? '');
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
    
    // Validation
    if (empty($message)) {
        $errors['message'] = 'Message is required';
    }
    
    if (empty($errors)) {
        $conn = getDBConnection();
        
        // For sellers: Verify they own the service being discussed
        if ($user_type === 'seller' && $service_id) {
            $check_sql = "SELECT user_id FROM services WHERE id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $service_id);
            $check_stmt->execute();
            $service_owner = $check_stmt->get_result()->fetch_assoc()['user_id'] ?? null;
            $check_stmt->close();
            
            if ($service_owner != $user_id) {
                $errors['general'] = "You can only reply to messages about your services";
            }
        }
        
        // For replies: Verify thread ownership
        if (empty($errors) && $parent_id) {
            $thread_sql = "SELECT * FROM messages WHERE id = ? AND (sender_id = ? OR receiver_id = ?)";
            $thread_stmt = $conn->prepare($thread_sql);
            $thread_stmt->bind_param("iii", $parent_id, $user_id, $user_id);
            $thread_stmt->execute();
            $parent_message = $thread_stmt->get_result()->fetch_assoc();
            $thread_stmt->close();
            
            if (!$parent_message) {
                $errors['general'] = 'Invalid message thread';
            } else {
                // Auto-set receiver for replies
                $receiver_id = ($parent_message['sender_id'] == $user_id) 
                    ? $parent_message['receiver_id'] 
                    : $parent_message['sender_id'];
            }
        }
        
        if (empty($errors)) {
            $sql = "INSERT INTO messages (parent_id, sender_id, receiver_id, service_id, message) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiiis", $parent_id, $user_id, $receiver_id, $service_id, $message);
            
            if ($stmt->execute()) {
                // Send email notification (optional)
                sendMessageNotification($receiver_id, $user_id, $message);
                
                header("Location: messages.php?action=" . ($action === 'sent' ? 'sent' : 'inbox'));
                exit();
            } else {
                $errors['general'] = 'Failed to send message. Please try again.';
            }
            $stmt->close();
        }
        $conn->close();
    }
}

// Get messages with proper filtering
$conn = getDBConnection();

if ($action == 'inbox') {
    $sql = "SELECT m.*, u.username as sender_name, s.title as service_title 
            FROM messages m 
            JOIN users u ON m.sender_id = u.id 
            LEFT JOIN services s ON m.service_id = s.id 
            WHERE m.receiver_id = ? 
            " . ($user_type === 'seller' ? "AND (s.user_id = ? OR s.user_id IS NULL)" : "") . "
            ORDER BY m.created_at DESC";
} elseif ($action == 'sent') {
    $sql = "SELECT m.*, u.username as receiver_name, s.title as service_title 
            FROM messages m 
            JOIN users u ON m.receiver_id = u.id 
            LEFT JOIN services s ON m.service_id = s.id 
            WHERE m.sender_id = ? 
            ORDER BY m.created_at DESC";
} elseif ($action == 'view' && $message_id) {
    // Mark as read if receiver
    $update_sql = "UPDATE messages SET is_read = TRUE WHERE id = ? AND receiver_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $message_id, $user_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    // Get full conversation thread with proper access control
    $sql = "WITH RECURSIVE message_thread AS (
                SELECT * FROM messages WHERE id = ?
                UNION ALL
                SELECT m.* FROM messages m JOIN message_thread mt ON m.parent_id = mt.id
            )
            SELECT m.*, 
                   u1.username as sender_name, 
                   u2.username as receiver_name,
                   s.title as service_title,
                   s.user_id as service_owner_id
            FROM message_thread m
            JOIN users u1 ON m.sender_id = u1.id
            JOIN users u2 ON m.receiver_id = u2.id
            LEFT JOIN services s ON m.service_id = s.id
            WHERE " . ($user_type === 'seller' ? "(s.user_id = ? OR s.user_id IS NULL)" : "1=1") . "
            ORDER BY m.created_at";
}

$stmt = $conn->prepare($sql);

if ($action == 'view' && $message_id) {
    if ($user_type === 'seller') {
        $stmt->bind_param("ii", $message_id, $user_id);
    } else {
        $stmt->bind_param("i", $message_id);
    }
} else {
    if ($action == 'inbox' && $user_type === 'seller') {
        $stmt->bind_param("ii", $user_id, $user_id);
    } else {
        $stmt->bind_param("i", $user_id);
    }
}

$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get unread count for badge
$unread_sql = "SELECT COUNT(*) as unread_count FROM messages 
               WHERE receiver_id = ? AND is_read = 0";
$unread_stmt = $conn->prepare($unread_sql);
$unread_stmt->bind_param("i", $user_id);
$unread_stmt->execute();
$unread_count = $unread_stmt->get_result()->fetch_assoc()['unread_count'] ?? 0;
$unread_stmt->close();

$conn->close();

// Set page title
$page_title = ucfirst($action) . ' Messages' . ($unread_count > 0 ? " ($unread_count)" : "");
if ($action == 'view' && isset($messages[0])) {
    $page_title = 'Conversation with ' . (
        $messages[0]['sender_id'] == $user_id 
            ? $messages[0]['receiver_name'] 
            : $messages[0]['sender_name']
    );
}

require_once '../includes/header.php';
?>

<section class="featured-listings">
    <div class="container">
        <div class="section-title">
            <h2><?php echo $page_title; ?></h2>
            
            <div class="message-actions">
                <a href="messages.php?action=inbox" class="btn <?php echo $action == 'inbox' ? 'active' : ''; ?>">
                    Inbox <?php echo $unread_count > 0 ? "<span class='badge'>$unread_count</span>" : ""; ?>
                </a>
                <a href="messages.php?action=sent" class="btn <?php echo $action == 'sent' ? 'active' : ''; ?>">Sent</a>
            </div>
        </div>
        
        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-danger">
                <p><?php echo htmlspecialchars($errors['general']); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <p>Message sent successfully!</p>
            </div>
        <?php endif; ?>
        
        <?php if ($action == 'view'): ?>
            <?php if (empty($messages)): ?>
                <div class="text-center py-2">
                    <p>Message not found or you don't have permission to view it.</p>
                    <a href="messages.php" class="btn">Back to Messages</a>
                </div>
            <?php else: ?>
                <div class="message-view">
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-bubble <?php echo $msg['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                            <div class="message-header">
                                <strong><?php echo htmlspecialchars($msg['sender_name']); ?></strong>
                                <small class="message-meta">
                                    <i class="fas fa-clock"></i> <?php echo date('M j, Y g:i A', strtotime($msg['created_at'])); ?>
                                    <?php if ($msg['service_id']): ?>
                                        | <i class="fas fa-tag"></i> <?php echo htmlspecialchars($msg['service_title']); ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <div class="message-content">
                                <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="message-reply mt-2">
                        <form action="messages.php?action=view&id=<?php echo $message_id; ?>" method="POST">
                            <input type="hidden" name="parent_id" value="<?php echo $message_id; ?>">
                            <input type="hidden" name="receiver_id" value="<?php 
                                echo $messages[0]['sender_id'] == $user_id 
                                    ? $messages[0]['receiver_id'] 
                                    : $messages[0]['sender_id']; 
                            ?>">
                            <?php if ($messages[0]['service_id']): ?>
                                <input type="hidden" name="service_id" value="<?php echo $messages[0]['service_id']; ?>">
                            <?php endif; ?>
                            <div class="form-group">
                                <textarea name="message" class="form-control" placeholder="Type your reply..." required></textarea>
                                <?php if (isset($errors['message'])): ?>
                                    <small class="text-danger"><?php echo $errors['message']; ?></small>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="submit-btn">Send Reply</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <?php if (empty($messages)): ?>
                <div class="text-center py-2">
                    <p>No messages found in your <?php echo $action; ?>.</p>
                </div>
            <?php else: ?>
                <div class="messages-list">
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-item <?php echo !$msg['is_read'] && $msg['receiver_id'] == $user_id ? 'unread' : ''; ?>">
                            <div class="message-summary">
                                <h4>
                                    <?php if ($action == 'inbox'): ?>
                                        From: <?php echo htmlspecialchars($msg['sender_name']); ?>
                                    <?php else: ?>
                                        To: <?php echo htmlspecialchars($msg['receiver_name']); ?>
                                    <?php endif; ?>
                                </h4>
                                <p class="message-preview">
                                    <?php echo htmlspecialchars(substr($msg['message'], 0, 100)); ?>...
                                </p>
                                <p class="message-meta">
                                    <i class="fas fa-clock"></i> <?php echo date('M j, Y g:i A', strtotime($msg['created_at'])); ?>
                                    <?php if ($msg['service_id']): ?>
                                        | <i class="fas fa-tag"></i> <?php echo htmlspecialchars($msg['service_title']); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="message-actions">
                                <a href="messages.php?action=view&id=<?php echo $msg['id']; ?>" class="btn">View</a>
                                <?php if ($action == 'inbox'): ?>
                                    <a href="messages.php?action=view&id=<?php echo $msg['id']; ?>#reply" class="btn btn-outline">Reply</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<style>
    .message-bubble {
        padding: 12px 15px;
        margin-bottom: 10px;
        border-radius: 8px;
        max-width: 70%;
    }
    .message-bubble.sent {
        background-color: #e3f2fd;
        margin-left: auto;
    }
    .message-bubble.received {
        background-color: #f1f1f1;
        margin-right: auto;
    }
    .message-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
    }
    .message-meta {
        color: #666;
        font-size: 0.8rem;
    }
    .badge {
        background-color: #e74c3c;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 0.8rem;
        margin-left: 5px;
    }
    .unread {
        border-left: 4px solid #3498db;
        background-color: #f8f9fa;
    }
    .message-item {
        padding: 15px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .message-summary {
        flex: 1;
    }
    .message-actions {
        margin-left: 15px;
    }
</style>
<?php
require_once '../includes/footer.php';
?>
