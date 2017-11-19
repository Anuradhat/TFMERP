<?php
ob_start();

$page_title = 'Employee Designation - Edit Designation';
require_once('includes/load.php');
page_require_level(2);

preventGetAction('employee_designation.php');

?>


<?php
if(isset($_POST['designation'])){
    $p_desigcode = remove_junk($db->escape($_POST['DesignationCode']));

    if(!$p_desigcode){
        $session->msg("d","Missing designation identification.");
        redirect('employee_designation.php');
    }
    else
    {
        $designation = find_by_sp("call spSelectEmployeeDesignationFromCode('{$p_desigcode}');");

        if(!$designation){
            $session->msg("d","Missing designation details.");
            redirect('employee_designation.php');
        }
    }
}
?>

<?php
if(isset($_POST['edit_designation'])){
    $req_fields = array('hDesignationCode','DesignationName');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_DesignationCode  = remove_junk($db->escape($_POST['hDesignationCode']));
        $p_DesignationName = remove_junk($db->escape($_POST['DesignationName']));

        $date    = make_date();
        $user = "anush";

        $query  = "call spUpdateEmployeeDesignation('{$p_DesignationCode}','{$p_DesignationName}','{$date}','{$user}');";

        if($db->query($query)){
            $session->msg('s',"Designation updated");
            redirect('employee_designation.php', false);
        } else {
            $session->msg('d',' Sorry failed to updated!');
            //redirect('customer.php', false);
        }

    } else{
        $session->msg("d", $errors);
        redirect('edit_employee_designation.php',false);
    }
}

?>

<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Employee Designation
        <small>Update Designation Details</small>
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
    <form method="post" action="edit_employee_designation.php">

        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="edit_designation" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'employee_designation.php'">Cancel  </button>
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
                            <label>Designation Code</label>
                            <input type="text" class="form-control" name="DesignationCode" placeholder="Designation Code" required="required" value="<?php echo remove_junk($designation['DesignationCode']);?>" readonly="readonly" disabled="disabled" />
                            <input type="hidden" name="hDesignationCode" value="<?php echo remove_junk($designation['DesignationCode']);?>" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Designation Name</label>
                            <input type="text" class="form-control" name="DesignationName" placeholder="Designation Name" required="required" value="<?php echo remove_junk($designation['DesignationName']);?>" />
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </form>

</section>

<?php include_once('layouts/footer.php'); ?>