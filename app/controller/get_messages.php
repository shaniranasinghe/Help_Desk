<?php
session_start();
require_once '../model/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$ticket_id = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

$query = "SELECT m.*, 
          CASE 
              WHEN m.sender_id = ? THEN 'support'
              ELSE 'client'
          END as sender_type
          FROM chat_messages m
          WHERE m.ticket_id = ? AND m.id > ?
          ORDER BY m.created_at ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $_SESSION['user_id'], $ticket_id, $last_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'id' => $row['id'],
        'message' => htmlspecialchars($row['message']),
        'sender_type' => $row['sender_type'],
        'created_at' => date('M j, Y g:i A', strtotime($row['created_at']))
    ];
}

echo json_encode(['success' => true, 'messages' => $messages]);
