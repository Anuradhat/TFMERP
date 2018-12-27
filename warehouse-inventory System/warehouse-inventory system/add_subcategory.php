<?php
$page_title = 'Sub-category Master - New Sub-category';
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
        $p_Commission       = remove_junk(string2Value($db->escape($_POST['Commission'])));

        $scatcode = $p_CategoryCode.$p_SubcategoryCode;

        $date    = make_date();
        $user =  current_user();

        $scat_count = find_by_sp("call spSelectSubcategoryFromCode('{$scatcode}');");

        if($scat_count)
        {
            $flashMessages->warning('This subcategory code exist in the system.','add_subcategory.php');
        }

 
        $query  = "call spInsertSubcategory('{$p_CategoryCode}','{$scatcode}','{$p_SubcategoryDesc}',{$p_Commission},'{$date}','{$user["username"]}');";

        if($db->query($query)){
            InsertRecentActvity("Subcategory added","Reference No. ".$p_SubcategoryCode);

            $flashMessages->success('Subcategory added','add_subcategory.php');
        } else {
            $flashMessages->error('Sorry failed to added!','subcategory.php');
        }

    } else{
        $flashMessages->error($errors,'add_subcategory.php');
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
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="add_subcategory" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'subcategory.php'">Cancel  </button>
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
                            <select class="form-control select2" name="Category" placeholder="Select Category" required="required">
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
                            <label >Commission (<output class="inline" for="fader" id="rate">0</output>%)</label>
                            <input type="text" class="form-control" name="Commission" placeholder="Commission (%)"/>
                            <!--<input type="range" class="form-control" data-slider-id="blue" min="0" max="100" value="0" step="1" data-slider-tooltip="show" name="Commission" placeholder="Commission (%)" oninput="outputUpdate(value)" />-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

<?php include_once('layouts/footer.php'); ?>

<script>
    function outputUpdate(vol) {
        document.querySelector('#rate').value = vol;
    }
</script>