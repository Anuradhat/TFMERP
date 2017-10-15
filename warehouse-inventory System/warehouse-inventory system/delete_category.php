<?php
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);
?>


<?php
if(isset($_POST['category'])){
    $p_catcode = remove_junk($db->escape($_POST['CategoryCode']));

    if(!$p_catcode){
        $session->msg("d","Missing category identification.");
        redirect('category.php');
    }

    $delete_id = delete_by_sp("call spDeleteCategory('{$p_catcode}');");

    if($delete_id){
        $session->msg("s","Category deleted.");
        redirect('category.php');
    } else {
        $session->msg("d","category deletion failed.");
        redirect('category.php');
    }
}
?>