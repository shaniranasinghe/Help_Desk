<?php
ob_start();
include_once '../common/log_header.php';
require '../../controller/TicketController.php';

// Check if the ticket ID is provided via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id'])) {
    $ticketId = intval($_POST['ticket_id']);

    // Initialize the TicketController with the database connection
    $ticketController = new TicketController($conn);

    // Call a method to delete the ticket
    $success = $ticketController->deleteTicket($ticketId);

    if ($success) {
        // Redirect back to the tickets page with a success message
        header("Location: view_tickets.php?status=success");
        exit();
    } else {
        // Redirect back with an error message
        header("Location: view_tickets.php?status=error");
        exit();
    }
}
ob_end_flush();
?>
