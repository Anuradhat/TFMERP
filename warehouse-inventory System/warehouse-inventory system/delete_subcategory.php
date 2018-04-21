<?php
ob_start();

require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);

preventGetAction('subcategory.php');
?>


<?php
if(isset($_POST['SubcategoryCode'])){
    $p_scatcode = remove_junk($db->escape($_POST['SubcategoryCode']));

    if(!$p_scatcode){
        $flashMessages->warning('Missing subcategory identification.','subcategory.php');
    }

    $delete_id = delete_by_sp("call spDeleteSubcategory('{$p_scatcode}');");

    if($delete_id){
        $flashMessages->success('Subcategory deleted.','subcategory.php');
    } else {
        $flashMessages->error('Subcategory deletion failed.','subcategory.php');
    }
}
?>