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
$supportMemberId = $_SESSION['user_id'];

if (!$ticketId) {
    echo json_encode(['success' => false, 'message' => 'Ticket ID is required']);
    exit;
}

// Update ticket assignment
$query = "UPDATE tickets SET assigned_to = ? WHERE ticket_id = ? AND (assigned_to IS NULL OR assigned_to = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $supportMemberId, $ticketId, $supportMemberId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ticket is already assigned to someone else']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
