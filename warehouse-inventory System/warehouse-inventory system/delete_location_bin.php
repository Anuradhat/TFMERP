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
        $flashMessages->warning('Missing bin identification.','location_bin.php');
    }

    if($p_defaultbin == "1"){
        $flashMessages->warning("Can't delete default bin.",'location_bin.php');
    }

    $delete_id = delete_by_sp("call spDeleteLocationBin('{$p_locacode}','{$p_bincode}','{$user['username']}');");

    if($delete_id){
        $flashMessages->success("Bin deleted.",'location_bin.php');
    } else {
        $flashMessages->error("Bin deletion failed.",'location_bin.php');
    }
}
?>