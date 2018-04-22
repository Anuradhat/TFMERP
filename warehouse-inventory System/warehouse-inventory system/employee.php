<?php
ob_start();

$page_title = 'Employee';
require_once('includes/load.php');
UserPageAccessControle(1,'Employee Details');

$all_Employee = find_by_sql("call spSelectAllEmployee();")
?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Employee Details
        <small>Create employees</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Employee
            </a>
        </li>
        <li class="active">Employee</li>
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
                        <button type="button" name="add_employee" onclick="window.location = 'add_employee.php'" class="btn btn-primary">&nbsp;&nbsp;New&nbsp;&nbsp;</button>
                        <button type="button" class="btn btn-warning" onclick="window.location = 'home.php'">Cancel  </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?php echo display_msg($msg); ?>
        </div>
    </div>

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Employee Details</h3>

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
                                        <th>EPF</th>
                                        <th>Name</th>
                                        <th>TEL:</th>
                                        <th>Email</th>
                                        <th>Designation</th>
                                        <th>Department </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_Employee as $empl): ?>
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <form method="post" action="edit_employee.php">
                                                    <button type="submit" name="employee" class="btn  btn-warning btn-xs glyphicon glyphicon-edit"></button>
                                                    <input type="hidden" name="EpfNumber" value="<?php echo remove_junk($empl['EpfNumber']);?>" />
                                                </form>
                                                <!--<form method="post" action="delete_employee.php">-->
                                                <button type="button" name="employee" class="DeleteBtn btn btn-danger btn-xs glyphicon glyphicon-trash"></button>
                                                    
                                                <!--</form>-->
                                            </div>
                                        </td>
                                        <td class="clsRowId">
                                            <?php echo remove_junk(ucfirst($empl['EpfNumber'])); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($empl['EmployeeName']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($empl['TelephoneNo']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($empl['Email']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($empl['DesignationName']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($empl['DepartmentName']); ?>
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
            message: "Do you want to delete this employee? This cannot be undo.",
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
                        url: 'delete_employee.php',
                     type: "POST",
                     data: { EpfNumber: RowNo },
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