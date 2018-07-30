<?php
ob_start();

$page_title = 'Pending Approvals';
require_once('includes/load.php');
page_require_level(1);

preventGetAction('home.php');

$TransactionCode = $_GET["TransactionCode"];
$current_user = current_user();
$EmployeeCode = $current_user["EmployeeCode"];

if(isset($current_user) && isset($TransactionCode))
{
    if($TransactionCode == "all")
        $pending_approvals = check_pending_approvels();
    else
        $pending_approvals = check_pending_approvels("{$TransactionCode}");
}
else
 redirect('home.php', false);

?>

<?php
if(isset($_POST['ReferenceNo']))
{
    $ReferenceNo = remove_junk($db->escape($_POST['ReferenceNo']));
    $TransactionCode = remove_junk($db->escape($_POST['TransactionCode']));
    $Level = remove_junk($db->escape($_POST['Level']));

    if ($TransactionCode == "001")
    {
        $_SESSION['PurchaseOrder'] = $ReferenceNo;
        $_SESSION['Level'] = $Level;
    }
    else if ($TransactionCode == "004")
    {
        $_SESSION['SalesOrder'] = $ReferenceNo;
        $_SESSION['Level'] = $Level;
    }
    else if ($TransactionCode == "005")
    {
        $_SESSION['CustomerPO'] = $ReferenceNo;
        $_SESSION['Level'] = $Level;
    }

    $_SESSION['redirect'] = true;
    echo 'redirect';
}

if (isset($_POST['Approved']) && isset($_POST['TransactionCode']) && isset($_POST['RefNo']) && isset($_POST['Level'])) {
    $Approved = remove_junk($db->escape($_POST['Approved']));
    $TransactionCode = remove_junk($db->escape($_POST['TransactionCode']));
    $ReferenceNo = remove_junk($db->escape($_POST['RefNo']));
    $Level = remove_junk($db->escape($_POST['Level']));

    //Insert approval details
    if($Approved == 1)
    {
        $query  = "call spTransactionApproved('{$TransactionCode}','{$ReferenceNo}',{$Level});";
        $db->query($query);
    }
    else
    {
        $query  = "call spTransactionReject('{$TransactionCode}','{$ReferenceNo}',{$Level});";
        $db->query($query);
    }

    echo 'OK';
}
?>



<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Pending Approvals
        <small>Optional description</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Transaction
            </a>
        </li>
        <li class="active">Pending Approvals</li>
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
    <div class="box box-default">
        <div class="box-body">
            <div class="row">
                <div class="col-md-12 ">
                    <div class="btn-group">
                        <button type="button" class="btn btn-info" onclick="window.location.reload(true);">Refresh  </button>
                        <button type="button" class="btn btn-warning" onclick="window.location = 'home.php'">Cancel  </button>
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

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Transaction Details</h3>

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
                                        <th>View</th>
                                        <th>Transaction</th>
                                        <th style="display: none;">Transaction Code</th>
                                        <th>Work-Flow</th>
                                        <th>Reference Number</th>
                                        <th>Supplier/Customer</th>
                                        <th>Approval Level</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_approvals as $approvals): ?>
                                    <tr>
                                        <td>
                                            <!--<button type="submit" name="Approved" class="btn  btn-primary btn-sm glyphicon glyphicon-list-alt"></button>-->
                                            <button type="button" class="EditBtn btn btn-warning btn-sm glyphicon glyphicon-edit" <?php if($approvals['TrnsactionCode'] != '004' && $approvals['TrnsactionCode'] != '001' && $approvals['TrnsactionCode'] != '005' ) echo "disabled" ?>></button>
                                        </td>
                                        <td class="clsTransaction">
                                            <?php echo remove_junk($approvals['TransactionName']); ?>
                                        </td>
                                        <td style="display: none;" class="clsTransactionCode">
                                            <?php echo remove_junk($approvals['TrnsactionCode']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($approvals['Description']); ?>
                                        </td>
                                        <td class="clsRefNo">
                                            <?php echo remove_junk($approvals['ReferenceNo']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk(ucfirst($approvals['RefName'])); ?>
                                        </td>
                                        <td class="clsLevel">
                                            <?php echo remove_junk(ucfirst($approvals['Level'])); ?>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <button type="submit" name="Approved" class="ApprovedBtn btn  btn-success btn-sm btn-flat">Approve</button>
                                                <button type="submit" name="Reject" class="RejectBtn btn btn-danger btn-sm btn-flat">&nbsp;&nbsp;Reject&nbsp;&nbsp;</button>
                                            </div>
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

<?php include_once('layouts/footer.php'); ?>

<script>
   $(document).ready(function () {
       $(".ApprovedBtn").click(function () {
           var $row = $(this).closest("tr");

          var RefNo = $row.find(".clsRefNo").text().trim();
          var Level = $row.find(".clsLevel").text().trim();
          var TransactionCode = $row.find(".clsTransactionCode").text().trim();

        bootbox.confirm({
            title: "Confirmation",
            message: "Do you want to approve this transaction?",
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> Cancel'
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> Confirm'
                }
            },
            callback: function (result) {
                if (result === true) {
                    $.ajax({
                    url: 'approval_task.php',
                    type: "POST",
                    data: {Approved: 1,TransactionCode: TransactionCode, RefNo: RefNo, Level: Level},
                    success: function (result) {
                        location.reload();
                    }
                });
                }
            }
        });
    });
   });

   $(document).ready(function () {
       $(".RejectBtn").click(function () {
           var $row = $(this).closest("tr");

           var RefNo = $row.find(".clsRefNo").text().trim();
           var Level = $row.find(".clsLevel").text().trim();
           var TransactionCode = $row.find(".clsTransactionCode").text().trim();

           bootbox.confirm({
               title: "Confirmation",
               message: "Do you want to reject this transaction?  <br><br> <input type=text class=form-control name=comment id=comment placeholder=Comments />",
               buttons: {
                   cancel: {
                       label: '<i class="fa fa-times"></i> Cancel'
                   },
                   confirm: {
                       label: '<i class="fa fa-check"></i> Confirm'
                   }
               },
               callback: function (result) {
                   if (result === true) {
                       $.ajax({
                           url: 'approval_task.php',
                           type: "POST",
                           data: { Approved: 0, TransactionCode: TransactionCode, RefNo: RefNo, Level: Level },
                           success: function (result) {
                               location.reload();
                           }
                       });
                   }
               }
           });
       });
   });


   $(document).ready(function () {
       $(".EditBtn").click(function () {
           $('.loader').show();
           var $row = $(this).closest("tr");
           var RefNo = $row.find(".clsRefNo").text().trim();
           var TranCode = $row.find(".clsTransactionCode").text().trim();
           var Level = $row.find(".clsLevel").text().trim();

           $.ajax({
               url: "approval_task.php",
               type: "POST",
               data: { ReferenceNo: RefNo, TransactionCode: TranCode, Level: Level },
               success: function (result) {
                   if (TranCode == '001')
                      window.location = 'edit_po_.php';
                   else if (TranCode == '004')
                       window.location = 'edit_salesorder_.php';
                   else if (TranCode == '005')
                       window.location = 'edit_customerpo_.php';
               },
               complete: function (result) {
                   $('.loader').fadeOut();
               }
           });


       });
   });



</script>