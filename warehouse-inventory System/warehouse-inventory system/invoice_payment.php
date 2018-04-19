<?php
ob_start();

$page_title = 'Invoice - Payment';
require_once('includes/load.php');
page_require_level(2);

preventGetAction('create_invoice.php');

$arr_header = array();
$arr_item = array();
$arr_card = array();
$arr_cheque = array();
$arr_banktrn = array();

$_cash = 0;


if($_SESSION['header'] != null) $arr_header = $_SESSION['header'];
if($_SESSION['details'] != null) $arr_item = $_SESSION['details'];
if($_SESSION['card'] != null) $arr_card = $_SESSION['card'];
if($_SESSION['cheque'] != null) $arr_cheque = $_SESSION['cheque'];
if($_SESSION['banktrn'] != null) $arr_banktrn = $_SESSION['banktrn'];
if($_SESSION['Cash'] != null) $_cash = $_SESSION['Cash'];

$Location = find_by_sp("call spSelectLocationFromCode('{$arr_header['LocationCode']}');");
$Customer = find_by_sp("call spSelectCustomerFromCode('{$arr_header['CustomerCode']}');");
$Salesman = find_by_sp("call spSelectEmployeeFromEpf('{$arr_header['SalesmanCode']}');");




?>


<?php

if(isset($_POST['invoice_payment'])){

    if($_POST['invoice_payment'] == "Save")
    {
        $req_fields = array();

        validate_fields($req_fields);


        if(empty($errors))
        {
            $p_LocationCode  = $arr_header['LocationCode'];
            $p_CustomerCode  = $arr_header['CustomerCode'];
            $p_CustomerPoCode  = $arr_header['CustomerPoCode'];
            $p_SalesmanCode = $arr_header['SalesmanCode'];
            $p_Remarks  = $arr_header['Remarks'];

            $p_GrossAmount = $arr_header['GrossAmount'];
            $p_DiscountAmount = $arr_header['Discount'];
            $p_NetAmount = $arr_header['NetAmount'];

            $date    = make_date();
            $datetime = make_datetime();
            $user =  current_user();


            //Get all sessions values
            $arr_item= $_SESSION['details'];

            //check details values
            if(count($arr_item)>0)
            {

                //Check transaction qty
                $IsQtyExist = true;

                foreach($arr_item as $row => $value)
                    if ($value[4] <= 0)
                        $IsQtyExist = false;

                if(!$IsQtyExist)
                {
                    $flashMessages->warning('Some invoice item qty not found.','invoice_payment.php');
                    //$session->msg("d", "Some invoice item qty not found.");
                    //redirect('invoice_payment.php',false);
                }


                //******* Check with SIH ***************************************
                foreach($arr_item as $row => $value)
                {
                    if (SelectStockSIHFormProduct($value[0],$p_LocationCode) < $value[4])
                    {
                        //$session->msg("d", "Some invoice qty is greater than SIH.");
                        //redirect('invoice_payment.php',false);
                        $flashMessages->warning('Some invoice qty is greater than SIH.','invoice_payment.php');
                        exit;
                    }
                }

                //********************** Check serial qty ************************
                foreach($arr_item as $row => $value)
                {
                    $ProductCode = $value[0];
                    $InvQty = $value[4];

                    if($InvQty > 0)
                    {
                        $SerialCount = count($value[6]);
                        if($InvQty != $SerialCount)
                        {
                            //$session->msg("d", "Invoice serial details are invalid. Reference: ".$StockCode);
                            //redirect('invoice_payment.php',false);
                            $flashMessages->warning('Invoice serial details are invalid. Reference: '.$ProductCode,'invoice_payment.php');
                            exit;
                        }
                    }
                }


                //Create invoice
                try
                {
                    $p_InvoiceCode  = autoGenerateNumber('tfmInvoiceHT',1);

                    $db->begin();

                    $Invoice_count = find_by_sp("call spSelectInvoiceHFromCode('{$p_InvoiceCode}');");

                    if($Invoice_count)
                    {
                        //$session->msg("d", "This invoice number exist in the system.");
                        $flashMessages->warning('Duplicate invoice number found','invoice_payment.php');
                    }

                    $TotalCardValue = 0;  foreach($arr_card  as &$value) { $TotalCardValue += $value["value"];}
                    $TotalChequeValue = 0;  foreach($arr_cheque  as &$value) { $TotalChequeValue += $value["value"];}
                    $ToatlBankTrnPayment = 0;  foreach($arr_banktrn  as &$value) { $ToatlBankTrnPayment += $value["value"];}
                    $Credit = ($arr_header['NetAmount'] - ($_cash + $TotalCardValue + $TotalChequeValue + $ToatlBankTrnPayment)) < 0 ? 0 : ($arr_header['NetAmount'] - ($_cash + $TotalCardValue + $TotalChequeValue +$ToatlBankTrnPayment));

                    $PaidAmount = $_cash + $TotalCardValue + $TotalChequeValue + $ToatlBankTrnPayment;

                    //Insert invoice header details
                    $query  = "call spInsertInvoiceH('{$p_InvoiceCode}','{$p_LocationCode}','{$date}','{$datetime}','{$p_CustomerPoCode}','{$p_CustomerCode}',{$p_GrossAmount},0,{$p_DiscountAmount},{$p_NetAmount},{$Credit},{$PaidAmount},{$Credit},'{$p_SalesmanCode}',0,'','{$date}','{$user["username"]}');";
                    $db->query($query);


                    //Update customer PO to process
                    $query  = "call spUpdateCusPurchaseOrderToProcess('{$p_CustomerPoCode}','{$date}');";
                    $db->query($query);


                    //Insert invoice details and TAX
                    foreach($arr_item as $row => $value)
                    {
                        $LineAmount = $value[4] * $value[3];
                        $query  = "call spInsertInvoiceD('{$p_InvoiceCode}','{$p_LocationCode}','{$value[0]}','{$value[1]}',{$value[2]},{$value[3]},{$value[4]},0,{$value[7]},{$LineAmount});";
                        $db->query($query);

                        //Tax
                        $ToatlTax = 0;

                        if($value[7] != 0){
                          $ProductTax = find_by_sql("call spSelectProductTaxFromProductCode('{$value[0]}');");
                          foreach($ProductTax as &$pTax)
                          {
                             $TaxRatesM = find_by_sql("call spSelectTaxRatesFromCode('{$pTax["TaxCode"]}');");
                             foreach($TaxRatesM as &$TaxRt)
                             {
                                $ToatlTax += $TaxRt["TaxRate"];
                             }
                          }

                          $query  = "call spInsertInvoiceTax('{$p_InvoiceCode}','{$p_LocationCode}','{$value[0]}',{$ToatlTax},{$value[7]});";
                          $db->query($query);
                        }

                    }

                    //Update Customer Due
                    $query  = "call spUpdateCustomerDue('{$p_CustomerCode}',{$Credit});";
                    $db->query($query);

                    //***************************** Insert Payment Details ************************************************************


                    //Cash
                    $query  = "call spInsertInvoicePaymentD('{$p_InvoiceCode}','{$p_LocationCode}','P001','006','','','','',{$_cash},{$p_NetAmount},'{$date}','{$user["username"]}');";
                    $db->query($query);



                    //Credit/Debit Card
                    foreach($arr_card  as $row => $value)
                    {
                        $query  = "call spInsertInvoicePaymentD('{$p_InvoiceCode}','{$p_LocationCode}','P002','006','','{$value['key']}','','',{$value['value']},{$p_NetAmount},'{$date}','{$user["username"]}');";
                        $db->query($query);
                    }



                    //Cheque
                    foreach($arr_cheque  as $row => $value)
                    {
                        $query  = "call spInsertInvoicePaymentD('{$p_InvoiceCode}','{$p_LocationCode}','P004','006','{$value['bank']}','{$value['key']}','{$value['date']}','',{$value['value']},{$p_NetAmount},'{$date}','{$user["username"]}');";
                        $db->query($query);
                    }




                    //Bank Transfer
                    foreach($arr_banktrn  as $row => $value)
                    {
                        $query  = "call spInsertInvoicePaymentD('{$p_InvoiceCode}','{$p_LocationCode}','P005','006','{$value['bank']}','{$value['key']}','{$value['date']}','{$value['name']}',{$value['value']},{$p_NetAmount},'{$date}','{$user["username"]}');";
                        $db->query($query);
                    }


                    //Update Stock and serials
                    foreach($arr_item as $row => $value)
                    {
                        $SerialCodes = $value[6];

                        foreach($SerialCodes as $row => $Serial)
                        {
                            $SerialDetails = find_by_sp("call spSelectGRNSerialDetailsFromSerialCode('{$Serial}');");
                            $StockDetails = find_by_sp("call spSelectStock('{$SerialDetails['StockCode']}','{$SerialDetails['LocationCode']}','{$SerialDetails['BinCode']}');");

                            //Update serial flag
                            $query  = "call spUpdateSaleFlagGRNSerialFromSerialCode('{$Serial}','{$p_InvoiceCode}');";
                            $db->query($query);

                            //Insert Invoice Serials
                            $query  = "call spInsertInvoiceSerialD('{$p_InvoiceCode}','{$p_LocationCode}','{$SerialDetails['StockCode']}','{$Serial}',7);";
                            $db->query($query);

                            //Update Stock
                            $query  = "call spUpdateStock('{$SerialDetails['StockCode']}','{$SerialDetails['LocationCode']}','{$SerialDetails['BinCode']}',1,'{$date}');";
                            $db->query($query);

                            //Insert stock movement
                            $query  = "call spStockMovement('{$SerialDetails['StockCode']}','{$SerialDetails['LocationCode']}','{$SerialDetails['BinCode']}',
                                       '{$StockDetails['ProductCode']}','{$Serial}','{$p_InvoiceCode}','{$StockDetails['SupplierCode']}','006',{$value[2]},{$value[3]},0,{$StockDetails['AvgCostPrice']},0,-1,'{$StockDetails['ExpireDate']}','{$date}','{$user["username"]}');";
                            $db->query($query);
                        }
                    }

                    $db->commit();


                    $flashMessages->success('Invoice has been saved successfully,\n   Your invoice No: '.$p_InvoiceCode,'create_invoice.php');

                }
                catch(Exception $ex)
                {
                    $db->rollback();

                    $flashMessages->error('Sorry failed to create invoice! '.$ex->getMessage(),'invoice_payment.php');
                }


            }
            else
            {
                $flashMessages->warning('Invoice item(s) not found!'.$ex->getMessage(),'invoice_payment.php');
            }
        }
        else
        {
            $flashMessages->warning($errors,'invoice_payment.php');
        }

    }
}




if (isset($_POST['CardPayment'])) {
    return include('_partial_addcreditcard.php');
}

if (isset($_POST['ChequePayment'])) {
    return include('_partial_addcheque.php');
}

if (isset($_POST['BankTrnsfer'])) {
    return include('_partial_addbanktransfer.php');
}

if (isset($_POST['CardNumber']) && isset($_POST['Value'])) {

    $arr_card[] = array($_POST['CardNumber'],$_POST['Value']);
    $_SESSION['card'] = $arr_card;
    echo 'OK';
}

if (isset($_POST['CashPayment'])) {

    $_cash = $_POST['CashPayment'];
    $_SESSION['Cash'] = $_cash;
    echo 'OK';
}



?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Payment
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Invoice
            </a>
        </li>
        <li class="active">Payment</li>
    </ol>
    <style>
        .column {
            float: left;
            padding: 10px;
            height: 600px;
        }

        .column1 {
            width: 25%;
        }

        .column2 {
            width: 50%;
        }

        .column3 {
            width: 25%;
        }
    </style>
</section>

<!-- Main content -->
<section class="content">
    <!-- Your Page Content Here -->
    <form method="post" action="invoice_payment.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div>
                            <div class="btn-group">
                                <button type="submit" name="invoice_payment" class="btn btn-primary" value="Save">&nbsp;Save&nbsp;</button>
                                <button type="button" class="btn btn-warning" onclick="window.location = 'home.php'">Cancel  </button>
                            </div>
                            <button type="button" class="btn btn-success pull-right" onclick="window.location = 'create_invoice.php'">Go To Invoice</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div id="message" class="col-md-12">
                <?php include('_partial_message.php'); ?>
            </div>
        </div>

        <div class="row">
            <div class="column column1">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Billing Information</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="form-group">
                                        <label>Location</label>
                                        <input type="text" class="form-control" name="Location" readonly="readonly" disabled="disabled" value="<?php echo $Location['LocationName']  ?>" />
                                    </div>
                                    <div class="form-group">
                                        <label>Customer</label>
                                        <input type="text" class="form-control" name="Customer" readonly="readonly" disabled="disabled" value="<?php echo $Customer['CustomerName']  ?>" />
                                    </div>

                                    <div class="form-group">
                                        <label>Customer PO</label>
                                        <input type="text" class="form-control" name="CustomerPo" readonly="readonly" disabled="disabled" value="<?php echo $arr_header['CustomerPoCode'];  ?>" />
                                    </div>

                                    <div class="form-group">
                                        <label>Salesman</label>
                                        <input type="text" class="form-control" name="Salesman" readonly="readonly" disabled="disabled" value="<?php echo $Salesman['EmployeeName']  ?>" />
                                    </div>

                                    <div class="form-group">
                                        <label>Remarks</label>
                                        <input type="text" class="form-control" name="Remarks" readonly="readonly" disabled="disabled" value="<?php echo $arr_header['Remarks'];  ?>" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column column2">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Payment Information</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <input type="hidden" id="hNetAmount" value="<?php  echo $arr_header['NetAmount'] ?>" />


                                <div class="form-group">
                                    <label>Cash Payment</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control text-right integer" id="CashPayment" name="CashPayment" placeholder="Cash Payment" value="<?php echo number_format($_cash,2,'.',''); ?>" autocomplete="off" />
                                        <span class="input-group-btn">
                                            <button type="button" class="CardBtn btn btn-default btn-flat" data-toggle="modal" data-target="#myModal" contenteditable="false" disabled>
                                                <i class="fa fa-money"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Card Payment</label>
                                    <div class="input-group">
                                        <?php  $TotalCardValue = 0;  foreach($arr_card  as &$value) { $TotalCardValue += $value["value"]; } ?>
                                        <input type="text" class="form-control text-right" id="CardPayment" name="CardPayment" placeholder="Credit/Debit Card Payment" value="<?php echo number_format($TotalCardValue,2,'.',''); ?>" autocomplete="off" readonly="readonly" disabled />
                                        <span class="input-group-btn">
                                            <button type="button" class="CardBtn btn btn-default btn-flat" data-toggle="modal" data-target="#myModal" contenteditable="false">
                                                <i class="fa fa-credit-card"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label>Cheque Payment</label>
                                    <div class="input-group">
                                        <?php  $TotalChequeValue = 0;  foreach($arr_cheque  as &$value) { $TotalChequeValue += $value["value"]; } ?>
                                        <input type="text" class="form-control text-right" id="ChequePayment" name="ChequePayment" placeholder="Cheque Payment" value="<?php echo number_format($TotalChequeValue,2,'.',''); ?>" autocomplete="off" readonly="readonly" disabled />
                                        <span class="input-group-btn">
                                            <button type="button" class="ChequeBtn btn btn-default btn-flat" data-toggle="modal" data-target="#myModal" contenteditable="false">
                                                <i class="fa fa-newspaper-o"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Bank Transfer Payment</label>
                                    <div class="input-group">
                                        <?php  $ToatlBankTrnPayment = 0;  foreach($arr_banktrn  as &$value) { $ToatlBankTrnPayment += $value["value"]; } ?>
                                        <input type="text" class="form-control text-right" id="BankTransferPayment" name="BankTransferPayment" placeholder="Bank Transfer Payment" value="<?php echo number_format($ToatlBankTrnPayment,2,'.',''); ?>" autocomplete="off" readonly="readonly" disabled />
                                        <span class="input-group-btn">
                                            <button type="button" class="BankTrnBtn btn btn-default btn-flat" data-toggle="modal" data-target="#myModal" contenteditable="false">
                                                <i class="fa fa-share-square-o"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label>Credit</label>
                                    <div class="input-group">
                                        <?php  $Credit = ($arr_header['NetAmount'] - ($_cash + $TotalCardValue + $TotalChequeValue +$ToatlBankTrnPayment)) < 0 ? 0 : ($arr_header['NetAmount'] - ($_cash + $TotalCardValue + $TotalChequeValue +$ToatlBankTrnPayment)); ?>
                                        <input type="text" class="form-control text-right" name="Credit" id="Credit" placeholder="Credit Value" autocomplete="off" value="<?php  echo number_format($Credit,2,'.',''); ?>" readonly="readonly" disabled />
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-default btn-flat" disabled>
                                                <i class="fa fa-handshake-o"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column column3">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Finalize Invoice</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="InvoiceSummary" class="table table-condensed">
                                        <thead>
                                            <tr>
                                                <td>
                                                    <strong>Item</strong>
                                                </td>
                                                <td class="text-right">
                                                    <strong>Totals</strong>
                                                </td>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <td class="thick-line"></td>
                                                <td class="thick-line"></td>
                                            </tr>
                                            <tr>
                                                <td class="thick-line text-center">
                                                    <strong>Gross Amount (Rs)</strong>
                                                </td>
                                                <td class="thick-line text-right">
                                                    <?php echo number_format($arr_header['GrossAmount'],2); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="thick-line text-center">
                                                    <strong>Discount Amount (Rs)</strong>
                                                </td>
                                                <td class="thick-line text-right">
                                                    <?php echo number_format($arr_header['Discount'],2); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="thick-line text-center">
                                                    <strong>Net Amount (Rs)</strong>
                                                </td>
                                                <td class="thick-line text-right">
                                                    <?php echo number_format($arr_header['NetAmount'],2); ?>
                                                </td>
                                            </tr>
                                        </tfoot>
                                        <tbody>
                                            <?php  foreach($arr_item  as &$value) { ?>
                                            <tr>
                                                <td>
                                                    <?php echo substr($value[1],6)."..." ?>
                                                </td>
                                                <td class="text-right">
                                                    <?php echo number_format(($value[5] == null ? 0 : $value[3] * $value[4]),2) ?>
                                                </td>
                                            </tr>
                                            <?php  } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
  </form>
</section>
<?php include_once('layouts/footer.php'); ?>

<script>
    $(document).ready(function () {
        $(".CardBtn").click(function () {
            $('.loader').show();

            var $row = $(this).closest("tr");
            var RowNo = $row.find(".clsRowId").text();

            $.ajax({
                url: "invoice_payment.php",
                type: "POST",
                data: 'CardPayment=' + 'OK',
                success: function (result) {
                    var modalBody = $('<div id="modalContent"></div>');
                    modalBody.append(result);
                    $("#myModalLabel").text('Credit/Debit Card Payment');
                    $('.modal-body').html(modalBody);
                    $('.loader').fadeOut();
                }
            });


        });
    });
    
    $(document).ready(function () {
        $(".ChequeBtn").click(function () {
            $('.loader').show();

            var $row = $(this).closest("tr");
            var RowNo = $row.find(".clsRowId").text();

            $.ajax({
                url: "invoice_payment.php",
                type: "POST",
                data: 'ChequePayment=' + 'OK',
                success: function (result) {
                    var modalBody = $('<div id="modalContent"></div>');
                    modalBody.append(result);
                    $("#myModalLabel").text('Cheque Payment');
                    $('.modal-body').html(modalBody);
                    $('.loader').fadeOut();
                }
            });


        });
    });

    $(document).ready(function () {
        $(".BankTrnBtn").click(function () {
            $('.loader').show();

            var $row = $(this).closest("tr");
            var RowNo = $row.find(".clsRowId").text();

            $.ajax({
                url: "invoice_payment.php",
                type: "POST",
                data: 'BankTrnsfer=' + 'OK',
                success: function (result) {
                    var modalBody = $('<div id="modalContent"></div>');
                    modalBody.append(result);
                    $("#myModalLabel").text('Bank Transfer');
                    $('.modal-body').html(modalBody);
                    $('.loader').fadeOut();
                }
            });


        });
    });


    $("#CashPayment").change(function () {
        $('.loader').show();

        var CashPayment = parseFloat($("#CashPayment").val());

        if (CashPayment < 0)
        {
            bootbox.alert('Invalid cash payment.');
            $("#CashPayment").val('<?php echo number_format($_cash,2,'.',''); ?>')
        }
        else {
            $.ajax({
                url: "invoice_payment.php",
                type: "POST",
                data: { CashPayment: CashPayment },
                success: function (result) {
                    CalculateCreditDue();
                    $('.loader').fadeOut();
                }
            });

        }
    });


    function CalculateCreditDue() {
        $('.loader').show();

        var NetAmount = $("#hNetAmount").val() == "" ? 0 : $("#hNetAmount").val();
        var CashValue = $("#CashPayment").val() == "" ? 0 : $("#CashPayment").val();
        var CardValue = $("#CardPayment").val() == "" ? 0 : $("#CardPayment").val();
        var ChequeValue = $("#ChequePayment").val() == "" ? 0 : $("#ChequePayment").val();
        var TransferValue = $("#BankTransferPayment").val() == "" ? 0 : $("#BankTransferPayment").val();
        

        var Credit = (parseFloat(NetAmount) - (parseFloat(CashValue) + parseFloat(CardValue) + parseFloat(ChequeValue) + parseFloat(TransferValue))) < 0 ? 0 : (parseFloat(NetAmount) - (parseFloat(CashValue) + parseFloat(CardValue) + parseFloat(ChequeValue) + parseFloat(TransferValue)));
        $("#Credit").val((Credit).toFixed(2));

        $('.loader').fadeOut();
    }

    //Textbox integer accept
    $(".integer").keypress(function (evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    });

</script>