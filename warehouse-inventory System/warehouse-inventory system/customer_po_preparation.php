<?php
ob_start();

session_set_cookie_params(0);
session_start();

$page_title = 'CPO Preparation';
require_once('includes/load.php');
UserPageAccessControle(1,'Customer PO Preparation');

$all_CustomerPO = find_by_sql("call spSelectAllCustomerPoOpen();");
?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Customer PO Preparation
        <small>CPO Preparation</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Transaction
            </a>
        </li>
        <li class="active">Customer Purchase Order</li>
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
    <div class="row">
        <div class="col-md-12">
            <div id="message">
                <?php 
                include('_partial_message.php'); 
                ?>
            </div>
        </div>
    </div>

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Customer Purchase Order Details</h3>

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
                                        <th>CPO</th>
                                        <th>Quotation NO</th>
                                        <th>CPO Date</th>
                                        <th>Reference No</th>
                                        <th>Customer Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_CustomerPO as $CustomerPO): ?>
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <form method="post" action="customer_po_itemscan.php" >
                                                    <button type="submit" name="ScanPO" class="btn  btn-warning btn-xs glyphicon glyphicon-edit"></button>
                                                    <input type="hidden" name="CPONo" value="<?php echo remove_junk($CustomerPO['CusPoNo']);?>" />
                                                    <input type="hidden" name="SoNo" value="<?php echo remove_junk($CustomerPO['SoNo']);?>" />
                                                    <input type="hidden" name="CusPoDate" value="<?php echo remove_junk($CustomerPO['CusPoDate']);?>" />
                                                    <input type="hidden" name="ReferenceNo" value="<?php echo remove_junk($CustomerPO['ReferenceNo']);?>" />
                                                    <input type="hidden" name="CustomerName" value="<?php echo remove_junk($CustomerPO['CustomerName']);?>" />
                                                </form>
                                            </div>
                                        </td>
                                        <td class="clsCusPoNo">
                                            <?php echo remove_junk(ucfirst($CustomerPO['CusPoNo'])); ?>
                                        </td>
                                        <td class="clsSoNo">
                                            <?php echo remove_junk($CustomerPO['SoNo']); ?>
                                        </td>
                                        <td class="clsCusPoDate">
                                            <?php echo remove_junk($CustomerPO['CusPoDate']); ?>
                                        </td>
                                        <td class="clsReferenceNo">
                                            <?php echo remove_junk($CustomerPO['ReferenceNo']); ?>
                                        </td>
                                        <td class="clsCustomerName">
                                            <?php echo remove_junk($CustomerPO['CustomerName']); ?>
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
   //$(document).ready(function () {
   //   $(".DeleteBtn").click(function () {
   //       var $row = $(this).closest("tr");
   //       var RowNo = $row.find(".clsCusPoNo").text().trim();

   //     bootbox.confirm({
   //         title: "Delete Confirmation",
   //         message: "Do you want to delete this designation? This cannot be undo.",
   //         buttons: {
   //             cancel: {
   //                 label: '<i class="fa fa-times"></i> Cancel'
   //             },
   //             confirm: {
   //                 label: '<i class="fa fa-check"></i> Confirm'
   //             }
   //         },
   //         callback: function (result) {
   //             if (result === true) {
   //                 $('.loader').show();

   //                 $.ajax({
   //                     url: 'delete_employee_designation.php',
   //                  type: "POST",
   //                  data: { DesignationCode: RowNo },
   //                  success: function (result) {
   //                     location.reload();
   //                 },
   //                 complete: function (result) {
   //                     $('.loader').fadeOut();
   //                 }
   //             });
   //            }
   //         }
   //     });
   // });
   //});


   //$(document).ready(function () {
   //    $(".EditCpo").click(function () {
   //        var $row = $(this).closest("tr");
   //        var pCusPoNo = $row.find(".clsCusPoNo").text().trim();
   //        var pclsSoNo = $row.find(".clsSoNo").text().trim();
   //        var pclsCusPoDate = $row.find(".clsCusPoDate").text().trim();
   //        var pclsReferenceNo = $row.find(".clsReferenceNo").text().trim();
   //        var pclsCustomerName = $row.find(".clsCustomerName").text().trim();

   //        $('.loader').show();

   //        $.ajax({
   //            url: 'customer_po_itemscan.php',
   //            type: "POST",
   //            data: { _CusPoNo: RowNo },
   //            success: function (result) {
   //                location.reload();
   //            },
   //            complete: function (result) {
   //                $('.loader').fadeOut();
   //            }
   //        });
   //    });
   //});
</script>

<?php include_once('layouts/footer.php'); ?>