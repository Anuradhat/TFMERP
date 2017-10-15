<?php
$page_title = 'Category Master - Edit Category';
require_once('includes/load.php');
page_require_level(2);
?>


<?php
if(isset($_POST['category'])){
    $p_catcode = remove_junk($db->escape($_POST['CategoryCode']));

    if(!$p_catcode){
        $session->msg("d","Missing category identification.");
        redirect('category.php');
    }
    else
    {
        $category = find_by_sp("call spSelectCategoryFromCode('{$p_catcode}');");

        if(!$category){
            $session->msg("d","Missing category details.");
            redirect('category.php');
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
        $user = "anush";

        $query  = "call spUpdateCategory('{$p_CategoryCode}','{$p_CategoryDesc}','{$date}','{$user}');";

        if($db->query($query)){
            $session->msg('s',"Category updated");
            redirect('category.php', false);
        } else {
            $session->msg('d',' Sorry failed to updated!');
            //redirect('customer.php', false);
        }

    } else{
        $session->msg("d", $errors);
        redirect('edit_category.php',false);
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


        <button type="submit" name="edit_category" class="btn btn-success btn-lg">Save  </button>
    </form>

    <div class="form-group"></div>

</section>

<?php include_once('layouts/footer.php'); ?>