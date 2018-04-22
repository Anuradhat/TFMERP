<?php
ob_start();

$page_title = 'Work-Flow Master - Update Work-Flow';
require_once('includes/load.php');
UserPageAccessControle(1,'Work Flow Edit');

preventGetAction('workflow.php');

$all_Employees = find_by_sql("call spSelectAllEmployees();");
?>


<?php
if(isset($_POST['workflow'])){
    $p_WorkFlowCode = remove_junk($db->escape($_POST['WorkFlowCode']));

    if(!$p_WorkFlowCode){
        $session->msg("d","Missing work-flow identification.");
        redirect('workflow.php');
    }
    else
    {
        $workflowH = find_by_sp("call spSelectWorkFlowFromCode('{$p_WorkFlowCode}');");
        $workflowD = find_by_sql("call spSelectWorkFlowDetailsFromCode('{$p_WorkFlowCode}');");

        if(!$workflowH || !$workflowD){
            $session->msg("d","Missing work-flow details.");
            redirect('workflow.php');
        }

        //Get level details
        $level1 = ArraySearchWithCoulmn($workflowD,"1","Level");
        $level2 = ArraySearchWithCoulmn($workflowD,"2","Level");
        $level3 = ArraySearchWithCoulmn($workflowD,"3","Level");
        $level4 = ArraySearchWithCoulmn($workflowD,"4","Level");
        $level5 = ArraySearchWithCoulmn($workflowD,"5","Level");
    }
}
?>

<?php
if(isset($_POST['edit_workflow'])){
    $req_fields = array('hWorkFlowCode','Description','EmployeeCode1');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_WorkFlowCode  = remove_junk($db->escape($_POST['hWorkFlowCode']));
        $p_Description  = remove_junk($db->escape($_POST['Description']));

        $p_EmployeeCode1  = remove_junk($db->escape($_POST['EmployeeCode1']));
        $p_Level2 = remove_junk(string2Boolean($db->escape($_POST['Level2'])));
        $p_EmployeeCode2  = remove_junk($db->escape($_POST['EmployeeCode2']));
        $p_Level3 = remove_junk(string2Boolean($db->escape($_POST['Level3'])));
        $p_EmployeeCode3  = remove_junk($db->escape($_POST['EmployeeCode3']));
        $p_Level4 = remove_junk(string2Boolean($db->escape($_POST['Level4'])));
        $p_EmployeeCode4  = remove_junk($db->escape($_POST['EmployeeCode4']));
        $p_Level5 = remove_junk(string2Boolean($db->escape($_POST['Level5'])));
        $p_EmployeeCode5  = remove_junk($db->escape($_POST['EmployeeCode5']));

        $date    = make_date();
        $user =  current_user();

        try
        {
            $db->begin();

            $wf_count = find_by_sp("call spSelectWorkFlowFromCode('{$p_WorkFlowCode}');");

            if(!$wf_count)
            {
                $session->msg("d", "This work-flow code not exist in the system.");
                redirect('workflow.php',false);
            }
            //Update Workflow header
            $query  = "call spUpdateWorkFlowH('{$p_WorkFlowCode}','{$p_Description}','{$date}','{$user["username"]}');";
            $db->query($query);

            //Delete records from work-flow details
            $query  = "call spDeleteWorkFlowDFromCode('{$p_WorkFlowCode}');";
            $db->query($query);

            //Level 01
            $query  = "call spInsertWorkFlowD('{$p_WorkFlowCode}','{$p_EmployeeCode1}',1);";
            $db->query($query);

            //Level 02
            if($p_Level2){
             $query  = "call spInsertWorkFlowD('{$p_WorkFlowCode}','{$p_EmployeeCode2}',2);";
             $db->query($query);
            }

            //Level 03
            if($p_Level3){
                $query  = "call spInsertWorkFlowD('{$p_WorkFlowCode}','{$p_EmployeeCode3}',3);";
                $db->query($query);
            }

            //Level 04
            if($p_Level4){   
                $query  = "call spInsertWorkFlowD('{$p_WorkFlowCode}','{$p_EmployeeCode4}',4);";
                $db->query($query);
            }

            //Level 05
            if($p_Level5){   
                $query  = "call spInsertWorkFlowD('{$p_WorkFlowCode}','{$p_EmployeeCode5}',5);";
                $db->query($query);
            }

            $db->commit();
            $session->msg('s',"Work-flow updated ");
            redirect('workflow.php', false);

        }
        catch(Exception $ex)
        {
            $db->rollback();

            $session->msg('d',' Sorry failed to updated!');
            redirect('workflow.php', false);
        }
    }
    else{
        $session->msg("d", $errors);
        redirect('workflow.php',false);
    }
}

?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Work-Flow Master
        <small>Update Work-Flow Details</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Administration
            </a>
        </li>
        <li class="active">Work Flow</li>
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
    <form method="post" action="edit_workflow.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="edit_workflow" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'workflow.php'">Cancel  </button>
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
                            <label>Work-Flow Code</label>
                            <input type="text" class="form-control" name="WorkFlowCode" placeholder="Code will generate after save" readonly="readonly" disabled="disabled" value="<?php echo $workflowH['WorkFlowCode']; ?>"/>
                            <input type="hidden" name="hWorkFlowCode" value="<?php echo $workflowH['WorkFlowCode']; ?>"/>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" class="form-control" name="Description" placeholder="Work-Flow Description" required="required" value="<?php echo $workflowH['Description']; ?>"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Approval Level Details</h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group checkbox">
                            <label> Approval Level 01 </label>
                                <select class="form-control select2" style="width: 100%;" name="EmployeeCode1" id="EmployeeCode1" required="required">
                                    <option value="">Select Employee</option><?php  foreach ($all_Employees as $EMP): ?>
                                    <option value="<?php echo $EMP['EpfNumber'] ?>" <?php if($EMP['EpfNumber'] === $level1['EmployeeCode']): echo "selected"; endif; ?>><?php echo $EMP['EmployeeName'] ?>
                                    </option><?php endforeach; ?>
                                </select>
                            
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group checkbox">
                            <label class="form-check-label">
                                <input type="checkbox" id="Level2" name="Level2" class="form-check-input" <?php if($level2 != null): echo "checked"; endif; ?> />
                                Approval Level 02
                            </label>
                                <select class="form-control select2" style="width: 100%;" name="EmployeeCode2" id="EmployeeCode2" required="required" <?php if($level2 == null): echo "disabled"; endif; ?>>
                                    <option value="">Select Employee</option><?php  foreach ($all_Employees as $EMP): ?>
                                    <option value="<?php echo $EMP['EpfNumber'] ?>" <?php if($EMP['EpfNumber'] === $level2['EmployeeCode']): echo "selected"; endif; ?>><?php echo $EMP['EmployeeName'] ?>
                                    </option><?php endforeach; ?>
                                </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group checkbox">
                            <label class="form-check-label">
                                <input type="checkbox" id="Level3" name="Level3" class="form-check-input" <?php if($level3 != null): echo "checked"; endif; ?>/>
                                Approval Level 03
                            </label>
                                <select class="form-control select2" style="width: 100%;" name="EmployeeCode3" id="EmployeeCode3"  required="required" <?php if($level3 == null): echo "disabled"; endif; ?>>
                                    <option value="">Select Employee</option><?php  foreach ($all_Employees as $EMP): ?>
                                    <option value="<?php echo $EMP['EpfNumber'] ?>" <?php if($EMP['EpfNumber'] === $level3['EmployeeCode']): echo "selected"; endif; ?>><?php echo $EMP['EmployeeName'] ?>
                                    </option><?php endforeach; ?>
                                </select>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group checkbox">
                            <label class="form-check-label">
                                <input type="checkbox" id="Level4" name="Level4" class="form-check-input" <?php if($level4 != null): echo "checked"; endif; ?>/>
                                Approval Level 04
                            </label>
                                <select class="form-control select2" style="width: 100%;" name="EmployeeCode4" id="EmployeeCode4" required="required" <?php if($level4 == null): echo "disabled"; endif; ?>>
                                    <option value="">Select Employee</option><?php  foreach ($all_Employees as $EMP): ?>
                                    <option value="<?php echo $EMP['EpfNumber'] ?>" <?php if($EMP['EpfNumber'] === $level4['EmployeeCode']): echo "selected"; endif; ?>><?php echo $EMP['EmployeeName'] ?>
                                    </option><?php endforeach; ?>
                                </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group checkbox">
                            <label class="form-check-label">
                                <input type="checkbox" id="Level5" name="Level5" class="form-check-input" <?php if($level5 != null): echo "checked"; endif; ?>/>
                                Approval Level 05
                            </label>
                                <select class="form-control select2" style="width: 100%;" name="EmployeeCode5" id="EmployeeCode5" required="required" <?php if($level5 == null): echo "disabled"; endif; ?>>
                                    <option value="">Select Employee</option><?php  foreach ($all_Employees as $EMP): ?>
                                    <option value="<?php echo $EMP['EpfNumber'] ?>" <?php if($EMP['EpfNumber'] === $level5['EmployeeCode']): echo "selected"; endif; ?>><?php echo $EMP['EmployeeName'] ?>
                                    </option><?php endforeach; ?>
                                </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group checkbox"></div>
                    </div>
                </div>


            </div>
        </div>
    </form>
</section>

<script>
    $('#Level2').change(function () {
        $("#EmployeeCode2").prop("disabled", !$(this).is(':checked'));
    });

    $('#Level3').change(function () {
        $("#EmployeeCode3").prop("disabled", !$(this).is(':checked'));
    });

    $('#Level4').change(function () {
        $("#EmployeeCode4").prop("disabled", !$(this).is(':checked'));
    });

    $('#Level5').change(function () {
        $("#EmployeeCode5").prop("disabled", !$(this).is(':checked'));
    });
</script>

<?php include_once('layouts/footer.php'); ?>