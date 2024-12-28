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

// Get latest message for each ticket
$query = "SELECT 
            cm.*, 
            t.ticket_title,
            t.ticket_id,
            CASE 
                WHEN cm.sender_id = t.assigned_to THEN 'Support'
                ELSE 'You'
            END as sender_name,
            DATE_FORMAT(cm.created_at, '%M %d, %Y %h:%i %p') as formatted_time
          FROM chat_messages cm
          JOIN tickets t ON cm.ticket_id = t.ticket_id
          WHERE t.user_id = ?
          AND cm.id IN (
              SELECT MAX(id)
              FROM chat_messages
              GROUP BY ticket_id
          )
          ORDER BY cm.created_at DESC
          LIMIT 10";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
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
        'is_read' => $row['is_read']
    ];
}

echo json_encode([
    'success' => true,
    'messages' => $messages
]);

$stmt->close();
$conn->close();
