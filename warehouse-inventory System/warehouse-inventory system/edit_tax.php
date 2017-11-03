<?php
$page_title = 'Tax Master - Edit Tax';
require_once('includes/load.php');
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
    else
    {
        $tax = find_by_sp("call spSelectTaxRatesFromCode('{$p_TaxCode}');");

        if(!$tax){
            $session->msg("d","Missing tax details.");
            redirect('tax.php');
        }
    }
}
?>


<?php
if(isset($_POST['edit_tax'])){
    $req_fields = array('hTaxCode','TaxDesc','TaxRate');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_TaxCode  = remove_junk($db->escape($_POST['hTaxCode']));
        $p_TaxDesc  = remove_junk($db->escape($_POST['TaxDesc']));
        $p_TaxRate  = remove_junk($db->escape($_POST['TaxRate']));

        $date    = make_date();
        $user = "anush";

        $query  = "call spUpdateTaxRates('{$p_TaxCode}','{$p_TaxDesc}',{$p_TaxRate},'{$date}','{$user}');";

        if($db->query($query)){
            $session->msg('s',"Tax updated");
            redirect('tax.php', false);
        } else {
            $session->msg('d',' Sorry failed to updated!');
            //redirect('customer.php', false);
        }

    } else{
        $session->msg("d", $errors);
        redirect('edit_tax.php',false);
    }
}

?>



<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Tax Master
        <small>Enter New Tax Details</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Master
            </a>
        </li>
        <li class="active">Tax</li>
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
    <form method="post" action="edit_tax.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="edit_tax" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'tax.php'">Cancel  </button>
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
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tax Code</label>
                            <input type="text" class="form-control" name="TaxCode" placeholder="Tax Code" required="required" value="<?php echo remove_junk($tax['TaxCode']);?>" readonly="readonly" disabled="disabled" />
                            <input type="hidden" name="hTaxCode" value="<?php echo remove_junk($tax['TaxCode']);?>" />
                        </div>

                        <div class="form-group">
                            <label>Tax Rate (%)&nbsp;<output for="fader" id="rate"><?php echo remove_junk($tax['TaxRate']);?></output> </label>
                            <input type="range" min="0" max="100" value="<?php echo remove_junk($tax['TaxRate']);?>" step="1" class="form-control" name="TaxRate" placeholder="Tax Rate (%)" required="required" oninput="outputUpdate(value)" />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tax Description</label>
                            <input type="text" id="fader" class="form-control col-md-4" name="TaxDesc" value="<?php echo remove_junk($tax['TaxDesc']);?>" placeholder="Tax Description" required="required" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

<?php include_once('layouts/footer.php'); ?>

<script>
    function outputUpdate(vol) {
        document.querySelector('#rate').value = vol;
    }
</script>