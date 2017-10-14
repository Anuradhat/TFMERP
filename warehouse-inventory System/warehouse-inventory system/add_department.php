<?php
$page_title = 'Add Department';
require_once('includes/load.php');
page_require_level(2);
?>

<?php
if(isset($_POST['add_department'])){
    $req_fields = array('DepartmentCode','DepartmentDesc');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_DepartmentCode  = remove_junk($db->escape($_POST['DepartmentCode']));
        $p_DepartmentDesc  = remove_junk($db->escape($_POST['DepartmentDesc']));

        $date    = make_date();
        $user = "anush";

        $dep_count = find_by_sp("call spSelectDepartmentFromCode('{$p_DepartmentCode}');");

        if($dep_count)
        {
            $session->msg("d", "This product code exist in the system.");
           // redirect('add_department.php',false);
            return;
        }

        $query  = "call spInsertDepartment('{$p_DepartmentCode}','{$p_DepartmentDesc}','{$date}','{$user}');";

        if($db->query($query)){
            $session->msg('s',"Department added ");
            redirect('add_department.php', false);
        } else {
            $session->msg('d',' Sorry failed to added!');
            redirect('department.php', false);
        }

    } else{
        $session->msg("d", $errors);
        redirect('add_department.php',false);
    }
}

?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Department Master
        <small>Enter New Department Details</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Master
            </a>
        </li>
        <li class="active">Department</li>
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
    <form method="post" action="add_department.php">
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
                            <input type="text" class="form-control" name="DepartmentCode" placeholder="Department Code" required="required" />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Department Description</label>
                            <input type="text" class="form-control" name="DepartmentDesc" placeholder="Department Description" required="required" />
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <button type="submit" name="add_department" class="btn btn-success btn-lg">Save  </button>
    </form>

        <div class="form-group"></div>

</section>

<?php include_once('layouts/footer.php'); ?>