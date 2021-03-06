<?php
ob_start();
require_once('includes/load.php');
// Checkin What level user has permission to view this page
UserPageAccessControle(1,'Employee Details Delete');

preventGetAction('employee.php');
?>


<?php
if(isset($_POST['EpfNumber'])){
    $p_EpfNumber = remove_junk($db->escape($_POST['EpfNumber']));

    if(!$p_EpfNumber){
        $session->msg("d","Missing employee identification.");
        redirect('employee.php');
    }

    $delete_id = delete_by_sp("call spDeleteEmployee('{$p_EpfNumber}');");

    if($delete_id){
        InsertRecentActvity("Employee deleted","Reference No. ".$p_EpfNumber);

        $session->msg("s","Employee deleted.");
        redirect('employee.php');
    } else {
        $session->msg("d","Employee deletion failed.");
        redirect('employee.php');
    }
}
?>