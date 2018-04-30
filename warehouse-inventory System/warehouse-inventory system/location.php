<?php
ob_start();
$page_title = 'Location Master';
require_once('includes/load.php');
UserPageAccessControle(1,'Location');

$all_locations = find_by_sql("call spSelectAllLocations();")
?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Location Master
        <small>Optional description</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Master
            </a>
        </li>
        <li class="active">Location</li>
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
                        <button type="button" name="add_department" onclick="window.location = 'add_location.php'" class="btn btn-primary">&nbsp;&nbsp;New&nbsp;&nbsp;</button>
                        <button type="button" class="btn btn-warning" onclick="window.location = 'home.php'">Cancel  </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div id="message" class="col-md-12"><?php include('_partial_message.php'); ?></div>
    </div>


    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Location Details</h3>

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
                                        <th>Location Code</th>
                                        <th>Location Name</th>
                                        <th>Is Outlet Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_locations as $loc): ?>
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <form method="post" action="edit_location.php">
                                                    <button type="submit" name="location" class="btn  btn-warning btn-xs glyphicon glyphicon-edit"></button>
                                                    <input type="hidden" name="LocationCode" value="<?php echo remove_junk($loc['LocationCode']);?>" />
                                                </form>
                                                <button type="button" name="location" class="DeleteBtn btn btn-danger btn-xs glyphicon glyphicon-trash"></button>
                                            </div>
                                        </td>
                                        <td class="clsRowId">
                                            <?php echo remove_junk($loc['LocationCode']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk(ucfirst($loc['LocationName'])); ?>
                                        </td>
                                        <td>
                                            <?php echo $loc['LocationOutlet'] == 1 ? 'Yes' : 'No'; ?>
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
          var RowNo = $row.find(".clsRowId").text();

        bootbox.confirm({
            title: "Delete Confirmation",
            message: "Do you want to delete selected location?This cannot be undone.",
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
                    url: 'delete_location.php',
                    type: "POST",
                    data: { department: 'OK', DepartmentCode : RowNo },
                    success: function (result) {

                    }
                });
                }
            }
        });
    });
  });
</script>
