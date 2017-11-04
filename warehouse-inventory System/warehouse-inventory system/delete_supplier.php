<?php
ob_start();
$page_title = 'Supplier - Delete Supplier';
require_once('includes/load.php');
page_require_level(2);

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
if(isset($_POST['deletesupplier2'])){
    $req_fields = array('hSupplierCode');
    validate_fields($req_fields);

    if(empty($errors)){
        $p_SupplierCode = remove_junk($db->escape($_POST['hSupplierCode']));
        
        $p_date = make_date();
        $p_user = current_user();

        $SupplierCount = find_by_sp("call spSelectSupplierByCode('{$p_SupplierCode}');");

        if(!$SupplierCount)
        {
            $session->msg('d','Supplier not found');
            redirect('Supplier.php',false);
        }

        $query = "call spDeleteSupplier('{$p_SupplierCode}','{$p_user["username"]}');";

        if($db->query($query))
        {
            $session->msg('s',"Supplier deleted ");
            redirect('supplier.php', false);
        }
        else
        {
            $session->msg('d',' Sorry failed to delete!');
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
        <small>Delete Supplier</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Master
            </a>
        </li>
        <li class="active">Delete Supplier</li>
    </ol>
    <style>
        form {
            display: inline;
        }
    </style>
</section>

<section class="content">
    <form method="post"  id="deleteSupplierForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
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
                        <input type="text" class="form-control" name="SupplierName" placeholder="Supplier Name" required="required" disabled="disabled" readonly="readonly" value="<?php echo remove_junk($SupplierCount['SupplierName']) ?>" />
                    </div>
                    <div class="col-md-4">
                        <lable>Supplier Address</lable>
                        <input type="text" class="form-control" name="SupplierAddress1" placeholder="Street Number" disabled="disabled" readonly="readonly" value="<?php echo remove_junk($SupplierCount['SupplierAddress1']) ?>" />
                        <input type="text" class="form-control" name="SupplierAddress2" placeholder="Street Name" required="required" disabled="disabled" readonly="readonly" value="<?php echo remove_junk($SupplierCount['SupplierAddress2']) ?>" />
                        <input type="text" class="form-control" name="SupplierAddress3" placeholder="City" required="required" disabled="disabled" readonly="readonly" value="<?php echo remove_junk($SupplierCount['SupplierAddress3']) ?>" />
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
                        <input type="text" class="form-control" name="Telephone" placeholder="Contact Number" required="required" disabled="disabled" readonly="readonly" value="<?php echo remove_junk($SupplierCount['SupplierTel']) ?>" />
                    </div>
                    <div class="col-md-3">
                        <lable>Fax:</lable>
                        <input type="text" class="form-control" name="Fax" placeholder="Fax" disabled="disabled" readonly="readonly" value="<?php echo remove_junk($SupplierCount['SupplierFax']) ?>" />
                    </div>
                    <div class="col-md-3">
                        <lable>Email:</lable>
                        <input type="email" class="form-control" name="Email" placeholder="Email" disabled="disabled" readonly="readonly" value="<?php echo remove_junk($SupplierCount['SupplierEmail']) ?>" />
                    </div>
                    <div class="col-md-3">
                        <lable>Contact Person</lable>
                        <input type="text" class="form-control" name="ContactPerson" placeholder="Contac Person" disabled="disabled" readonly="readonly" value="<?php echo remove_junk($SupplierCount['SupplierContactPerson']) ?>" />
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
                        <input type="text" class="form-control" name="VatNo" placeholder="VAT No" disabled="disabled" readonly="readonly" value="<?php echo remove_junk($SupplierCount['SupplierVatNo']) ?>" />
                    </div>
                    <div class="col-md-3">
                        <lable>SVAT No:</lable>
                        <input type="text" class="form-control" name="SVatNo" placeholder="SVAT No" disabled="disabled" readonly="readonly" value="<?php echo remove_junk($SupplierCount['SupplierSVatNo']) ?>" />
                    </div>

                    <div class="col-md-3">
                        <lable>Credit Period</lable>
                        <input type="text" class="form-control" name="CreditPeriod" placeholder="Credit Period" disabled="disabled" readonly="readonly" value="<?php echo remove_junk($SupplierCount['SupplierCreditPeriod']) ?>" />
                    </div>
                    <div class="col-md-3">
                        <lable>Currency</lable>
                        <select class="form-control" name="CurrencyCode" disabled="disabled" readonly="readonly">
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
        <input type="submit" name="deletesupplier"  class="btn btn-danger" onclick="deleteConfirmation(this, event);" value="Delete  ">
        <a class="btn btn-info" href="Supplier.php">Cancel</a>
        <input type="hidden" name="deletesupplier2" value="hello" />
    </form>
</section>

<?php include_once('layouts/footer.php'); ?>

<script>
    function deleteConfirmation(ctl, event) {
        event.preventDefault();
        bootbox.confirm({
            title: "Delete Confirmation",
            message: "Do you want to delete the selected supplier? This cannot be undone.",
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> Cancel'
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> Confirm'
                }
            },
            callback: function (result) {
                if (result === true) {
                    $("#deleteSupplierForm").submit();
                }
            }
        });
    }
</script>
