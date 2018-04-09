<?php
ob_start();

$page_title = 'Category Master - New Category';
require_once('includes/load.php');
page_require_level(2);

//$all_departments = find_by_sql("call spSelectAllDepartments();")

?>

<?php
if(isset($_POST['add_category'])){
    $req_fields = array('CategoryCode','CategoryDesc');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_DepartmentCode  = "GEN";//remove_junk($db->escape($_POST['Department']));
        $p_CategoryCode  = remove_junk($db->escape($_POST['CategoryCode']));
        $p_CategoryDesc  = remove_junk($db->escape($_POST['CategoryDesc']));

        $catcode = $p_CategoryCode;

        $date    = make_date();
        $user =  current_user();

        $cat_count = find_by_sp("call spSelectCategoryFromCode('{$catcode}');");

        if($cat_count)
        {
            $session->msg("d", "This category code exist in the system.");
            redirect('add_category.php',false);
        }

        $query  = "call spInsertCategory('{$p_DepartmentCode}','{$catcode}','{$p_CategoryDesc}','{$date}','{$user["username"]}');";

        if($db->query($query)){
            $session->msg('s',"Category added ");
            redirect('add_category.php', false);
        } else {
            $session->msg('d',' Sorry failed to added!');
            redirect('category.php', false);
        }

    } else{
        $session->msg("d", $errors);
        redirect('add_category.php',false);
    }
}

?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Category Master
        <small>Enter New Category Details</small>
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
    <form method="post" action="add_category.php">

        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="add_category" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'category.php'">Cancel  </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12"><?php echo display_msg($msg); ?>
            </div>
        </div>
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Basic Details</h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <!--<div class="form-group">
                            <label>Department</label>
                            <select class="form-control select2" name="Department" placeholder="Select Department" required="required">
                                <option value="">Select Department</option>
                                <?php  //foreach ($all_departments as $dep): ?>
                                <option value="<?php// echo $dep['DepartmentCode'] ?>"><?php //echo $dep['DepartmentDesc'] ?>
                                </option><?php //endforeach; ?>
                            </select>
                        </div>-->
                        <div class="form-group">
                            <label>Category Code</label>
                            <input type="text" class="form-control" name="CategoryCode" placeholder="Category Code" required="required" />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Category Description</label>
                            <input type="text" class="form-control" name="CategoryDesc" placeholder="Category Description" required="required" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>

</section>

<?php include_once('layouts/footer.php'); ?>