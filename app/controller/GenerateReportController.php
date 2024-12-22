<?php
// Start output buffering to prevent any unwanted output before generating the PDF
ob_start();

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
        $headers = ['Ticket ID', 'Ticket Title', 'Ticket Description', 'Status', 'Priority' , 'Submitted by', 'Current company', 'Transfer', 'Ticket replies'];
        $widths = [20, 20, 20, 20, 20, 20, 20, 20, 20];
        $query = "
            SELECT 
            t.ticket_id, t.ticket_title, t.ticket_description, t.ticket_status, t.priority, 
            u.user_name AS submitted_by, 
            c.company_name AS current_company, 
            tt.from_company_id, tt.to_company_id, tt.transferred_at,
            GROUP_CONCAT(CONCAT('From: ', tt.from_company_id, ' To: ', tt.to_company_id, ' At: ', tt.transferred_at) SEPARATOR '\n') AS transfer_history,
            GROUP_CONCAT(CONCAT(tr.ticket_reply, ' (', tr.replied_at, ')') SEPARATOR '\n') AS ticket_replies
        FROM tickets t 
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN companies c ON t.company_id = c.company_id
        LEFT JOIN ticket_transfers tt ON t.ticket_id = tt.ticket_id
        LEFT JOIN ticket_replies tr ON t.ticket_id = tr.ticket_id 
        WHERE t.user_id = ? 
        GROUP BY t.ticket_id
        ORDER BY t.created_at DESC, tr.replied_at ASC
        ";
        

    } else {
        die("Invalid Report Type.");
    }

    if (!empty($query)) {
        // Prepare the statement
        if ($reportType === 'user_tickets' && $userId !== null) {
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }
            $stmt->bind_param('i', $userId);  // Bind the userId as an integer parameter
        } else {
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }
        }

        // Execute the query
        $stmt->execute();
        $result = $stmt->get_result();
        
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
            foreach ($headers as $index => $header) {
                $columnKey = strtolower(str_replace(' ', '_', $header));
                $column = isset($row[$columnKey]) ? $row[$columnKey] : '';
                $pdf->Cell($widths[$index], 10, $column, 1, 0, 'C');
            }
            $pdf->Ln();
        }

// Output PDF
ob_end_clean(); // Ensure no unexpected output before sending the PDF
$pdf->Output('D', $reportType . '_report.pdf');
exit;
    }
}

// End output buffering to ensure no data is output before PDF generation
ob_end_clean();
?>