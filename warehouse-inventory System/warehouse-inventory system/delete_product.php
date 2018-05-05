<?php
ob_start();

require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);

preventGetAction('product.php');
?>


<?php
if(isset($_POST['ProductCode'])){
    $p_procode = remove_junk($db->escape($_POST['ProductCode']));

    if(!$p_procode){
        $flashMessages->error('Missing product identification.','product.php');
    }

    $delete_id = delete_by_sp("call spDeleteProduct('{$p_procode}');");

    if($delete_id){
        InsertRecentActvity("Product deleted","Reference No. ".$p_procode);

        $flashMessages->success('Product deleted','product.php');
    } else {
        $flashMessages->warning('Product deletion failed.','product.php');
    }
}
?>
