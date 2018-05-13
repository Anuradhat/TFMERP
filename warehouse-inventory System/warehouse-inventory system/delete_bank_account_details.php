<?php
ob_start();
require_once('includes/load.php');
// Checkin What level user has permission to view this page
UserPageAccessControle(1,'Bank Accounts Delete');

preventGetAction('BankAccountDetails.php');
?>


<?php
if(isset($_POST['AccountID'])){
    $p_AccountID = remove_junk($db->escape($_POST['AccountID']));

    if(!$p_AccountID){
        $flashMessages->warning('Missing account referance.','BankAccountDetails.php');
        
    }

    $delete_id = delete_by_sp("call spDeleteBankAccount('{$p_AccountID}');");

    if($delete_id){
        InsertRecentActvity("Account deleted","Reference No. ".$p_AccountID);

        $flashMessages->success('Account deleted.','BankAccountDetails.php');
        
    } else {
        $flashMessages->error('Account deletion failed.','BankAccountDetails.php');        
    }
}
?>