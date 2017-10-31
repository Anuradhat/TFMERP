<?php
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);
?>


<?php
if(isset($_POST['product'])){
    $p_procode = remove_junk($db->escape($_POST['ProductCode']));

    if(!$p_procode){
        $session->msg("d","Missing product identification.");
        redirect('product.php');
    }

    $delete_id = delete_by_sp("call spDeleteProduct('{$p_procode}');");

    if($delete_id){
        $session->msg("s","product deleted.");
        redirect('product.php');
    } else {
        $session->msg("d","product deletion failed.");
        redirect('product.php');
    }
}
?>
