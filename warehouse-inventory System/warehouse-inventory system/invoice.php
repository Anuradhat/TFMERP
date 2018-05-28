<?php
ob_start();

session_set_cookie_params(0);
session_start();

require_once('pdf/fpdf.php');


$page_title = 'Invoice';
require_once('includes/load.php');

$inv_Customer = find_by_sp("call spSelectCusDetailsFromInvoiceNo('000001');");
$inv_Header = find_by_sp("call spSelectInvoiceHFromCode('000001');");
$inv_Detail = find_by_sql("call spSelectInvoiceDFromCode('000001');");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Times','',10);
$pdf->Cell(4);
$pdf->Cell(40,10,$inv_Customer['CustomerName']);
$pdf->Ln(5);
$pdf->Cell(4);
$pdf->Cell(40,10,$inv_Customer['CustomerAddress1']);
$pdf->Ln(5);
$pdf->Cell(4);
$pdf->Cell(40,10,$inv_Customer['CustomerAddress2']);
$pdf->Ln(5);
$pdf->Cell(4);
$pdf->Cell(40,10,$inv_Customer['CustomerAddress3']);
$pdf->Ln(8);
$pdf->Cell(11);
$pdf->Cell(40,10,$inv_Customer['VATNo']);
//Invoice header details
$pdf->Ln(8);
$pdf->Cell(4);
$pdf->Cell(25,10,$inv_Header['InvDate']);
$pdf->Cell(10,10,$inv_Header['CustomerPOCode']);
$pdf->Cell(45,10,$inv_Customer['ContactPerson']);
$pdf->Cell(22,10,'TERMS');
$pdf->Cell(45,10,$inv_Customer['EmployeeName']);
$pdf->Cell(20,10,$inv_Customer['InvoiceNo']);
$pdf->Ln(12);

foreach($inv_Detail as $row => $value)
{
    $pdf->Cell(4);
    $pdf->Cell(18,10,$value['ProductCode']);
    $pdf->Cell(65,10,$value['Description']);
    $pdf->Cell(10,10,"PCS");
    $pdf->Cell(8,10,$value['Qty']);
    $pdf->Cell(13,10,$value['SalePrice']);
    $pdf->Cell(14,10,$value['TaxAmount']);
    $pdf->Cell(20,10,$value['Amount']);
    $pdf->Ln(5);
}


$pdf->Output();


?>
