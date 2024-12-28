<?php
session_start();
require_once '../model/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$ticket_id = $data['ticket_id'] ?? 0;
$message = $data['message'] ?? '';

// Get user_id from the ticket
$query = "SELECT user_id FROM tickets WHERE ticket_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();
$ticket = $result->fetch_assoc();
$user_id = $ticket['user_id'];

if (!$ticket_id || !$message) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Insert message with user_id
$query = "INSERT INTO chat_messages (ticket_id, sender_id, user_id, message) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiis", $ticket_id, $_SESSION['user_id'], $user_id, $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
