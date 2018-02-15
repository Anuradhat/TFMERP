<?php
ob_start();

$page_title = 'Invoice - Payment';
require_once('includes/load.php');
page_require_level(2);

$arr_header = array();
$arr_item = array();
$arr_card = array();
$arr_cheque = array();
$_cash = 0;


if($_SESSION['header'] != null) $arr_header = $_SESSION['header'];
if($_SESSION['details'] != null) $arr_item = $_SESSION['details'];
if($_SESSION['card'] != null) $arr_card = $_SESSION['card'];
if($_SESSION['cheque'] != null) $arr_cheque = $_SESSION['cheque'];
if($_SESSION['Cash'] != null) $_cash = $_SESSION['Cash'];

$Location = find_by_sp("call spSelectLocationFromCode('{$arr_header['LocationCode']}');");
$Customer = find_by_sp("call spSelectCustomerFromCode('{$arr_header['CustomerCode']}');");
$Salesman = find_by_sp("call spSelectEmployeeFromEpf('{$arr_header['SalesmanCode']}');");

?>


<?php

if (isset($_POST['CardPayment'])) {

    return include('_partial_addcreditcard.php');
}

if (isset($_POST['ChequePayment'])) {

    return include('_partial_addcheque.php');
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
            height: 600px; /* Should be removed. Only for demonstration */
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

        .number {
            width: 50px;
            height: 50px;
            background: #80c0ff;
            color: #FFF;
            font-size: 12px;
            text-align: center;
            border-radius: 50%;
            float: left;
            padding-top: 5px;
            margin-left: 10px;
        }

        .step {
            width: 100%;
            height: auto;
            color: #80c0ff;
            padding-left: 1%;
            padding-top: 20px;
            float: left;
            position: relative;
        }

            .step:after {
                content: '';
                width: 80%;
                height: 3px;
                background: #80c0ff;
                position: absolute;
                bottom: 0;
                right: 0;
                border-top-left-radius: 10px;
                border-bottom-left-radius: 10px;
            }

        .title {
            float: left;
            width: 70%;
            margin-left: 3%;
            font-size: 1.2em;
            font-weight: 200;
            margin-top: -5px;
        }

            .title t1 {
                font-weight: 200;
            }
    </style>
</section>

<!-- Main content -->
<section class="content">
    <!-- Your Page Content Here -->

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
                                    <input type="text" class="form-control text-right" id="CashPayment" name="CashPayment" placeholder="Cash Payment" value="<?php echo number_format($_cash,2,'.',''); ?>" autocomplete="off" />
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
                                <label>Credit</label>
                                <div class="input-group">
                                    <input type="text" class="form-control text-right" name="Credit" id="Credit" placeholder="Credit Value" autocomplete="off" value="<?php  echo number_format(($arr_header['NetAmount'] - ($_cash + $TotalCardValue + $TotalChequeValue)),2,'.','') ?>" readonly="readonly" disabled />
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
                            <?php  foreach($arr_item  as &$value) { ?>
                            <?php echo substr($value[1],6)."..."."&nbsp;&nbsp;&nbsp;".$value[4]."&nbsp;&nbsp;&nbsp;".number_format(($value[5] == null ? 0 : $value[3] * $value[4]),2)."<br/>"?>
                            <?php  } ?>
                            <hr class="divider" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include_once('layouts/footer.php'); ?>

<script>
    $(document).ready(function () {
        $(".CardBtn").click(function () {

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
                }
            });


        });
    });

    $(document).ready(function () {
        $(".ChequeBtn").click(function () {

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
                }
            });


        });
    });


    $("#CashPayment").change(function () {
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
                }
            });

        }
    });


    function CalculateCreditDue() {
        var NetAmount = $("#hNetAmount").val() == "" ? 0 : $("#hNetAmount").val();
        var CashValue = $("#CashPayment").val() == "" ? 0 : $("#CashPayment").val();
        var CardValue = $("#CardPayment").val() == "" ? 0 : $("#CardPayment").val();
        var ChequeValue = $("#ChequePayment").val() == "" ? 0 : $("#ChequePayment").val();

        $("#Credit").val((parseFloat(NetAmount) - (parseFloat(CashValue) + parseFloat(CardValue) + parseFloat(ChequeValue))).toFixed(2));
    }
</script>