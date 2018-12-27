<?php
ob_start();

session_set_cookie_params(0);
session_start();

require_once('pdf/fpdf.php');


$page_title = 'Invoice';
require_once('includes/load.php');

preventGetAction('create_invoice.php');

if($_SESSION['InvoiceNo'] != null)
    $InvoiceNo = $_SESSION['InvoiceNo'];
else
    redirect('create_invoice.php');


$Setup_VATNo = ReadSystemConfig('VATNo');
$SVATSetup_VATNo = ReadSystemConfig('SVATNo');

$user =  current_user();

$inv_Customer = find_by_sp("call spSelectCusDetailsFromInvoiceNo('{$InvoiceNo}');");
$inv_Header = find_by_sp("call spSelectInvoiceHFromCode('{$InvoiceNo}');");
$inv_Detail = find_by_sql("call spSelectInvoiceDFromCode('{$InvoiceNo}');");

$pdf = new FPDF('P','mm',array(215,280));
$pdf->AddPage();
$pdf->SetFont('Arial','',11);
$pdf->Ln(13);
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
$pdf->Cell(17);
$pdf->Cell(40,10,$inv_Customer['VATNo'].($inv_Customer["SVATNo"]!= ''? " S.V.A.T. NO: ".$inv_Customer['SVATNo']:""));


//Invoice header details
$pdf->Ln(19);
$pdf->Cell(1);
$pdf->Cell(30,10,$inv_Header['InvDate']);
$pdf->Cell(20,10,$inv_Header['CustomerPOCode']);
$pdf->Cell(49,10,$inv_Customer['ContactPerson']);
$pdf->Cell(25,10,'     ');//TERMS
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
    $pdf->Cell(15,10,"   ");
    $pdf->Cell(12,10,$value['Qty']);
    $pdf->Cell(23,10,$value['SalePrice']);
    $pdf->Cell(7,10, $TaxRate.'%');
    $pdf->Cell(35,10, number_format($Amount,2),0,0,'R');
    $pdf->Ln(6);

    $TotalLineFeed += 6;
}


  $pdf->Text(194,205,number_format($TotalAmount,2));
  $pdf->Text(194,215,number_format($TaxAmount,2));
  $pdf->Text(194,225,$inv_Header['NetAmount']);


  if($TaxAmount > 0)
  {
      if($inv_Customer["SVATNo"]!= "")
      {
         $pdf->Rect(144,25,54,8);
         $pdf->Text(146,31,"SUSPENDED TAX  INVOICE");

         $pdf->Rect(144,43,54,6);
         $pdf->Text(146,47,"S.V.A.T. NO.: ".$SVATSetup_VATNo);
      }
      else
      {
        $pdf->Rect(150,25,38,8);
        $pdf->Text(152,31,"T A X  I N V O I C E");
      }


      $pdf->Rect(144,35,54,6);
      $pdf->Text(146,40,"V.A.T. NO.: ".$Setup_VATNo);

  }


  //Invoice By
  $pdf->Text(22,250,$user["username"]);

$pdf->Output();


?>
