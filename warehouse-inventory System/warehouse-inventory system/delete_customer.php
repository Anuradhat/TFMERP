<?php
ob_start();

require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);

preventGetAction('customer.php');
?>


<?php
if(isset($_POST['customer'])){
    $p_cuscode = remove_junk($db->escape($_POST['CustomerCode']));

    if(!$p_cuscode){
        $session->msg("d","Missing customer identification.");
        redirect('customer.php');
    }

    $delete_id = delete_by_sp("call spDeleteCustomer('{$p_cuscode}');");

    if($delete_id){
        $session->msg("s","Customer deleted.");
        redirect('customer.php');
    } else {
        $session->msg("d","customer deletion failed.");
        redirect('customer.php');
    }
}
?>
