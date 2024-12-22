<?php
ob_start();
require('../../libs/fpdf.php');
include('../model/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reportType = $_POST['report_type'];

    if ($reportType === 'tickets') {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(275, 10, 'Tickets Report', 0, 1, 'C');
        $headers = ['Ticket ID', 'User ID', 'Ticket Title', 'Ticket Description', 'Issue Type', 'Status', 'Current Company', 'Transfer History'];
        $widths = [15, 15, 30, 30, 30, 20, 20, 30];

        $query = "
            SELECT t.ticket_id, t.user_id, t.ticket_title, t.ticket_description, t.issue_type, t.ticket_status,
                   tt1.to_company_id AS current_company,
                   GROUP_CONCAT(CONCAT('From: ', tt.from_company_id, ' To: ', tt.to_company_id) SEPARATOR '\n') AS transfer_history
            FROM tickets t
            LEFT JOIN ticket_transfers tt1 ON t.ticket_id = tt1.ticket_id
            LEFT JOIN ticket_transfers tt ON t.ticket_id = tt.ticket_id
            GROUP BY t.ticket_id
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        $pdf->SetFillColor(200, 220, 255);
        foreach ($headers as $index => $header) {
            $pdf->Cell($widths[$index], 10, $header, 1, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 9);
        while ($row = $result->fetch_assoc()) {
            foreach ($row as $index => $column) {
                $columnWidth = isset($widths[array_search($index, array_keys($row))]) ? $widths[array_search($index, array_keys($row))] : 0;
                if ($index == 'transfer_history') {
                    $pdf->MultiCell($columnWidth, 10, $column, 1, 'C');
                } else {
                    $pdf->Cell($columnWidth, 10, $column, 1, 0, 'C');
                }
            }
            $pdf->Ln();
        }
        
        $pdf->Output('D', 'tickets_report.pdf');
        exit;
    }
}
ob_end_clean();
?>