<?php
ob_start();
$page_title = 'Location Master - Edit Location';
require_once('includes/load.php');
page_require_level(2);

preventGetAction('location.php');

?>


<?php
if(isset($_POST['location'])){
    $p_loccode = remove_junk($db->escape($_POST['LocationCode']));

    if(!$p_loccode){
        $session->msg("d","Missing location identification.");
        redirect('location.php');
    }
    else
    {
        $location = find_by_sp("call spSelectLocationFromCode('{$p_loccode}');");

        if(!$location){
            $session->msg("d","Missing location details.");
            redirect('location.php');
        }
    }
}

?>

<?php
if(isset($_POST['edit_location'])){
    $req_fields = array('hLocationCode','LocationName');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_LocationCode  = remove_junk($db->escape($_POST['hLocationCode']));
        $p_LocationName  = remove_junk($db->escape($_POST['LocationName']));
        $p_LocationOutlet  = remove_junk(string2Boolean($db->escape($_POST['LocationOutlet'])));

        $date    = make_date();
        $user = "anush";

        $query  = "call spUpdateLocation('{$p_LocationCode}','{$p_LocationName}',{$p_LocationOutlet},'{$date}','{$user}');";

        if($db->query($query)){
            $session->msg('s',"Location updated");
            redirect('location.php', false);
        } else {
            $session->msg('d',' Sorry failed to updated!');
            redirect('location.php', false);
        }

    } else{
        $session->msg("d", $errors);
        redirect('location.php',false);
    }
}


?>

<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Location Master
        <small>Update Location Details</small>
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
    <form method="post" action="edit_location.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="edit_location" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'location.php'">Cancel  </button>
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
                        <div class="form-group">
                            <label>Location Code</label>
                            <input type="text" class="form-control" name="LocationCode" placeholder="Location Code" required="required" value="<?php echo remove_junk($location['LocationCode']);?>" readonly="readonly" disabled="disabled" />
                            <input type="hidden" name="hLocationCode" value="<?php echo remove_junk($location['LocationCode']);?>" />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Location Name</label>
                            <input type="text" class="form-control" name="LocationName" placeholder="Location Name" required="required" value="<?php echo remove_junk($location['LocationName']);?>" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group checkbox">
                            <label class="form-check-label">
                                <input type="checkbox" name="LocationOutlet" class="form-check-input" <?php if(remove_junk($location['LocationOutlet'] === "1")): echo "checked"; endif; ?> />
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