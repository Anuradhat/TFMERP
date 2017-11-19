<?php
ob_start();

$page_title = 'Employee Department - New Department';
require_once('includes/load.php');
page_require_level(2);

//$all_departments = find_by_sql("call spSelectAllEmployeeDepartment();")

?>

<?php
if(isset($_POST['add_department'])){
    $req_fields = array('DepartmentName');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_DepartmentCode  = autoGenerateNumber('tfmEmployeeDepartmentM',1);
        $p_DepartmentName  = remove_junk($db->escape($_POST['DepartmentName']));

        $depcode = $p_DepartmentCode;

        $date    = make_date();
        $user = "anush";

        $dep_count = find_by_sp("call spSelectEmployeeDepartmentFromCode('{$depcode}');");

        if($dep_count)
        {
            $session->msg("d", "This department code exist in the system.");
            redirect('add_employee_department.php',false);
        }

        $query  = "call spInsertEmployeeDepartment('{$depcode}','{$p_DepartmentName}','{$date}','{$user}');";

        if($db->query($query)){
            $session->msg('s',"Department added ");
            redirect('add_employee_department.php', false);
        } else {
            $session->msg('d',' Sorry failed to add!');
            redirect('employee_department.php', false);
        }

    } else{
        $session->msg("d", $errors);
        redirect('add_employee_department.php',false);
    }
}

?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Employee Department
        <small>Enter New Department Details</small>
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
    <form method="post" action="add_employee_department.php">

        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="add_department" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'employee_department.php'">Cancel  </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12"><?php echo display_msg($msg); ?>
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
                            <input type="text" class="form-control" name="DepartmentCode" placeholder="Code will generate after save" readonly="readonly" disabled="disabled" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Department Name</label>
                            <input type="text" class="form-control" name="DepartmentName" placeholder="Department Name" required="required" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>

</section>

<?php include_once('layouts/footer.php'); ?>