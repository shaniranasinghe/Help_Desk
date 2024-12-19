<?php
require('../../libs/fpdf.php');
include('../model/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reportType = $_POST['report_type'];

    // Create a new PDF document
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);  // Adjusted font size

    // Report Type Logic
    if ($reportType === 'tickets') {
        // Title
        $pdf->Cell(190, 10, 'Tickets Report', 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Ln(10);

        // Table Headers
        $headers = ['Ticket ID', 'User ID', 'Ticket Title', 'Ticket Description', 'Issue Type', 'Status', 'Current Company', 'Transfer History'];
        $widths = [15, 15, 30, 30, 30, 20, 20, 30]; // Increased width for transfer history


    } elseif ($reportType === 'users') {
        // Title
        $pdf->Cell(190, 10, 'Users Report', 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Ln(10);

        // Table Headers
        $headers = ['User ID', 'User Name', 'Email', 'Acc_type', 'Company ID'];
        $widths = [20, 50, 60, 30, 30];

    } elseif ($reportType === 'companies') {
        // Title
        $pdf->Cell(190, 10, 'Companies Report', 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Ln(10);

        // Table Headers
        $headers = ['Company ID', 'Name', 'Email', 'Type'];
        $widths = [40, 50, 50, 50];

    } else {
        die("Invalid Report Type.");
    }

    // Render Table Headers
    $pdf->SetFillColor(200, 220, 255); // Light blue background
    foreach ($headers as $index => $header) {
        $pdf->Cell($widths[$index], 10, $header, 1, 0, 'C', true);
    }
    $pdf->Ln();

    // Fetch Data
    if ($reportType === 'tickets') {
        // Query to fetch ticket data, including the current company and transfer history
        $query = "
            SELECT t.ticket_id, t.user_id, t.ticket_title, t.ticket_description, t.issue_type, t.ticket_status,
                   tt1.to_company_id AS current_company,
                   GROUP_CONCAT(CONCAT('From: ', tt.from_company_id, ' \nTo: ', tt.to_company_id, ' \nAt: ', tt.transferred_at) ORDER BY tt.transferred_at SEPARATOR '\n') AS transfer_history
            FROM tickets t
            LEFT JOIN ticket_transfers tt1 ON t.ticket_id = tt1.ticket_id
            LEFT JOIN ticket_transfers tt ON t.ticket_id = tt.ticket_id
            GROUP BY t.ticket_id
        ";
    } elseif ($reportType === 'users') {
        $query = "SELECT id, user_name, email, Acc_type, company_id FROM users";
    } elseif ($reportType === 'companies') {
        $query = "SELECT company_id, company_name, company_email, company_type FROM companies";
    }

    $result = $conn->query($query);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    // Render Table Content
    $pdf->SetFont('Arial', '', 9);  // Adjusted font size for content
    while ($row = $result->fetch_assoc()) {
        $i = 0; // Counter to access column widths
        foreach ($row as $index => $column) {
            // Check if it's the Transfer History column
            if ($index == 'transfer_history') {
                // Use MultiCell for Transfer History to wrap text
                $pdf->MultiCell($widths[$i], 10, $column, 1, 'C');
            } else {
                // Regular cell rendering
                $pdf->Cell($widths[$i], 10, $column, 1, 0, 'C');
            }
            $i++;
        }
        $pdf->Ln();
    }

    // Output the PDF
    $pdf->Output('D', $reportType . '_report.pdf');
    exit;
}
?>
