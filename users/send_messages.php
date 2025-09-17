<?php
require_once '../includes/config.php'; // includes session_start()

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (!isset($_POST['receiver_id'], $_POST['service_id'], $_POST['message'])) {
        header("Location: ../services/view.php?id=" . ($_POST['service_id'] ?? 0) . "&error=missing");
        exit();
    }

    $receiver_id = (int) $_POST['receiver_id'];
    $service_id  = (int) $_POST['service_id'];
    $message     = trim($_POST['message']);
    $sender_id   = $_SESSION['user_id'] ?? null;

    if (!$sender_id) {
        header("Location: ../login.php?error=not_logged_in");
        exit();
    }

    if ($receiver_id === $sender_id) {
        header("Location: ../services/view.php?id={$service_id}&error=self_message");
        exit();
    }

    if ($message === '') {
        header("Location: ../services/view.php?id={$service_id}&error=empty_message");
        exit();
    }

    // Save message to DB
    if (saveMessage($sender_id, $receiver_id, $service_id, $message)) {
        // Optional: send notification
        sendMessageNotification($receiver_id, $sender_id, $message, $service_id);

        // ✅ Redirect back to the service page with a success flag
        header("Location: ../services/view.php?id={$service_id}&sent=1");
        exit();
    } else {
        header("Location: ../services/view.php?id={$service_id}&error=failed");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
