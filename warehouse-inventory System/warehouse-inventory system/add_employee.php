<?php
ob_start();

$page_title = 'Employee - New Employee';
require_once('includes/load.php');
page_require_level(2);
$allEmployeeDepartmens = find_by_sql("call spSelectAllEmployeeDepartment();");
$allEmployeeDesignation = find_by_sql("call spSelectAllEmployeeDesignation();");
?>

<?php
if(isset($_POST['add_employee'])){
    $req_fields = array('EpfNumber','EmployeeName','DepartmentCode','DesignationCode','TelephoneNo','Email','EmployeeAddress2','EmployeeAddress3');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_EpfNumber  = remove_junk($db->escape($_POST['EpfNumber']));
        $p_EmployeeName  = remove_junk($db->escape($_POST['EmployeeName']));
        $p_DepartmentCode  = remove_junk($db->escape($_POST['DepartmentCode']));
        $p_DesignationCode  = remove_junk($db->escape($_POST['DesignationCode']));
        $p_TelephoneNo  = remove_junk($db->escape($_POST['TelephoneNo']));
        $p_Email  = remove_junk($db->escape($_POST['Email']));
        $p_EmployeeAddress1  = remove_junk($db->escape($_POST['EmployeeAddress1']));
        $p_EmployeeAddress2  = remove_junk($db->escape($_POST['EmployeeAddress2']));
        $p_EmployeeAddress3  = remove_junk($db->escape($_POST['EmployeeAddress3']));

        $epfno = $p_EpfNumber;

        $date    = make_date();
        $user = current_user();

        $epf_count = find_by_sp("call spSelectEmployeeFromEpf('{$epfno}');");

        if($epf_count)
        {
            $session->msg("d", "This Employee exist in the system.");
            redirect('add_employee.php',false);
        }

        $query  = "call spInsertEmployee('{$p_EpfNumber}','{$p_EmployeeName}','{$p_EmployeeAddress1}','{$p_EmployeeAddress2}',
'{$p_EmployeeAddress3}','{$p_TelephoneNo}','{$p_Email}','{$p_DepartmentCode}','{$p_DesignationCode}','{$user}');";

        if($db->query($query)){
            $session->msg('s',"Employee added ");
            redirect('add_employee.php', false);
        } else {
            $session->msg('d',' Sorry failed to add!');
            redirect('employee.php', false);
        }

    } else{
        $session->msg("d", $errors);
        redirect('add_employee.php',false);
    }
}

?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Employee
        <small>Enter New Employee Details</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Employee
            </a>
        </li>
        <li class="active">Employee</li>
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
    <form method="post" action="add_employee.php">

        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="add_employee" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'employee.php'">Cancel  </button>
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
                            <label>EPF Number</label>
                            <input type="text" class="form-control" name="EpfNumber" placeholder="EPF Number" required="required"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Employee Name</label>
                            <input type="text" class="form-control" name="EmployeeName" placeholder="Employee Name" required="required" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Department</label>
                            <select required class="form-control select2" name="DepartmentCode">
                            <option value="">Select Department</option>
                            <?php foreach($allEmployeeDepartmens as $allDepartments): ?>
                            <option value=<?php echo remove_junk($allDepartments['DepartmentCode']); ?>><?php echo remove_junk($allDepartments['DepartmentName']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Designation</label>
                            <select required class="form-control select2" name="DesignationCode">
                            <option value="">Select Designation</option>
                            <?php foreach($allEmployeeDesignation as $allDesignation): ?>
                            <option value=<?php echo remove_junk($allDesignation['DesignationCode']); ?>><?php echo remove_junk($allDesignation['DesignationName']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>   
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Contacts Details</h3>

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
                            <label>TelephoneNo</label>
                            <input type="text" class="form-control" name="TelephoneNo" placeholder="Telephone No" required="required"/>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="Email" placeholder="Email" required="required" />
                        </div>
                    </div>
                    <div class="col-md-4">
                    <div class="form-group">
                        <label>Employee Address</label>
                        <input type="text" class="form-control" name="EmployeeAddress1" placeholder="Street Number" />
                        <input type="text" class="form-control" name="EmployeeAddress2" placeholder="Street Name" required="required" />
                        <input type="text" class="form-control" name="EmployeeAddress3" placeholder="City" required="required" />
                    <div>
                    </div>                    
                </div>
            </div>
        </div>
    </form>

</section>

<?php include_once('layouts/footer.php'); ?>