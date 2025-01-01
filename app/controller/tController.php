<?php
ob_start();
require('../../libs/fpdf.php');
include('../model/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reportType = $_POST['report_type'];

    if ($reportType === 'tickets') {
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(275, 10, 'Tickets Report', 0, 1, 'C'); // Centered title
        $pdf->Ln(5);

        $headers = ['Ticket ID', 'User ID', 'Ticket Title', 'Ticket Description', 'Issue Type', 'Status', 'Current Company', 'Transfer History'];
        $widths = [20, 20, 40, 50, 30, 20, 40, 60];

        $query = "
            SELECT t.ticket_id, t.user_id, t.ticket_title, t.ticket_description, t.issue_type, t.ticket_status,
                c.company_name AS current_company,
                COALESCE(GROUP_CONCAT(CONCAT(c1.company_name, ' To: ', c2.company_name) SEPARATOR ', '), 'Not Transferred') AS transfer_history
            FROM tickets t
            LEFT JOIN ticket_transfers tt1 ON t.ticket_id = tt1.ticket_id
            LEFT JOIN ticket_transfers tt ON t.ticket_id = tt.ticket_id
            LEFT JOIN companies c ON t.company_id = c.company_id
            LEFT JOIN companies c1 ON tt.from_company_id = c1.company_id
            LEFT JOIN companies c2 ON tt.to_company_id = c2.company_id
            GROUP BY t.ticket_id

        ";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        // Function to add table headers
        function addTableHeaders($pdf, $headers, $widths) {
            $pdf->SetFont('Arial', 'B', 10); // Set bold for headers
            $pdf->SetFillColor(200, 220, 255);
            foreach ($headers as $index => $header) {
                $pdf->Cell($widths[$index], 10, $header, 1, 0, 'C', true);
            }
            $pdf->Ln();
        }

        addTableHeaders($pdf, $headers, $widths);

        $pdf->SetFont('Arial', '', 9); // Set normal font for content
        while ($row = $result->fetch_assoc()) {
            // Check if adding a row would overflow the page
            if ($pdf->GetY() + 10 > $pdf->GetPageHeight() - 20) { // Adjust for footer space
                $pdf->AddPage();
                addTableHeaders($pdf, $headers, $widths);
                $pdf->SetFont('Arial', '', 9); // Reset to normal font for content
            }

            $yStart = $pdf->GetY();

            // Output cells for each column
            $pdf->Cell($widths[0], 10, $row['ticket_id'], 1, 0, 'C');
            $pdf->Cell($widths[1], 10, $row['user_id'], 1, 0, 'C');
            $pdf->Cell($widths[2], 10, $row['ticket_title'], 1, 0, 'C');
            $pdf->Cell($widths[3], 10, $row['ticket_description'], 1, 0, 'C');
            $pdf->Cell($widths[4], 10, $row['issue_type'], 1, 0, 'C');
            $pdf->Cell($widths[5], 10, $row['ticket_status'], 1, 0, 'C');
            $pdf->Cell($widths[6], 10, $row['current_company'], 1, 0, 'C');

            // MultiCell for Transfer History
            $xStart = $pdf->GetX();
            $pdf->MultiCell($widths[7], 10, wordwrap($row['transfer_history'], 60, "\n"), 1, 'C');

            // Ensure correct row alignment after MultiCell
            $yEnd = $pdf->GetY();
            $pdf->SetY(max($yStart + 10, $yEnd)); // Align Y for the next row
        }

        $pdf->Output('D', 'tickets_report.pdf');
        exit;
    }
}
ob_end_clean();
?>
