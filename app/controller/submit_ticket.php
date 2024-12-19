<?php
session_start();
require '../model/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $ticket_title = $conn->real_escape_string(trim($_POST['ticket_title']));
    $ticket_description = $conn->real_escape_string(trim($_POST['ticket_description']));
    $issue_type = $conn->real_escape_string($_POST['issue_type']);
    $company_id = $conn->real_escape_string($_POST['company_id']);
    $priority = $conn->real_escape_string($_POST['priority']);  // Capture priority field

    // Prepare SQL query to include the priority
    $query = "INSERT INTO tickets (ticket_title, ticket_description, user_id, issue_type, company_id, priority) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("SQL preparation error: " . $conn->error);
    }

    // Bind parameters, including the priority
    $stmt->bind_param("ssisss", $ticket_title, $ticket_description, $user_id, $issue_type, $company_id, $priority);

    // Execute query
    if ($stmt->execute()) {
        echo "Ticket raised successfully!";
        header("Location: ../view/tickets/ticket_confirmation.php");
        exit();
    } else {
        echo "Execution error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>
