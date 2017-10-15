<?php
$page_title = 'Subcategory Master - Edit Subcategory';
require_once('includes/load.php');
page_require_level(2);
?>


<?php
if(isset($_POST['subcategory'])){
    $p_scatcode = remove_junk($db->escape($_POST['SubcategoryCode']));

    if(!$p_scatcode){
        $session->msg("d","Missing subcategory identification.");
        redirect('subcategory.php');
    }
    else
    {
        $subcategory = find_by_sp("call spSelectSubcategoryFromCode('{$p_scatcode}');");

        if(!$subcategory){
            $session->msg("d","Missing subcategory details.");
            redirect('subcategory.php');
        }
    }
}
?>

<?php
if(isset($_POST['edit_subcategory'])){
    $req_fields = array('hSubcategoryCode','SubcategoryDesc');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_SubcategoryCode  = remove_junk($db->escape($_POST['hSubcategoryCode']));
        $p_SubcategoryDesc  = remove_junk($db->escape($_POST['SubcategoryDesc']));

        $date    = make_date();
        $user = "anush";

        $query  = "call spUpdateSubcategory('{$p_SubcategoryCode}','{$p_SubcategoryDesc}','{$date}','{$user}');";

        if($db->query($query)){
            $session->msg('s',"Subcategory updated");
            redirect('subcategory.php', false);
        } else {
            $session->msg('d',' Sorry failed to updated!');
            //redirect('customer.php', false);
        }

    } else{
        $session->msg("d", $errors);
        redirect('edit_subcategory.php',false);
    }
}

?>

<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Subcategory Master
        <small>Update Subcategory Details</small>
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
    <form method="post" action="edit_subcategory.php">
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
                            <label>Subcategory Code</label>
                            <input type="text" class="form-control" name="SubcategoryCode" placeholder="Subcategory Code" required="required" value="<?php echo remove_junk($subcategory['SubcategoryCode']);?>" readonly="readonly" disabled="disabled" />
                            <input type="hidden" name="hSubcategoryCode" value="<?php echo remove_junk($subcategory['SubcategoryCode']);?>" />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Subcategory Description</label>
                            <input type="text" class="form-control" name="SubcategoryDesc" placeholder="Subcategory Description" required="required" value="<?php echo remove_junk($subcategory['SubcategoryDesc']);?>" />
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <button type="submit" name="edit_subcategory" class="btn btn-success btn-lg">Save  </button>
    </form>

    <div class="form-group"></div>

</section>

<?php include_once('layouts/footer.php'); ?>