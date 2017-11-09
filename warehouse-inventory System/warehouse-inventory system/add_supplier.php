<?php
ob_start();
$page_title = 'Supplier - New Supplier';
require_once('includes/load.php');
page_require_level(2);
$allCurrencyTypes = find_by_sql("call spSelectAllCurrency();")
?>

<?php
if(isset($_POST['add_supplier'])){
    $req_fields = array('SupplierName','SupplierAddress2','SupplierAddress3','Telephone');
    validate_fields($req_fields);

    if(empty($errors)){
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

        try
        {
            $db->begin();

            //Supplier code
            $p_SupplierCode = autoGenerateNumber('tfmSupplierM',1);

            $SupplierCount = find_by_sp("call spSelectSupplierByCode('{$p_SupplierCode}');");

            if($SupplierCount)
            {
                $db->rollback();
                $session->msg('d','This supplier code exist in the system.');
                redirect('add_supplier.php',false);
            }

            $query = "call spInsertSupplier('{$p_SupplierCode}','{$p_SupplierName}','{$p_SupplierAddress1}','{$p_SupplierAddress2}','{$p_SupplierAddress3}','{$p_Telephone}',
           '{$p_Fax}','{$p_Email}','{$p_ContactPerson}','{$p_VatNo}','{$p_SVatNo}','{$p_CreditPeriod}','{$p_CurrencyCode}','{$p_user["username"]}');";

            if($db->query($query))
            {
                $db->commit();
                $session->msg('s',"Supplier added ");
                redirect('supplier.php', false);
            }
            else
            {
                $db->rollback();
                $session->msg('d',' Sorry failed to added!');
                redirect('add_supplier.php', false);
            }
        }
        catch(Exception $ex)
        {
            $db->rollback();

            $session->msg('d',' Sorry failed to added!');
            redirect('supplier.php', false);
        }
    }
    else
    {
        $session->msg("d", $errors);
        redirect('add_supplier.php',false);
    }
}


?>

<?php include_once ('layouts/header.php') ?>
<section class="content-header">
    <h1>
        Supplier
        <small>Enter New Supplier Details</small>
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
                            <button type="submit" name="add_supplier" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'supplier.php'">Cancel  </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php echo display_msg($msg); ?>
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
                <div class="row form-group">
                    <div class="col-md-4">
                        <lable>Supplier Code</lable>
                        <input type="text" class="form-control" name="SupplierCode" placeholder="Code will generate after save"  readonly="readonly" disabled="disabled"/>
                    </div>
                    <div class="col-md-4">
                        <lable>Supplier Name</lable>
                        <input type="text" class="form-control" name="SupplierName" placeholder="Supplier Name" required="required" />
                    </div>
                    <div class="col-md-4">
                        <lable>Supplier Address</lable>
                        <input type="text" class="form-control" name="SupplierAddress1" placeholder="Street Number" />
                        <input type="text" class="form-control" name="SupplierAddress2" placeholder="Street Name" required="required" />
                        <input type="text" class="form-control" name="SupplierAddress3" placeholder="City" required="required" />
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
                        <input type="text" class="form-control integer" name="Telephone" placeholder="Contact Number" required="required" />
                    </div>
                    <div class="col-md-3">
                        <lable>Fax:</lable>
                        <input type="text" class="form-control integer" name="Fax" placeholder="Fax" />
                    </div>
                    <div class="col-md-3">
                        <lable>Email:</lable>
                        <input type="email" class="form-control" name="Email" placeholder="Email" />
                    </div>
                    <div class="col-md-3">
                        <lable>Contact Person</lable>
                        <input type="text" class="form-control" name="ContactPerson" placeholder="Contact Person" />
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
                        <input type="text" class="form-control" name="VatNo" placeholder="VAT No" />
                    </div>
                    <div class="col-md-3">
                        <lable>SVAT No:</lable>
                        <input type="text" class="form-control" name="SVatNo" placeholder="SVAT No" />
                    </div>
                
                    <div class="col-md-3">
                        <lable>Credit Period</lable>
                        <input type="text" class="form-control integer" name="CreditPeriod" placeholder="Credit Period" />
                    </div>
                    <div class="col-md-3">
                        <lable>Currency</lable>
                        <select class="form-control select2" name="CurrencyCode">
                            <option value="">Select Currency</option>
                            <?php foreach($allCurrencyTypes as $allcurrency): ?>
                            <option value=<?php echo remove_junk($allcurrency['CurrencyCode']); ?>><?php echo remove_junk($allcurrency['CurrencyDescription']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

<?php include_once('layouts/footer.php'); ?>

<script>
    //Textbox integer accept
    $(".integer").keypress(function (e) {
        if ((e.which < 48 || e.which > 57)  && e.which != 8) {
            return (false);  // stop processing
        }
    });

    //Textbox decimal accept
    $(".decimal").keypress(function (e) {
       var ex = /^[0-9]+\.?[0-9]*$/;
       if(ex.test(e.value)==false){
           e.value = e.value.substring(0,e.value.length - 1);
       }
    });
</script>