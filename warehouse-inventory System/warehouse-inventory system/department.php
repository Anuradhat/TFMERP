<?php
ob_start();
$page_title = 'Department Master';
require_once('includes/load.php');
page_require_level(1);

$all_departments = find_by_sql("call spSelectAllDepartments();")
?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Department Master
        <small>Optional description</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Master
            </a>
        </li>
        <li class="active">Department</li>
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
            <h3 class="box-title">Department Details</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <form method="get" action="add_department.php">
            <button type="submit" name="add_dep" class="btn btn-primary">Add Department</button>
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
                                        <th>Department Code</th>
                                        <th>Department Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_departments as $dep): ?>
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <form method="post" action="edit_department.php">
                                                    <button type="submit" name="department" class="btn  btn-warning btn-xs glyphicon glyphicon-edit"></button>
                                                    <input type="hidden" name="DepartmentCode" value="<?php echo remove_junk($dep['DepartmentCode']);?>" />
                                                </form>
                                                <form method="post" action="delete_department.php">
                                                    <button type="submit" name="department" class="btn btn-danger btn-xs glyphicon glyphicon-trash"></button>
                                                    <input type="hidden" name="DepartmentCode" value="<?php echo remove_junk($dep['DepartmentCode']);?>" />
                                                </form>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($dep['DepartmentCode']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk(ucfirst($dep['DepartmentDesc'])); ?>
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