<?php
require '../../model/includes/config.php'; 
require '../../model/TicketModel.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['company_id'])) {
    $companyId = $_POST['company_id'];

    // Create an instance of TicketModel
    $ticketModel = new TicketModel($conn);

    // Fetch support members for the given company
    $supportMembers = $ticketModel->getSupportMembersByCompany($companyId);

    // Prepare the data to send back as JSON
    $membersArray = [];
    while ($row = $supportMembers->fetch_assoc()) {
        $membersArray[] = $row;
    }

    // Return JSON-encoded data
    echo json_encode($membersArray);
}
?>
