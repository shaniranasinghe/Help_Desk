<?php
session_start();
require_once '../model/includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Count unread messages for the user's tickets
$query = "SELECT COUNT(*) as count 
          FROM chat_messages cm
          JOIN tickets t ON cm.ticket_id = t.ticket_id
          WHERE t.user_id = ? 
          AND cm.sender_id != ? 
          AND cm.is_read = 0";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'count' => (int)$row['count']
]);

$stmt->close();
$conn->close();
