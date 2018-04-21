<?php
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);

preventGetAction('category.php');
?>


<?php
if(isset($_POST['CategoryCode'])){
    $p_catcode = remove_junk($db->escape($_POST['CategoryCode']));

    if(!$p_catcode){
        $flashMessages->warning('Missing category identification','category.php');
    }

    $delete_id = delete_by_sp("call spDeleteCategory('{$p_catcode}');");

    if($delete_id){
        $flashMessages->success('Category deleted.','category.php');
    } else {
        $flashMessages->error('category deletion failed.','category.php');
    }
}
?>