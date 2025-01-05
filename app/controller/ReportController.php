<?php
include('../../model/ReportModel.php');
include('../../model/includes/config.php');


class ReportController {
    public function searchUserTickets($ticketID) {
        global $conn; // Ensure $conn is available
        $reportModel = new ReportModel($conn);
        return $reportModel->getFilteredTickets($ticketID);
    }
}
