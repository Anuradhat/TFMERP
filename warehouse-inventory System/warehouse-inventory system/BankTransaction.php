<?php
ob_start();
session_start();

$page_title = 'Bank Transaction - New Bank Transaction';
require_once('includes/load.php');
UserPageAccessControle(1,'Bank Tansaction Crate');

$allBanks = find_by_sql("call spSelectAllBanks();");
$allPaymentType = find_by_sql("call spSelectAllPaymentType();");
$all_BankTransaction = find_by_sql("call spSelectBankTransactionFromAccount();");
$all_BankAccounts = find_by_sql("call spSelectAllBankAccountDetails();");
?>

<?php

//if (isset($_POST['jBankCode'])) {

//    $BankCode = remove_junk($db->escape($_POST['jBankCode']));

//    $all_Branch = find_by_sql("call spSelectBankBranchFromBankCode('{$BankCode}');");

//    echo '<option value="">Select Branch</option>';
//    foreach($all_Branch as &$value){

//        echo '<option value='.remove_junk(ucfirst($value["BranchCode"])).'>'.remove_junk(ucfirst($value["BranchName"])).'</option>';
//    }

//    return;
//}

if(isset($_POST['add_BankTransaction'])){
    $req_fields = array('DebitAccount','CreditAccount','PaymentType','Ammount','CHQNo');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_DebitAccount  = remove_junk($db->escape($_POST['DebitAccount']));
        $p_CreditAccount  = remove_junk($db->escape($_POST['CreditAccount']));
        $p_PaymentType  = remove_junk($db->escape($_POST['PaymentType']));
        $p_Ammount  = remove_junk($db->escape($_POST['Ammount']));
        $p_Reference  = remove_junk($db->escape($_POST['Reference']));
        $p_Comment  = remove_junk($db->escape($_POST['Comment']));
        $p_CHQNo  = remove_junk($db->escape($_POST['CHQNo']));


        $p_TrnCode  = autoGenerateNumber('tfmBankTransactionDT',1);

       $user = current_user();

       $query  = "call spInsertBankTransaction('{$p_DebitAccount}','{$p_CreditAccount}','{$p_PaymentType}','{$p_TrnCode}','{$p_Ammount}','{$p_Reference}','{$p_Comment}','{$user["username"]}','{$p_CHQNo}');";

        if($db->query($query)){
            InsertRecentActvity("Bank Transaction","Transaction No. ".$p_TrnCode);

            $flashMessages->success('Bank transaction updated. ','BankTransaction.php');
            //$session->msg('s',"Bank account created ");
            //redirect('add_BankAccountDetails.php', false);
        } else {
            $flashMessages->error('Account transaction not created ','BankTransaction.php');
            //$session->msg('d',' Sorry failed to add!');
            //redirect('BankAccountDetails.php', false);
        }

    } else{
        $flashMessages->error($errors,'BankTransaction.php');
        //$session->msg("d", $errors);
        //redirect('add_BankAccountDetails.php',false);
    }
}

?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Bank Transaction
        <small>Enter Bank Transaction</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Bank
            </a>
        </li>
        <li class="active">Bank Transaction</li>
    </ol>
    <style>
        form {
            display: inline;
        }
    </style>
</section>

<!-- Main content -->
<section class="content">
    <!-- Your Page Content Here -->
    <form method="post" action="BankTransaction.php">

        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="add_BankTransaction" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <!--<button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;&nbsp;</button>-->
                            <!--<button type="button" class="btn btn-warning" onclick="window.location = 'BankTransaction.php'">Cancel  </button>-->
                        </div>
                    </div>
                </div>                
            </div>
        </div>
        <div class="row">
            <div id="message" class="col-md-12"><?php include('_partial_message.php'); ?> </div>
        </div>
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Transaction Details</h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Debit Account</label>
                            <select required class="form-control select2" name="DebitAccount" id="DebitAccount" >
                                <option value="">Select Debit Account</option><?php foreach($all_BankAccounts as $allBankAccounts): ?>
                                <option value=<?php echo remove_junk($allBankAccounts['AccountNo']); ?>><?php echo remove_junk($allBankAccounts['AccountName']); ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label>Credit Account</label>
                        <select required class="form-control select2" name="CreditAccount" id="CreditAccount">
                            <option value="">Select Credit Account</option><?php foreach($all_BankAccounts as $allBankAccounts): ?>
                            <option value=<?php echo remove_junk($allBankAccounts['AccountNo']); ?>><?php echo remove_junk($allBankAccounts['AccountName']); ?>
                            </option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Payment Type</label>
                            <select required class="form-control select2" name="PaymentType" id="PaymentType" >
                                <option value="">Select Payment Type</option><?php foreach($allPaymentType as $allType): ?>
                                <option value=<?php echo remove_junk($allType['PayTypeCode']); ?>><?php echo remove_junk($allType['Description']); ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Reference</label>
                            <input type="text" class="form-control" name="Reference" placeholder="Add Reference" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Comment</label>
                            <input type="text" class="form-control" name="Comment" placeholder="Add Comment"  />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>CHQ No</label>
                            <input type="text" class="form-control" name="CHQNo" placeholder="Enter CHQ No"  />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Ammount</label>
                            <input type="number" class="form-control" name="Ammount" placeholder="0.000" required="required" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Top 100 Transactions</h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <table id="table" class="table table-bordered table-striped datatable">
                                <thead>
                                    <tr>                                        
                                        <th>Debit Account Name</th>                                        
                                        <th>Debit Account</th>
                                        <th>Credit Account Name</th>
                                        <th>Credit Account</th>
                                        <th>Ammount</th>
                                        <th>Transaction Date</th>
                                    </tr>
                                </thead>
                                <tbody><?php foreach ($all_BankTransaction as $transaction): ?>
                                    <tr>
                                        <td><?php echo remove_junk($transaction['DebitAccountName']); ?>
                                        </td>
                                        <td><?php echo remove_junk($transaction['DEBAccount']); ?>
                                        </td>
                                        <td><?php echo remove_junk($transaction['CreditAccountName']); ?>
                                        </td>
                                        <td><?php echo remove_junk($transaction['CREAccount']); ?>
                                        </td>
                                        <td><?php echo remove_junk($transaction['Amount']); ?>
                                        </td>
                                        <td><?php echo remove_junk($transaction['TrnDate']); ?>
                                        </td>
                                    </tr><?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

</section>

<!--<script type="text/javascript">
    function FillBranchas() {
        $('.loader').show();
    var jBankCode = $('#BankCode').val();

        $.ajax({
            url: "add_BankAccountDetails.php",
            type: "POST",
            data: { jBankCode: jBankCode },
            success: function (result) {
                $("#BankBranchCode").html(""); // clear before appending new list
                $("#BankBranchCode").html(result);
                $('.loader').fadeOut();
            }
        });

    }
</script>-->

<?php include_once('layouts/footer.php'); ?>