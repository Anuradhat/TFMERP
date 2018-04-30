<?php
ob_start();

$page_title = 'Sub-Category Master';
require_once('includes/load.php');
UserPageAccessControle(1,'Sub Category');

$all_Subcategory = find_by_sql("call spSelectAllSubcategory();");
?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Sub-Category Master
        <small>Optional description</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Master
            </a>
        </li>
        <li class="active">Sub Category</li>
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
                        <button type="button" name="add_subcategory" onclick="window.location = 'add_subcategory.php'" class="btn btn-primary">&nbsp;&nbsp;New&nbsp;&nbsp;</button>
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
            <h3 class="box-title">Sub-Category Details</h3>

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
                                        <th>Category</th>
                                        <th>Subcategory Code</th>
                                        <th>Subcategory Description</th>
                                        <th>Commission (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_Subcategory as $scat): ?>
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <form method="post" action="edit_subcategory.php">
                                                    <button type="submit" name="subcategory" class="btn  btn-warning btn-xs glyphicon glyphicon-edit"></button>
                                                    <input type="hidden" name="SubcategoryCode" value="<?php echo remove_junk($scat['SubcategoryCode']);?>" />
                                                </form>

                                                <button type="button" name="subcategory" class="DeleteBtn btn btn-danger btn-xs glyphicon glyphicon-trash"></button>

                                            </div>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($scat['CategoryDesc']); ?>
                                        </td>
                                        <td id="RowId" class="clsRowId">
                                            <?php echo remove_junk($scat['SubcategoryCode']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk(ucfirst($scat['SubcategoryDesc'])); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($scat['Commission']); ?>
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
            message: "Do you want to delete this sub-category? This cannot be undone.",
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
                     url: 'delete_subcategory.php',
                     type: "POST",
                     data: { SubcategoryCode: RowNo },
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