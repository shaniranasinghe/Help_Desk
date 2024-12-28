<?php
session_start();
require_once '../model/includes/config.php';

// Check if user is logged in and is a support member
if (!isset($_SESSION['user_id']) || ($_SESSION['Acc_type'] != 'Support' && $_SESSION['Acc_type'] != 'Admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$support_id = $_SESSION['user_id'];

// Count unread messages for tickets assigned to this support member
$query = "SELECT COUNT(*) as count 
          FROM chat_messages cm
          JOIN tickets t ON cm.ticket_id = t.ticket_id
          WHERE t.assigned_to = ? 
          AND cm.sender_id != ? 
          AND cm.is_read = 0";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $support_id, $support_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'count' => (int)$row['count']
]);

$stmt->close();
$conn->close();
