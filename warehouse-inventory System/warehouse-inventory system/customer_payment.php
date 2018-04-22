<?php
ob_start();

session_set_cookie_params(0);
session_start();

$page_title = 'Customer Payment';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
UserPageAccessControle(1,'Customer Payments');

$default_salesrepDesig = ReadSystemConfig('DefaultSalesRepDesigCode');


$all_locations = find_by_sql("call spSelectAllLocations();");
$all_Customers = find_by_sql("call spSelectAllCustomers();");
$all_salesrep = find_by_sql("call spSelectEmployeeFromDesignationCode('{$default_salesrepDesig}');");

$arr_header = array();
$arr_item = array();
$arr_card = array();
$arr_cheque = array();
$arr_banktrn = array();

$_cash = 0;
//$AvalableToPayment = 0;

if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && !$flashMessages->hasErrors() && !$flashMessages->hasWarnings()) // $session->msg == null
{
    unset($_SESSION['details']);
    unset($_SESSION['header']);
    unset($_SESSION['card']);
    unset($_SESSION['cheque']);
    unset($_SESSION['banktrn']);
    unset($_SESSION['Cash']);
    unset($_SESSION['AvalableToPayment']);
    unset($_SESSION['DiscountAmount']);
    unset($_SESSION['LocationCode']);
}

if($_SESSION['header'] != null) $arr_header = $_SESSION['header'];
if($_SESSION['details'] != null) $arr_item = $_SESSION['details'];
if($_SESSION['card'] != null) $arr_card = $_SESSION['card'];
if($_SESSION['cheque'] != null) $arr_cheque = $_SESSION['cheque'];
if($_SESSION['banktrn'] != null) $arr_banktrn = $_SESSION['banktrn'];
if($_SESSION['Cash'] != null) $_cash = $_SESSION['Cash'];
if($_SESSION['AvalableToPayment'] != null) $AvalableToPayment = $_SESSION['AvalableToPayment'];
 
 $TotalCardValue = 0;  
 foreach($arr_card  as &$value) { $TotalCardValue += $value["value"];}
 $TotalChequeValue = 0;  
 foreach($arr_cheque  as &$value) { $TotalChequeValue += $value["value"];}
 $ToatlBankTrnPayment = 0;  
 foreach($arr_banktrn  as &$value) { $ToatlBankTrnPayment += $value["value"];}

 $TotalPayment = ($_cash + $TotalCardValue + $TotalChequeValue + $ToatlBankTrnPayment);

?>

<?php

if(isset($_POST['customer_payment'])){

    if($_POST['customer_payment'] == "save")
    {
        $req_fields = array('LocationCode','CustomerCode');

        validate_fields($req_fields);


        if(empty($errors))
        {
            $p_LocationCode  = remove_junk($db->escape($_POST['LocationCode']));
            $p_CustomerCode  = remove_junk($db->escape($_POST['CustomerCode']));
            $date    = make_date();
            $user =  current_user();


            $arr_header = array('LocationCode'=>$p_LocationCode,'CustomerCode'=>$p_CustomerCode);
            $_SESSION['header'] = $arr_header;

            //check details values
            if(count($arr_item)>0)
            {
                $LinePayment = 0;
                foreach($arr_item  as &$value) { $LinePayment += $value[5];}


                if($TotalPayment == null || $TotalPayment <= 0)
                {
                    $flashMessages->warning('No any customer payment found!','customer_payment.php');
                }
                else if($LinePayment == null || $LinePayment <= 0)
                {
                    $flashMessages->warning('No any invoice payment found!','customer_payment.php');
                }
                else if($TotalPayment < $LinePayment)
                {
                    $flashMessages->warning('Invoice payment cannot exceed than total payment.','customer_payment.php');
                }
                elseif (($TotalPayment - $LinePayment) > 0 )
                {
                    $flashMessages->warning('Cannot have any balance to payment amount.','customer_payment.php');
                }
                else
                {

                    try
                    {
                        $p_CusPaymentCode  = autoGenerateNumber('tfmCusPaymentHT',1);

                        $db->begin();

                        $CusPayment_count = find_by_sp("call spSelectCusPaymentHFromCode('{$p_CusPaymentCode}');");


                        if($CusPayment_count)
                        {
                            $flashMessages->warning('Duplicate customer payment number found','customer_payment.php');
                        }


                        //Insert Customer Payment Header
                        $query  = "call spInsertCusPaymentH('{$p_CusPaymentCode}','{$p_LocationCode}','{$date}',{$TotalPayment},'{$date}','{$user["username"]}');";
                        $db->query($query);


                        //Insert Customer Payment Details
                        foreach($arr_item as $row => $value)
                        {
                            if ($value[5] > 0)
                            {
                               //Invoice customer payment details
                              $query  = "call spInsertCusPaymentD('{$p_CusPaymentCode}','{$value[0]}',{$value[4]},{$value[5]});";
                              $db->query($query);

                              //Update invoice balance
                              $query  = "call spUpdateInvoiceBalanceFromInvoiceCode('{$value[0]}',{$value[5]});";
                              $db->query($query);
                            }
                        }

                        $CusDue = $TotalPayment * -1;

                        //Update Customer Due
                        $query  = "call spUpdateCustomerDue('{$p_CustomerCode}',{$CusDue});";
                        $db->query($query);


                        //***************************** Insert Payment Details ************************************************************

                        //Cash
                        $query  = "call spInsertInvoicePaymentD('{$p_CusPaymentCode}','{$p_LocationCode}','P001','007','','','','',{$_cash},0,'{$date}','{$user["username"]}');";
                        $db->query($query);



                        //Credit/Debit Card
                        foreach($arr_card  as $row => $value)
                        {
                            $query  = "call spInsertInvoicePaymentD('{$p_CusPaymentCode}','{$p_LocationCode}','P002','007','','{$value['key']}','','',{$value['value']},0,'{$date}','{$user["username"]}');";
                            $db->query($query);
                        }


                        //Cheque
                        foreach($arr_cheque  as $row => $value)
                        {
                            $query  = "call spInsertInvoicePaymentD('{$p_CusPaymentCode}','{$p_LocationCode}','P004','007','{$value['bank']}','{$value['key']}','{$value['date']}','',{$value['value']},0,'{$date}','{$user["username"]}');";
                            $db->query($query);
                        }


                        //Bank Transfer
                        foreach($arr_banktrn  as $row => $value)
                        {
                            $query  = "call spInsertInvoicePaymentD('{$p_CusPaymentCode}','{$p_LocationCode}','P005','007','{$value['bank']}','{$value['key']}','{$value['date']}','{$value['name']}',{$value['value']},0,'{$date}','{$user["username"]}');";
                            $db->query($query);
                        }


                        $db->commit();

                        $flashMessages->success('Customer payment has been saved successfully, Your payment No: '.$p_CusPaymentCode,'customer_payment.php');

                    }
                    catch(Exception $ex)
                    {
                        $db->rollback();

                        $flashMessages->error('Sorry failed to create invoice payment! '.$ex->getMessage(),'customer_payment.php');
                    }
                }
            }
            else
            {
                $flashMessages->warning('Customer payment detail(s) not found!','customer_payment.php');
            }
        }
        else
        {
            $flashMessages->warning($errors,'customer_payment.php');
        }

    }
}




if (isset($_POST['_InvoiceNo'])) {
    $InvoiceNo = remove_junk($db->escape($_POST['_InvoiceNo']));
    $serchitem = ArraySearch($arr_item,$InvoiceNo);

    return include('_partial_paymentitem.php');
}


if (isset($_POST['AvalableToPayment'])) {
    $AvalableToPayment = 0;
    $LinePayment = 0;
    
    foreach($arr_item  as &$value) { $LinePayment += $value[5];}
    $_SESSION['AvalableToPayment'] = $TotalPayment - $LinePayment;

    return;
}


if (isset($_POST['CustomerChanged'])) {
    $arr_item = array();

    $_SESSION['details'] = null;

    return include('_partial_paymentdetails.php');
}


if (isset($_POST['CashPayment'])) {

    $_cash = $_POST['CashPayment'];
    $_SESSION['Cash'] = $_cash;
    echo 'OK';
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





if (isset($_POST['CustomerCode'])) {

    $CustomerCode = remove_junk($db->escape($_POST['CustomerCode']));


    $default_salesrepDesig = ReadSystemConfig('DefaultSalesRepDesigCode');
    $all_salesrep = find_by_sql("call spSelectEmployeeFromDesignationCode('{$default_salesrepDesig}');");
    $Customer =    find_by_sp("call spSelectCustomerFromCode('{$CustomerCode}');");

    echo "<option value=''>Select Salesman</option>";
    foreach($all_salesrep as &$value){
        $Selected = $value["EpfNumber"] == $Customer["SalesPersonCode"] ? "selected":"";
        echo "<option value ={$value["EpfNumber"]}  {$Selected} >{$value["EmployeeName"]}</option>";
    }
    return;
}



if (isset($_POST['FillTable']) &&  isset($_POST['Customer'])) {
    $_SESSION['details']  = null;
    $arr_item = array(); 

    $Customer= remove_junk($db->escape($_POST['Customer']));

    $Invoice_Details = find_by_sql("call spSelectAllCreditInvoiceFromCustomerCode('{$Customer}');");


    foreach($Invoice_Details as &$value){
        $arr_item[]  = array($value["InvoiceNo"],$value["InvDate"],$value["GrossAmount"],$value["NetAmount"],$value["Balance"],0);
    }

    $_SESSION['details'] = $arr_item;

    return include('_partial_paymentdetails.php');
}


if (isset($_POST['Edit'])) {

    $InvoiceNo = remove_junk($db->escape($_POST['InvoiceNo']));
    $Payment = remove_junk($db->escape($_POST['Payment']));

    $arr_item = $_SESSION['details'];


    $arr_item = ChangValueFromListOfArray( $arr_item,$InvoiceNo,5,$Payment);

    $_SESSION['details'] = $arr_item;

    return include('_partial_paymentdetails.php');
}
?>

<?php include_once('layouts/header.php'); ?>

<section class="content-header">
    <h1>
        Customer Payment
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Payment
            </a>
        </li>
        <li class="active">Customer</li>
    </ol>
    <style>
        form {
            display: inline;
        }
    </style>
</section>

<!-- Main content -->
<section class="content">
    <!-- Your Page Content Here -->
    <form method="post" action="customer_payment.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="customer_payment" class="btn btn-primary" value="save">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'home.php'">Cancel  </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div id="message" class="col-md-12"><?php include('_partial_message.php'); ?> </div>
        </div>

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Basic Details</h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="form-group">
                                <label>Payment No</label>
                                <input type="text" class="form-control" name="InvoiceNo" placeholder="Code will generate after save" readonly="readonly" disabled="disabled" />
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="text" class="form-control" name="SoDate" id="SoDate" placeholder="Date" readonly="readonly" disabled="disabled" value="<?php echo make_date(); ?>" />
                            </div>
                        </div>

                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Location</label>
                            <select class="form-control select2" style="width: 100%;" name="LocationCode" id="LocationCode" required="required">
                                <option value="">Select Location</option><?php  foreach ($all_locations as $loc): ?>
                                <option value="<?php echo $loc['LocationCode'] ?>" <?php if($loc['LocationCode'] == $arr_header["LocationCode"]) echo "selected";  ?>><?php echo $loc['LocationName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Total Payment</label>
                            <input type="text" class="form-control text-right" id="TotalPayment" name="TotalPayment" placeholder="Total Payment" value="<?php echo number_format($TotalPayment,2,'.',''); ?>" autocomplete="off" readonly="readonly" disabled />
                        </div>

                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Customer <span class="text-danger">(Credit Amount:&nbsp;<output class="inline" for="fader" id="creditamount">0</output>)</span></label>
                            <select class="form-control select2" style="width: 100%;" name="CustomerCode" id="CustomerCode" required="required" onchange="FillInvoice();">
                                <option value="">Select Customer</option><?php  foreach ($all_Customers as $cus): ?>
                                <option value="<?php echo $cus['CustomerCode'] ?>" <?php if($cus['CustomerCode'] == $arr_header["CustomerCode"]) echo "selected";  ?>><?php echo $cus['CustomerName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>
                        
                    </div>

                </div>
            </div>
        </div>
    </form>


    <div class="box box-default">
        <!-- /.box-header -->
        <form method="post" action="customer_payment.php">
            <input type="hidden" value="customer_payment"name="customer_payment" />

            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
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
                    </div>

                    <div class="col-md-3">
                        <label>Card Payment</label>
                        <div class="input-group">
                            <input type="text" class="form-control text-right" id="CardPayment" name="CardPayment" placeholder="Credit/Debit Card Payment" value="<?php echo number_format($TotalCardValue,2,'.',''); ?>" autocomplete="off" readonly="readonly" disabled />
                            <span class="input-group-btn">
                                <button type="button" class="CardBtn btn btn-default btn-flat" data-toggle="modal" data-target="#myModal" contenteditable="false">
                                    <i class="fa fa-credit-card"></i>
                                </button>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label>Cheque Payment</label>
                        <div class="input-group">
                            <input type="text" class="form-control text-right" id="ChequePayment" name="ChequePayment" placeholder="Cheque Payment" value="<?php echo number_format($TotalChequeValue,2,'.',''); ?>" autocomplete="off" readonly="readonly" disabled />
                            <span class="input-group-btn">
                                <button type="button" class="ChequeBtn btn btn-default btn-flat" data-toggle="modal" data-target="#myModal" contenteditable="false">
                                    <i class="fa fa-newspaper-o"></i>
                                </button>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Bank Transfer Payment</label>
                            <div class="input-group">
                                <input type="text" class="form-control text-right" id="BankTransferPayment" name="BankTransferPayment" placeholder="Bank Transfer Payment" value="<?php echo number_format($ToatlBankTrnPayment,2,'.',''); ?>" autocomplete="off" readonly="readonly" disabled />
                                <span class="input-group-btn">
                                    <button type="button" class="BankTrnBtn btn btn-default btn-flat" data-toggle="modal" data-target="#myModal" contenteditable="false">
                                        <i class="fa fa-share-square-o"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
  </div>




    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Invoices Details</h3>

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
                        <?php include('_partial_paymentdetails.php'); ?>
                    </div>
                    </div>
                </div>
            </div>
        </div>


</section>

<script type="text/javascript">

    function FillInvoice() {
        $('.loader').show();

        var Customer = $('#CustomerCode').val();

        var CustomerCode = "";
        var CustomerName = "";
        var Credit = 0;

        $.ajax({
            url: 'autocomplete.php',
            type: 'POST',
            data: { Customer: Customer },
            dataType: 'json',
            success: function (data) {
                jQuery(data).each(function (i, item) {
                    CustomerCode = item.CustomerCode;
                    CustomerName = item.CustomerName;
                    Credit = item.Credit;
                });
            },
            complete: function (data) {
                if (CustomerCode == "") {
                    document.querySelector('#creditamount').value = 0.00;
                }
                else {
                    document.querySelector('#creditamount').value = Credit;
                }
            }
        });

  
        //Fill details
        $.ajax({
            type: "POST",
            url: "customer_payment.php", // Name of the php files
            data: { FillTable: 'FillTable' , Customer: Customer },
            success: function (result) {
                $("#table").html(result);
                $('.loader').fadeOut();
            }
        });
    }


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
                url: "customer_payment.php",
                type: "POST",
                data: { CashPayment: CashPayment },
                success: function (result) {
                    CalculateCreditDue();
                    $('.loader').fadeOut();
                }
            });

        }
    });


    $(document).ready(function () {
        $(".CardBtn").click(function () {
            $('.loader').show();

            var $row = $(this).closest("tr");
            var RowNo = $row.find(".clsRowId").text();

            $.ajax({
                url: "customer_payment.php",
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
                url: "customer_payment.php",
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
                url: "customer_payment.php",
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


    function CalculateCreditDue() {
        $('.loader').show();

        var NetAmount = $("#hNetAmount").val() == "" ? 0 : $("#hNetAmount").val();
        var CashValue = $("#CashPayment").val() == "" ? 0 : $("#CashPayment").val();
        var CardValue = $("#CardPayment").val() == "" ? 0 : $("#CardPayment").val();
        var ChequeValue = $("#ChequePayment").val() == "" ? 0 : $("#ChequePayment").val();
        var TransferValue = $("#BankTransferPayment").val() == "" ? 0 : $("#BankTransferPayment").val();


        var Credit = (parseFloat(NetAmount) - (parseFloat(CashValue) + parseFloat(CardValue) + parseFloat(ChequeValue) + parseFloat(TransferValue))) < 0 ? 0 : (parseFloat(NetAmount) - (parseFloat(CashValue) + parseFloat(CardValue) + parseFloat(ChequeValue) + parseFloat(TransferValue)));
        $("#Credit").val((Credit).toFixed(2));

        var TotalPayment = parseFloat(CashValue) + parseFloat(CardValue) + parseFloat(ChequeValue) + parseFloat(TransferValue);
        $("#TotalPayment").val((TotalPayment).toFixed(2));

        $('.loader').fadeOut();
    }

</script>

<?php include_once('layouts/footer.php'); ?>

