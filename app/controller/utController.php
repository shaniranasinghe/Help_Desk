<?php 
ob_start();
require('../../libs/fpdf.php');
include('../model/includes/config.php');
require_once('../model/ReportModel.php');

// Handle download report request
if (isset($_POST['download_report']) && $_POST['report_type'] === 'user_tickets') {
    // Extend FPDF for custom Header and Footer
    class PDF extends FPDF {
        // Header
        function Header() {
            $this->SetFont('Arial', 'B', 14);
            $this->SetTextColor(0, 0, 0); // Black text
            $this->Cell(0, 10, 'User Tickets Report', 0, 1, 'C');
            $this->Ln(5);
        }

        // Footer
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }
    }

    // Initialize PDF
    $pdf = new PDF('L', 'mm', 'A4'); // A4 Landscape
    $pdf->AddPage();

    // Filters
    $filters = [
        'user_id' => $_POST['user_id'] ?? '',
        'priority' => $_POST['priority'] ?? 'all',
        'status' => $_POST['status'] ?? 'all',
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'company' => $_POST['company'] ?? ''
    ];

    $ticketModel = new ReportModel();
    $filteredTickets = $ticketModel->getFilteredTickets($filters);

    if (!empty($filteredTickets)) {
        // Table Header with Styling
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(200, 220, 255); // Light blue background
        $pdf->SetTextColor(0, 0, 0); // Black text

        $headers = [
            ['label' => 'Ticket ID', 'width' => 17],
            ['label' => 'User ID', 'width' => 17],
            ['label' => 'Submitted By', 'width' => 25],
            ['label' => 'Ticket Title', 'width' => 25],
            ['label' => 'Ticket Description', 'width' => 30],
            ['label' => 'Status', 'width' => 20],
            ['label' => 'Priority', 'width' => 20],
            ['label' => 'Current Company', 'width' => 30],
            ['label' => 'Transfer History', 'width' => 50],
            ['label' => 'Replies', 'width' => 50]
        ];

        foreach ($headers as $header) {
            $pdf->Cell($header['width'], 10, $header['label'], 1, 0, 'C', true); // Fill color applied
        }
        $pdf->Ln();

        // Table Rows
        $pdf->SetFont('Arial', '', 8);
        foreach ($filteredTickets as $ticket) {
            $pdf->Cell(17, 10, '#' . $ticket['ticket_id'], 1);
            $pdf->Cell(17, 10, $ticket['user_id'], 1);
            $pdf->Cell(25, 10, $ticket['submitted_by'], 1);
            $pdf->Cell(25, 10, $ticket['ticket_title'], 1);
            $pdf->Cell(30, 10, $ticket['ticket_description'], 1);
            $pdf->Cell(20, 10, $ticket['ticket_status'], 1);
            $pdf->Cell(20, 10, $ticket['priority'], 1);
            $pdf->Cell(30, 10, $ticket['current_company'], 1);
            
            // Display Transfer History
            $transferText = '';
            if (!empty($ticket['transfers'])) {
                foreach ($ticket['transfers'] as $transfer) {
                    $transferText .= $transfer['from_company_name'] . ' - To: ' . $transfer['to_company_name'];
                }
            } else {
                $transferText = 'No transfers';
            }
            $pdf->Cell(50, 10, $transferText, 1);

            // Display Replies with controlled line breaks
            $replyText = '';
            if (!empty($ticket['replies'])) {
                foreach ($ticket['replies'] as $reply) {
                    $replyText .= $reply['ticket_reply'];
                }
            } else {
                $replyText = 'No replies';
            }

            // Display the replies within a controlled MultiCell
            $pdf->Cell(50, 10, $replyText, 1);

            $pdf->Ln(); // Line break after each ticket row
        }

        // Output the PDF
        $pdf->Output('D', 'filtered_tickets_report.pdf');
        exit();
    } else {
        // Redirect back with an error message if no data
        header("Location: ../view/ticketListView.php?error=No tickets found for the applied filters");
        exit();
    }
}
ob_end_clean();
?>
