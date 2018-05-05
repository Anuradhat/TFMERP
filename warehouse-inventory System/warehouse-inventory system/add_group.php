<?php
$page_title = 'Add Group';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(1);
?>
<?php
if(isset($_POST['add'])){

    $req_fields = array('group-name','group-level');
    validate_fields($req_fields);

    if(find_by_groupName($_POST['group-name']) === false ){
        $session->msg('d','<b>Sorry!</b> Entered Group Name already in database!');
        redirect('add_group.php', false);
    }elseif(find_by_groupLevel($_POST['group-level']) === false) {
        $session->msg('d','<b>Sorry!</b> Entered Group Level already in database!');
        redirect('add_group.php', false);
    }
    if(empty($errors)){
        $name = remove_junk($db->escape($_POST['group-name']));
        $level = remove_junk($db->escape($_POST['group-level']));
        $status = remove_junk($db->escape($_POST['status']));

        $query  = "INSERT INTO user_groups (";
        $query .="group_name,group_level,group_status";
        $query .=") VALUES (";
        $query .=" '{$name}', '{$level}','{$status}'";
        $query .=")";
        if($db->query($query)){
            //sucess
            $session->msg('s',"Group has been creted! ");
            redirect('add_group.php', false);
        } else {
            //failed
            $session->msg('d',' Sorry failed to create Group!');
            redirect('add_group.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('add_group.php',false);
    }
}
?>
<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        User Group
        <small>Add new user group</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Administration
            </a>
        </li>
        <li class="active">User Group</li>
    </ol>
    <style>
        form {
            display: inline;
        }
    </style>
</section>

<section class="content">
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Add Group Details</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-xs-6">

                    <div class="box">
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div><?php echo display_msg($msg); ?>
                                <form method="post" action="add_group.php" class="clearfix">
                                    <div class="form-group">
                                        <label for="name" class="control-label">Group Name</label>
                                        <input type="name" class="form-control" name="group-name">
                                    </div>
                                    <div class="form-group">
                                        <label for="level" class="control-label">Group Level</label>
                                        <input type="number" class="form-control" name="group-level">
                                    </div>
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select class="form-control" name="status">
                                            <option value="1">Active</option>
                                            <option value="0">Deactive</option>
                                        </select>
                                    </div>
                                    <div class="form-group clearfix">
                                        <button type="submit" name="add" class="btn btn-info">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
     </div>
</section>
    
    <?php include_once('layouts/footer.php'); ?>
