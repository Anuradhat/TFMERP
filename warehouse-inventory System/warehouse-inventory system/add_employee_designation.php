<?php
ob_start();

$page_title = 'Employee Designation - New Designation';
require_once('includes/load.php');
page_require_level(2);

?>

<?php
if(isset($_POST['add_designation'])){
    $req_fields = array('DesignationName');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_DesignationCode  = autoGenerateNumber('tfmEmployeeDesignationM',1);
        $p_DesignationName  = remove_junk($db->escape($_POST['DesignationName']));

        $desigcode = $p_DesignationCode;

        $date    = make_date();
        $user =  current_user();

        $desig_count = find_by_sp("call spSelectEmployeeDesignationFromCode('{$desigcode}');");

        if($desig_count)
        {
            $session->msg("d", "This designation code exist in the system.");
            redirect('add_employee_designation.php',false);
        }

        $query  = "call spInsertEmployeeDesignation('{$desigcode}','{$p_DesignationName}','{$date}','{$user["username"]}');";

        if($db->query($query)){
            $session->msg('s',"Designation added ");
            redirect('add_employee_designation.php', false);
        } else {
            $session->msg('d',' Sorry failed to add!');
            redirect('employee_designation.php', false);
        }

    } else{
        $session->msg("d", $errors);
        redirect('add_employee_designation.php',false);
    }
}

?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Employee Designation
        <small>Enter New Designation Details</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Employee
            </a>
        </li>
        <li class="active">Employee Designation</li>
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
    <form method="post" action="add_employee_designation.php">

        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="add_designation" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'employee_designation.php'">Cancel  </button>
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
                            <label>Designation Code</label>
                            <input type="text" class="form-control" name="DesignationCode" placeholder="Code will generate after save" readonly="readonly" disabled="disabled" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Designation Name</label>
                            <input type="text" class="form-control" name="DesignationName" placeholder="Designation Name" required="required" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>

</section>

<?php include_once('layouts/footer.php'); ?>