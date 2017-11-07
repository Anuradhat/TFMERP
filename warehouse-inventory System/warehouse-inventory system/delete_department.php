<?php
 ob_start();

 require_once('includes/load.php');
 // Checkin What level user has permission to view this page
 page_require_level(2);

 preventGetAction('department.php');

?>


<?php
if(isset($_POST['department'])){
    $p_depcode = remove_junk($db->escape($_POST['DepartmentCode']));

    if(!$p_depcode){
        $session->msg("d","Missing department identification.");
        redirect('department.php');
    }

    $delete_id = delete_by_sp("call spDeleteDepartment('{$p_depcode}');");

    if($delete_id){
        $session->msg("s","Department deleted.");
        redirect('department.php');
    } else {
        $session->msg("d","department deletion failed.");
        redirect('department.php');
    }
}
?>