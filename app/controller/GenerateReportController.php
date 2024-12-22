<?php
require('../../libs/fpdf.php');
include('../model/includes/config.php');
include('../model/TicketModel.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reportType = $_POST['report_type'];
    $userId = isset($_POST['user_id']) ? $_POST['user_id'] : null;

    // Create a new PDF document
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);

    // Initialize query variable
    $query = "";

    // Report Type Logic
    if ($reportType === 'tickets') {
        $pdf->Cell(190, 10, 'Tickets Report', 0, 1, 'C');
        $headers = ['Ticket ID', 'User ID', 'Ticket Title', 'Ticket Description', 'Issue Type', 'Status', 'Current Company', 'Transfer History'];
        $widths = [15, 15, 30, 30, 30, 20, 20, 30];

        $query = "
            SELECT t.ticket_id, t.user_id, t.ticket_title, t.ticket_description, t.issue_type, t.ticket_status,
                   tt1.to_company_id AS current_company,
                   GROUP_CONCAT(CONCAT('From: ', tt.from_company_id, ' To: ', tt.to_company_id, ' At: ', tt.transferred_at) SEPARATOR '\n') AS transfer_history
            FROM tickets t
            LEFT JOIN ticket_transfers tt1 ON t.ticket_id = tt1.ticket_id
            LEFT JOIN ticket_transfers tt ON t.ticket_id = tt.ticket_id
            GROUP BY t.ticket_id
        ";
    } elseif ($reportType === 'users') {
        $pdf->Cell(190, 10, 'Users Report', 0, 1, 'C');
        $headers = ['User ID', 'User Name', 'Email', 'Acc_type', 'Company ID'];
        $widths = [20, 50, 60, 30, 30];
        $query = "SELECT id, user_name, email, Acc_type, company_id FROM users";
    } elseif ($reportType === 'companies') {
        $pdf->Cell(190, 10, 'Companies Report', 0, 1, 'C');
        $headers = ['Company ID', 'Name', 'Email', 'Type'];
        $widths = [40, 50, 50, 50];
        $query = "SELECT company_id, company_name, company_email, company_type FROM companies";
    } elseif ($reportType === 'user_tickets' && $userId !== null) {
        $pdf->Cell(190, 10, 'User Tickets Report', 0, 1, 'C');
        $headers = ['Ticket ID', 'Ticket Title', 'Ticket Description', 'Status', 'Ticket replies'];
        $widths = [20, 40, 40, 20, 60];
        $ticketModel = new TicketModel($conn);
        $tickets = $ticketModel->getTicketsWithRepliesByUserId($userId);

        $pdf->SetFillColor(200, 220, 255);  // Set header background color
        foreach ($headers as $index => $header) {
            $pdf->Cell($widths[$index], 10, $header, 1, 0, 'C', true);  // Header style
        }
        $pdf->Ln();

        // Render Table Content
        $pdf->SetFont('Arial', '', 9);
        foreach ($tickets as $ticket) {
            // Add ticket data
            $pdf->Cell($widths[0], 10, $ticket['ticket_id'], 1, 0, 'C');
            $pdf->Cell($widths[1], 10, $ticket['ticket_title'], 1, 0, 'C');
            $pdf->Cell($widths[2], 10, $ticket['ticket_description'], 1, 0, 'C');            
            $pdf->Cell($widths[3], 10, $ticket['ticket_status'], 1, 0, 'C');
           

            // Handle transfer history with MultiCell for overflow text
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell($widths[4], 10, implode("\n", array_map(function($reply) { return $reply['ticket_reply']; }, $ticket['replies'])), 1, 0, 'C');
            $pdf->Ln(); // New row
        }
        $pdf->Output('D', 'user_' . $userId . '_tickets_report.pdf');
        exit;
    } else {
        die("Invalid Report Type.");
    }

    if (!empty($query)) {
        $result = $conn->query($query);
        if (!$result) {
            die("Query failed: " . $conn->error);
        }

        // Render Table Headers
        $pdf->SetFillColor(200, 220, 255);
        foreach ($headers as $index => $header) {
            $pdf->Cell($widths[$index], 10, $header, 1, 0, 'C', true);
        }
        $pdf->Ln();

        // Render Table Content
        $pdf->SetFont('Arial', '', 9);
        while ($row = $result->fetch_assoc()) {
            foreach ($row as $index => $column) {
                if ($index == 'transfer_history') {
                    $pdf->MultiCell($widths[array_search($index, array_keys($row))], 10, $column, 1, 'C');
                } else {
                    $pdf->Cell($widths[array_search($index, array_keys($row))], 10, $column, 1, 0, 'C');
                }
            }
            $pdf->Ln();
        }
        $pdf->Output('D', $reportType . '_report.pdf');
        exit;
    }
}
?>