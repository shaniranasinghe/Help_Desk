<?php
session_start();
require_once '../model/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$ticket_id = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;

// Update read status for messages where the user is not the sender
$query = "UPDATE chat_messages 
          SET is_read = 1 
          WHERE ticket_id = ? 
          AND sender_id != ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $ticket_id, $_SESSION['user_id']);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update read status']);
}

$stmt->close();
$conn->close();
