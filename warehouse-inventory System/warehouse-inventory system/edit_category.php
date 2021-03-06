<?php
ob_start();

$page_title = 'Category Master - Edit Category';
require_once('includes/load.php');
page_require_level(2);

preventGetAction('category.php');

//$all_departments = find_by_sql("call spSelectAllDepartments();")
?>


<?php
if(isset($_POST['category'])){
    $p_catcode = remove_junk($db->escape($_POST['CategoryCode']));

    if(!$p_catcode){
        $flashMessages->warning('Missing category identification.','category.php');
    }
    else
    {
        $category = find_by_sp("call spSelectCategoryFromCode('{$p_catcode}');");

        if(!$category){
            $flashMessages->warning('Missing category details.','category.php');
        }
    }
}
?>

<?php
if(isset($_POST['edit_category'])){
    $req_fields = array('hCategoryCode','CategoryDesc');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_CategoryCode  = remove_junk($db->escape($_POST['hCategoryCode']));
        $p_CategoryDesc  = remove_junk($db->escape($_POST['CategoryDesc']));

        $date    = make_date();
        $user =  current_user();

        $query  = "call spUpdateCategory('{$p_CategoryCode}','{$p_CategoryDesc}','{$date}','{$user["username"]}');";

        if($db->query($query)){
            InsertRecentActvity("Category updated","Reference No. ".$p_CategoryCode);

            $flashMessages->success('Category updated','category.php');
        } else {
            $flashMessages->error('Sorry failed to updated!','category.php');
        }

    } else{
        $flashMessages->warning($errors,'edit_category.php');
    }
}

?>

<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Category Master
        <small>Update Category Details</small>
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
    <form method="post" action="edit_category.php">

        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="edit_category" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'category.php'">Cancel  </button>
                        </div>
                    </div>
                </div>
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
                            <select class="form-control select2" name="Department" placeholder="Select Department" required="required" readonly="readonly" disabled="disabled">
                               <option value="">Select Department</option>
                                <?php  //foreach ($all_departments as $dep): ?>
                                <option value="<?php //echo $dep['DepartmentCode'] ?>"  <?php //if($dep['DepartmentCode'] === $category['DepartmentCode']): echo "selected"; endif; ?> ><?php //echo $dep['DepartmentDesc'] ?>
                                </option><?php //endforeach; ?>
                            </select>
                        </div>-->
                        <div class="form-group">
                            <label>Category Code</label>
                            <input type="text" class="form-control" name="CategoryCode" placeholder="Category Code" required="required" value="<?php echo remove_junk($category['CategoryCode']);?>" readonly="readonly" disabled="disabled" />
                            <input type="hidden" name="hCategoryCode" value="<?php echo remove_junk($category['CategoryCode']);?>" />
                        </div>
                        
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Category Description</label>
                            <input type="text" class="form-control" name="CategoryDesc" placeholder="Category Description" required="required" value="<?php echo remove_junk($category['CategoryDesc']);?>" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

</section>

<?php include_once('layouts/footer.php'); ?>