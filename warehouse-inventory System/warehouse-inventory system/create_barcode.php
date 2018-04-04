<?php
ob_start();

session_set_cookie_params(0);
session_start();

$page_title = 'Create Barcode';
require_once('includes/load.php');

// Checkin What level user has permission to view this page
page_require_level(2);


if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && !$flashMessages->hasErrors() && !$flashMessages->hasWarnings())
{
    unset($_SESSION['Transaction']);
    unset($_SESSION['TransactionNo']);
}


?>

<?php



if (isset($_POST['Transaction'])) {

    $Transaction = remove_junk($db->escape($_POST['Transaction']));
    $TransactionNo = remove_junk($db->escape($_POST['TransactionNo']));

    $_SESSION['Transaction'] = $Transaction;
    $_SESSION['TransactionNo'] = $TransactionNo;

    echo 'ok';
}


?>

<?php include_once('layouts/header.php'); ?>

<section class="content-header">
    <h1>
       Create Barcode
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Other
            </a>
        </li>
        <li class="active">Barcode</li>
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
    <form method="post" action="create_barcode.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
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
                <h3 class="box-title">Barcode Printing Details</h3>

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
                            <label>Transaction</label>
                            <select class="form-control select2" style="width: 100%;" name="Transaction" id="Transaction" required="required">
                                <option value="">Select Trnsaction</option>
                                <option value="GRN">Good Recived Note</option>
                                <option value="GTN">Transfer Note</option>
                            </select>
                        </div>

                    </div>
         
                    <div class="col-md-4">
                        <div class="form-group">
                        <label>Transaction No</label>
                        <input type="text" class="form-control" name="TransactionNo" id="TransactionNo" placeholder="Transaction No" autocomplete="off" />
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="form-group pull-right">
                            <label>&nbsp;</label><br>
                            <button type="submit" class="btn btn-info" name="create_po" onclick="AddItem(this, event);" value="print">&nbsp;&nbsp;&nbsp;Print&nbsp;&nbsp;&nbsp;</button>
                            <a href="barcode.php" class="btn btn-info" id="printbarcode" style="display:none" mce_href="barcode.php" >Download Text File</a>
                        
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </form>


</section>

<script type="text/javascript">
    function AddItem(ctrl, event) {
        event.preventDefault();
        $('.loader').show();

        var Transaction = $("#Transaction").val();
        var TransactionNo = $('#TransactionNo').val();

        if (Transaction == "") {
            bootbox.alert('Please select the transaction.');
            $('.loader').fadeOut();
        }
        else if (TransactionNo == "") {
            bootbox.alert('Please select the transaction no.');
            $('.loader').fadeOut();
        }
        else {

            $.ajax({
                url: 'create_barcode.php',
                type: 'POST',
                data: { Transaction: Transaction, TransactionNo: TransactionNo },
                success: function (data) {
                    $("#printbarcode").show();
                    $('.loader').fadeOut();
                }
            });

        }
    }

   

</script>

<?php include_once('layouts/footer.php'); ?>

