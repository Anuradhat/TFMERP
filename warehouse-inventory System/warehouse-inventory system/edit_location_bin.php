<?php
ob_start();
$page_title = 'Bin Master - Edit Bins';
require_once('includes/load.php');
page_require_level(2);

preventGetAction('location_bin.php');

?>


<?php
if(isset($_POST['bincode'])){
    $p_loccode = remove_junk($db->escape($_POST['locationcode']));
    $p_bincode = remove_junk($db->escape($_POST['bincode']));


    if(!$p_loccode && !$p_bincode){
        $session->msg("d","Missing location or bin identification.");
        redirect('location_bin.php');
    }
    else
    {
        $bin = find_by_sp("call spSelectBinFromLocationAndBinCode('{$p_loccode}','{$p_bincode}');");

        if(!$bin){
            $session->msg("d","Missing bin details.");
            redirect('location_bin.php');
        }
    }
}

?>

<?php
if(isset($_POST['edit_location_bin'])){
    $req_fields = array('hLocationCode','hBinCode','BinName');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_LocationCode  = remove_junk($db->escape($_POST['hLocationCode']));
        $p_BinCode  = remove_junk($db->escape($_POST['hBinCode']));
        $p_BinName  = remove_junk($db->escape($_POST['BinName']));
        $p_DefaultBin  = remove_junk(string2Boolean($db->escape($_POST['DefaultBin'])));

        $date    = make_date();
        $user = current_user();

        $query  = "call spUpdateBin('{$p_LocationCode}','{$p_BinCode}','{$p_BinName}',{$p_DefaultBin},'{$user['username']}');";

        if($db->query($query)){
            $session->msg('s',"Bin updated");
            redirect('location_bin.php', false);
        } else {
            $session->msg('d',' Sorry failed to update!');
            redirect('location_bin.php', false);
        }

    } else{
        $session->msg("d", $errors);
        redirect('location_bin.php',false);
    }
}


?>

<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Bin Master
        <small>Update Bin Details</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Item
            </a>
        </li>
        <li class="active">Stock Bin</li>
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
    <form method="post" action="edit_location_bin.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="edit_location_bin" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'location_bin.php'">Cancel  </button>
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Location Code</label>
                            <input type="text" class="form-control" name="LocationCode" placeholder="Location Code" required="required" value="<?php echo remove_junk($bin['LocationCode']);?>" readonly="readonly" disabled="disabled" />
                            <input type="hidden" name="hLocationCode" value="<?php echo remove_junk($bin['LocationCode']);?>" />

                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Bin Code</label>
                            <input type="text" class="form-control" name="BinCode" placeholder="Location Code" required="required" value="<?php echo remove_junk($bin['BinCode']);?>" readonly="readonly" disabled="disabled" />
                            <input type="hidden" name="hBinCode" value="<?php echo remove_junk($bin['BinCode']);?>" />
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Bin Name</label>
                            <input type="text" class="form-control" name="BinName" placeholder="Change Bin Name" required="required" value="<?php echo remove_junk($bin['BinDesc']);?>" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group checkbox">
                            <label>
                                <input type="checkbox" class="form-check-input" name="DefaultBin" <?php if(remove_junk($bin['DefaultBin']==="1")): echo "checked"; endif;?> />
                                Default Bin
                            </label>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>

</section>

<?php include_once('layouts/footer.php'); ?>