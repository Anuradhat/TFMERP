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

$pdf = new FPDF('P','mm',array(215,280));
$pdf->AddPage();
$pdf->SetFont('Arial','',11);
$pdf->Ln(9);
$pdf->Cell(15);
$pdf->Cell(40,10,$inv_Customer['CustomerName']);
$pdf->Ln(5);
$pdf->Cell(14);
$pdf->Cell(40,10,$inv_Customer['CustomerAddress1']);
$pdf->Ln(5);
$pdf->Cell(14);
$pdf->Cell(40,10,$inv_Customer['CustomerAddress2']);
$pdf->Ln(5);
$pdf->Cell(14);
$pdf->Cell(40,10,$inv_Customer['CustomerAddress3']);
$pdf->Ln(14);
$pdf->Cell(24);
$pdf->Cell(40,10,$inv_Customer['VATNo']);

//Invoice header details
$pdf->Ln(19);
$pdf->Cell(30,10,$inv_Header['InvDate']);
$pdf->Cell(20,10,$inv_Header['CustomerPOCode']);
$pdf->Cell(49,10,$inv_Customer['ContactPerson']);
$pdf->Cell(25,10,'TERMS');
$pdf->Cell(52,10,$inv_Customer['EmployeeName']);
$pdf->Cell(20,10,$inv_Customer['InvoiceNo']);
$pdf->Ln(17);

$TotalLineFeed = 0;
$BottmLine = 110;
$TaxAmount = 0;

$Amount = 0;
$TotalAmount = 0;

foreach($inv_Detail as $row => $value)
{
    $inv_Tax = find_by_sp("call spSelectInvoiceTax('{$value['InvoiceNo']}','{$value['ProductCode']}');");


    $Amount = $value['SalePrice'] * $value['Qty'];
    $TotalAmount += $Amount;
    $TaxAmount += $value['TaxAmount'];

    $TaxRate = $inv_Tax['TaxRate'] == "" ? 0 : $inv_Tax['TaxRate'];

    $pdf->Cell(20,10,$value['ProductCode']);
    $pdf->Cell(92,10,$value['Description']);
    $pdf->Cell(15,10,"PCS");
    $pdf->Cell(12,10,$value['Qty']);
    $pdf->Cell(23,10,$value['SalePrice']);
    $pdf->Cell(17,10, $TaxRate.'%');
    $pdf->Cell(35,10, number_format($Amount,2));
    $pdf->Ln(6);

    $TotalLineFeed += 6;
}

$pdf->Ln($BottmLine);
$pdf->Cell(176);
$pdf->Cell(30,10,number_format($TotalAmount,2));
$pdf->Ln(8);
$pdf->Cell(176);
$pdf->Cell(30,10,number_format($TaxAmount,2));
$pdf->Ln(8);
$pdf->Cell(176);
$pdf->Cell(30,10,$inv_Header['NetAmount']);

$pdf->Output();


?>
