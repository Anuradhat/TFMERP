<?php
ob_start();
require_once('includes/load.php');
// Checkin What level user has permission to view this page
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

    $delete_id = delete_by_sp("call spDeleteEmployeeDesignation('{$p_desigcode}');");

    if($delete_id){
        $session->msg("s","Designation deleted.");
        redirect('employee_designation.php');
    } else {
        $session->msg("d","Designation deletion failed.");
        redirect('employee_designation.php');
    }
}
?>