<?php
$page_title = 'Edit Group';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
//page_require_level(1);
UserPageAccessControle(1,'User Group Edit');
?>
<?php
$e_group = find_by_id('user_groups',(int)$_GET['id']);
if(!$e_group){
    $session->msg("d","Missing Group id.");
    redirect('group.php');
}

$e_GroupMenuAcsess = find_by_sql("call spSelectGroupAccessFromGroupName('{$e_group['group_name']}');");

?>
<?php
if(isset($_POST['update'])){
    $req_fields = array('group-name','group-level');
    validate_fields($req_fields);
    if(empty($errors)){
        $name = remove_junk($db->escape($_POST['group-name']));
        $level = remove_junk($db->escape($_POST['group-level']));
        $status = remove_junk($db->escape($_POST['status']));
        $query  = "UPDATE user_groups SET ";
        $query .= "group_name='{$name}',group_level='{$level}',group_status='{$status}'";
        $query .= "WHERE ID='{$db->escape($e_group['id'])}'";
        $result = $db->query($query);
        if($result && $db->affected_rows() === 1){
            //sucess
            $session->msg('s',"Group has been updated! ");
            redirect('edit_group.php?id='.(int)$e_group['id'], false);
        } else {
            //failed
            $session->msg('d',' Sorry failed to updated Group!');
            redirect('edit_group.php?id='.(int)$e_group['id'], false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('edit_group.php?id='.(int)$e_group['id'], false);
    }
}

if(isset($_POST['_checkedStatus'])){
    $req_fields = array('_MainMenu', '_SubMenu', '_Page', '_Controller', '_checkedStatus','_groupName');
    validate_fields($req_fields);

    if(empty($errors)){
        $p_groupName  = remove_junk($db->escape($_POST['_groupName']));
        $p_MainMenu  = remove_junk($db->escape($_POST['_MainMenu']));
        $p_SubMenu  = remove_junk($db->escape($_POST['_SubMenu']));
        $p_Page  = remove_junk($db->escape($_POST['_Page']));
        $p_Controller  = remove_junk($db->escape($_POST['_Controller']));
        $p_checkedStatus  = remove_junk($db->escape($_POST['_checkedStatus']));

        $user = current_user();

        $query  = "call spUpdateGroupMenuAccessByKey('{$p_groupName}','{$p_MainMenu}','{$p_SubMenu}','{$p_Page}',
'{$p_Controller}','{$user['username']}',{$p_checkedStatus});";

        if($db->query($query)){
            $session->msg('s',"Group access updated");
            //redirect('edit_group.php', false);
        } else {
            $session->msg('d',' Sorry failed to updated!');
            //redirect('edit_group.php', false);
        }
    }else{
        $session->msg("d", $errors);
        redirect('edit_group.php',false);
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
                <div class="col-xs-12">

                    <div class="box">
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div><?php echo display_msg($msg); ?>
                                <form method="post" action="edit_group.php?id=<?php echo (int)$e_group['id'];?>" class="clearfix">
                                    <div class="row">
                                        <div class="col-xs-3">
                                            <div class="form-group">
                                                <label for="name" class="control-label">Group Name</label>
                                                <input id="GroupName" type="name" class="form-control" name="group-name" value="<?php echo remove_junk(ucwords($e_group['group_name'])); ?>" readonly="readonly" >
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="form-group">
                                                <label for="level" class="control-label">Group Level</label>
                                                <input type="number" class="form-control" name="group-level" value="<?php echo (int)$e_group['group_level']; ?>">
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="form-group">
                                                <label for="status">Status</label>
                                                <select class="form-control" name="status">
                                                    <option <?php if($e_group['group_status'] === '1') echo 'selected="selected"';?> value="1"> Active </option>
                                                    <option <?php if($e_group['group_status'] === '0') echo 'selected="selected"';?> value="0">Deactive</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <label for="space"></label>
                                            <div class="form-group clearfix">
                                                <button type="submit" name="update" class="btn btn-info">Update</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Add Group Menu Access</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <!-- /.box-header -->
                        <div class="box-body">
                            <table id="GroupAccessTable" class="table table-bordered table-striped datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Main Menu</th>
                                        <th>Sub Menu</th>
                                        <th>Page</th>
                                        <th>Controller</th>
                                        <th>Access</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($e_GroupMenuAcsess as $GroupAccess): ?>
                                    <Tr>
                                        <td>
                                            <?php echo remove_junk(ucfirst($GroupAccess['ID'])); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($GroupAccess['MainMenu']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($GroupAccess['SubMenu']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($GroupAccess['Page']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($GroupAccess['Controller']); ?>
                                        </td>
                                        <td>
                                            <input type="checkbox" name=<?php remove_junk($GroupAccess['Controller']); ?>  value="yes" <?php echo ($GroupAccess['Access'] == 1 ? 'checked' : '');?>>                                            
                                        </td>
                                    </Tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group clearfix">
                <button id="btnUpdateMenuAccess" type="submit" name="updateMenuAccess" class="btn btn-info">Update</button>
            </div>
        </div>
    </div>
</section>
        
<?php include_once('layouts/footer.php'); ?>

<script type="text/javascript">
    $("#btnUpdateMenuAccess").click(function () {
        $('.loader').show();
        $("#GroupAccessTable tr").each(function (i, el) {
            var $tds = $(this),
                ID = $tds.find("td:eq(0)").text(),
                MainMenu = $tds.find("td:eq(1)").text(),
                SubMenu = $tds.find("td:eq(2)").text(),
                Page = $tds.find("td:eq(3)").text(),
                Controller = $tds.find("td:eq(4)").text(),
                $Access = $tds.find("input[type=checkbox]");
            var checkedStatus = 0;
            var groupName = $("#GroupName").val();

            if ($Access != '') {

                if ($Access.is(":checked"))
                {
                    checkedStatus = 1;
                }
                else {
                    checkedStatus = 0;
                }
            }

            $.ajax({
                type: "POST",
                url: "edit_group.php",
                data: { "_ID": ID.trim(), "_MainMenu": MainMenu.trim(), "_SubMenu": SubMenu.trim(), "_Page": Page.trim(), "_Controller": Controller.trim(), "_checkedStatus": checkedStatus, "_groupName": groupName.trim() },
                success : function (){
                //    altert('Success')
                },
                complete: function (result) {
                    $('.loader').fadeOut();
                }

            });

            })
            
    });
</script>
