<?php
require('../../libs/fpdf.php');

class PDFGenerator extends FPDF {
   
    public function addHeaders($headers) {
        $this->SetFont('Arial', 'B', 12);
        foreach ($headers as $header) {
            $this->Cell(40, 10, $header, 1);
        }
        $this->Ln();
    }

    public function addRow($row) {
        $this->SetFont('Arial', '', 10);
        foreach ($row as $cell) {
            $this->Cell(40, 10, $cell, 1);
        }
        $this->Ln();
    }
}
?>
