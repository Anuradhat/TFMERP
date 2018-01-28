<?php
ob_start();
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);

preventGetAction('employee_department.php');
?>


<?php
if(isset($_POST['department'])){
    $p_depcode = remove_junk($db->escape($_POST['DepartmentCode']));

    if(!$p_depcode){
        $session->msg("d","Missing department identification.");
        redirect('employee_department.php');
    }

    $delete_id = delete_by_sp("call spDeleteEmployeeDepartment('{$p_depcode}');");

    if($delete_id){
        $session->msg("s","Department deleted.");
        redirect('employee_department.php');
    } else {
        $session->msg("d","Department deletion failed.");
        redirect('employee_department.php');
    }
}
?>