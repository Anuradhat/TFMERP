<?php
ob_start();

require_once('includes/load.php');
 // Checkin What level user has permission to view this page
 page_require_level(2);

 preventGetAction('tax.php');
?>


<?php
if(isset($_POST['taxrate'])){
    $p_TaxCode = remove_junk($db->escape($_POST['TaxCode']));

    if(!$p_TaxCode){
        $session->msg("d","Missing tax identification.");
        redirect('tax.php');
    }

    $delete_id = delete_by_sp("call spDeleteTaxRates('{$p_TaxCode}');");

    if($delete_id){
        $session->msg("s","Tax deleted");
        redirect('tax.php');
    } else {
        $session->msg("d","tax deletion failed.");
        redirect('tax.php');
    }
}
?>