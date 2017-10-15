<?php
$page_title = 'Subcategory Master';
require_once('includes/load.php');
page_require_level(1);

$all_Subcategory = find_by_sql("call spSelectAllSubcategory();")
?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Subcategory Master
        <small>Optional description</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Master
            </a>
        </li>
        <li class="active">Subcategory</li>
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
        <div class="box-header with-border">
            <h3 class="box-title">Subcategory Details</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <form method="get" action="add_subcategory.php">
            <button type="submit" name="add_scat" class="btn btn-primary">Add Subcategory</button>
        </form>
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
                                                <form method="post" action="delete_subcategory.php">
                                                    <button type="submit" name="subcategory" class="btn btn-danger btn-xs glyphicon glyphicon-trash"></button>
                                                    <input type="hidden" name="SubcategoryCode" value="<?php echo remove_junk($scat['SubcategoryCode']);?>" />
                                                </form>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($scat['CategoryDesc']); ?>
                                        </td>
                                        <td>
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

<?php include_once('layouts/footer.php'); ?>