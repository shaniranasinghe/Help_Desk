<?php
include_once '../common/AdminlogHeader.php';

// Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['Acc_type'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Database connection
require_once('../../controller/TicketController.php');
$ticketController = new TicketController($conn);

// Get the feedback ID from URL
$feedbackId = $_GET['id'] ?? null;

if (!$feedbackId) {
    die("Feedback ID is missing");
}

// Delete the feedback from the database
$query = "DELETE FROM feedback WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $feedbackId);
$stmt->execute();

// Redirect back to the feedback list
header("Location: feedback.php");
exit();
?>
