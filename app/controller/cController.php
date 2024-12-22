<?php
ob_start();
require('../../libs/fpdf.php');
include('../model/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reportType = $_POST['report_type'];

    if ($reportType === 'companies') {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(190, 10, 'Companies Report', 0, 1, 'C');
        $headers = ['Company ID', 'Name', 'Email', 'Type'];
        $widths = [40, 50, 50, 50];
        $query = "SELECT company_id, company_name, company_email, company_type FROM companies";
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
                $pdf->Cell($widths[array_search($index, array_keys($row))], 10, $column, 1, 0, 'C');
            }
            $pdf->Ln();
        }
        
        $pdf->Output('D', 'companies_report.pdf');
        exit;
    }
}
ob_end_clean();
?>