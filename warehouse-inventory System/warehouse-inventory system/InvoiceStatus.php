<?php
ob_start();

$page_title = 'Invoice Status';
require_once('includes/load.php');
UserPageAccessControle(1,'Invoice Status');



if (isset($_POST['_RowNo'])) {
    $InvoiceNo = remove_junk($db->escape($_POST['_RowNo']));
    $InvoiceStatus = remove_junk($db->escape($_POST['_RowNoInvSts']));
    $all_InvoicesStatusM = find_by_sql("call spSelectAllInvoiceStatusM();");

    return include('_partial_invoicestatus.php');
}

// Update Invoice Status
if (isset($_POST['InvNo'])) {
    $InvoiceNo = remove_junk($db->escape($_POST['InvNo']));
    $InvoiceStatus = remove_junk($db->escape($_POST['InvSts']));

    try{
        $db->begin();

        $query  = "call spUpdateActiveInvoiceStatus('{$InvoiceNo}','{$InvoiceStatus}');";
        $db->query($query);

        InsertRecentActvity("Active invoice status updated manually to " .$InvoiceStatus,"Invoice No. ".$InvoiceNo);

        $db->commit();

        $flashMessages->success('Invoice status successfully updated.','InvoiceStatus.php');
    }
    catch(Exception $ex){
        $db->rollback();

        $flashMessages->error('Failed to update invoice status. '.$ex->getMessage(),'InvoiceStatus.php');
    }
}

$all_ActiveInvoices = find_by_sql("call spSelectAllActiveInvoiceStatusDetails();");

?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Invoice Status
        <small>Change invoice status</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Transaction
            </a>
        </li>
        <li class="active">Invoice</li>
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
    <!--<div class="box box-default">
        <div class="box-body">
            <div class="row">
                <div class="col-md-12 ">
                    <div class="btn-group">
                        <button type="button" name="add_bankaccounts" onclick="window.location = 'add_BankAccountDetails.php'" class="btn btn-primary">&nbsp;&nbsp;New&nbsp;&nbsp;</button>
                        <button type="button" class="btn btn-warning" onclick="window.location = 'home.php'">Cancel  </button>
                    </div>
                </div>
            </div>
        </div>
    </div>-->
    <div class="row">
        <div id="message" class="col-md-12">
            <?php include('_partial_message.php'); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?php echo display_msg($msg); ?>
        </div>
    </div>

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Invoice Status Details</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>

   
        <!-- /.box-header -->
        <div class="box-body">
            <div class="row">
                <div class="col-xs-12">

                    <div class="box">
                        <!-- /.box-header -->
                        <div class="box-body">
                            <table id="table" class="table table-bordered table-striped datatable">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>ID</th>
                                        <th>Invoice No</th>
                                        <th>Inv Date</th>
                                        <!--<th>Location</th>-->
                                        <th>Customer</th>
                                        <th>Gross Amount</th>
                                        <th>Credit Amount</th>
                                        <th>Paid Amount</th>
                                        <th>Balance</th>
                                        <th>Sales Men</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_ActiveInvoices as $ActiveInvoices): ?>
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <button type="button" name="employee" class="EditBtn btn btn-warning btn-xs glyphicon glyphicon-edit" data-toggle="modal" data-target="#myModal" contenteditable="false"></button>
                                            </div>
                                        </td>
                                        <td >
                                            <?php echo remove_junk(ucfirst($ActiveInvoices['ID'])); ?>
                                        </td>
                                        <td class="clsRowId">
                                            <?php echo remove_junk($ActiveInvoices['InvoiceNo']); ?>
                                        </td>
                                        <td >
                                            <?php echo remove_junk($ActiveInvoices['InvDate']); ?>
                                        </td>                                        
                                        <td>
                                            <?php echo remove_junk($ActiveInvoices['CustomerName']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($ActiveInvoices['GrossAmount']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($ActiveInvoices['CreditAmount']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($ActiveInvoices['PaidAmount']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($ActiveInvoices['Balance']); ?>
                                        </td>
                                        <td><?php echo remove_junk($ActiveInvoices['EmployeeName']); ?>
                                        </td>
                                        <td id="InvSts" class="clsRowInvoiceStatus">
                                            <?php echo remove_junk($ActiveInvoices['InvoiceStatusDescription']); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                </div>
            </div>
        </div>
    </div>

</section>

<script>  

    $(document).ready(function () {
        $(".EditBtn").click(function () {

            $('.loader').show();

            var $row = $(this).closest("tr");
            var RowNo = $row.find(".clsRowId").text();
            var RowNoInvSts = $row.find(".clsRowInvoiceStatus").text();

            $.ajax({
                url: "InvoiceStatus.php",
                type: "POST",
                data: { _RowNo: RowNo.trim(), _RowNoInvSts: RowNoInvSts.trim() },
                success: function (result) {
                    var modalBody = $('<div id="modalContent"></div>');
                    modalBody.append(result);
                    $("#myModalLabel").text('Invoice Status');
                    //$("#InvSts").remove();
                    $('.modal-body').html(modalBody);
                },
                complete: function (result) {
                    $('.loader').fadeOut();
                }
            });            
        });
    });
</script>

<?php include_once('layouts/footer.php'); ?>