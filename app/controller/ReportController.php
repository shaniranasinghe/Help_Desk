<?php
include('../../model/ReportModel.php');
include('../../model/includes/config.php');
require(__DIR__ . '/../../libs/fpdf.php');


class ReportController {
    public function searchUserTickets($userID) {
        $reportModel = new ReportModel();
        return $reportModel->getUserTickets($userID);
    }

    public function generateUserReport() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
            $userID = intval($_POST['user_id']);
            $reportModel = new ReportModel();
            $tickets = $reportModel->getUserTickets($userID);
    
            if ($tickets->num_rows > 0) {
                $this->generatePDF($tickets, $userID);
            } else {
                header("Location: ../view/ticketListView.php?error=NoTicketsFound");
                exit();
            }
        }
    }
    

    private function generatePDF($tickets, $userID) {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(190, 10, "User Ticket Report (User ID: $userID)", 0, 1, 'C');
        $pdf->Ln(5);
    
        $headers = ['Ticket ID', 'Title', 'Description', 'Status', 'Priority', 'Submitted By', 'Current Company', 'Transfer History'];
        $widths = [15, 25, 40, 20, 20, 25, 25, 50];
    
        // Table Headers
        foreach ($headers as $index => $header) {
            $pdf->Cell($widths[$index], 10, $header, 1, 0, 'C');
        }
        $pdf->Ln();
    
        // Table Data
        while ($row = $tickets->fetch_assoc()) {
            $pdf->Cell($widths[0], 10, $row['ticket_id'], 1);
            $pdf->Cell($widths[1], 10, $row['ticket_title'], 1);
            $pdf->Cell($widths[2], 10, substr($row['ticket_description'], 0, 40), 1);
            $pdf->Cell($widths[3], 10, $row['ticket_status'], 1);
            $pdf->Cell($widths[4], 10, $row['priority'], 1);
            $pdf->Cell($widths[5], 10, $row['submitted_by'], 1);
            $pdf->Cell($widths[6], 10, $row['current_company'], 1);
            $pdf->MultiCell($widths[7], 10, nl2br($row['transfer_history']), 1);
        }
    
        $pdf->MultiCell($widths[7], 10, $row['transfer_history'], 1);

        exit;
    }
    
}
?>
