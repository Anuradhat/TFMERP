<?php
 ob_start();

 require_once('includes/load.php');
 // Checkin What level user has permission to view this page
 page_require_level(2);

 preventGetAction('location.php');

?>


<?php
if(isset($_POST['LocationCode'])){
    $p_locacode = remove_junk($db->escape($_POST['LocationCode']));

    if(!$p_locacode){
        $session->msg("d","Missing location identification.");
        redirect('location.php');
    }

    $delete_id = delete_by_sp("call spDeleteLocation('{$p_locacode}');");

    if($delete_id){
        InsertRecentActvity("Location deleted","Reference No. ".$p_locacode);

        $session->msg("s","Location deleted.");
        redirect('location.php');
    } else {
        $session->msg("d","Location deletion failed.");
        redirect('location.php');
    }
}
?>