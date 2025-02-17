<?php
include_once '../common/AdminlogHeader.php';

// Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['Acc_type'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Database connection
require_once('../../controller/TicketController.php');

if (!$conn) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle deletion
if (isset($_GET['id'])) {
    $feedbackId = intval($_GET['id']);

    $deleteQuery = "DELETE FROM tickets WHERE ticket_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $feedbackId);

    if ($stmt->execute()) {
        header("Location: feedback.php?message=Feedback deleted successfully");
        exit();
    } else {
        die("Failed to delete feedback: " . $stmt->error);
    }
} else {
    header("Location: feedback.php");
    exit();
}
?>
