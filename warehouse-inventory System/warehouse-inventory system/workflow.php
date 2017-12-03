<?php
ob_start();
$page_title = 'Work-Flow Master';
require_once('includes/load.php');
page_require_level(1);

$all_workflows = find_by_sql("call spSelectAllWorkFlow();");
?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Work-Flow Master
        <small>Optional description</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Administration
            </a>
        </li>
        <li class="active">Work Flow</li>
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
                        <button type="button" name="add_department" onclick="window.location = 'add_workflow.php'" class="btn btn-primary">&nbsp;&nbsp;New&nbsp;&nbsp;</button>
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
            <h3 class="box-title">Work-Flow Details</h3>

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
                            <table id="table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Work-Flow Code</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_workflows as $wflow): ?>
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <form method="post" action="edit_workflow.php">
                                                    <button type="submit" name="workflow" class="btn  btn-warning btn-xs glyphicon glyphicon-edit"></button>
                                                    <input type="hidden" name="WorkFlowCode" value="<?php echo remove_junk($wflow['WorkFlowCode']);?>" />
                                                </form>

                                                <button type="button" name="workflow" class="DeleteBtn btn btn-danger btn-xs glyphicon glyphicon-trash"></button>
                                                <input type="hidden" name="WorkFlowCode" value="<?php echo remove_junk($wflow['WorkFlowCode']);?>" />
                                            </div>
                                        </td>
                                        <td class="clsRowId">
                                            <?php echo remove_junk($wflow['WorkFlowCode']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk(ucfirst($wflow['Description'])); ?>
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
      $(".DeleteBtn").click(function () {
          var $row = $(this).closest("tr");
          var RowNo = $row.find(".clsRowId").text().trim();

        bootbox.confirm({
            title: "Delete Confirmation",
            message: "Do you want to delete selected work-flow?This cannot be undone.",
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
                    url: 'delete_workflow.php',
                    type: "POST",
                    data: { WorkFlowCode: RowNo },
                    success: function (result) {
                        location.reload();
                    }
                });
                }
            }
        });
    });
  });
</script>
