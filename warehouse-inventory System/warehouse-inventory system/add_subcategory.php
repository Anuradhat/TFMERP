<?php
$page_title = 'Subcategory Master - New Subcategory';
require_once('includes/load.php');
page_require_level(2);

$all_Category = find_by_sql("call spSelectAllCategory();")
?>

<?php
if(isset($_POST['add_subcategory'])){
    $req_fields = array('SubcategoryCode','SubcategoryDesc');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_CategoryCode     = remove_junk($db->escape($_POST['Category']));
        $p_SubcategoryCode  = remove_junk($db->escape($_POST['SubcategoryCode']));
        $p_SubcategoryDesc  = remove_junk($db->escape($_POST['SubcategoryDesc']));
        $p_Commission       = remove_junk($db->escape($_POST['Commission']));

        $scatcode = $p_CategoryCode.$p_SubcategoryCode;

        $date    = make_date();
        $user = "anush";

        $scat_count = find_by_sp("call spSelectSubcategoryFromCode('{$scatcode}');");

        if($scat_count)
        {
            $session->msg("d", "This subcategory code exist in the system.");
            redirect('add_subcategory.php',false);
        }

 
        $query  = "call spInsertSubcategory('{$p_CategoryCode}','{$scatcode}','{$p_SubcategoryDesc}',{$p_Commission},'{$date}','{$user}');";

        if($db->query($query)){
            $session->msg('s',"Subcategory added ");
            redirect('add_subcategory.php', false);
        } else {
            $session->msg('d',' Sorry failed to added!');
            redirect('subcategory.php', false);
        }

    } else{
        $session->msg("d", $errors);
        redirect('add_subcategory.php',false);
    }
}

?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Subcategory Master
        <small>Enter New Subcategory Details</small>
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
    <form method="post" action="add_subcategory.php">
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
                            <label>Category</label>
                            <select class="form-control" name="Category" placeholder="Select Category" required="required">
                                <option value="">Select Category</option><?php  foreach ($all_Category as $cat): ?>
                                <option value="<?php echo $cat['CategoryCode'] ?>"><?php echo $cat['CategoryDesc'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Subcategory Description</label>
                            <input type="text" class="form-control" name="SubcategoryDesc" placeholder="Subcategory Description" required="required" />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Subcategory Code</label>
                            <input type="text" class="form-control" name="SubcategoryCode" placeholder="Subcategory Code" required="required" />
                        </div>

                        <div class="form-group">
                            <label>Commission (%)</label>
                            <input type="number" class="form-control" name="Commission" placeholder="Commission (%)"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <button type="submit" name="add_subcategory" class="btn btn-success btn-lg">Save  </button>
    </form>

    <div class="form-group"></div>

</section>

<?php include_once('layouts/footer.php'); ?>