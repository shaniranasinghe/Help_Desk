<?php
include_once '../../model/includes/config.php';
require_once '../../model/TicketModel.php'; 
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['Acc_type'] !== 'Support') {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['transfer_ticket'])) {
    $ticket_id = intval($_POST['ticket_id']);
    $to_company_id = intval($_POST['to_company_id']);
    
    // Call the function from the TicketModel to transfer the ticket
    $ticketModel = new TicketModel($conn);
    $result = $ticketModel->transferTicket($ticket_id, $to_company_id);

    if ($result) {
        header("Location: support_dashboard.php?success=Ticket+transferred+successfully");
    } else {
        header("Location: support_dashboard.php?error=Failed+to+transfer+ticket");
    }
} else {
    header("Location: support_dashboard.php?error=Invalid+request");
}
?>
