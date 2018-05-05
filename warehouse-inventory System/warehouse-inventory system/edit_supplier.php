<?php
ob_start();
$page_title = 'Supplier - Edit Supplier';
require_once('includes/load.php');
UserPageAccessControle(1,'Supplier Edit');

preventGetAction('supplier.php');

$allCurrencyTypes = find_by_sql("call spSelectAllCurrency();");
?>

<?php 
if(isset($_POST['supplier'])){
    $p_SupplierCode = remove_junk($db->escape($_POST['SupplierCode']));
    $SupplierCount = find_by_sp("call spSelectSupplierByCode('{$p_SupplierCode}');");
}
?>

<?php
if(isset($_POST['edit_supplier'])){
    $req_fields = array('hSupplierCode','SupplierName','SupplierAddress2','SupplierAddress3','Telephone');
    validate_fields($req_fields);

    if(empty($errors)){
        $p_SupplierCode = remove_junk($db->escape($_POST['hSupplierCode']));
        $p_SupplierName = remove_junk($db->escape($_POST['SupplierName']));
        $p_SupplierAddress1 = remove_junk($db->escape($_POST['SupplierAddress1']));
        $p_SupplierAddress2 = remove_junk($db->escape($_POST['SupplierAddress2']));
        $p_SupplierAddress3 = remove_junk($db->escape($_POST['SupplierAddress3']));
        $p_Telephone = remove_junk($db->escape($_POST['Telephone']));
        $p_Fax = remove_junk($db->escape($_POST['Fax']));
        $p_Email = remove_junk($db->escape($_POST['Email']));
        $p_ContactPerson = remove_junk($db->escape($_POST['ContactPerson']));
        $p_VatNo = remove_junk($db->escape($_POST['VatNo']));
        $p_SVatNo = remove_junk($db->escape($_POST['SVatNo']));
        $p_CreditPeriod = remove_junk($db->escape($_POST['CreditPeriod']));
        $p_CurrencyCode = remove_junk($db->escape($_POST['CurrencyCode']));
        $p_date = make_date();
        $p_user = current_user();

        $SupplierCount = find_by_sp("call spSelectSupplierByCode('{$p_SupplierCode}');");

        if(!$SupplierCount)
        {
            $session->msg('d','Supplier not found ');
            redirect('supplier.php',false);
        }

        $query = "call spUpdateSupplier('{$p_SupplierCode}','{$p_SupplierName}','{$p_SupplierAddress1}','{$p_SupplierAddress2}','{$p_SupplierAddress3}','{$p_Telephone}',
'{$p_Fax}','{$p_Email}','{$p_ContactPerson}','{$p_VatNo}','{$p_SVatNo}','{$p_CreditPeriod}','{$p_CurrencyCode}','{$p_user["username"]}');";

        if($db->query($query))
        {
            InsertRecentActvity("Supplier updated","Reference No. ".$p_SupplierCode);

            $session->msg('s',"Supplier updated");
            redirect('supplier.php', false);
        }
        else
        {
            $session->msg('d',' Sorry failed to update!');
            redirect('supplier.php', false);
        }
    }
    else
    {
        $session->msg("d", $errors);
        redirect('supplier.php',false);
    }
}


?>

<?php include_once ('layouts/header.php') ?>
<section class="content-header">
    <h1>
        Supplier
        <small>Edit Supplier Details</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Master
            </a>
        </li>
        <li class="active">Supplier</li>
    </ol>
    <style>
        form {
            display: inline;
        }
    </style>
</section>

<section class="content">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="edit_supplier" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'supplier.php'">Cancel  </button>
                        </div>
                    </div>
                </div>
            </div>
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
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12"><?php echo display_msg($msg); ?>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-4">
                        <lable>Supplier Code</lable>
                        <input type="text" class="form-control" name="SupplierCode" placeholder="Supplier Code" required="required" disabled="disabled" readonly="readonly" value="<?php echo remove_junk($SupplierCount['SupplierCode']) ?>" />
                        <input type="hidden" name="hSupplierCode" value="<?php echo remove_junk($SupplierCount['SupplierCode']) ?>" />
                    </div>
                    <div class="col-md-4">
                        <lable>Supplier Name</lable>
                        <input type="text" class="form-control" name="SupplierName" placeholder="Supplier Name" required="required" value="<?php echo remove_junk($SupplierCount['SupplierName']) ?>" />
                    </div>
                    <div class="col-md-4">
                        <lable>Supplier Address</lable>
                        <input type="text" class="form-control" name="SupplierAddress1" placeholder="Street Number" value="<?php echo remove_junk($SupplierCount['SupplierAddress1']) ?>" />
                        <input type="text" class="form-control" name="SupplierAddress2" placeholder="Street Name" required="required" value="<?php echo remove_junk($SupplierCount['SupplierAddress2']) ?>" />
                        <input type="text" class="form-control" name="SupplierAddress3" placeholder="City" required="required" value="<?php echo remove_junk($SupplierCount['SupplierAddress3']) ?>" />
                    </div>
                </div>
            </div>
        </div>
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Contac Details</h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">

                <div class="row form-group">
                    <div class="col-md-3">
                        <lable>Telephone:</lable>
                        <input type="text" class="form-control integer" name="Telephone" placeholder="Contact Number" required="required" value="<?php echo remove_junk($SupplierCount['SupplierTel']) ?>" />
                    </div>
                    <div class="col-md-3">
                        <lable>Fax:</lable>
                        <input type="text" class="form-control integer" name="Fax" placeholder="Fax" value="<?php echo remove_junk($SupplierCount['SupplierFax']) ?>" />
                    </div>
                    <div class="col-md-3">
                        <lable>Email:</lable>
                        <input type="email" class="form-control" name="Email" placeholder="Email" value="<?php echo remove_junk($SupplierCount['SupplierEmail']) ?>" />
                    </div>
                    <div class="col-md-3">
                        <lable>Contact Person</lable>
                        <input type="text" class="form-control" name="ContactPerson" placeholder="Contac Person" value="<?php echo remove_junk($SupplierCount['SupplierContactPerson']) ?>" />
                    </div>
                </div>


            </div>
        </div>
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Other Details</h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row form-group">

                    <div class="col-md-3">
                        <lable>VAT No:</lable>
                        <input type="text" class="form-control" name="VatNo" placeholder="VAT No" value="<?php echo remove_junk($SupplierCount['SupplierVatNo']) ?>" />
                    </div>
                    <!--<div class="col-md-3">
                        <lable>SVAT No:</lable>
                        <input type="text" class="form-control" name="SVatNo" placeholder="SVAT No" value="<?php echo remove_junk($SupplierCount['SupplierSVatNo']) ?>" />-->
                    <!--</div>-->

                    <div class="col-md-3">
                        <lable>Credit Period</lable>
                        <input type="text" class="form-control integer" name="CreditPeriod" placeholder="Credit Period" value="<?php echo remove_junk($SupplierCount['SupplierCreditPeriod']) ?>" />
                    </div>
                    <div class="col-md-3">
                        <lable>Currency</lable>
                        <select class="form-control select2" name="CurrencyCode">
                            <option value="">Select Currency</option><?php foreach($allCurrencyTypes as $allcurrency): ?>
                            <option value=<?php echo remove_junk($allcurrency['CurrencyCode']); ?>
                                    <?php if(remove_junk($allcurrency['CurrencyCode']) === remove_junk($SupplierCount['CurrencyCode'])): ?> selected="selected"
                                    <?php endif ?>><?php echo remove_junk($allcurrency['CurrencyDescription']); ?>
                            </option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

<?php include_once('layouts/footer.php'); ?>