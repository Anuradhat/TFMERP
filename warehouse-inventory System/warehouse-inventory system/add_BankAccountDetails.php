<?php
ob_start();
session_start();

$page_title = 'Bank Account - New Bank Account';
require_once('includes/load.php');
UserPageAccessControle(1,'Bank Accounts Create');

$allBanks = find_by_sql("call spSelectAllBanks();");
$allAccountType = find_by_sql("call spSelectAllAccountType();");
$all_BankAccounts = find_by_sql("call spSelectAllBankAccountDetails();");
?>

<?php

if (isset($_POST['jBankCode'])) {

    $BankCode = remove_junk($db->escape($_POST['jBankCode']));

    $all_Branch = find_by_sql("call spSelectBankBranchFromBankCode('{$BankCode}');");

    echo '<option value="">Select Branch</option>';
    foreach($all_Branch as &$value){

        echo '<option value='.remove_junk(ucfirst($value["BranchCode"])).'>'.remove_junk(ucfirst($value["BranchName"])).'</option>';
    }

    return;
}

if(isset($_POST['add_BankAccounts'])){
    $req_fields = array('AccountType','BankCode','BankBranchCode','AccountNumber','AccountName');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_AccountType  = remove_junk($db->escape($_POST['AccountType']));
        $p_BankCode  = remove_junk($db->escape($_POST['BankCode']));
        $p_BankBranchCode  = remove_junk($db->escape($_POST['BankBranchCode']));
        $p_AccountNumber  = remove_junk($db->escape($_POST['AccountNumber']));
        $p_AccountName  = remove_junk($db->escape($_POST['AccountName']));


        //$epfno = $p_EpfNumber;

        $date    = make_date();
        $user = current_user();

        $bank_accountdet= find_by_sp("call spSelectAccountDetailsFromBankAndAccountNumber('{$p_BankCode}','{$p_AccountNumber}');");

        if($bank_accountdet)
        {
            $flashMessages->warning('This account exist in the system.','add_BankAccountDetails.php');
            //$session->msg("d", "This account exist in the system.");
            //redirect('add_BankAccountDetails.php',false);
        }

        $query  = "call spInsertBankAccounts('{$p_BankCode}','{$p_BankBranchCode}','{$p_AccountNumber}','{$p_AccountName}','{$p_AccountType}','{$user["username"]}');";

        if($db->query($query)){
            InsertRecentActvity("Bank account created","Reference No. ".$p_AccountNumber);

            $flashMessages->success('Bank account created. ','add_BankAccountDetails.php');
            //$session->msg('s',"Bank account created ");
            //redirect('add_BankAccountDetails.php', false);
        } else {
            $flashMessages->error('Account not created ','add_BankAccountDetails.php');
            //$session->msg('d',' Sorry failed to add!');
            //redirect('BankAccountDetails.php', false);
        }

    } else{
        $flashMessages->error($errors,'add_BankAccountDetails.php');
        //$session->msg("d", $errors);
        //redirect('add_BankAccountDetails.php',false);
    }
}

?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Bank Accounts
        <small>Enter New Bank Account Details</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Bank
            </a>
        </li>
        <li class="active">Bank Accounts</li>
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
    <form method="post" action="add_BankAccountDetails.php">

        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="add_BankAccounts" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'BankAccountDetails.php'">Cancel  </button>
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
                <h3 class="box-title">Basic Details</h3>

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
                            <label>Bank</label>
                            <select required class="form-control select2" name="BankCode" id="BankCode" onchange="FillBranchas();">
                                <option value="">Select Bank</option><?php foreach($allBanks as $allBnk): ?>
                                <option value=<?php echo remove_junk($allBnk['BankCode']); ?>><?php echo remove_junk($allBnk['BankName']); ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Bank Branch</label>
                            <select required class="form-control select2" name="BankBranchCode" id="BankBranchCode">

                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Account Type</label>
                            <select required class="form-control select2" name="AccountType" id="AccountType" >
                                <option value="">Select Account Type</option><?php foreach($allAccountType as $allType): ?>
                                <option value=<?php echo remove_junk($allType['AccountType']); ?>><?php echo remove_junk($allType['AccountTypeDescription']); ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Account Number</label>
                            <input type="text" class="form-control" name="AccountNumber" placeholder="Account Number" required="required"/>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Account Name</label>
                            <input type="text" class="form-control" name="AccountName" placeholder="Account Name" required="required" />
                        </div>
                    </div>


                </div>
            </div>
        </div>

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Account Details</h3>

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
                                        <th>ID</th>
                                        <th>Bank Name</th>
                                        <th>Branch Name</th>
                                        <th>Account Type Description</th>
                                        <th>Account No</th>
                                        <th>Account Name</th>
                                    </tr>
                                </thead>
                                <tbody><?php foreach ($all_BankAccounts as $accouts): ?>
                                    <tr>
                                        <td class="clsRowId"><?php echo remove_junk(ucfirst($accouts['ID'])); ?>
                                        </td>
                                        <td><?php echo remove_junk($accouts['BankName']); ?>
                                        </td>
                                        <td><?php echo remove_junk($accouts['BranchName']); ?>
                                        </td>
                                        <td><?php echo remove_junk($accouts['AccountTypeDescription']); ?>
                                        </td>
                                        <td><?php echo remove_junk($accouts['AccountNo']); ?>
                                        </td>
                                        <td><?php echo remove_junk($accouts['AccountName']); ?>
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

<script type="text/javascript">
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
</script>

<?php include_once('layouts/footer.php'); ?>