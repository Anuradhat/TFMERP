<?php
ob_start();

$page_title = 'Bank Account Details';
require_once('includes/load.php');
UserPageAccessControle(1,'Bank Accounts');

$all_BankAccounts = find_by_sql("call spSelectAllBankAccountDetails();");
?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Bank Account Details
        <small>Create bank accounts</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Bank
            </a>
        </li>
        <li class="active">Bank Accounts</li>
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
                        <button type="button" name="add_bankaccounts" onclick="window.location = 'add_BankAccountDetails.php'" class="btn btn-primary">&nbsp;&nbsp;New&nbsp;&nbsp;</button>
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

    <div class="row">
        <div class="col-md-12">
            <?php echo display_msg($msg); ?>
        </div>
    </div>

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Bank Account Details</h3>

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
                                        <th>Bank Code</th>
                                        <th>Bank Name</th>
                                        <th>Branch Code</th>
                                        <th>Branch Name</th>
                                        <th>Account Type</th>
                                        <th>Account Type Description</th>
                                        <th>Account No</th>
                                        <th>Account Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_BankAccounts as $accouts): ?>
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <button type="button" name="employee" class="DeleteBtn btn btn-danger btn-xs glyphicon glyphicon-trash"></button>
                                            </div>
                                        </td>
                                        <td class="clsRowId">
                                            <?php echo remove_junk(ucfirst($accouts['ID'])); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($accouts['BankCode']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($accouts['BankName']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($accouts['BranchCode']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($accouts['BranchName']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($accouts['AccountType']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($accouts['AccountTypeDescription']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($accouts['AccountNo']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($accouts['AccountName']); ?>
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
      $(".DeleteBtn").click(function () {
          var $row = $(this).closest("tr");
          var RowNo = $row.find(".clsRowId").text().trim();

        bootbox.confirm({
            title: "Delete Confirmation",
            message: "Do you want to delete this account? This cannot be undo.",
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
                    $('.loader').show();

                    $.ajax({
                        url: 'delete_bank_account_details.php',
                     type: "POST",
                     data: { AccountID: RowNo },
                     success: function (result) {
                        location.reload();
                    },
                    complete: function (result) {
                        $('.loader').fadeOut();
                    }
                });
               }
            }
        });
    });
  });
</script>

<?php include_once('layouts/footer.php'); ?>