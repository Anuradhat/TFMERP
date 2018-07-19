<?php
ob_start();

session_set_cookie_params(0);
session_start();

require_once('pdf/fpdf.php');


$page_title = 'Credit Note';
require_once('includes/load.php');

preventGetAction('home.php');

if($_SESSION['CreditNoteNo'] != null)
    $CreditNoteNo = $_SESSION['CreditNoteNo'];
else
    redirect('create_creditnote.php');

$CompanyName = ReadSystemConfig('CompanyName');
$Address1 = ReadSystemConfig('Address1');
$Address2 = ReadSystemConfig('Address2');
$Address3 = ReadSystemConfig('Address3');

$Telephone1 = ReadSystemConfig('Telephone1');
$Telephone2 = ReadSystemConfig('Telephone2');
$Fax1 = ReadSystemConfig('Fax1');

$MobileNo = ReadSystemConfig('MobileNo');
$EMail = ReadSystemConfig('E-Mail');

$VATNo = ReadSystemConfig('VATNo');

$CreditNote = find_by_sp("call spSelectCreditNoteForPrint('{$CreditNoteNo}');");
$CreditNoteDetails = find_by_sql("call spSelectCreditNoteDFromCode('{$CreditNoteNo}');");

$pdf = new FPDF('P','mm',array(215,280));
$pdf->AddPage();
$pdf->SetFont('Arial','B',13);
$pdf->Ln(1);
$pdf->Cell(1);
$pdf->Cell(40,10,$CompanyName);
$pdf->SetFont('Arial','',9);
$pdf->Ln(6);
$pdf->Cell(1);
$pdf->Cell(40,10,$Address1.$Address2.$Address3);
$pdf->Ln(4);
$pdf->Cell(1);
$pdf->Cell(40,10,'Tel. '.$Telephone1.','.$Telephone2.' Fax :'.$Fax1);
$pdf->Ln(4);
$pdf->Cell(1);
$pdf->Cell(40,10,'Hot Line : '.$MobileNo.' email. :'.$EMail);
$pdf->SetFont('Arial','B',13);
$pdf->Ln(10);
$pdf->Cell(52);
$pdf->Cell(40,10,'TAX CREDIT NOTE/ Credit Note');
$pdf->SetFont('Arial','',9);
$pdf->Ln(8);
$pdf->Cell(1);
$pdf->Cell(40,10,$CreditNote["CustomerAddress1"]);
$pdf->Cell(75);
$pdf->Cell(40,10,'VAT NO: '.$CreditNote["VATNo"]);
$pdf->Cell(1);
$pdf->Ln(4);
$pdf->Cell(1);
$pdf->Cell(40,10,$CreditNote["CustomerAddress2"].','.$CreditNote["CustomerAddress3"]);
$pdf->Cell(75);
$pdf->Cell(40,10,'CREDIT NOTE NO: '.$CreditNote["CreditNoteNo"]);
$pdf->Cell(1);
$pdf->Ln(4);
$pdf->Cell(1);
$pdf->Cell(40,10,'VAT NO: '.$CreditNote["VATNo"]);
$pdf->Cell(75);
$pdf->Cell(40,10,'DATE: '.$CreditNote["CrNoteDate"]);
$pdf->SetFont('Arial','B',9);
$pdf->Ln(10);
$pdf->Cell(1);
$pdf->Cell(74,10,'DETAILS OF INVOICE');
$pdf->Cell(66,10,'SUPPORTING DOCUMENTS');
$pdf->Cell(40,10,'REASONS FOR CREDIT');

$pdf->SetFont('Arial','',9);
$pdf->Ln(5);
$pdf->Cell(1);
$pdf->Cell(37,10,'INVOICE NO.');
$pdf->Cell(37,10,$CreditNote["InvoiceNo"]);

$pdf->Cell(1);
$pdf->Cell(33,10,'FULL CREDIT');
$pdf->Rect(119,65,4,4);
$pdf->SetFont('Arial','b',9);
$pdf->Cell(33,10,$CreditNote["SupportDocument"] == '1' ? 'X' : '');
$pdf->SetFont('Arial','',9);

$pdf->Rect(187,65,4,4);
$pdf->Cell(35,10,'INVOICE REVERSAL');
$pdf->SetFont('Arial','b',9);
$pdf->Cell(20,10,$CreditNote["Reason"] == '1' ? 'X' : '');
$pdf->SetFont('Arial','',9);


$pdf->Ln(5);
$pdf->Cell(1);
$pdf->Cell(37,10,'INVOICE DATE.');
$pdf->Cell(37,10,$CreditNote["InvDate"]);

$pdf->Cell(1);
$pdf->Cell(33,10,'PARTIAL CREDIT');
$pdf->Rect(119,70,4,4);
$pdf->SetFont('Arial','b',9);
$pdf->Cell(33,10,$CreditNote["SupportDocument"] == '2' ? 'X' : '');
$pdf->SetFont('Arial','',9);

$pdf->Rect(187,70,4,4);
$pdf->Cell(35,10,'GOODS RETURN');
$pdf->SetFont('Arial','b',9);
$pdf->Cell(20,10,$CreditNote["Reason"] == '2' ? 'X' : '');
$pdf->SetFont('Arial','',9);

$pdf->Ln(5);
$pdf->Cell(1);
$pdf->Cell(37,10,'CUSTOMER PO.');
$pdf->Cell(37,10,$CreditNote["CustomerPOCode"]);

$pdf->Cell(1);
$pdf->Cell(33,10,'');
$pdf->Cell(33,10,'');

$pdf->Rect(187,75,4,4);
$pdf->Cell(35,10,'OTHER');
$pdf->SetFont('Arial','b',9);
$pdf->Cell(20,10,$CreditNote["Reason"] == '3' ? 'X' : '');
$pdf->SetFont('Arial','',9);

$pdf->Ln(5);
$pdf->Cell(1);
$pdf->Cell(37,10,'SALES PERSON');
$pdf->Cell(37,10,strtoupper($CreditNote["EmployeeName"]));

$pdf->Line(12,88,200,88);
$pdf->Line(12,88,12,200);
$pdf->Line(12,93,200,93);
$pdf->Line(200,88,200,215);

$pdf->Line(40,88,40,200);
$pdf->Line(105,88,105,215);
$pdf->Line(125,88,125,200);
$pdf->Line(140,88,140,200);
$pdf->Line(165,88,165,215);

$pdf->Line(12,200,200,200);

$pdf->Line(105,205,200,205);
$pdf->Line(105,210,200,210);
$pdf->Line(105,215,200,215);

$pdf->SetFont('Arial','b',9);

$pdf->Text(106,204,"Value of the Supply");
$pdf->Text(186,204,$CreditNote["CreditNoteAmount"]);
$pdf->Text(106,209,"VAT 11%");
$pdf->Text(191,209,"0.00");
$pdf->Text(106,214,"Total value");
$pdf->Text(186,214,$CreditNote["CreditNoteAmount"]);

$pdf->Ln(8.5);
$pdf->Cell(3);


$pdf->Cell(50,10,'CODE');
$pdf->Cell(47,10,'DISCRIPTION');
$pdf->Cell(18,10,'UNIT');
$pdf->Cell(19,10,'QTY');
$pdf->Cell(26,10,'RATE');
$pdf->Cell(60,10,'AMOUNT');
$pdf->SetFont('Arial','',9);

$pdf->Ln(6);

foreach($CreditNoteDetails as $row => $value)
{
    $pdf->Cell(2);
    $pdf->Cell(28,10,$value['ProductCode']);
    $pdf->Cell(67,10,$value['ProductDesc']);
    $pdf->Cell(21,10,'EACH');
    $pdf->Cell(22,10,$value['Qty']);
    $pdf->Cell(34,10,$value['SellingPrice']);
    $pdf->Cell(10,10,$value['Amount']);
    $pdf->Ln(5);
}

$pdf->Text(20,258,"..........................................");
$pdf->Text(30,262,"Prepared by");


$pdf->Text(150,258,"..........................................");
$pdf->Text(160,262,"Authorised by");


$pdf->Output();


?>
