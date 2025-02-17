<?php
require '../../model/includes/config.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticketId = $_POST['ticket_id'];
    $feedback = trim($_POST['feedback']);

    if (!empty($feedback)) {
        $stmt = $conn->prepare("UPDATE tickets SET feedback = ? WHERE ticket_id = ?");
        $stmt->bind_param("si", $feedback, $ticketId);
        if ($stmt->execute()) {
            header("Location: view_tickets.php?success=Feedback submitted successfully.");
            exit;
        } else {
            header("Location: view_tickets.php?error=Failed to submit feedback.");
            exit;
        }
    } else {
        header("Location: view_tickets.php?error=Feedback cannot be empty.");
        exit;
    }
}
?>
