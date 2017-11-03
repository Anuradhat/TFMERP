<?php
ob_start();

require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);

preventGetAction('subcategory.php');
?>


<?php
if(isset($_POST['subcategory'])){
    $p_scatcode = remove_junk($db->escape($_POST['SubcategoryCode']));

    if(!$p_scatcode){
        $session->msg("d","Missing subcategory identification.");
        redirect('subcategory.php');
    }

    $delete_id = delete_by_sp("call spDeleteSubcategory('{$p_scatcode}');");

    if($delete_id){
        $session->msg("s","Subcategory deleted.");
        redirect('subcategory.php');
    } else {
        $session->msg("d","Subcategory deletion failed.");
        redirect('subcategory.php');
    }
}
?>