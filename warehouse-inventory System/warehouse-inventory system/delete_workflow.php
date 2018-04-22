<?php
ob_start();

require_once('includes/load.php');
// Checkin What level user has permission to view this page
UserPageAccessControle(1,'Work Flow Delete');

preventGetAction('workflow.php');

?>


<?php
if(isset($_POST['WorkFlowCode'])){
    $p_WorkFlowCode = remove_junk($db->escape($_POST['WorkFlowCode']));

    if(!$p_WorkFlowCode){
        $session->msg("d","Missing work-flow identification.");
        redirect('workflow.php');
    }

    $date    = make_date();
    $user =  current_user();

    $delete_id = delete_by_sp("call spDeleteWorkFlowH('{$p_WorkFlowCode}','{$date}','{$user["username"]}');");

    if($delete_id){
        $session->msg("s","Work-Flow deleted.");
        redirect('workflow.php');
    } else {
        $session->msg("d","Work-Flow deletion failed.");
        redirect('workflow.php');
    }
}
?>