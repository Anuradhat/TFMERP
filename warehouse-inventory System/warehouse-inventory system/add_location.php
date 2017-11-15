<?php
ob_start();

$page_title = 'Location Master - New Location';
require_once('includes/load.php');
page_require_level(2);
?>

<?php
if(isset($_POST['add_location'])){
    $req_fields = array('LocationName');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_LocationName  = remove_junk($db->escape($_POST['LocationName']));
        $p_LocationOutlet  = remove_junk(string2Boolean($db->escape($_POST['LocationOutlet'])));

        $date    = make_date();
        $user = "anush";


        $p_LocationCode  = autoGenerateNumber('tfmLocationM',1);

        try
        {
            $db->begin();

            $loc_count = find_by_sp("call spSelectLocationFromCode('{$p_LocationCode}');");

            if($loc_count)
            {
                $session->msg("d", "This location code exist in the system.");
                redirect('add_location.php',false);
            }

            $query  = "call spInsertLocation('{$p_LocationCode}','{$p_LocationName}',{$p_LocationOutlet},'{$date}','{$user}');";

            if($db->query($query)){
                $db->commit();
                $session->msg('s',"Location added ");
                redirect('add_location.php', false);
            } else {
                $db->rollback();
                $session->msg('d',' Sorry failed to added!');
                redirect('location.php', false);
            }
        }
        catch(Exception $ex)
        {
            $db->rollback();

            $session->msg('d',' Sorry failed to added!');
            redirect('location.php', false);
        }
    }
    else{
        $session->msg("d", $errors);
        redirect('add_location.php',false);
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
                <i class="fa fa-dashboard"></i>Master
            </a>
        </li>
        <li class="active">Location</li>
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
    <form method="post" action="add_location.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="add_location" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'location.php'">Cancel  </button>
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
                            <label>Location Code</label>
                            <input type="text" class="form-control" name="LocationCode" placeholder="Code will generate after save" readonly="readonly" disabled="disabled" />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Location Name</label>
                            <input type="text" class="form-control" name="LocationName" placeholder="Location Name" required="required" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="box-body">
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
            </div>
        </div>
    </form>
</section>

<?php include_once('layouts/footer.php'); ?>