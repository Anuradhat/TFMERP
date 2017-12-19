<?php
ob_start();

$page_title = 'Category Master';
require_once('includes/load.php');
page_require_level(1);

$all_Category = find_by_sql("call spSelectAllCategory();")
?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Category Master
        <small>Optional description</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Master
            </a>
        </li>
        <li class="active">Category</li>
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
                        <button type="button" name="add_category" onclick="window.location = 'add_category.php'" class="btn btn-primary">&nbsp;&nbsp;New&nbsp;&nbsp;</button>
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
            <h3 class="box-title">Category Details</h3>

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
                                        <!--<th>Department</th>-->
                                        <th>Category Code</th>
                                        <th>Category Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_Category as $cat): ?>
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <form method="post" action="edit_category.php">
                                                    <button type="submit" name="category" class="btn  btn-warning btn-xs glyphicon glyphicon-edit"></button>
                                                    <input type="hidden" name="CategoryCode" value="<?php echo remove_junk($cat['CategoryCode']);?>" />
                                                </form>
                                                <form method="post" action="delete_category.php">
                                                    <button type="submit" name="category" class="btn btn-danger btn-xs glyphicon glyphicon-trash"></button>
                                                    <input type="hidden" name="CategoryCode" value="<?php echo remove_junk($cat['CategoryCode']);?>" />
                                                </form>
                                            </div>
                                        </td>
                                        <!--<td>
                                            <?php //echo remove_junk(ucfirst($cat['DepartmentDesc'])); ?>
                                        </td>-->
                                        <td>
                                            <?php echo remove_junk($cat['CategoryCode']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk(ucfirst($cat['CategoryDesc'])); ?>
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