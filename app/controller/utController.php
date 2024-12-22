<?php
ob_start();
require('../../libs/fpdf.php');
include('../model/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reportType = $_POST['report_type'];
    $userId = isset($_POST['user_id']) ? $_POST['user_id'] : null;

    if ($reportType === 'user_tickets' && $userId !== null) {
        $pdf = new FPDF('L', 'mm', 'A4'); // Landscape mode for wider tables
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(275, 10, 'User Tickets Report', 0, 1, 'C');

        // Headers and column widths
        $headers = [
            'Ticket ID', 'Ticket Title', 'Ticket Description', 'Status', 
            'Priority', 'Submitted by', 'Current Company', 'Transfer', 'Ticket Replies'
        ];
        $widths = [15, 30, 30, 20, 20, 30, 30, 30, 50]; // Column widths

        // Map headers to database columns
        $columnMapping = [
            'Ticket ID' => 'ticket_id',
            'Ticket Title' => 'ticket_title',
            'Ticket Description' => 'ticket_description',
            'Status' => 'ticket_status',
            'Priority' => 'priority',
            'Submitted by' => 'submitted_by',
            'Current Company' => 'current_company',
            'Transfer' => 'transfer_history',
            'Ticket Replies' => 'ticket_replies',
        ];

        // Query to fetch ticket data
        $query = "
            SELECT 
                t.ticket_id, t.ticket_title, t.ticket_description, t.ticket_status, t.priority, 
                u.user_name AS submitted_by, 
                c.company_name AS current_company, 
                GROUP_CONCAT(CONCAT('From: ', tt.from_company_id, ' To: ', tt.to_company_id) SEPARATOR '\n') AS transfer_history,
                GROUP_CONCAT(CONCAT(tr.ticket_reply) SEPARATOR '\n') AS ticket_replies
            FROM tickets t 
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN companies c ON t.company_id = c.company_id
            LEFT JOIN ticket_transfers tt ON t.ticket_id = tt.ticket_id
            LEFT JOIN ticket_replies tr ON t.ticket_id = tr.ticket_id 
            WHERE t.user_id = ? 
            GROUP BY t.ticket_id
            ORDER BY t.created_at DESC, tr.replied_at ASC
        ";

        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        // Add headers to the PDF
        $pdf->SetFillColor(200, 220, 255);
        foreach ($headers as $index => $header) {
            $pdf->Cell($widths[$index], 12, $header, 1, 0, 'C', true); // Increased height to 12
        }
        $pdf->Ln();

        // Add data rows
        $pdf->SetFont('Arial', '', 8);
        while ($row = $result->fetch_assoc()) {
            foreach ($headers as $index => $header) {
                $columnKey = $columnMapping[$header];
                $column = isset($row[$columnKey]) ? $row[$columnKey] : '';

                // If no transfer history, display "Not Transferred"
                if ($header === 'Transfer') {
                    if (empty($column)) {
                        $column = 'Not Transferred'; // Default text if no transfer history
                    }
                    $x = $pdf->GetX();
                    $y = $pdf->GetY();
                    $pdf->MultiCell($widths[$index], 12, $column, 1, 'C'); // Increased row height to 12
                    $pdf->SetXY($x + $widths[$index], $y); // Reset X position for the next cell
                }
                // If no ticket replies, display "No Replies"
                else if ($header === 'Ticket Replies') {
                    if (empty($column)) {
                        $column = 'No Replies'; // Default text if no ticket replies
                    }
                    $x = $pdf->GetX();
                    $y = $pdf->GetY();
                    $pdf->MultiCell($widths[$index], 12, $column, 1, 'C'); // Increased row height to 12
                    $pdf->SetXY($x + $widths[$index], $y); // Reset X position for the next cell
                }
                // For other columns, just display normally
                else {
                    $pdf->Cell($widths[$index], 12, $column, 1, 0, 'C'); // Increased row height to 12
                }
            }
            $pdf->Ln();
        }

        // Output the PDF
        $pdf->Output('D', 'user_tickets_report.pdf');
        exit;
    }
}
ob_end_clean();
?>
