<?php
session_start();
require_once '../model/includes/config.php';

// Check if user is logged in and is a support member
if (!isset($_SESSION['user_id']) || $_SESSION['Acc_type'] !== 'Support') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$support_id = $_SESSION['user_id'];

// Get latest messages for tickets assigned to this support member
$query = "SELECT 
            cm.*,
            t.ticket_title,
            t.ticket_id,
            DATE_FORMAT(cm.created_at, '%h:%i %p, %b %d') as formatted_time,
            CASE 
                WHEN cm.sender_id = t.assigned_to THEN 'You'
                ELSE 'User'
            END as sender_name,
            (CASE WHEN cm.is_read = 0 AND cm.sender_id != ? THEN 1 ELSE 0 END) as is_unread
          FROM chat_messages cm
          JOIN tickets t ON cm.ticket_id = t.ticket_id
          WHERE t.assigned_to = ?
          AND cm.id IN (
              SELECT MAX(id)
              FROM chat_messages
              GROUP BY ticket_id
          )
          ORDER BY cm.created_at DESC
          LIMIT 10";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $support_id, $support_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'ticket_id' => $row['ticket_id'],
        'ticket_title' => htmlspecialchars($row['ticket_title']),
        'message' => htmlspecialchars($row['message']),
        'sender_name' => $row['sender_name'],
        'created_at' => $row['formatted_time'],
        'is_read' => !$row['is_unread']
    ];
}

echo json_encode([
    'success' => true,
    'messages' => $messages
]);

$stmt->close();
$conn->close();
