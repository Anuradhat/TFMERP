<?php
ob_start();

$page_title = 'Bin Master - New Bin';
require_once('includes/load.php');
page_require_level(2);

$all_Location = find_by_sql("call spSelectAllLocations();");

?>

<?php
if(isset($_POST['add_location_bin'])){
    $req_fields = array('LocationCode','BinCode','BinName');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_LocationCode  = remove_junk($db->escape($_POST['LocationCode']));
        $p_BinCode  = remove_junk($db->escape($_POST['BinCode']));
        $p_BinName  = remove_junk($db->escape($_POST['BinName']));
        
        $date    = make_date();
        $user = current_user();        

        try
        {
            
            $bin_count = find_by_sp("call spSelectBinFromLocationAndBinCode('{$p_LocationCode}','{$p_BinCode}');");

            if($bin_count)
            {
                $flashMessages->warning('This Bin code exists in the system.','add_location_bin.php');
            }

            $db->begin();

            $query  = "call spInsertBin('{$p_LocationCode}','{$p_BinCode}','{$p_BinName}','{$user['username']}');";

            if($db->query($query)){
                InsertRecentActvity("Bin Created","Reference No. ".$p_BinCode);


                $db->commit();

                $flashMessages->success('Bin Created.','add_location_bin.php');

            } else {
                $db->rollback();
         
                $flashMessages->error('Sorry failed to create!','add_location_bin.php');
            }
        }
        catch(Exception $ex)
        {
            $db->rollback();
            $flashMessages->error('Sorry failed to create! '.$ex->getMessage(),'add_location_bin.php');
        }
    }
    else{
        $flashMessages->warning($errors,'add_location_bin.php');
    }
}

?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Location Master
        <small>Enter New Location Details</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Items
            </a>
        </li>
        <li class="active">Stock Bins</li>
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
    <form method="post" action="add_location_bin.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="add_location_bin" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'location_bin.php'">Cancel  </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div id="message" class="col-md-12"><?php include('_partial_message.php'); ?>
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
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Location</label>
                            <select class="form-control select2" name="LocationCode" id="LocationCode" required="required">
                                <option value="">Select Location</option><?php foreach($all_Location as $allLocation): ?>
                                <option value=<?php echo remove_junk($allLocation['LocationCode']); ?>><?php echo remove_junk($allLocation['LocationName']); ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Bin Code</label>
                            <input type="text" class="form-control" name="BinCode" placeholder="Enter Bin Code" required="required"/>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Bin Name</label>
                            <input type="text" class="form-control" name="BinName" placeholder="Enter Bin Name" required="required" />
                        </div>
                    </div>
                </div>
            </div>

            <!--<div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group checkbox">
                            <label class="form-check-label">
                                <input type="checkbox" name="LocationOutlet" class="form-check-input" />
                                Outlet Location
                            </label>
                        </div>
                    </div>
                </div>
            </div>-->
        </div>
    </form>
</section>

<?php include_once('layouts/footer.php'); ?>