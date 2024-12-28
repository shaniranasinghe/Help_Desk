<?php
session_start();
require_once '../model/includes/config.php';

// Check if user is logged in and is support team member
if (!isset($_SESSION['user_id']) || $_SESSION['Acc_type'] !== 'Support') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$ticketId = $data['ticket_id'] ?? null;
$status = $data['status'] ?? null;

if (!$ticketId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Update ticket status
$query = "UPDATE tickets SET ticket_status = ? WHERE ticket_id = ? AND assigned_to = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sii", $status, $ticketId, $_SESSION['user_id']);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or unauthorized']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
