<?php
ob_start();

$page_title = 'Employee Department - Edit Department';
require_once('includes/load.php');
UserPageAccessControle(1,'Employee Department Edit');

preventGetAction('employee_department.php');

?>


<?php
if(isset($_POST['department'])){
    $p_depcode = remove_junk($db->escape($_POST['DepartmentCode']));

    if(!$p_depcode){
        $session->msg("d","Missing department identification.");
        redirect('employee_department.php');
    }
    else
    {
        $department = find_by_sp("call spSelectEmployeeDepartmentFromCode('{$p_depcode}');");

        if(!$department){
            $session->msg("d","Missing department details.");
            redirect('employee_department.php');
        }
    }
}
?>

<?php
if(isset($_POST['edit_department'])){
    $req_fields = array('hDepartmentCode','DepartmentName');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_DepartmentCode  = remove_junk($db->escape($_POST['hDepartmentCode']));
        $p_DepartmentName = remove_junk($db->escape($_POST['DepartmentName']));

        $date    = make_date();
        $user =  current_user();

        $query  = "call spUpdateEmployeeDepartment('{$p_DepartmentCode}','{$p_DepartmentName}','{$date}','{$user["username"]}');";

        if($db->query($query)){
            InsertRecentActvity("Department updated","Reference No. ".$p_DepartmentCode);

            $session->msg('s',"Department updated");
            redirect('employee_department.php', false);
        } else {
            $session->msg('d',' Sorry failed to updated!');
            //redirect('customer.php', false);
        }

    } else{
        $session->msg("d", $errors);
        redirect('edit_employee_department.php',false);
    }
}

?>

<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Employee Department
        <small>Update Department Details</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Employee
            </a>
        </li>
        <li class="active">Employee Department</li>
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
    <form method="post" action="edit_employee_department.php">

        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="edit_department" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'employee_department.php'">Cancel  </button>
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
                            <label>Department Code</label>
                            <input type="text" class="form-control" name="DepartmentCode" placeholder="Department Code" required="required" value="<?php echo remove_junk($department['DepartmentCode']);?>" readonly="readonly" disabled="disabled" />
                            <input type="hidden" name="hDepartmentCode" value="<?php echo remove_junk($department['DepartmentCode']);?>" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Department Name</label>
                            <input type="text" class="form-control" name="DepartmentName" placeholder="Department Name" required="required" value="<?php echo remove_junk($department['DepartmentName']);?>" />
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </form>

</section>

<?php include_once('layouts/footer.php'); ?>