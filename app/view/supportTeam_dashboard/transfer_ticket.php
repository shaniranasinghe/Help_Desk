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
    $transfer_type = $_POST['transfer_type'] ?? null; // Get the transfer type

    // Check for valid transfer type
    if ($transfer_type === 'external' && isset($_POST['to_company_id'])) {
        // External transfer
        $to_company_id = intval($_POST['to_company_id']);
        $ticketModel = new TicketModel($conn);
        $result = $ticketModel->transferTicket($ticket_id, $to_company_id);

        if ($result) {
            echo "<script>alert('Ticket transferred to another company successfully');</script>";
        } else {
            echo "<script>alert('Failed to transfer ticket to another company');</script>";
        }
    } elseif ($transfer_type === 'internal' && isset($_POST['to_assigned_to'])) {
        // Internal transfer
        $to_assigned_to = intval($_POST['to_assigned_to']);
        $ticketModel = new TicketModel($conn);
        $result = $ticketModel->assignTicketToSupportMember($ticket_id, $to_assigned_to);

        if ($result) {
            echo "<script>alert('Ticket assigned to a support member successfully');</script>";
        } else {
            echo "<script>alert('Failed to assign ticket to a support member');</script>";
        }
    } else {
        // Invalid transfer request
        echo "<script>alert('Invalid transfer type or missing parameters');</script>";
    }

    // Redirect to the dashboard after processing
    echo "<script>window.location.href='support_dashboard.php';</script>";
    exit();
} else {
    header("Location: support_dashboard.php?error=Invalid+request");
    exit();
}

