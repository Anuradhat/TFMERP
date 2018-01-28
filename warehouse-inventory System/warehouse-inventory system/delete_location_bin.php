<?php
 ob_start();

 require_once('includes/load.php');
 // Checkin What level user has permission to view this page
 page_require_level(2);

 preventGetAction('location_bin.php');

?>


<?php
if(isset($_POST['bincode'])){
    $p_bincode = remove_junk($db->escape($_POST['bincode']));
    $p_locacode = remove_junk($db->escape($_POST['locationcode']));
    $p_defaultbin = remove_junk($db->escape($_POST['defaultbin']));
    $user = current_user();

    if(!$p_locacode){
        $session->msg("d","Missing bin identification.");
        redirect('location_bin.php');
    }

    if($p_defaultbin == "1"){
        $session->msg("d","Can't delte default bin");
        redirect('location_bin.php');
        exit;
    }

    $delete_id = delete_by_sp("call spDeleteLocationBin('{$p_locacode}','{$p_bincode}','{$user}');");

    if($delete_id){
        $session->msg("s","Bin deleted.");
        redirect('location_bin.php');
    } else {
        $session->msg("d","Bin deletion failed.");
        redirect('location_bin.php');
    }
}
?>