<?php
include_once '../../model/includes/config.php';
require_once '../../model/TicketModel.php';

session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['Acc_type'] !== 'Support') {
    header("Location: ../auth/login.php");
    exit();
}

// Check if form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ticket_id'], $_POST['ticket_reply'])) {
    $ticket_id = intval($_POST['ticket_id']);
    $ticket_reply = trim($_POST['ticket_reply']);

    $ticketModel = new TicketModel($conn); 
    $result = $ticketModel->resolveTicket($ticket_id, $_SESSION['company_id'], $ticket_reply);

    if ($result) {
        header("Location: support_dashboard.php?success=Ticket+resolved+successfully");
    } else {
        header("Location: support_dashboard.php?error=Failed+to+resolve+ticket");
    }
}
?>
